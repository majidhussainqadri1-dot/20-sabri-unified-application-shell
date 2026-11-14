(function () {
  'use strict';

  // Only visible, owner-gated static metadata is searchable. No storage, URL
  // mutation, network request or telemetry is issued from this control.
  function normalize(value) {
    var text = String(value || '');
    if (typeof text.normalize === 'function') {
      text = text.normalize('NFKC');
    }
    return text.toLowerCase()
      .replace(/[\u064b-\u065f\u0670\u06d6-\u06ed]/g, '')
      .replace(/\u064a/g, '\u06cc').replace(/\u0643/g, '\u06a9')
      .replace(/[\u200c\u200d]/g, ' ').replace(/\s+/g, ' ').trim();
  }

  function attach(root) {
    if (!root || root.getAttribute('data-sabri-v12-settings-ready') === '1') { return; }
    var input = root.querySelector('[data-sabri-v12-settings-search]');
    if (!input) { return; }
    root.setAttribute('data-sabri-v12-settings-ready', '1');
    var entries = Array.prototype.slice.call(root.querySelectorAll('[data-sabri-v12-control-key]'));
    var groups = Array.prototype.slice.call(root.querySelectorAll('[data-sabri-v12-group]'));
    var status = root.querySelector('[data-sabri-v12-search-status]');
    var links = Array.prototype.slice.call(root.querySelectorAll('[data-sabri-v12-group-link]'));
    var matches = entries.slice();

    function update() {
      var query = normalize(input.value);
      matches = [];
      entries.forEach(function (entry) {
        var metadata = entry.getAttribute('data-sabri-v12-search-tokens') || '';
        var isMatch = !query || normalize(metadata).indexOf(query) !== -1;
        entry.hidden = !isMatch;
        if (isMatch) { matches.push(entry); }
      });
      groups.forEach(function (group) {
        var groupHasMatch = group.querySelector('[data-sabri-v12-control-key]:not([hidden])');
        group.hidden = !groupHasMatch;
        links.forEach(function (link) {
          if (link.getAttribute('data-sabri-v12-group-link') === group.getAttribute('data-sabri-v12-group')) {
            link.parentElement.hidden = !groupHasMatch;
          }
        });
      });
      if (status) {
        status.textContent = matches.length === 0 ? 'کوئی متعلقہ ترتیب دستیاب نہیں' : matches.length + ' نتائج';
      }
    }
    input.addEventListener('input', update);
    input.addEventListener('keydown', function (event) {
      if (event.key !== 'Enter' || matches.length !== 1) { return; }
      event.preventDefault();
      matches[0].focus();
      if (typeof matches[0].scrollIntoView === 'function') {
        matches[0].scrollIntoView({ block: 'nearest' });
      }
    });
    update();
  }
  function init() {
    Array.prototype.forEach.call(document.querySelectorAll('[data-sabri-v12-settings]'), attach);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
}());
