# File 20 — Ninth Independent Ten-Round Corrective Review

Date: 2026-08-08
Base `main`: `a8c3b959d0fc9b791501db69fd81ed55434e781c` (`1.4.8`)
Candidate: `1.4.9`
Branch: `audit/file20-ninth-ten-round-2026-08-08`
Pull request: `#24`

This is a fresh audit over merged 1.4.8 against the current File 20 v5.0 plan, central routing/recovery/QA law and current repository source. Previous green runs are historical evidence only. Every discovered repository-owned defect was corrected before advancing. The approved Future Shell product scope remains exactly eighteen enhancements.

## Round results

### Round 1 — File25 visual ownership at persisted-default level — DEFECT FOUND AND CORRECTED
File20 had retired the Appearance editor but `Defaults::settings()` still created an `appearance` group during fresh install/normalization. Settings schema advanced to 4 and fresh File20 visual state was removed. Existing unknown legacy values remain migration evidence rather than being destructively deleted.

### Round 2 — Emergency state direct-write bypass — DEFECT FOUND AND CORRECTED
Generic settings writes could still change `emergency_disabled` outside the hardened reason/actor/review/audit lifecycle. A final option guard now blocks/audits direct transitions; only `SafeMode::set_emergency_disabled()` authorizes the canonical write.

### Round 3 — Recovery snapshot could not distinguish absence from null — DEFECT FOUND AND CORRECTED
Old PlanV4 recovery snapshots stored raw `get_option(..., null)` values, making absent and null ambiguous. Snapshot format 2 now stores `exists + value`; legacy ambiguous snapshots are automatic-rollback incompatible/read-only.

### Round 4 — Rollback target retention/TOCTOU race — DEFECT FOUND AND CORRECTED
Rollback preview occurred before lock; creating the pre-rollback snapshot could evict the oldest selected target from the 10-snapshot retention list. Execution now revalidates compatibility/integrity under lock, holds the target in memory, then creates the pre-rollback snapshot.

### Round 5 — Repair preview-to-execute settings race — DEFECT FOUND AND CORRECTED
Expected settings-row version was checked before the recovery lock only. It is now checked again under lock before snapshot/write, and the repair plan is regenerated; mismatch returns 409 without writes.

### Round 6 — Required page-map repair action missing — DEFECT FOUND AND CORRECTED
Complete Repair lacked a separately selectable Page-map repair. Added dry-run stale File20 Page-ID quarantine: only configured IDs no longer pointing to published WordPress Pages are changed to zero. No page/content/companion ownership is modified. Programmatic repair writes advance audited settings-row evidence.

### Round 7 — URL override policy below governing security law — DEFECT FOUND AND CORRECTED
Generic URL sanitization allowed HTTP, arbitrary external hosts and query/fragment-bearing overrides. Added `RouteSecurity`: safe relative path or HTTPS only, same-site by default, explicit external-host allowlist, no credentials/query/fragment/protocol-relative/CRLF, persistence quarantine and read-time legacy revalidation.

### Round 8 — Canonical route precedence violated — DEFECT FOUND AND CORRECTED
An arbitrary companion destination callback could resolve before shortcode/archive/slug. Navigation now follows configured/registered published Page ID → shortcode page → archive → approved slug → strict validated override → unavailable. Registered companion page maps remain Page-ID evidence; arbitrary callback URLs cannot silently preempt the sequence.

### Round 9 — Explicit uninstall purge incomplete — DEFECT FOUND AND CORRECTED
`delete_on_uninstall=true` removed only early File20 options, leaving later audit/recovery/FutureShell/Emergency/job state. The opt-in allowlist now covers current File20-owned operational state and schedule while default uninstall remains non-destructive and companion pages/content/tables/options remain untouched.

### Round 10 — Cross-system rollback safety + release/QA closure — DEFECT FOUND AND CORRECTED; EXACT-FINAL-HEAD GATE REQUIRED
The Round-2 Emergency guard could reject a rollback Emergency change while recovery failed to verify the write; recovery also captured/restored the optimistic settings-row version, allowing the counter to move backwards. Automatic rollback now preserves the current Emergency flag, excludes Emergency lifecycle metadata and settings-row counter from restoration, verifies every restored option after write, advances settings-row evidence monotonically after real settings restoration, purges caches and smoke-tests. Snapshot metadata still records safety/concurrency evidence without making it rollback state.

Round 10 also advances runtime/stable-tag/workflow/package identity to `1.4.9`, adds `FutureShellV5NinthHardening 1.0.9`, adds the ninth-pass adversarial suite, advances inherited preservation tests from 1.4.8/schema3 to current truth, and aligns active README/STATUS/MANIFEST/MIGRATION/STAGING/release/verification documentation. During CI closure, the first 1.4.9 run also exposed stale preservation assertions and an isolated contract-harness dependency in published-page validation; those were corrected without weakening the production Page-type validation when the WordPress helper exists.

The final PR head must pass PHP 7.4/8.3 syntax, every repository regression/adversarial suite, JS/JSON/CSS/static ownership/routing/recovery/privacy checks, deterministic production-only package construction, canonical ZIP root/path/duplicate safety, source/stage/extracted file-set and SHA-256 parity, embedded/external manifest equality, ZIP CRC and artifact upload. Any failure remains Round 10 and must be corrected/re-run before merge.

## Round count

Defects were found and corrected in rounds **1, 2, 3, 4, 5, 6, 7, 8, 9 and 10**.

Repository/code/package release-blocker count can be declared zero only after the exact final PR head is green and merged `main` passes the same quality/baseline gates again. Hostinger staging, Founder acceptance, live deployment and operational acceptance remain separate and unclaimed.
