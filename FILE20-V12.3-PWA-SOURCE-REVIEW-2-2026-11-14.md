# File20 V12.3 PWA source Review Round 2 — newly isolated cache lifecycle defect

**Date:** 14 November 2026. Scope: PR #50 exact source after first limited review; this is NOT global G9 acceptance and NOT an installed/Live incident.

**Finding F20-SW-R2-001 (P1, privacy/cache retention):** New static-only cache policy retains the old `CACHE='sabri-shell-v140-static'` name. The previous SW used the same cache name while caching any plugin-path fetched URL. On an upgrade, the activation cleanup loop excludes the cache with exactly the same name, so legacy entries (including unreviewed plugin response paths) remain stored rather than being purged. This is a **source-code lifecycle defect**, not proof of Live exposure.

**Root cause:** a cache-policy change without a cache namespace/version migration.

**Corrective action:** rotate to `sabri-shell-v123-static-public` so the existing activation cleanup deletes older `sabri-shell-*` caches; add emitted-source/source-contract assertion for new name and old-name absence, plus exact VM activation purge simulation.

**Acceptance limit:** even source-correct cache rotation does not establish that real devices have activated the new worker or that a stuck older worker was removed. Real PWA upgrade, shared-device signout, and cache state must be checked in staging/live before production acceptance.
