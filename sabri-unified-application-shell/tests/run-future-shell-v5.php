<?php
$root    = dirname( __DIR__ );
$php     = file_get_contents( $root . '/includes/class-future-shell-v5.php' );
$hard    = file_get_contents( $root . '/includes/class-future-shell-v5-hardening.php' );
$client  = file_get_contents( $root . '/includes/class-future-shell-v5-client-context.php' );
$control = file_get_contents( $root . '/includes/class-future-shell-v5-control-guard.php' );
$second  = file_get_contents( $root . '/includes/class-future-shell-v5-second-hardening.php' );
$third   = file_get_contents( $root . '/includes/class-future-shell-v5-third-hardening.php' );
$fourth  = file_get_contents( $root . '/includes/class-future-shell-v5-fourth-hardening.php' );
$fifth   = file_get_contents( $root . '/includes/class-future-shell-v5-fifth-hardening.php' );
$js      = file_get_contents( $root . '/assets/js/future-shell-v5.js' );
$guard   = file_get_contents( $root . '/assets/js/future-shell-v5-editable-guard.js' );
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
    'release 1.4.5' => false !== strpos( $main, '* Version: 1.4.5' ) && false !== strpos( $main, "define( 'SABRI_SHELL_VERSION', '1.4.5' );" ),
    'seven hardening layers registered' => false !== strpos( $main, 'FutureShellV5Hardening::register();' ) && false !== strpos( $main, 'FutureShellV5ClientContext::register();' ) && false !== strpos( $main, 'FutureShellV5ControlGuard::register();' ) && false !== strpos( $main, 'FutureShellV5SecondHardening::register();' ) && false !== strpos( $main, 'FutureShellV5ThirdHardening::register();' ) && false !== strpos( $main, 'FutureShellV5FourthHardening::register();' ) && false !== strpos( $main, 'FutureShellV5FifthHardening::register();' ),
    'File26 search preserved' => false !== strpos( $php, 'FourPlanHarmonization::file26_search_contract()' ),
    'release rings present' => false !== strpos( $php, "case 'limited'" ) && false !== strpos( $php, "case 'staging'" ),
    'stored ring sanitizer fails closed' => false !== strpos( $hard, "\$ring    = 'disabled';" ),
    'fifth final release-ring evaluator' => false !== strpos( $fifth, "CONTRACT_VERSION = '1.0.5'" ) && false !== strpos( $fifth, 'sabri_shell_future_internal_principal_allowed' ) && false !== strpos( $fifth, 'PHP_INT_MAX' ),
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
    'dynamic private policy no longer persisted' => false !== strpos( $second, "remove_action( 'init', array( FutureShellV5ClientContext::class, 'ensure_private_path_policy' ), 6 )" ) && false !== strpos( $second, "apply_filters( 'sabri_shell_future_private_path_fragments'" ),
    'preboot context before JS' => false !== strpos( $client, 'window.SabriShellFutureV5Hardening=' ) && false !== strpos( $client, 'Object.assign({},window.SabriShellFutureV5||{},window.SabriShellFutureV5Hardening)' ),
    'second preboot context overrides subdirectory paths' => false !== strpos( $second, 'scoped_private_paths' ) && false !== strpos( $second, 'currentRoutePublic' ) && false !== strpos( $second, 'SabriShellFutureV5SecondHardening' ),
    'third privacy overflow fail closed' => false !== strpos( $third, 'MAX_PRIVATE_PATHS' ) && false !== strpos( $third, 'privacyPolicyComplete' ) && false !== strpos( $third, 'POLICY_COMPLETE' ),
    'third latest auth routes private' => false !== strpos( $third, "'/account-security'" ) && false !== strpos( $third, "'/account-passkeys'" ) && false !== strpos( $third, "'/resolve-account'" ),
    'third single final PWA owner' => false !== strpos( $third, "remove_action( 'template_redirect', array( FutureShellV5::class, 'serve_virtual_assets' ), 0 )" ) && false !== strpos( $third, "remove_action( 'template_redirect', array( FutureShellV5SecondHardening::class, 'serve_virtual_assets' ), -30 )" ),
    'third current Sabri Green fallback' => false !== strpos( $third, "BRAND_FALLBACK    = '#087a4e'" ),
    'fourth latest companion contracts' => false !== strpos( $fourth, 'membership-core-1.2.13' ) && false !== strpos( $fourth, 'foundation-runtime-2.0.0-future-foundation-18' ) && false !== strpos( $fourth, 'modern-auth-1.3.0-candidate' ) && false !== strpos( $fourth, 'notifications-runtime-3.0.0-intelligent-attention-os' ) && false !== strpos( $fourth, 'home-news-runtime-1.0.1-ng30-amended' ),
    'fourth File24 state boundary' => false !== strpos( $fourth, "'incident-containment'" ) && false !== strpos( $fourth, 'native-modules-enforce' ),
    'fourth public WebAuthn standard route' => false !== strpos( $fourth, '/.well-known/webauthn' ) && false === strpos( $third, "'/.well-known/webauthn'" ),
    'fifth internal principal extension remains fail-closed' => false !== strpos( $fifth, "case 'internal':" ) && false !== strpos( $fifth, "current_user_can( 'manage_options' )" ) && false !== strpos( $fifth, 'get_current_user_id()' ),
    'LKG previous state' => false !== strpos( $hard, 'capture_previous_lkg' ) && false !== strpos( $hard, 'FutureShellV5::capture_lkg( array(), $old_value )' ),
    'LKG compatibility' => false !== strpos( $hard, "snapshot['plugin_version']" ) && false !== strpos( $hard, 'Defaults::SCHEMA_VERSION === absint' ) && false !== strpos( $control, 'Defaults::SCHEMA_VERSION !== absint' ),
    'circuit breaker bounded' => false !== strpos( $php, 'CIRCUIT_THRESHOLD' ) && false !== strpos( $second, 'MAX_CIRCUITS' ) && false !== strpos( $second, 'bound_circuit_state' ) && false !== strpos( $second, 'circuit_state_count' ),
    'recent public privacy' => false !== strpos( $js, 'sabriShellRecentPublicRoutesV' ) && false !== strpos( $js, 'cfg.currentRoutePublic' ) && false !== strpos( $js, 'canonicalLocalUrl' ) && false !== strpos( $js, 'localStorage.removeItem(legacyRecentKey)' ),
    'private path boundary matcher' => false !== strpos( $js, "candidate === prefix || candidate.indexOf(prefix + '/') === 0" ),
    'pins public nav only' => false !== strpos( $js, 'publicAnchorUrl(anchor)' ) && false !== strpos( $js, "anchor.closest('.sabri-shell-primary-nav')" ) && false !== strpos( $js, 'aria-pressed' ),
    'performance browser local bounded' => false !== strpos( $js, 'sabri:shell-performance' ) && false === strpos( $js, 'sendBeacon' ) && false !== strpos( $js, 'observer.disconnect()' ),
    'bounded safe prefetch' => false !== strpos( $js, 'Object.keys(prefetched).length >= 3' ) && false !== strpos( $js, 'publicAnchorUrl(anchor)' ) && false !== strpos( $js, 'dataSaverActive()' ),
    'editable keyboard guard' => false !== strpos( $js, 'isEditableTarget' ) && false !== strpos( $guard, 'event.stopImmediatePropagation()' ) && false !== strpos( $guard, "toLowerCase() !== 'k'" ),
    'safe Back' => false !== strpos( $js, "document.querySelector('[data-sabri-context-back]')" ) && false === strpos( $js, 'history.back()' ),
    'dialog focus restoration' => false !== strpos( $js, 'dialogReturnFocus' ) && false !== strpos( $js, 'restoreDialogFocus' ),
    'language quick command' => false !== strpos( $js, 'features.language_direction' ) && false !== strpos( $js, "label: 'Language and Direction'" ),
    'pressed states' => false !== strpos( $js, 'syncPrefButtons' ) && false !== strpos( $hard, 'aria-pressed="false"' ),
    'split workspace desktop only and non-immersive' => false !== strpos( $js, "matchMedia('(min-width: 1024px)')" ) && false !== strpos( $js, 'closeSplit();' ) && false !== strpos( $css, '@media (max-width:1023px)' ) && false !== strpos( $second, 'Layout::IMMERSIVE === Layout::current_mode()' ),
    'focus mode actual context nav' => false !== strpos( $css, '.sabri-context-navigation' ),
    'data saver native content preserved' => false === strpos( $css, '.sabri-shell-data-saver *' ) && false !== strpos( $css, '[data-sabri-decorative-background]' ),
    'File25 visual ownership' => false === strpos( $css, '--sabri-shell-v5-accent' ) && false === strpos( $css, 'filter:contrast(' ) && false !== strpos( $css, 'var(--sabri-shell-radius' ) && false !== strpos( $css, 'var(--sabri-shell-shadow' ) && false !== strpos( $second, 'CentralPlanContract::visual_tokens()' ),
    'File20 does not globally restyle native paragraph spacing' => false === strpos( $css, '.sabri-shell-a11y-spacing p,.sabri-shell-a11y-spacing li' ),
    'PWA disabled virtual routes retire workers' => false !== strpos( $second, 'status_header( 410 )' ) && false !== strpos( $second, 'Sabri Shell PWA is disabled.' ),
    'PWA cache version follows plugin version' => false !== strpos( $second, "preg_replace( '/[^a-z0-9]+/i', '', SABRI_SHELL_VERSION )" ),
    'PWA manifest consumes File25 tokens' => false !== strpos( $second, "'theme_color'" ) && false !== strpos( $second, "tokens['primary_color']" ) && false !== strpos( $second, "tokens['background']" ),
    'partial settings preserve old values' => false !== strpos( $second, 'preserve_partial_future_settings' ) && false !== strpos( $second, 'array_replace( $old_features[ $feature ]' ),
    'view transition progressive' => false !== strpos( $css, '@supports (view-transition-name:none)' ) && false !== strpos( $css, '@view-transition' ),
    'foldable safe area' => false !== strpos( $css, 'env(safe-area-inset-left)' ) && false !== strpos( $css, 'horizontal-viewport-segments:2' ),
    'no foreign backend' => false === strpos( $php . $hard . $client . $control . $second . $third . $fourth . $fifth, 'CREATE TABLE' ) && false === strpos( $php . $hard . $client . $control . $second . $third . $fourth . $fifth, 'dbDelta(' ),
);
foreach ( $checks as $name => $ok ) { if ( ! $ok ) { $fail[] = $name; } }
if ( $fail ) {
    fwrite( STDERR, "Future Shell v5 FAIL: " . implode( '; ', $fail ) . "\n" );
    exit( 1 );
}
echo "Future Shell v5: 18/18 enhancements + five ten-round corrective passes PASS\n";
