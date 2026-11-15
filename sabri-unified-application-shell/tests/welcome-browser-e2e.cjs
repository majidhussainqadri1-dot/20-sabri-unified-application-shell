/* Real Chromium over a fresh ephemeral WordPress 7.1.4 + File00/20/25 CI DB.
 * Never run against production or a real person's device. No external data.
 */
'use strict';
const assert = require('node:assert/strict');
const { chromium } = require('playwright');

const base = process.env.SABRI_WP_TEST_URL || 'http://127.0.0.1:8888/';
const toMs = 30 * 86400000;
const tests = [];
async function caseOf(name, test) {
  try { await test(); tests.push(name); process.stdout.write('PASS ' + name + '\n'); }
  catch (error) { process.stderr.write('FAIL ' + name + '\n' + (error.stack || error) + '\n'); process.exitCode = 1; }
}
async function visible(page) {
  await page.waitForSelector('#sabri-shell-welcome:not([hidden])', { timeout: 10000 });
  return page.locator('#sabri-shell-welcome');
}
function storageSnapshot() {
  const keys = Object.keys(localStorage).filter(key =>
    key.startsWith('sabri-shell-welcome-dismiss-until-v1-'));
  return keys.map(key => ({ key, until: Number(localStorage.getItem(key)) }));
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  try {
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();
    await caseOf('First eligible guest visit displays nonblocking File25 welcome', async () => {
      const response = await page.goto(base, { waitUntil: 'domcontentloaded' });
      assert.ok(response && response.status() < 400, 'WordPress homepage HTTP response');
      await visible(page);
      assert.ok(await page.locator('main, [role="main"]').count(), 'Underlying content is present');
      assert.ok(await page.locator('[data-sabri-welcome-skip]').count());
      assert.ok(await page.locator('[data-sabri-welcome-dismiss]').count());
      assert.ok(await page.locator('[data-sabri-welcome-continue]').count());
      assert.equal(await page.locator('[aria-live="polite"]').count() > 0, true);
    });
    let dismissed = null;
    await caseOf('Skip hides immediately and persists a full 30 days', async () => {
      await page.locator('[data-sabri-welcome-skip]').click();
      assert.equal(await page.locator('#sabri-shell-welcome').evaluate(el => el.hidden), true);
      const stored = await page.evaluate(storageSnapshot);
      assert.equal(stored.length, 1, 'Only one site-local dismissal preference');
      assert.ok(stored[0].until >= Date.now() + toMs - 30000, '30 complete days');
      dismissed = stored[0];
    });
    await caseOf('Reload/new tab do not repeat while the device preference is active', async () => {
      await page.reload({ waitUntil: 'domcontentloaded' });
      assert.equal(await page.locator('#sabri-shell-welcome').evaluate(el => el.hidden), true);
      const nextTab = await context.newPage();
      await nextTab.goto(base, { waitUntil: 'domcontentloaded' });
      assert.equal(await nextTab.locator('#sabri-shell-welcome').evaluate(el => el.hidden), true);
      await nextTab.close();
    });
    await caseOf('Manual replay works without resetting 30-day preference', async () => {
      await page.goto(base + '?sabri_welcome=manual', { waitUntil: 'domcontentloaded' });
      await visible(page);
      await page.locator('[data-sabri-welcome-dismiss]').click();
      assert.equal(await page.locator('#sabri-shell-welcome').evaluate(el => el.hidden), true);
      const stored = await page.evaluate(storageSnapshot);
      assert.deepEqual(stored, [dismissed]);
    });
    await context.close();

    const escContext = await browser.newContext();
    const esc = await escContext.newPage();
    await caseOf('Escape closes fresh-device introduction and suppresses recurrence', async () => {
      await esc.goto(base, { waitUntil: 'domcontentloaded' });
      await visible(esc);
      await esc.keyboard.press('Escape');
      assert.equal(await esc.locator('#sabri-shell-welcome').evaluate(el => el.hidden), true);
      assert.equal((await esc.evaluate(storageSnapshot)).length, 1);
    });
    await escContext.close();

    const hashContext = await browser.newContext();
    const hashPage = await hashContext.newPage();
    await caseOf('Hash deep-link to clinical task cannot show welcome', async () => {
      await hashPage.goto(base + '#/clinical/patient', { waitUntil: 'domcontentloaded' });
      assert.equal(await hashPage.locator('#sabri-shell-welcome').evaluate(el => el.hidden), true);
      assert.equal((await hashPage.evaluate(storageSnapshot)).length, 0);
    });
    await hashContext.close();

    const deniedContext = await browser.newContext();
    await deniedContext.addInitScript(() => {
      for (const name of ['localStorage', 'sessionStorage']) {
        Object.defineProperty(window, name, { configurable: true, get() { throw Error('Simulated storage denial'); } });
      }
    });
    const denied = await deniedContext.newPage();
    await caseOf('Browser storage denied -> automatic banner suppressed; content remains usable', async () => {
      await denied.goto(base, { waitUntil: 'domcontentloaded' });
      assert.equal(await denied.locator('#sabri-shell-welcome').evaluate(el => el.hidden), true);
      assert.ok(await denied.locator('main, [role="main"]').count());
    });
    await deniedContext.close();

    const reducedContext = await browser.newContext({ reducedMotion: 'reduce', viewport: { width: 360, height: 800 } });
    const mobile = await reducedContext.newPage();
    await caseOf('Mobile reduced-motion, Arabic/Urdu RTL stays in viewport', async () => {
      await mobile.goto(base, { waitUntil: 'domcontentloaded' });
      await visible(mobile);
      await mobile.evaluate(() => { document.documentElement.dir = 'rtl'; });
      const css = await mobile.locator('#sabri-shell-welcome').evaluate(el => {
        const style = getComputedStyle(el);
        return { animation: style.animationDuration, transition: style.transitionDuration,
          rect: el.getBoundingClientRect().toJSON(), viewport: innerWidth };
      });
      assert.ok(css.rect.x >= -1, 'RTL left boundary');
      assert.ok(css.rect.right <= css.viewport + 1, 'RTL right boundary');
      assert.equal(css.animation, '0s');
      assert.equal(css.transition, '0s');
      await mobile.screenshot({ path: process.env.SABRI_CI_SCREENSHOT || '/tmp/sabri-welcome-mobile.png', fullPage: true });
    });
    await reducedContext.close();

    if (process.exitCode) throw Error('One or more real-browser acceptance journeys failed.');
    process.stdout.write('V12.3 real Chromium/WordPress: ' + tests.length + ' journeys passed\n');
  } finally {
    await browser.close();
  }
})().catch(err => {
  process.stderr.write(String(err.stack || err) + '\n');
  process.exitCode = 1;
});
