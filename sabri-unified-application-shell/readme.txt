=== Sabri Unified Application Shell ===
Contributors: majidhussainqadri1-dot
Tags: application shell, navigation, layout, accessibility, homeopathy
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
Text Domain: sabri-unified-application-shell

Responsive application shell and integration layer for the Sabri Social Homeopathy Platform.

== Description ==

Sabri Unified Application Shell provides the global header, resolved platform navigation, persistent left navigation, conditional contextual panels, mobile drawers, bottom navigation, minimal authentication/system layouts, settings, System Check, Complete Repair, Safe Mode, activation snapshot, and rollback.

Version 1.1.0 corrects the original 1.0.0 integration and layout defects. It resolves real companion page maps and shortcodes, honors Membership Core founder/doctor/publisher contracts, uses the moderated public composer, prevents duplicate feeds and notification bells, preserves theme DOM hierarchy and IDs, and replaces list settings correctly.

The plugin does not create duplicate messaging, notification, appointment, profile, marketplace, clinic, publishing, or clinical databases.

Hostinger staging testing and founder acceptance are required before production deployment. The `Tested up to` value records the published package metadata only; WordPress 7.0.1 staging acceptance remains a project gate and is not claimed by this release candidate.

== Installation ==

1. Back up the site.
2. Install or upgrade on staging only.
3. Activate the required companion modules.
4. Open Sabri Shell in wp-admin and run System Check.
5. Resolve all required destinations and warnings.
6. Complete `STAGING-ACCEPTANCE.md` before production.

== Shortcode ==

Use `[sabri_shell_home_feed]` only as a chronological fallback feed. Automatic insertion is suppressed when an authoritative platform feed is already present.

== Changelog ==

= 1.1.0 =
* Corrected companion page-map, shortcode, role, profile, clinic, appointment, authentication, publishing, language, and notification integrations.
* Removed unsafe theme-wrapper reparenting and preserved theme DOM hierarchy and IDs.
* Corrected list-array settings merge behavior and expanded cache invalidation.
* Prevented duplicate Home feeds and duplicate notification outputs.
* Added behavioral regression tests and corrective CI gates.

= 1.0.0 =
* Original baseline release.
