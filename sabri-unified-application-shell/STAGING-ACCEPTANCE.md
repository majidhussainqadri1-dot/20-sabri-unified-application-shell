# Staging Acceptance

Hostinger-equivalent staging acceptance is the normal production-promotion gate. Automated QA is repository evidence only and does not satisfy this checklist. A proven production incident may use equivalent bounded Live evidence, but staging and Live remain separate realities.

## Exact candidate and backup

- Record File 20 `1.4.17`, exact GitHub head, deterministic `20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER.zip` package, SHA-256 and source manifest.
- Confirm one canonical plugin ZIP root, no development tests, safe paths/no duplicates, embedded/external manifest equality and source/stage/extracted SHA-256 parity.
- Prove a restorable files/database backup; test fresh install and the real upgrade path from the actually deployed File20 version.
- Record actual WordPress/PHP/database/cache/theme and companion-module versions before acceptance.

## Canonical programmatic settings writer gate

- `Settings::update_programmatically()` must be the sole trusted full `sabri_shell_settings` writer outside the tab-oriented Settings API submission path.
- File01 reconciliation, PlanV4 repair/rollback, Emergency lifecycle, activation-snapshot rollback, defaults normalization and retired-state migration must use the canonical writer.
- Dynamic active-sanitizer regression and static no-direct-write regression must pass.
- Additional source-hardened paths remain environment-unproven until actually exercised; repository evidence must not be converted into a Live claim.

## File01 reconciliation gate

- Before any File01 reconciliation write, deploy the exact approved **1.4.17** candidate to the selected environment and prove File20 runtime/package parity.
- Run File01 reconciliation dry-run only. `home` and `news` must remain accepted by `file-21`.
- The twelve File20 handoffs must each return an accepted `file-20` owner plan with command version `1.0.1`.
- File01 blocker count must be exactly zero before Apply Reconciliation is permitted.
- Every File20 plan must state `shell_navigation_reference_only`; File20 must not claim native content/domain ownership from Files 03/05/06/07/08/10/11/12/15/16/17/18.
- Record the reviewed File01 plan hash. If it changes before apply, stop and regenerate/review the dry-run.
- Apply only through File01's controlled reconciliation action.
- Verify File01 state is `applied`, all fourteen owner receipts are present, `spf_page_map` and `spf_founder_user_id` are retired by File01, all twelve File20 route references persist, Home/News remain File21-owned and no compensation/incomplete state remains.
- Rehearse rollback where permitted; exact prior File20 navigation rows must restore through the canonical writer, the Settings sanitizer must be restored, state/receipt/plan drift must fail closed and replay must remain idempotent.

## Historical live-incident regression

- Preserve the dated 2026-08-29 File01 reconciliation incident as scoped Live evidence only.
- Re-test `/google-account-security/`; it must not return HTTP 500 and the historical `Sabri\UnifiedShell\Renderer::item_visible_to_user()` undefined-method fatal must remain absent.
- A green staging or GitHub result does not by itself prove a separate Live incident resolved.

## Canonical ownership and health gates

- File20's historical local Home-feed runtime must remain retired and inert. File21 remains canonical Home/News owner.
- With File21 active, verify the approved native Home/News slots render once and fallback does not duplicate authoritative output.
- File25 remains visual authority. File20 may consume a validated File25 contract and may use only a continuity fallback when File25 is unavailable.
- File20 continuity fallback primary must match the current governing Sabri Green `#087A4E`; it must not revive a retired primary token.
- File26 remains Search/Discovery/Ranking authority. Without a validated File26 contract, Search must be unavailable/hidden; no WordPress/File20 search fallback may appear.
- Verify File00 runtime/version/health independently. Static metadata is not runtime health or authorization proof.
- Critical File20/File00 Unknown, Unavailable or Incompatible states must never become Healthy.
- Emergency re-enable must fail when critical health is not Healthy or audit integrity is invalid.

## Recovery, routing and privacy gates

- Generic settings/API writes must not toggle `emergency_disabled` around the canonical lifecycle.
- Repair must support dry-run exact diff, bounded File20-only corrections, under-lock row-version revalidation and no false success.
- Recovery snapshot format/integrity/code-major/settings-schema compatibility must be revalidated before automatic rollback.
- Automatic rollback must not restore shared WordPress front-page options.
- Route precedence and same-origin override validation must remain fail-closed; unauthorized/stale/deleted objects must not leak through route/cache behavior.
- Query Safe Mode must be administrator-authenticated and nonce-bound; raw query state alone must not enable it.
- Default uninstall must be non-destructive; explicit purge may delete only File20-owned state.

## Future Shell, responsive and accessibility gates

- Test all four layout modes and all five release rings.
- Test PWA install/update/retirement/privacy behavior, offline/weak-network state, Data Saver, Recent/Resume, command palette, Focus Mode, Split Workspace, prefetch and LKG/circuit/recovery behavior.
- Test 320–1920 px viewports, RTL/LTR, long Urdu/English labels, keyboard/focus, screen reader, reduced motion, 200%/400% zoom and representative Chrome/Firefox/Safari/Edge/device combinations.
- Verify no horizontal page overflow, hidden action, duplicate shell, duplicate bell, duplicate create authority or duplicate domain backend.

## Acceptance record

Record tester, date/time, environment, exact package/head/checksum, actual companion versions/contracts, screenshots/logs, File01 dry-run plan hash/action/blocker counts, reconciliation receipts/state, defects/retests, backup restore proof, rollback evidence and Founder decision.

**Pass condition:** zero known unresolved release-blocking defects, including zero File01 reconciliation blockers before apply, or an explicitly permitted documented Founder risk acceptance. Staging acceptance still does not equal Live-Deployed or Operational status.
