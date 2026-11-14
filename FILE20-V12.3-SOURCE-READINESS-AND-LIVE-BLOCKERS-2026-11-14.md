# File 20 — V12.3 source-readiness and exact-state execution ledger

**Date:** 14 November 2026 (Pakistan) · **Status:** SOURCE AUDIT / UNMERGED · **Authority:** Sabri-Website-Master-Plan-V12.3.docx, Chapters 1–3, 8–12.  
**Execution plan:** File20-V12.3-Comprehensive-Completion-Plan-2026-11-14.docx (source work breakdown; not a release approval).

## Five realities — not interchangeable

| Evidence | Verified source or limitation |
|---|---|
| Repository main HEAD | `8a4dbcaf4fef8e926b9b834ecfde16c21a0f00ca`, source 1.4.17, settings schema 5 |
| Proposed PR #43 head | `a054094d5ad3de5916f160333749a76e909e16a1`, source 1.4.18; open **Draft**, not merged |
| Deployed Version | **UNVERIFIED**; 29 Aug 2026 historical File01 parity is not current proof |
| DB Version / wp_options | **UNVERIFIED**; no authenticated read access |
| Migration State | **UNVERIFIED** |
| Live Verification Status | **NOT PERFORMED** for V12.3 candidate |
| Access blocker | WordPress.com Jetpack reports `site_disconnected`; WPVibe connection request returned HTTP 530. The 530 alone does not identify the cause. |

**Hard stop:** The exact deployed code is unverified. All repository-based diagnosis is provisional. This audit is not a live repair. No plugin ZIP is being installed, no DB is changed, and PR #41/#42/#43 are not being merged.

## Completed source-audit checkpoints

1. Reviewed **the actual V12.3 execution-plan Word file** (G0 / 26 work packages) and governing plan requirements CV-026–CV-036.
2. Revalidated PR #41 Welcome (hold, File13 ownership), PR #42 experiment CAS (non-executable preflight), PR #43 Help/Forward/Recent/Saved/security (source candidate only).
3. Recorded **14** canonical menu destinations, **7** settings groups, all **23** exact keys, **18** approved Future Shell feature keys, and **26** work packages in `contracts/file20-v12-3-readiness.json`.
4. Added runnable source gate `tests/run-file20-v12-3-readiness.php` (run by existing `tests/run*.php` exact-head GitHub Actions workflow). It fails CI on missing or duplicated keys, changed ownership/provenance claims, or accidental promotion of unverified live states.
5. Kept settings controls as explicit **unverified owner providers**, not phantom functional settings.

## Source gaps: claims limited to the inspected source lines

| ID | Evidence / classification | What can be done after required owner/parity evidence |
|---|---|---|
| F20-D-001 | V12.3 7-group front-end Settings Hub differs from WordPress `admin/class-admin.php` admin tab controls. **Verified source-level gap; no Live diagnosis.** | Versioned per-control owner schema, safe shell UI and conflict/rollback states |
| F20-D-002 | `includes/class-renderer.php::render_primary_nav()` uses fixed first six visible entries and rest in More, rather than measured responsive fit. **Verified source-level gap.** | Width-aware accessible menu; six width/locale/no-JS test fixtures |
| F20-D-003 | `includes/class-future-shell-v5.php` advertises PWA theme `#16803a`, while V12.3 identifies Sabri Green `#087A4E`. **Source discrepancy; owner File25 and deployed output remain unverified.** | Confirm latest File25 token and change all authoritative derived surfaces in a tested implementation batch |

These 3 findings are **not 3 proven live defects**. No live symptom can be marked resolved here.

## Owner-proof dependencies, in order

- **File00/02/03/09/17/19/CF05/CF06/05/12/10/11:** supply versioned genuine per-control authority, effective state, permissions, save/reset/revocation. File20 displays the answer; it does not create another datastore.
- **File26:** settings-search alias normalization; Urdu/Roman Urdu/approved words, no raw personal-query or hidden-category indexing.
- **File25:** token and accessible visual component authority, 840px internal two-pane layout and focus contrast; File24 independent evidence.
- **File02/24:** exact Account Safety rewrite and unique published public guidance page required before their conditional navigation is visible.
- **File13 Welcome:** PR #41 remains on hold unless Founder resolves existing canonical ownership and staged migration.
- **File14 CAS:** PR #42 remains source preflight only; no SQL restore authorization or last-writer evidence.
- **File05/12/17/22/CF02:** Help/Recent/Saved producer contracts and privacy/capability proof required for PR #43 consumer.

## Next controlled steps and acceptance

**G1:** obtain active plugin runtime version; exact deployed files + manifest checksum; WP/PHP/theme/plug-in versions; schema tables/columns/indexes; `sabri_shell_settings` and migration options; recent redacted logs. If main and deployed differ: stop application diagnosis and open parity audit.  
**G2:** obtain and freeze owner contracts, especially 23 controls and File25 visual token.  
**G3–G8:** implement **separate, reviewable**, V12.3-ID-tagged source batches (responsive menu, Settings Hub/search, history, degraded state, PWA/owner integrations). No CI-green equals deployment.  
**G9:** full Review 1 → frozen defects → root-cause fixes → regressions → exact-head CI; then full Review 2 with concurrent authorization, stale migration and rollback.  
**G10–G12:** deterministic installable package checksum → real Staging + QA-15…24 + backup/restore → Founder-approved limited Live → live re-test/parity → handoff/monitoring.

## Session defect and blocker accounting

| Classification | Count | Corrected | Open |
|---|---:|---:|---:|
| Proven new live defects | 0 (live unavailable) | 0 | Not testable |
| Source-readiness gap candidates | 3 | 0 | 3 |
| Named cross-module / environment blockers | 7 | 0 | 7 |
| New runtime PHP/JS fixes | 0 | 0 | Not attempted |

**Gate:** source-only audit plus regression; **NO MERGE, NO DEPLOY, NO OPERATIONAL ACCEPTANCE**.  
The subsequent team must preserve five distinct evidence fields: Repository HEAD / Deployed Version / DB Version / Migration State / Live Verification Status.
