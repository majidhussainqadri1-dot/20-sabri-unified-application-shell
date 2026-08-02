# File 20 — Central Plan v4 Traceability and Corrective Record

## Governing baseline

- Platform constitution: `SSH-PMP-2026-v3.0`.
- File specification: `File 20 v4.0 — Final Central-Plan-Harmonized Specification`.
- Audited repository baseline: File 20 `1.1.2`, commit `a89859fccbd7fffd006cccddf1e71151be460fb3`.
- Corrective runtime target: `1.2.0`.
- Lifecycle status: code candidate only; Hostinger staging and production acceptance are not claimed.

## Traceability result

| Requirement | 1.1.2 baseline | 1.2.0 correction | Verification |
|---|---|---|---|
| One canonical global shell | Present | Preserved | existing regression suite |
| Four exact layout modes | Three/Two/Minimal only | Added Immersive and exact context constitution | `tests/run-central-plan-v4.php` |
| Profiles/timelines use Two-column | Query-driven Three-column path existed | Removed generic profile query promotion | static negative assertion |
| Publishing Dashboard uses Two-column private application | Could be misclassified as Minimal | Explicitly excluded from Minimal classifier | static negative assertion |
| Desktop primary navigation is one horizontal no-wrap line | Corrective CSS allowed wrapping | Added final no-wrap, max-content and bounded overflow rule | CSS assertions |
| File 25 owns visual tokens and component appearance | File 20 still owned appearance | Added File 25 consumer contract; retired File 20 Appearance editor | contract and asset assertions |
| File 00–25 ownership/dependency registry | Distributed and incomplete | Added canonical registry with failure behavior | registry assertions |
| File 22 Create contract | Exact package-owned `1.0.1` | Preserved unchanged | existing and new tests |
| Safe Mode and recovery | Present | Preserved; operational states published | existing suite and registry |
| Truthful lifecycle status | Repository-only QA | Explicit staging/live boundary retained | documentation review |

## Corrected defects

1. Missing Immersive layout mode and context detection.
2. Profile query parameter could promote an ordinary public profile to Three-column.
3. Desktop primary navigation could wrap despite the governing one-line rule.
4. File 20 retained appearance ownership that belongs to File 25.
5. No complete machine-readable File 00–25 integration registry existed.
6. No explicit operational-state vocabulary existed for dependent modules.
7. File 25 visual provider/fallback status was not observable.
8. The legacy Appearance tab permitted continued File 20 writes to File 25-owned concerns.

## Review round 1

Requirements, ownership, layouts, security boundaries, navigation, migration compatibility and documentation were reviewed. The first corrective implementation added the contract registry, four-mode layout resolver, File 25 boundary adapter, no-wrap navigation correction and regression suite.

Corrections made during this round:

- removed `publishing-dashboard` from Minimal routing after detecting conflict with File 23's private two-column workspace;
- scoped the legacy Appearance-tab suppression to the File 20 administration page;
- kept the existing File 22 Create contract independent from the new central-plan class so an optional harmonization failure cannot corrupt established authorization behavior.

## Review round 2 — fresh adversarial review

The corrected source was re-reviewed for fail-open behavior, ownership spoofing, CSS injection, route misclassification, class preclaims, privilege elevation, theme DOM takeover, stale appearance writes and rollback regression.

Controls retained or added:

- File 25 contract owner allowlist and semantic version floor;
- strict token sanitization and bounded numeric ranges;
- continuity fallback marked truthfully as fallback rather than File 25 success;
- no arbitrary subject ID in Create decisions;
- no DOM reparenting or replacement;
- no File 20 write path for new appearance values;
- exact package-file ownership before class registration;
- PHP 7.4-compatible syntax and deterministic CI gates.

## Remaining external acceptance gates

The following are deliberately not claimed by repository work:

- Hostinger staging fresh install and upgrade;
- real File 00, 21, 22, 24 and 25 contract integration;
- Chrome, Firefox, Safari and Edge acceptance;
- 320–1920 px responsive and RTL visual acceptance;
- screen-reader and keyboard acceptance;
- backup/restore and rollback rehearsal;
- production deployment and operational monitoring;
- Founder acceptance.


## Repository completion addendum — operational implementation
The plan-v4 completion batch adds runtime context evidence, provider provenance/health, the File 01-B search boundary, settings concurrency, private cache/index controls, File 24 assurance events, bounded maintenance, privacy-safe audit evidence, dry-run repair and compatible rollback. Separate completion and fresh adversarial suites are mandatory. Staging, live and operational gates remain unclaimed.
