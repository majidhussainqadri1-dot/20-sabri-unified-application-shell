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
  function removeExpired(store, name) {
    var value = get(store, name);
    if (value === null || value === undefined) { return false; }
    if (stillSuppressed(value)) { return true; }
    // Browser storage has no built-in Max-Age. Expire only this File20/site
    // preference and never delete another plugin's or site's browser state.
    try { if (store && typeof store.removeItem === 'function') { store.removeItem(name); } } catch (_) {}
    return false;
  }
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
  // A surviving/restored tab must not extend a one-session suppression
  // beyond the canonical 30-day recurrence deadline.
  // Inspect both stores even when one suppression is valid, so stale
  // local preference data is not retained forever behind a fresh cookie.
  var sessionSuppressed = removeExpired(session, key);
  var cookieSuppressed = cfg.loggedIn !== true && stillSuppressed(cookieTime());
  var localSuppressed = cfg.loggedIn !== true && removeExpired(local, localKey);
  if (sessionSuppressed || cookieSuppressed || localSuppressed) { return; }
  put(session, key, now);
  var previousFocus = document.activeElement || null;
  host.hidden = false;
  // File25 owns the nonfocusable live status markup. Announce the existing
  // heading once AFTER the region becomes visible, without moving focus.
  var notice = host.querySelector && host.querySelector('[data-sabri-welcome-announcement]');
  var heading = host.querySelector && host.querySelector('#sabri-welcome-title');
  function announceOnce() {
    if (host.hidden || !notice || !heading || notice.textContent) { return; }
    notice.textContent = String(heading.textContent || '').trim();
  }
  if (typeof window.setTimeout === 'function') { window.setTimeout(announceOnce, 0); }
  else { announceOnce(); }
  // This is a non-modal region, not a dialog. Do not steal keyboard or
  // screen-reader focus as the user reads/interacts with the underlying page.
  var closed = false;
  function dismiss() {
    if (closed) { return; }
    closed = true;
    var restoreFocus = previousFocus && typeof previousFocus.focus === 'function' &&
      (!host.contains || host.contains(document.activeElement));
    host.hidden = true;
    if (restoreFocus) { try { previousFocus.focus({ preventScroll: true }); } catch (_) { previousFocus.focus(); } }
    // The governing 30-day window begins at the user's actual dismissal,
    // not when the page first loaded (which may have been days earlier).
    var dismissedAt = Math.floor(Date.now() / 1000);
    put(session, key, dismissedAt);
    // Do not leak an authenticated account dismissal into shared guest storage.
    if (cfg.loggedIn !== true) {
      put(local, localKey, dismissedAt);
      document.cookie = cfg.cookieName + '=' + dismissedAt + '; Path=' + cookiePath + '; Max-Age=' + period + '; SameSite=Lax' +
        (window.location.protocol === 'https:' ? '; Secure' : '');
    }
    if (cfg.loggedIn !== true || !cfg.endpoint || !cfg.nonce || typeof window.fetch !== 'function') { return; }
    // Server user-meta is authoritative across devices. A transient network
    // failure must not be silently treated as a persisted 30-day preference.
    // Retry at most once; 4xx authorization failures must never be replayed.
    function savePreference(attempt) {
      try {
        window.fetch(cfg.endpoint, {
          method: 'POST', credentials: 'same-origin', keepalive: true,
          headers: { 'X-WP-Nonce': cfg.nonce, 'Content-Type': 'application/json' },
          body: '{}'
        }).then(function (response) {
          if (response && response.ok) { return; }
          if (attempt === 0 && response && Number(response.status) >= 500 && typeof window.setTimeout === 'function') {
            window.setTimeout(function () { savePreference(1); }, 1000);
          }
        }).catch(function () {
          if (attempt === 0 && typeof window.setTimeout === 'function') {
            window.setTimeout(function () { savePreference(1); }, 1000);
          }
        });
      } catch (_) {
        if (attempt === 0 && typeof window.setTimeout === 'function') {
          window.setTimeout(function () { savePreference(1); }, 1000);
        }
      }
    }
    savePreference(0);
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