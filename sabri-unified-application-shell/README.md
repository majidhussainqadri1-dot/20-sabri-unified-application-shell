# Sabri Unified Application Shell

Sabri Unified Application Shell is the canonical responsive WordPress application shell for the **Sabri Social Homeopathy Platform**.

- Version: `1.4.1`
- File 22 Create contract: `1.0.1`
- Central-plan contract: `1.0.0`
- Four-plan harmonization contract: `1.0.0`
- Future Shell v5 contract: `1.0.0`; corrective hardening contract: `1.0.1`
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

## Version 1.4.1 — ten-round corrective hardening

The ten-round post-implementation audit found and corrected residual defects without changing the approved eighteen-feature scope:

- release-ring decisions are narrowed fail-closed after filters; invalid persisted rings become Disabled rather than General;
- footer controls are rendered only for the features enabled for the current release ring;
- the service worker uses the site/subdirectory scope, the same protected-route policy as the shell, no-store control responses and an online self-unregister check if File 20 stops serving its manifest;
- deactivation clears File 20 PWA rewrite state;
- Recent/Resume is versioned, browser-local, query/hash-free and limited to server-classified public routes; legacy local history is removed;
- Smart Navigation pins are scrubbed to eligible public primary-nav destinations;
- last-known-good recovery snapshots the **previous** settings state and rejects cross-version/schema automatic recovery;
- dialog focus restoration, editable-region keyboard protection, `aria-pressed` preference/pin state and desktop-only Split Workspace focus/escape handling are added;
- PerformanceObserver instances disconnect after the bounded local sample;
- Data Saver no longer removes arbitrary native-content backgrounds; native owners can opt decorative backgrounds into reduction;
- File 25 visual ownership is preserved by removing the Future Shell-owned accent token and global contrast filter;
- deterministic packaging and regression gates now identify the corrected release as `1.4.1`.

The PWA service worker never caches authenticated/sensitive module routes, WordPress admin/login, REST, messages, network, appointments, security, verification or account paths. Split Workspace renders only when a native owner explicitly provides a slot. Platform search continues to use File 26's validated contract.

## Existing guarantees preserved

The four exact layout modes, File 00/22 authorization, File 19 one-bell, File 25 visual ownership, File 26 fail-closed search, single top navigation, Back/Home controls, single free tier, donor-neutral policy, Welcome session/30-day law, Safe Mode, Repair, snapshots and rollback remain intact.

## Staging acceptance

Repository completion is not production acceptance. Hostinger staging must test PWA registration/update/self-removal, offline behavior, low-data mode, all dialogs/keyboard paths, local privacy exclusions, split workspace integration, release-ring behavior, circuit recovery, LKG recovery, responsive/foldable layouts, supported browsers, accessibility, cache, backup/restore and rollback before Founder-approved production promotion.
