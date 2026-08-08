# Staging Acceptance

Hostinger staging acceptance is mandatory before production promotion or live activation. Automated QA is repository evidence only and does not satisfy this checklist.

## Exact Candidate and Backup

- Record File 20 version `1.4.7`, exact GitHub head, deterministic package name, SHA-256 and source manifest.
- Confirm one canonical `sabri-unified-application-shell/` ZIP root and no `tests/` or declared development-only material.
- Verify embedded `MANIFEST.sha256` equals the external source manifest and source/stage/extracted parity is green.
- Verify a restorable files-and-database backup before installation.
- Test fresh install and real upgrade from the actually deployed File 20 baseline.
- Verify rollback restores File-20-owned settings/configuration only and preserves all companion data.

## Current Compatibility Truth

System Check and registry metadata must describe File 00/01/02/24 current facts as **declared compatibility targets, not runtime detection**. Record real installed/provider responses independently. Any mismatch or missing provider must produce honest unavailable/degraded state, never fabricated health.

## Seventh-Pass Conditional Modules

CF-01 through CF-06 are **conditional**. Merely appearing in File 20's registry does not activate, authorize or prove any native module.

- CF-01: verify clinical records/patients/encounters/prescriptions/follow-up/governance/API routes are native-owner authorized and remain out of File 20 Recent/Resume, Smart Navigation and predictive prefetch.
- CF-02: `/support` public help may remain public; case/appeal/admin/API surfaces must remain private and native-owner controlled.
- CF-03: verify single free tier, voluntary donation, zero commission and donor neutrality. Paid collection stays dormant unless a later Founder change-control decision and native CF-03 activation authorize it.
- CF-04: verify upload/admin/API surfaces are protected; `/media/d/{grant}` must receive Minimal/no visual Future Shell treatment while native CF-04 retains token, `Range`, cache-control and delivery semantics.
- CF-05: `/insights`, analytics admin and API remain authorized/private; File 20 must not expose raw user/clinical analytics or create a metric store.
- CF-06: language/direction controls must consume an approved localization provider; File 20 must not create locale/translation truth.
- Verified transfer remains File 17 + activated CF-04 with the approved **1 GB** per-file limit. Eligible download remains native-owner/File-24/CF-04 territory.

## Identity, Authentication and Security Boundaries

- File 00 remains membership/identity/trust authority; File 20 performs no membership/identity/consent write.
- File 02 owns credentials, passkeys, sessions, containment, recovery and security-event production; File 20 mounts/routes only.
- Privileged password reset must fail closed without the required File 00 dual-control receipt where specified; File 20 must not generate, infer or bypass it.
- Controlled File 24 unavailability must not disable native File 02/other owner security. File 20 may render Unknown/Unavailable but cannot become a second security engine.

## Integration Contracts

- Confirm Home, News, Founder, Learn, Encyclopedia, Doctors, Worldwide Clinic, Video Wall, Reels, PDF Library, Radar, AI, Network, Marketplace, Appointments, Messages and Notifications resolve to real owner routes.
- Confirm unresolved optional/conditional destinations are hidden or honestly unavailable; no dead `#` links or fabricated fallback data.
- Confirm File 19 renders exactly one notification bell/output, File 21 mounts five native slots once, File 25 retains visual authority and File 26 remains Search/Discovery/Ranking owner.
- Confirm Create appears only after current File 00 + File 22 authorization contracts allow it.

## Current Private and Task Routes

Verify exact privacy/cache/index/layout behavior for existing account/membership/system/private application routes and all CF private routes listed above. `/.well-known/webauthn` remains File-02-owned public standards JSON/no-cache with Minimal/no visual shell and must not be misclassified private.

Test root and WordPress-subdirectory installations. No sensitive route may enter Recent/Resume, Smart Navigation or predictive prefetch.

## Future Shell, PWA, Layout and Accessibility

- Verify exact release-ring states `disabled`, `internal`, `staging`, `limited`, `general`; invalid input fails closed and REST configuration remains `manage_options` only.
- Confirm only one final File 20 service-worker/manifest handler; public same-origin navigation may degrade offline while sensitive routes are never cached/intercepted.
- Exercise protected-path overflow; privacy-sensitive conveniences and interception must fail closed rather than silently dropping paths.
- Confirm all four approved layout modes, theme DOM ownership and File 25 visual ownership.
- Test 320, 360, 390, 480, 768, 900, 1024, 1100, 1280, 1366, 1440, 1600 and 1920 px; keyboard/focus, dialogs/drawers, 200%/400% zoom, reduced motion, RTL/LTR, touch targets and screen readers.
- Test Chrome, Firefox, Edge, Safari where available, representative Android/iOS, slow/offline/Save-Data and LiteSpeed/Hostinger cache purge/regeneration.

## Continuity and Acceptance Record

- Test Safe Mode, Complete Repair, LKG recovery, activation snapshot, backup restore and rollback.
- Record tester, date/time, environment, exact package/head/checksum, actual File 00–26 and activated CF versions/contracts, screenshots/logs, defects, repairs, retest evidence, restore/rollback evidence and Founder decision.

**Pass condition:** zero known unresolved release-blocking defects, or explicit Founder-approved documented risk acceptance where the governing plan permits it. Staging acceptance still does not equal Live-Deployed or Operational status.
