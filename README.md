# File 20 — Sabri Unified Application Shell

Independent repository for **File 20 — Sabri Unified Application Shell** of the **Sabri Social Homeopathy Platform**.

## Current lifecycle

- Main baseline before this audit: `1.4.4` at `a7cc344901473d5a45ec2fed53044385b049e200`.
- Current fifth-audit candidate: `1.4.5` on `audit/file20-fifth-ten-round-2026-08-08`.
- Future Shell v5 approved scope: exactly **18 enhancements**.
- Future Shell contract: `1.0.0`; corrective layers: `1.0.1`, `1.0.2`, `1.0.3`, `1.0.4`, and fifth hardening `1.0.5`.
- Status: repository/code candidate under exact-head QA; Hostinger staging, Founder acceptance, live deployment and operational acceptance remain separate gates.

## Canonical responsibilities

File 20 owns the global structural application shell, navigation/layout resolution, integration slots, degraded/recovery continuity and File-20-owned shell preferences. It does **not** duplicate native domain backends or become the owner of membership, authentication, publishing, messaging, notifications, appointments, marketplace, clinical data, Search/Discovery/Ranking, security governance or File 25 visual truth.

## Fifth ten-round audit — 1.4.5

This audit reopens the already merged 1.4.4 baseline and checks source, cross-file boundaries, release rings, documentation, migration/staging evidence and deterministic packaging afresh.

Corrections in the candidate include:

- production ZIP excludes development `tests/` and other declared development-only directories;
- ZIP path traversal, duplicate-entry and canonical-root checks run before extraction;
- clean production source, clean staging tree and extracted ZIP must have an identical file set and SHA-256 manifest;
- embedded `MANIFEST.sha256` must equal the external source manifest;
- the five-state Future Shell release-ring evaluator supports `Disabled`, `Internal`, `Staging`, `Limited` and `General` exactly;
- `Internal` remains manager-enabled by default but may also consume the explicit fail-closed `sabri_shell_future_internal_principal_allowed` contract from the canonical identity/entitlement layer;
- release-ring REST configuration remains `manage_options` only;
- repository, migration, staging, package and changelog evidence is being advanced to the same `1.4.5` release identity.

No nineteenth Future Shell feature is added.

## Quality boundary

Repository checks establish source/package/automated-QA status only. Real WordPress/Hostinger staging, real companion contracts, PWA lifecycle, browsers/devices, accessibility, cache, backup/restore, rollback and production acceptance remain separate gates. A green repository run must never be described as live or operational acceptance.
