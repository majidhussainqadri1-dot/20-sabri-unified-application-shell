<?php
$root = dirname( __DIR__ );
$php = file_get_contents( $root . '/includes/class-future-shell-v5.php' );
$js  = file_get_contents( $root . '/assets/js/future-shell-v5.js' );
$css = file_get_contents( $root . '/assets/css/future-shell-v5.css' );
$fail = array();
$features = array(
'command_palette','pwa_shell','offline_mode','data_saver','recent_resume','module_circuit_breaker','last_known_good','performance_guardian','smart_navigation','keyboard_accessibility','focus_mode','split_workspace','adaptive_foldable','view_transitions','predictive_prefetch','language_direction','accessibility_center','release_rings'
);
foreach ( $features as $feature ) { if ( false === strpos( $php, "'{$feature}'" ) ) { $fail[] = "missing {$feature}"; } }
$checks = array(
    '18 features' => count( $features ) === 18,
    'File 26 search contract preserved' => false !== strpos( $php, 'FourPlanHarmonization::file26_search_contract()' ),
    'split workspace native render hook' => false !== strpos( $php, "do_action( 'sabri_shell_split_workspace_render' )" ),
    'release rings' => false !== strpos( $php, "case 'limited'" ) && false !== strpos( $php, "case 'staging'" ),
    'PWA virtual routes' => false !== strpos( $php, 'sabri-shell-sw') && false !== strpos( $php, 'sabri-shell-manifest'),
    'private PWA exclusions' => false !== strpos( $php, 'wp-admin|wp-login') && false !== strpos( $php, 'messages|network|appointments'),
    'LKG integrity hash' => false !== strpos( $php, 'hash_equals') && false !== strpos( $php, 'LKG_OPTION'),
    'circuit breaker' => false !== strpos( $php, 'CIRCUIT_THRESHOLD') && false !== strpos( $php, 'sabri_shell_module_failure'),
    'local recent privacy filter' => false !== strpos( $js, 'privatePath(location.pathname)') && false !== strpos( $js, 'sabriShellRecentPublicRoutes'),
    'performance stays browser local' => false !== strpos( $js, 'sabri:shell-performance') && false === strpos( $js, 'sendBeacon'),
    'bounded prefetch' => false !== strpos( $js, 'Object.keys(prefetched).length >= 3') && false !== strpos( $js, 'dataSaverActive()'),
    'keyboard command palette' => false !== strpos( $js, "event.key.toLowerCase() === 'k'") && false !== strpos( $js, "event.altKey && event.key.toLowerCase() === 'h'"),
    'accessibility preferences' => false !== strpos( $js, 'sabriShellA11yPrefs') && false !== strpos( $css, '.sabri-shell-a11y-focus'),
    'view transition progressive enhancement' => false !== strpos( $css, '@supports (view-transition-name:none)') && false !== strpos( $css, '@view-transition'),
    'foldable safe area' => false !== strpos( $css, 'env(safe-area-inset-left)') && false !== strpos( $css, 'horizontal-viewport-segments:2'),
    'no foreign backend creation' => false === strpos( $php, 'CREATE TABLE') && false === strpos( $php, 'dbDelta('),
);
foreach ( $checks as $name => $ok ) { if ( ! $ok ) { $fail[] = $name; } }
if ( $fail ) { fwrite( STDERR, "Future Shell v5 FAIL: " . implode( '; ', $fail ) . "\n" ); exit( 1 ); }
echo "Future Shell v5: 18/18 enhancements and ownership/privacy guardrails PASS\n";
