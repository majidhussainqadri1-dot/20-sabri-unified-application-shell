(function () {
  'use strict';

  function editable(target) {
    if (!target) return false;
    if (/INPUT|TEXTAREA|SELECT/.test(target.tagName || '')) return true;
    return !!(target.isContentEditable || (target.closest && target.closest('[contenteditable="true"],[contenteditable=""]')));
  }

  /*
   * This listener is a dependency of future-shell-v5.js, so it is registered
   * first at document bubble phase. Target/editor handlers have already run by
   * this point; stopImmediatePropagation only prevents the later File 20 global
   * Ctrl/Cmd+K listener from hijacking an editable context.
   */
  document.addEventListener('keydown', function (event) {
    if (!(event.ctrlKey || event.metaKey) || String(event.key || '').toLowerCase() !== 'k') return;
    if (!editable(event.target || document.activeElement)) return;
    event.stopImmediatePropagation();
  });
})();
