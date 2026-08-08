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
$seventh = file_get_contents($root . '/includes/class-future-shell-v5-seventh-hardening.php');
$eighth = file_get_contents($root . '/includes/class-future-shell-v5-eighth-hardening.php');
$ninth = file_get_contents($root . '/includes/class-future-shell-v5-ninth-hardening.php');
$central = file_get_contents($root . '/includes/class-central-plan-contract.php');
$defaults = file_get_contents($root . '/includes/class-defaults.php');
$settings = file_get_contents($root . '/includes/class-settings.php');
$css = file_get_contents($root . '/assets/css/four-plan-harmonization.css');
$basecss = file_get_contents($root . '/assets/css/shell.css');
$assert(strpos($main, 'Version: 1.4.9') !== false && strpos($main, "SABRI_SHELL_VERSION', '1.4.9") !== false, 'release identity 1.4.9');
$assert(strpos($main, 'FourPlanHarmonization::register') !== false, 'harmonization registered');
$assert(strpos($main, 'FutureShellV5SeventhHardening::register') !== false, 'seventh conditional harmonization registered');
$assert(strpos($main, 'FutureShellV5EighthHardening::register') !== false && strpos($eighth, "CONTRACT_VERSION = '1.0.8'") !== false, 'eighth corrective harmonization preserved');
$assert(strpos($main, 'FutureShellV5NinthHardening::register') !== false && strpos($ninth, "CONTRACT_VERSION = '1.0.9'") !== false, 'ninth corrective harmonization registered');
$assert(strpos($main, 'PublishingDashboardEntry::register') !== false, 'File23 entry registered');
$assert(strpos($renderer, 'FourPlanHarmonization::render_search();') !== false, 'search delegated to File26 adapter');
$assert(substr_count($renderer, 'self::render_mobile_bottom_nav(') === 0, 'duplicate mobile bottom nav not invoked');
$assert(strpos($renderer, 'sabri-shell-nav-more') !== false, 'single-row More overflow rendered');
$assert(strpos($harm, 'WELCOME_INTERVAL_DAYS  = 30') !== false, '30-day Welcome rule');
$assert(strpos($harm, 'single-free-tier') !== false && strpos($harm, "donor_advantage']     = false") !== false, 'single free tier and donor neutrality');
$assert(strpos($harm, 'sabri_shell_file26_search_contract') !== false && strpos($harm, 'same_origin_url') !== false, 'File26 owner/version/same-origin search contract');
$assert(strpos($central, "'26' => array( 'Search Discovery and Ranking'") !== false, 'canonical registry includes File26');
foreach (array('CF-01','CF-02','CF-03','CF-04','CF-05','CF-06') as $cf) {
    $assert(strpos($seventh, "\$registry['{$cf}']") !== false, 'conditional registry includes ' . $cf);
}
$assert(strpos($seventh, 'single-free-tier-voluntary-donation-no-donor-advantage') !== false, 'conditional finance preserves current free/donation law');
$assert(strpos($seventh, 'file-17-cf-04-after-activation') !== false && strpos($seventh, '1073741824') !== false, 'conditional media transfer boundary and 1GB limit');
$assert(strpos($seventh, 'file-20-shell-surface-cf-06-locale-provider-after-activation') !== false, 'conditional localization provider boundary');
$assert(strpos($defaults, 'const SCHEMA_VERSION       = 4;') !== false, 'settings schema migrated to 4');
$assert(strpos($defaults, "'appearance'            => array(") === false, 'fresh File20 defaults create no File25 visual state');
$assert(strpos($defaults, "'bottom_nav'               => false") !== false, 'bottom nav default disabled');
$assert(strpos($settings, "output['bottom_nav']        = false") !== false, 'bottom nav cannot be re-enabled');
$assert(strpos($css, '.sabri-shell-bottom-nav') !== false && strpos($css, 'display: none !important') !== false, 'CSS duplicate-nav guard');
$assert(strpos($basecss, '#ff8a1f') === false && strpos($central, '#ff8a1f') === false, 'legacy orange runtime fallback removed');
$assert(strpos($harm, "'smail'") !== false && strpos($harm, "'verified_file_transfer'") !== false && strpos($harm, "'download_manager'") !== false, 'UI-only ownership contracts declared');
$js = file_get_contents($root . '/assets/js/four-plan-harmonization.js');
$readme = file_get_contents($root . '/README.md');
$readmetxt = file_get_contents($root . '/readme.txt');
$review = file_get_contents($root . '/REVIEW-CORRECTIONS.md');
$assert(strpos($harm, 'WELCOME_SESSION_COOKIE') !== false && strpos($harm, 'prepare_welcome_invocation') !== false && strpos($harm, 'welcome_seen_this_session') !== false, 'Welcome once-per-session server gate');
$assert(strpos($js, 'sessionStorage') !== false && strpos($js, 'markSessionSeen') !== false, 'Welcome sessionStorage fallback');
$assert(strpos($js, 'rebalanceNavigation') !== false && strpos($js, 'data-sabri-nav-overflow-moved') !== false, 'adaptive More overflow rebalance');
$assert(strpos($renderer, 'get_search_query()') === false && strpos($renderer, 'name="s"') === false, 'dormant native WordPress search fallback removed');
$assert(strpos($renderer, 'array_slice( $visible, 0, 6 )') !== false && strpos($renderer, 'array_slice( $visible, 6 )') !== false, 'conservative direct navigation set');
$assert(!preg_match("/'bottom_nav'\s*=>\s*true/", $defaults), 'stale destination bottom-nav metadata removed');
$assert(strpos($readme, 'Version: `1.4.9`') !== false && strpos($readmetxt, 'Stable tag: 1.4.9') !== false, 'release documentation identity 1.4.9');
$assert(strpos($review, 'green continuity fallback') !== false && stripos($review, '#FF8A1F') === false, 'review register uses current visual-policy truth');
echo 'Four-plan plus conditional/ninth harmonization assertions: ' . count($checks) . " passed\n";
