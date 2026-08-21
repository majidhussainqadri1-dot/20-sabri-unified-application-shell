# Migration and Upgrade

## Governing Rule

Normal release promotion is staging-first. A proven live production incident may use a bounded hotfix path only after live reality is frozen: exact deployed runtime/files, relevant database/schema state, runtime error, active configuration/dependencies and deployment parity where obtainable. Repository state is never assumed to equal Live. A production incident is not Resolved until the exact corrected artifact is deployed, the original live symptom is re-tested and deployed parity is confirmed.

Version **1.4.15** is repository/code/package/automated-QA truth only after its exact candidate CI passes. It is not live truth until the exact artifact is deployed and parity is re-proven.

## Upgrade to 1.4.15

1. Preserve the live-evidence freeze that justified this correction: File01-B `2.0.1` exact deployed-code parity, schema `1.2.0` physical PASS, migration health PASS, 15 reconciliation actions, 12 blocked owner plans, File21 Home/News accepted, exact deployed File20 `1.4.14` parity and no File01 reconciliation hook code in that deployed File20 build.
2. Verify a restorable files/database backup and record the actually deployed File20 version, File01 plan hash, File01 legacy map, File20 settings, active companion versions and current live route behavior.
3. Install the exact deterministic `20-sabri-unified-application-shell-1.4.15-FILE01-RECONCILIATION-REPAIR.zip` artifact on the approved deployment target. Confirm one canonical ZIP root, no development tests and exact source/stage/extracted manifest/SHA parity.
4. Confirm deployed File20 runtime is exactly `1.4.15`; compare deployed runtime files/package manifest against the approved exact-head artifact before doing any File01 write.
5. Re-run File01 reconciliation dry-run only. `home` and `news` must remain accepted by `file-21`; exactly the twelve formerly blocked keys—`founder`, `learn`, `encyclopedia`, `doctors`, `clinic`, `videos`, `reels`, `pdf`, `radar`, `ai`, `network`, `marketplace`—must now receive accepted `file-20` plans. Blocker count must be zero.
6. Inspect each File20 plan before apply. Scope must be `shell_navigation_reference_only`; File20 may persist its own navigation Page-ID reference but must not claim or mutate native content/domain truth owned by Files 03/05/06/07/08/10/11/12/15/16/17/18.
7. Verify the File01 reconciliation plan hash has not changed between dry-run review and apply. If it changes, stop and regenerate/review the dry-run.
8. Apply File01 reconciliation only through File01's own controlled action. File20 execute callbacks must return bounded version-matched reversible receipts; File01 must persist its snapshot/receipt state before retiring `spf_page_map` and `spf_founder_user_id`.
9. After apply, confirm `spf_page_map` and the unsafe legacy Founder option are removed by File01, File01 reconciliation state is `applied`, receipt count matches the File20/File21 handoffs, and no compensation/error state exists.
10. Verify the twelve former legacy routes still resolve through File20's configured Page-ID priority after `spf_page_map` removal, while Home/News continue through File21 ownership. Verify native pages/content/tables were not copied or rewritten by File20.
11. Exercise File01 rollback in a controlled non-production/staging rehearsal where permitted. File20 must restore the exact pre-reconciliation navigation row, reject plan/receipt/state drift, and handle rollback replay idempotently.
12. Re-test the historical production incident route `/google-account-security/`; it must not return HTTP 500 or the historical `Renderer::item_visible_to_user()` undefined-method fatal.
13. Run System Check and authenticated sanitized export. File20/File00 critical Unknown/Unavailable/Incompatible states must never resolve to Healthy. Validate File00 runtime/version/health independently; static reviewed metadata is not authorization or live-health proof.
14. Verify File20 no longer registers its historical local Home-feed runtime; File21 remains canonical Home/News owner. Verify File25 visual ownership and File26 Search/Discovery/Ranking ownership remain unchanged.
15. Run Complete Repair dry-run, recovery/rollback, strict route override, privacy/no-store, Settings/LKG concurrency, PWA/accessibility/browser/device and explicit uninstall gates already required by 1.4.14.
16. Complete `STAGING-ACCEPTANCE.md` for normal promotion, or for a bounded live-incident repair record equivalent live evidence, deployment checksum/parity, File01 zero-blocker dry-run, controlled reconciliation result and live route/recovery re-test before production resolution may be claimed.

## From 1.4.14

The `1.4.14 → 1.4.15` upgrade is a root-cause correction for a live File01-B reconciliation block. File01's fail-closed behavior was correct. File21 already implemented Home/News owner reconciliation. Exact deployed File20 `1.4.14` matched repository runtime but implemented none of the three File01 owner-reconciliation hooks. Version 1.4.15 therefore changes File20, not File01: it adds a reversible adapter that moves only shell navigation Page-ID references into File20-owned settings before File01 retires its legacy map. There is no File20 database-schema change and no native companion-content migration.

## From 1.4.13

The `1.4.13 → 1.4.14` upgrade was a release/operator-truth correction discovered during a fresh review. The proven 1.4.13 Renderer runtime repair is preserved unchanged. 1.4.14 corrected stale migration/staging/changelog/rollback guidance and added permanent documentation-truth regression coverage.

## From 1.4.12

The `1.4.12 → 1.4.13` repair restored the proven-deleted `Renderer` helper block after production evidence showed the live faulting renderer matched repository source and retained calls to missing methods. Version 1.4.15 preserves that runtime repair and all 1.4.14 release-truth corrections.

## From 1.4.10 / 1.4.11

The `1.4.10 → 1.4.11` upgrade was an eighty-round File20 consolidation. It advanced settings schema to 5, removed retired local-domain runtime/configuration paths, strengthened canonical route/access/provider validation, hardened recovery/concurrency/control-plane state and completed dynamic File20 cache/uninstall ownership. Version 1.4.12 subsequently added the second eighty-round route, ownership, System Check, REST privacy, concurrency and LKG hardening.

## From 1.4.9

The `1.4.9 → 1.4.10` upgrade was canonical-ownership and health-truth hardening. It retired the residual File20 local Home-feed runtime/state under File21 ownership, published the five exact native File21 slots with single-render semantics, removed residual File20 visual runtime ownership, removed WordPress/File20 Search fallback under File26 ownership, bound provider health to actual semantic versions, prevented critical provider states from false-green Healthy, and applied that critical gate to Emergency re-enable.

It does **not** add a nineteenth Future Shell feature, activate CF modules, create companion databases, repair File00's native security defects, or migrate native companion-domain records.

## Earlier versions / no shell / another shell

Upgrade directly to the exact approved 1.4.15 artifact after verified backup and environment freeze. Do not recreate intermediate packages. Validate native-owner contracts independently, avoid duplicate pages/backends/shells, and use only integrity-valid current-format/current-schema snapshots for automatic rollback.
