# Migration and Upgrade

## Governing Rule

All installation, upgrade, repair and rollback work must be performed on staging first. Version **1.4.9** remains a repository/code/package/automated-QA candidate until Hostinger staging, real companion integration, backup/restore/rollback rehearsal and Founder acceptance pass.

## Upgrade to 1.4.9

1. Verify a restorable files/database backup and record the deployed File20 version, package checksum, settings, page mappings, theme and actual companion contracts.
2. Install the exact deterministic `1.4.9-NINTH-TEN-ROUND-HARDENED` artifact on staging only. Confirm one canonical ZIP root, no development tests and exact source/stage/extracted manifest/SHA parity.
3. Verify fresh File20 schema-4 defaults create no Appearance group; File25 remains visual owner. Legacy Appearance values may remain migration-only evidence.
4. Run System Check and authenticated sanitized export. Unknown/Unavailable/Incompatible remain distinct from PASS.
5. Test generic settings writes cannot change Emergency state; only canonical Emergency Disable/re-enable may do so with reason/actor/time/review/audit/health/cache evidence.
6. Run Complete Repair dry-run. Verify real normalization, the separately selectable stale File20 Page-ID quarantine, exact before→after diff, pre-repair snapshot and settings-row concurrency revalidation under lock.
7. Verify recovery snapshot format 2 distinguishes absent options from existing null values. Legacy/format/code/schema-incompatible snapshots must remain read-only for automatic rollback.
8. Verify rollback revalidates and holds the target under lock before creating the pre-rollback snapshot; absent options restore as absent; every restored option is verified; current Emergency state is preserved; Emergency metadata and the settings-row counter are not rolled back; the row counter advances monotonically after real settings restoration; caches purge and smoke test passes.
9. Test exact navigation precedence: configured/registered published Page ID → shortcode page → archive → approved slug → strict validated override → unavailable.
10. Adversarially test route overrides: reject HTTP, protocol-relative URLs, credentials, query strings, fragments, CR/LF and unauthorized external hosts. External HTTPS is allowed only through the explicit host allowlist; stored legacy overrides are revalidated on read.
11. Test explicit `delete_on_uninstall=true` only in a disposable staging copy: File20 operational state and schedule should purge while companion pages/content/tables/options remain untouched. Default uninstall must remain non-destructive.
12. Verify all prior privacy/System Check/PWA/release-ring/CF ownership gates, accessibility/browser/device behavior and actual File00–26 providers.
13. Complete `STAGING-ACCEPTANCE.md`, backup restore proof, rollback rehearsal and Founder acceptance before production promotion.

## From 1.4.8

The `1.4.8 → 1.4.9` upgrade is File20 structural/recovery/routing hardening. It removes fresh File20 visual-state creation, closes Emergency direct-write bypass, adds presence-aware recovery snapshots, closes repair/rollback TOCTOU and retention races, adds stale Page-ID repair, strict route security and exact precedence, completes explicit File20-only uninstall cleanup, and preserves current Emergency/concurrency safety during rollback.

It does **not** add a nineteenth Future Shell feature, activate CF modules, create companion databases, or migrate native companion-domain records.

## Earlier versions / no shell / another shell

Upgrade directly to 1.4.9 on staging after verified backup. Do not recreate intermediate packages. Validate native-owner contracts independently, avoid duplicate pages/backends/shells, and use only integrity-valid current-format/current-schema snapshots for automatic rollback.
