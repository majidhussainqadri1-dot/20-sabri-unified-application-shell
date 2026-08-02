# Sabri Unified Application Shell

Sabri Unified Application Shell is the independent, responsive WordPress application shell for the **Sabri Social Homeopathy Platform**.

- Version: `1.2.0`
- File 22 Create contract: `1.0.1`
- Central-plan contract: `1.0.0`
- Status: corrective candidate; Hostinger staging acceptance required
- Plugin slug/text domain: `sabri-unified-application-shell`
- Author: Dr. Allamah Majid Hussain Sabri Muhaddith Mursheed

## Scope

File 20 owns global structural presentation and routing: header, one horizontal no-wrap primary navigation line, left and contextual right panels, mobile drawers, bottom navigation, route mounting, exact Three/Two/Minimal/Immersive layouts, System Check, Complete Repair, Safe Mode, snapshots and rollback.

It does **not** create duplicate membership, publishing, profile, messaging, notification, appointment, marketplace, clinic, clinical or security-assurance databases.

## Version 1.2.0 central-plan correction

Version 1.2.0 reconciles runtime code with Definitive Master Plan v3.0 and File 20 v4.0:

1. adds the missing Immersive mode for Reels, full-screen video/live and PDF reader contexts;
2. keeps profiles/timelines and the File 23 Publishing Dashboard in Two-column contexts;
3. enforces a one-line no-wrap desktop primary navigation with bounded horizontal overflow;
4. publishes a complete File 00–25 ownership/dependency/failure registry;
5. consumes File 25's versioned visual contract and marks fallback state truthfully;
6. retires File 20's legacy Appearance editor while preserving old values as migration-only data;
7. preserves the exact package-owned File 22 Create contract `1.0.1` and File 00 fail-closed authority.

## File 25 visual boundary

File 25 is canonical for colors, typography, spacing, radius, shadows, component states, profiles/timelines and visual-regression governance. File 20 owns only shell geometry and consumes `sabri_shell_file25_visual_contract`. If File 25 is absent or incompatible, File 20 uses a sanitized continuity fallback and exposes `sabri-shell-visual-fallback`; it does not claim File 25 integration succeeded.

## Layout constitution

- **Three:** Home, Worldwide Clinic directory, single doctor/clinic.
- **Two:** ordinary public pages, directories, profiles/timelines, knowledge pages and private applications.
- **Minimal:** authentication, registration, recovery, verification, feeds, REST/AJAX/cron, Safe Mode and Repair.
- **Immersive:** Reels, full-screen video/live and PDF reader.

## Installation and staging acceptance

1. Back up files and database.
2. Install or upgrade on Hostinger staging only.
3. Activate required companion modules.
4. Run **Sabri Shell → System Check**.
5. Verify File 22's two File 20 contract checks.
6. Verify File 25 provider status or the truthful fallback marker.
7. Test all four layout modes from 320–1920 px, keyboard, screen reader, RTL and supported browsers.
8. Rehearse Safe Mode, Repair, backup/restore and rollback.
9. Do not promote until Founder acceptance is recorded.

## Automated verification

```bash
find . -type f -name '*.php' -print0 | xargs -0 -n1 php -l
node --check assets/js/shell.js
node --check assets/js/shell-corrective-1.1.1.js
php tests/run.php
php tests/run-create-contract-layout.php
php tests/run-central-plan-v4.php
```

See `CENTRAL-PLAN-V4-TRACEABILITY.md` and `contracts/file20-central-plan-v4.json`. Automated QA does not itself establish staging, accessibility or production acceptance.
