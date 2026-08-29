from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]
P = ROOT / 'sabri-unified-application-shell'

def read(rel):
    return (ROOT / rel).read_text(encoding='utf-8')

def write(rel, text):
    (ROOT / rel).write_text(text, encoding='utf-8')

def replace_once(text, old, new, label):
    count = text.count(old)
    if count != 1:
        raise SystemExit(f'{label}: expected exactly one match, got {count}')
    return text.replace(old, new, 1)

# 1. Canonical programmatic writer belongs to Settings, not individual callers.
rel = 'sabri-unified-application-shell/includes/class-settings.php'
s = read(rel)
old = """\t\t$merged                   = self::deep_merge( Defaults::settings(), $current );
\t\t$merged['schema_version'] = Defaults::SCHEMA_VERSION;
\t\t$merged = self::enforce_owned_invariants( $merged );
\t\tupdate_option( Defaults::OPTION_NAME, $merged, false );
\t}

\t/**
\t * Sanitize submitted settings. Only the submitted tab is normalized.
"""
new = """\t\t$merged                   = self::deep_merge( Defaults::settings(), $current );
\t\t$merged['schema_version'] = Defaults::SCHEMA_VERSION;
\t\tself::update_programmatically( $merged );
\t}

\t/**
\t * Canonical persistence path for trusted File 20 programmatic settings writes.
\t *
\t * The registered Settings API sanitizer is intentionally tab-oriented for
\t * options.php submissions. Trusted internal workflows do not carry an
\t * _active_tab marker, so calling update_option() directly while that sanitizer
\t * is registered can normalize the proposed value back to the old settings.
\t *
\t * This method suspends only this class' Settings API sanitizer for the exact
\t * bounded write, explicitly applies File 20 ownership invariants, leaves every
\t * other WordPress/core/security/concurrency pre-update filter active, and
\t * restores the sanitizer in finally. Nested calls are safe: only the call that
\t * actually removed the filter restores it.
\t *
\t * @param array<string,mixed> $settings Trusted File 20 settings state.
\t * @return bool WordPress update_option() result.
\t */
\tpublic static function update_programmatically( array $settings ) {
\t\t$settings = self::enforce_owned_invariants( $settings );
\t\t$hook     = 'sanitize_option_' . Defaults::OPTION_NAME;
\t\t$callback = array( __CLASS__, 'sanitize' );
\t\t$removed  = false;

\t\tif ( function_exists( 'remove_filter' ) ) {
\t\t\t$removed = remove_filter( $hook, $callback, 10 );
\t\t}

\t\ttry {
\t\t\treturn update_option( Defaults::OPTION_NAME, $settings, false );
\t\t} finally {
\t\t\tif ( $removed && function_exists( 'add_filter' ) ) {
\t\t\t\tadd_filter( $hook, $callback, 10, 1 );
\t\t\t}
\t\t}
\t}

\t/**
\t * Sanitize submitted settings. Only the submitted tab is normalized.
"""
s = replace_once(s, old, new, 'Settings canonical writer insertion')
write(rel, s)

# 2. File01 adapter delegates to the canonical writer; remove its private bypass.
rel = 'sabri-unified-application-shell/includes/class-file01-reconciliation-adapter.php'
s = read(rel)
s = s.replace('self::persist_settings_option( $raw );', 'Settings::update_programmatically( $raw );')
if 'self::persist_settings_option' in s:
    raise SystemExit('Adapter still calls private persistence helper')
pat = re.compile(r"\n\t/\*\*\n\t \* Persist a trusted File20-owned settings mutation without the tab-oriented.*?\n\tprivate static function persist_settings_option\( array \$value \) \{.*?\n\t\}\n", re.S)
s, n = pat.subn('\n', s, count=1)
if n != 1:
    raise SystemExit(f'Adapter private persistence helper removal: expected 1, got {n}')
write(rel, s)

# 3. SafeMode canonical lifecycle keeps its emergency guard but uses central writer.
rel = 'sabri-unified-application-shell/includes/class-safe-mode.php'
s = read(rel)
s = replace_once(s,
    "\t\ttry { update_option( Defaults::OPTION_NAME, $settings, false ); }",
    "\t\ttry { Settings::update_programmatically( $settings ); }",
    'SafeMode settings write')
write(rel, s)

# 4. PlanV4Recovery settings repair and rollback use the central writer.
rel = 'sabri-unified-application-shell/includes/class-plan-v4-recovery.php'
s = read(rel)
s = replace_once(s,
    """        update_option( $option, $value, false );
        $stored = get_option( $option, new \\stdClass() );
""",
    """        if ( Defaults::OPTION_NAME === $option ) {
            Settings::update_programmatically( $value );
        } else {
            update_option( $option, $value, false );
        }
        $stored = get_option( $option, new \\stdClass() );
""",
    'Recovery restore settings write')
s = replace_once(s,
    "        update_option( Defaults::OPTION_NAME, $after, false );",
    "        Settings::update_programmatically( $after );",
    'Recovery stale binding write')
write(rel, s)

# 5. Activation snapshot rollback uses the central writer.
rel = 'sabri-unified-application-shell/includes/class-snapshot.php'
s = read(rel)
s = replace_once(s,
    "\t\t\t\tupdate_option( Defaults::OPTION_NAME, $restored_settings, false );",
    "\t\t\t\tSettings::update_programmatically( $restored_settings );",
    'Snapshot settings rollback write')
write(rel, s)

# 6. Retired Home-feed migration also goes through the same canonical path.
rel = 'sabri-unified-application-shell/includes/class-future-shell-v5-tenth-hardening.php'
s = read(rel)
s = replace_once(s,
    "        update_option( Defaults::OPTION_NAME, $target, false );",
    "        Settings::update_programmatically( $target );",
    'Tenth hardening migration write')
write(rel, s)

# 7. Version identity 1.4.17.
rel = 'sabri-unified-application-shell/sabri-unified-application-shell.php'
s = read(rel).replace('* Version: 1.4.16', '* Version: 1.4.17', 1).replace("define( 'SABRI_SHELL_VERSION', '1.4.16' );", "define( 'SABRI_SHELL_VERSION', '1.4.17' );", 1)
write(rel, s)

# 8. Dedicated regression exercising the real Settings class under an active registered sanitizer.
regression = r'''<?php
/** Regression for the canonical trusted File20 programmatic settings writer. */
declare(strict_types=1);

namespace {
    define( 'ABSPATH', __DIR__ . '/' );
    $GLOBALS['test_options'] = array();
    $GLOBALS['test_filters'] = array();

    function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) ); }
    function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
    function sanitize_title( $value ) { return sanitize_key( str_replace( ' ', '-', (string) $value ) ); }
    function sanitize_textarea_field( $value ) { return trim( strip_tags( (string) $value ) ); }
    function absint( $value ) { return abs( (int) $value ); }
    function esc_url_raw( $url, $protocols = null ) { unset( $protocols ); return (string) $url; }
    function wp_http_validate_url( $url ) { return '' !== (string) $url; }

    function callback_id( $callback ) {
        if ( is_array( $callback ) ) { return ( is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0] ) . '::' . $callback[1]; }
        return is_string( $callback ) ? $callback : 'closure-' . spl_object_hash( $callback );
    }
    function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
        $GLOBALS['test_filters'][ $tag ][ $priority ][ callback_id( $callback ) ] = array( 'function' => $callback, 'accepted_args' => $accepted_args );
        return true;
    }
    function remove_filter( $tag, $callback, $priority = 10 ) {
        $id = callback_id( $callback );
        if ( ! isset( $GLOBALS['test_filters'][ $tag ][ $priority ][ $id ] ) ) { return false; }
        unset( $GLOBALS['test_filters'][ $tag ][ $priority ][ $id ] );
        return true;
    }
    function apply_test_filters( $tag, $value, ...$args ) {
        if ( empty( $GLOBALS['test_filters'][ $tag ] ) ) { return $value; }
        $priorities = array_keys( $GLOBALS['test_filters'][ $tag ] );
        sort( $priorities, SORT_NUMERIC );
        foreach ( $priorities as $priority ) {
            foreach ( $GLOBALS['test_filters'][ $tag ][ $priority ] as $entry ) {
                $all = array_merge( array( $value ), $args );
                $value = call_user_func_array( $entry['function'], array_slice( $all, 0, $entry['accepted_args'] ) );
            }
        }
        return $value;
    }
    function register_setting( $group, $option, $args ) {
        unset( $group );
        if ( ! empty( $args['sanitize_callback'] ) ) { add_filter( 'sanitize_option_' . $option, $args['sanitize_callback'], 10, 1 ); }
    }
    function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['test_options'] ) ? $GLOBALS['test_options'][ $key ] : $default; }
    function update_option( $key, $value, $autoload = null ) {
        unset( $autoload );
        $old = get_option( $key, false );
        $value = apply_test_filters( 'sanitize_option_' . $key, $value, $key, $old );
        $value = apply_test_filters( 'pre_update_option_' . $key, $value, $old, $key );
        $GLOBALS['test_options'][ $key ] = $value;
        return true;
    }
}

namespace Sabri\UnifiedShell {
    final class Defaults {
        const OPTION_NAME = 'sabri_shell_settings';
        const SCHEMA_VERSION = 5;
        public static function settings() {
            return array(
                'schema_version' => 5,
                'enabled' => true,
                'emergency_disabled' => false,
                'delete_on_uninstall' => false,
                'home_feed' => array( 'retired' => true, 'auto_insert' => false, 'posts_count' => 0 ),
                'header' => array(), 'mobile' => array( 'bottom_nav' => false ), 'integrations' => array(),
                'right_sidebar' => array(),
                'navigation' => array( 'home' => array( 'shortcode' => 'sabri_complete_home_feed' ), 'founder' => array( 'page_id' => 0 ) ),
            );
        }
        public static function destinations() { return array(); }
        public static function groups() { return array(); }
    }
    final class Navigation { public static function invalidate_cache() {} }
    final class Integrations { public static function invalidate_cache() {} }
}

namespace {
    require dirname( __DIR__ ) . '/includes/class-settings.php';
    use Sabri\UnifiedShell\Defaults;
    use Sabri\UnifiedShell\Settings;

    $failures = array();
    $assert = static function ( $condition, $message ) use ( &$failures ) {
        echo ( $condition ? 'PASS: ' : 'FAIL: ' ) . $message . "\n";
        if ( ! $condition ) { $failures[] = $message; }
    };

    $GLOBALS['test_options'][ Defaults::OPTION_NAME ] = Defaults::settings();
    Settings::register();
    add_filter( 'pre_update_option_' . Defaults::OPTION_NAME, array( Settings::class, 'enforce_owned_invariants_filter' ), PHP_INT_MAX - 30, 3 );

    $probe = get_option( Defaults::OPTION_NAME );
    $probe['navigation']['founder']['page_id'] = 164;
    update_option( Defaults::OPTION_NAME, $probe, false );
    $assert( 0 === absint( get_option( Defaults::OPTION_NAME )['navigation']['founder']['page_id'] ?? 0 ), 'Control reproduces the registered tab-oriented sanitizer swallowing a raw programmatic write.' );

    $trusted = get_option( Defaults::OPTION_NAME );
    $trusted['navigation']['founder']['page_id'] = 164;
    $trusted['navigation']['home']['shortcode'] = 'sabri_shell_home_feed';
    Settings::update_programmatically( $trusted );
    $stored = get_option( Defaults::OPTION_NAME );

    $assert( 164 === absint( $stored['navigation']['founder']['page_id'] ?? 0 ), 'Canonical programmatic writer persists the trusted Page-ID mutation.' );
    $assert( 'sabri_complete_home_feed' === ( $stored['navigation']['home']['shortcode'] ?? '' ), 'Canonical programmatic writer enforces File20 ownership invariants before persistence.' );
    $assert( isset( $GLOBALS['test_filters']['sanitize_option_' . Defaults::OPTION_NAME][10]['Sabri\\UnifiedShell\\Settings::sanitize'] ), 'Settings API sanitizer is restored after the bounded trusted write.' );

    $again = $stored;
    $again['navigation']['founder']['page_id'] = 165;
    update_option( Defaults::OPTION_NAME, $again, false );
    $assert( 164 === absint( get_option( Defaults::OPTION_NAME )['navigation']['founder']['page_id'] ?? 0 ), 'Restored sanitizer still protects subsequent raw no-tab writes.' );

    if ( $failures ) { exit( 1 ); }
    echo "\nCanonical File20 programmatic settings writer regression PASS.\n";
}
'''
write('sabri-unified-application-shell/tests/run-programmatic-settings-writer-regression.php', regression)

# Existing File01 regression uses a Settings test double; teach it the canonical writer contract.
rel = 'sabri-unified-application-shell/tests/run-file01-reconciliation-sanitizer-regression.php'
s = read(rel)
needle = """\t\tpublic static function enforce_owned_invariants( array $value ) { return $value; }
\t}
"""
insert = """\t\tpublic static function enforce_owned_invariants( array $value ) { return $value; }
\t\tpublic static function update_programmatically( array $value ) {
\t\t\t$hook = 'sanitize_option_' . Defaults::OPTION_NAME;
\t\t\t$callback = array( __CLASS__, 'sanitize' );
\t\t\t$removed = remove_filter( $hook, $callback, 10 );
\t\t\ttry { return update_option( Defaults::OPTION_NAME, self::enforce_owned_invariants( $value ), false ); }
\t\t\tfinally { if ( $removed ) { add_filter( $hook, $callback, 10, 1 ); } }
\t\t}
\t}
"""
s = replace_once(s, needle, insert, 'File01 test Settings double')
write(rel, s)

# 9. Static guard: no trusted direct sabri_shell_settings writes may bypass Settings.
guard = r'''<?php
/** Static guard for File20's single canonical programmatic settings writer. */
declare(strict_types=1);
$root = dirname( __DIR__ );
$allowed = realpath( $root . '/includes/class-settings.php' );
$violations = array();
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file ) {
    if ( ! $file->isFile() || 'php' !== strtolower( $file->getExtension() ) ) { continue; }
    $path = $file->getRealPath();
    if ( $path === $allowed || false !== strpos( $path, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR ) ) { continue; }
    $code = (string) file_get_contents( $path );
    if ( preg_match( '/update_option\s*\(\s*Defaults::OPTION_NAME\b/', $code ) ) {
        $violations[] = $path;
    }
}
if ( $violations ) {
    fwrite( STDERR, "Direct File20 settings writes bypass the canonical writer:\n" . implode( "\n", $violations ) . "\n" );
    exit( 1 );
}
$settings = (string) file_get_contents( $allowed );
foreach ( array( 'public static function update_programmatically', "remove_filter( \$hook, \$callback, 10 )", 'self::enforce_owned_invariants( $settings )', "add_filter( \$hook, \$callback, 10, 1 )" ) as $needle ) {
    if ( false === strpos( $settings, $needle ) ) { fwrite( STDERR, "Canonical writer invariant missing: {$needle}\n" ); exit( 1 ); }
}
echo "File20 canonical programmatic settings writer static guard PASS\n";
'''
write('sabri-unified-application-shell/tests/run-programmatic-settings-writer-static.php', guard)

# 10. Release/operator truth.
rel = 'sabri-unified-application-shell/readme.txt'
s = read(rel)
s = s.replace('Stable tag: 1.4.16', 'Stable tag: 1.4.17', 1)
marker = '== Description ==\n\n'
section = "Version 1.4.17 centralizes the live-proven Settings API persistence correction into one canonical File20 programmatic writer. A fresh post-merge repository review found the same source-level risk in File20 repair, rollback, Emergency, activation-snapshot rollback and retired-state migration paths. Those internal workflows now use `Settings::update_programmatically()`; direct trusted `sabri_shell_settings` writes outside the Settings owner are rejected by a permanent static gate. This is repository/source-level remediation; it does not claim those additional paths were failing on Live.\n\n"
s = replace_once(s, marker, marker + section, 'readme description')
ch = '== Changelog ==\n\n'
entry = "= 1.4.17 =\n* Centralized trusted File20 programmatic `sabri_shell_settings` persistence in `Settings::update_programmatically()`.\n* Routed File01 reconciliation, hardened repair/rollback, Emergency lifecycle, activation-snapshot rollback, defaults normalization and retired Home-feed migration through the canonical writer.\n* Added dynamic active-sanitizer regression plus a static gate forbidding direct trusted settings writes outside the Settings owner.\n* Preserved the 1.4.16 live-proven bounded sanitizer strategy while removing adapter-local persistence duplication.\n* No Live deployment or operational-resolution claim is made.\n\n"
s = replace_once(s, ch, ch + entry, 'readme changelog')
write(rel, s)

rel = 'sabri-unified-application-shell/README.md'
s = read(rel).replace('- Version: `1.4.16`', '- Version: `1.4.17`', 1)
anchor = '## Version 1.4.16 — File 01-B Settings API sanitizer persistence repair\n'
newsec = "## Version 1.4.17 — canonical programmatic settings writer\n\nA fresh post-merge source review of 1.4.16 found that the same Settings API sanitizer class of defect remained reachable in other trusted File20 admin/runtime workflows: PlanV4 repair/rollback, Emergency state persistence, activation-snapshot rollback and retired Home-feed state migration. This is a repository/source-level defect finding; it is not a claim that each path was observed failing on Live.\n\nVersion 1.4.17 moves the bounded sanitizer suspension/restoration logic into the canonical Settings owner as `Settings::update_programmatically()`. Every identified trusted File20 settings mutation uses that one path; File20 invariants and all other security/concurrency/pre-update filters remain active, the sanitizer is restored in `finally`, and a static regression forbids new direct `sabri_shell_settings` writes outside `class-settings.php`.\n\n"
if anchor not in s: raise SystemExit('README 1.4.16 anchor missing')
s = s.replace(anchor, newsec + anchor, 1)
write(rel, s)

rel = 'sabri-unified-application-shell/CHANGELOG.md'
s = read(rel)
anchor = '# Changelog\n\n'
entry = "## 1.4.17 — 2026-08-29 — Canonical Programmatic Settings Writer\n\n- Fresh post-merge review of 1.4.16 found the live-proven tab-oriented Settings API sanitizer hazard was still duplicated as a repository/source-level risk across trusted File20 repair, rollback, Emergency, activation-snapshot rollback and retired-state migration writes.\n- Added `Settings::update_programmatically()` as the single canonical File20-owned persistence path for trusted full settings mutations.\n- The canonical writer explicitly enforces File20 ownership invariants, suspends only `Settings::sanitize` for the bounded write, preserves all other WordPress/core/security/concurrency/pre-update filters and restores the sanitizer in `finally`.\n- File01 reconciliation now delegates to the Settings owner instead of retaining an adapter-local bypass.\n- PlanV4Recovery, SafeMode, Snapshot, Settings defaults normalization and TenthHardening retired-state migration now use the same writer.\n- Added a dynamic regression that reproduces the raw sanitizer swallow and proves canonical persistence/invariants/restoration, plus a static guard rejecting direct File20 settings writes outside `class-settings.php`.\n- This is repository/source remediation; no additional Live-path failure or operational resolution is claimed without deployment/re-test evidence.\n\n"
s = replace_once(s, anchor, anchor + entry, 'CHANGELOG header')
write(rel, s)

rel = 'sabri-unified-application-shell/MIGRATION.md'
s = read(rel)
s = s.replace('Version **1.4.16** is repository/code/package/automated-QA truth only after its exact candidate CI passes.', 'Version **1.4.17** is repository/code/package/automated-QA truth only after its exact candidate CI passes.', 1)
anchor = '## Upgrade to 1.4.16\n'
entry = "## Upgrade to 1.4.17\n\n1. Treat 1.4.17 as a repository/source hardening over 1.4.16, not as new Live evidence. The additional trusted-write paths were discovered by post-merge code review; only the original File01 persistence defect had live proof.\n2. Use the exact deterministic `20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER.zip` candidate after exact-head QA.\n3. Verify all trusted `sabri_shell_settings` programmatic mutations flow through `Settings::update_programmatically()` and that the static direct-write gate is green.\n4. Preserve File20 invariants, Emergency authorization, recovery verification, concurrency/audit evidence and all canonical ownership boundaries.\n5. Repository/CI/package success remains separate from deployment, parity and Live operational acceptance.\n\n"
if anchor not in s: raise SystemExit('MIGRATION anchor missing')
s = s.replace(anchor, entry + anchor, 1)
write(rel, s)

rel = 'sabri-unified-application-shell/STAGING-ACCEPTANCE.md'
s = read(rel)
s = s.replace('Record File 20 `1.4.16`, exact GitHub head, deterministic `20-sabri-unified-application-shell-1.4.16-FILE01-SANITIZER-PERSISTENCE-REPAIR.zip` package', 'Record File 20 `1.4.17`, exact GitHub head, deterministic `20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER.zip` package', 1)
anchor = '## File01 Reconciliation Sanitizer Persistence Gate\n'
entry = "## Canonical Programmatic Settings Writer Gate\n\n- Confirm `Settings::update_programmatically()` is the sole trusted full `sabri_shell_settings` writer outside the tab-oriented Settings API submission path.\n- Confirm File01 reconciliation, PlanV4 repair/rollback, Emergency lifecycle, activation-snapshot rollback, defaults normalization and retired Home-feed migration all use the canonical writer.\n- Confirm the dynamic active-sanitizer regression and the static no-direct-write regression both pass.\n- Treat these additional paths as source-level hardening until independently exercised in the selected deployment environment; do not convert repository evidence into a Live claim.\n\n"
if anchor not in s: raise SystemExit('STAGING anchor missing')
s = s.replace(anchor, entry + anchor, 1)
write(rel, s)

# 11. QA workflow/package identity.
rel = '.github/workflows/corrective-quality.yml'
s = read(rel)
s = s.replace('name: File 20 Version 1.4.16 File01 Sanitizer Persistence Repair Quality', 'name: File 20 Version 1.4.17 Canonical Programmatic Settings Writer Quality', 1)
s = s.replace("assert '* Version: 1.4.16' in main", "assert '* Version: 1.4.17' in main", 1)
s = s.replace("assert \"define( 'SABRI_SHELL_VERSION', '1.4.16' );\" in main", "assert \"define( 'SABRI_SHELL_VERSION', '1.4.17' );\" in main", 1)
s = s.replace("assert 'Stable tag: 1.4.16' in readmetxt", "assert 'Stable tag: 1.4.17' in readmetxt", 1)
s = s.replace("assert '## Upgrade to 1.4.16' in migration", "assert '## Upgrade to 1.4.17' in migration", 1)
s = s.replace("assert '20-sabri-unified-application-shell-1.4.16-FILE01-SANITIZER-PERSISTENCE-REPAIR.zip' in migration", "assert '20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER.zip' in migration", 1)
s = s.replace("assert 'Record File 20 `1.4.16`' in staging", "assert 'Record File 20 `1.4.17`' in staging", 1)
s = s.replace("assert '## 1.4.16 ' in changelog and '## 1.4.15 ' in changelog and '## 1.4.14 ' in changelog", "assert '## 1.4.17 ' in changelog and '## 1.4.16 ' in changelog and '## 1.4.15 ' in changelog", 1)
# Replace adapter-local sanitizer static assertions with canonical-writer assertions.
s = s.replace("          assert 'persist_settings_option' in adapter\n          assert \"remove_filter( $hook, $callback, 10 )\" in adapter\n          assert \"Settings::enforce_owned_invariants( $value )\" in adapter\n          assert \"add_filter( $hook, $callback, 10, 1 )\" in adapter\n", "          settings=read('includes/class-settings.php')\n          assert 'Settings::update_programmatically' in adapter\n          assert 'public static function update_programmatically' in settings\n          assert \"remove_filter( $hook, $callback, 10 )\" in settings\n          assert \"self::enforce_owned_invariants( $settings )\" in settings\n          assert \"add_filter( $hook, $callback, 10, 1 )\" in settings\n")
s = s.replace("print('Static File 20 v1.4.16 File01 sanitizer persistence and preserved release-truth checks PASS')", "print('Static File 20 v1.4.17 canonical programmatic settings writer and preserved release-truth checks PASS')", 1)
s = s.replace("base='20-sabri-unified-application-shell-1.4.16-FILE01-SANITIZER-PERSISTENCE-REPAIR'", "base='20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER'")
s = s.replace("fixed=(2026,8,29,4,30,0)", "fixed=(2026,8,29,7,15,0)", 1)
s = s.replace("print('Deterministic File20 1.4.16 File01 sanitizer persistence package PASS')", "print('Deterministic File20 1.4.17 canonical programmatic settings writer package PASS')", 1)
s = s.replace('FILE-20-1.4.16-FILE01-SANITIZER-PERSISTENCE-TEST-REPORT.md', 'FILE-20-1.4.17-CANONICAL-SETTINGS-WRITER-TEST-REPORT.md')
s = s.replace('# File 20 Version 1.4.16 File01 Sanitizer Persistence Repair Evidence', '# File 20 Version 1.4.17 Canonical Programmatic Settings Writer Evidence', 1)
s = s.replace('- Live deployed 1.4.16: NO', '- Live deployed 1.4.17: NO', 1)
s = s.replace('file20-1.4.16-file01-sanitizer-persistence-', 'file20-1.4.17-canonical-programmatic-settings-writer-')
s = s.replace('release/20-sabri-unified-application-shell-1.4.16-FILE01-SANITIZER-PERSISTENCE-REPAIR.zip', 'release/20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER.zip')
s = s.replace('release/20-sabri-unified-application-shell-1.4.16-FILE01-SANITIZER-PERSISTENCE-REPAIR.sha256', 'release/20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER.sha256')
s = s.replace('release/20-sabri-unified-application-shell-1.4.16-FILE01-SANITIZER-PERSISTENCE-REPAIR-SOURCE-MANIFEST.sha256', 'release/20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER-SOURCE-MANIFEST.sha256')
write(rel, s)

# 12. Advance only current-release assertions in tests; retain historical changelog/evidence references.
for path in (P / 'tests').glob('run*.php'):
    txt = path.read_text(encoding='utf-8')
    txt = txt.replace("* Version: 1.4.16", "* Version: 1.4.17")
    txt = txt.replace("SABRI_SHELL_VERSION', '1.4.16", "SABRI_SHELL_VERSION', '1.4.17")
    txt = txt.replace("define( 'SABRI_SHELL_VERSION', '1.4.16' );", "define( 'SABRI_SHELL_VERSION', '1.4.17' );")
    txt = txt.replace('under 1.4.16', 'under 1.4.17')
    txt = txt.replace('release identity 1.4.16', 'release identity 1.4.17')
    txt = txt.replace('current release identity 1.4.16', 'current release identity 1.4.17')
    txt = txt.replace('release 1.4.16 preserves', 'release 1.4.17 preserves')
    txt = txt.replace('current release 1.4.16', 'current release 1.4.17')
    txt = txt.replace('File 20 1.4.16 release/operator documentation truth PASS', 'File 20 1.4.17 release/operator documentation truth PASS')
    if path.name == 'run-release-documentation-truth.php':
        txt = txt.replace("$current = '1.4.16';", "$current = '1.4.17';")
        txt = txt.replace("$artifact = '20-sabri-unified-application-shell-1.4.16-FILE01-SANITIZER-PERSISTENCE-REPAIR.zip';", "$artifact = '20-sabri-unified-application-shell-1.4.17-CANONICAL-PROGRAMMATIC-SETTINGS-WRITER.zip';")
        txt = txt.replace("$assert( false !== strpos( $changelog, '## 1.4.16 ' ), 'changelog current release' );", "$assert( false !== strpos( $changelog, '## 1.4.17 ' ), 'changelog current release' );\n$assert( false !== strpos( $changelog, '## 1.4.16 ' ), 'changelog prior sanitizer persistence release' );")
    path.write_text(txt, encoding='utf-8')

# New release note.
write('sabri-unified-application-shell/RELEASE-1.4.17-NOTE.md', '''# File 20 v1.4.17 — Canonical Programmatic Settings Writer\n\n## Post-merge repository finding\n\nAfter 1.4.16 merged green, a fresh exact-HEAD source review found that the live-proven Settings API sanitizer hazard had been corrected only inside the File01 adapter. Other trusted File20 full-settings mutation paths still called `update_option()` directly and could encounter the same tab-oriented sanitizer whenever it was registered during admin workflows. This is a source-level finding; it does not assert those additional paths were observed failing on Live.\n\n## Correction\n\n`Settings::update_programmatically()` is now the single canonical trusted writer. It enforces File20 invariants, temporarily removes only `Settings::sanitize`, preserves every other filter, restores the sanitizer in `finally`, and is used by File01 reconciliation, recovery repair/rollback, Emergency persistence, activation-snapshot rollback, defaults normalization and retired-state migration.\n\nA dynamic active-sanitizer regression and a static no-direct-write gate make this invariant permanent.\n\n## Evidence boundary\n\nRepository/CI/package completion remains separate from deployment and Live verification.\n''')

# 13. Remove this one-shot transformer and its workflow from the resulting commit.
for rel in ('.github/apply-file20-1.4.17.py', '.github/workflows/apply-file20-1.4.17.yml'):
    p = ROOT / rel
    if p.exists(): p.unlink()

print('File20 1.4.17 canonical settings writer transformation complete')
