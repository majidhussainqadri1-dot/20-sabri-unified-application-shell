# Changelog

## 1.1.2 — Authorization, Privacy, and Bounded Discovery Correction

### Authorization integrity

- Made `sabri_shell_can_show_create` a deny-only narrowing hook; File 22 cannot turn a false File 00/File 20 decision into an allowance.
- Rendered desktop and mobile Create links only after the exact package-owned current-user contract returns true.
- Required subject-bound File 00 contract `1.1.2` or later and removed role/meta/filter fallbacks from privileged identity decisions.
- Preserved the explicit institutional Administrator exception only after current File 00 approval, eligibility, 2FA, and `manage_options`.

### Public data integrity

- Required current File 00 assertions before File 03 directory eligibility can expose a verified Doctor.
- Removed raw Membership profile and generic File 03 getter fallbacks from public doctor fields and contact data.
- Kept professional fields empty unless File 03 provides an explicit approved projection.

### Performance and QA

- Replaced two `posts_per_page => -1` compatibility scans with deterministic 100-Page batches and a fixed 50-batch ceiling.
- Added tests for filter non-elevation, server-rendered Create omission, mobile authorization, contract provenance, bounded scans, and public-projection privacy.

## 1.1.1 — File 22 Create Contract and Public Layout Correction

- Added the exact package-owned File 22 Create contract `1.0.1` and package-source ownership checks.
- Added bounded managed-single layout recovery, navigation wrapping, profile-status separation, and content containment.
- Preserved theme and companion DOM ownership and added deterministic package evidence.

## 1.1.0 — Corrective Release Candidate

### Integration contracts

- Replaced guessed module shortcodes with the actual File 02–19 contracts.
- Added authoritative companion page-map resolution before generic discovery.
- Added current and legacy Membership Core founder, trusted publisher, doctor, and verified-doctor recognition.
- Added public platform login, signup, password recovery, profile, completion, and moderated composer resolution.
- Removed the WordPress admin post editor as the Create fallback.
- Added existing profile/credential/approved-clinic adapters without creating a duplicate database.
- Added real language switcher output for supported multilingual plugins.

### Layout and accessibility

- Removed broad theme wrapper reparenting.
- Preserved theme element IDs and DOM hierarchy.
- Restricted content targeting to conservative content containers.
- Added fail-safe behavior when no safe target is found.
- Improved drawer background handling, focus management, and mobile behavior.
- Added the circular `S | H` identity and approved orange `#FF8A1F`.

### Visibility and duplication controls

- Enforced logged-in visibility for private Messages and Notifications actions.
- Integrated the File 19 bell and suppressed the duplicate floating bell.
- Suppressed fallback feed insertion when File 04, File 21, or platform Home feed output already exists.
- Prevented unresolved News from silently linking to Home.
- Corrected doctor directory filter parameter names.

### Data, cache, and recovery

- Corrected recursive list-array merging so removed roles/items do not survive settings updates.
- Added locale- and epoch-aware navigation caching.
- Expanded invalidation for companion page maps, pages, post types, plugins, themes, permalinks, front-page configuration, and language changes.
- Restored captured front-page settings during rollback and scheduled rewrite flushing.
- Made System Check distinguish static declarations from unverified runtime/staging behavior.

### Verification

- Added PHP behavioral regression tests.
- Added CI checks for PHP syntax, JavaScript syntax, regression behavior, CSS balance, corrected integration contracts, forbidden core composer fallback, forbidden DOM reparenting, version consistency, and release artifact integrity.

## 1.0.0 — Original Baseline

- Initial independent Sabri Unified Application Shell package.