# Changelog

## 1.4.2 — 2026-08-08 — Second Independent Ten-Round Corrective Hardening

- Completed a second independent ten-round review over the merged 1.4.1 baseline; this pass did not reuse the first audit as proof of completion.
- Made protected-route classification and service-worker exclusions WordPress-subdirectory aware.
- Added explicit `410` PWA virtual-route retirement when PWA is disabled, so installed workers do not mistake ordinary HTML for a live control manifest.
- Derived PWA manifest/theme colors from File 25's validated visual contract and service-worker cache identity from `SABRI_SHELL_VERSION`.
- Preserved omitted feature/recovery/privacy values during partial Future Shell settings updates and failed malformed explicit feature rules closed.
- Bounded circuit-breaker metadata to 64 active/recent states and removed expired states before health reporting.
- Added a dependency-ordered editable-context guard so global `Ctrl/Cmd+K` cannot hijack inputs or rich editors.
- Stopped dynamic provider private-path filters from being silently persisted into File 20 options; they are evaluated per request for SW/client parity.
- Rejected Split Workspace in Minimal and Immersive modes while preserving native provider hooks elsewhere.
- Consumed File 25 border/radius/shadow/focus tokens in Future Shell controls and scoped File 20 spacing/reduced-motion styling to File-20-owned surfaces.
- Updated plugin/readmes/tests/workflow/deterministic package identity to `1.4.2` and added `SECOND-TEN-ROUND-REVIEW-2026-08-08.md`.
- Hostinger staging, Founder acceptance, live deployment and operational acceptance remain separate evidence gates.

## 1.4.1 — 2026-08-08 — Ten-Round Corrective Hardening

- Completed ten independent post-implementation review rounds against the governing File 20 plan, Future Shell v5 scope, privacy/security boundaries and deterministic release law.
- Made release-ring state fail closed after filters and converted malformed persisted rings to Disabled instead of General.
- Replaced unconditional Future Shell footer markup with current-ring-aware output.
- Hardened PWA scope/private-route policy, no-store control delivery, deactivation cleanup and browser self-unregister behavior if File 20 stops serving the control manifest.
- Versioned/scrubbed Recent & Resume and limited capture to server-classified public, query/hash-free routes.
- Limited Smart Navigation pins and predictive prefetch to bounded public shell destinations.
- Corrected last-known-good capture to snapshot the previous compatible settings state and reject automatic cross-version/schema restoration.
- Added dialog focus restoration, editable-region shortcut guards, `aria-pressed` state and desktop-only Split Workspace focus/Escape behavior.
- Bounded PerformanceObserver lifetime after the local measurement snapshot.
- Removed Future Shell visual-token ownership and global contrast/data-saver overrides that could affect File 25 or native content.
- Updated release identity, regression tests and deterministic package gates to `1.4.1`.
- Hostinger staging, Founder acceptance, live deployment and operational acceptance remain separate evidence gates.

## 1.4.0 — 2026-08-08 — Future Shell v5 / 18 Enhancements

- Added all eighteen approved modern application-shell enhancements: Command Palette, PWA, Offline/Weak Network, Data Saver, Recent & Resume, Circuit Breaker, Last-Known-Good recovery, Performance Guardian, Smart Navigation, Keyboard Layer, Focus Mode, Split Workspace, Adaptive/Foldable Shell, View Transitions, bounded Predictive Prefetch, Language/Direction control, Accessibility Center and Release Rings.
- Preserved File 00/22 authorization, File 19 one-bell, File 25 visual ownership, File 26 search ownership and all existing central-plan layout/recovery contracts.
- Added Future Shell regression checks and deterministic package/CI evidence.

## 1.3.1 — 2026-08-07

- Completed a fresh four-plan review over the merged 1.3.0 baseline.
- Enforced once-per-session Welcome invocation plus 30-day post-dismissal suppression.
- Removed dormant native WordPress search fallback and stale bottom-strip destination metadata.
- Hardened desktop top-navigation overflow into the existing More disclosure.
- Aligned release documentation, regression gates and deterministic package identity.

## 1.3.0 — 2026-08-07

- Harmonized File 20 against all four governing plans.
- Replaced native WordPress search with a fail-closed File 26 contract.
- Removed the duplicate mobile bottom navigation and added one-row More overflow.
- Added 30-day Welcome invocation/frequency control while preserving File 13 visual ownership.
- Ported the authorized File 23 Publishing Dashboard entry onto current main.
- Enforced File 25 visual ownership, green continuity fallback, single-free-tier and donor-neutral shell policy.
- Added Smail/file-transfer/download UI-only ownership contracts; no duplicate backend.


## Unreleased — Shared Context Navigation

- Added one File 20-owned Back + Home control for internal public pages, positioned RTL-first on the right.
- Kept Back safe and deterministic through a bounded same-origin session stack, a same-origin referrer fallback, and a canonical section/Home fallback.
- Preserved a functional no-JavaScript Back link and the canonical Home link.
- Deliberately omitted a permanent generic Forward control; native modules remain responsible for meaningful Previous/Next content controls.
- Added 44×44 minimum touch targets, visible keyboard focus, reduced-motion handling, duplicate-output protection, PHP 7.4/8.3 syntax coverage, JavaScript syntax checks, CSS balance checks, and dedicated static security regressions.
- Hostinger staging, visual acceptance, and live deployment remain pending.

## 1.2.0 Publication Layout R3 — 2026-08-04

- Accept File 21's neutral `sabri-hnf-content-integrity-single` signal for legacy Markdown articles without taking content ownership.
- Recover and annotate the nearest safe theme content column for both managed and legacy public publications.
- Hide fixed desktop sidebars during bounded recovery and fail to a readable centered content layout instead of overlaying the article.
- Add explicit pending/repaired/failed states and cache-busting CSS/JavaScript identities.
- Preserve no-DOM-reparenting, Safe Mode, File 00 authorization and File 22 Create-contract boundaries.

## 1.2.0 — Central-Plan v4 Architecture Harmonization

### Canonical architecture

- Added the complete File 00–25 ownership, dependency, criticality and failure-behavior registry.
- Published exact layout-context and operational-state contracts.
- Preserved native module ownership and the existing File 22 Create contract `1.0.1`.

### Layout correction

- Added the missing Immersive mode for Reels, full-screen video/live and PDF reader contexts.
- Removed query-parameter-based profile promotion to Three-column.
- Kept Founder/Doctor/member profiles and timelines in Two-column presentation.
- Kept the File 23 Publishing Dashboard as a Two-column private application rather than Minimal.
- Enforced one horizontal no-wrap primary-navigation line with bounded accessible overflow.

### File 25 boundary

- Added a versioned File 25 visual-contract consumer with an owner allowlist and semantic-version floor.
- Moved runtime visual-token authority out of File 20's base Assets class.
- Retired and hid File 20's legacy Appearance editor while preserving old values as migration-only data.
- Added a truthful `fallback` state when File 25 is unavailable instead of claiming full visual integration.

### QA and lifecycle

- Added central-plan static regression, machine-readable JSON contract and deterministic CI package artifact.
- Recorded two separate review-and-fix rounds in `CENTRAL-PLAN-V4-TRACEABILITY.md`.
- Retained the explicit boundary that repository/CI success is not Hostinger staging or production acceptance.

## 1.1.2 — Authorization, Privacy, and Bounded Discovery Correction

### Authorization integrity

- Made `sabri_shell_can_show_create` a deny-only narrowing hook; File 22 cannot turn a false File 00/File 20 decision into an allowance.
- Rendered desktop and mobile Create links only after the exact package-owned current-user contract returns true.
- Required subject-bound File 00 contract `1.1.2` or later and removed role/meta/filter fallbacks from privileged identity decisions.
- Preserved the explicit institutional Administrator exception only after current File 00 approval, eligibility, 2FA, and `manage_options`.

### Public data integrity

- Required current File 00 assertions before File 03 directory eligibility can expose a verified Doctor.
- Removed raw Membership profile and generic File 03 getter fallbacks from public doctor fields and contact data.
- Kept professional fields empty unless File 03 provides an explicit approved projection.

### Performance and QA

- Replaced two `posts_per_page => -1` compatibility scans with deterministic 100-Page batches and a fixed 50-batch ceiling.
- Added tests for filter non-elevation, server-rendered Create omission, mobile authorization, contract provenance, bounded scans, and public-projection privacy.

## 1.1.1 — File 22 Create Contract and Public Layout Correction

- Added the exact package-owned File 22 Create contract `1.0.1` and package-source ownership checks.
- Added bounded managed-single layout recovery, navigation wrapping, profile-status separation, and content containment.
- Preserved theme and companion DOM ownership and added deterministic package evidence.

## 1.1.0 — Corrective Release Candidate

- Corrected companion contracts, authority boundaries, layout targeting, accessibility, duplication, cache invalidation, repair and rollback.
- Added PHP behavioral regression and deterministic release gates.

## 1.0.0 — Original Baseline

- Initial independent Sabri Unified Application Shell package.
