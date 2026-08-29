# File 20 v1.4.17 — Canonical Programmatic Settings Writer

## Post-merge repository finding

After 1.4.16 merged green, a fresh exact-HEAD source review found that the live-proven Settings API sanitizer hazard had been corrected only inside the File01 adapter. Other trusted File20 full-settings mutation paths still called `update_option()` directly and could encounter the same tab-oriented sanitizer whenever it was registered during admin workflows. This is a source-level finding; it does not assert those additional paths were observed failing on Live.

## Correction

`Settings::update_programmatically()` is now the single canonical trusted writer. It enforces File20 invariants, temporarily removes only `Settings::sanitize`, preserves every other filter, restores the sanitizer in `finally`, and is used by File01 reconciliation, recovery repair/rollback, Emergency persistence, activation-snapshot rollback, defaults normalization and retired-state migration.

A dynamic active-sanitizer regression and a static no-direct-write gate make this invariant permanent.

## Evidence boundary

Repository/CI/package completion remains separate from deployment and Live verification.
