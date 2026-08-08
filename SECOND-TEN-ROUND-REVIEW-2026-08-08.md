# File 20 — Second Independent Ten-Round Review and Corrective Register

**Date:** 2026-08-08  
**Repository:** `majidhussainqadri1-dot/20-sabri-unified-application-shell`  
**Reviewed baseline:** merged runtime `1.4.1` at `f7a68c46527f714414b6a1ed8995d9fb857ad8d3`  
**Corrected candidate:** `1.4.2`  
**Scope:** a fresh adversarial pass over File 20 / Future Shell v5 against the governing central plan, File 20 plan, and direct File 00/01/02/19/21/24/25/26 ownership and privacy contracts. The approved Future Shell scope remains exactly eighteen enhancements.

## Review law

This is not a recount of the first ten-round register. Each round uses a new lens over the already-corrected `1.4.1` main branch. Any discovered defect is corrected in the same review cycle and then represented by a permanent regression/static/package gate. Code/package/automated-QA status remains distinct from Hostinger staging, live deployment and operational acceptance.

## Second ten rounds

| Round | Fresh review lens | Defect? | Finding and correction |
|---|---|---:|---|
| 1 | WordPress subdirectory privacy parity | **YES** | Protected prefixes were expressed as root-relative `/messages`, `/account`, `/wp-json`, etc., while actual request/SW paths can be `/subdir/messages` on a subdirectory install. Added final home-scope conversion for server classification, browser context and service-worker exclusions. |
| 2 | PWA disable lifecycle, release identity and File 25 visual ownership | **YES** | A disabled PWA virtual route could fall through to ordinary HTML instead of giving an installed worker a non-OK control response; manifest/head colors and SW cache identity were hard-coded. Added explicit `410` retirement response, File 25 token consumption, and cache identity derived from `SABRI_SHELL_VERSION`. |
| 3 | Partial Feature/Release-Ring settings writes | **YES** | The first-pass sanitizer began from defaults, so a partial programmatic settings write could reset omitted feature rules to `general/100`. Added a pre-sanitizer merge preserving omitted existing values; malformed explicit feature rules fail closed to Disabled/0. |
| 4 | Circuit-breaker state growth and truthful health | **YES** | Arbitrary module failure keys could grow circuit metadata without a total bound, and old opened states could remain visible in System Check until individually queried. Added stale-state cleanup, a 64-entry cap and corrected active-only health reporting. |
| 5 | Editable-context global keyboard shortcut | **YES** | `?` and `Alt+H` respected editable regions, but `Ctrl/Cmd+K` could still hijack an editor/input. Added a dependency-ordered document guard that lets target/editor handlers run and then blocks only the later File 20 global listener in editable contexts. |
| 6 | File 25 visual-system boundary in Future Shell CSS | **YES** | Future Shell controls still hard-coded radius/shadow/border styling and global accessibility spacing/motion selectors could alter native-domain components. CSS now consumes File 25-provided shell tokens and scopes File 20 spacing/motion effects to File-20-owned controls. |
| 7 | Dynamic private-path contract lifecycle | **YES** | Provider-supplied private prefixes were automatically persisted into File 20 options, causing silent configuration mutation and stale provider paths after provider changes. The persistence hook is removed; dynamic paths are evaluated on each request and included directly in the final SW/client policy. |
| 8 | Immersive-mode ownership and Split Workspace | **YES** | Split Workspace was desktop-gated but could still be offered inside Immersive Video/Reels/PDF contexts. A final availability guard now rejects Split Workspace in both Minimal and Immersive modes while preserving native owner hooks elsewhere. |
| 9 | Cross-file canonical ownership / duplicate-backend re-audit | **NO** | Re-read File 00 identity fail-closed assertions, File 02 authentication boundary, File 19 one-bell, File 21 Home/News ownership, File 24 assurance boundary, File 25 visual ownership and File 26 search contract. No new native-domain backend, duplicate bell/feed/create/search authority, or permissive identity fallback was found in the reviewed File 20 path. Existing tests remain applicable. |
| 10 | Exact release evidence, deterministic package and QA closure | **YES** | Once rounds 1–8 changed runtime behavior, `1.4.1` release metadata/tests/package identity were necessarily stale. Bumped to `1.4.2`, aligned plugin/readmes/workflow/tests, added second-pass source/package gates and this immutable review register. |

## Defect-round result

**Defects found:** rounds **1, 2, 3, 4, 5, 6, 7, 8 and 10**.  
**No new defect found:** round **9**.

This count applies only to this **second** ten-round sweep over the already-merged `1.4.1` baseline. It does not rewrite the first register, in which defects were found in all ten earlier rounds.

## Canonical ownership preserved

- File 00 remains membership/identity/authorization/guardian authority.
- File 02 remains credential authentication/login/recovery owner.
- File 19 remains notification entity/delivery and single-bell owner.
- File 21 remains Home/News/feed owner.
- File 24 remains security/privacy/compliance/resilience assurance owner; native enforcement remains distributed.
- File 25 remains visual-system/token/component-state owner.
- File 26 remains Search/Discovery/Ranking owner.
- File 20 changes in this pass are shell privacy, PWA lifecycle, feature-control integrity, bounded resilience metadata, accessibility orchestration and layout-boundary corrections only.

## Release truth

The corrected `1.4.2` head must pass both repository workflows before merge. The corrective workflow must run all existing PHP suites under PHP 7.4 and PHP 8.3, syntax-check all JavaScript, validate CSS/JSON/static boundaries, build the deterministic installable ZIP with source manifest and SHA-256, clean-extract it and verify the second-hardening files are included.

**Not claimed here:** Hostinger staging acceptance, real root/subdirectory WordPress PWA acceptance, real browsers/devices, companion-provider staging, backup/restore rehearsal, Founder acceptance, live deployment or operational monitoring. Those remain separate Definition-of-Done gates.
