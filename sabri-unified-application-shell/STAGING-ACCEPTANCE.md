# Staging Acceptance

Hostinger staging acceptance is mandatory before merge to the production release line or live activation.

## Backup and Upgrade

- Verify a restorable files-and-database backup.
- Test both fresh installation and upgrade from 1.0.0.
- Verify schema version, settings preservation, cache invalidation, and rewrite flushing.
- Verify rollback restores shell-owned settings and captured front-page values only.

## Integration Contracts

- Activate the intended companion modules in the approved dependency order.
- Confirm Home, News, Founder, Learn, Encyclopedia, Doctors, Worldwide Clinic, Video Wall, Reels, PDF Library, Radar, AI, Network, Marketplace, Appointments, Messages, and Notifications resolve to real pages.
- Confirm unresolved optional destinations are hidden and no dead `#` links appear.
- Confirm public login, signup, password recovery, profile, and completion routes use the platform pages.
- Confirm Create appears only for Founder, Administrator, trusted publishers, and eligible verified doctors, and opens the moderated public composer.
- Confirm patient, student, pending doctor, unverified doctor, and logged-out accounts cannot reach a publishing action through the shell.

## Layout and Theme Safety

- Record the active theme content wrapper ID and parent before activation; confirm both remain unchanged afterward.
- Confirm the shell does not move `.wp-site-blocks`, `#page`, `.site`, the theme header, or the theme footer.
- Confirm Home, Worldwide Clinic directory, and doctor/clinic contexts receive the approved three-column behavior.
- Confirm other public content receives the approved two-column behavior.
- Confirm authentication, password recovery, verification, system, feed, REST, AJAX, cron, XML-RPC, robots, sitemap, embed, preview, print, Safe Mode, and maintenance contexts remain minimal.
- Confirm the right sidebar is absent from the DOM in two-column mode.
- Confirm no horizontal page overflow.

## Duplication and Ownership

- Confirm one global header and one primary navigation.
- Confirm File 04/File 21 Home or News output is not duplicated by the fallback feed.
- Confirm exactly one Notifications bell/output, with real unread behavior from File 19.
- Confirm the shell creates no duplicate profile, clinic, appointment, message, notification, marketplace, publishing, or clinical tables.

## Doctor and Clinic Workflows

- Test Founder, trusted verified doctor, ordinary verified doctor, pending doctor, patient, student, and general member accounts.
- Confirm verified doctor discovery uses the real verification status.
- Confirm directory filters use the real country, city, language, qualification, experience, and consultation-mode contracts as applicable.
- Confirm profile, clinic, fee, currency, timings, specialty, language, phone, WhatsApp, Message, and Appointment actions display only approved public data.
- Confirm private identity, date of birth, documents, patient records, and private contact fields never appear.

## Accessibility and Responsive Acceptance

Test at 320, 360, 390, 480, 768, 900, 1024, 1100, 1280, 1366, 1440, 1600, and 1920 px.

- keyboard navigation and visible focus;
- skip link target;
- drawer focus trap, Escape close, outside-click close, focus restoration, and body scroll lock;
- 44 px minimum mobile touch targets;
- reduced-motion behavior;
- RTL readiness;
- no clipping, sidebar overlap, or inaccessible fixed content.

## Runtime and Browser Matrix

- WordPress 7.0.1 and PHP 8.3.30 on project staging;
- current Chrome, Firefox, Edge, and Safari where available;
- logged-in and logged-out cache behavior;
- LiteSpeed/Hostinger cache purge and regeneration;
- no PHP warnings, notices, fatal errors, JavaScript errors, database errors, or Safe Mode regression.

## Acceptance Record

Record tester, date, environment, plugin versions, screenshots, defects, repairs, retest evidence, backup restore evidence, rollback evidence, and Founder decision.

Automated CI does not satisfy this checklist.
