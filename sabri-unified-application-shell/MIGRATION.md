# Migration and Upgrade

## Governing Rule

All installation, upgrade, repair and rollback work must be performed on staging first. Version **1.4.10** remains a repository/code/package/automated-QA candidate until Hostinger staging, real companion integration, backup/restore/rollback rehearsal and Founder acceptance pass.

## Upgrade to 1.4.10

1. Verify a restorable files/database backup and record the deployed File20 version, package checksum, settings, page mappings, theme and actual companion contracts.
2. Install the exact deterministic `1.4.10-TENTH-TEN-ROUND-HARDENED` artifact on staging only. Confirm one canonical ZIP root, no development tests and exact source/stage/extracted manifest/SHA parity.
3. Verify File20 no longer registers its historical local Home-feed runtime. Existing `home_feed` state must be migrated to `retired=true`, `auto_insert=false`, `posts_count=0`; configured `sabri_shell_home_feed` must not remain an active route source.
4. With File21 available, verify the five exact hooks: `sabri_shell_home_before_main`, `sabri_shell_home_main`, `sabri_shell_home_after_main`, `sabri_shell_home_right_sidebar`, `sabri_shell_news_main`. Native File21 main output must replace legacy page/shortcode fallback rather than duplicate it; fallback remains only when native output is absent.
5. Verify fresh File20 defaults create no Appearance group and runtime body classes are structural only; File25 remains visual owner. Legacy Appearance values may remain migration-only evidence.
6. Run System Check and authenticated sanitized export. File20/File00 critical Unknown/Unavailable/Incompatible states must never resolve to Healthy. Validate File00 runtime/version/health independently; the static reviewed 1.2.18 audit record is not authorization or staging proof.
7. Verify File26 is the only Search/Discovery/Ranking provider. If its validated contract is absent, the search control is unavailable/hidden; no WordPress/File20 search fallback is allowed.
8. Verify File25/File01-B provider health uses the actually advertised semantic version; malformed versions are unavailable and below-minimum versions incompatible.
9. Test generic settings writes cannot change Emergency state. Emergency re-enable must fail closed unless critical File20 + File00 health is verified Healthy; audit integrity and cache purge remain mandatory.
10. Run Complete Repair dry-run. Verify real normalization, separately selectable stale File20 Page-ID quarantine, exact before→after diff, pre-repair snapshot and settings-row concurrency revalidation under lock.
11. Verify recovery snapshot format 2 distinguishes absent options from existing null values. Legacy/format/code/schema-incompatible snapshots remain read-only for automatic rollback.
12. Verify rollback revalidates and holds the target under lock before creating the pre-rollback snapshot; absent options restore as absent; every restored option is verified; current Emergency state is preserved; Emergency metadata and settings-row counter are not rolled back; caches purge and smoke test passes.
13. Test exact navigation precedence and adversarial route overrides: configured/registered published Page ID → shortcode page → archive → approved slug → strict validated override → unavailable; reject HTTP, protocol-relative URLs, credentials, query strings, fragments, CR/LF and unauthorized external hosts.
14. In a disposable staging copy only, verify default uninstall is non-destructive and explicit `delete_on_uninstall=true` removes only File20-owned operational state/schedules.
15. Complete `STAGING-ACCEPTANCE.md`, backup restore proof, rollback rehearsal, accessibility/browser/device/PWA checks and Founder acceptance before production promotion.

## From 1.4.9

The `1.4.9 → 1.4.10` upgrade is canonical-ownership and health-truth hardening. It retires the residual File20 local Home-feed runtime/state under File21 ownership, publishes the five exact native File21 slots with single-render semantics, removes residual File20 visual runtime ownership, records the reviewed File00 1.2.18 audit without falsely declaring it production-safe, removes WordPress/File20 Search fallback under File26 ownership, binds provider health to actual semantic versions, prevents critical provider states from false-green Healthy, and applies that critical gate to Emergency re-enable.

It does **not** add a nineteenth Future Shell feature, activate CF modules, create companion databases, repair File00's native security defects, or migrate native companion-domain records.

## Earlier versions / no shell / another shell

Upgrade directly to 1.4.10 on staging after verified backup. Do not recreate intermediate packages. Validate native-owner contracts independently, avoid duplicate pages/backends/shells, and use only integrity-valid current-format/current-schema snapshots for automatic rollback.
