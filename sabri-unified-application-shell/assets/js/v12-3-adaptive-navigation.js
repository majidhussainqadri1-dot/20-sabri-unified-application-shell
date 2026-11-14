(function () {
  'use strict';
  var PRIMARY_KEYS = [
    'home', 'news', 'founder', 'learn', 'encyclopedia', 'doctors',
    'clinic', 'video_wall', 'reels', 'pdf_library', 'radar', 'ai',
    'network', 'marketplace'
  ];

  /**
   * Deterministic, no-data scheduling of existing same-page nav entries.
   * Real DOM measurements are separate from this pure/testable policy.
   */
  function selectDirect(items, available, reservedMore, mobile, gap) {
    var safeWidth = Math.max(0, Number(available) || 0);
    var itemGap = typeof gap === 'number' && Number.isFinite(gap) ? Math.max(0, gap) : 4;
    var sum = items.reduce(function (v, item) { return v + Math.max(0, Number(item.width) || 0); }, 0);
    sum += itemGap * Math.max(0, items.length - 1);
    if (!mobile && sum <= safeWidth) {
      return items.map(function (item) { return item.key; });
    }
    var budget = Math.max(0, safeWidth - Math.max(44, reservedMore || 0) - itemGap - 8);
    var chosen = [];
    var used = 0;
    var candidates = mobile
      ? items.filter(function (item) { return item.key === 'home' || item.key === 'learn'; })
      : items;
    candidates.forEach(function (item) {
      var width = Math.max(0, Number(item.width) || 0);
      var required = width + (chosen.length ? itemGap : 0);
      if (width && used + required <= budget) {
        chosen.push(item.key);
        used += required;
      }
    });
    return chosen;
  }

  if (typeof module === 'object' && module.exports) {
    module.exports = { selectDirect: selectDirect, PRIMARY_KEYS: PRIMARY_KEYS };
  }
  if (typeof document === 'undefined' || typeof window === 'undefined') { return; }

  function setup(nav) {
    if (!nav || nav.getAttribute('data-sabri-v12-adaptive') === '1') { return; }
    var outer = nav.querySelector('ul');
    if (!outer) { return; }
    var more = outer.querySelector('.sabri-shell-nav-more');
    if (!more || !more.querySelector('summary')) { return; }
    var moreMenu = more.querySelector('.sabri-shell-nav-more-menu');
    if (!moreMenu) { return; }
    var summary = more.querySelector('summary');
    var defaultText = summary.textContent.trim();
    var original = Array.prototype.slice.call(outer.children)
      .filter(function (el) { return el !== more; })
      .concat(Array.prototype.slice.call(moreMenu.children));
    // No other nav or sidebars are touched. This is a single global registry.
    if (!original.length || original.some(function (li) {
      return !li.getAttribute('data-sabri-destination') ||
        PRIMARY_KEYS.indexOf(li.getAttribute('data-sabri-destination')) < 0;
    })) { return; }
    nav.setAttribute('data-sabri-v12-adaptive', '1');

    function measure() {
      var meter = document.createElement('ul');
      meter.setAttribute('aria-hidden', 'true');
      meter.className = 'sabri-v12-nav-meter';
      nav.appendChild(meter);
      var entries = original.map(function (li) {
        var clone = li.cloneNode(true);
        meter.appendChild(clone);
        return {key: li.getAttribute('data-sabri-destination'), width: Math.ceil(clone.getBoundingClientRect().width)};
      });
      var moreClone = more.cloneNode(true);
      var nested = moreClone.querySelector('.sabri-shell-nav-more-menu');
      if (nested) { nested.textContent = ''; }
      moreClone.removeAttribute('hidden');
      meter.appendChild(moreClone);
      var moreWidth = Math.max(44, Math.ceil(moreClone.getBoundingClientRect().width));
      meter.remove();
      return {entries: entries, moreWidth: moreWidth};
    }

    function reflow() {
      // Never move a focused destination or reparent an opened dropdown.
      if (nav.contains(document.activeElement) && document.activeElement !== nav) { return; }
      if (more.querySelector('details') && more.querySelector('details').open) { return; }
      var width = outer.clientWidth;
      if (!width) { return; }
      var styles = window.getComputedStyle(outer);
      var padding = parseFloat(styles.paddingInlineStart) + parseFloat(styles.paddingInlineEnd);
      if (!Number.isFinite(padding)) { padding = 36; }
      var available = Math.max(0, width - padding - 4);
      var measured = measure();
      var gap = parseFloat(styles.columnGap || styles.gap);
      var directKeys = selectDirect(measured.entries, available, measured.moreWidth, nav.clientWidth < 768, Number.isFinite(gap) ? gap : 4);
      var desired = {};
      directKeys.forEach(function (key) { desired[key] = true; });
      var visible = document.createDocumentFragment();
      var hidden = document.createDocumentFragment();
      var hiddenActiveName = '';
      original.forEach(function (li) {
        var key = li.getAttribute('data-sabri-destination');
        if (desired[key]) { visible.appendChild(li); }
        else {
          hidden.appendChild(li);
          var a = li.querySelector('a[aria-current="page"]');
          if (a) { hiddenActiveName = a.textContent.trim(); }
        }
      });
      var hiddenCount = original.length - directKeys.length;
      moreMenu.appendChild(hidden);
      // Remove any former direct items and return the canonical list order.
      outer.appendChild(visible);
      outer.appendChild(more);
      more.hidden = hiddenCount === 0;
      summary.textContent = hiddenActiveName ? defaultText + ' · ' + hiddenActiveName : defaultText;
      summary.setAttribute('aria-label', hiddenActiveName ? defaultText + ' · ' + hiddenActiveName : defaultText);
      summary.setAttribute('title', hiddenActiveName ? hiddenActiveName : defaultText);
      if (hiddenActiveName) { summary.setAttribute('aria-current', 'page'); }
      else { summary.removeAttribute('aria-current'); }
    }

    var queued = false;
    function requestReflow() {
      if (queued) { return; }
      queued = true;
      window.requestAnimationFrame(function () {
        queued = false;
        reflow();
      });
    }
    window.addEventListener('resize', requestReflow, {passive: true});
    nav.addEventListener('focusout', requestReflow);
    var details = more.querySelector('details');
    if (details) { details.addEventListener('toggle', requestReflow); }
    if (typeof window.ResizeObserver === 'function') {
      var resizeObserver = new window.ResizeObserver(requestReflow);
      resizeObserver.observe(nav);
    }
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(requestReflow);
    }
    requestReflow();
  }
  function init() {
    Array.prototype.forEach.call(document.querySelectorAll('.sabri-shell-primary-nav'), setup);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, {once: true});
  } else { init(); }
}());
