(function () {
  'use strict';
  var cfg = window.SabriShellFutureV5 || {};
  var features = cfg.features || {};
  var body = document.body;
  if (!body) return;

  function safeParse(key, fallback) {
    try { var v = JSON.parse(localStorage.getItem(key)); return v === null ? fallback : v; } catch (e) { return fallback; }
  }
  function safeSet(key, value) { try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) {} }
  function normalizePath(path) {
    path = String(path || '/').split('?')[0].split('#')[0];
    if (path.charAt(0) !== '/') path = '/' + path;
    if (path.length > 1) path = path.replace(/\/+$/, '');
    return path.toLowerCase();
  }
  function privatePath(path) {
    var candidate = normalizePath(path);
    return (cfg.privatePaths || []).some(function (fragment) {
      var prefix = normalizePath(fragment);
      return candidate === prefix || candidate.indexOf(prefix + '/') === 0;
    });
  }
  function sameOrigin(url) { try { return new URL(url, location.href).origin === location.origin; } catch (e) { return false; } }
  function canonicalLocalUrl(url) {
    try {
      var u = new URL(url, location.href);
      if (u.origin !== location.origin || u.search || privatePath(u.pathname)) return '';
      u.hash = '';
      return u.origin + u.pathname;
    } catch (e) { return ''; }
  }
  function publicAnchorUrl(anchor) {
    if (!anchor || !anchor.href || !sameOrigin(anchor.href)) return '';
    if (!anchor.closest('.sabri-shell-primary-nav') && anchor.getAttribute('data-sabri-public-route') !== '1') return '';
    return canonicalLocalUrl(anchor.href);
  }
  function isEditableTarget(target) {
    if (!target) return false;
    if (/INPUT|TEXTAREA|SELECT/.test(target.tagName || '')) return true;
    return !!(target.isContentEditable || (target.closest && target.closest('[contenteditable="true"],[contenteditable=""]')));
  }

  var dialogReturnFocus = typeof WeakMap === 'function' ? new WeakMap() : null;
  function openDialog(el) {
    if (!el) return;
    if (dialogReturnFocus) dialogReturnFocus.set(el, document.activeElement);
    if (typeof el.showModal === 'function') el.showModal(); else el.setAttribute('open', 'open');
    var input = el.querySelector('input,button,[tabindex]');
    if (input) setTimeout(function () { input.focus(); }, 0);
  }
  function restoreDialogFocus(el) {
    if (!el || !dialogReturnFocus) return;
    var previous = dialogReturnFocus.get(el);
    dialogReturnFocus.delete(el);
    if (previous && previous !== document.body && document.contains(previous) && typeof previous.focus === 'function') {
      setTimeout(function () { previous.focus(); }, 0);
    }
  }
  function closeDialog(el) {
    if (!el) return;
    if (typeof el.close === 'function' && el.open) el.close();
    else { el.removeAttribute('open'); restoreDialogFocus(el); }
  }
  Array.prototype.forEach.call(document.querySelectorAll('dialog'), function (dialog) {
    dialog.addEventListener('close', function () { restoreDialogFocus(dialog); });
    dialog.addEventListener('cancel', function () { setTimeout(function () { restoreDialogFocus(dialog); }, 0); });
  });
  function safeBack() {
    var back = document.querySelector('[data-sabri-context-back]');
    if (back && back.href && sameOrigin(back.href)) { back.click(); }
    else { location.href = cfg.homeUrl || '/'; }
  }

  var installPrompt = null;
  if ('serviceWorker' in navigator) {
    if (features.pwa_shell) {
      window.addEventListener('load', function () {
        var scope = cfg.swScope || '/';
        try { scope = new URL(scope, cfg.homeUrl || location.href).pathname || '/'; } catch (e) {}
        navigator.serviceWorker.register(cfg.swUrl, { scope: scope }).catch(function () {});
      });
      window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        installPrompt = event;
        document.documentElement.classList.add('sabri-shell-installable');
      });
    } else {
      document.documentElement.classList.remove('sabri-shell-installable');
      navigator.serviceWorker.getRegistrations().then(function (registrations) {
        var target = '';
        try { target = new URL(cfg.swUrl || '', location.href).href; } catch (e) {}
        registrations.forEach(function (registration) {
          var worker = registration.active || registration.waiting || registration.installing;
          if (worker && target && worker.scriptURL === target) registration.unregister().catch(function () {});
        });
      }).catch(function () {});
    }
  }

  var status = document.getElementById('sabri-shell-connectivity');
  var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
  var lastDataSaver = null;
  function dataSaverActive() {
    var prefs = safeParse('sabriShellA11yPrefs', {});
    return !!(features.data_saver && (prefs.data || (connection && connection.saveData)));
  }
  function syncDataSaver() {
    var active = dataSaverActive();
    body.classList.toggle('sabri-shell-data-saver', active);
    if (active !== lastDataSaver) {
      lastDataSaver = active;
      window.dispatchEvent(new CustomEvent('sabri:data-saver-change', { detail: { active: active } }));
    }
  }
  function applyConnectivity() {
    var offline = navigator.onLine === false;
    var weak = !!(connection && (connection.saveData || /(^|-)2g$/.test(connection.effectiveType || '')));
    body.classList.toggle('sabri-shell-offline', !!features.offline_mode && offline);
    body.classList.toggle('sabri-shell-weak-network', !!features.offline_mode && !offline && weak);
    syncDataSaver();
    if (status && features.offline_mode) {
      if (offline) { status.textContent = (cfg.strings || {}).offline || 'Offline'; status.hidden = false; }
      else if (weak) { status.textContent = (cfg.strings || {}).weak || 'Weak network'; status.hidden = false; }
      else { status.hidden = true; status.textContent = ''; }
    } else if (status) { status.hidden = true; }
  }
  window.addEventListener('online', applyConnectivity);
  window.addEventListener('offline', applyConnectivity);
  if (connection && connection.addEventListener) connection.addEventListener('change', applyConnectivity);
  applyConnectivity();

  var legacyRecentKey = 'sabriShellRecentPublicRoutes';
  var recentKey = 'sabriShellRecentPublicRoutesV' + (cfg.recentsVersion || 2);
  try { if (legacyRecentKey !== recentKey) localStorage.removeItem(legacyRecentKey); } catch (e) {}
  function cleanRecentItems(items) {
    var seen = {};
    return (Array.isArray(items) ? items : []).filter(function (item) {
      if (!item || !item.url) return false;
      var url = canonicalLocalUrl(item.url);
      if (!url || seen[url]) return false;
      seen[url] = true;
      item.url = url;
      item.title = String(item.title || url).slice(0, 240);
      item.at = Number(item.at || 0);
      return true;
    }).slice(0, 12);
  }
  if (features.recent_resume) {
    var recents = cleanRecentItems(safeParse(recentKey, []));
    if (cfg.currentRoutePublic) {
      var currentUrl = canonicalLocalUrl(location.origin + location.pathname);
      if (currentUrl) {
        recents = recents.filter(function (item) { return item.url !== currentUrl; });
        recents.unshift({ url: currentUrl, title: document.title || location.pathname, at: Date.now() });
      }
    }
    safeSet(recentKey, cleanRecentItems(recents));
  }
  function renderRecents() {
    var box = document.getElementById('sabri-shell-recent-list');
    if (!box || !features.recent_resume) return;
    while (box.firstChild) box.removeChild(box.firstChild);
    cleanRecentItems(safeParse(recentKey, [])).forEach(function (item) {
      var a = document.createElement('a');
      a.href = item.url;
      a.textContent = item.title || item.url;
      a.className = 'sabri-shell-recent-link';
      box.appendChild(a);
    });
  }

  var splitReturnFocus = null;
  function splitDesktopAllowed() { return !window.matchMedia || window.matchMedia('(min-width: 1024px)').matches; }
  function closeSplit() {
    var split = document.getElementById('sabri-shell-split-workspace');
    if (!split || split.hidden) return;
    split.hidden = true;
    if (splitReturnFocus && document.contains(splitReturnFocus) && typeof splitReturnFocus.focus === 'function') splitReturnFocus.focus();
    splitReturnFocus = null;
  }
  function openSplit() {
    var split = document.getElementById('sabri-shell-split-workspace');
    if (!split || !features.split_workspace || !splitDesktopAllowed()) return;
    splitReturnFocus = document.activeElement;
    split.hidden = false;
    var close = split.querySelector('[data-sabri-split-close]');
    if (close) close.focus();
  }
  document.addEventListener('click', function (event) {
    var clear = event.target.closest('[data-sabri-clear-recents]');
    if (clear && features.recent_resume) { try { localStorage.removeItem(recentKey); } catch (e) {} renderRecents(); }
    var splitClose = event.target.closest('[data-sabri-split-close]');
    if (splitClose && features.split_workspace) closeSplit();
  });

  if (features.performance_guardian && 'PerformanceObserver' in window) {
    var metrics = { cls: 0, lcp: 0, longtask: 0 };
    var observers = [];
    try { var clsObserver = new PerformanceObserver(function (list) { list.getEntries().forEach(function (e) { if (!e.hadRecentInput) metrics.cls += e.value || 0; }); }); clsObserver.observe({ type: 'layout-shift', buffered: true }); observers.push(clsObserver); } catch (e) {}
    try { var lcpObserver = new PerformanceObserver(function (list) { var entries = list.getEntries(); if (entries.length) metrics.lcp = entries[entries.length - 1].startTime || 0; }); lcpObserver.observe({ type: 'largest-contentful-paint', buffered: true }); observers.push(lcpObserver); } catch (e) {}
    try { var taskObserver = new PerformanceObserver(function (list) { list.getEntries().forEach(function (e) { metrics.longtask += e.duration || 0; }); }); taskObserver.observe({ type: 'longtask', buffered: true }); observers.push(taskObserver); } catch (e) {}
    window.addEventListener('load', function () {
      setTimeout(function () {
        metrics.navigation = performance.getEntriesByType('navigation')[0] ? performance.getEntriesByType('navigation')[0].duration : 0;
        safeSet('sabriShellPerformanceLatest', metrics);
        var poor = metrics.cls > 0.25 || metrics.lcp > 4000 || metrics.navigation > 6000;
        body.classList.toggle('sabri-shell-performance-poor', poor);
        window.dispatchEvent(new CustomEvent('sabri:shell-performance', { detail: metrics }));
        observers.forEach(function (observer) { try { observer.disconnect(); } catch (e) {} });
      }, 1000);
    });
  }

  var pinnedKey = 'sabriShellPinnedRoutes';
  function decoratePins() {
    if (!features.smart_navigation) return;
    var anchors = Array.prototype.slice.call(document.querySelectorAll('.sabri-shell-primary-nav a[href]'));
    var allowed = anchors.map(publicAnchorUrl).filter(Boolean);
    var pinned = safeParse(pinnedKey, []).filter(function (url) { return allowed.indexOf(url) !== -1; }).slice(0, 8);
    safeSet(pinnedKey, pinned);
    anchors.forEach(function (anchor) {
      var safeUrl = publicAnchorUrl(anchor);
      if (!safeUrl || anchor.parentElement.querySelector('[data-sabri-pin]')) return;
      var selected = pinned.indexOf(safeUrl) !== -1;
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'sabri-shell-pin';
      b.setAttribute('data-sabri-pin', safeUrl);
      b.setAttribute('aria-label', (selected ? 'Unpin ' : 'Pin ') + anchor.textContent.trim());
      b.setAttribute('aria-pressed', selected ? 'true' : 'false');
      b.textContent = selected ? '★' : '☆';
      anchor.parentElement.appendChild(b);
    });
  }
  document.addEventListener('click', function (event) {
    var pin = event.target.closest('[data-sabri-pin]');
    if (!pin || !features.smart_navigation) return;
    var url = canonicalLocalUrl(pin.getAttribute('data-sabri-pin'));
    if (!url) return;
    var pinned = safeParse(pinnedKey, []);
    var idx = pinned.indexOf(url);
    if (idx === -1) pinned.unshift(url); else pinned.splice(idx, 1);
    pinned = pinned.slice(0, 8);
    safeSet(pinnedKey, pinned);
    var selected = idx === -1;
    pin.textContent = selected ? '★' : '☆';
    pin.setAttribute('aria-pressed', selected ? 'true' : 'false');
    pin.setAttribute('aria-label', (selected ? 'Unpin ' : 'Pin ') + (pin.parentElement.querySelector('a') ? pin.parentElement.querySelector('a').textContent.trim() : 'route'));
  });
  decoratePins();

  var focusKey = 'sabriShellFocusMode';
  function setFocusMode(on) {
    if (!features.focus_mode) return;
    body.classList.toggle('sabri-shell-focus-mode', !!on);
    safeSet(focusKey, !!on);
  }
  if (features.focus_mode) setFocusMode(!!safeParse(focusKey, false)); else body.classList.remove('sabri-shell-focus-mode');

  var prefsKey = 'sabriShellA11yPrefs';
  function syncPrefButtons(p) {
    document.querySelectorAll('[data-sabri-pref]').forEach(function (button) {
      var key = button.getAttribute('data-sabri-pref');
      button.setAttribute('aria-pressed', p[key] ? 'true' : 'false');
    });
  }
  function applyPrefs() {
    var p = features.accessibility_center ? safeParse(prefsKey, {}) : {};
    body.classList.toggle('sabri-shell-a11y-large', !!p.font);
    body.classList.toggle('sabri-shell-a11y-contrast', !!p.contrast);
    body.classList.toggle('sabri-shell-a11y-focus', !!p.focus);
    body.classList.toggle('sabri-shell-a11y-spacing', !!p.spacing);
    body.classList.toggle('sabri-shell-a11y-reduce-motion', !!p.motion);
    syncPrefButtons(p);
    syncDataSaver();
  }
  document.addEventListener('click', function (event) {
    var pref = event.target.closest('[data-sabri-pref]');
    if (!pref || !features.accessibility_center) return;
    var p = safeParse(prefsKey, {});
    var key = pref.getAttribute('data-sabri-pref');
    if (key === 'data' && !features.data_saver) return;
    p[key] = !p[key];
    safeSet(prefsKey, p);
    applyPrefs();
  });
  applyPrefs();

  var prefetched = {};
  function maybePrefetch(anchor) {
    if (!features.predictive_prefetch || dataSaverActive() || !anchor || Object.keys(prefetched).length >= 3) return;
    var url = publicAnchorUrl(anchor);
    if (!url || prefetched[url]) return;
    var link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = url;
    link.as = 'document';
    document.head.appendChild(link);
    prefetched[url] = true;
  }
  document.addEventListener('pointerenter', function (e) { maybePrefetch(e.target.closest('a[href]')); }, true);
  document.addEventListener('focusin', function (e) { maybePrefetch(e.target.closest('a[href]')); });

  var palette = document.getElementById('sabri-shell-command-palette');
  var input = document.getElementById('sabri-shell-command-input');
  var results = document.getElementById('sabri-shell-command-results');
  function commands() {
    var list = [
      { label: 'Home', action: function () { location.href = cfg.homeUrl || '/'; } },
      { label: 'Back', action: safeBack }
    ];
    if (features.recent_resume) list.push({ label: 'Recent and Resume', action: function () { renderRecents(); closeDialog(palette); openDialog(document.getElementById('sabri-shell-recent-center')); } });
    if (features.accessibility_center) list.push({ label: 'Accessibility and Reading Preferences', action: function () { closeDialog(palette); openDialog(document.getElementById('sabri-shell-accessibility-center')); } });
    if (features.language_direction) list.push({ label: 'Language and Direction', action: function () { closeDialog(palette); openDialog(document.getElementById('sabri-shell-accessibility-center')); } });
    if (features.focus_mode) list.push({ label: body.classList.contains('sabri-shell-focus-mode') ? 'Exit Focus Mode' : 'Enter Focus Mode', action: function () { setFocusMode(!body.classList.contains('sabri-shell-focus-mode')); closeDialog(palette); } });
    if (features.pwa_shell && installPrompt) list.push({ label: 'Install Sabri Homeopathy App', action: function () { installPrompt.prompt(); installPrompt.userChoice.finally(function () { installPrompt = null; document.documentElement.classList.remove('sabri-shell-installable'); }); closeDialog(palette); } });
    var split = document.getElementById('sabri-shell-split-workspace');
    if (features.split_workspace && split && splitDesktopAllowed()) list.push({ label: 'Open Secondary Workspace', action: function () { closeDialog(palette); openSplit(); } });
    if (features.smart_navigation) safeParse(pinnedKey, []).forEach(function (url) {
      var safe = canonicalLocalUrl(url);
      if (safe) list.push({ label: 'Pinned: ' + (new URL(safe)).pathname, action: function () { location.href = safe; } });
    });
    if (cfg.search && cfg.search.url && sameOrigin(cfg.search.url)) list.push({ label: 'Search the platform…', search: true, action: function (q) { var u = new URL(cfg.search.url, location.href); if (u.origin !== location.origin) return; u.searchParams.set(cfg.search.query_param || 'q', q || ''); location.href = u.toString(); } });
    return list;
  }
  function renderCommands(query) {
    if (!results || !features.command_palette) return;
    while (results.firstChild) results.removeChild(results.firstChild);
    var q = (query || '').toLowerCase();
    var list = commands().filter(function (c) { return !q || c.label.toLowerCase().indexOf(q) !== -1 || c.search; });
    list.slice(0, 12).forEach(function (cmd) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'sabri-shell-command-item';
      b.textContent = cmd.search && q ? 'Search: “' + query + '”' : cmd.label;
      b.addEventListener('click', function () { cmd.action(query); });
      results.appendChild(b);
    });
  }
  if (input && features.command_palette) input.addEventListener('input', function () { renderCommands(input.value); });
  document.addEventListener('keydown', function (event) {
    if (!features.keyboard_accessibility && !features.command_palette) return;
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k' && features.command_palette) {
      event.preventDefault();
      renderCommands('');
      openDialog(palette);
      if (input) { input.value = ''; input.focus(); }
      return;
    }
    if (event.key === '?' && features.keyboard_accessibility && features.accessibility_center && !event.ctrlKey && !event.metaKey && !isEditableTarget(document.activeElement)) {
      event.preventDefault();
      openDialog(document.getElementById('sabri-shell-accessibility-center'));
    }
    if (event.altKey && event.key.toLowerCase() === 'h' && features.keyboard_accessibility && !isEditableTarget(document.activeElement)) {
      event.preventDefault();
      location.href = cfg.homeUrl || '/';
    }
    if (event.key === 'Escape') {
      [palette, document.getElementById('sabri-shell-accessibility-center'), document.getElementById('sabri-shell-recent-center')].forEach(closeDialog);
      closeSplit();
    }
  });

  function viewportState() {
    if (!features.adaptive_foldable) return;
    var width = window.visualViewport ? window.visualViewport.width : window.innerWidth;
    body.classList.toggle('sabri-shell-ultrawide', width >= 1600);
    body.classList.toggle('sabri-shell-tablet-landscape', width >= 768 && width < 1200 && window.innerWidth > window.innerHeight);
    document.documentElement.style.setProperty('--sabri-visual-height', (window.visualViewport ? window.visualViewport.height : window.innerHeight) + 'px');
    if (!splitDesktopAllowed()) closeSplit();
  }
  if (features.adaptive_foldable) {
    viewportState();
    window.addEventListener('resize', viewportState);
    if (window.visualViewport) window.visualViewport.addEventListener('resize', viewportState);
  }
})();
