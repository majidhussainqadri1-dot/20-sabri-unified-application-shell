'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {selectDirect, PRIMARY_KEYS} = require('../assets/js/v12-3-adaptive-navigation.js');
const originalKeys = [
  'home','news','founder','learn','encyclopedia','doctors',
  'clinic','video_wall','reels','pdf_library','radar','ai',
  'network','marketplace'
];
assert.deepEqual(PRIMARY_KEYS, originalKeys, 'Only V12.3 14 core nav destinations; no invented fifteenth');
const items = PRIMARY_KEYS.map(key => ({key, width: 80}));
assert.deepEqual(selectDirect(items, 1800, 72, false), originalKeys, 'Full-width desktop shows all available menu destinations');
assert.ok(selectDirect(items, 1120, 72, false, 4).length < 14, '14×80 plus CSS gaps is NOT falsely considered to fit in 1120px');
assert.deepEqual(selectDirect(items, 1172, 72, false, 4), originalKeys, 'Entire menu fits when exact item widths plus 13 gaps fit');
const desktop = selectDirect(items, 430, 72, false);
assert.equal(desktop.length, 4, 'Width-fit desktop moves rest into More without horizontal scroll');
assert.deepEqual(desktop, originalKeys.slice(0, 4), 'Original registered navigation order remains stable');
assert.deepEqual(selectDirect(items, 280, 72, true), ['home', 'learn'], 'Mobile Home + Education / Learn remain direct, rest via More');
assert.deepEqual(selectDirect(items, 120, 72, true), [], 'Very narrow layout never overflows; More remains universal fallback');
assert.deepEqual(selectDirect(items, 0, 72, true), [], 'Unknown width fails into accessible default dropdown rather than falsely fitting');
assert.deepEqual(selectDirect(items, 1000, 72, true), ['home', 'learn'], 'Mobile limit never becomes giant duplicated global menu');
assert.equal(new Set(desktop.concat(originalKeys.filter(k=>!desktop.includes(k)))).size, 14, 'No route lost after partition');
const src = fs.readFileSync(path.join(__dirname, '../assets/js/v12-3-adaptive-navigation.js'), 'utf8');
assert.equal(/(?:localStorage|sessionStorage|XMLHttpRequest|sendBeacon|document\.cookie|\bfetch\s*\()/.test(src), false, 'Navigation adds no storage/telemetry/network side-effects');
assert.equal(src.includes("nav.contains(document.activeElement)"), true, 'Focused destination protected against reparenting');
assert.equal(src.includes("moreMenu.appendChild(hidden)"), true, 'Original nodes moved without duplicate anchors');
assert.equal(src.includes("summary.setAttribute('aria-current', 'page')"), true, 'More retains active state semantics');
assert.equal(src.includes("summary.setAttribute('aria-label'"), true, 'Full active destination remains available to assistive navigation despite visual truncation');
const css = fs.readFileSync(path.join(__dirname, '../assets/css/v12-3-adaptive-navigation.css'), 'utf8');
assert.equal(css.includes('clip-path: inset(50%)'), true, 'Narrow-screen brand remains accessible when visually clipped.');
assert.equal(css.includes('.sabri-shell-header-inner .sabri-shell-brand-text { display: none; }'), false, 'No display:none on brand accessible name.');
assert.equal(css.includes('.sabri-shell-nav-more-menu'), true, 'Native More dropdown has a styled presentation for JS and no-JS.');
console.log('V12.3 adaptive navigation policy PASS; real device a11y acceptance not claimed.');
