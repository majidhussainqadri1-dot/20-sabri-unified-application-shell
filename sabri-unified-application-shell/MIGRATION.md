# Migration and Upgrade

## Governing Rule

Normal release promotion is staging-first. A proven live production incident may use a bounded hotfix path only after live reality is frozen: exact deployed runtime/files, relevant database/schema state, runtime error, active configuration/dependencies and deployment parity where obtainable. Repository state is never assumed to equal Live. A production incident is not Resolved until the exact corrected artifact is deployed, the original live symptom is re-tested and deployed parity is confirmed.

Version **1.4.16** is repository/code/package/automated-QA truth only after its exact candidate CI passes. It is not live truth until the exact artifact is deployed and parity is re-proven.

## Upgrade to 1.4.16

1. Preserve the exact live failure evidence from deployed File20 `1.4.15`: File01 dry-run blockers were zero, controlled apply failed, verified compensation restored all 14 legacy mappings and the Founder option, all pages returned to non-quarantined state, and both File20/File21 receipt stores returned to zero.
2. Preserve the persistence root-cause evidence: Founder page 164 was published and owner-compatible; every File20 pre-update/read filter preserved proposed `navigation.founder.page_id=164`; raw database and `get_option()` both remained `0`; object-cache truth matched DB; the registered tab-oriented `Settings::sanitize()` callback therefore swallowed the trusted programmatic settings write before the adapter's pre-update pipeline.
3. Verify a restorable files/database backup and record the actually deployed File20 `1.4.15` runtime, File01 plan hash, File01 legacy map, File20 settings, active companion versions and current route behavior.
4. Install the exact deterministic `20-sabri-unified-application-shell-1.4.16-FILE01-SANITIZER-PERSISTENCE-REPAIR.zip` artifact on the approved deployment target. Confirm one canonical ZIP root, no development tests and exact source/stage/extracted manifest/SHA parity.
5. Confirm deployed File20 runtime is exactly `1.4.16`; compare deployed runtime files/package manifest against the approved exact-head artifact before doing any File01 write.
6. Re-run File01 reconciliation dry-run only. `home` and `news` must remain accepted by `file-21`; the twelve File20-owned handoffs must be accepted by `file-20` with command version `1.0.1`; blocker count must be zero.
7. Inspect each File20 plan before apply. Scope must remain `shell_navigation_reference_only`; File20 may persist its own navigation Page-ID reference but must not claim or mutate native content/domain truth owned by Files 03/05/06/07/08/10/11/12/15/16/17/18.
8. Verify the File01 reconciliation plan hash has not changed between dry-run review and apply. If it changes, stop and regenerate/review the dry-run.
9. Apply File01 reconciliation only through File01's own controlled action. File20's bounded trusted writer may suspend only the registered tab-oriented `Settings::sanitize` callback for the exact programmatic settings mutation, must explicitly enforce File20 invariants, must leave every other WordPress/core/security/concurrency/pre-update filter in force and must restore the sanitizer immediately in `finally`.
10. After apply, confirm File01 reconciliation state is `applied`, receipt count is complete, `spf_page_map` and `spf_founder_user_id` are removed by File01, all fourteen owner receipts are present in the File01 snapshot, and no compensation/error state exists.
11. Verify the twelve File20 routes now persist the expected Page IDs and still resolve after legacy-map retirement; Home/News must continue through File21 ownership. Verify native pages/content/tables were not copied or rewritten by File20.
12. Exercise rollback in a controlled non-production/staging rehearsal where permitted. The same trusted writer must restore exact pre-reconciliation File20 rows while the Settings sanitizer is reattached after each bounded write; plan/receipt/state drift must fail closed and replay must remain idempotent.
13. Re-test the historical production incident route `/google-account-security/`; it must not return HTTP 500 or the historical `Renderer::item_visible_to_user()` undefined-method fatal.
14. Run System Check and authenticated sanitized export. File20/File00 critical Unknown/Unavailable/Incompatible states must never resolve to Healthy. Validate File00 runtime/version/health independently; static reviewed metadata is not authorization or live-health proof.
15. Verify File20 no longer registers its historical local Home-feed runtime; File21 remains canonical Home/News owner. Verify File25 visual ownership and File26 Search/Discovery/Ranking ownership remain unchanged.
16. Complete `STAGING-ACCEPTANCE.md` for normal promotion, or for a bounded live-incident repair record equivalent live evidence, deployment checksum/parity, File01 zero-blocker dry-run, controlled reconciliation result and live route/recovery re-test before production resolution may be claimed.

## From 1.4.15

The `1.4.15 → 1.4.16` upgrade fixes the exact live persistence defect found only after 1.4.15 was deployed and File01 reached a zero-blocker dry-run. The 1.4.15 owner adapter itself was recognized and accepted, but WordPress Settings API sanitization re-applied the tab-oriented `Settings::sanitize()` callback to adapter-owned programmatic writes lacking `_active_tab`, replacing the proposed navigation value with the existing settings before File20 pre-update filters. Version 1.4.16 changes only the bounded File20 persistence path: command contract `1.0.1`, explicit File20 invariants, temporary suspension/restoration of that one sanitizer callback, all other filters preserved, and a permanent regression reproducing the live failure before proving execute/rollback success.

## From 1.4.14

The `1.4.14 → 1.4.15` upgrade is a root-cause correction for a live File01-B reconciliation block. File01's fail-closed behavior was correct. File21 already implemented Home/News owner reconciliation. Exact deployed File20 `1.4.14` matched repository runtime but implemented none of the three File01 owner-reconciliation hooks. Version 1.4.15 therefore changed File20, not File01: it added a reversible adapter that moves only shell navigation Page-ID references into File20-owned settings before File01 retires its legacy map. There is no File20 database-schema change and no native companion-content migration.

## From 1.4.13

The `1.4.13 → 1.4.14` upgrade was a release/operator-truth correction discovered during a fresh review. The proven 1.4.13 Renderer runtime repair is preserved unchanged. 1.4.14 corrected stale migration/staging/changelog/rollback guidance and added permanent documentation-truth regression coverage.

## From 1.4.12

The `1.4.12 → 1.4.13` repair restored the proven-deleted `Renderer` helper block after production evidence showed the live faulting renderer matched repository source and retained calls to missing methods. Version 1.4.16 preserves that runtime repair plus all 1.4.14 and 1.4.15 corrections.

## From 1.4.10 / 1.4.11

The `1.4.10 → 1.4.11` upgrade was an eighty-round File20 consolidation. It advanced settings schema to 5, removed retired local-domain runtime/configuration paths, strengthened canonical route/access/provider validation, hardened recovery/concurrency/control-plane state and completed dynamic File20 cache/uninstall ownership. Version 1.4.12 subsequently added the second eighty-round route, ownership, System Check, REST privacy, concurrency and LKG hardening.

## From 1.4.9

The `1.4.9 → 1.4.10` upgrade was canonical-ownership and health-truth hardening. It retired the residual File20 local Home-feed runtime/state under File21 ownership, published the five exact native File21 slots with single-render semantics, removed residual File20 visual runtime ownership, removed WordPress/File20 Search fallback under File26 ownership, bound provider health to actual semantic versions, prevented critical provider states from false-green Healthy, and applied that critical gate to Emergency re-enable.

It does **not** add a nineteenth Future Shell feature, activate CF modules, create companion databases, repair File00's native security defects, or migrate native companion-domain records.

## Earlier versions / no shell / another shell

Upgrade directly to the exact approved 1.4.16 artifact after verified backup and environment freeze. Do not recreate intermediate packages. Validate native-owner contracts independently, avoid duplicate pages/backends/shells, and use only integrity-valid current-format/current-schema snapshots for automatic rollback.
