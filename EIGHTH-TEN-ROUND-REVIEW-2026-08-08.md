# File 20 — Eighth Independent Ten-Round Corrective Review

Date: 2026-08-08
Base `main`: `44e2bdae557332e6e9988787ebdd3dabb0adc528` (`1.4.7`)
Candidate: `1.4.8`
Branch: `audit/file20-eighth-ten-round-2026-08-08`
Pull request: `#23`

This is a fresh audit over merged 1.4.7 against the current File 20 v5.0 Future Shell plan, the central File-20 recovery/QA constitution and current repository source. The preceding seventh audit is historical evidence only and was not reused as proof. Every discovered repository-owned defect was corrected before the next review round. The approved Future Shell product scope remains exactly eighteen enhancements.

## Round results

### Round 1 — File 25 visual ownership / stale Appearance editor — DEFECT FOUND AND CORRECTED

Finding: File 20 still exposed an Appearance administration surface containing color/density/radius-era controls even though File 25 is the canonical visual owner and File 20 settings no longer accept those writes.

Correction: added `FutureShellV5EighthHardening` contract `1.0.8`; direct Appearance access is retired/redirected, the stale tab is suppressed, and historical appearance values remain migration-only evidence rather than current File 20 authority.

### Round 2 — Sensitive task layout not WordPress-subdirectory safe — DEFECT FOUND AND CORRECTED

Finding: the earlier sensitive task classifier compared root paths and could miss `/wordpress/account-security`-style routes on a subdirectory installation.

Correction: added a final site-relative path classifier using the current `home_url('/')` path, preserving Minimal presentation for governed account/membership/system task routes at both root and subdirectory installs. Native modules retain authorization authority.

### Round 3 — System Check evidence model incomplete/dormant — DEFECT FOUND AND CORRECTED

Finding: multiple hardening layers emitted `sabri_shell_system_check_sections`, but the primary System Check did not consume them; rows also lacked required ID, severity, evidence, last-run, affected-surface and remediation fields.

Correction: System Check now emits structured evidence, consumes bounded/sanitized registered sections, retains explicit Unknown/Unavailable/Incompatible states rather than promoting them to PASS, and keeps historical label/value/status compatibility for the admin table.

### Round 4 — No controlled sanitized System Check export — DEFECT FOUND AND CORRECTED

Finding: structured health evidence had no controlled operator export surface.

Correction: added authenticated `manage_options` REST export at `/sabri-shell/v1/system-check/export`, using bounded sanitized System Check data plus `private, no-store` and `X-Robots-Tag` response controls.

### Round 5 — Bounded audit retention and privacy erasure could invalidate the hash chain — DEFECT FOUND AND CORRECTED

Finding: once the 500-event bound truncated older records, verification still assumed a zero genesis hash, so a valid retained log could report invalid. Privacy erasure also changed actor/context without rebuilding hashes; append did not first fail closed on an invalid existing chain.

Correction: added a retained-chain anchor, owner-token-locked anchor advancement, fail-closed pre-append verification, bounded pruning, and deterministic authorized rehash after privacy erasure. Existing bounded logs can migrate their first retained predecessor into the anchor.

### Round 6 — Hidden mbstring dependency in operational evidence — DEFECT FOUND AND CORRECTED

Finding: assurance evidence called `mb_substr()` unconditionally although File 20 declares PHP 7.4+, not mbstring as a required extension; failure reporting itself could fatal on a server without mbstring.

Correction: audit/assurance truncation now uses `mb_substr` when available and a bounded `substr` fallback otherwise. Sanitized-key collisions are also preserved rather than silently overwritten.

### Round 7 — Complete Repair normalization was a no-op and dry-run lacked exact/planned diff — DEFECT FOUND AND CORRECTED

Finding: `plan_v4_normalize_settings` only read settings and returned `is_array`; it did not normalize persisted state. Repair preview listed actions but not their bounded exact/planned differences.

Correction: repair now computes bounded/redacted diffs, performs real idempotent default/schema normalization, preserves settings-row concurrency, creates a pre-repair snapshot, records per-action outcomes and refuses false success on a failing operation.

### Round 8 — Safe Mode/Emergency lifecycle below governing requirement — DEFECT FOUND AND CORRECTED

Finding: query Safe Mode accepted a plain manager query flag without nonce binding. Emergency Disable stored only a boolean and lacked reason/actor/time/review/audit plus health/cache-gated re-enable.

Correction: administrator query Safe Mode is nonce-bound; the configuration constant remains the highest-priority break-glass path. Emergency Disable requires reason and stores actor/time/review evidence + audit; re-enable requires valid audit/provider health and purges File 20/cache state before success.

### Round 9 — Recovery snapshot/schema/activation/admin paths were not fully harmonized — DEFECT FOUND AND CORRECTED

Finding: automatic rollback compared plugin major only, recovery snapshots omitted separate Future Shell settings, legacy activation snapshot lacked current integrity/source/schema evidence and could restore shared WordPress front-page options, while visible admin Repair/Rollback/Emergency actions still pointed to legacy one-click handlers.

Correction: rollback now requires compatible code major **and exact current settings schema**, creates a pre-rollback snapshot, restores File-20/Future-Shell-owned state only, invalidates/purges caches, smoke-tests and audits. Snapshots carry source/version/schema/contract/route/feature fingerprints. Activation snapshot format 2 is integrity protected and never writes `page_on_front`/`show_on_front`. Admin Repair/Rollback/Emergency callbacks are replaced by hardened controllers with dry-run/snapshot/evidence-aware UI.

### Round 10 — Release identity, inherited QA and active evidence drift — DEFECT FOUND AND CORRECTED; EXACT-HEAD GATE REQUIRED

Finding: the new corrections initially remained labeled 1.4.7, inherited preservation tests/workflows still expected the seventh release, the baseline workflow still used outdated checkout/current-version assumptions, and active lifecycle/release documentation still described 1.4.7 or much older candidates. During this closure review a locale edge case was also found: duplicate-shell PASS/FAIL truth depended on an English display string and could become a false FAIL under translation.

Correction: advanced runtime/stable tag/workflow/package identity to **1.4.8**, added dedicated eighth-pass and locale-independent duplicate-shell regressions, advanced inherited 1.4.x preservation tests, modernized baseline checkout, aligned README/STATUS/MANIFEST/MIGRATION/STAGING/release-directory/verification evidence, and made duplicate-shell truth derive from a structured active-plugin list rather than translated display text. The deterministic production artifact is `20-sabri-unified-application-shell-1.4.8-EIGHTH-TEN-ROUND-HARDENED.zip`.

Round 10 is not considered releasable merely because these files were edited. The **exact final PR head** must pass PHP 7.4/8.3 syntax, every `run*.php` regression/adversarial suite, JavaScript/JSON/CSS/static ownership/recovery/privacy checks, deterministic production-only package construction, ZIP path/root/duplicate safety, exact source/stage/extracted file-set and SHA-256 parity, manifest equality, ZIP CRC and artifact upload. Any failure must be corrected and rerun before merge.

## Round count

Defects were found and corrected in rounds **1, 2, 3, 4, 5, 6, 7, 8, 9 and 10**.

Repository/code/package release-blocker count can be declared zero only after the final exact-head workflow is green and the merge/main head passes again. Hostinger staging, Founder acceptance, live deployment and operational acceptance remain separate and are not claimed by this review record.
