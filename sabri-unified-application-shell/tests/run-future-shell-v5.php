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
'command_palette','pwa_shell','offline_mode','data_saver','recent_resume','module_circuit_breaker','last_known_good','performance_guardian','smart_navigation','keyboard_accessibility','focus_mode','split_workspace','adaptive_foldable','view_transitions','predictive_prefetch','language_direction','accessibility_center','release_rings'
);
foreach ( $features as $feature ) {
    if ( false === strpos( $php, "'{$feature}'" ) ) { $fail[] = "missing {$feature}"; }
}
$checks = array(
    '18 features' => count( $features ) === 18,
    'release 1.4.1' => false !== strpos( $main, '* Version: 1.4.1' ) && false !== strpos( $main, "define( 'SABRI_SHELL_VERSION', '1.4.1' );" ),
    'hardening loaded and registered' => false !== strpos( $main, 'class-future-shell-v5-hardening.php' ) && false !== strpos( $main, 'FutureShellV5Hardening::register();' ),
    'preboot client context loaded and registered' => false !== strpos( $main, 'class-future-shell-v5-client-context.php' ) && false !== strpos( $main, 'FutureShellV5ClientContext::register();' ),
    'control guard loaded and registered' => false !== strpos( $main, 'class-future-shell-v5-control-guard.php' ) && false !== strpos( $main, 'FutureShellV5ControlGuard::register();' ),
    'File 26 search contract preserved' => false !== strpos( $php, 'FourPlanHarmonization::file26_search_contract()' ),
    'split workspace native render hook' => false !== strpos( $hard, "do_action( 'sabri_shell_split_workspace_render' )" ),
    'release rings present' => false !== strpos( $php, "case 'limited'" ) && false !== strpos( $php, "case 'staging'" ),
    'release rings fail closed after filters' => false !== strpos( $hard, "case 'disabled'" ) && false !== strpos( $hard, "default:\n\t\t\t\t\$allowed = false" ) && false !== strpos( $hard, 'return (bool) $enabled && $allowed;' ),
    'invalid persisted ring fails closed' => false !== strpos( $hard, '$ring    = \'disabled\';' ) && false !== strpos( $hard, '$percent = 0;' ),
    'invalid REST ring rejected before base callback' => false !== strpos( $control, "'/sabri-shell/v1/future/features'" ) && false !== strpos( $control, 'sabri_shell_invalid_release_ring' ) && false !== strpos( $control, "'status' => 400" ),
    'legacy automatic recovery blocked at final priority' => false !== strpos( $control, "add_filter( 'sabri_shell_auto_recovery_allowed'" ) && false !== strpos( $control, 'PHP_INT_MAX' ) && false !== strpos( $control, 'block_legacy_auto_restore' ),
    'guarded recovery uses try catch finally' => false !== strpos( $control, 'try {' ) && false !== strpos( $control, 'catch ( \\Throwable $error )' ) && false !== strpos( $control, 'finally {' ),
    'guarded recovery suppresses LKG overwrite' => false !== strpos( $control, "remove_action( 'update_option_' . Defaults::OPTION_NAME, $hook, 30 )" ) && false !== strpos( $control, "add_action( 'update_option_' . Defaults::OPTION_NAME, $hook, 30, 3 )" ),
    'ring aware output replaces unconditional footer' => false !== strpos( $hard, "remove_action( 'wp_footer', array( FutureShellV5::class, 'render' ), 80 )" ) && false !== strpos( $hard, 'if ( $command )' ) && false !== strpos( $hard, 'if ( $recent )' ),
    'PWA virtual routes' => false !== strpos( $php, 'sabri-shell-sw') && false !== strpos( $php, 'sabri-shell-manifest'),
    'PWA rewrite migration' => false !== strpos( $main, 'sabri_shell_future_rewrite_version' ) && false !== strpos( $main, "update_option( 'sabri_shell_flush_rewrite_rules', 1, false )" ),
    'PWA scope follows site path' => false !== strpos( $js, 'cfg.swScope') && false !== strpos( $js, 'serviceWorker.register(cfg.swUrl, { scope: scope })' ),
    'PWA least privilege and self unregister' => false !== strpos( $hard, 'header( \'Service-Worker-Allowed: \' . $scope )' ) && false !== strpos( $hard, 'controlAlive()' ) && false !== strpos( $hard, 'self.registration.unregister()' ),
    'private PWA exclusions centralized' => false !== strpos( $hard, "'/wp-json'" ) && false !== strpos( $hard, 'const PRIVATE=') && false !== strpos( $hard, 'privatePath(u.pathname)'),
    'expanded user-specific private policy' => false !== strpos( $client, "'/login'" ) && false !== strpos( $client, "'/notifications'" ) && false !== strpos( $client, "'/publishing-dashboard'" ) && false !== strpos( $client, 'sabri_shell_future_private_path_fragments' ),
    'private policy persisted for SW and client parity' => false !== strpos( $client, 'ensure_private_path_policy' ) && false !== strpos( $client, 'update_option( FutureShellV5::OPTION, $current, false )' ),
    'preboot client context merged before JS' => false !== strpos( $client, 'window.SabriShellFutureV5Hardening=' ) && false !== strpos( $client, 'Object.assign({},window.SabriShellFutureV5||{},window.SabriShellFutureV5Hardening)' ),
    'PWA deactivation cleanup' => false !== strpos( $main, "FutureShellV5Hardening', 'deactivate'" ) && false !== strpos( $hard, 'flush_rewrite_rules( false )' ),
    'LKG captures previous state' => false !== strpos( $hard, 'capture_previous_lkg' ) && false !== strpos( $hard, 'FutureShellV5::capture_lkg( array(), $old_value )' ) && false !== strpos( $hard, "remove_action( 'update_option_' . Defaults::OPTION_NAME" ),
    'LKG current version schema only' => false !== strpos( $hard, "snapshot['plugin_version']" ) && false !== strpos( $hard, 'Defaults::SCHEMA_VERSION === absint'),
    'guarded restore current version schema only' => false !== strpos( $control, "snapshot['plugin_version']" ) && false !== strpos( $control, 'Defaults::SCHEMA_VERSION !== absint' ),
    'circuit breaker' => false !== strpos( $php, 'CIRCUIT_THRESHOLD') && false !== strpos( $php, 'sabri_shell_module_failure'),
    'local recent public-route privacy' => false !== strpos( $js, 'sabriShellRecentPublicRoutesV') && false !== strpos( $js, 'cfg.currentRoutePublic') && false !== strpos( $js, 'canonicalLocalUrl') && false !== strpos( $js, 'localStorage.removeItem(legacyRecentKey)'),
    'boundary aware private paths' => false !== strpos( $js, "candidate === prefix || candidate.indexOf(prefix + '/') === 0" ),
    'smart pins limited to public shell nav' => false !== strpos( $js, 'publicAnchorUrl(anchor)') && false !== strpos( $js, "anchor.closest('.sabri-shell-primary-nav')") && false !== strpos( $js, 'aria-pressed'),
    'performance stays browser local and disconnects' => false !== strpos( $js, 'sabri:shell-performance') && false === strpos( $js, 'sendBeacon') && false !== strpos( $js, 'observer.disconnect()'),
    'bounded public prefetch' => false !== strpos( $js, 'Object.keys(prefetched).length >= 3') && false !== strpos( $js, 'publicAnchorUrl(anchor)') && false !== strpos( $js, 'dataSaverActive()'),
    'keyboard command palette' => false !== strpos( $js, "event.key.toLowerCase() === 'k'") && false !== strpos( $js, "event.altKey && event.key.toLowerCase() === 'h'"),
    'keyboard respects editable regions' => false !== strpos( $js, 'isEditableTarget') && false !== strpos( $js, 'isContentEditable'),
    'safe Back reuses contextual contract' => false !== strpos( $js, "document.querySelector('[data-sabri-context-back]')" ) && false === strpos( $js, 'history.back()'),
    'dialog focus restoration' => false !== strpos( $js, 'dialogReturnFocus') && false !== strpos( $js, 'restoreDialogFocus'),
    'language quick command' => false !== strpos( $js, 'features.language_direction') && false !== strpos( $js, "label: 'Language and Direction'"),
    'accessibility preferences pressed state' => false !== strpos( $js, 'syncPrefButtons') && false !== strpos( $hard, 'aria-pressed="false"'),
    'split workspace desktop only and escape close' => false !== strpos( $js, "matchMedia('(min-width: 1024px)')") && false !== strpos( $js, 'closeSplit();') && false !== strpos( $css, '@media (max-width:1023px)'),
    'focus mode hides actual context navigation' => false !== strpos( $css, '.sabri-context-navigation'),
    'data saver does not strip native content backgrounds' => false === strpos( $css, '.sabri-shell-data-saver *') && false !== strpos( $css, '[data-sabri-decorative-background]'),
    'File25 visual ownership preserved' => false === strpos( $css, '--sabri-shell-v5-accent') && false === strpos( $css, 'filter:contrast('),
    'view transition progressive enhancement' => false !== strpos( $css, '@supports (view-transition-name:none)') && false !== strpos( $css, '@view-transition'),
    'foldable safe area' => false !== strpos( $css, 'env(safe-area-inset-left)') && false !== strpos( $css, 'horizontal-viewport-segments:2'),
    'no foreign backend creation' => false === strpos( $php . $hard . $client . $control, 'CREATE TABLE') && false === strpos( $php . $hard . $client . $control, 'dbDelta('),
);
foreach ( $checks as $name => $ok ) { if ( ! $ok ) { $fail[] = $name; } }
if ( $fail ) {
    fwrite( STDERR, "Future Shell v5 FAIL: " . implode( '; ', $fail ) . "\n" );
    exit( 1 );
}
echo "Future Shell v5: 18/18 enhancements + ten-round corrective hardening PASS\n";
