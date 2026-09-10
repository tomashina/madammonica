(function (window, document) {
  'use strict';

  var script = document.currentScript;
  var pixelId = script ? String(script.getAttribute('data-pixel-id') || '') : '';
  var consentCookieName = 'mm_consent_v1';
  var consent = readMarketingConsent();
  var pendingEvents = [];
  var pendingOnceEvents = {};
  var contextEvents = {};
  var sentContextEvents = {};
  var sentOnceEvents = {};
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

  function send(eventName, parameters, custom, eventId) {
    installBaseCode();
    var method = custom ? 'trackSingleCustom' : 'trackSingle';
    if (eventId) {
      window.fbq(method, pixelId, eventName, parameters || {}, {eventID: String(eventId)});
    } else {
      window.fbq(method, pixelId, eventName, parameters || {});
    }
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

  function onceStorageKey(eventName, dedupeId) {
    return 'mm_meta_event:' + pixelId + ':' + eventName + ':' + String(dedupeId);
  }

  function wasSentOnce(key) {
    if (sentOnceEvents[key]) return true;

    try {
      return window.localStorage.getItem(key) === '1';
    } catch (error) {
      return false;
    }
  }

  function markSentOnce(key) {
    sentOnceEvents[key] = true;

    try {
      window.localStorage.setItem(key, '1');
    } catch (error) {
      // In-memory deduplication still protects this page when storage is blocked.
    }
  }

  function trackOnce(eventName, dedupeId, parameters, eventId) {
    if (!eventName || !dedupeId) return;

    var key = onceStorageKey(eventName, dedupeId);
    if (wasSentOnce(key)) return;

    if (consent === true) {
      send(eventName, parameters, false, eventId);
      markSentOnce(key);
    } else if (consent === null) {
      pendingOnceEvents[key] = {
        name: eventName,
        parameters: parameters || {},
        eventId: eventId || ''
      };
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

      Object.keys(pendingOnceEvents).forEach(function (key) {
        var event = pendingOnceEvents[key];
        if (!wasSentOnce(key)) {
          send(event.name, event.parameters, false, event.eventId);
          markSentOnce(key);
        }
      });
      pendingOnceEvents = {};
    }
  }

  function revokeConsent() {
    consent = false;
    pendingEvents = [];
    pendingOnceEvents = {};
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

  function queryValue(name) {
    var query = String(window.location.search || '').replace(/^\?/, '').split('&');

    for (var i = 0; i < query.length; i += 1) {
      var parts = query[i].split('=');
      if (decodeURIComponent((parts.shift() || '').replace(/\+/g, ' ')) === name) {
        return decodeURIComponent(parts.join('=').replace(/\+/g, ' '));
      }
    }

    return '';
  }

  function responseJson(xhr) {
    if (xhr.responseJSON) return xhr.responseJSON;

    if (xhr.responseText) {
      try { return JSON.parse(xhr.responseText); } catch (error) { return null; }
    }

    return null;
  }

  function endpointEvent(route, callback) {
    if (!window.jQuery) return;

    var baseUrl = window.jQuery('base').attr('href') || '';
    window.jQuery.ajax({
      url: baseUrl + 'index.php?route=extension/fbecommevnt/' + route,
      type: 'get',
      dataType: 'json',
      cache: false
    }).done(callback);
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

    function trackPageCommerceEvents() {
      var route = queryValue('route');
      var searchString = queryValue('search');
      var bodyClass = String(document.body.className || '');

      if (searchString && (route === 'product/search' || bodyClass.indexOf('product-search') !== -1 || $('#product-search').length)) {
        track('Search', {
          search_string: searchString,
          content_category: 'search'
        }, false);
      }

      if (route === 'checkout/checkout' || bodyClass.indexOf('checkout-checkout') !== -1 || $('#checkout-checkout').length) {
        endpointEvent('cartevent', function (response) {
          if (response && response.items) track('InitiateCheckout', response.items, false);
        });
      }

      if (route === 'checkout/success' || bodyClass.indexOf('checkout-success') !== -1) {
        endpointEvent('purchaseevent', function (response) {
          if (response && response.order_id && response.items) {
            trackOnce('Purchase', response.order_id, response.items, response.event_id);
          }
        });
      }
    }

    trackVisibleProduct();
    trackPageCommerceEvents();

    $(document).ajaxSuccess(function (_event, xhr, settings) {
      if (!settings) return;

      var requestUrl = String(settings.url || '');
      if (requestUrl.indexOf('route=product/product') !== -1) {
        window.setTimeout(trackVisibleProduct, 0);
      }

      var response = responseJson(xhr);
      if (!response || !response.success) return;

      var request = parseRequestData(settings.data);
      if (requestUrl.indexOf('route=checkout/cart/add') !== -1 || requestUrl.indexOf('route=extension/basel/basel_features/add_to_cart') !== -1) {
        productEvent(request.product_id, 'AddToCart', parseInt(request.quantity, 10) || 1);
      } else if (requestUrl.indexOf('route=account/wishlist/add') !== -1 || requestUrl.indexOf('route=extension/basel/basel_features/add_to_wishlist') !== -1) {
        productEvent(request.product_id, 'AddToWishlist', 1);
      }
    });
  }

  window.mmMetaPixel = {
    pixelId: pixelId,
    track: function (eventName, parameters) { track(eventName, parameters, false); },
    trackCustom: function (eventName, parameters) { track(eventName, parameters, true); },
    trackOnce: trackOnce,
    productEvent: productEvent,
    getState: function () {
      return {
        pixelId: pixelId,
        consent: consent,
        initialized: initialized,
        pendingEvents: pendingEvents.length,
        pendingOnceEvents: Object.keys(pendingOnceEvents).length
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
