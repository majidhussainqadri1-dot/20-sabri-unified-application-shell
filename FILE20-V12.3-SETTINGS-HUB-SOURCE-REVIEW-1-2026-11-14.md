# File20 V12.3 — Settings Hub limited Source Review 1 (frozen before fixes)

**Scope:** only new Draft PR #47 SettingsHub shortcode, JS, CSS, test and register call. This is not full File20 2-round acceptance, not installed-state debugging and not a Live defect report.

| Finding | Priority | Root cause/source | Reproduction | Planned correction |
|---|---|---|---|---|
| F20-V12-R1-001 | P1 | `SettingsHub::safe_owner_url()` accepts a percent-encoded repeated slash `/%2f%2fevil.example/`; although origin remains same and no direct open redirect is proven, a native router could interpret double slashes differently | `tests/run-v12-3-settings-hub.php` adversarial URL table | Deny duplicated slash path after recursive decoding |
| F20-V12-R1-002 | P1 a11y | Hardcoded search `id` and group anchor IDs collide when `[sabri_shell_settings_hub]` is instantiated twice on one page (block+widget) | Render twice and count IDs; same label IDs presently repeated | Add per-render unique IDs, scoped label/help anchor references and regression |
| F20-V12-R1-003 | P2 / acceptance | Styles are requested during shortcode rendering and may be enqueued after the head; real builder/late-shortcode conditions are not demonstrated | Require WordPress Staging, page-builder and no-JS smoke | Register/enqueue early for confirmed current post shortcode when WordPress content is available; retain late fallback; staging remains blocked |

| F20-V12-R1-004 | P1 privacy | The logged-in fallback renders all 23 private/control keys even when a native owner provides no authorized projection | Render with authenticated principal but no owner-filter response | Treat unverified private keys as unlisted, not searchable or displayed; 23 stay in source catalogue |
| F20-V12-R1-005 | P1 owner authenticity | `native-domain` generic placeholder is accepted as a provider owner ID, contrary to canonical per-domain ownership | Claim `owner_file=native-domain`, `authorized=true` and a same-origin URL | Reject placeholder owner claims until a concrete approved domain version exists |

**Staging-only blocker:** even static same-origin authenticated links can be cached by an external reverse proxy; cache partition/no-store needs independent Live/Staging proof and is not automatically asserted by this shortcode.

All findings are **source-candidate review defects** in new unmerged code; 0 confirmed Live defects. Owner File20 remains presentation only; no new DB, fake save state or native Settings route takeover allowed.

**Next:** Fix 001 and 002 with negative/positive tests, test style-load fallback safely without assuming builder acceptance, run complete exact-head CI; then separate fresh Review 2.
