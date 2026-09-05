# Migration and Upgrade

## Governing rule

Normal release promotion is staging-first. For a proven production incident, first freeze exact deployed runtime/files, database/schema state, relevant runtime error, active configuration/dependencies and deployment parity where obtainable. Repository state is never assumed to equal Live. A production incident is not Resolved until the exact corrected artifact is deployed, the original live symptom is re-tested and deployed parity is confirmed.

Repository, staging and Live are separate evidence realities.

## Current upgrade target — 1.4.17

1. Use the exact deterministic `20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER.zip` artifact only after exact-head QA succeeds.
2. Record source HEAD, artifact SHA-256, source manifest and the actually deployed previous version before replacement.
3. Prove a restorable files/database backup and, where staging is used, test fresh install plus the actual supported upgrade path.
4. Confirm every trusted full `sabri_shell_settings` programmatic mutation routes through `Settings::update_programmatically()`; the static no-direct-write gate and active-sanitizer regression must pass.
5. Preserve File20 invariants, Emergency authorization, recovery verification, concurrency/audit evidence and all canonical ownership boundaries.
6. After deployment, prove exact deployed package/runtime parity before relying on new behavior.

## File01 reconciliation evidence and current procedure

The 1.4.15 live controlled apply failed after a zero-blocker dry-run, and verified compensation restored the pre-apply state. Forensic evidence showed Founder page 164 was published and owner-compatible; every File20 pre-update/read filter preserved proposed `navigation.founder.page_id=164`; raw database and `get_option()` both remained `0`; object-cache truth matched the database. The root cause was the registered tab-oriented `Settings::sanitize()` callback swallowing the trusted programmatic settings write before File20's pre-update pipeline.

Version 1.4.16 corrected the bounded reconciliation write path. Version 1.4.17 then centralized the same safe persistence invariant across all identified trusted File20 full-settings writers.

For any current File01 reconciliation re-test:

1. Deploy the exact approved **1.4.17** artifact and prove deployed parity.
2. Run File01 dry-run only. `home` and `news` must remain File21-owned. The twelve File20 handoffs must be accepted by `file-20` with command version `1.0.1`; blocker count must be exactly zero.
3. Each File20 plan must remain `shell_navigation_reference_only`; File20 may persist only its own navigation Page-ID references and must not claim native content/domain truth from Files 03/05/06/07/08/10/11/12/15/16/17/18.
4. Record the reviewed File01 plan hash. If it changes before apply, stop and regenerate/review the dry-run.
5. Apply only through File01's controlled action. Verify bounded reversible receipts and snapshot evidence before File01 retires its own legacy options.
6. After apply, verify File01 state is `applied`, all fourteen owner receipts exist, `spf_page_map` and `spf_founder_user_id` are retired by File01, no compensation/incomplete state remains, all twelve File20 route references persist, and Home/News remain File21-owned.
7. Rehearse rollback in an approved non-production environment: prior File20 navigation rows must restore through the canonical writer, sanitizer restoration must be proven, plan/receipt/state drift must fail closed and replay must remain idempotent.

## Historical live Renderer regression

Re-test `/google-account-security/` after any production promotion that could affect File20. It must not return HTTP 500 and the historical `Sabri\UnifiedShell\Renderer::item_visible_to_user()` undefined-method fatal must remain absent.

## Ownership and migration boundaries

- File21 remains canonical Home/News owner; File20's historical local Home-feed runtime is retired.
- File25 remains visual authority; File20 owns structural shell/layout only.
- File26 remains Search/Discovery/Ranking authority; File20 must not provide an alternate search backend.
- File00 remains identity/authorization authority; static reviewed metadata is never runtime authorization proof.
- Automatic rollback scope remains File20-owned options only. Shared WordPress front-page options are not silently restored by File20.
- No dual-write or foreign table/content mutation is permitted unless separately designed, approved, time-bounded and migration-tested.

## Older versions

Versions 1.4.16 and earlier remain historical migration/root-cause evidence. Do **not** direct operators to an obsolete artifact as the current upgrade target. Upgrade to the exact approved current artifact after backup/environment freeze and validate native-owner contracts independently.

## Acceptance boundary

Repository/CI/package success does not establish staging, Live or Operational acceptance. Complete `STAGING-ACCEPTANCE.md`, prove exact deployment parity, re-test the original affected workflows, and preserve rollback/monitoring evidence before broader production resolution is claimed.
