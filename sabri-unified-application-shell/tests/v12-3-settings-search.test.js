'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../assets/js/v12-3-settings-hub.js'), 'utf8');
assert.equal(/(?:localStorage|sessionStorage|\bfetch\s*\(|XMLHttpRequest|sendBeacon|location\.|history\.)/.test(source), false, 'Search source never persists, transmits, or reflects query into URL');

function entry(tokens) {
  return {
    hidden: false, focused: false, tokens,
    getAttribute(name) { return name === 'data-sabri-v12-search-tokens' ? this.tokens : null; },
    focus() { this.focused = true; },
    scrollIntoView() {}
  };
}
const quiet = entry('خاموش اوقات khamosh auqat quiet hours');
const caption = entry('کیپشن caption subtitles');
const items = [quiet, caption];
const groups = ['notifications', 'display'].map((id, i) => ({
  id, hidden: false,
  getAttribute() { return this.id; },
  querySelector() { return items[i].hidden ? null : items[i]; }
}));
const links = groups.map(group => ({
  parentElement: {hidden: false},
  getAttribute() { return group.id; }
}));
const status = {textContent: ''};
const input = {
  value: '', handlers: {},
  addEventListener(type, cb) { this.handlers[type] = cb; }
};
const root = {
  ready: '0',
  getAttribute() { return this.ready; },
  setAttribute(key, value) { if (key === 'data-sabri-v12-settings-ready') this.ready = value; },
  querySelector(query) {
    if (query === '[data-sabri-v12-settings-search]') return input;
    if (query === '[data-sabri-v12-search-status]') return status;
    return null;
  },
  querySelectorAll(query) {
    if (query === '[data-sabri-v12-control-key]') return items;
    if (query === '[data-sabri-v12-group]') return groups;
    if (query === '[data-sabri-v12-group-link]') return links;
    return [];
  }
};
const document = {readyState: 'complete', querySelectorAll(query) {return query === '[data-sabri-v12-settings]' ? [root] : [];}};
vm.runInNewContext(source, {document});
assert.equal(root.ready, '1', 'UI filter attaches once');
assert.equal(status.textContent, '2 نتائج', 'Both allowed controls visible initially');
input.value = 'KHAMOSH AUQAT';
input.handlers.input();
assert.equal(quiet.hidden, false, 'Roman Urdu alias finds quiet hours');
assert.equal(caption.hidden, true, 'Nonmatching control hidden');
assert.equal(groups[1].hidden, true, 'Empty group hidden');
assert.equal(links[1].parentElement.hidden, true, 'No stale group navigation link');
let prevented = false;
input.handlers.keydown({key: 'Enter', preventDefault() {prevented=true;}});
assert.equal(prevented, true, 'Enter focuses sole result without automatic navigation');
assert.equal(quiet.focused, true, 'Accessible focus target is selected');
input.value = 'خَامُوش اوقات';
input.handlers.input();
assert.equal(quiet.hidden, false, 'Arabic diacritics normalization works in Urdu');
input.value = 'PRIVATE PATIENT RECORD';
input.handlers.input();
assert.equal(status.textContent, 'کوئی متعلقہ ترتیب دستیاب نہیں', 'No private matching metadata or fabricated fallback');
assert.equal(groups.every(g => g.hidden), true, 'Both groups suppressed on zero matches');
input.value = '';
input.handlers.input();
assert.equal(status.textContent, '2 نتائج', 'Clearing filter safely restores only rendered controls');
console.log('V12.3 client-only Settings search PASS (no private network/store/URL side effects).');
