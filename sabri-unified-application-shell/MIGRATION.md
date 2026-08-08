# Migration and Upgrade

## Governing Rule

All installation, upgrade, repair and rollback work must be performed on staging first. Version **1.4.6** is a repository/code/package/automated-QA candidate only until Hostinger staging, real companion integration, backup/restore/rollback rehearsal and Founder acceptance pass.

## Upgrade to 1.4.6

1. Take and verify a restorable files-and-database backup before upload.
2. Record the actually deployed File 20 version/commit/package checksum, current shell settings, page mappings, navigation state, active theme and actual companion versions.
3. Install the exact `1.4.6` deterministic candidate on staging without deleting or mutating companion-owned data.
4. Confirm the installable ZIP has one canonical plugin root, excludes repository `tests/`, and has source/stage/extracted file-set and SHA-256 parity evidence plus safe-path checks.
5. Activate/update File 20. Version 1.4.6 adds no File 20 database table and no companion-domain data store.
6. Flush/reconcile rewrite rules so current PWA manifest/service-worker virtual routes are registered.
7. Run **Sabri Shell > System Check**. The sixth-pass compatibility section must identify its facts as declared compatibility targets, not live detection.
8. Independently verify the actually installed/runtime companion contracts before enabling dependent actions:
   - File 00 target: runtime 1.2.13, schema 1.3.0, Public Membership 1.2.0, CF-01 1.0.0, Advanced Trust 1.0.0;
   - File 01-B target: runtime 2.0.0, schema 1.2.0, Foundation Contract 2.0.0, Future Foundation 18;
   - File 02 target: runtime 1.3.1, DB 1.3.0, passkey schema 1.1.0, auth-event projection 1.1.0, 24 enhancements;
   - File 24 target: runtime candidate 0.99.0, schema 0.25.5, 25 Future Security requirements.
9. Confirm File 02 current task routes remain owner-controlled and `/.well-known/webauthn` remains public JSON/no-cache with Minimal/no visual shell.
10. Confirm privileged password reset remains a File 02 action requiring the File 00 dual-control receipt; File 20 must never invent or bypass that receipt.
11. Simulate File 24 unavailability and verify native File 02/other owner security remains enforced while File 20 only renders an honest unavailable/unknown state.
12. Test all five release-ring states; REST configuration remains `manage_options` only and Internal remains fail closed without manager or explicit approved internal-principal contract.
13. Test PWA root/subdirectory registration, update, offline public navigation, protected-route bypass, disable/410 retirement and worker self-removal.
14. Confirm File 04/File 21 output, File 19 one-bell, File 25 visual ownership and File 26 Search/Discovery/Ranking are not duplicated.
15. Confirm the theme content wrapper/landmarks remain intact and all four File 20 layout modes resolve correctly.
16. Complete `STAGING-ACCEPTANCE.md` and rollback rehearsal before any production decision.

## From 1.4.5

The `1.4.5 → 1.4.6` upgrade is non-destructive and compatibility/evidence focused.

It:

- adds the sixth hardening contract `1.0.6`;
- updates declared File 00/01/02/24 compatibility targets;
- adds missing File 00 CF-01 and File 01 Foundation Contract/schema facts;
- advances File 02 from the superseded `1.3.0` target to `1.3.1` and records its current schema/event/dual-control boundaries;
- advances File 24 to `0.99.0`/schema `0.25.5`/25 Future Security requirements;
- explicitly labels static contract-registry facts as **compatibility targets, not runtime health**;
- adds sixth-pass regression and deterministic release evidence.

It does **not** migrate or rewrite File 00, 01, 02, 19, 21, 24, 25 or 26 native records.

## From 1.4.4 or Earlier 1.4.x

Upgrade directly to 1.4.6 on staging. Prior privacy/PWA/release-ring/package hardening is cumulative. Do not manually recreate intermediate packages.

If a configured/provider private-path registry exceeds the supported bound, the current release intentionally disables privacy-sensitive shell conveniences and service-worker interception until the policy is valid. Do not remove legitimate protected paths merely to re-enable convenience features.

## From Older File 20 Releases

1. Perform the same verified backup and staging-only upgrade.
2. Run historical File-20-owned settings/schema migrations idempotently through the current plugin.
3. Validate current identity/authentication/notification/Home-News/security/visual/search owner contracts rather than relying on old role/meta/search fallbacks.
4. Test Safe Mode, Repair, LKG recovery, PWA retirement, release rings and rollback from the actually deployed baseline.

## From No Shell

Install on staging, activate required companion modules, run System Check, configure unresolved destinations only, and complete full staging acceptance/rollback proof. Do not create duplicate pages or databases when a companion already owns them.

## From Another Shell

Disable the other shell on staging, activate File 20, remove duplicate theme/header rules only after visual inspection, and confirm one header, one primary navigation, one Home feed, one notification output and one canonical Search/Discovery path.

No migration step creates, deletes or mutates membership identity, authentication/passkeys, messaging, notification, appointment, profile, marketplace, clinic, publishing, search/ranking, security-assurance or clinical records owned by companion files.
