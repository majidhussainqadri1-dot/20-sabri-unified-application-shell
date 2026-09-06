# File 20 — Sabri Unified Application Shell

Independent repository for **File 20 — Sabri Unified Application Shell** of the **Sabri Social Homeopathy Platform**.

## Current lifecycle

- Current reviewed source baseline: **Version `1.4.17`** on `main` at `ed9a43d62a3c1b9851ecc38f9fac195f6a39ea73` before the 2026-09-05 twenty-round review cycle.
- Active review branch: `review/file20-twenty-round-2026-09-05`.
- Settings schema: **5**.
- File 01 reconciliation command contract: **1.0.1**.
- Future Shell v5 remains exactly **18 approved enhancements**; no nineteenth feature is added.
- File 20 remains structural shell/navigation/layout/slots/diagnostics/recovery only. Native companion domains keep identity, authentication, Home/News, communication, notification, appointments, marketplace, Search/Discovery/Ranking, visual, clinical, financial, media, analytics and localization truth.
- Repository/code/package/automated-QA evidence is not staging/live/operational acceptance.

## Current 1.4.17 source truth

Version 1.4.17 centralizes trusted full `sabri_shell_settings` writes in `Settings::update_programmatically()`. File01 reconciliation, PlanV4 repair/rollback, Emergency persistence, activation-snapshot rollback, defaults normalization and retired-state migration use that canonical writer. The dynamic active-sanitizer regression and static no-direct-settings-write gate preserve this invariant.

The 2026-08-29 File01 reconciliation incident has separate scoped Live evidence proving File20 1.4.17 deployment/package parity for that incident and successful reconciliation. That evidence does **not** establish whole-File20 staging, Live or Operational acceptance.

## Twenty-round review discipline

The 2026-09-05 review cycle follows the governing sequence for every numbered round:

`Complete Review → Defect Ledger Freeze → Fix All Round Defects → Regression/Exact-Head QA → Next Round`

No defect is corrected while its review round is still in progress. The next round begins only after all defects from the completed round are corrected and re-tested.

## Quality boundary

Current exact-head quality gates cover PHP 7.4/8.3 syntax, every repository regression/adversarial suite, JavaScript/JSON/CSS/static ownership/routing/recovery/privacy checks, and deterministic production-only packaging with canonical ZIP root/path safety, exact source/stage/extracted SHA-256 parity, manifest equality and CRC validation.

Hostinger-equivalent staging, real companion-runtime/browser/device/accessibility behavior, backup/restore/rollback rehearsal, Founder acceptance, controlled live deployment and operational monitoring remain separate gates.
