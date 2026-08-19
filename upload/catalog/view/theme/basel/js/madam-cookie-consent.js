(function () {
  'use strict';

  var COOKIE_NAME = 'mm_consent_v1';
  var root = document.getElementById('mm-consent');
  if (!root) return;

  var dialog = root.querySelector('.mm-consent__dialog');
  var preferences = document.getElementById('mm-consent-preferences');
  var analytics = document.getElementById('mm-consent-analytics');
  var marketing = document.getElementById('mm-consent-marketing');
  var preferencesButton = root.querySelector('[data-mm-consent-preferences]');
  var saveButton = root.querySelector('[data-mm-consent-save]');
  var hadChoice = false;
  var previousFocus = null;

  function readCookie() {
    var match = document.cookie.match(new RegExp('(?:^|; )' + COOKIE_NAME + '=([^;]*)'));
    if (!match) return null;
    try {
      var value = JSON.parse(decodeURIComponent(match[1]));
      return value && value.revision === 1 ? value : null;
    } catch (error) {
      return null;
    }
  }

  function writeCookie(value) {
    var expires = new Date(Date.now() + 182 * 86400000).toUTCString();
    var secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = COOKIE_NAME + '=' + encodeURIComponent(JSON.stringify(value)) + '; Expires=' + expires + '; Path=/; SameSite=Lax' + secure;
  }

  function clearOptionalCookies() {
    document.cookie.split(';').forEach(function (item) {
      var name = item.split('=')[0].trim();
      if (/^(_ga|_gid|_gat|_fbp|_fbc|_gcl)/.test(name)) {
        document.cookie = name + '=; Max-Age=0; Path=/; SameSite=Lax';
      }
    });
  }

  function activateScripts(category) {
    var selector = 'script[type="text/plain"][data-mm-consent="' + category + '"]:not([data-mm-consent-loaded])';
    Array.prototype.slice.call(document.querySelectorAll(selector)).forEach(function (source) {
      var script = document.createElement('script');
      Array.prototype.slice.call(source.attributes).forEach(function (attribute) {
        if (attribute.name !== 'type' && attribute.name !== 'data-mm-consent' && attribute.name !== 'data-mm-consent-loaded') {
          script.setAttribute(attribute.name, attribute.value);
        }
      });
      script.text = source.text || source.textContent || '';
      source.setAttribute('data-mm-consent-loaded', 'true');
      source.parentNode.insertBefore(script, source.nextSibling);
    });
  }

  function apply(value) {
    window.mmConsent = value;
    if (value.analytics && typeof window.mmLoadAnalytics === 'function') window.mmLoadAnalytics();
    if (value.marketing) activateScripts('marketing');
    if (!value.analytics && !value.marketing) clearOptionalCookies();
    document.dispatchEvent(new CustomEvent('mm:consent', {detail: value}));
  }

  function save(analyticsAllowed, marketingAllowed) {
    var value = {revision: 1, necessary: true, analytics: !!analyticsAllowed, marketing: !!marketingAllowed, timestamp: new Date().toISOString()};
    writeCookie(value);
    hadChoice = true;
    apply(value);
    close();
  }

  function open(showPreferences) {
    previousFocus = document.activeElement;
    root.hidden = false;
    root.classList.add('is-open');
    document.documentElement.classList.add('mm-consent-lock');
    preferences.hidden = !showPreferences;
    preferencesButton.hidden = !!showPreferences;
    saveButton.hidden = !showPreferences;
    if (showPreferences) {
      var current = readCookie() || {analytics: false, marketing: false};
      analytics.checked = !!current.analytics;
      marketing.checked = !!current.marketing;
    }
    window.setTimeout(function () { dialog.focus(); }, 20);
  }

  function close() {
    if (!hadChoice) return;
    root.classList.remove('is-open');
    root.hidden = true;
    document.documentElement.classList.remove('mm-consent-lock');
    if (previousFocus && previousFocus.focus) previousFocus.focus();
  }

  function focusable() {
    return Array.prototype.slice.call(dialog.querySelectorAll('a[href],button:not([hidden]),input:not([disabled])')).filter(function (element) {
      return element.offsetParent !== null;
    });
  }

  root.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && hadChoice) close();
    if (event.key !== 'Tab') return;
    var items = focusable();
    if (!items.length) return;
    var first = items[0];
    var last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
  });

  root.addEventListener('click', function (event) {
    if (event.target.closest('[data-mm-consent-all]')) save(true, true);
    else if (event.target.closest('[data-mm-consent-necessary]')) save(false, false);
    else if (event.target.closest('[data-mm-consent-preferences]')) open(true);
    else if (event.target.closest('[data-mm-consent-save]')) save(analytics.checked, marketing.checked);
    else if (event.target.closest('[data-mm-consent-close]')) close();
  });

  document.addEventListener('click', function (event) {
    if (event.target.closest('[data-mm-consent-open]')) open(true);
  });

  var current = readCookie();
  hadChoice = !!current;
  window.MadamMonicaConsent = {
    show: function () { open(true); },
    get: readCookie,
    has: function (category) { var choice = readCookie(); return !!(choice && choice[category]); }
  };

  if (current) apply(current);
  else open(false);
}());
