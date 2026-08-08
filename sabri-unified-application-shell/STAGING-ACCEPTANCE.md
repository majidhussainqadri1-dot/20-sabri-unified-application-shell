# Staging Acceptance

Hostinger staging acceptance is mandatory before production promotion or live activation. Automated QA is repository evidence only and does not satisfy this checklist.

## Exact Candidate and Backup

- Record File 20 version `1.4.8`, exact GitHub head, deterministic `1.4.8-EIGHTH-TEN-ROUND-HARDENED` package name, SHA-256 and source manifest.
- Confirm one canonical `sabri-unified-application-shell/` ZIP root and no `tests/` or development-only material.
- Verify embedded `MANIFEST.sha256` equals the external source manifest and source/stage/extracted file-set + SHA-256 parity is green; verify ZIP CRC/path/root/duplicate-entry gates.
- Verify a restorable files-and-database backup before installation; test fresh install and real upgrade from the actually deployed File 20 baseline.

## Eighth-Pass Recovery and Diagnostic Gates

- Confirm the historical File 20 Appearance editor is unavailable and File 25 remains visual authority.
- Run System Check. Every row must expose ID, status, severity, evidence, last run, affected surface and remediation; Unknown/Unavailable/Incompatible must not be reported as PASS.
- Export `/wp-json/sabri-shell/v1/system-check/export` using authenticated administrator REST credentials/nonces. Verify bounded sanitized evidence, `private, no-store` and no secret/private-domain payload leakage.
- Test duplicate-shell detection under English and at least one translated/RTL locale; display translation must not alter PASS/FAIL truth.
- Generate more than the retained audit-event bound in a controlled fixture/staging test and verify anchor-based chain validation remains valid. Run a privacy erasure fixture and verify the authorized rehash preserves chain validity.
- Test a PHP environment without mbstring if practical, or equivalent controlled fixture, and verify audit/assurance evidence truncation does not fatal.
- On Hardened Repair, inspect the dry-run diff before execution. Verify real settings normalization, settings-row concurrency, selected-action scope, pre-repair snapshot, audit evidence and no false success on a forced failing operation.
- On Hardened Rollback, verify integrity-invalid, code-major-incompatible and schema-incompatible snapshots are blocked/read-only. A compatible rollback must create a pre-rollback snapshot, restore File-20/Future-Shell state only, invalidate caches, purge cache and pass the smoke test.
- Verify activation snapshot format 2 integrity and prove rollback never changes WordPress `page_on_front` / `show_on_front` ownership.
- Open query Safe Mode only through the administrator nonce-bound URL. Verify forged/missing nonce does not activate it; configuration constant remains the highest-priority break-glass path.
- Emergency Disable must require reason and store actor/time/review evidence + audit. Re-enable must fail when audit integrity/provider critical health is invalid and must purge cache before success.
- Confirm the visible File 20 admin Repair/Rollback/Emergency controls route through the hardened controllers; legacy one-click forms must not provide an alternate write path.

## Compatibility and Conditional Module Truth

System Check/registry compatibility data are declared targets, not runtime detection. Record real installed/provider responses independently. Any mismatch or missing provider must produce an honest unavailable/degraded/unknown state, never fabricated health.

CF-01 through CF-06 remain **conditional**. Registry presence does not activate, authorize or prove a native module. Verify clinical/support/finance/media/analytics/localization ownership stays native. Preserve CF-04 `/media/d/{grant}` token/Range/cache authority, the one-free-tier/voluntary-donation/zero-commission/donor-neutral law, File 17 + activated CF-04 **1 GB per-file** transfer and CF-06 locale/translation truth.

## Identity, Integration and Security Boundaries

- File 00 remains membership/identity/trust authority; File 02 owns credentials/passkeys/sessions/recovery/security events; File 24 governs assurance/security state; File 20 renders/routes only.
- Privileged password reset must fail closed without the required File 00 dual-control receipt where specified.
- Controlled File 24 unavailability must not disable native File 02/other owner security.
- Confirm Home/News/Founder/Learn/Encyclopedia/Doctors/Clinic/Video/Reels/PDF/Radar/AI/Network/Marketplace/Appointments/Messages/Notifications resolve to real owner routes or honest unavailable state.
- Confirm exactly one File 19 bell/output, File 21's five native slots once, File 25 visual authority, File 26 Search/Discovery/Ranking ownership and File 00+22 authorized Create behavior.

## Private Routes, Future Shell, PWA and Accessibility

Test root and WordPress-subdirectory installations. Sensitive account/membership/system/CF routes must receive governed Minimal/private behavior and must not enter Recent/Resume, Smart Navigation or predictive prefetch. `/.well-known/webauthn` remains File-02-owned public standards JSON/no-cache and must not be misclassified private.

Verify all five release-ring states, one final service-worker/manifest owner, protected-path overflow fail-closed behavior, public-only offline cache behavior, all four layout modes, Split Workspace exclusions, File 25 visual tokens, 320–1920 px matrix, 200%/400% zoom, keyboard/focus/dialogs, reduced motion, RTL/LTR, screen readers, Chrome/Firefox/Edge/Safari where available, representative Android/iOS, slow/offline/Save-Data and LiteSpeed/Hostinger cache behavior.

## Continuity and Acceptance Record

Record tester, date/time, environment, exact package/head/checksum, actual File 00–26 and activated CF versions/contracts, screenshots/logs, defects, repairs/retests, backup restore proof, rollback evidence and Founder decision.

**Pass condition:** zero known unresolved release-blocking defects, or explicit Founder-approved documented risk acceptance where the governing plan permits it. Staging acceptance still does not equal Live-Deployed or Operational status.
