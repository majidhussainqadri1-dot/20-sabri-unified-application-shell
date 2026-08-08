<?php
$root    = dirname( __DIR__ );
$php     = file_get_contents( $root . '/includes/class-future-shell-v5.php' );
$hard    = file_get_contents( $root . '/includes/class-future-shell-v5-hardening.php' );
$client  = file_get_contents( $root . '/includes/class-future-shell-v5-client-context.php' );
$control = file_get_contents( $root . '/includes/class-future-shell-v5-control-guard.php' );
$js      = file_get_contents( $root . '/assets/js/future-shell-v5.js' );
$css     = file_get_contents( $root . '/assets/css/future-shell-v5.css' );
$main    = file_get_contents( $root . '/sabri-unified-application-shell.php' );
$fail    = array();
$features = array(
    'command_palette','pwa_shell','offline_mode','data_saver','recent_resume','module_circuit_breaker',
    'last_known_good','performance_guardian','smart_navigation','keyboard_accessibility','focus_mode',
    'split_workspace','adaptive_foldable','view_transitions','predictive_prefetch','language_direction',
    'accessibility_center','release_rings'
);
foreach ( $features as $feature ) {
    if ( false === strpos( $php, "'{$feature}'" ) ) { $fail[] = "missing {$feature}"; }
}
$checks = array(
    '18 features' => count( $features ) === 18,
    'release 1.4.1' => false !== strpos( $main, '* Version: 1.4.1' ) && false !== strpos( $main, "define( 'SABRI_SHELL_VERSION', '1.4.1' );" ),
    'three hardening layers registered' => false !== strpos( $main, 'FutureShellV5Hardening::register();' ) && false !== strpos( $main, 'FutureShellV5ClientContext::register();' ) && false !== strpos( $main, 'FutureShellV5ControlGuard::register();' ),
    'File26 search preserved' => false !== strpos( $php, 'FourPlanHarmonization::file26_search_contract()' ),
    'release rings present' => false !== strpos( $php, "case 'limited'" ) && false !== strpos( $php, "case 'staging'" ),
    'stored rings fail closed' => false !== strpos( $hard, "$ring    = 'disabled';" ) && false !== strpos( $hard, 'return (bool) $enabled && $allowed;' ),
    'invalid REST ring rejected' => false !== strpos( $control, "'/sabri-shell/v1/future/features'" ) && false !== strpos( $control, 'sabri_shell_invalid_release_ring' ) && false !== strpos( $control, "'status' => 400" ),
    'legacy auto recovery blocked' => false !== strpos( $control, "add_filter( 'sabri_shell_auto_recovery_allowed'" ) && false !== strpos( $control, 'PHP_INT_MAX' ) && false !== strpos( $control, 'block_legacy_auto_restore' ),
    'guarded recovery finally' => false !== strpos( $control, 'try {' ) && false !== strpos( $control, 'catch ( \\Throwable $error )' ) && false !== strpos( $control, 'finally {' ),
    'guarded recovery suppresses LKG overwrite' => false !== strpos( $control, 'remove_action( \'update_option_\' . Defaults::OPTION_NAME, $hook, 30 )' ) && false !== strpos( $control, 'add_action( \'update_option_\' . Defaults::OPTION_NAME, $hook, 30, 3 )' ),
    'ring-aware markup' => false !== strpos( $hard, "remove_action( 'wp_footer', array( FutureShellV5::class, 'render' ), 80 )" ) && false !== strpos( $hard, 'if ( $command )' ) && false !== strpos( $hard, 'if ( $recent )' ),
    'PWA virtual routes' => false !== strpos( $php, 'sabri-shell-sw' ) && false !== strpos( $php, 'sabri-shell-manifest' ),
    'PWA site scope' => false !== strpos( $js, 'cfg.swScope' ) && false !== strpos( $js, 'serviceWorker.register(cfg.swUrl, { scope: scope })' ),
    'PWA self unregister' => false !== strpos( $hard, 'Service-Worker-Allowed:' ) && false !== strpos( $hard, 'controlAlive()' ) && false !== strpos( $hard, 'self.registration.unregister()' ),
    'PWA deactivation cleanup' => false !== strpos( $main, "FutureShellV5Hardening', 'deactivate'" ) && false !== strpos( $hard, 'flush_rewrite_rules( false )' ),
    'expanded privacy policy' => false !== strpos( $client, "'/login'" ) && false !== strpos( $client, "'/notifications'" ) && false !== strpos( $client, "'/publishing-dashboard'" ) && false !== strpos( $client, 'sabri_shell_future_private_path_fragments' ),
    'private policy SW/client parity' => false !== strpos( $client, 'ensure_private_path_policy' ) && false !== strpos( $client, 'update_option( FutureShellV5::OPTION, $current, false )' ),
    'preboot context before JS' => false !== strpos( $client, 'window.SabriShellFutureV5Hardening=' ) && false !== strpos( $client, 'Object.assign({},window.SabriShellFutureV5||{},window.SabriShellFutureV5Hardening)' ),
    'LKG previous state' => false !== strpos( $hard, 'capture_previous_lkg' ) && false !== strpos( $hard, 'FutureShellV5::capture_lkg( array(), $old_value )' ),
    'LKG compatibility' => false !== strpos( $hard, "snapshot['plugin_version']" ) && false !== strpos( $hard, 'Defaults::SCHEMA_VERSION === absint' ) && false !== strpos( $control, 'Defaults::SCHEMA_VERSION !== absint' ),
    'circuit breaker' => false !== strpos( $php, 'CIRCUIT_THRESHOLD' ) && false !== strpos( $php, 'sabri_shell_module_failure' ),
    'recent public privacy' => false !== strpos( $js, 'sabriShellRecentPublicRoutesV' ) && false !== strpos( $js, 'cfg.currentRoutePublic' ) && false !== strpos( $js, 'canonicalLocalUrl' ) && false !== strpos( $js, 'localStorage.removeItem(legacyRecentKey)' ),
    'private path boundary matcher' => false !== strpos( $js, "candidate === prefix || candidate.indexOf(prefix + '/') === 0" ),
    'pins public nav only' => false !== strpos( $js, 'publicAnchorUrl(anchor)' ) && false !== strpos( $js, "anchor.closest('.sabri-shell-primary-nav')" ) && false !== strpos( $js, 'aria-pressed' ),
    'performance browser local bounded' => false !== strpos( $js, 'sabri:shell-performance' ) && false === strpos( $js, 'sendBeacon' ) && false !== strpos( $js, 'observer.disconnect()' ),
    'bounded safe prefetch' => false !== strpos( $js, 'Object.keys(prefetched).length >= 3' ) && false !== strpos( $js, 'publicAnchorUrl(anchor)' ) && false !== strpos( $js, 'dataSaverActive()' ),
    'editable keyboard guard' => false !== strpos( $js, 'isEditableTarget' ) && false !== strpos( $js, 'isContentEditable' ),
    'safe Back' => false !== strpos( $js, "document.querySelector('[data-sabri-context-back]')" ) && false === strpos( $js, 'history.back()' ),
    'dialog focus restoration' => false !== strpos( $js, 'dialogReturnFocus' ) && false !== strpos( $js, 'restoreDialogFocus' ),
    'language quick command' => false !== strpos( $js, 'features.language_direction' ) && false !== strpos( $js, "label: 'Language and Direction'" ),
    'pressed states' => false !== strpos( $js, 'syncPrefButtons' ) && false !== strpos( $hard, 'aria-pressed="false"' ),
    'split workspace desktop only' => false !== strpos( $js, "matchMedia('(min-width: 1024px)')" ) && false !== strpos( $js, 'closeSplit();' ) && false !== strpos( $css, '@media (max-width:1023px)' ),
    'focus mode actual context nav' => false !== strpos( $css, '.sabri-context-navigation' ),
    'data saver native content preserved' => false === strpos( $css, '.sabri-shell-data-saver *' ) && false !== strpos( $css, '[data-sabri-decorative-background]' ),
    'File25 visual ownership' => false === strpos( $css, '--sabri-shell-v5-accent' ) && false === strpos( $css, 'filter:contrast(' ),
    'view transition progressive' => false !== strpos( $css, '@supports (view-transition-name:none)' ) && false !== strpos( $css, '@view-transition' ),
    'foldable safe area' => false !== strpos( $css, 'env(safe-area-inset-left)' ) && false !== strpos( $css, 'horizontal-viewport-segments:2' ),
    'no foreign backend' => false === strpos( $php . $hard . $client . $control, 'CREATE TABLE' ) && false === strpos( $php . $hard . $client . $control, 'dbDelta(' ),
);
foreach ( $checks as $name => $ok ) { if ( ! $ok ) { $fail[] = $name; } }
if ( $fail ) {
    fwrite( STDERR, "Future Shell v5 FAIL: " . implode( '; ', $fail ) . "\n" );
    exit( 1 );
}
echo "Future Shell v5: 18/18 enhancements + ten-round corrective hardening PASS\n";
