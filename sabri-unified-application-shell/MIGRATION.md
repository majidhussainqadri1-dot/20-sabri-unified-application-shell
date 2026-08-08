# Migration and Upgrade

## Governing Rule

All installation, upgrade, repair and rollback work must be performed on staging first. Version **1.4.3** is a repository/code/package/automated-QA candidate only until Hostinger staging, real companion integration, backup/restore/rollback rehearsal and Founder acceptance pass.

## Upgrade to 1.4.3

1. Take and verify a restorable files-and-database backup before upload.
2. Record the deployed File 20 version/commit/package checksum, current shell settings, page mappings, navigation state, active theme and companion versions.
3. Install the exact 1.4.3 deterministic candidate on staging without deleting companion plugins or their data.
4. Activate/update File 20. The release adds **no new File 20 database table** and must preserve unknown future settings and companion-owned data.
5. Flush/reconcile rewrite rules so the PWA manifest/service-worker virtual routes are current.
6. Run **Sabri Shell > System Check** and confirm the third-hardening section reports the privacy policy as complete. Any protected-path overflow is a fail-closed staging finding, not permission to relax privacy.
7. Confirm current File 00/01/02 task routes—especially account security, passkeys, collision resolution, membership/guardian/security and platform-system status—use Minimal presentation and private/no-store/noindex behavior where specified by their owners.
8. Confirm PWA behavior on both root and WordPress-subdirectory staging: registration, update, offline public navigation, protected-route bypass, disable/410 retirement and worker self-removal.
9. Confirm Create opens the moderated File 22 composer and never a shell-owned or WordPress-admin publishing fallback.
10. Confirm File 04/File 21 Home output, File 19 Notifications output, File 25 visual ownership and File 26 Search/Discovery/Ranking are not duplicated.
11. Confirm the theme's original content wrapper/landmarks remain intact and all four File 20 layout modes still resolve correctly.
12. Complete `STAGING-ACCEPTANCE.md`, then perform rollback rehearsal before any production decision.

## From 1.4.2

The 1.4.2 → 1.4.3 upgrade is code/configuration-compatible and non-destructive. It tightens route/privacy classification, PWA callback ownership and cross-file metadata; it does not migrate or rewrite File 00, 01, 02, 19, 21, 24, 25 or 26 native records.

If a configured/provider private-path registry exceeds the supported bound, 1.4.3 intentionally disables privacy-sensitive shell conveniences and service-worker interception until the policy is reduced or split by an approved owner contract. Do not work around this by removing legitimate protected paths.

## From Older File 20 Releases

1. Perform the same verified backup and staging-only upgrade.
2. Run all historical settings/schema migrations idempotently through the current plugin.
3. Validate the current File 00 identity claims, File 02 authentication routes, File 19 one-bell, File 21 Home/News slots, File 25 visual contract and File 26 search contract rather than relying on old role/meta/search fallbacks.
4. Test Safe Mode, Repair, LKG recovery, PWA retirement and rollback from the actual deployed baseline.

## From No Shell

1. Install on staging.
2. Activate the required identity, authentication, content, clinic, communication and notification modules.
3. Run System Check.
4. Configure only unresolved destinations; do not create duplicate pages or databases when a companion module already owns them.
5. Complete staging acceptance and rollback proof.

## From Another Shell

1. Disable the other shell on staging.
2. Activate File 20.
3. Remove duplicate theme/header visibility rules only after visual inspection.
4. Confirm one header, one primary navigation, one Home feed, one Notifications output and one canonical Search/Discovery provider path.
5. Confirm existing posts, pages, users, media, comments and companion data remain unchanged.

No migration step creates, deletes or mutates membership identity, authentication/passkeys, messaging, notification, appointment, profile, marketplace, clinic, publishing, search/ranking, security-assurance or clinical records owned by companion files.
