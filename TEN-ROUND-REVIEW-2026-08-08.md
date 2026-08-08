# File 20 — Ten-Round Post-Implementation Review and Corrective Register

**Date:** 2026-08-08  
**Repository:** `majidhussainqadri1-dot/20-sabri-unified-application-shell`  
**Reviewed baseline:** merged runtime `1.4.0`  
**Corrected candidate:** `1.4.1`  
**Scope:** File 20 central-plan contracts plus the approved Future Shell v5 eighteen enhancements. No nineteenth feature and no duplicate native-domain backend were introduced.

## Governing review law

Each round was treated as an independent review lens. A discovered defect was corrected in the same corrective cycle, then the affected static/runtime contract and the wider regression suite were re-run. Code/package/automated-QA evidence remains distinct from Hostinger staging, live deployment and operational acceptance.

## Ten rounds

| Round | Independent review lens | Defect discovered | Correction applied | Verification gate |
|---|---|---|---|---|
| 1 | Feature/ring traceability and rendered output | Future Shell footer controls were emitted even when the corresponding feature ring was disabled; Accessibility UI could expose Data Saver/Language controls outside their own ring state. | Replaced unconditional footer rendering with ring-aware File-20 markup; every optional control is now emitted only when its current feature decision permits it. | Future Shell static tests require removal of legacy footer hook and conditional command/recent/a11y/language/data output. |
| 2 | PWA scope, lifecycle and private-route safety | Service worker used root-wide scope, a hard-coded private-route regex and insufficient disable/deactivation cleanup. | Added site/subdirectory scope, unified protected paths, no-store control responses, control-manifest heartbeat/self-unregister and File-20 rewrite cleanup on deactivation. | PWA scope, private exclusions, self-unregister, deactivation and deterministic package checks. |
| 3 | Browser-local privacy: Recent/Resume, Smart Pins and user-specific routes | Local history could retain full/query-bearing destinations; old local history survived; private/authenticated route coverage was incomplete; pins could retain unsuitable URLs. | Versioned/scrubbed local history; query/hash-free canonical URLs; server-classified public-route capture; expanded auth/account/notifications/newsroom/publishing/marketplace-private route policy; extension filter; public primary-nav-only pins. | Public-route client context + protected-prefix regression assertions; no private/query route persistence. |
| 4 | Last-Known-Good resilience semantics | Original update hook captured the newly written settings as LKG, which could preserve the bad change rather than the previous known state; automatic recovery lacked strict version/schema compatibility. | Capture previous settings state; verify plugin version and settings schema before automatic restore; rotate stale LKG after compatible migration. | LKG previous-state and version/schema gates in Future Shell regression suite. |
| 5 | Control-plane fail-closed behavior and recovery exception safety | Unknown release-ring input could be normalized by the base REST callback to `general`; legacy automatic restore could leave recovery state unsafe if an exception occurred during settings restoration. | Added pre-dispatch invalid-ring rejection (`400`), blocked the legacy automatic restore path, and added guarded recovery with hash/version/schema checks plus `try/catch/finally` and temporary LKG-hook suppression. | ControlGuard registration, invalid-ring code, final-priority block, `finally` and no-foreign-backend assertions. |
| 6 | Keyboard/dialog accessibility | Dialog focus restoration was incomplete; shortcut suppression did not fully cover editable regions; pressed/toggled controls did not consistently expose state. | Added previous-focus restoration, contenteditable-aware shortcut guards and `aria-pressed` synchronization for preference/pin controls. | JS syntax + dedicated Future Shell accessibility assertions. |
| 7 | Desktop Split Workspace and adaptive interaction | A desktop-named workspace could be exposed/opened on smaller viewports and had incomplete focus/Escape lifecycle. | Enforced desktop-width gate, mobile CSS suppression, focus transfer/restore and Escape close. | JS/CSS regressions require desktop media gate and close/focus behavior. |
| 8 | Data Saver, performance lifecycle and File 25 ownership | Broad Data Saver background removal could modify native-domain content; Future Shell accent/contrast rules could overreach File 25 visual authority; PerformanceObserver lifetime was unbounded after sampling. | Limited Data Saver to explicitly decorative backgrounds, removed File-20-owned Future Shell accent/global contrast override, used structural/current visual hooks, and disconnected observers after the bounded local snapshot. | Static File 25 ownership, no-global-Data-Saver selector, no telemetry upload and observer-disconnect gates. |
| 9 | Fresh post-fix execution-order and cross-layer review | Hardened server facts (`currentRoutePublic`, PWA scope/private paths) were originally appended after the Future Shell script, too late for synchronous browser-local privacy decisions. | Added a pre-boot client-context layer that merges hardened facts into the localized Future Shell object before JS executes; unified route policy is persisted for SW/client parity. | Pre-boot registration/merge and private-policy parity assertions. |
| 10 | Release evidence, test quality, exact-head packaging and regression closure | Release identity/documentation lagged the corrected patch; early new QA assertions contained interpolation/case mistakes and the static gate initially searched the wrong Recent key; the ten-round control-plane files also needed exact package inclusion. | Bumped corrected candidate to `1.4.1`; aligned plugin/readmes/changelog/tests/workflow/package names; fixed QA assertion literals/case; added ControlGuard/ClientContext/package gates and this review register. | PHP 7.4 + PHP 8.3 suites, JS/JSON/CSS/static checks, deterministic ZIP + manifest + SHA-256 + clean extraction on the final PR head. |

## Defect-round result

**Defects were found in rounds 1, 2, 3, 4, 5, 6, 7, 8, 9 and 10.** Every discovered defect listed above was corrected before the final exact-head release gate. A round is not counted as clean merely because an earlier CI was green; the purpose of each later round was to challenge a different boundary.

## Preserved canonical ownership

The corrections do not transfer native-domain authority into File 20. File 00/02 remain identity/authentication authorities; File 19 remains notification owner; File 21 remains Home/News owner; File 22 remains Create/composer authority; File 24 remains security/privacy assurance owner; File 25 remains visual-system owner; File 26 remains Search/Discovery/Ranking owner. Future Shell changes are shell, lifecycle, privacy and presentation-contract hardening only.

## Release truth

The final pull-request head must pass both repository workflows before merge:

- **File 20 Baseline Archive Integrity**
- **File 20 Version 1.4.1 Ten-Round Hardened Quality**

The second workflow runs all PHP regressions under PHP 7.4 and PHP 8.3, then static ownership/privacy/security checks and builds a deterministic installable `1.4.1` ZIP with source manifest and SHA-256 evidence.

**Not claimed by this register:** Hostinger staging acceptance, real browser/device/PWA acceptance, real companion-provider staging, Founder acceptance, live deployment or operational monitoring. Those remain later evidence gates under the File 20 Definition of Done.
