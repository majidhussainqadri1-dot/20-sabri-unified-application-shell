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
    try {
      const probe = 'sabri-welcome-storage-check';
      storage.setItem(probe, '1');
      if (storage.getItem(probe) !== '1') return false;
      storage.removeItem(probe);
      return true;
    } catch (_) {
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

    const key = configuration.storageKey;
    const local = win.localStorage;
    const session = win.sessionStorage;
    const clock = () => Date.now();
    let shown = false;
    let priorFocus = null;

    function hide() {
      panel.hidden = true;
      shown = false;
      const focused = doc.activeElement;
      if (focused && panel.contains(focused) && priorFocus &&
          typeof priorFocus.focus === 'function' && doc.contains(priorFocus)) {
        priorFocus.focus();
      }
    }

    function dismiss() {
      if (!shown) return;
      dismissUntil(local, session, key, clock(), configuration.days);
      hide();
    }

    if (!canAutoShow(local, session, key, clock())) {
      panel.hidden = true;
      return { shown: () => false, dismiss, hide };
    }

    try {
      session.setItem(key + ':seen', '1');
    } catch (_) {
      panel.hidden = true;
      return { shown: () => false, dismiss, hide };
    }

    priorFocus = doc.activeElement;
    panel.hidden = false;
    shown = true;
    // Non-modal: neither focus theft nor a focus trap; no delayed animation
    // is required to access the page or dismiss the banner.
    const closeButtons = panel.querySelectorAll('[data-sabri-welcome-dismiss], [data-sabri-welcome-skip]');
    closeButtons.forEach(button => button.addEventListener('click', dismiss));
    const continueLink = panel.querySelector('[data-sabri-welcome-continue]');
    if (continueLink) continueLink.addEventListener('click', dismiss);

    doc.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && shown) {
        event.preventDefault();
        dismiss();
      }
    });
    win.addEventListener('storage', function (event) {
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
