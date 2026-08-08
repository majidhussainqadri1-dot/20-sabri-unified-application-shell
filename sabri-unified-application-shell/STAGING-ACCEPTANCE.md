# Staging Acceptance

Hostinger staging acceptance is mandatory before production promotion or live activation. Automated QA is evidence for the repository candidate; it does not satisfy this checklist.

## Exact Candidate and Backup

- Record File 20 version `1.4.3`, exact GitHub head, deterministic package name, SHA-256 and source manifest.
- Verify a restorable files-and-database backup before installation.
- Test both fresh installation and real upgrade from the actually deployed File 20 baseline.
- Verify schema/settings preservation, cache invalidation and rewrite flushing.
- Verify rollback restores shell-owned settings/configuration only and preserves companion data.

## Integration Contracts

- Activate intended companion modules in the approved dependency order.
- Confirm Home, News, Founder, Learn, Encyclopedia, Doctors, Worldwide Clinic, Video Wall, Reels, PDF Library, Radar, AI, Network, Marketplace, Appointments, Messages and Notifications resolve to real owner routes.
- Confirm unresolved optional destinations are hidden or honestly unavailable; no dead `#` links or fabricated fallback data.
- Confirm File 01 supplies foundation/registry/contracts/activation conventions without becoming a second shell or Search/Discovery truth store.
- Confirm File 02 owns credentials/passkeys/sessions/risk/recovery and File 20 only mounts its task surfaces.
- Confirm File 19 renders exactly one notification bell/output and File 26 remains the validated Search/Discovery/Ranking owner.
- Confirm Create appears only after current File 00 + File 22 authorization contracts allow it; direct forged Create routes remain denied by the native owner.

## Current Private and Task Routes

Verify exact privacy/cache/index/layout behavior for current routes, including:

- `/account-security/` — private/no-store/noindex, Minimal task layout;
- `/account-passkeys/` — private/no-store/noindex, Minimal task layout;
- `/resolve-account/` — no-store/noindex collision-resolution task flow, Minimal presentation;
- `/membership-application/`, `/membership-status/`, `/guardian-consent/`, `/membership-security/` — File 00 private/task semantics;
- `/platform-system-check/` and `/platform-foundation/status/` — restricted/private system surfaces;
- existing messages, appointments, security, verification, settings, notification center, publishing dashboard, newsroom and marketplace participant routes remain protected.

Test the same routes on both a root installation and an equivalent WordPress subdirectory installation. No sensitive route may enter Recent/Resume, Smart Navigation or predictive prefetch.

## PWA and Privacy-Policy Acceptance

- Confirm only one final File 20 virtual service-worker/manifest handler is effective at runtime.
- Confirm PWA enabled: public same-origin navigation degrades safely offline; sensitive routes are never cached/intercepted.
- Confirm PWA disabled: manifest/service-worker virtual routes return the retirement response and an installed worker self-unregisters when control is retired.
- Exercise provider/configured private-path additions below the supported bound and verify server/client/service-worker parity.
- Exercise a staging-only overflow above the protected-path bound: System Check must report incomplete/overflow policy; Recent/Resume, Smart Navigation, predictive prefetch and service-worker interception must fail closed. No path may be silently dropped as if public.
- Confirm no private query/token URL is retained in local shell history.

## Layout and Theme Safety

- Record the active theme content wrapper ID and parent before activation; confirm both remain unchanged afterward.
- Confirm the shell does not move `.wp-site-blocks`, `#page`, `.site`, the theme header or the theme footer.
- Confirm Home, Worldwide Clinic directory and doctor/clinic contexts receive approved Three-column behavior.
- Confirm ordinary public pages, profiles/timelines, Doctors Directory and private application surfaces receive their approved Two-column behavior.
- Confirm authentication, account-security/passkey/collision-resolution, membership verification/security, system/recovery, feed, REST, AJAX, cron, XML-RPC, robots, sitemap, embed, preview, print, Safe Mode and maintenance contexts remain Minimal or no-visual-shell as specified.
- Confirm Reels, full-screen Video/Live and activated PDF reader remain Immersive and Split Workspace is unavailable there.
- Confirm the right sidebar is absent in Two-column/Minimal/Immersive contexts and no page-level horizontal overflow occurs.

## Visual Ownership and Brand Continuity

- With a valid File 25 visual contract, confirm File 25 tokens remain authoritative and File 20 does not overwrite them.
- In a controlled File-25-unavailable continuity test only, confirm File 20 uses Sabri Green `#087A4E` as the non-authoritative primary fallback.
- Reconnect File 25 and confirm its validated token contract takes precedence again without stale cache.

## Accessibility and Responsive Acceptance

Test at 320, 360, 390, 480, 768, 900, 1024, 1100, 1280, 1366, 1440, 1600 and 1920 px, including portrait/landscape where relevant:

- full keyboard navigation and visible focus;
- skip link and semantic landmarks;
- Command Palette/editable-field `Ctrl/Cmd+K` protection;
- dialog/drawer focus trap, Escape close, outside-click close and focus restoration;
- 44 px approximate mobile touch targets;
- 200%/400% zoom/reflow;
- reduced-motion behavior;
- RTL Urdu/Arabic plus mixed LTR English;
- no clipping, sidebar overlap or inaccessible fixed content.

## Runtime and Browser Matrix

- Project staging WordPress/PHP/MySQL/LiteSpeed environment recorded exactly;
- current Chrome, Firefox, Edge and Safari where available, plus representative Android/iOS;
- logged-in/logged-out cache behavior;
- slow/offline/Save-Data behavior;
- LiteSpeed/Hostinger cache purge and regeneration;
- no unexplained PHP warnings/notices/fatals, JavaScript errors, database errors or Safe Mode regression.

## Acceptance Record

Record tester, date/time, environment, exact package/head/checksum, companion versions, screenshots/logs, defects, repairs, retest evidence, backup restore evidence, rollback evidence and Founder decision.

**Pass condition:** zero known unresolved release-blocking defects, or an explicit Founder-approved documented risk acceptance where the governing plan permits it. Hostinger staging acceptance still does not equal Live-Deployed or Operational status.
