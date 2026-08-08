# Migration and Upgrade

## Governing Rule

All installation, upgrade, repair and rollback work must be performed on staging first. Version **1.4.7** is a repository/code/package/automated-QA candidate only until Hostinger staging, real companion integration, backup/restore/rollback rehearsal and Founder acceptance pass.

## Upgrade to 1.4.7

1. Take and verify a restorable files-and-database backup before upload.
2. Record the actually deployed File 20 version/commit/package checksum, current shell settings, page mappings, navigation state, active theme, actual File 00–26 companion versions and any deliberately activated CF modules.
3. Install the exact `1.4.7` deterministic candidate on staging without deleting or mutating companion-owned data.
4. Confirm the installable ZIP has one canonical plugin root, excludes repository `tests/`, and has source/stage/extracted file-set and SHA-256 parity evidence plus safe-path checks.
5. Activate/update File 20. Version 1.4.7 adds no File 20 database table and no clinical/support/finance/media/analytics/localization domain data store.
6. Flush/reconcile rewrite rules so current PWA manifest/service-worker virtual routes are registered.
7. Run **Sabri Shell > System Check**. Sixth-pass current compatibility facts and seventh-pass CF facts must be identified as declared targets, never live detection.
8. Independently verify the actually installed/runtime File 00–26 contracts before enabling dependent actions.
9. For CF-01..CF-06, verify explicit native module activation and required Founder/change-control evidence before surfacing any conditional action. File 20 registry presence alone is never activation.
10. Verify CF-01 sensitive clinical routes remain private and File 20 performs no clinical authorization, consent, prescription or break-glass action.
11. Verify CF-02 public help remains public while cases/appeals/admin/API surfaces remain private and native-owner controlled.
12. Verify CF-03 current law remains single free tier, voluntary donation, zero commission and no donor advantage. Paid collection must remain dormant unless a later Founder decision and native CF-03 activation authorize it.
13. Verify CF-04 upload/admin/API surfaces are protected. Verify `/media/d/{grant}` receives Minimal/no visual Future Shell treatment but native CF-04 retains token, range and cache semantics.
14. Verify CF-05 Insights/admin/API surfaces remain authorized/private and File 20 creates no event-ingestion or analytics store.
15. Verify CF-06 language/direction shell controls consume an approved provider; File 20 creates no locale registry or translation bundle truth.
16. Verify verified transfer remains File 17 + activated CF-04 with the approved 1 GB per-file limit and eligible download remains native-owner/File-24/CF-04 territory.
17. Confirm File 02 privileged reset remains a native action requiring the File 00 dual-control receipt where specified; File 20 must never invent or bypass it.
18. Test all five release-ring states and root/subdirectory PWA lifecycle/privacy behavior.
19. Confirm File 04/File 21 output, File 19 one-bell, File 25 visual ownership and File 26 Search/Discovery/Ranking are not duplicated.
20. Complete `STAGING-ACCEPTANCE.md`, backup/restore and rollback rehearsal before any production decision.

## From 1.4.6

The `1.4.6 → 1.4.7` upgrade is non-destructive and shell-integration focused.

It:

- adds seventh hardening contract `1.0.7`;
- declares CF-01 through CF-06 conditional integration targets without activating their backends;
- adds privacy-aware shell handling for sensitive CF routes;
- keeps CF-04 token-delivery cache/range authority native while suppressing Future Shell visual/client conveniences on `/media/d/{grant}`;
- preserves the current single-free-tier, voluntary-donation, zero-commission and donor-neutral financial law;
- reconciles File 17 + CF-04 verified transfer at the 1 GB per-file limit, native/File-24/CF-04 download ownership and CF-06 localization-provider ownership;
- adds seventh-pass regressions and deterministic 1.4.7 package evidence.

It does **not** migrate, activate or rewrite CF-01..CF-06 native records or File 00–26 native records.

## From Earlier 1.4.x

Upgrade directly to 1.4.7 on staging. Prior privacy/PWA/release-ring/package and compatibility hardening is cumulative. Do not manually recreate intermediate packages.

If the configured/provider private-path registry exceeds the supported bound, the current release intentionally disables privacy-sensitive shell conveniences and service-worker interception until the policy is valid. Do not remove legitimate protected paths merely to re-enable convenience features.

## From Older File 20 Releases / No Shell / Another Shell

Use the same verified backup and staging-only process. Validate current native-owner contracts, run System Check, avoid duplicate pages/databases/backends, test Safe Mode/Repair/LKG/PWA/rollback, and complete staging acceptance before production. Another structural shell must not remain concurrently authoritative.

No migration step creates, deletes or mutates membership identity, authentication/passkeys, messaging, notification, appointment, profile, marketplace, clinic, publishing, search/ranking, security-assurance, clinical, support-case, financial-ledger, media-processing, analytics or localization records owned by companion files.
