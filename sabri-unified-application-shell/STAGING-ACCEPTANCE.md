# Staging Acceptance

Hostinger staging acceptance is mandatory before production promotion or live activation. Automated QA is repository evidence only and does not satisfy this checklist.

## Exact Candidate and Backup

- Record File 20 version `1.4.6`, exact GitHub head, deterministic package name, SHA-256 and source manifest.
- Confirm one canonical `sabri-unified-application-shell/` ZIP root and no `tests/` or declared development-only material.
- Verify embedded `MANIFEST.sha256` equals the external source manifest and source/stage/extracted parity is green.
- Verify a restorable files-and-database backup before installation.
- Test fresh install and real upgrade from the actually deployed File 20 baseline.
- Verify rollback restores File-20-owned settings/configuration only and preserves companion data.

## Sixth-Pass Compatibility Truth

System Check and registry metadata must describe the following as **declared compatibility targets, not runtime detection**. Record the real installed/provider response independently.

- File 00: runtime `1.2.13`, schema `1.3.0`, Public Membership `1.2.0`, CF-01 `1.0.0`, Advanced Trust `1.0.0`.
- File 01-B: runtime `2.0.0`, schema `1.2.0`, Foundation Contract `2.0.0`, exact 18 Future Foundation enhancements.
- File 02: runtime `1.3.1`, DB schema `1.3.0`, passkey schema `1.1.0`, Authentication Assurance v2 `2.0.0`, auth-event projection contract `1.1.0`, exact 24 modern-auth enhancements.
- File 24: runtime candidate `0.99.0`, schema `0.25.5`, exact 25 Future Security requirements `F24-FUT-001..F24-FUT-025`.

A mismatch, missing provider or incompatible owner contract must produce an honest unavailable/degraded result, never a fabricated healthy state.

## Identity, Authentication and Security Boundaries

- Confirm File 00 remains membership/identity/trust authority; File 20 performs no membership/identity/consent write.
- Confirm File 02 owns credentials, passkeys, sessions, containment, recovery and security-event production; File 20 mounts/routes only.
- Verify the File 02 current event family includes `AuthenticationCompromiseReported.v1`, `AuthenticationLockdownEnabled.v1` and `RecoveryChangeCoolingStarted.v1` where the native provider exposes them.
- Verify privileged password reset fails closed without the required File 00 dual-control receipt. File 20 must not generate, infer or bypass this receipt.
- Disable/unavailable File 24 in a controlled staging scenario: native File 02 and other owner security controls must remain effective; File 20 may render Unknown/Unavailable but must not create a second security engine.
- Confirm File 24 recommendations/security states remain render/routing inputs only; native modules enforce their own reads/writes.

## Integration Contracts

- Confirm Home, News, Founder, Learn, Encyclopedia, Doctors, Worldwide Clinic, Video Wall, Reels, PDF Library, Radar, AI, Network, Marketplace, Appointments, Messages and Notifications resolve to real owner routes.
- Confirm unresolved optional destinations are hidden or honestly unavailable; no dead `#` links or fabricated fallback data.
- Confirm File 19 renders exactly one notification bell/output and File 26 remains the validated Search/Discovery/Ranking owner.
- Confirm File 21 mounts exactly its five native slots once.
- Confirm Create appears only after current File 00 + File 22 authorization contracts allow it; forged native actions remain denied.
- Confirm File 25 visual tokens remain authoritative when its valid contract is present.

## Current Private and Task Routes

Verify exact privacy/cache/index/layout behavior for `/account-security/`, `/account-passkeys/`, `/resolve-account/`, membership/guardian/security routes, platform system/foundation routes, Messages, Appointments, Notifications, Publishing Dashboard and other protected surfaces.

`/.well-known/webauthn` must remain File-02-owned **public** standards JSON/no-cache with Minimal/no visual shell. It must not be misclassified as a private File 20 route.

Test root and WordPress-subdirectory installations. No sensitive route may enter Recent/Resume, Smart Navigation or predictive prefetch.

## Future Shell Release Rings

Verify the exact states `disabled`, `internal`, `staging`, `limited`, `general`.

- invalid/malformed input fails closed;
- REST configuration remains `manage_options` only;
- Internal allows `manage_options` by default or an authenticated principal explicitly approved through `sabri_shell_future_internal_principal_allowed`;
- absent/false owner contract denies a non-manager;
- unauthenticated users never pass Internal;
- Staging does not auto-enable on production;
- Limited is authenticated and deterministic by user+feature.

## PWA and Privacy

- Confirm only one final File 20 service-worker/manifest handler is effective.
- Public same-origin navigation may degrade offline; sensitive routes are never cached/intercepted.
- Disabled PWA routes retire correctly and installed workers self-unregister.
- Exercise dynamic private paths and protected-path overflow; overflow must disable privacy-sensitive conveniences and interception rather than dropping paths.
- No private query/token URL enters shell-local history.

## Layout, Visual, Accessibility and Responsive Acceptance

- Active theme DOM/landmarks remain intact; no theme root/header/footer reparenting.
- All four approved layout modes resolve correctly; Split Workspace is absent in Minimal/Immersive contexts.
- File 25 is visual owner; File 20 uses Sabri Green continuity fallback only when the File 25 contract is genuinely unavailable.
- Test 320, 360, 390, 480, 768, 900, 1024, 1100, 1280, 1366, 1440, 1600 and 1920 px.
- Test keyboard, visible focus, dialogs/drawers, 200%/400% zoom/reflow, reduced motion, Urdu/Arabic RTL plus mixed LTR English, touch targets and screen-reader behavior.
- Test current Chrome, Firefox, Edge and Safari where available plus representative Android/iOS.
- Test slow/offline/Save-Data behavior and LiteSpeed/Hostinger cache purge/regeneration.

## Continuity and Acceptance Record

- Test Safe Mode, Complete Repair, LKG recovery, activation snapshot and rollback.
- Record tester, date/time, environment, exact package/head/checksum, actual companion versions, screenshots/logs, defects, repairs, retest evidence, backup restore evidence, rollback evidence and Founder decision.

**Pass condition:** zero known unresolved release-blocking defects, or explicit Founder-approved documented risk acceptance where the governing plan permits it. Staging acceptance still does not equal Live-Deployed or Operational status.
