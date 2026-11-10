# Staging Acceptance

Hostinger-equivalent staging acceptance is the normal production-promotion gate. Automated QA is repository evidence only and does not satisfy this checklist. A proven production incident may use equivalent bounded Live evidence, but staging and Live remain separate realities.

## Exact candidate and backup

- Record File 20 `1.4.18` draft candidate, exact GitHub head, deterministic `20-sabri-unified-application-shell-1.4.18-V12-1-CONTEXTUAL-SHELL.zip` package, SHA-256 and source manifest.
- Confirm one canonical plugin ZIP root, no development tests, safe paths/no duplicates, embedded/external manifest equality and source/stage/extracted SHA-256 parity.
- Prove a restorable files/database backup; test fresh install and the real upgrade path from the actually deployed File20 version.
- Record actual WordPress/PHP/database/cache/theme and companion-module versions before acceptance.

## Canonical programmatic settings writer gate

- `Settings::update_programmatically()` must be the sole trusted full `sabri_shell_settings` writer outside the tab-oriented Settings API submission path.
- File01 reconciliation, PlanV4 repair/rollback, Emergency lifecycle, activation-snapshot rollback, defaults normalization and retired-state migration must use the canonical writer.
- Dynamic active-sanitizer regression and static no-direct-write regression must pass.
- Additional source-hardened paths remain environment-unproven until actually exercised; repository evidence must not be converted into a Live claim.

## File01 reconciliation gate

- Before any File01 reconciliation write, deploy the exact approved **1.4.18** candidate after Staging approval to the selected environment and prove File20 runtime/package parity.
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

## V12.1 File02 and File24 public safety routes in one existing More menu

- File02 owns the canonical `/account/safety/` rewrite, not necessarily a WordPress Page. The File20 Account Safety link is derived only from native `SAUTH_Canonical_Routes` and `SAUTH_Account_Safety`, the registered owner shortcode and render hook, the owner route filter and exact version-bound rewrite-ready options.
- File24 owns its public `[sabri_security_guidance]` shortcode. File20 requires the actual `SecurityGuidance::renderPublic` callback registered on that shortcode, plus a unique published WordPress Page found by the existing collision-aware scanner. No auto-creation or publication is performed.
- Both links appear as entries inside the existing More menu only when native evidence is available on the same request. They are never cached as owner-health truth; no second navigation bar.
- Reject other hosts, scheme downgrades, query/fragment/credential URLs, encoded or doubly encoded separators, dot traversal, route collisions and invalid/native-disabled owner hooks. Links disappear when owners are unavailable.
- File02 PR #27 and File24 PR #32 remain draft. Stage-test actual rewrite rules, published Page ID/shortcode, public/private content, no-store headers, mobile menu, keyboard, RTL/LTR, 404 and rollback. Green source tests do not mean a Live page was published.

## V12.1 native-owner private workspace entry acceptance

- File20 may mount only owner-verified generic hub links for Recent or Saved. Source filters are `sabri_shell_workspace_entries_contract` v1.0.0 and `sabri_shell_workspace_entry_owner_approved`; no owner approval means no link.
- Private hub contract must be bound to the authenticated current principal (`principal_id` is an integer), every item must use intent `entry-only`, and the URL must be same-origin/approved, query-free and fragment-free. Owner must recheck current ACL and actual object visibility on destination.
- File20 must discard every provider-supplied entry label and substitute fixed generic owner labels. No user-specific titles, chat excerpts, draft IDs, patient metadata, entity copies, generic global saved database, private localStorage, shared-session transient or cross-user cache may be introduced. Generic entry-links are NOT evidence that per-object private Recent or unified collections are fully working.
- Test logout/session changes, guest access, different principal, stale authorization, missing provider, duplicate route, external URL, page cache, repeated shortcode, mobile/RTL and real owner-private hub navigation before acceptance.

## V12.1 native-owner contextual Help acceptance

- File20 supplies only a semantic native HTML details/summary slot. File02/05/06/08/CF02 or other approved native owners provide public-safe explanation, example, glossary and valid support destination through `sabri_shell_context_help_contract` v1.0.0.
- The native owner must separately authorize each current route through `sabri_shell_context_help_owner_approved`. With missing owner/route/version/approval, the slot is absent, not misleading placeholder help.
- Reject external/downgraded/query/fragment/support URLs, unknown owner, wrong current route, sensitive/private content, excessive glossary and unbounded text. Test no HTML/script rendering, native keyboard access, no-JS usability, RTL/LTR, zoom and role boundaries.
- File20 does not create case tickets, publish native content, capture screen/form values or store any private help context. Exact owner/provider integration remains an independent staging gate.

## V12.1 genuine contextual Forward acceptance

- Forward is hidden by default; it may appear only when native Navigation API canGoForward is true AND the current/next native entries match a valid same-origin history path. No fake history.length, referrer, session shortcut or button that manufactures a new visit.
- Test genuine browser Back then Forward, unsupported browsers, same-origin versus external history, page restore (pageshow), touch/keyboard/focus, and Urdu RTL / English LTR arrows.
- File02 public /account/safety/ and File24 public security-guidance shortcode are separate native-owner destinations. Only publish File24 navigation after explicit approval, a genuine published Page and verified route ownership.
- File20 Recent is intentionally public-only. Private cross-domain Recent and Saved collections require native owner permission/revocation contracts before shell mounting; no File20 private-data store.

## Future Shell, responsive and accessibility gates

- Test all four layout modes and all five release rings.
- Test PWA install/update/retirement/privacy behavior, offline/weak-network state, Data Saver, Recent/Resume, command palette, Focus Mode, Split Workspace, prefetch and LKG/circuit/recovery behavior.
- Test 320–1920 px viewports, RTL/LTR, long Urdu/English labels, keyboard/focus, screen reader, reduced motion, 200%/400% zoom and representative Chrome/Firefox/Safari/Edge/device combinations.
- Verify no horizontal page overflow, hidden action, duplicate shell, duplicate bell, duplicate create authority or duplicate domain backend.

## Acceptance record

Record tester, date/time, environment, exact package/head/checksum, actual companion versions/contracts, screenshots/logs, File01 dry-run plan hash/action/blocker counts, reconciliation receipts/state, defects/retests, backup restore proof, rollback evidence and Founder decision.

**Pass condition:** zero known unresolved release-blocking defects, including zero File01 reconciliation blockers before apply, or an explicitly permitted documented Founder risk acceptance. Staging acceptance still does not equal Live-Deployed or Operational status.
