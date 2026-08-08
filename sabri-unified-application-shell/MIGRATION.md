# Migration and Upgrade

## Governing Rule

All installation, upgrade, repair and rollback work must be performed on staging first. Version **1.4.8** is a repository/code/package/automated-QA candidate only until Hostinger staging, real companion integration, backup/restore/rollback rehearsal and Founder acceptance pass.

## Upgrade to 1.4.8

1. Take and verify a restorable files-and-database backup before upload.
2. Record the actually deployed File 20 version/commit/package checksum, current shell/Future-Shell settings, active theme, page/navigation mappings, actual File 00–26 companion versions and any deliberately activated CF modules.
3. Install the exact deterministic `1.4.8-EIGHTH-TEN-ROUND-HARDENED` candidate on staging. Confirm one canonical plugin root, no `tests/`/development material, safe ZIP paths and exact source/stage/extracted SHA-256 parity.
4. Activate/update File 20. Version 1.4.8 creates no companion-domain database table and must not mutate clinical/support/finance/media/analytics/localization records.
5. Run **Sabri Shell > System Check** and export the authenticated sanitized evidence. Confirm IDs, severity, evidence, last-run, affected-surface and remediation fields; Unknown/Unavailable/Incompatible must never be treated as PASS.
6. Test root and WordPress-subdirectory installations for `/account-security`, `/account-passkeys`, `/resolve-account`, membership/guardian/system routes and other sensitive surfaces; governed task pages must resolve to Minimal/private behavior consistently.
7. Verify File 25 remains visual authority and the historical File 20 Appearance editor cannot be used as a competing settings surface.
8. Exercise Hardened Repair: inspect the dry-run diff, select actions, verify settings-row concurrency, pre-repair snapshot, real normalization, audit evidence, provider recheck and post-repair status.
9. Exercise Hardened Rollback: only integrity-valid snapshots with compatible File 20 code major **and exact current settings schema** may execute. Confirm a pre-rollback snapshot is created, File-20/Future-Shell state only is restored, cache is purged and the smoke test passes.
10. Verify activation snapshot format is integrity-protected and never rewrites shared WordPress `page_on_front` or `show_on_front` ownership.
11. Open Safe Mode only through the administrator nonce-bound URL or the higher-priority configuration constant. Test Emergency Disable with mandatory reason, actor/time/review evidence and audit record; test that re-enable is blocked by invalid audit/provider health and performs cache purge before success.
12. Independently verify the actually installed/runtime File 00–26 contracts and any explicitly activated CF-01..CF-06 modules. File 20 metadata is not runtime detection or activation evidence.
13. Preserve CF-04 `/media/d/{grant}` native token/range/cache semantics; preserve current one-free-tier, voluntary-donation, zero-commission, donor-neutral financial law; preserve File 17 + activated CF-04 1 GB transfer and CF-06 localization ownership.
14. Test all five release rings, PWA root/subdirectory lifecycle/privacy, keyboard/accessibility, RTL/LTR, responsive/foldable layouts, weak-network behavior and Split Workspace exclusions.
15. Complete `STAGING-ACCEPTANCE.md`, backup restore proof, rollback rehearsal and Founder acceptance before any production decision.

## From 1.4.7

The `1.4.7 → 1.4.8` upgrade is non-destructive and File-20 recovery/diagnostic hardening focused. It retires the stale File 20 Appearance surface, corrects subdirectory-sensitive layout classification, structures System Check/export evidence, repairs bounded audit-chain retention and privacy erasure integrity, removes a hidden mbstring dependency, makes settings normalization real with dry-run diffs, adds schema-aware File-20/Future-Shell snapshots/rollback, replaces the legacy activation snapshot behavior, hardens Safe Mode/Emergency state transitions and routes admin recovery actions through the hardened controllers.

It does **not** add a nineteenth Future Shell feature, activate CF modules or migrate native companion-domain records.

## From Earlier 1.4.x / Older Releases / No Shell / Another Shell

Upgrade directly to 1.4.8 on staging after verified backup. Do not recreate intermediate packages. Validate current native-owner contracts, use only integrity-valid/current-schema File 20 recovery snapshots for automatic rollback, avoid duplicate pages/databases/backends, test Safe Mode/Repair/PWA/rollback and complete staging acceptance before production. Another structural shell must not remain concurrently authoritative.

No migration step creates, deletes or mutates membership identity, authentication/passkeys, messaging, notification, appointment, profile, marketplace, clinic, publishing, search/ranking, security-assurance, clinical, support-case, financial-ledger, media-processing, analytics or localization records owned by companion files.
