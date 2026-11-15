/* Genuine WordPress locale -> File20 approved copy -> File25 RTL component.
 * Runs only against ephemeral CI WordPress; no live WordPress is accessed.
 */
'use strict';
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const AxeBuilder = require('@axe-core/playwright').default;

const locale = process.env.SABRI_E2E_LOCALE;
const base = process.env.SABRI_WP_TEST_URL || 'http://127.0.0.1:8888/';
const lang = {
  ur: {
    title: 'صابری سوشل ہومیوپیتھی پلیٹ فارم میں خوش آمدید',
    skip: 'چھوڑ دیں', close: 'بند کریں', continue: 'پلیٹ فارم دیکھیں'
  },
  ar: {
    title: 'مرحبًا بك في منصة صبري الاجتماعية للمعالجة المثلية',
    skip: 'تخطَّ', close: 'إغلاق', continue: 'استكشف المنصة'
  }
}[locale];
if (!lang) throw Error('Set SABRI_E2E_LOCALE=ur or ar explicitly.');

(async () => {
  const browser = await chromium.launch({ headless: true });
  try {
    const context = await browser.newContext({
      viewport: { width: 360, height: 800 },
      reducedMotion: 'reduce'
    });
    const page = await context.newPage();
    const response = await page.goto(base, { waitUntil: 'domcontentloaded' });
    assert.ok(response && response.status() === 200, 'Canonical CI homepage must be HTTP 200.');
    const panel = page.locator('#sabri-shell-welcome');
    await panel.waitFor({ state: 'visible', timeout: 10000 });
    const region = page.locator('.sabri-ui-welcome-panel');
    assert.equal(await region.getAttribute('lang'), locale);
    assert.equal(await region.getAttribute('dir'), 'rtl');
    const title = page.locator('#sabri-welcome-title');
    assert.equal(await title.textContent(), lang.title);
    assert.equal(await title.getAttribute('dir'), 'auto');
    assert.equal(await title.evaluate(el => getComputedStyle(el).direction), 'rtl');
    assert.equal((await page.locator('[data-sabri-welcome-skip]').textContent()).trim(), lang.skip);
    assert.equal((await page.locator('[data-sabri-welcome-dismiss]').textContent()).trim(), lang.close);
    assert.equal((await page.locator('[data-sabri-welcome-continue]').textContent()).trim(), lang.continue);
    const rect = await panel.evaluate(el => el.getBoundingClientRect().toJSON());
    assert.ok(rect.x >= -1 && rect.right <= 361, 'Localized panel must remain within 360px viewport.');
    const results = await new AxeBuilder({ page }).include('#sabri-shell-welcome')
      .withTags(['wcag2a','wcag2aa','wcag21a','wcag21aa','wcag22aa']).analyze();
    assert.equal(results.violations.length, 0, 'Localized WCAG 2.2 violations: ' +
      results.violations.map(x => x.id + ':' + x.help).join('; '));
    await page.screenshot({
      path: process.env.SABRI_CI_SCREENSHOT || '/tmp/sabri-welcome-' + locale + '.png',
      fullPage: true
    });
    await page.locator('[data-sabri-welcome-skip]').click();
    assert.equal(await panel.evaluate(el => el.hidden), true);
    await context.close();
    console.log('PASS real WordPress ' + locale + ' -> File20 approved copy -> File25 RTL + axe WCAG 2.2 + Skip.');
  } finally { await browser.close(); }
})().catch(error => { console.error(error.stack || error); process.exitCode = 1; });
