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
  function privatePath(path) { return (cfg.privatePaths || []).some(function (fragment) { return path.indexOf(fragment) !== -1; }); }
  function sameOrigin(url) { try { return new URL(url, location.href).origin === location.origin; } catch (e) { return false; } }
  function openDialog(el) { if (!el) return; if (typeof el.showModal === 'function') el.showModal(); else el.setAttribute('open', 'open'); var input = el.querySelector('input,button,[tabindex]'); if (input) setTimeout(function () { input.focus(); }, 0); }
  function closeDialog(el) { if (!el) return; if (typeof el.close === 'function') el.close(); else el.removeAttribute('open'); }

  var installPrompt = null;
  if (features.pwa_shell && 'serviceWorker' in navigator) {
    window.addEventListener('load', function () { navigator.serviceWorker.register(cfg.swUrl, { scope: '/' }).catch(function () {}); });
    window.addEventListener('beforeinstallprompt', function (event) { event.preventDefault(); installPrompt = event; document.documentElement.classList.add('sabri-shell-installable'); });
  }

  var status = document.getElementById('sabri-shell-connectivity');
  var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
  function dataSaverActive() { var prefs = safeParse('sabriShellA11yPrefs', {}); return !!(prefs.data || (connection && connection.saveData)); }
  function applyConnectivity() {
    var offline = navigator.onLine === false;
    var weak = !!(connection && (connection.saveData || /(^|-)2g$/.test(connection.effectiveType || '')));
    body.classList.toggle('sabri-shell-offline', features.offline_mode && offline);
    body.classList.toggle('sabri-shell-weak-network', features.offline_mode && !offline && weak);
    body.classList.toggle('sabri-shell-data-saver', features.data_saver && dataSaverActive());
    if (status && features.offline_mode) {
      if (offline) { status.textContent = (cfg.strings || {}).offline || 'Offline'; status.hidden = false; }
      else if (weak) { status.textContent = (cfg.strings || {}).weak || 'Weak network'; status.hidden = false; }
      else { status.hidden = true; status.textContent = ''; }
    } else if (status) { status.hidden = true; }
  }
  window.addEventListener('online', applyConnectivity); window.addEventListener('offline', applyConnectivity);
  if (connection && connection.addEventListener) connection.addEventListener('change', applyConnectivity);
  applyConnectivity();

  var recentKey = 'sabriShellRecentPublicRoutes';
  if (features.recent_resume && !privatePath(location.pathname)) {
    var recents = safeParse(recentKey, []);
    recents = recents.filter(function (item) { return item && item.url !== location.href && sameOrigin(item.url) && !privatePath(new URL(item.url).pathname); });
    recents.unshift({ url: location.href, title: document.title || location.pathname, at: Date.now() });
    safeSet(recentKey, recents.slice(0, 12));
  }
  function renderRecents() {
    var box = document.getElementById('sabri-shell-recent-list'); if (!box || !features.recent_resume) return;
    while (box.firstChild) box.removeChild(box.firstChild);
    safeParse(recentKey, []).forEach(function (item) { if (!item || !sameOrigin(item.url)) return; var a = document.createElement('a'); a.href = item.url; a.textContent = item.title || item.url; a.className = 'sabri-shell-recent-link'; box.appendChild(a); });
  }
  document.addEventListener('click', function (event) {
    var clear = event.target.closest('[data-sabri-clear-recents]'); if (clear && features.recent_resume) { localStorage.removeItem(recentKey); renderRecents(); }
    var splitClose = event.target.closest('[data-sabri-split-close]'); if (splitClose && features.split_workspace) { var split = document.getElementById('sabri-shell-split-workspace'); if (split) split.hidden = true; }
  });

  if (features.performance_guardian && 'PerformanceObserver' in window) {
    var metrics = { cls: 0, lcp: 0, longtask: 0 };
    try { new PerformanceObserver(function (list) { list.getEntries().forEach(function (e) { if (!e.hadRecentInput) metrics.cls += e.value || 0; }); }).observe({ type: 'layout-shift', buffered: true }); } catch (e) {}
    try { new PerformanceObserver(function (list) { var entries = list.getEntries(); if (entries.length) metrics.lcp = entries[entries.length - 1].startTime || 0; }).observe({ type: 'largest-contentful-paint', buffered: true }); } catch (e) {}
    try { new PerformanceObserver(function (list) { list.getEntries().forEach(function (e) { metrics.longtask += e.duration || 0; }); }).observe({ type: 'longtask', buffered: true }); } catch (e) {}
    window.addEventListener('load', function () { setTimeout(function () {
      metrics.navigation = performance.getEntriesByType('navigation')[0] ? performance.getEntriesByType('navigation')[0].duration : 0;
      safeSet('sabriShellPerformanceLatest', metrics);
      var poor = metrics.cls > 0.25 || metrics.lcp > 4000 || metrics.navigation > 6000;
      body.classList.toggle('sabri-shell-performance-poor', poor);
      window.dispatchEvent(new CustomEvent('sabri:shell-performance', { detail: metrics }));
    }, 1000); });
  }

  var pinnedKey = 'sabriShellPinnedRoutes';
  function decoratePins() {
    if (!features.smart_navigation) return;
    var pinned = safeParse(pinnedKey, []);
    document.querySelectorAll('.sabri-shell-primary-nav a[href]').forEach(function (anchor) {
      if (!sameOrigin(anchor.href) || anchor.parentElement.querySelector('[data-sabri-pin]')) return;
      var b = document.createElement('button'); b.type = 'button'; b.className = 'sabri-shell-pin'; b.setAttribute('data-sabri-pin', anchor.href); b.setAttribute('aria-label', 'Pin ' + anchor.textContent.trim()); b.textContent = pinned.indexOf(anchor.href) !== -1 ? '★' : '☆'; anchor.parentElement.appendChild(b);
    });
  }
  document.addEventListener('click', function (event) {
    var pin = event.target.closest('[data-sabri-pin]'); if (!pin || !features.smart_navigation) return;
    var url = pin.getAttribute('data-sabri-pin'); var pinned = safeParse(pinnedKey, []); var idx = pinned.indexOf(url);
    if (idx === -1) pinned.unshift(url); else pinned.splice(idx, 1); pinned = pinned.slice(0, 8); safeSet(pinnedKey, pinned); pin.textContent = idx === -1 ? '★' : '☆';
  });
  decoratePins();

  var focusKey = 'sabriShellFocusMode';
  function setFocusMode(on) { if (!features.focus_mode) return; body.classList.toggle('sabri-shell-focus-mode', !!on); safeSet(focusKey, !!on); }
  if (features.focus_mode) setFocusMode(!!safeParse(focusKey, false)); else body.classList.remove('sabri-shell-focus-mode');

  var prefsKey = 'sabriShellA11yPrefs';
  function applyPrefs() {
    var p = features.accessibility_center ? safeParse(prefsKey, {}) : {};
    body.classList.toggle('sabri-shell-a11y-large', !!p.font);
    body.classList.toggle('sabri-shell-a11y-contrast', !!p.contrast);
    body.classList.toggle('sabri-shell-a11y-focus', !!p.focus);
    body.classList.toggle('sabri-shell-a11y-spacing', !!p.spacing);
    body.classList.toggle('sabri-shell-a11y-reduce-motion', !!p.motion);
    body.classList.toggle('sabri-shell-data-saver', features.data_saver && !!(p.data || (connection && connection.saveData)));
  }
  document.addEventListener('click', function (event) { var pref = event.target.closest('[data-sabri-pref]'); if (!pref || !features.accessibility_center) return; var p = safeParse(prefsKey, {}); var key = pref.getAttribute('data-sabri-pref'); p[key] = !p[key]; safeSet(prefsKey, p); applyPrefs(); });
  applyPrefs();

  var prefetched = {};
  function maybePrefetch(anchor) {
    if (!features.predictive_prefetch || dataSaverActive() || !anchor || !anchor.href || !sameOrigin(anchor.href)) return;
    var u = new URL(anchor.href); if (privatePath(u.pathname) || u.search || Object.keys(prefetched).length >= 3 || prefetched[u.href]) return;
    var link = document.createElement('link'); link.rel = 'prefetch'; link.href = u.href; link.as = 'document'; document.head.appendChild(link); prefetched[u.href] = true;
  }
  document.addEventListener('pointerenter', function (e) { maybePrefetch(e.target.closest('a[href]')); }, true);
  document.addEventListener('focusin', function (e) { maybePrefetch(e.target.closest('a[href]')); });

  var palette = document.getElementById('sabri-shell-command-palette'); var input = document.getElementById('sabri-shell-command-input'); var results = document.getElementById('sabri-shell-command-results');
  function commands() {
    var list = [
      { label: 'Home', action: function () { location.href = cfg.homeUrl || '/'; } },
      { label: 'Back', action: function () { history.back(); } }
    ];
    if (features.recent_resume) list.push({ label: 'Recent and Resume', action: function () { renderRecents(); closeDialog(palette); openDialog(document.getElementById('sabri-shell-recent-center')); } });
    if (features.accessibility_center) list.push({ label: 'Accessibility and Reading Preferences', action: function () { closeDialog(palette); openDialog(document.getElementById('sabri-shell-accessibility-center')); } });
    if (features.focus_mode) list.push({ label: body.classList.contains('sabri-shell-focus-mode') ? 'Exit Focus Mode' : 'Enter Focus Mode', action: function () { setFocusMode(!body.classList.contains('sabri-shell-focus-mode')); closeDialog(palette); } });
    if (features.pwa_shell && installPrompt) list.push({ label: 'Install Sabri Homeopathy App', action: function () { installPrompt.prompt(); installPrompt.userChoice.finally(function () { installPrompt = null; }); closeDialog(palette); } });
    var split = document.getElementById('sabri-shell-split-workspace'); if (features.split_workspace && split) list.push({ label: 'Open Secondary Workspace', action: function () { split.hidden = false; closeDialog(palette); } });
    if (features.smart_navigation) safeParse(pinnedKey, []).forEach(function (url) { if (sameOrigin(url)) list.push({ label: 'Pinned: ' + (new URL(url)).pathname, action: function () { location.href = url; } }); });
    if (cfg.search && cfg.search.url) list.push({ label: 'Search the platform…', search: true, action: function (q) { var u = new URL(cfg.search.url); u.searchParams.set(cfg.search.query_param || 'q', q || ''); location.href = u.toString(); } });
    return list;
  }
  function renderCommands(query) {
    if (!results || !features.command_palette) return; while (results.firstChild) results.removeChild(results.firstChild);
    var q = (query || '').toLowerCase(); var list = commands().filter(function (c) { return !q || c.label.toLowerCase().indexOf(q) !== -1 || c.search; });
    list.slice(0, 12).forEach(function (cmd) { var b = document.createElement('button'); b.type = 'button'; b.className = 'sabri-shell-command-item'; b.textContent = cmd.search && q ? 'Search: “' + query + '”' : cmd.label; b.addEventListener('click', function () { cmd.action(query); }); results.appendChild(b); });
  }
  if (input && features.command_palette) input.addEventListener('input', function () { renderCommands(input.value); });
  document.addEventListener('keydown', function (event) {
    if (!features.keyboard_accessibility && !features.command_palette) return;
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k' && features.command_palette) { event.preventDefault(); renderCommands(''); openDialog(palette); if (input) { input.value = ''; input.focus(); } return; }
    if (event.key === '?' && features.keyboard_accessibility && features.accessibility_center && !event.ctrlKey && !event.metaKey && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) { event.preventDefault(); openDialog(document.getElementById('sabri-shell-accessibility-center')); }
    if (event.altKey && event.key.toLowerCase() === 'h' && features.keyboard_accessibility) { event.preventDefault(); location.href = cfg.homeUrl || '/'; }
    if (event.key === 'Escape') { [palette, document.getElementById('sabri-shell-accessibility-center'), document.getElementById('sabri-shell-recent-center')].forEach(closeDialog); }
  });

  function viewportState() {
    if (!features.adaptive_foldable) return;
    var width = window.visualViewport ? window.visualViewport.width : window.innerWidth;
    body.classList.toggle('sabri-shell-ultrawide', width >= 1600);
    body.classList.toggle('sabri-shell-tablet-landscape', width >= 768 && width < 1200 && window.innerWidth > window.innerHeight);
    document.documentElement.style.setProperty('--sabri-visual-height', (window.visualViewport ? window.visualViewport.height : window.innerHeight) + 'px');
  }
  if (features.adaptive_foldable) { viewportState(); window.addEventListener('resize', viewportState); if (window.visualViewport) window.visualViewport.addEventListener('resize', viewportState); }
})();
