# Repository Manifest — File 20

## Historical preserved source

- `SOURCE-ARCHIVE/20-sabri-unified-application-shell-1.0.0-FINAL.zip` is the immutable historical baseline.
- Root `CHECKSUMS.sha256` records that historical archive only.
- `SOURCE-PROVENANCE.md` preserves the import/provenance chain.

## Current source and release flow

- `sabri-unified-application-shell/` is the current reviewed plugin source.
- Current runtime/source version: **1.4.17**; settings schema: **5**.
- `.github/workflows/corrective-quality.yml` is the current **1.4.17 Canonical Programmatic Settings Writer** exact-head release workflow.
- `STATUS.md` records lifecycle truth. Current installable artifacts are generated from exact-head CI; CI does not itself establish deployment or operational acceptance.

## Production-package constitution

For **1.4.17**, CI derives a clean production source set, excludes tests/development-only files, writes an embedded manifest, builds one canonical plugin root, rejects traversal/duplicate/wrong-root ZIP entries, proves source/stage/extracted file-set plus SHA-256 parity, requires embedded/external manifest equality, and verifies archive SHA-256/CRC.

Canonical artifact basename:

`20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER`

The historical File20 HomeFeed producer must not exist in the installable artifact.

## Current 1.4.17 invariant

`Settings::update_programmatically()` is the single canonical trusted writer for full `sabri_shell_settings` persistence. It enforces File20-owned invariants, temporarily removes only the tab-oriented `Settings::sanitize` callback, preserves every other filter, restores the sanitizer in `finally`, and is used by File01 reconciliation, recovery repair/rollback, Emergency persistence, activation-snapshot rollback, defaults normalization and retired-state migration.

Dynamic active-sanitizer regression and a static no-direct-settings-write gate make this source invariant permanent.

## Exact-head release gates

The current quality workflow verifies PHP 7.4 and PHP 8.3 syntax plus every repository regression/adversarial suite, JavaScript syntax, JSON validity, File01 reconciliation boundaries, route/renderer ownership, recovery and Safe Mode invariants, settings concurrency, privacy/cache behavior, exact eighteen Future Shell features, and deterministic production package parity.

The Baseline Archive Integrity workflow separately protects the immutable historical source archive.

## Recovery/evidence boundary

Recovery snapshots distinguish option absence from `null`, record stored schema state, and automatic rollback requires compatible snapshot format, File20 code major and current settings schema. Schema upgrades take a pre-upgrade recovery snapshot. Current Emergency safety state and monotonic settings-row concurrency evidence are not silently rolled backward. Restored state is verified and smoke-tested.

## Native-owner and lifecycle boundary

File21 owns Home/News/feed data and rendering; File20 owns only the structural slot host. File25 owns visual truth. File26 owns Search/Discovery/Ranking. File00 remains native identity/security authority; File20 must fail closed rather than invent a replacement identity/security backend.

CF-01 through CF-06 remain conditional contract targets, not automatically activated runtime backends. Their native owners remain responsible for their domain truth.

## Live evidence boundary

The repository includes a dated 2026-08-29 Live incident closure proving that File20 `1.4.17` was deployed with exact runtime/package parity for the File01 legacy reconciliation incident and that the reconciliation then completed successfully. That evidence is **scoped to that incident**.

It does not prove that all File20 repair, rollback, Emergency, activation-snapshot, migration, LKG, browser/device, accessibility, PWA, backup/restore or operational journeys have independently passed on Live.

The repository can establish Specified/Coded/Packaged/Automated-QA evidence. Full staging acceptance, broader Live acceptance and Operational status remain separate evidence states under the governing plan.
