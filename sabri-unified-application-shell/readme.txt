=== Sabri Unified Application Shell ===
Contributors: majidhussainqadri1-dot
Tags: application shell, navigation, layout, accessibility, homeopathy
Requires at least: 6.0
Tested up to: 7.0.1
Requires PHP: 7.4
Stable tag: 1.1.2
License: GPLv2 or later
Text Domain: sabri-unified-application-shell

Responsive application shell and integration layer for the Sabri Social Homeopathy Platform.

== Description ==

Sabri Unified Application Shell provides the global header, resolved platform navigation, persistent left navigation, conditional contextual panels, mobile drawers, bottom navigation, minimal authentication/system layouts, settings, System Check, Complete Repair, Safe Mode, activation snapshot, and rollback.

Version 1.1.2 closes four authorization, privacy, and performance defects found during a fresh adversarial review: File 22 can no longer elevate a File 00 denial; Create links are omitted from server markup when the exact contract denies them; public doctor data no longer falls back to raw profile metadata; and shortcode-page discovery is bounded and paginated.

Version 1.1.1 added the exact package-owned File 20 Create contract required by File 22, separated the profile-card status, wrapped desktop navigation, and repaired the safe content column for File 21 managed single publications without reparenting theme or companion markup.

Version 1.1.0 corrected the original 1.0.0 integration and layout defects. It resolves real companion page maps and shortcodes, honors Membership Core founder/doctor/publisher contracts, uses the moderated public composer, prevents duplicate feeds and notification bells, preserves theme DOM hierarchy and IDs, and replaces list settings correctly.

The plugin does not create duplicate messaging, notification, appointment, profile, marketplace, clinic, publishing, or clinical databases.

Hostinger staging testing and founder acceptance are required before production deployment.

== Installation ==

1. Back up the site.
2. Install or upgrade on staging only.
3. Activate the required companion modules.
4. Open Sabri Shell in wp-admin and run System Check.
5. Confirm both File 20 Create-contract checks pass in File 22 Composer Health.
6. Verify the Home Feed and long-form single-publication layout at desktop, tablet, and mobile widths.
7. Complete `STAGING-ACCEPTANCE.md` before production.

== Shortcode ==

Use `[sabri_shell_home_feed]` only as a chronological fallback feed. Automatic insertion is suppressed when an authoritative platform feed is already present.

== Changelog ==

= 1.1.2 =
* Made the File 22 visibility filter deny-only; it cannot elevate a principal denied by File 00/File 20.
* Omitted desktop and mobile Create markup unless the exact current-user contract returns true.
* Required subject-bound File 00 contract 1.1.2+ assertions and removed legacy identity fallbacks from privileged shell decisions.
* Required File 00 truth before File 03 directory eligibility can expose a verified doctor.
* Removed raw Membership/File 03 getter fallbacks from public professional data; only approved projections may populate fields.
* Replaced unbounded all-Page shortcode scans with deterministic 100-item batches and a 5,000-page ceiling.
* Added regressions for deny-only filtering, server-rendered markup, bounded discovery, and public-projection privacy.

= 1.1.1 =
* Added exact File 22 Create contract version 1.0.1 with canonical ownership and current-user-only visibility.
* Preserved fail-closed behavior for partial or foreign contract claims and Safe Mode.
* Added bounded File 21 managed-single content-column recovery without DOM reparenting.
* Wrapped desktop navigation, separated profile-card status text, and contained publication content/actions/comments.
* Added PHP 7.4/8.3, JavaScript, CSS, contract, package, and checksum gates.

= 1.1.0 =
* Corrected companion page-map, shortcode, role, profile, clinic, appointment, authentication, publishing, language, and notification integrations.
* Removed unsafe theme-wrapper reparenting and preserved theme DOM hierarchy and IDs.
* Corrected list-array settings merge behavior and expanded cache invalidation.
* Prevented duplicate Home feeds and duplicate notification outputs.
* Added behavioral regression tests and corrective CI gates.

= 1.0.0 =
* Original baseline release.