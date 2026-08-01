# Migration and Upgrade

## Governing Rule

All installation, upgrade, repair, and rollback work must be performed on staging first. Version 1.1.0 is a corrective release candidate and must not be treated as production-accepted merely because static checks pass.

## Upgrade from 1.0.0

1. Take a complete files-and-database backup.
2. Record current front-page, posts-page, shell, navigation, theme-visibility, and companion page-map settings.
3. Install 1.1.0 on staging without deleting companion plugins or their data.
4. Activate the plugin. The schema upgrade is idempotent and preserves unknown future settings.
5. Run **Sabri Shell > System Check**.
6. Confirm that required destinations resolve through existing companion page maps or approved shortcodes.
7. Confirm that Create opens the moderated public composer and never the WordPress admin post editor.
8. Confirm that the theme's original content wrapper ID and DOM position remain unchanged.
9. Confirm that File 04/File 21 Home output and File 19 Notifications output are not duplicated.
10. Complete `STAGING-ACCEPTANCE.md`.

## From No Shell

1. Install on staging.
2. Activate the required identity, authentication, content, clinic, communication, and notification modules.
3. Run System Check.
4. Configure only unresolved destinations; do not create duplicate pages or databases when a companion module already owns them.
5. Complete staging acceptance and rollback proof.

## From Another Shell

1. Disable the other shell on staging.
2. Activate this plugin.
3. Remove duplicate theme/header visibility rules only after visual inspection.
4. Confirm one header, one primary navigation, one Home feed, and one Notifications output.
5. Confirm existing posts, pages, users, media, comments, and companion data remain unchanged.

No migration step creates or deletes messaging, notification, appointment, profile, marketplace, clinic, publishing, or clinical records.
