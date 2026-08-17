# Migration and Upgrade

## Governing Rule

Normal release promotion is staging-first. A proven live production incident may use a bounded hotfix path only after live reality is frozen: exact deployed runtime/files, relevant database/schema state, runtime error, active configuration/dependencies and deployment parity where obtainable. Repository state is never assumed to equal Live. A production incident is not Resolved until the exact corrected artifact is deployed, the original live symptom is re-tested and deployed parity is confirmed.

Version **1.4.14** remains repository/code/package/automated-QA truth until its exact artifact is separately deployed and live-verified.

## Upgrade to 1.4.14

1. Verify a restorable files/database backup and record the actually deployed File20 version, deployed package/file evidence, settings, page mappings, theme and actual companion contracts.
2. Install the exact deterministic `20-sabri-unified-application-shell-1.4.14-REVIEW1-RELEASE-TRUTH-CORRECTION.zip` artifact on the approved deployment target. Confirm one canonical ZIP root, no development tests and exact source/stage/extracted manifest/SHA parity.
3. Confirm the deployed File20 runtime is exactly `1.4.14`; where possible compare deployed critical files or the deployed artifact against the approved manifest/checksum.
4. Re-test the original production incident route `/google-account-security/`. It must not return HTTP 500 or the historical `Renderer::item_visible_to_user()` undefined-method fatal.
5. Verify the three restored Renderer helpers remain present exactly once: `render_panel()`, `destination_url()`, and `item_visible_to_user()`.
6. Verify File20 no longer registers its historical local Home-feed runtime. Existing `home_feed` state must be migrated to `retired=true`, `auto_insert=false`, `posts_count=0`; configured `sabri_shell_home_feed` must not remain an active route source.
7. With File21 available, verify the five exact hooks: `sabri_shell_home_before_main`, `sabri_shell_home_main`, `sabri_shell_home_after_main`, `sabri_shell_home_right_sidebar`, `sabri_shell_news_main`. Native File21 main output must replace legacy page/shortcode fallback rather than duplicate it; fallback remains only when native output is absent.
8. Verify fresh File20 defaults create no Appearance group and runtime body classes are structural only; File25 remains visual owner. Legacy Appearance values may remain migration-only evidence.
9. Run System Check and authenticated sanitized export. File20/File00 critical Unknown/Unavailable/Incompatible states must never resolve to Healthy. Validate File00 runtime/version/health independently; static reviewed metadata is not authorization or live-health proof.
10. Verify File26 is the only Search/Discovery/Ranking provider. If its validated contract is absent, the search control is unavailable/hidden; no WordPress/File20 search fallback is allowed.
11. Test generic settings writes cannot change Emergency state. Emergency re-enable must fail closed unless critical File20 + File00 health is verified Healthy; audit integrity and cache purge remain mandatory.
12. Run Complete Repair dry-run. Verify real normalization, separately selectable stale File20 Page-ID quarantine, exact before→after diff, pre-repair snapshot and settings-row concurrency revalidation under lock.
13. Verify recovery snapshot format 2 distinguishes absent options from existing null values. Legacy/format/code/schema-incompatible snapshots remain read-only for automatic rollback.
14. Verify rollback revalidates and holds the target under lock before creating the pre-rollback snapshot; only File20-owned automatic rollback options are restorable; current Emergency state is preserved; caches purge and smoke test passes.
15. Test exact navigation precedence and adversarial route overrides: configured/registered published Page ID → shortcode page → archive → approved slug → strict validated override → unavailable; reject HTTP, protocol-relative URLs, credentials, query strings, fragments, CR/LF and unauthorized external hosts.
16. In a disposable staging copy only, verify default uninstall is non-destructive and explicit `delete_on_uninstall=true` removes only File20-owned operational state/schedules.
17. Complete `STAGING-ACCEPTANCE.md` for normal promotion, or for a bounded live-incident repair record equivalent live evidence, deployment checksum/parity and the original-route retest before production resolution may be claimed.

## From 1.4.13

The `1.4.13 → 1.4.14` upgrade is a release/operator-truth correction discovered during a fresh review. The proven 1.4.13 Renderer runtime repair is preserved unchanged. 1.4.14 corrects stale migration/staging/changelog/rollback guidance and adds permanent documentation-truth regression coverage. It adds no new feature, backend, ownership domain or database schema.

## From 1.4.12

The `1.4.12 → 1.4.13` repair restored the proven-deleted `Renderer` helper block after production evidence showed the live faulting renderer matched repository source and retained calls to missing methods. 1.4.14 includes that same runtime repair plus the Review-1 release-truth corrections.

## From 1.4.10 / 1.4.11

The `1.4.10 → 1.4.11` upgrade was an eighty-round File20 consolidation. It advanced settings schema to 5, removed retired local-domain runtime/configuration paths, strengthened canonical route/access/provider validation, hardened recovery/concurrency/control-plane state and completed dynamic File20 cache/uninstall ownership. Version 1.4.12 subsequently added the second eighty-round route, ownership, System Check, REST privacy, concurrency and LKG hardening.

## From 1.4.9

The `1.4.9 → 1.4.10` upgrade was canonical-ownership and health-truth hardening. It retired the residual File20 local Home-feed runtime/state under File21 ownership, published the five exact native File21 slots with single-render semantics, removed residual File20 visual runtime ownership, removed WordPress/File20 Search fallback under File26 ownership, bound provider health to actual semantic versions, prevented critical provider states from false-green Healthy, and applied that critical gate to Emergency re-enable.

It does **not** add a nineteenth Future Shell feature, activate CF modules, create companion databases, repair File00's native security defects, or migrate native companion-domain records.

## Earlier versions / no shell / another shell

Upgrade directly to the exact approved 1.4.14 artifact after verified backup and environment freeze. Do not recreate intermediate packages. Validate native-owner contracts independently, avoid duplicate pages/backends/shells, and use only integrity-valid current-format/current-schema snapshots for automatic rollback.
