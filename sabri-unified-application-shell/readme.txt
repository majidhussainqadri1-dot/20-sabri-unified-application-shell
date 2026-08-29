=== Sabri Unified Application Shell ===
Contributors: majidhussainqadri1-dot
Tags: application shell, navigation, layout, accessibility, pwa, offline
Requires at least: 6.0
Tested up to: 7.0.1
Requires PHP: 7.4
Stable tag: 1.4.17
License: GPLv2 or later
Text Domain: sabri-unified-application-shell

Responsive canonical application shell and integration layer for the Sabri Social Homeopathy Platform.

== Description ==

Version 1.4.17 centralizes the live-proven Settings API persistence correction into one canonical File20 programmatic writer. A fresh post-merge repository review found the same source-level risk in File20 repair, rollback, Emergency, activation-snapshot rollback and retired-state migration paths. Those internal workflows now use `Settings::update_programmatically()`; direct trusted `sabri_shell_settings` writes outside the Settings owner are rejected by a permanent static gate. This is repository/source-level remediation; it does not claim those additional paths were failing on Live.

Version 1.4.16 is a bounded File 01-B reconciliation persistence correction based on exact live evidence from the failed 1.4.15 controlled apply. The 1.4.15 adapter removed all dry-run blockers, but real WordPress Settings API sanitization discarded the trusted programmatic `sabri_shell_settings` navigation write before File20 pre-update filters, causing File01 to compensate. Live traces proved the proposed Founder Page ID 164 survived every pre-update/read filter, raw DB and `get_option()` both remained 0, and object-cache truth matched DB.

Version 1.4.16 advances the File01 reconciliation command contract to 1.0.1 and adds a bounded trusted File20 settings writer. It suspends only the tab-oriented `Settings::sanitize` callback for the exact adapter-owned programmatic settings mutation, explicitly applies File20 invariants, leaves all other WordPress/core/security/concurrency/pre-update filters in force, restores the sanitizer in `finally`, and uses the same path for compensation/rollback restoration. A permanent regression first reproduces the live sanitizer-swallow failure and then proves execute/rollback persistence with sanitizer restoration.

Version 1.4.15 is preserved as the owner-adapter release that restored File01 owner-plan/execute/rollback support for exactly twelve shell-route handoffs after live evidence showed deployed File20 1.4.14 had none of the required hooks. File21 remains canonical Home/News owner.

Version 1.4.14 was the first fresh review correction over the 1.4.13 live-Renderer repair. The 1.4.13 runtime correction is preserved unchanged; 1.4.14 corrected release/operator truth that still referenced obsolete 1.4.11 artifacts or obsolete rollback/Safe Mode behavior and added permanent release-documentation regression coverage.

Version 1.4.13 restored only the three proven-deleted Renderer helpers—`render_panel()`, `destination_url()`, and `item_visible_to_user()`—after live evidence proved the deployed 1.4.12 Renderer was byte-for-byte identical to the faulting repository file and retained call sites for a deleted helper block. The exact eighteen approved Future Shell enhancements and File 20 ownership boundaries remain unchanged.

File 20 owns only the global structural shell, canonical navigation/layout resolution, integration slots, diagnostics and File-20 recovery continuity. Native membership/authentication, Home/News, messaging, notifications, appointments, marketplace, security enforcement, visual truth, Search/Discovery/Ranking and conditional clinical/support/finance/media/analytics/localization backends remain with their canonical companion owners.

Repository/CI correction does not by itself claim Hostinger staging acceptance, live deployment, File01 reconciliation completion, Founder acceptance or operational acceptance.

== Installation ==

1. Back up files and database and prove restore.
2. Install or upgrade the exact `20-sabri-unified-application-shell-1.4.16-FILE01-SANITIZER-PERSISTENCE-REPAIR.zip` artifact on the approved deployment target.
3. Verify one canonical ZIP root, no tests/development material and exact source/stage/extracted SHA-256 parity.
4. Confirm deployed File20 runtime reports 1.4.16 and prove deployed-artifact/runtime parity with the approved candidate.
5. Re-run File01 reconciliation dry-run. `home` and `news` must remain File21-owned; the twelve File20 mappings must receive accepted `file-20` owner plans with command version 1.0.1; blockers must be zero before apply.
6. Verify each File20 plan is `shell_navigation_reference_only`, native content ownership is unchanged, and no foreign table/content mutation is introduced.
7. Before controlled apply, confirm File01 plan hash is current, rollback snapshot/receipts are available and backup/restore evidence is current.
8. Apply File01 reconciliation once through File01's controlled action; verify the trusted File20 programmatic settings write persists each route and the Settings sanitizer is restored after each bounded mutation.
9. After apply, verify File01 state is `applied`, all 14 owner receipts are present, `spf_page_map` and `spf_founder_user_id` are removed by File01, and no compensation/error state exists.
10. Verify all twelve File20 shell routes plus File21 Home/News still resolve correctly after legacy-map retirement; then run System Check and the remaining ownership/recovery/privacy/accessibility gates before declaring operational resolution.

== Changelog ==

= 1.4.17 =
* Centralized trusted File20 programmatic `sabri_shell_settings` persistence in `Settings::update_programmatically()`.
* Routed File01 reconciliation, hardened repair/rollback, Emergency lifecycle, activation-snapshot rollback, defaults normalization and retired Home-feed migration through the canonical writer.
* Added dynamic active-sanitizer regression plus a static gate forbidding direct trusted settings writes outside the Settings owner.
* Preserved the 1.4.16 live-proven bounded sanitizer strategy while removing adapter-local persistence duplication.
* No Live deployment or operational-resolution claim is made.

= 1.4.16 =
* Corrected the live-proven Settings API sanitizer conflict that swallowed trusted programmatic File20 navigation writes during File01 reconciliation.
* Advanced the File01 reconciliation command contract to 1.0.1.
* Added a bounded trusted File20 settings persistence path that suspends only `Settings::sanitize`, explicitly enforces File20 invariants, preserves all other filters and restores the sanitizer in `finally`.
* Applied the same corrected persistence path to execute compensation and rollback restoration.
* Added a permanent regression that reproduces the 1.4.15 live failure before proving corrected execute/rollback behavior under an active sanitizer.
* Advanced deterministic package identity to 1.4.16; deployment, parity, zero-blocker dry-run, controlled reconciliation and live re-test remain separate gates.

= 1.4.15 =
* Added File01 owner-plan, execute and rollback reconciliation hooks for exactly twelve shell-route handoffs discovered by live File01 dry-run evidence.
* Preserved File21 Home/News precedence and native content ownership for Files 03/05/06/07/08/10/11/12/15/16/17/18.
* Persisted only File20-owned navigation Page-ID references before File01 removes its legacy page map.
* Added deterministic, bounded, integrity-bound rollback receipts with idempotent execute/rollback behavior and state-drift fail-closed checks.
* Added permanent reconciliation regression coverage and explicit uninstall cleanup for the bounded receipt store.
* Advanced deterministic production package identity to 1.4.15; deployment, File01 zero-blocker re-test, controlled reconciliation and live verification remain separate gates.

= 1.4.14 =
* Completed one fresh review over the 1.4.13 live-Renderer repair.
* Preserved the proven 1.4.13 runtime helper correction unchanged.
* Corrected stale `MIGRATION.md`, `STAGING-ACCEPTANCE.md`, `CHANGELOG.md`, and `ROLLBACK.md` release/operator truth.
* Aligned automatic rollback documentation with the current File-20-only option allowlist; shared WordPress front-page options are not automatic rollback scope.
* Removed unsafe obsolete advice that raw `?sabri_shell_safe=1` alone enables query Safe Mode; current query Safe Mode is authenticated, administrator-only and nonce-bound.
* Added permanent release-documentation truth regression coverage and advanced the deterministic artifact identity to 1.4.14.
* Repository/CI status remains separate from live deployment, live re-test and operational resolution.

= 1.4.13 =
* Restored the File 20 Renderer helper block accidentally deleted while its call sites remained.
* Restored `render_panel()`, `destination_url()`, and `item_visible_to_user()` without expanding File 20 ownership or adding features.
* Added a permanent Renderer helper-integrity regression that fails when an owned `self::method()` call has no same-class method definition.
* Live incident basis: deployed File 20 1.4.12 repeatedly fatally called missing `item_visible_to_user()` at `class-renderer.php:215`, and the deployed renderer Git blob matched GitHub `main` exactly.
* Repository/CI correction did not claim live resolution; deployment, live re-test and parity confirmation remained mandatory.

= 1.4.12 =
* Completed a second fresh eighty-round File 20 review over merged 1.4.11.
* Added FutureShellV5EleventhHardening contract 1.0.11 without adding a nineteenth Future Shell feature.
* Unified relative/absolute route path canonicalization and broadened Page-ID single-owner collision validation.
* Corrected File17 Messages shortcode/Page-ID/diagnostic separation from Network.
* Blocked generic WordPress registration fallback under High-Trust Verified Entry.
* Hardened configured internal URLs to relative/same-site HTTPS and external-purpose WhatsApp to HTTPS only.
* Corrected File01 foundation ownership wording so Search truth remains File26.
* Preserved provider-only File21 Home right-slot output responsively.
* Replaced stale doctor-role System Check logic with File00/File09 authority evidence and explicit File20/File00 health presence requirements.
* Added no-store headers for sensitive File20 recovery/System Check REST evidence.
* Serialized Settings API concurrency and LKG restore transactions; explicit uninstall purge includes their locks.
* Repository/CI status remained separate from Hostinger staging/live/operational acceptance.

= 1.4.11 =
* Completed the first eighty-round consolidation over 1.4.10 and advanced settings schema to 5.
* Removed retired File20 HomeFeed producer and strengthened ownership/routing/recovery/privacy/control-plane boundaries.

= 1.4.10 =
* Completed the tenth fresh ten-round corrective review over 1.4.9 and published the exact File21 native slots.

= 1.4.9 =
* Added schema-aware File20 recovery, strict route override security and canonical Emergency write governance.

= 1.4.8 =
* Hardened System Check, audit/recovery and Safe Mode while preserving File25 ownership.

= 1.4.7 =
* Added CF-01 through CF-06 conditional integration targets without activating native backends.

= 1.4.6 =
* Reconciled newer File00/01/02/24 compatibility evidence.

= 1.4.5 =
* Hardened production-only packaging and release-ring behavior.

= 1.4.4 =
* Reconciled File00/01/02/19/21/24 contracts.

= 1.4.3 =
* Added protected route overflow fail-closed behavior and final PWA ownership.

= 1.4.2 =
* Added subdirectory privacy, PWA retirement, bounded circuits and editable shortcut protection.

= 1.4.1 =
* Hardened release rings, PWA privacy, LKG recovery and accessibility.

= 1.4.0 =
* Added all eighteen approved Future Shell v5 enhancements.
