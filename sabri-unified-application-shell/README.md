# Sabri Unified Application Shell

Sabri Unified Application Shell is the independent, responsive WordPress application shell for the **Sabri Social Homeopathy Platform**.

- Version: `1.1.1`
- File 22 Create contract: `1.0.1`
- Status: corrective candidate; Hostinger staging acceptance required
- Plugin slug: `sabri-unified-application-shell`
- Text domain: `sabri-unified-application-shell`
- Author: Dr. Allamah Majid Hussain Sabri Muhaddith Mursheed

## Scope

File 20 owns the shared presentation and routing layer:

- global header, search, role-aware Create, Messages, Notifications, Help, language and account controls;
- primary navigation, persistent left navigation, contextual right panels, mobile drawers and bottom navigation;
- conservative layout resolution for three-column, two-column and minimal contexts;
- companion page/shortcode/profile/clinic/appointment/notification/publishing adapters;
- settings, System Check, Complete Repair, Safe Mode, snapshots and rollback.

It does **not** create duplicate membership, publishing, profile, messaging, notification, appointment, marketplace, clinic or clinical databases.

## Version 1.1.1 correction

Version 1.1.1 supplies the exact package-owned Create producer contract expected by File 22:

- `SABRI_SHELL_CREATE_CONTRACT_VERSION = 1.0.1`;
- `SABRI_SHELL_CREATE_CONTRACT_OWNER = sabri-unified-application-shell`;
- `SABRI_SHELL_CREATE_FUNCTIONS_OWNED = true`;
- `sabri_shell_create_contract_available()`;
- `sabri_shell_create_visible_for_current_user()`.

The contract:

1. is declared only when the complete symbol family is unclaimed;
2. proves the SafeMode and CreateContract classes originate from the exact package files;
3. exposes producers from the canonical plugin bootstrap;
4. accepts no foreign subject ID and resolves only the current logged-in user;
5. rechecks Safe Mode, shell settings, File 00 publishing assertions, canonical Create URL and File 22 adapter availability;
6. fails closed on recursion, exceptions, partial ownership or unavailable authority.

The same corrective release:

- applies the final Create decision to public presentation;
- separates the account name and `Signed in` status;
- wraps desktop navigation without creating a page-level horizontal scrollbar;
- repairs the safe content column for File 21 managed single publications through bounded retries;
- never reparents, replaces or deletes theme or companion DOM nodes;
- contains article bodies, actions, comments, media, tables and long words within the available column.

## Version 1.1.0 foundation

Version 1.1.0 replaced broad theme-wrapper reparenting with conservative in-place annotation, preserved theme IDs and hierarchy, corrected companion page-map/shortcode discovery, removed the WordPress admin editor as a Create fallback, prevented duplicate feeds and notification bells, and hardened File 00/File 03 authority boundaries.

## Identity and publishing

Sabri Membership Core is authoritative for current membership and publishing assertions. File 20 does not invent a fallback publisher role. The Create action appears only when a real moderated composer is resolved and the current File 00/File 22 decision allows it.

## Home Feed

`[sabri_shell_home_feed]` is a chronological fallback only. Automatic insertion is suppressed when an authoritative platform or File 21 feed is already present.

## Installation and staging acceptance

1. Take a complete files and database backup.
2. Install or upgrade on Hostinger staging only.
3. Activate the companion modules required by the intended destinations.
4. Open **Sabri Shell → System Check**.
5. Run **File 22 → Universal Composer Health** and verify both File 20 contract checks pass.
6. Retest `/create/`, the controlled File 22 publication, and a long File 21 article.
7. Test desktop, tablet, mobile, keyboard, RTL, Safe Mode and rollback.
8. Do not promote to production until founder acceptance is recorded.

See `STAGING-ACCEPTANCE.md`, `MIGRATION.md`, `ROLLBACK.md`, `CHANGELOG.md`, and the repository-level `FILE-20-CREATE-CONTRACT-LAYOUT-CORRECTION-1.1.1.md`.

## Safe Mode

Administrators may append:

```text
?sabri_shell_safe=1
```

A developer may also define:

```php
define( 'SABRI_SHELL_DISABLE', true );
```

Both suppress shell rendering without deleting platform or companion data.

## Automated verification

```bash
find . -type f -name '*.php' -print0 | xargs -0 -n1 php -l
node --check assets/js/shell.js
node --check assets/js/shell-corrective-1.1.1.js
php tests/run.php
php tests/run-create-contract-layout.php
```

Repository CI additionally verifies PHP 7.4/8.3 behavior, exact contract ownership, prohibited bypasses and DOM movement, CSS/JavaScript integrity, deterministic packaging, source equality, SHA-256 and ZIP CRC.

Automated QA does not itself establish Hostinger staging, cross-browser, accessibility or production acceptance.
