'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const code = fs.readFileSync(path.resolve(__dirname, '../assets/js/context-navigation.js'), 'utf8');
function scenario(options) {
  const handlers = {}, listeners = {}, storage = {};
  const button = { hidden: false, disabled: false, addEventListener(name, cb) { handlers[name] = cb; } };
  const back = { getAttribute(k) { return k === 'data-fallback-url' || k === 'data-home-url' ? 'https://example.test/' : null; }, addEventListener(name, cb) { handlers['back_' + name] = cb; } };
  const navigation = options.unsupported ? undefined : {
    canGoForward: !!options.canGoForward,
    canGoBack: !!options.canGoBack,
    currentEntry: { key: 'current' },
    entries() { return [{ key: 'prev', url: options.prevUrl || 'https://example.test/previous' }, { key: 'current', url: 'https://example.test/a' }, { key: 'next', url: options.nextUrl || 'https://example.test/b' }]; },
    count: 0,
    backCount: 0,
    back() { this.backCount++; return { finished: Promise.resolve() }; },
    forward() { this.count++; return { finished: Promise.resolve() }; },
    addEventListener(name, cb) { listeners[name] = cb; }
  };
  const window = {
    navigation,
    location: { href: 'https://example.test/a', origin: 'https://example.test', pathname: '/a', assign() { throw Error('synthetic visit'); } },
    sessionStorage: { getItem(k) { return storage[k] || null; }, setItem(k, v) { storage[k] = String(v); }, removeItem(k) { delete storage[k]; } },
    addEventListener(name, cb) { listeners['window_' + name] = cb; }
  };
  const document = { readyState: 'complete', referrer: '', querySelectorAll(q) { return q === '[data-sabri-context-back]' ? [back] : []; }, querySelector(q) { return q === '[data-sabri-context-forward]' ? button : null; } };
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

x = scenario({ canGoForward: false, canGoBack: true });
assert.equal(x.button.hidden, true);
x.handlers.back_click({ preventDefault() {} });
assert.equal(x.navigation.backCount, 1, 'Verified native Back preserves browser history');
x.navigation.currentEntry = { key: 'prev' };
x.navigation.canGoBack = false;
x.navigation.canGoForward = true;
x.listeners.currententrychange();
assert.equal(x.button.hidden, false, 'Native Back exposes genuine Forward');
x.handlers.click();
assert.equal(x.navigation.count, 1, 'Forward traverses the original entry');

console.log('Contextual native Back/Forward dynamic regression PASS');
