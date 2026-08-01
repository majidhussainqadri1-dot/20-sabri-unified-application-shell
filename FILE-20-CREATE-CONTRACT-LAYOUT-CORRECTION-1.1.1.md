# File 20 — Create Contract and Public Layout Correction 1.1.1

## Confirmed staging defects

File 22 Composer Health reported:

- `file20_legacy_contract_missing`;
- `file20_visibility_contract_missing`.

Public acceptance screenshots also showed a visible page-level navigation scrollbar, a glued profile-card name/status string, and File 21 single-publication content extending beneath the fixed left sidebar.

## Correction

Version 1.1.1:

- exposes the exact File 22 Create producer contract version `1.0.1`;
- declares canonical owner `sabri-unified-application-shell` and package-owned global functions;
- leaves partial or foreign preclaims incomplete so File 22 fails closed;
- accepts no foreign subject ID and evaluates only the current logged-in principal;
- rechecks Safe Mode, shell settings, File 00 publishing authority, canonical Create URL, and File 22's adapter-aware filter;
- applies the resulting decision to the rendered shell through explicit body classes;
- separates the user-card name and `Signed in` status without rewriting the stored display name;
- wraps desktop primary navigation and removes the page-level horizontal scrollbar;
- adds bounded recovery of the safe content column when a File 21 managed single publication is rendered after the original shell target pass;
- uses a three-second MutationObserver ceiling and scheduled retries rather than permanent DOM observation;
- annotates an existing content-level container and never moves or replaces theme or companion nodes;
- contains publication body, action rows, comments, media, tables, and long words inside the available content column.

## Ownership boundaries

- File 20 owns global shell presentation, navigation, sidebars, and Create-control visibility.
- File 22 remains the final adapter/workflow authority.
- File 21 remains the canonical publication and public content owner.
- File 25 remains the public profile/timeline visual-composition owner.
- This correction creates no publishing, profile, messaging, clinic, or membership database.

## Required staging acceptance

1. Install the exact File 20 1.1.1 artifact on staging.
2. Purge all caches and sign in again.
3. Run File 22 Composer Health; both File 20 contract codes must disappear.
4. Verify `/create/` shows only current-user-authorized workflows.
5. Open the controlled File 22 test post and the long cholesterol article.
6. Verify content, actions, and comments no longer sit beneath either sidebar.
7. Verify the primary navigation wraps without a page-level horizontal scrollbar.
8. Verify the user-card name and `Signed in` status appear on separate lines; an unintended digit in the actual WordPress display name must be corrected in the user profile rather than stripped by File 20.
9. Test 320, 360, 390, 768, 1024, 1366, 1440, and 1920 pixel widths.
10. Verify keyboard focus, drawers, RTL, Safe Mode, rollback, and duplicate-plugin detection before live promotion.

Repository merge and automated QA do not constitute staging or production acceptance.
