<?php
$root = dirname(__DIR__);
$checks = array();
$assert = static function ($condition, $label) use (&$checks) {
    if (!$condition) { fwrite(STDERR, "FAIL: {$label}\n"); exit(1); }
    $checks[] = $label;
};
$main = file_get_contents($root . '/sabri-unified-application-shell.php');
$renderer = file_get_contents($root . '/includes/class-renderer.php');
$harm = file_get_contents($root . '/includes/class-four-plan-harmonization.php');
$central = file_get_contents($root . '/includes/class-central-plan-contract.php');
$defaults = file_get_contents($root . '/includes/class-defaults.php');
$settings = file_get_contents($root . '/includes/class-settings.php');
$css = file_get_contents($root . '/assets/css/four-plan-harmonization.css');
$basecss = file_get_contents($root . '/assets/css/shell.css');
$assert(strpos($main, 'Version: 1.3.0') !== false && strpos($main, "SABRI_SHELL_VERSION', '1.3.0") !== false, 'release identity 1.3.0');
$assert(strpos($main, 'FourPlanHarmonization::register') !== false, 'harmonization registered');
$assert(strpos($main, 'PublishingDashboardEntry::register') !== false, 'File23 entry registered');
$assert(strpos($renderer, 'FourPlanHarmonization::render_search();') !== false, 'search delegated to File26 adapter');
$assert(substr_count($renderer, 'self::render_mobile_bottom_nav(') === 0, 'duplicate mobile bottom nav not invoked');
$assert(strpos($renderer, 'sabri-shell-nav-more') !== false, 'single-row More overflow rendered');
$assert(strpos($harm, 'WELCOME_INTERVAL_DAYS  = 30') !== false, '30-day Welcome rule');
$assert(strpos($harm, 'single-free-tier') !== false && strpos($harm, "donor_advantage']     = false") !== false, 'single free tier and donor neutrality');
$assert(strpos($harm, 'sabri_shell_file26_search_contract') !== false && strpos($harm, 'same_origin_url') !== false, 'File26 owner/version/same-origin search contract');
$assert(strpos($central, "'26' => array( 'Search Discovery and Ranking'") !== false, 'canonical registry includes File26');
$assert(strpos($defaults, 'const SCHEMA_VERSION       = 3;') !== false, 'settings schema migrated');
$assert(strpos($defaults, "'bottom_nav'               => false") !== false, 'bottom nav default disabled');
$assert(strpos($settings, "output['bottom_nav']        = false") !== false, 'bottom nav cannot be re-enabled');
$assert(strpos($css, '.sabri-shell-bottom-nav') !== false && strpos($css, 'display: none !important') !== false, 'CSS duplicate-nav guard');
$assert(strpos($basecss, '#ff8a1f') === false && strpos($central, '#ff8a1f') === false, 'legacy orange runtime fallback removed');
$assert(strpos($harm, "'smail'") !== false && strpos($harm, "'verified_file_transfer'") !== false && strpos($harm, "'download_manager'") !== false, 'new UI-only ownership contracts declared');
echo 'Four-plan harmonization assertions: ' . count($checks) . " passed\n";
