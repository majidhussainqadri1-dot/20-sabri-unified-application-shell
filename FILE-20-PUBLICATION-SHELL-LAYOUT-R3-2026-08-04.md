# File 20 Publication Shell Layout Correction R3

## Confirmed defect

File 20's target-recovery JavaScript activated only for `sabri-hnf-managed-single`. A legacy/general article with explicit Markdown was intentionally not File 21-owned, so the theme content column was not annotated and the fixed desktop left sidebar could cover the article.

## Correction

- Accept the neutral `sabri-hnf-content-integrity-single` signal in addition to the strict managed signal.
- Keep File 20 limited to layout annotation; no content, post, taxonomy or publication ownership is acquired.
- Suppress fixed sidebars while bounded target recovery is pending.
- If recovery fails, hide fixed sidebars and center the publication rather than allowing an overlay.
- Retain the prohibition on DOM movement, replacement or deletion.

## Review rounds

1. Full PHP and JavaScript syntax plus current Create/layout and Central Plan v4 regression suites.
2. Fresh adversarial checks for neutral-class support, pending/failure states, no DOM reparenting, desktop fail-safe CSS and asset cache invalidation.

## Lifecycle boundary

Repository coding and automated QA do not equal Hostinger staging or live acceptance.
