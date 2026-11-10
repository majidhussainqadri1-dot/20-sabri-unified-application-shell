'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const code = fs.readFileSync(path.resolve(__dirname, '../assets/js/context-navigation.js'), 'utf8');
function scenario(options) {
  const handlers = {}, listeners = {}, storage = {};
  const button = { hidden: false, disabled: false, addEventListener(name, cb) { handlers[name] = cb; } };
  const navigation = options.unsupported ? undefined : {
    canGoForward: !!options.canGoForward,
    currentEntry: { key: 'current' },
    entries() { return [{ key: 'current', url: 'https://example.test/a' }, { key: 'next', url: options.nextUrl || 'https://example.test/b' }]; },
    count: 0,
    forward() { this.count++; return { finished: Promise.resolve() }; },
    addEventListener(name, cb) { listeners[name] = cb; }
  };
  const window = {
    navigation,
    location: { href: 'https://example.test/a', origin: 'https://example.test', pathname: '/a', assign() { throw Error('synthetic visit'); } },
    sessionStorage: { getItem(k) { return storage[k] || null; }, setItem(k, v) { storage[k] = String(v); }, removeItem(k) { delete storage[k]; } },
    addEventListener(name, cb) { listeners['window_' + name] = cb; }
  };
  const document = { readyState: 'complete', referrer: '', querySelectorAll() { return []; }, querySelector(q) { return q === '[data-sabri-context-forward]' ? button : null; } };
  vm.runInNewContext(code, { window, document, URL }, { timeout: 1000 });
  return { navigation, button, handlers, listeners };
}
let x = scenario({ unsupported: true });
assert.equal(x.button.hidden, true);
assert.equal(x.button.disabled, true);
x = scenario({ canGoForward: false });
assert.equal(x.button.hidden, true);
x.handlers.click();
assert.equal(x.navigation.count, 0);
x.navigation.canGoForward = true;
x.listeners.currententrychange();
assert.equal(x.button.hidden, false);
x.handlers.click();
assert.equal(x.navigation.count, 1);
x = scenario({ canGoForward: true, nextUrl: 'https://outside.test/b' });
assert.equal(x.button.hidden, true);
x.handlers.click();
assert.equal(x.navigation.count, 0);
x = scenario({ canGoForward: true });
assert.equal(x.button.hidden, false);
x.navigation.canGoForward = false;
x.listeners.window_pageshow();
assert.equal(x.button.hidden, true);
console.log('Contextual native Forward dynamic regression PASS');
