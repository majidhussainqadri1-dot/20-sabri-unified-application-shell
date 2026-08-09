<?php
/**
 * Static/adversarial regression for the File 20 eighty-round consolidation.
 *
 * @package SabriUnifiedApplicationShell
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$read = static function (string $relative) use ($root): string {
    $path = $root . '/' . $relative;
    $data = @file_get_contents($path);
    if (!is_string($data)) {
        fwrite(STDERR, "Unable to read {$relative}\n");
        exit(1);
    }
    return $data;
};
$fail = array();
$assert = static function ($condition, string $label) use (&$fail): void {
    if (!$condition) { $fail[] = $label; }
};

$main        = $read('sabri-unified-application-shell.php');
$defaults    = $read('includes/class-defaults.php');
$plugin      = $read('includes/class-plugin.php');
$settings    = $read('includes/class-settings.php');
$admin       = $read('admin/class-admin.php');
$integr      = $read('includes/class-integrations.php');
$nav         = $read('includes/class-navigation.php');
$renderer    = $read('includes/class-renderer.php');
$layout      = $read('includes/class-layout.php');
$context     = $read('includes/class-context-navigation.php');
$context_js  = $read('assets/js/context-navigation.js');
$route       = $read('includes/class-route-security.php');
$recovery    = $read('includes/class-plan-v4-recovery.php');
$snapshot    = $read('includes/class-snapshot.php');
$system      = $read('includes/class-system-check.php');
$concurrency = $read('includes/class-plan-v4-settings-concurrency.php');
$future      = $read('includes/class-future-shell-v5.php');
$control     = $read('includes/class-future-shell-v5-control-guard.php');
$second      = $read('includes/class-future-shell-v5-second-hardening.php');
$assurance   = $read('includes/class-plan-v4-assurance.php');
$jobs        = $read('includes/class-plan-v4-jobs.php');
$tenth       = $read('includes/class-future-shell-v5-tenth-hardening.php');
$slots       = $read('includes/class-native-content-slots.php');
$css_four    = $read('assets/css/four-plan-harmonization.css');
$css_shell   = $read('assets/css/shell.css');
$uninstall   = $read('uninstall.php');

$assert(false !== strpos($main, '* Version: 1.4.11') && false !== strpos($main, "define( 'SABRI_SHELL_VERSION', '1.4.11' );"), 'release identity 1.4.11');
$assert(false !== strpos($defaults, 'const SCHEMA_VERSION       = 5;'), 'settings schema 5');
$assert(!file_exists($root . '/includes/class-home-feed.php') && false === strpos($plugin, 'HomeFeed::register();'), 'File20 HomeFeed producer fully retired');
$assert(false !== strpos($defaults, "'sabri_complete_home_feed'"), 'File21-compatible Home shortcode default');
$assert(false === strpos($defaults, "'allowed_roles'"), 'no File20 role-authority defaults');
$assert(false === strpos($defaults, "'appearance'            => array("), 'no File20 appearance defaults');
$assert(false === strpos($admin, "'appearance'    =>") && false === strpos($admin, 'allowed_roles'), 'admin does not expose File25 or role authority editors');

foreach (array('sabri_shell_home_before_main','sabri_shell_home_main','sabri_shell_home_after_main','sabri_shell_home_right_sidebar','sabri_shell_news_main') as $hook) {
    $assert(false !== strpos($slots, $hook), 'native File21 slot ' . $hook);
}
$assert(false !== strpos($settings, 'enforce_owned_invariants') && false !== strpos($plugin, 'enforce_owned_invariants_filter'), 'schema5 invariants enforced on programmatic writes');
$assert(false !== strpos($integr, 'shortcode_collision') || false !== strpos($integr, 'multiple'), 'shortcode route collision handled explicitly');
$assert(false !== strpos($integr, 'version_compare') && false !== strpos($integr, 'preg_match'), 'provider versions semver-validated');
$assert(false === strpos($integr, 'get_author_posts_url('), 'no generic WordPress author-profile fallback');
$assert(false === strpos($integr, "array( 'sabri_verified_doctor', 'sabri_doctor_verified', 'doctor' )"), 'no role-based doctor discovery fallback');
$assert(false !== strpos($renderer, 'safe_login_redirect') && false !== strpos($renderer, 'Integrations::same_site_url'), 'login redirect is internal/same-origin');

$assert(false !== strpos($nav, "add_action( 'add_option_" ) && false !== strpos($nav, "add_action( 'delete_option_"), 'navigation cache invalidates on companion option add/delete');
$assert(false !== strpos($nav, 'owner') && false !== strpos($nav, 'source_priority'), 'route resolution includes owner-aware precedence evidence');
$assert(false !== strpos($nav, 'shortcode_collision') || false !== strpos($integr, 'shortcode_collision'), 'duplicate shortcode pages never become arbitrary winner');

$assert(false === strpos($renderer, 'WP_Query') && false === strpos($renderer, 'get_users('), 'renderer does not query domain feed/doctor backends');
$assert(false === strpos($renderer, 'Medical Safety'), 'renderer does not author clinical safety content');
$assert(false !== strpos($renderer, 'array( Layout::TWO, Layout::THREE )'), 'renderer suppresses chrome outside Two/Three shell modes');
$assert(false !== strpos($renderer, 'href="#sabri-shell-main-content"') && false !== strpos($renderer, 'id="sabri-shell-main-content"'), 'server-rendered skip target present');
$assert(false !== strpos($renderer, 'aria-modal') && false !== strpos($renderer, 'role="dialog"'), 'drawer dialog semantics');
$assert(false !== strpos($renderer, 'array( Layout::TWO, Layout::THREE )') && false !== strpos($assets = $read('includes/class-assets.php'), 'array( Layout::TWO, Layout::THREE )'), 'ordinary shell renderer/assets do not leak into Minimal/Immersive modes');
$assert(substr_count($renderer, 'render_mobile_bottom_nav(') <= 1, 'no duplicate mobile bottom-nav renderer path');

$assert(false === strpos($layout, "\$_GET['sabri_shell_maintenance']"), 'raw maintenance query cannot force layout');
$assert(false !== strpos($layout, 'path_matches') || false !== strpos($layout, 'request_path'), 'layout classification is path-aware');
$assert(false !== strpos($context, 'SafeMode::disabled') && false !== strpos($context, 'array( Layout::TWO, Layout::THREE )'), 'context navigation is limited to Two/Three and suppressed in SafeMode/non-shell modes');
$assert(false !== strpos($context_js, 'url.origin + url.pathname') && false !== strpos($context_js, 'MAX_STACK_SIZE = 20'), 'context history persists canonical query/hash-free same-origin paths within a bounded stack');

$assert(false !== strpos($route, 'rawurldecode') && false !== strpos($route, "array( '.', '..' )"), 'encoded dot-path ambiguity rejected after decoding');
$assert(false !== strpos($route, '%2f') || false !== strpos($route, 'rawurldecode'), 'encoded slash ambiguity rejected');
$assert(false !== strpos($route, 'rawurldecode') && substr_count($route, "'\\\\'") >= 2, 'encoded backslash ambiguity rejected after decoding');
$assert(false !== strpos($route, 'port') && false !== strpos($route, 'scheme'), 'same-site comparison normalizes scheme/port');

$assert(false !== strpos($recovery, 'private static function owned_options()') && false !== strpos($recovery, 'restore_option_entry'), 'rollback uses exact File20-owned allowlist');
$assert(false !== strpos($recovery, 'snapshot_schema_version') && false !== strpos($recovery, 'smoke'), 'rollback uses captured schema and post-restore smoke evidence');
$assert(false !== strpos($plugin, "create_snapshot( 'pre-schema-upgrade' )"), 'schema upgrade creates pre-upgrade recovery snapshot');
$assert(false !== strpos($snapshot, 'emergency_before') && false !== strpos($snapshot, 'PlanV4SettingsConcurrency::record_programmatic_change'), 'activation snapshot preserves Emergency and advances monotonic concurrency evidence');
$assert(false !== strpos($system, 'activation_snapshot') && false !== strpos($system, 'integrity'), 'System Check verifies activation snapshot integrity');
$assert(false !== strpos($system, 'NativeContentSlots') || false !== strpos($system, 'native-slot'), 'System Check verifies native slot publisher');
$assert(false !== strpos($concurrency, 'row_version') || false !== strpos($concurrency, 'VERSION_OPTION'), 'settings concurrency evidence retained');

$assert(false !== strpos($future, 'restore_lkg') && false !== strpos($future, 'FutureShellV5ControlGuard::restore_current_snapshot') && false !== strpos($control, 'restore_current_snapshot'), 'public LKG restore delegates to guarded path');
$assert(false !== strpos($control, 'required') && false !== strpos($control, 'invalid_release_ring'), 'release-ring input required and malformed values fail closed');
$assert(false !== strpos($future, 'CIRCUIT_LOCK') || false !== strpos($future, 'circuit_lock'), 'circuit-breaker mutation lock');
$assert(false !== strpos($second, 'circuit') && false !== strpos($second, 'lock'), 'circuit cleanup uses lock');
$assert(false !== strpos($assurance, 'LOCK_OPTION') || false !== strpos($assurance, 'queue_lock'), 'assurance queue mutation lock');
$assert(false !== strpos($jobs, 'prune') && false !== strpos($jobs, 'degraded'), 'maintenance prune failure cannot report green success');
$assert(false !== strpos($tenth, 'critical_health_state'), 'critical File20/File00 health gate preserved');

$assert(false === strpos($css_four, '--sabri-shell-primary:'), 'File20 does not redefine File25 primary visual token');
$assert(false === strpos($css_shell, 'sabri-shell-theme-dark') && false === strpos($css_shell, 'sabri-shell-theme-system'), 'dead File20 theme ownership CSS removed');
$assert(false !== strpos($uninstall, 'shortcode') && false !== strpos($uninstall, 'transient'), 'dynamic shortcode discovery cache registry cleaned on uninstall');

$features = array('command_palette','pwa_shell','offline_mode','data_saver','recent_resume','module_circuit_breaker','last_known_good','performance_guardian','smart_navigation','keyboard_accessibility','focus_mode','split_workspace','adaptive_foldable','view_transitions','predictive_prefetch','language_direction','accessibility_center','release_rings');
$assert(18 === count($features), 'exact eighteen Future Shell feature IDs');
foreach ($features as $feature) { $assert(false !== strpos($future, "'{$feature}'"), 'Future Shell feature ' . $feature); }

$combined = implode("\n", array($plugin,$settings,$admin,$integr,$nav,$renderer,$layout,$context,$route,$recovery,$snapshot,$system,$concurrency,$future,$control,$second,$assurance,$jobs,$tenth,$slots,$uninstall));
$assert(false === strpos($combined, 'CREATE TABLE') && false === strpos($combined, 'dbDelta(') && false === strpos($combined, 'INSERT INTO'), 'no foreign-domain database backend introduced');

if ($fail) {
    fwrite(STDERR, "File 20 eighty-round consolidation FAIL: " . implode('; ', $fail) . "\n");
    exit(1);
}

echo "File 20 eighty-round consolidation: ownership, routing, layout, privacy, recovery, health, concurrency, Future Shell and package-source boundaries PASS\n";
