# Changelog

## 1.4.16 — 2026-08-29 — File01 Settings API Sanitizer Persistence Repair

- Live controlled File01 apply on exact deployed File20 `1.4.15` failed after the dry-run had reached zero blockers; verified automatic compensation restored all 14 legacy mappings, the unsafe Founder option, non-quarantined page state and empty owner receipt stores.
- Live forensic traces proved Founder page `164` was published and owner-compatible, every File20 pre-update/read filter preserved proposed `navigation.founder.page_id=164`, raw database and `get_option()` both remained `0`, and persistent object-cache truth matched the database.
- Root cause: WordPress invoked the registered tab-oriented `Settings::sanitize()` callback on the adapter's programmatic `sabri_shell_settings` update. With no `_active_tab`, the sanitizer normalized the proposed navigation write back to existing settings before File20's pre-update filters.
- Advanced the File01 reconciliation command contract from `1.0.0` to `1.0.1`.
- Added a bounded trusted File20 settings persistence path that explicitly enforces File20 invariants, suspends only the registered `Settings::sanitize` callback for the exact adapter-owned programmatic mutation, preserves every other WordPress/core/security/concurrency/pre-update filter and restores the sanitizer in `finally`.
- Applied the same corrected persistence path to execute compensation and rollback restoration.
- Added a permanent regression that first reproduces the live sanitizer-swallow failure and then proves corrected execute/rollback persistence plus sanitizer callback restoration.
- Advanced plugin/package identity to `1.4.16`; repository/CI success does not claim live deployment, deployed parity, zero-blocker re-test, successful controlled reconciliation, live route verification or operational resolution.

## 1.4.15 — 2026-08-21 — File01 Reconciliation Root-Cause Repair

- Live-first evidence proved deployed File01-B `2.0.1` exact runtime parity, schema `1.2.0` physical health and a correctly fail-closed reconciliation dry-run with 15 actions and 12 owner-plan blockers.
- Live callback evidence proved File21 already supplied File01 owner-plan/execute/rollback callbacks for Home/News, while exact deployed File20 `1.4.14` matched repository runtime and contained none of the three File01 reconciliation hook strings.
- Added `File01ReconciliationAdapter` for exactly twelve shell-route handoffs: Founder, Learn, Encyclopedia, Doctors, Clinic, Video Wall, Reels, PDF Library, Radar, AI, Network and Marketplace.
- Preserved File21 Home/News precedence and native content/domain ownership for Files 03/05/06/07/08/10/11/12/15/16/17/18; File20 persists only its own navigation Page-ID reference before File01 retires `spf_page_map`.
- Added bounded integrity-bound reversible receipts, deterministic idempotent replay, exact pre-state restoration and fail-closed plan/receipt/state-drift checks.
- Added dedicated reconciliation regression coverage and explicit uninstall cleanup for the File20-owned receipt store.
- Advanced plugin/package identity to `1.4.15`; repository/CI success does not claim deployment, File01 zero-blocker re-test, controlled reconciliation, live route verification or operational resolution.

## 1.4.14 — 2026-08-17 — Review-1 Release/Operator Truth Correction

- Completed one fresh review over the merged 1.4.13 live-Renderer repair.
- Found no new defect in the restored Renderer helper logic; preserved the 1.4.13 runtime correction unchanged.
- Corrected `MIGRATION.md` and `STAGING-ACCEPTANCE.md`, which still named obsolete 1.4.11 artifacts.
- Restored missing 1.4.12 and 1.4.13 release history in this changelog.
- Corrected `ROLLBACK.md` to the current File-20-only automatic rollback allowlist and removed the false claim that shared WordPress front-page options are automatically restorable.
- Corrected Safe Mode operator guidance: raw `?sabri_shell_safe=1` alone is not sufficient; current query Safe Mode is authenticated, administrator-only and nonce-bound.
- Added permanent release-documentation truth regression coverage and advanced the deterministic release identity to 1.4.14 rather than replacing the already checksummed 1.4.13 artifact in place.
- Repository/CI completion does not imply live deployment, live resolution or operational acceptance.

## 1.4.13 — 2026-08-17 — Live Renderer Helper Repair

- Bounded production-incident correction over 1.4.12 after live `/google-account-security/` returned HTTP 500 with `Call to undefined method Sabri\UnifiedShell\Renderer::item_visible_to_user()` at deployed `class-renderer.php:215`.
- Proven faulting-file parity: deployed Renderer Git blob `7351fb82cc9a8a301130181a7f2691ac236ca7db` matched repository `main` exactly.
- Restored the proven-deleted contiguous Renderer helper block: `render_panel()`, `destination_url()`, and `item_visible_to_user()`.
- Added a permanent regression that rejects unresolved owned `self::method()` calls and protects the visibility callback.
- Preserved 1.4.12 ownership/routing/privacy/recovery/Future Shell behavior; no new feature, backend, schema or ownership domain was introduced.
- Exact-head and post-merge PHP 7.4/8.3, regression, baseline-integrity and deterministic-package gates passed, but live deployment/re-test remained a separate gate.

## 1.4.12 — 2026-08-09 — Second Fresh Eighty-Round Hardening

- Completed a second fresh eighty-round review over merged 1.4.11 while preserving exactly eighteen approved Future Shell enhancements.
- Added `FutureShellV5EleventhHardening` contract `1.0.11` without adding a nineteenth feature.
- Unified decoded route validation for relative and absolute HTTPS overrides and strengthened Page-ID single-owner collision validation.
- Separated File17 Network and Messages shortcode/Page-ID/diagnostic truth and blocked generic WordPress open-registration fallback under High-Trust Verified Entry.
- Hardened configured internal URLs, File01 no-Search ownership truth, File21 responsive provider-only right-slot behavior, System Check critical File20/File00 evidence, sensitive REST no-store responses, Safe Mode same-site nonce URLs, Settings API serialization and LKG restore locking.
- Preserved repository/staging/live/operational evidence boundaries.

## 1.4.11 — 2026-08-09 — Eighty-Round Corrective Consolidation

- Reopened merged `1.4.10` for eighty independent review passes; previous green CI was historical evidence only.
- Consolidated File 20 ownership boundaries directly into core code, including File21 Home/News, File25 visual, File26 Search and File00 privileged-identity truth.
- Advanced settings schema to 5 and enforced retired/inert domain-owned state on all File20 settings writes.
- Hardened routing uniqueness/owner/access validation, provider semantic-version truth, Minimal/Immersive classification, right-sidebar ownership, public profile/doctor projection and safe action URL handoff.
- Hardened recovery snapshots, rollback allowlists/smoke tests, settings-row concurrency, LKG compatibility entry points, circuit-breaker state, assurance queue serialization and dynamic shortcode cache cleanup.
- Removed the retired local HomeFeed source file and dead/legacy admin/mobile/appearance paths.
- Preserved exactly eighteen Future Shell v5 enhancements and added no companion-domain backend.
- Hostinger staging, real companion/browser/accessibility testing, backup/restore/rollback rehearsal, Founder acceptance, live deployment and operational acceptance remain separate gates.

## 1.4.10 — 2026-08-09 — Tenth Fresh Ten-Round Corrective Hardening

- Reopened merged `1.4.9` from scratch against File20 v5.0, the central QA/ownership constitution, File21 native-slot law and the latest reviewed File00 evidence.
- Added `FutureShellV5TenthHardening` contract `1.0.10` while preserving exactly eighteen approved Future Shell enhancements.
- Retired File20's historical local Home-feed runtime under canonical File21 ownership and forced residual File20 `home_feed` state/configured `sabri_shell_home_feed` route source inert.
- Published the five exact File21 native presentation hooks: `sabri_shell_home_before_main`, `sabri_shell_home_main`, `sabri_shell_home_after_main`, `sabri_shell_home_right_sidebar`, `sabri_shell_news_main`.
- Prevented native + legacy duplicate Home/News rendering: native main output is authoritative when present; page/shortcode content remains compatibility fallback only when native output is absent.
- Removed residual File20 Appearance-driven body classes; File25 remains sole visual authority while File20 emits structural layout classes only.
- Updated declared File00 evidence to reviewed runtime `1.2.18`, schema `1.3.0`, contract `1.2.0`, commit `3a84c32a6ddad151f2ed09d244fa8aa536a58108`, explicitly retaining its external audit blockers and refusing to imply production safety.
- Removed the legacy WordPress/File20 Search fallback; only a validated File26 Search/Discovery/Ranking contract may provide the search surface.
- Bound File25 and File01-B health rows to the semantic versions actually advertised by native providers; malformed versions are unavailable and below-minimum versions incompatible.
- Prevented aggregate health from reporting Healthy while critical File20/File00 evidence is Unknown, Unavailable or Incompatible.
- Made Emergency re-enable consume the same critical File20/File00 health truth in addition to audit/cache gates.
- Added dedicated tenth-pass regression coverage and advanced deterministic production-only package identity to `1.4.10-TENTH-TEN-ROUND-HARDENED`.
- Hostinger staging, Founder acceptance, live deployment and operational acceptance remain separate evidence gates.

## 1.4.9 — 2026-08-08 — Ninth Independent Ten-Round Corrective Hardening

- Removed fresh File20 visual-state creation under File25 ownership and made Emergency state a single audited write authority.
- Added presence-aware recovery snapshot format 2 and closed rollback-target retention plus repair-preview TOCTOU races.
- Added selectable dry-run stale File20 Page-ID quarantine and monotonic programmatic settings evidence.
- Enforced strict relative/HTTPS route overrides and exact Page ID → shortcode → archive → slug → validated override → unavailable precedence.
- Completed opt-in File20-only uninstall cleanup and rollback preservation of current Emergency state plus monotonic settings-row version.
- Verified each restored option post-write and preserved all prior PWA/privacy/conditional-owner boundaries.

## 1.4.8 — 2026-08-08 — Eighth Independent Ten-Round Corrective Hardening

- Retired the stale File20 Appearance admin surface under File25 visual ownership.
- Hardened structured System Check/export, audit-chain retention/privacy erasure, mbstring-independent diagnostics and settings normalization.
- Added schema-aware File20/Future-Shell recovery snapshots, integrity-protected activation snapshot behavior and nonce-bound query Safe Mode.
- Routed Repair/Rollback/Emergency controls through hardened controllers and kept staging/live/operational claims separate.

## 1.4.7 — 2026-08-08 — Seventh Independent Ten-Round Corrective Hardening

- Added CF-01 through CF-06 as declared conditional/native-owner contract targets, not runtime or activation claims.
- Added privacy-aware conditional route handling while preserving CF-04 media token/range/cache authority, File17 + activated CF-04 1 GB transfer, CF-06 localization truth and current one-free-tier/voluntary-donation/zero-commission/donor-neutral law.
- Replaced an ineffective assurance placeholder with real redaction/bounded/provider-exception checks and advanced deterministic release gates.

## 1.4.6 — 2026-08-08 — Sixth Independent Ten-Round Corrective Hardening

- Reconciled then-current File00, File01-B, File02 and File24 compatibility targets while preserving native-owner enforcement and fail-safe boundaries.
- Added a dedicated sixth-pass regression and deterministic release gates.

## 1.4.5 — 2026-08-08 — Fifth Independent Ten-Round Corrective Hardening

- Hardened production-only packaging: development tests excluded, unsafe/traversal/duplicate/wrong-root ZIP entries rejected, source/stage/extracted SHA-256 parity and embedded/external manifest equality required.
- Finalized five-state Internal release-ring evaluation while retaining manager-only configuration authority.

## 1.4.4 — 2026-08-08 — Fourth Independent Ten-Round Corrective Hardening

- Reconciled File00/01/02/19/21/24 contracts and File25 visual ownership.
- Added File02 standards-route handling, File19 one-bell contract, File21 five-slot compatibility metadata and File24 render/routing conditions without transferring native enforcement.

## 1.4.3 — 2026-08-08 — Third Independent Ten-Round Corrective Hardening

- Added current sensitive task routes, fail-closed protected-path overflow, one final PWA virtual-asset owner and File25 continuity fallback aligned to Sabri Green.
- Preserved File01/File02 native authority and File26 Search ownership.

## 1.4.2 — 2026-08-08 — Second Independent Ten-Round Corrective Hardening

- Added WordPress-subdirectory-aware privacy, PWA retirement, partial-setting preservation, bounded circuit metadata, editable shortcut protection and Split Workspace mode restrictions.

## 1.4.1 — 2026-08-08 — First Future-Shell Corrective Ten-Round Hardening

- Hardened release rings, PWA privacy/lifecycle, Recent/Resume, Smart Navigation/prefetch, last-known-good recovery, accessibility/dialog focus, PerformanceObserver lifetime and Split Workspace.
- Removed Future Shell visual-token ownership and global content overrides.

## 1.4.0 — 2026-08-08 — Future Shell v5 / Exact 18 Enhancements

- Added Command Palette, PWA, Offline/Weak Network, Data Saver, Recent & Resume, Circuit Breaker, Last-Known-Good Recovery, Performance Guardian, Smart Navigation, Keyboard Layer, Focus Mode, Split Workspace, Adaptive/Foldable Shell, View Transitions, bounded Predictive Prefetch, Language/Direction Control, Accessibility Center and Release Rings.
- Preserved File00/22 authorization, File19 one-bell, File25 visual ownership, File26 search ownership and central-plan layout/recovery boundaries.

## 1.3.1 — 2026-08-07

- Completed fresh four-plan review, once-per-session Welcome plus 30-day suppression, removed dormant native WordPress search fallback and duplicate bottom-strip metadata, and hardened one-row More navigation overflow.

## 1.3.0 — 2026-08-07

- Harmonized File20 against the four governing plans, established fail-closed File26 search, removed duplicate mobile bottom navigation, added File23 dashboard entry, File25 visual ownership and UI-only Smail/file-transfer/download contracts.

## Unreleased — Shared Context Navigation

- Added one File20-owned Back + Home control for internal public pages with safe same-origin fallback, no-JavaScript support, accessible targets and dedicated regressions. Staging/visual/live acceptance remains pending.

## 1.2.0 Publication Layout R3 — 2026-08-04

- Accepted File21 neutral content-integrity signals for legacy publication compatibility without taking content ownership; bounded theme content-column recovery preserved Safe Mode/File00/File22 boundaries.

## 1.2.0 — Central-Plan v4 Architecture Harmonization

- Added File00–25 ownership/dependency/criticality/failure registry, four layout contexts, operational states, File25 visual-contract consumption and central-plan deterministic CI package evidence.

## 1.1.2 — Authorization, Privacy, and Bounded Discovery Correction

- Made Create visibility deny-only and subject-bound to File00 authority; removed role/meta/filter privilege fallbacks and raw/unbounded doctor-data compatibility scans.

## 1.1.1 — File22 Create Contract and Public Layout Correction

- Added package-owned File22 Create contract `1.0.1`, bounded managed-single layout recovery and deterministic package evidence.

## 1.1.0 — Corrective Release Candidate

- Corrected companion contracts, authority boundaries, layout targeting, accessibility, duplication, cache invalidation, repair and rollback.

## 1.0.0 — Original Baseline

- Initial independent Sabri Unified Application Shell package. The exact historical archive remains preserved in `SOURCE-ARCHIVE/`.
