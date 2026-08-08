# Staging Acceptance

Hostinger staging acceptance is mandatory before production promotion or live activation. Automated QA is repository evidence only and does not satisfy this checklist.

## Exact Candidate and Backup

- Record File 20 `1.4.9`, exact GitHub head, deterministic `1.4.9-NINTH-TEN-ROUND-HARDENED` package, SHA-256 and source manifest.
- Confirm one canonical plugin ZIP root, no development tests, safe paths/no duplicates, embedded/external manifest equality and source/stage/extracted SHA-256 parity.
- Prove a restorable files/database backup; test fresh install and real upgrade from the actually deployed File20 version.

## Ninth-Pass Recovery, Routing and Ownership Gates

- Fresh schema-4 File20 defaults must contain no Appearance group; File25 remains visual authority while legacy Appearance values remain migration-only evidence.
- Generic settings/API writes must not toggle `emergency_disabled`; canonical Emergency Disable requires reason/actor/time/review/audit and re-enable must pass audit/provider-health/cache gates.
- System Check structured evidence, authenticated sanitized export, locale-independent duplicate-shell truth, bounded audit anchor/privacy-erasure rehash and mbstring-optional evidence must remain green.
- Repair dry-run must show exact/planned diff. Verify real normalization plus the separately selectable stale File20 Page-ID quarantine. Only invalid/non-published configured Page IDs may be reset to zero; no page/content/companion mutation.
- Force a settings change between repair preview and execution and verify the under-lock row-version check blocks execution with 409/no write.
- Verify recovery snapshot format 2 distinguishes option absence from null. Legacy/format/code/schema-incompatible snapshots must be read-only for automatic rollback.
- Select the oldest retained compatible target and verify rollback revalidates/holds it under lock before the pre-rollback snapshot can affect retention.
- Verify absent options restore as absent; every restored option is post-write verified; current Emergency state is preserved; Emergency metadata and settings-row version are not restored; settings-row version remains monotonic after real settings restoration; caches purge and smoke test passes.
- Adversarially test strict route overrides: reject HTTP, protocol-relative, credentials, query/fragment/CRLF and unauthorized external hosts; allow external HTTPS only through explicit allowlist. Stored legacy unsafe overrides must be inert on read.
- Verify canonical route order: configured/registered published Page ID → shortcode page → archive → approved slug → validated override → honest unavailable. An arbitrary companion URL callback must not preempt this order.
- In a disposable staging copy only, verify default uninstall is non-destructive. With explicit `delete_on_uninstall=true`, File20-owned operational state/schedule/user welcome preference may purge, but companion pages/content/tables/options must remain untouched.

## Existing Security, Integration and Future Shell Gates

- Preserve File00 identity, File02 authentication, File19 one-bell, File21 five native slots, File24 assurance/native enforcement, File25 visual truth and File26 Search/Discovery/Ranking ownership.
- CF-01..CF-06 remain conditional; registry presence is not activation/runtime proof. Preserve CF-04 token/range/cache authority, one-free-tier/voluntary-donation/zero-commission/donor-neutral law, File17 + activated CF-04 1 GB transfer and CF-06 localization truth.
- Test root/subdirectory sensitive routes, all four layout modes, five release rings, PWA lifecycle/privacy, Split Workspace exclusions, keyboard/focus/dialogs, screen readers, RTL/LTR, reduced motion, 200%/400% zoom, representative browsers/devices, slow/offline/Save-Data and LiteSpeed/Hostinger cache behavior.

## Acceptance Record

Record tester, date/time, environment, exact package/head/checksum, actual companion versions/contracts, screenshots/logs, defects and retests, backup restore proof, rollback evidence and Founder decision.

**Pass condition:** zero known unresolved release-blocking defects, or an explicitly permitted and documented Founder risk acceptance. Staging acceptance still does not equal Live-Deployed or Operational status.
