# Changelog

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
