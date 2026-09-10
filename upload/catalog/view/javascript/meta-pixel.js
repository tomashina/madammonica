(function (window, document) {
  'use strict';

  var script = document.currentScript;
  var pixelId = script ? String(script.getAttribute('data-pixel-id') || '') : '';
  var consentCookieName = 'mm_consent_v1';
  var consent = readMarketingConsent();
  var pendingEvents = [];
  var contextEvents = {};
  var sentContextEvents = {};
  var initialized = false;

  if (!/^\d+$/.test(pixelId)) return;

  function readMarketingConsent() {
    var match = document.cookie.match(new RegExp('(?:^|; )' + consentCookieName + '=([^;]*)'));
    if (!match) return null;

    try {
      var value = JSON.parse(decodeURIComponent(match[1]));
      if (!value || value.revision !== 1) return null;
      return value.marketing === true;
    } catch (error) {
      return null;
    }
  }

  function installBaseCode() {
    if (initialized) return;
    initialized = true;

    if (!window.fbq) {
      (function (f, b, e, v, n, t, s) {
        if (f.fbq) return;
        n = f.fbq = function () {
          n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
        };
        if (!f._fbq) f._fbq = n;
        n.push = n;
        n.loaded = true;
        n.version = '2.0';
        n.queue = [];
        t = b.createElement(e);
        t.async = true;
        t.src = v;
        s = b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t, s);
      }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js'));
    }

    window.fbq('init', pixelId);
  }

  function send(eventName, parameters, custom) {
    installBaseCode();
    var method = custom ? 'trackSingleCustom' : 'trackSingle';
    window.fbq(method, pixelId, eventName, parameters || {});
  }

  function track(eventName, parameters, custom) {
    if (!eventName) return;

    if (eventName === 'PageView' || eventName === 'ViewContent') {
      contextEvents[eventName] = {parameters: parameters || {}, custom: !!custom};
    }

    if (consent === true) {
      send(eventName, parameters, custom);
      if (contextEvents[eventName]) sentContextEvents[eventName] = true;
    } else if (consent === null && !contextEvents[eventName]) {
      pendingEvents.push({name: eventName, parameters: parameters || {}, custom: !!custom});
    }
  }

  function grantConsent() {
    var wasGranted = consent === true;
    consent = true;
    installBaseCode();
    window.fbq('consent', 'grant');

    if (!wasGranted) {
      Object.keys(contextEvents).forEach(function (eventName) {
        if (!sentContextEvents[eventName]) {
          var event = contextEvents[eventName];
          send(eventName, event.parameters, event.custom);
          sentContextEvents[eventName] = true;
        }
      });

      pendingEvents.splice(0).forEach(function (event) {
        send(event.name, event.parameters, event.custom);
      });
    }
  }

  function revokeConsent() {
    consent = false;
    pendingEvents = [];
    if (window.fbq && initialized) window.fbq('consent', 'revoke');
  }

  function parseRequestData(data) {
    var values = {};

    if (typeof data === 'string') {
      data.split('&').forEach(function (pair) {
        var parts = pair.split('=');
        var key = decodeURIComponent((parts.shift() || '').replace(/\+/g, ' '));
        var value = decodeURIComponent(parts.join('=').replace(/\+/g, ' '));
        if (key) values[key] = value;
      });
    } else if (data && typeof data === 'object') {
      values = data;
    }

    return values;
  }

  function productEvent(productId, eventName, quantity) {
    if (!productId || !window.jQuery) return;

    window.jQuery.ajax({
      url: 'index.php?route=extension/fbecommevnt/trackevent',
      type: 'post',
      dataType: 'json',
      cache: false,
      data: {product_id: productId, quantity: quantity || 1}
    }).done(function (response) {
      if (response && response.items) track(eventName, response.items, false);
    });
  }

  function registerCommerceTracking() {
    if (!window.jQuery) return;

    var $ = window.jQuery;
    var lastViewedProductId = null;

    function trackVisibleProduct() {
      var productId = $("input[name='product_id']").first().val();
      if (productId && String(productId) !== lastViewedProductId) {
        lastViewedProductId = String(productId);
        productEvent(productId, 'ViewContent', 1);
      }
    }

    trackVisibleProduct();

    $(document).ajaxSuccess(function (_event, xhr, settings) {
      if (!settings) return;

      var requestUrl = String(settings.url || '');
      if (requestUrl.indexOf('route=product/product') !== -1) {
        window.setTimeout(trackVisibleProduct, 0);
      }
      if (requestUrl.indexOf('route=checkout/cart/add') === -1) return;

      var response = xhr.responseJSON;
      if (!response && xhr.responseText) {
        try { response = JSON.parse(xhr.responseText); } catch (error) { response = null; }
      }
      if (!response || !response.success) return;

      var request = parseRequestData(settings.data);
      productEvent(request.product_id, 'AddToCart', parseInt(request.quantity, 10) || 1);
    });
  }

  window.mmMetaPixel = {
    pixelId: pixelId,
    track: function (eventName, parameters) { track(eventName, parameters, false); },
    trackCustom: function (eventName, parameters) { track(eventName, parameters, true); },
    productEvent: productEvent,
    getState: function () {
      return {
        pixelId: pixelId,
        consent: consent,
        initialized: initialized,
        pendingEvents: pendingEvents.length
      };
    }
  };

  document.addEventListener('mm:consent', function (event) {
    if (event.detail && event.detail.marketing === true) grantConsent();
    else revokeConsent();
  });

  if (consent === true) grantConsent();
  track('PageView', {}, false);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', registerCommerceTracking);
  } else {
    registerCommerceTracking();
  }
}(window, document));
