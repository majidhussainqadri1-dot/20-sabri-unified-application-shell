'use strict';
const assert = require('node:assert/strict');
const vm = require('node:vm');
const {execFileSync} = require('node:child_process');
const path = require('node:path');

const generated = execFileSync('php', [path.join(__dirname, 'dump-v12-3-sw-cache.php')], {encoding: 'utf8'});
new vm.Script(generated);
let fetchHandler;
let cacheControl = '';
let responseType = 'basic';
let responseRedirected = false;
let responseUrlOverride = '';
let varyHeader = '';
let responseMimeOverride = '';
const recorded = [];
const self = {
  location: {origin: 'https://example.test'},
  addEventListener(type, cb) { if (type === 'fetch') fetchHandler = cb; },
  skipWaiting() {},
  clients: {claim: async () => {}}
};
const caches = {
  keys: async () => [],
  open: async () => ({
    match: async () => undefined,
    put: async (req) => {recorded.push(req.url);}
  })
};
const fetch = async (req) => ({
  ok: true, type: responseType,
  redirected: responseRedirected, url: responseUrlOverride || req.url,
  headers: {get(field) {
    field = field.toLowerCase();
    if (field === 'cache-control') return cacheControl;
    if (field === 'vary') return varyHeader;
    if (field === 'content-type') return responseMimeOverride || (req.destination === 'style' ? 'text/css; charset=utf-8' : 'application/javascript');
    return '';
  }},
  clone() {return {};}
});
const sandbox = {self,caches,fetch,URL,URLSearchParams,Response,Promise};
vm.runInNewContext(generated, sandbox, {timeout: 1000});
assert.equal(typeof fetchHandler, 'function', 'Exact generated SW registers fetch handler');

async function check(url, destination, shouldIntercept, shouldCache) {
  let promise = null;
  const req = {url,method:'GET',destination,mode:'no-cors'};
  fetchHandler({request:req,respondWith(value) {promise = value;}});
  assert.equal(Boolean(promise), shouldIntercept, url + ' interception status');
  const before = recorded.length;
  if (promise) {await promise;}
  assert.equal(recorded.length-before, shouldCache?1:0, url + ' cache write status');
}
(async () => {
  const base='https://example.test/wp-content/plugins/sabri-unified-application-shell/';
  await check(base+'assets/css/shell.css?ver=1.4.17', 'style', true, true);
  await check(base+'assets/js/shell.js?ver=1.4.17', 'script', true, true);
  await check(base+'assets/js/shell.js?ver=1.4.17', 'style', false, false);
  await check(base+'assets/css/shell.css?ver=1.4.17', 'script', false, false);
  await check(base+'assets/css/.secret.css?ver=1.4.17', 'style', false, false);
  await check(base+'assets/js/../css/shell.css?ver=1.4.17', 'script', false, false);
  await check(base+'includes/class-settings.php', 'script', false, false);
  await check(base+'assets/css/private.css?privateToken=a', 'style', false, false);
  await check(base+'assets/css/shell.css?ver=1.4.17&token=a', 'style', false, false);
  await check(base+'assets/secret.pdf', 'style', false, false);
  await check('https://evil.example/wp-content/plugins/sabri-unified-application-shell/assets/js/shell.js', 'script', false, false);
  await check(base+'assets/css/shell.css?ver=1.4.17', 'image', false, false);
  cacheControl='private, no-store';
  await check(base+'assets/css/shell.css?ver=1.4.17', 'style', true, false);
  cacheControl='';
  responseRedirected=true;
  await check(base+'assets/css/shell.css?ver=1.4.17', 'style', true, false);
  responseRedirected=false;
  responseUrlOverride='https://example.test/account/private/';
  await check(base+'assets/css/shell.css?ver=1.4.17', 'style', true, false);
  responseUrlOverride='';
  varyHeader='Accept-Encoding, Cookie';
  await check(base+'assets/css/shell.css?ver=1.4.17', 'style', true, false);
  varyHeader='';
  responseMimeOverride='text/html';
  await check(base+'assets/css/shell.css?ver=1.4.17', 'style', true, false);
  responseMimeOverride='';
  cacheControl='';
  responseType='opaque';
  await check(base+'assets/css/shell.css?ver=1.4.17', 'style', true, false);
  console.log('V12.3 PWA exact-generated SW privacy VM tests PASS; real browser/Hostinger acceptance not run.');
})().catch(error => {console.error(error);process.exitCode=1;});
