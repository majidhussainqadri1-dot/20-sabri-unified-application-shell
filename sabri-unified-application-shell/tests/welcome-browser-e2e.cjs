/* Real Chromium over a fresh ephemeral WordPress 7.1.4 + File00/20/25 CI DB.
 * Never run against production or a real person's device. No external data.
 */
'use strict';
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const AxeBuilder = require('@axe-core/playwright').default;

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
      const diagnostics = await page.evaluate(() => ({
        title: document.title,
        welcomeCount: document.querySelectorAll('#sabri-shell-welcome').length,
        clientConfig: window.SABRI_SHELL_WELCOME || null,
        script: [...document.scripts].filter(n => (n.src||'').includes('welcome')).map(n => n.src),
        plugins: [...document.querySelectorAll('link[rel="stylesheet"]')].filter(n => (n.href||'').includes('sabri')).map(n => n.href),
        page: document.documentElement.outerHTML.slice(0, 2100),
      }));
      process.stdout.write('WELCOME_BROWSER_DIAGNOSTICS ' + JSON.stringify(diagnostics) + '\\n');
      if (diagnostics.welcomeCount === 0) throw Error('Server omitted File20 host; inspect public_eligible/contract and server logs');
      await visible(page);
      assert.ok(await page.locator('main, [role="main"]').count(), 'Underlying content is present');
      assert.ok(await page.locator('[data-sabri-welcome-skip]').count());
      assert.ok(await page.locator('[data-sabri-welcome-dismiss]').count());
      assert.ok(await page.locator('[data-sabri-welcome-continue]').count());
      assert.equal(await page.locator('[aria-live="polite"]').count() > 0, true);
    });
    if (process.exitCode) throw Error('Stop at first HTTP/frontend gate until the cause is established.');
    await caseOf('Welcome controls pass automated WCAG 2.2 A/AA checks', async () => {
      const result = await new AxeBuilder({ page }).include('#sabri-shell-welcome')
        .withTags(['wcag2a','wcag2aa','wcag21a','wcag21aa','wcag22aa']).analyze();
      const notes = result.violations.map(v => v.id + ': ' + v.help).join('; ');
      assert.equal(result.violations.length, 0, 'WCAG 2.2 violations within welcome region: ' + notes);
    });
    await caseOf('First presentation suppresses a fresh session even with no Skip/Close click', async () => {
      const original = await page.evaluate(storageSnapshot);
      assert.equal(original.length, 1, 'First shown visit must record minimum device preference');
      assert.ok(original[0].until >= Date.now() + toMs - 30000, 'First-view state valid for full 30 days');
      const fresh = await context.newPage();
      await fresh.goto(base, { waitUntil: 'domcontentloaded' });
      assert.equal(await fresh.locator('#sabri-shell-welcome').evaluate(el => el.hidden), true,
        'New browser tab/session cannot force repeated first-view welcome');
      await fresh.close();
    });
    let dismissed = null;
    await caseOf('Skip hides immediately and persists a full 30 days', async () => {
      await page.locator('[data-sabri-welcome-skip]').click();
      assert.equal(await page.locator('#sabri-shell-welcome').evaluate(el => el.hidden), true);
      assert.equal(await page.evaluate(() => {
        const active = document.activeElement;
        return active !== null && (active.matches('main, [role="main"], #content, h1'));
      }), true, 'Keyboard focus restored to the main region rather than hidden Skip button');
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
    await caseOf('A completed 30-day period permits new automatic visit', async () => {
      await page.goto(base, { waitUntil: 'domcontentloaded' });
      await page.evaluate(() => {
        const key = Object.keys(localStorage).find(k => k.startsWith('sabri-shell-welcome-dismiss-until-v1-'));
        if (!key) throw Error('30-day preference does not exist');
        localStorage.setItem(key, String(Date.now() - 1000));
      });
      const newTab = await context.newPage();
      await newTab.goto(base, { waitUntil: 'domcontentloaded' });
      await visible(newTab);
      await newTab.close();
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
      await mobile.evaluate(() => { document.documentElement.dir = 'rtl'; document.documentElement.lang = 'ur'; });
      const englishDirection = await mobile.locator('.sabri-ui-welcome-panel__message').evaluate(el => getComputedStyle(el).direction);
      assert.equal(englishDirection, 'ltr', 'English copy must retain LTR even within Urdu UI.');
      await mobile.evaluate(() => {
        document.querySelector('#sabri-welcome-title').textContent = 'صابری سوشل ہومیوپیتھی میں خوش آمدید';
        document.querySelector('.sabri-ui-welcome-panel__message').textContent = 'کلاسیکی ہومیوپیتھی سیکھیں اور عالمی برادری سے رابطہ کریں۔';
      });
      const urduDirection = await mobile.locator('.sabri-ui-welcome-panel__message').evaluate(el => getComputedStyle(el).direction);
      assert.equal(urduDirection, 'rtl', 'Urdu source copy must retain RTL without JS layout patches.');
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
