/* Executable browser-state regressions for the File 20 V12.3 owner. */
'use strict';
const assert = require('node:assert/strict');
const controller = require('../assets/js/welcome-experience.js');

const KEY = 'sabri-shell-welcome-dismiss-until-v1';
const DAY_MS = 86400000;
function store() {
  const items = new Map();
  return {
    getItem(k) { return items.has(k) ? items.get(k) : null; },
    setItem(k, v) { items.set(k, String(v)); },
    removeItem(k) { items.delete(k); }
  };
}
function spyPanel() {
  const callbacks = {};
  const btn = (selector) => ({
    addEventListener(name, fn) { callbacks[selector + ':' + name] = fn; }
  });
  const panel = {
    hidden: true,
    contains(n) { return !!n && n.inPanel === true; },
    querySelectorAll() {
      return [btn('dismiss'), btn('skip')];
    },
    querySelector() { return btn('continue'); }
  };
  return { panel, callbacks };
}
function environment() {
  const localStorage = store();
  const sessionStorage = store();
  const events = {};
  const keyEvents = {};
  const { panel, callbacks } = spyPanel();
  const outsideFocus = { inPanel: false, focus() {} };
  const doc = {
    activeElement: outsideFocus,
    getElementById(id) { return id === 'sabri-shell-welcome' ? panel : null; },
    addEventListener(name, fn) { keyEvents[name] = fn; },
    contains(node) { return node === outsideFocus; }
  };
  const win = { localStorage, sessionStorage, addEventListener(name, fn) { events[name] = fn; } };
  return { localStorage, sessionStorage, events, keyEvents, callbacks, panel, doc, win };
}
function test(name, fn) {
  try { fn(); process.stdout.write('PASS ' + name + '\n'); }
  catch (error) { process.stderr.write('FAIL ' + name + ': ' + error.stack + '\n'); process.exitCode = 1; }
}
test('First eligible browser with storage can display without tracking', () => {
  const a = environment();
  assert.equal(controller.canAutoShow(a.localStorage, a.sessionStorage, KEY, 2000000000000), true);
});
test('No storage, blocked storage or malfunction always fail closed', () => {
  const s = store();
  const broken = { getItem() { throw Error('blocked'); }, setItem() { throw Error('blocked'); }, removeItem() {} };
  assert.equal(controller.canAutoShow(null, s, KEY, Date.now()), false);
  assert.equal(controller.canAutoShow(broken, s, KEY, Date.now()), false);
  assert.equal(controller.canAutoShow(s, broken, KEY, Date.now()), false);
});
test('Skip or Close prevents display for 30 complete days', () => {
  const a = environment();
  const now = 2000000000000;
  assert.equal(controller.dismissUntil(a.localStorage, a.sessionStorage, KEY, now, 1), true);
  assert.equal(a.localStorage.getItem(KEY), String(now + 30 * DAY_MS));
  a.sessionStorage.removeItem(KEY + ':seen');
  assert.equal(controller.canAutoShow(a.localStorage, a.sessionStorage, KEY, now + 29 * DAY_MS), false);
  assert.equal(controller.canAutoShow(a.localStorage, a.sessionStorage, KEY, now + 30 * DAY_MS), true);
});
test('Refresh and same-session navigation do not repeat welcome', () => {
  const a = environment();
  a.sessionStorage.setItem(KEY + ':seen', '1');
  assert.equal(controller.canAutoShow(a.localStorage, a.sessionStorage, KEY, Date.now()), false);
});
test('Corrupt preferences are suppressed, not treated as new devices', () => {
  const a = environment();
  a.localStorage.setItem(KEY, '<script>malformed</script>');
  assert.equal(controller.canAutoShow(a.localStorage, a.sessionStorage, KEY, Date.now()), false);
});
test('DOM controller shows non-modal panel and closes immediately on Skip', () => {
  const a = environment();
  const c = controller.createController(a.win, a.doc, { storageKey: KEY, days: 30 });
  assert.equal(c.shown(), true);
  assert.equal(a.panel.hidden, false);
  assert.equal(a.sessionStorage.getItem(KEY + ':seen'), '1');
  a.callbacks['skip:click']();
  assert.equal(c.shown(), false);
  assert.equal(a.panel.hidden, true);
  assert.ok(Number(a.localStorage.getItem(KEY)) > Date.now());
});
test('Escape immediately closes and writes the same suppression', () => {
  const a = environment();
  const c = controller.createController(a.win, a.doc, { storageKey: KEY, days: 30 });
  let prevented = false;
  a.keyEvents.keydown({ key: 'Escape', preventDefault() { prevented = true; } });
  assert.equal(prevented, true);
  assert.equal(c.shown(), false);
  assert.equal(a.panel.hidden, true);
});
test('Cross-tab dismissal closes a visible panel', () => {
  const a = environment();
  const c = controller.createController(a.win, a.doc, { storageKey: KEY, days: 30 });
  a.localStorage.setItem(KEY, String(Date.now() + 30 * DAY_MS));
  a.events.storage({ key: KEY });
  assert.equal(c.shown(), false);
  assert.equal(a.panel.hidden, true);
});
test('Closing from a focused Skip control restores keyboard focus to the main region', () => {
  const a = environment();
  let mainFocused = false;
  const main = {
    attrs: {},
    hasAttribute(k) { return Object.prototype.hasOwnProperty.call(this.attrs, k); },
    setAttribute(k, v) { this.attrs[k] = v; },
    focus() { mainFocused = true; }
  };
  a.doc.body = a.doc.activeElement;
  a.doc.querySelector = () => main;
  const c = controller.createController(a.win, a.doc, { storageKey: KEY, days: 30 });
  a.doc.activeElement = { inPanel: true };
  a.callbacks['skip:click']();
  assert.equal(c.shown(), false);
  assert.equal(main.attrs.tabindex, '-1');
  assert.equal(mainFocused, true);
});
test('Manual welcome remains usable without localStorage and never changes recurrence', () => {
  const a = environment();
  Object.defineProperty(a.win, 'localStorage', { get() { throw Error('blocked'); } });
  Object.defineProperty(a.win, 'sessionStorage', { get() { throw Error('blocked'); } });
  const c = controller.createController(a.win, a.doc, { storageKey: KEY, manual: true, days: 30 });
  assert.equal(c.shown(), true);
  a.callbacks['dismiss:click']();
  assert.equal(c.shown(), false);
  assert.equal(a.panel.hidden, true);
});
test('Controller fails closed on invalid binding or absent panel', () => {
  const a = environment();
  assert.equal(controller.createController(a.win, a.doc, { storageKey: 'bad key?' }), null);
  a.doc.getElementById = () => null;
  assert.equal(controller.createController(a.win, a.doc, { storageKey: KEY }), null);
});
test('Storage failure still exposes ordinary underlying content', () => {
  const a = environment();
  Object.defineProperty(a.win, 'localStorage', { get() { throw new Error('privacy mode'); } });
  try { controller.createController(a.win, a.doc, { storageKey: KEY }); } catch (_) { a.panel.hidden = true; }
  assert.equal(a.panel.hidden, true);
});
if (!process.exitCode) process.stdout.write('File20 V12.3 welcome JS behavior: 12 cases passed\n');
