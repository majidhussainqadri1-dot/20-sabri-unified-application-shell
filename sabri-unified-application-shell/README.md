# Sabri Unified Application Shell

Sabri Unified Application Shell is the canonical responsive WordPress application shell for the **Sabri Social Homeopathy Platform**.

- Version: `1.4.16`
- File 22 Create contract: `1.0.1`
- Central-plan contract: `1.0.0`
- File 01 reconciliation command contract: `1.0.1`
- Four-plan harmonization contract: `1.0.0`
- Future Shell v5 contract: `1.0.0`; corrective layers `1.0.1` through `1.0.11`
- Approved Future Shell scope: exactly **18 enhancements**
- Conditional companion set: **CF-01 through CF-06**, declared only; activation/runtime/staging/live status is not implied
- Status: repository/code/package/automated-QA candidate only after exact-head CI; production resolution requires separate deployment and live re-test
- Plugin slug/text domain: `sabri-unified-application-shell`

## Canonical scope

File 20 owns the shared structural application frame, navigation/layout resolution, native presentation slots, diagnostics and File-20 recovery continuity. It does not duplicate membership, authentication, Home/News feed/publishing, communication, notification, clinic, marketplace, clinical/support/financial/media-processing/analytics/localization, Search/Discovery/Ranking, security-enforcement or visual-design backends.

## Version 1.4.16 — File 01-B Settings API sanitizer persistence repair

Live-first evidence on 2026-08-29 proved that exact deployed File20 `1.4.15` matched the approved repository runtime and removed all File01 reconciliation dry-run blockers, but the controlled File01 apply failed and verified compensation restored the pre-apply state. Live forensic traces then proved Founder page `164` was published and owner-compatible, every File20 pre-update/read filter preserved the proposed `navigation.founder.page_id=164`, raw database and `get_option()` both remained `0`, and persistent object-cache truth matched the database.

The root cause was the registered WordPress Settings API sanitizer. `Settings::sanitize()` is intentionally tab-oriented for admin form submissions; the trusted reconciliation adapter called `update_option()` programmatically without `_active_tab`, so WordPress invoked that sanitizer before File20's pre-update filters and normalized the proposed navigation change back to existing settings.

Version 1.4.16 advances the File01 reconciliation command contract to `1.0.1` and adds a bounded trusted File20 settings persistence path. It explicitly enforces File20 ownership invariants, temporarily suspends only the registered tab-oriented `Settings::sanitize` callback for the exact adapter-owned programmatic mutation, leaves every other WordPress/core/security/concurrency/pre-update filter in force, restores the sanitizer in `finally`, and uses the same path for compensation and rollback restoration. A permanent regression first reproduces the live 1.4.15 sanitizer-swallow failure and then proves corrected execute/rollback persistence and sanitizer restoration.

This release does **not** itself prove the live incident resolved. Required sequence remains: exact-head CI/package → deploy File20 1.4.16 → prove deployed parity → rerun File01 dry-run → require zero blockers and File20 command version 1.0.1 → controlled File01 reconciliation → verify applied state/receipts/routes → live System Check and recovery re-test.

## Version 1.4.15 — File 01-B reconciliation owner bridge

Live-first evidence on 2026-08-21 proved that deployed File 01-B `2.0.1` matched its canonical repository/package runtime, schema `1.2.0` was physically healthy, and its reconciliation dry-run was correctly fail-closed with 15 actions and 12 blockers. File21 already supplied the owner-plan/execute/rollback contract for legacy `home` and `news`; exact deployed File20 `1.4.14` matched repository runtime and contained none of the three File01 reconciliation hooks required for the remaining legacy routes.

Version 1.4.15 added a bounded, reversible File20 reconciliation adapter for exactly twelve legacy shell-route handoffs: Founder, Learn, Encyclopedia, Doctors, Clinic, Video Wall, Reels, PDF Library, Radar, AI, Network and Marketplace. The adapter preserves native content/domain ownership with Files 03/05/06/07/08/10/11/12/15/16/17/18 and preserves File21 Home/News precedence. Version 1.4.16 preserves that owner boundary while correcting only its real WordPress persistence path.

## Future Shell v5 — exact 18 enhancements

The approved set remains: Command Palette; installable privacy-bounded PWA shell; offline/weak-network mode; Data Saver; Recent & Resume; module circuit breaker; last-known-good recovery; performance guardian; smart navigation; keyboard accessibility; Focus Mode; native-owner Split Workspace; foldable/tablet/ultra-wide adaptation; progressive View Transitions; bounded safe prefetch; language/direction control; accessibility preference center; and five-state release rings.

No corrective release may add a nineteenth feature without a new Founder-approved amendment.

## Version 1.4.14 — Review 1 release-truth correction

A fresh review of the completed 1.4.13 live-Renderer repair found no new defect in the restored Renderer helper logic, but it found four shipped release/operator-documentation defects: `MIGRATION.md` and `STAGING-ACCEPTANCE.md` still named obsolete 1.4.11 artifacts; `CHANGELOG.md` omitted 1.4.12 and 1.4.13; and `ROLLBACK.md` contradicted current rollback/Safe Mode runtime behavior. Because the approved 1.4.13 ZIP already had an immutable checksum, those corrections were issued as a new 1.4.14 artifact rather than silently replacing 1.4.13 under the same version.

Version 1.4.14 preserves the 1.4.13 runtime helper correction unchanged, aligns release/operator documentation with current code, and adds a permanent release-documentation truth regression. No new feature, backend, ownership expansion, database schema or foreign-domain authority is introduced.

## Version 1.4.13 — live Renderer helper repair

Version 1.4.13 is a bounded production-incident correction over 1.4.12. Live evidence proved that deployed File 20 1.4.12 repeatedly failed with `Call to undefined method Sabri\UnifiedShell\Renderer::item_visible_to_user()` at deployed `class-renderer.php:215`. The deployed faulting file Git blob `7351fb82cc9a8a301130181a7f2691ac236ca7db` matched GitHub `main` exactly, so faulting-file deployment parity was proven rather than assumed.

Repository history/source comparison proved that one contiguous Renderer helper block had been deleted while its call sites remained. Version 1.4.13 restored only the three proven-deleted helpers: `render_panel()`, `destination_url()`, and `item_visible_to_user()`. It also added a permanent static regression that rejects unresolved `self::method()` calls in `Renderer`.

## Version 1.4.12 hardening preserved

The second eighty-round 1.4.12 cycle remains preserved: decoded route validation applies identically to relative and absolute HTTPS overrides; Page-ID route sources require single canonical ownership; File17 Network and Messages shortcode/Page-ID/diagnostic truth are separated; generic WordPress open registration is not advertised as platform signup under High-Trust Verified Entry; configured internal mappings are relative/same-site HTTPS only; File01 foundation metadata cannot imply Search truth; provider-only File21 Home right-slot output remains accessible below desktop width; System Check no longer reads removed WordPress-role doctor diagnostics and requires explicit healthy File20/File00 evidence; recovery/System Check REST evidence is no-store; nonce-bearing Safe Mode URLs stay same-site; Settings API writes and LKG restore transactions are serialized with owner-token locks.

## Earlier hardening preserved

File21 remains canonical Home/News owner; the retired local File20 HomeFeed producer is absent. File25 remains visual authority. File26 remains Search/Discovery/Ranking authority with no WordPress search fallback. Presence-aware recovery snapshots, under-lock repair/rollback revalidation, strict route precedence, File20-only explicit uninstall cleanup, audit integrity, Emergency lifecycle, PWA privacy, five release rings, File19 one-bell, File24 assurance/native enforcement and all CF-01..CF-06 native-owner boundaries remain in force.

## Conditional-module law preserved

CF-01 clinical records, CF-02 support/appeals, CF-03 financial operations, CF-04 secure media, CF-05 analytics and CF-06 localization remain conditional native-owner domains. File20 provides route/layout/UI/privacy integration only.

`/media/d/{grant}` remains CF-04-owned for token/range/cache semantics. Verified user transfer remains File17 + activated CF-04 with the approved **1 GB** per-file limit. Current platform financial law remains one free tier, voluntary donation, zero commission and no donor advantage; paid collection remains dormant absent new Founder change control and native CF-03 activation evidence. Locale/translation truth remains with CF-06/approved provider.

## Deployment acceptance

Repository completion is not production acceptance. The exact `1.4.16` package/head/checksum must be deployed and verified separately. After deployment, rerun File01 reconciliation dry-run and require File21 Home/News plus all twelve File20 owner plans to be accepted, with File20 command version `1.0.1` and zero blockers, before any controlled reconciliation is applied. Then verify File01 state is `applied`, all expected receipts are persisted, `spf_page_map` and the unsafe Founder option are retired only by File01's transaction, the twelve shell routes persist their exact Page IDs after legacy-map removal, rollback remains reversible, the Settings sanitizer is restored after bounded writes, and the original `/google-account-security/` live-Renderer incident remains fixed. Real File00–26 and activated CF contracts, File17 Network/Messages separation, canonical High-Trust signup, File21 native mount/no-duplication and provider-only right-slot responsiveness, File25 visual truth, File26 unavailable/no-fallback behavior, File00 critical-health behavior, Emergency gate, strict route precedence/overrides, sensitive REST no-store behavior, concurrent Settings/LKG contention, repair/rollback, root/subdirectory privacy/layout, System Check, PWA, browsers/devices, accessibility, RTL/LTR, low-data/offline behavior, backup/restore and Founder acceptance remain separate operational evidence before production resolution may be claimed.
