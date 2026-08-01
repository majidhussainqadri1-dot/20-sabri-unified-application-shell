# Sabri Unified Application Shell

Sabri Unified Application Shell is the independent, responsive WordPress application shell for the **Sabri Social Homeopathy Platform**.

- Version: `1.1.0`
- Status: corrective release candidate; staging acceptance required
- Plugin slug: `sabri-unified-application-shell`
- Text domain: `sabri-unified-application-shell`
- Author: Dr. Allamah Majid Hussain Sabri Muhaddith Mursheed

## Scope

The plugin provides the shared presentation and routing layer only:

- global header, platform identity, search, role-aware Create, Messages, one Notifications output, Help, language switcher, and profile/auth controls;
- resolved primary navigation and persistent left navigation;
- conditional right-side contextual panels for Home, Worldwide Clinic, and doctor/clinic contexts;
- accessible mobile drawers and bottom navigation;
- layout resolution for three-column, two-column, and minimal contexts;
- companion-module page, shortcode, role, profile, clinic, appointment, notification, and publishing adapters;
- `[sabri_shell_home_feed]` as a guarded chronological fallback feed;
- settings, System Check, Complete Repair, Safe Mode, Emergency Disable/Re-enable, activation snapshot, and rollback.

It does **not** create duplicate messaging, notifications, appointments, profiles, marketplace, publishing, clinic, or clinical databases.

## Corrective Architecture in 1.1.0

Version 1.1.0 replaces the unsafe broad theme-wrapper reparenting used in the original 1.0.0 baseline. The JavaScript now identifies a conservative content container, annotates it in place, preserves the theme's original ID and DOM hierarchy, and hides desktop sidebars when no safe content target can be resolved.

The routing layer now prefers companion-owned page maps before shortcode, archive, slug, or validated URL fallback. Supported contracts include:

- `spf_page_map`, `spd_page_map`, `sdd_page_map`, `swc_page_map`;
- `svw_page_map`, `srl_page_map`, `spl_page_map`;
- `srf_page_map`, `sai_page_map`, `sa_page_map`, `snp_page_map`;
- `sn_network_page_id`, `smp_marketplace_page_id`.

The shell recognizes the actual current module shortcodes, including `slc_learning_home`, `he_encyclopedia_home`, `sdd_doctors_directory`, `swc_worldwide_clinic`, `svw_video_wall`, `srl_reels`, `spl_library`, `srf_radar`, `sai_study_guide`, and the appointment/authentication shortcodes.

## Identity and Publishing

The shell treats Sabri Membership Core as the authoritative identity and permission foundation when available. It recognizes current and legacy founder, trusted publisher, pending doctor, doctor, and verified doctor contracts.

The Create action never falls back to `wp-admin/post-new.php`. It appears only when:

1. the user has authoritative publishing permission; and
2. a real moderated platform composer is resolved.

Login, signup, forgot-password, profile, and completion actions prefer platform-managed public pages. WordPress core login or password recovery is retained only as a safe access fallback when the corresponding public platform page is unavailable.

## Navigation Resolution

Resolution precedence is:

1. configured published Page ID;
2. authoritative companion page contract;
3. published page containing an approved shortcode;
4. existing public post-type archive;
5. published slug candidate;
6. validated URL override;
7. homepage fallback for Home only.

Unresolved destinations are hidden. News does not silently resolve to Home.

Navigation is cached by locale and cache epoch. The cache is invalidated when shell settings, companion page maps, pages, post types, plugins, theme, permalinks, front-page settings, or language context change.

## Notifications

When File 19 is active, the shell uses the real notification bell shortcode and suppresses the companion floating duplicate for that request. Private Messages and Notifications actions are not rendered for logged-out visitors.

## Doctor and Clinic Data

Doctor panels read authoritative public data from existing profile helpers and Membership Core profile, professional credential, and approved clinic records. The shell does not create or own a doctor database. Public contact output remains subject to the source module's approved/public data contract and the shell's `sabri_shell_doctor_public_data` filter.

## Home Feed

`[sabri_shell_home_feed]` is a chronological fallback only. Automatic insertion is suppressed when the page already contains an authoritative platform, File 04, or File 21 feed shortcode. This prevents duplicate Home feeds.

## Installation and Upgrade

1. Take a complete backup.
2. Install or upgrade on Hostinger staging only.
3. Activate the companion modules required for the intended destinations.
4. Open **Sabri Shell > System Check**.
5. Resolve every required destination and review all warnings.
6. Run the full checklist in `STAGING-ACCEPTANCE.md`.
7. Do not deploy to production until founder acceptance and rollback proof are recorded.

See `MIGRATION.md` and `ROLLBACK.md`.

## Safe Mode

Administrators may append:

```text
?sabri_shell_safe=1
```

A developer may also define:

```php
define( 'SABRI_SHELL_DISABLE', true );
```

Both suppress shell rendering without deleting platform content or companion data.

## Automated Verification

Run:

```bash
find . -type f -name '*.php' -print0 | xargs -0 -n1 php -l
node --check assets/js/shell.js
php tests/run.php
```

The repository CI additionally verifies version consistency, CSS brace balance, prohibited DOM-reparenting patterns, prohibited core composer fallback, required corrected contracts, and candidate ZIP integrity when an artifact is present.

Automated checks do not establish WordPress runtime compatibility, live database behavior, cross-browser behavior, accessibility conformance, companion-module end-to-end behavior, or Hostinger staging acceptance.

## Production Limitations

The plugin does not provide or claim:

- a messaging backend, real calls, or audited end-to-end encryption;
- live streaming;
- AI diagnosis or prescription;
- universal compatibility with every WordPress theme;
- production acceptance without staging evidence.

See `REVIEW-CORRECTIONS.md` for the corrective traceability record.
