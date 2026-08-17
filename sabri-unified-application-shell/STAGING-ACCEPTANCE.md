# Staging Acceptance

Hostinger staging acceptance is the normal production-promotion gate. Automated QA is repository evidence only and does not satisfy this checklist. For a proven production incident requiring a bounded live repair, record equivalent live evidence, backup, exact package/checksum, deployment parity and original-symptom retest; staging and live remain separate realities.

## Exact Candidate and Backup

- Record File 20 `1.4.14`, exact GitHub head, deterministic `20-sabri-unified-application-shell-1.4.14-REVIEW1-RELEASE-TRUTH-CORRECTION.zip` package, SHA-256 and source manifest.
- Confirm one canonical plugin ZIP root, no development tests, safe paths/no duplicates, embedded/external manifest equality and source/stage/extracted SHA-256 parity.
- Prove a restorable files/database backup; test fresh install and real upgrade from the actually deployed File20 version where staging is the selected deployment path.
- Confirm the 1.4.13 Renderer correction remains unchanged: `render_panel()`, `destination_url()`, and `item_visible_to_user()` each exist exactly once and all owned `self::method()` calls resolve.

## Original Live-Incident Regression Gate

- If closing the production incident that triggered 1.4.13, freeze and record the exact deployed File20 version and critical deployed-file/artifact parity before replacement.
- After deploying the exact approved 1.4.14 artifact, confirm the live runtime reports `1.4.14`.
- Re-test `/google-account-security/`. It must not return HTTP 500 and the historical `Sabri\UnifiedShell\Renderer::item_visible_to_user()` undefined-method fatal must be absent from current logs.
- A green staging result or green GitHub Actions result does not by itself prove live resolution.

## Canonical Ownership and Health Gates

- File20's historical local Home-feed runtime must not register. Persisted `home_feed` state must be inert (`retired=true`, `auto_insert=false`, `posts_count=0`), and `sabri_shell_home_feed` must not act as an active route source.
- With the real File21 package active, verify exactly these native hooks at approved positions: `sabri_shell_home_before_main`, `sabri_shell_home_main`, `sabri_shell_home_after_main`, `sabri_shell_home_right_sidebar`, `sabri_shell_news_main`.
- Native File21 Home/News output must appear once. Where native main output exists, legacy page/shortcode fallback must not render a second authoritative surface; where provider output is absent, safe legacy content may remain as compatibility fallback.
- File25 remains visual authority. File20 body classes must be structural only and no fresh Appearance group may be created.
- File26 remains Search/Discovery/Ranking authority. Without a validated File26 contract, Search must be unavailable/hidden; no WordPress/File20 fallback may appear.
- Verify File00 runtime/version/health independently. File20 may display reviewed audit metadata, but static metadata must not become runtime-health, authorization, staging or production-safe proof.
- Force File20/File00 critical provider states through Healthy, Unknown, Unavailable and Incompatible fixtures. Overall health may be Healthy only when both critical contracts are verified Healthy. Optional provider failure may degrade the shell but must not be fabricated as critical health.
- For File25/File01-B, test malformed, below-minimum and compatible advertised semantic versions. Malformed must be unavailable; below-minimum incompatible; compatible may pass if the native probe also passes.
- Emergency Disable must remain canonical/audited. Re-enable must fail on invalid audit integrity and whenever critical File20/File00 health is not Healthy; successful re-enable still requires cache purge.

## Recovery, Routing and Existing Gates

- Generic settings/API writes must not toggle `emergency_disabled` around the canonical lifecycle.
- System Check structured evidence, authenticated sanitized export, locale-independent duplicate-shell truth, bounded audit anchor/privacy-erasure rehash and mbstring-optional evidence must remain green.
- Repair dry-run must show exact/planned diff. Verify real normalization plus separately selectable stale File20 Page-ID quarantine. Only invalid/non-published configured Page IDs may be reset to zero; no page/content/companion mutation.
- Force a settings change between repair preview and execution and verify the under-lock row-version check blocks execution with 409/no write.
- Verify recovery snapshot format 2 distinguishes option absence from null. Legacy/format/code/schema-incompatible snapshots must be read-only for automatic rollback.
- Select the oldest retained compatible target and verify rollback revalidates/holds it under lock before the pre-rollback snapshot can affect retention.
- Verify automatic rollback scope is File20-owned options only. Shared WordPress front-page options such as `show_on_front`, `page_on_front`, and `page_for_posts` must not be automatically restored by current File20 recovery.
- Verify absent File20-owned options restore as absent; every restored option is post-write verified; current Emergency state is preserved; Emergency metadata and settings-row version are not restored; settings-row version remains monotonic after real settings restoration; caches purge and smoke test passes.
- Adversarially test strict route overrides and canonical route order: configured/registered published Page ID → shortcode page → archive → approved slug → validated override → honest unavailable.
- In a disposable staging copy only, verify default uninstall is non-destructive. With explicit `delete_on_uninstall=true`, File20-owned operational state/schedules may purge, but companion pages/content/tables/options must remain untouched.
- Verify query Safe Mode is administrator-authenticated and nonce-bound. Raw `?sabri_shell_safe=1` without the product-generated nonce must not enable it. The `SABRI_SHELL_DISABLE` constant remains the emergency configuration fallback.

## Existing Security, Integration and Future Shell Gates

- Preserve File00 identity, File02 authentication, File19 one-bell, File21 native ownership, File24 assurance/native enforcement, File25 visual truth and File26 Search/Discovery/Ranking ownership.
- CF-01..CF-06 remain conditional; registry presence is not activation/runtime proof. Preserve CF-04 token/range/cache authority, one-free-tier/voluntary-donation/zero-commission/donor-neutral law, File17 + activated CF-04 1 GB transfer and CF-06 localization truth.
- Test root/subdirectory sensitive routes, all four layout modes, five release rings, PWA lifecycle/privacy, Split Workspace exclusions, keyboard/focus/dialogs, screen readers, RTL/LTR, reduced motion, 200%/400% zoom, representative browsers/devices, slow/offline/Save-Data and LiteSpeed/Hostinger cache behavior.

## Acceptance Record

Record tester, date/time, environment, exact package/head/checksum, actual companion versions/contracts, screenshots/logs, defects and retests, backup restore proof, rollback evidence and Founder decision.

**Pass condition:** zero known unresolved release-blocking defects, or an explicitly permitted and documented Founder risk acceptance. Staging acceptance still does not equal Live-Deployed or Operational status.
