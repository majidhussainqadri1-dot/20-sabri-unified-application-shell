# Sabri Unified Application Shell

Sabri Unified Application Shell is the canonical responsive WordPress application shell for the **Sabri Social Homeopathy Platform**.

- Version: `1.4.2`
- File 22 Create contract: `1.0.1`
- Central-plan contract: `1.0.0`
- Four-plan harmonization contract: `1.0.0`
- Future Shell v5 contract: `1.0.0`; first corrective hardening: `1.0.1`; second corrective hardening: `1.0.2`
- Status: repository/code/package/automated-QA candidate; Hostinger staging acceptance required
- Plugin slug/text domain: `sabri-unified-application-shell`

## Canonical scope

File 20 owns the shared application frame and structural continuity. Native membership, publishing, profile, communication, notification, clinic, marketplace, clinical, Search/Discovery/Ranking and assurance backends remain with their canonical files.

## Version 1.4.0 — Future Shell v5 / 18 enhancements

All eighteen approved shell enhancements were implemented without creating duplicate domain backends:

1. Global Command Palette (`Ctrl/Cmd+K`).
2. Installable PWA shell with privacy-bounded service worker.
3. Offline and weak-network status/degraded behavior.
4. Local Data-Saver preference plus browser Save-Data support.
5. Local-only Recent & Resume center.
6. Per-module circuit breaker contract with cooldown.
7. Integrity-checked last-known-good File 20 settings recovery.
8. Browser-local performance guardian with no user/URL telemetry upload.
9. Smart navigation local pins/favorites without changing canonical ranking/order.
10. Keyboard accessibility layer and shortcut help.
11. Focus/Reading shell mode.
12. Native-owner secondary Split Workspace slot.
13. Foldable/tablet/ultra-wide/safe-area/VisualViewport adaptation.
14. Progressive same-origin browser View Transitions.
15. Bounded privacy-safe prefetch.
16. Language/direction quick control consuming the existing language provider.
17. Local accessibility preference center.
18. Five-state release rings: disabled, internal, staging, limited percentage, general.

## Version 1.4.1 — first ten-round corrective hardening

The first ten-round post-implementation audit corrected residual defects without changing the approved eighteen-feature scope. It hardened release rings, PWA/private-route lifecycle, local history/pins/prefetch, previous-state LKG recovery, dialog/keyboard accessibility, desktop Split Workspace, bounded performance sampling and File 25/Data Saver ownership.

## Version 1.4.2 — second independent ten-round corrective hardening

A fresh second review did not assume the first green CI was sufficient. New checks against the governing File 20/Future Shell plans and File 00/01/02/19/21/24/25/26 boundaries produced the following corrections:

- protected routes are converted to the actual WordPress home scope, so `/subdir/messages`, `/subdir/account`, `/subdir/wp-json`, etc. remain private in subdirectory installations as well as root installations;
- PWA virtual routes return `410` when PWA is disabled, allowing previously installed workers to self-retire rather than accepting a misleading 200 HTML fallback;
- manifest/theme colors are consumed from File 25's validated visual-token contract, while the service-worker cache identity follows `SABRI_SHELL_VERSION` rather than a hard-coded release string;
- partial Future Shell settings updates preserve omitted feature/recovery/privacy values, while explicitly malformed feature rules are converted to Disabled/0 before persistence;
- circuit-breaker metadata is bounded and expired states are removed before health is reported;
- an editable-context guard runs before the global Command Palette listener so `Ctrl/Cmd+K` does not hijack inputs, textareas, selects or contenteditable editors;
- dynamic protected-path provider output is consumed per request instead of being silently written into File 20 options;
- Split Workspace is rejected in both Minimal and Immersive layout modes;
- File 20 CSS consumes File 25 border/radius/shadow/focus tokens and applies spacing/reduced-motion visual changes only to File-20-owned controls, leaving native-domain component styling to File 25/native owners.

The PWA service worker does not cache authenticated/sensitive module routes, WordPress admin/login/REST, messages, network, appointments, security, verification, account, notification, publishing-dashboard, newsroom and other protected paths. Split Workspace renders only when a native owner explicitly provides a slot and the layout is non-Minimal/non-Immersive. Platform search continues to use File 26's validated contract.

## Existing guarantees preserved

The four exact layout modes, File 00/22 authorization, File 19 one-bell, File 25 visual ownership, File 26 fail-closed search, single top navigation, Back/Home controls, single free tier, donor-neutral policy, Welcome session/30-day law, Safe Mode, Repair, snapshots and rollback remain intact.

## Staging acceptance

Repository completion is not production acceptance. Hostinger staging must test root/subdirectory PWA registration/update/disable/self-removal, offline behavior, low-data mode, all dialogs/keyboard paths, local privacy exclusions, partial release-ring updates, circuit cleanup, split workspace integration, LKG recovery, responsive/foldable layouts, supported browsers, accessibility, cache, backup/restore and rollback before Founder-approved production promotion.
