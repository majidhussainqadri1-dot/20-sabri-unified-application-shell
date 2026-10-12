(function () {
  'use strict';
  var cfg = window.sabriWelcomeFrequency || {};
  var host = document.getElementById('sabri-shell-welcome-host');
  if (!host || !cfg.seconds || !cfg.cookieName) { return; }
  var now = Math.floor(Date.now() / 1000);
  var period = Math.max(30 * 86400, Number(cfg.seconds) || 0);
  var siteScope = String(cfg.siteScope || 'default');
  var key = 'sabri-shell-welcome:v1:' + siteScope + ':' + String(cfg.scope || 'guest');
  var localKey = 'sabri-shell-welcome-dismissed:v1:' + siteScope;
  var cookiePath = typeof cfg.cookiePath === 'string' && /^\/[a-zA-Z0-9/_~.%+-]*\/$/.test(cfg.cookiePath) ? cfg.cookiePath : '/';
  function storage(name) { try { return window[name]; } catch (_) { return null; } }
  var session = storage('sessionStorage');
  var local = storage('localStorage');
  function get(store, name) { try { return store && store.getItem(name); } catch (_) { return null; } }
  function put(storage, name, value) { try { if (storage) { storage.setItem(name, String(value)); } } catch (_) {} }
  function cookieTime() {
    var parts = String(document.cookie || '').split(';');
    for (var i = 0; i < parts.length; i++) {
      var piece = parts[i].trim().split('=');
      if (piece[0] === cfg.cookieName && /^\d+$/.test(piece[1] || '')) { return Number(piece[1]); }
    }
    return 0;
  }
  function stillSuppressed(last) {
    last = Number(last);
    return Number.isFinite(last) && last > 0 && last <= now && (now - last) < period;
  }
  if (get(session, key) === '1') { return; }
  if (cfg.loggedIn !== true && (stillSuppressed(cookieTime()) || stillSuppressed(get(local, localKey)))) { return; }
  put(session, key, '1');
  var previousFocus = document.activeElement || null;
  host.hidden = false;
  var heading = host.querySelector && host.querySelector('h2');
  if (heading && typeof heading.focus === 'function') {
    if (typeof heading.setAttribute === 'function') { heading.setAttribute('tabindex', '-1'); }
    try { heading.focus({ preventScroll: true }); } catch (_) { heading.focus(); }
  }
  var closed = false;
  function dismiss() {
    if (closed) { return; }
    closed = true;
    var restoreFocus = previousFocus && typeof previousFocus.focus === 'function' &&
      (!host.contains || host.contains(document.activeElement));
    host.hidden = true;
    if (restoreFocus) { try { previousFocus.focus({ preventScroll: true }); } catch (_) { previousFocus.focus(); } }
    // Do not leak an authenticated account dismissal into shared guest storage.
    if (cfg.loggedIn !== true) {
      put(local, localKey, now);
      document.cookie = cfg.cookieName + '=' + now + '; Path=' + cookiePath + '; Max-Age=' + period + '; SameSite=Lax' +
        (window.location.protocol === 'https:' ? '; Secure' : '');
    }
    if (cfg.loggedIn !== true || !cfg.endpoint || !cfg.nonce || typeof window.fetch !== 'function') { return; }
    try {
      window.fetch(cfg.endpoint, {
        method: 'POST', credentials: 'same-origin', keepalive: true,
        headers: { 'X-WP-Nonce': cfg.nonce, 'Content-Type': 'application/json' },
        body: '{}'
      }).catch(function () {});
    } catch (_) {}
  }
  var controls = host.querySelectorAll('[data-sabri-welcome-dismiss], [data-sabri-welcome-skip], .sabri-ui-welcome-panel__actions a');
  Array.prototype.forEach.call(controls, function (element) {
    element.addEventListener('click', function (event) {
      // Continue already points to the current eligible homepage. A forced
      // reload disrupts content and may race an authenticated preference POST.
      // Preserve normal navigation for any future externally linked action.
      if (element.tagName === 'A' && typeof element.getAttribute === 'function' &&
          element.getAttribute('href') === cfg.homeUrl && event && typeof event.preventDefault === 'function') {
        event.preventDefault();
      }
      dismiss();
    }, { once: true });
  });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !host.hidden) { dismiss(); }
  });
}());