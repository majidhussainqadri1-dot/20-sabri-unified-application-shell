/* File 20 is the sole owner of welcome device state and session recurrence.
 * File 25 supplies markup. File 13 is permanently legacy-only.
 * No cookies, tracking identifiers, network requests, autoplay, or timers.
 */
(function (root) {
  'use strict';

  const DAY_MS = 24 * 60 * 60 * 1000;

  function isStorageUsable(storage) {
    if (!storage || typeof storage.getItem !== 'function' || typeof storage.setItem !== 'function' ||
        typeof storage.removeItem !== 'function') return false;
    const probe = 'sabri-welcome-storage-check';
    let prior = null;
    try {
      // Never destroy a pre-existing value owned by another script. The
      // temporary probe has no device identifier and is not transmitted.
      prior = storage.getItem(probe);
      storage.setItem(probe, '1');
      const writable = storage.getItem(probe) === '1';
      if (prior === null) storage.removeItem(probe);
      else storage.setItem(probe, prior);
      return writable;
    } catch (_) {
      try {
        if (prior === null) storage.removeItem(probe);
        else storage.setItem(probe, prior);
      } catch (ignored) {}
      return false;
    }
  }

  function canAutoShow(local, session, key, now) {
    if (!isStorageUsable(local) || !isStorageUsable(session)) return false;
    if (session.getItem(key + ':seen') !== null) return false;
    try {
      const raw = local.getItem(key);
      if (raw === null) return true;
      if (!/^\d{10,16}$/.test(raw)) return false;
      const until = Number(raw);
      return Number.isSafeInteger(until) && until > 0 && until <= now;
    } catch (_) {
      return false;
    }
  }

  function dismissUntil(local, session, key, now, days) {
    const safeDays = Math.max(30, Math.min(365, Number(days) || 30));
    const until = now + safeDays * DAY_MS;
    try {
      session.setItem(key + ':seen', '1');
      local.setItem(key, String(until));
      return true;
    } catch (_) {
      return false;
    }
  }

  function createController(win, doc, configuration) {
    const panel = doc.getElementById('sabri-shell-welcome');
    if (!panel || !configuration || typeof configuration.storageKey !== 'string' ||
        !/^[a-z0-9_-]{8,96}$/.test(configuration.storageKey)) return null;

    // URL fragments are invisible to PHP. A client-side appointment or account
    // route must not unexpectedly inherit an eligible server-rendered homepage.
    function routeStillEligible() {
      if (!win.location) return true; // Pure CI fixture without a DOM URL.
      // PHP normalizes request slugs with rawurldecode, whereas browsers
      // expose location.pathname in percent-encoded form. Decode exactly once
      // to preserve Urdu/Arabic slugs without accepting encoded separators.
      function normalizedPath(raw) {
        try {
          const encoded = String(raw || '/');
          if (/%(?:2e|2f|5c|00|0a|0d|7f)/i.test(encoded)) return null;
          const path = decodeURIComponent(encoded);
          if (path.includes('\\') || path.includes('\0') || path.includes('..') ||
              /%(?:2e|2f|5c|00|0a|0d|7f)/i.test(path)) return null;
          return path.replace(/\/+$/, '') || '/';
        } catch (_) { return null; }
      }
      const actualPath = normalizedPath(win.location.pathname);
      const serverPath = normalizedPath(configuration.serverPath || win.location.pathname);
      const queryAllowed = configuration.manual === true
        ? win.location.search === '?sabri_welcome=manual'
        : !win.location.search;
      return actualPath !== null && serverPath !== null &&
        !win.location.hash && queryAllowed && actualPath === serverPath;
    }
    if (!routeStillEligible()) {
      panel.hidden = true;
      return null;
    }
    const key = configuration.storageKey;
    const manual = configuration.manual === true;
    let local = null;
    let session = null;
    if (!manual) {
      try { local = win.localStorage; session = win.sessionStorage; }
      catch (_) { panel.hidden = true; return { shown: () => false, dismiss() {}, hide() {} }; }
    }
    const clock = () => Date.now();
    let shown = false;
    let priorFocus = null;
    let restoreHistory = () => {};

    function hide() {
      panel.hidden = true;
      shown = false;
      restoreHistory();
      const focused = doc.activeElement;
      if (focused && panel.contains(focused)) {
        let target = priorFocus && typeof priorFocus.focus === 'function' &&
          doc.contains(priorFocus) && priorFocus !== doc.body ? priorFocus : null;
        if (!target && typeof doc.querySelector === 'function') {
          target = doc.querySelector('main, [role="main"], #content, h1');
          if (target && typeof target.setAttribute === 'function' &&
              typeof target.hasAttribute === 'function' && !target.hasAttribute('tabindex')) {
            target.setAttribute('tabindex', '-1');
          }
        }
        if (target && typeof target.focus === 'function') {
          try { target.focus({ preventScroll: true }); }
          catch (_) { target.focus(); }
        }
      }
    }

    function dismiss() {
      if (!shown) return;
      if (!manual) dismissUntil(local, session, key, clock(), configuration.days);
      hide();
    }

    if (!manual && !canAutoShow(local, session, key, clock())) {
      panel.hidden = true;
      return { shown: () => false, dismiss, hide };
    }

    if (!manual) {
      // The first *shown* eligible visit starts the 30-day device interval,
      // even if the visitor navigates away without clicking a control.
      // Both session and device state must be writable before showing:
      // otherwise the promised no-repeat guarantee cannot be honored.
      if (!dismissUntil(local, session, key, clock(), configuration.days)) {
        panel.hidden = true;
        return { shown: () => false, dismiss, hide };
      }
    }

    priorFocus = doc.activeElement;
    panel.hidden = false;
    shown = true;
    // Non-modal: neither focus theft nor a focus trap; no delayed animation
    // is required to access the page or dismiss the banner.
    function suppressOnClientRouteChange() {
      if (shown && !routeStillEligible()) hide();
    }
    win.addEventListener('hashchange', suppressOnClientRouteChange);
    win.addEventListener('popstate', suppressOnClientRouteChange);
    // history.pushState/replaceState do not emit popstate. Observe only while
    // the optional intro is visible, and restore native methods on dismissal.
    // A route mutation must hide immediately, including query-only task paths.
    const history = win.history;
    if (history) {
      const wrapped = [];
      for (const method of ['pushState', 'replaceState']) {
        const original = history[method];
        if (typeof original !== 'function') continue;
        const wrapper = function (...args) {
          const result = original.apply(this, args);
          suppressOnClientRouteChange();
          return result;
        };
        try {
          history[method] = wrapper;
          if (history[method] === wrapper) wrapped.push({ method, original, wrapper });
        } catch (_) { /* Browser may expose read-only history methods. */ }
      }
      restoreHistory = function () {
        for (const { method, original, wrapper } of wrapped) {
          if (history[method] === wrapper) {
            try { history[method] = original; } catch (_) {}
          }
        }
      };
    }
    const closeButtons = panel.querySelectorAll('[data-sabri-welcome-dismiss], [data-sabri-welcome-skip]');
    closeButtons.forEach(button => button.addEventListener('click', dismiss));
    const continueLink = panel.querySelector('[data-sabri-welcome-continue]');
    if (continueLink) continueLink.addEventListener('click', function (event) {
      // Stay on the already loaded page. The manual-replay URL is different:
      // there Continue is allowed to navigate to the clean homepage.
      if (!manual && win.location && typeof continueLink.getAttribute === 'function' &&
          continueLink.getAttribute('href') === win.location.href && event &&
          typeof event.preventDefault === 'function') {
        event.preventDefault();
      }
      dismiss();
    });

    doc.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && shown) {
        event.preventDefault();
        dismiss();
      }
    });
    if (!manual) win.addEventListener('storage', function (event) {
      if (event.key === key && shown) {
        try {
          const until = Number(local.getItem(key));
          if (Number.isSafeInteger(until) && until > clock()) hide();
        } catch (_) {
          hide();
        }
      }
    });
    return { shown: () => shown, dismiss, hide };
  }

  const api = { isStorageUsable, canAutoShow, dismissUntil, createController };
  if (typeof module !== 'undefined' && module.exports) module.exports = api;

  if (root && root.document) {
    const boot = function () {
      try {
        createController(root, root.document, root.SABRI_SHELL_WELCOME);
      } catch (_) {
        const panel = root.document.getElementById('sabri-shell-welcome');
        if (panel) panel.hidden = true; // Content has never been gated.
      }
    };
    if (root.document.readyState === 'loading') {
      root.document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
      boot();
    }
  }
}(typeof window !== 'undefined' ? window : null));
