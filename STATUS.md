# File 20 Status

## Current source truth

File 20 is **Sabri Unified Application Shell 1.4.17**, settings schema **5**. The immutable historical `1.0.0 FINAL` archive remains in `SOURCE-ARCHIVE/` for provenance only and is not the current runtime candidate.

The current source implements the approved File 20 v5 / Future Shell specification, including the exact eighteen Future Shell enhancements. CF-01..CF-06 remain conditional native-owner integration targets; they are not silently promoted into File 20-owned domain backends.

## Truthful lifecycle status

| Stage | Current evidence |
|---|---|
| Specified | **Complete** — File 20 v5 / central-plan-harmonized specification plus exact 18-enhancement Future Shell amendment |
| Coded | **Complete for the approved repository scope** — runtime/source version `1.4.17` |
| Packaged | **Green repository evidence** — exact-head CI builds the deterministic production-only `1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER` artifact and proves source/package parity |
| Automated QA | **Green repository evidence** — PHP 7.4/8.3 full regression, static ownership/security/privacy checks, deterministic package parity and Baseline Archive Integrity pass on the current 1.4.17 source line |
| Staging accepted | **Not established for the whole File 20 acceptance matrix**. A scoped File01 reconciliation incident has separate exact-deployed Live evidence; that does not replace full File20 staging acceptance. |
| Live deployed | **Scoped evidence only** — File20 `1.4.17` was deployed and exact package/runtime parity was verified for the 2026-08-29 File01 reconciliation incident. This is not a claim of whole-File20 production acceptance. |
| Operational | **Not established for the whole File 20 scope** — full monitoring/SLO/support/backup-restore and all representative operational journeys remain separate evidence gates. |

## Current repository assurance

The 1.4.17 source centralizes trusted `sabri_shell_settings` writes in `Settings::update_programmatically()`. File01 reconciliation, recovery repair/rollback, Emergency persistence, activation-snapshot rollback, defaults normalization and retired-state migration use the canonical writer. Dynamic active-sanitizer regression plus a static no-direct-settings-write gate preserve this invariant.

The shell boundaries remain unchanged: File21 owns Home/News/feed truth; File25 owns visual truth; File26 owns Search/Discovery/Ranking; File00 remains identity/security authority. File20 owns the global application shell, navigation/layout resolution, structural slots, continuity, Safe Mode, repair and rollback surfaces.

## Scoped Live incident record

The 2026-08-29 File01 reconciliation incident is **LIVE VERIFIED / RESOLVED** on File20 `1.4.17`. The deployed runtime/package parity check reported 76 manifest files and 76 live runtime files with no observed missing/extra runtime evidence. The successful reconciliation produced 12/12 File20 route mappings and retained File21 ownership for Home/News.

That incident closure expressly does **not** prove every other File20 canonical-writer path or the complete File20 operational acceptance matrix on Live.

## Remaining non-repository gates

Full File20 acceptance still requires the plan-defined environment evidence where not already independently proven: Hostinger-equivalent fresh install/upgrade, active production theme compatibility, actual File00–26 provider contracts, responsive/RTL visual acceptance, keyboard/screen-reader testing, browser/device matrix, PWA lifecycle, full Safe Mode/Repair/rollback exercise, backup/restore proof, Founder acceptance and controlled production promotion/re-test.

Repository completion must never be used as a substitute for these deployment and operational gates.
