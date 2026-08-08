<?php
$root   = dirname( __DIR__ );
$third  = file_get_contents( $root . '/includes/class-future-shell-v5-third-hardening.php' );
$layout = file_get_contents( $root . '/includes/class-layout.php' );
$main   = file_get_contents( $root . '/sabri-unified-application-shell.php' );
$readme = file_get_contents( $root . '/README.md' );
$fail   = array();

$required_routes = array(
    '/account-security', '/account-passkeys', '/resolve-account',
    '/membership-application', '/membership-status', '/guardian-consent', '/membership-security',
    '/platform-system-check', '/platform-foundation/status', '/security-center'
);
foreach ( $required_routes as $route ) {
    if ( false === strpos( $third, "'{$route}'" ) ) {
        $fail[] = 'missing protected route ' . $route;
    }
}

$required_task_slugs = array(
    'account-security', 'account-passkeys', 'resolve-account', 'membership-application',
    'membership-status', 'guardian-consent', 'membership-security', 'platform-system-check', 'platform-foundation'
);
foreach ( $required_task_slugs as $slug ) {
    if ( false === strpos( $layout, "'{$slug}'" ) ) {
        $fail[] = 'missing Minimal task slug ' . $slug;
    }
}

$checks = array(
    'current release preserves 1.4.3 hardening' => false !== strpos( $main, '* Version: 1.4.4' ) && false !== strpos( $main, "define( 'SABRI_SHELL_VERSION', '1.4.4' );" ),
    'third hardening loaded' => false !== strpos( $main, "class-future-shell-v5-third-hardening.php" ) && false !== strpos( $main, 'FutureShellV5ThirdHardening::register();' ),
    'contract 1.0.3' => false !== strpos( $third, "CONTRACT_VERSION  = '1.0.3'" ),
    'privacy registry bounded' => false !== strpos( $third, 'MAX_PRIVATE_PATHS = 128' ) && false !== strpos( $third, "'overflow_count'" ),
    'overflow fails closed in browser' => false !== strpos( $third, 'privacyPolicyComplete' ) && false !== strpos( $third, 'recent_resume:false,predictive_prefetch:false,smart_navigation:false' ),
    'overflow fails closed in service worker' => false !== strpos( $third, 'const POLICY_COMPLETE=') && false !== strpos( $third, 'return !POLICY_COMPLETE||PRIVATE.some' ),
    'single final PWA owner' => false !== strpos( $third, "remove_action( 'template_redirect', array( FutureShellV5::class, 'serve_virtual_assets' ), 0 )" ) && false !== strpos( $third, "remove_action( 'template_redirect', array( FutureShellV5Hardening::class, 'serve_hardened_service_worker' ), -20 )" ) && false !== strpos( $third, "remove_action( 'template_redirect', array( FutureShellV5SecondHardening::class, 'serve_virtual_assets' ), -30 )" ) && false !== strpos( $third, "add_action( 'template_redirect', array( __CLASS__, 'serve_virtual_assets' ), -40 )" ),
    'server private headers harmonized' => false !== strpos( $third, "add_filter( 'sabri_shell_private_request'" ) && false !== strpos( $third, 'force_private_headers' ),
    'latest File01 boundary from third pass preserved' => false !== strpos( $third, 'bootstrap-registries-contracts-activation-shared-conventions' ) && false !== strpos( $third, 'consume-foundation-registry-no-shell-or-search-truth' ),
    'latest File02 boundary from third pass preserved' => false !== strpos( $third, 'credentials-passkeys-sessions-risk-recovery-account-completion' ) && false !== strpos( $third, 'modern-auth-1.3.0-candidate-staging-unaccepted' ),
    'Sabri green continuity fallback' => false !== strpos( $third, "BRAND_FALLBACK    = '#087a4e'" ) && false !== strpos( $third, 'apply_continuity_brand_fallback' ),
    'no foreign backend' => false === strpos( $third, 'CREATE TABLE' ) && false === strpos( $third, 'dbDelta(' ) && false === strpos( $third, 'INSERT INTO' ),
);
foreach ( $checks as $name => $ok ) {
    if ( ! $ok ) {
        $fail[] = $name;
    }
}

if ( $fail ) {
    fwrite( STDERR, "Future Shell v5 third hardening FAIL: " . implode( '; ', $fail ) . "\n" );
    exit( 1 );
}

echo "Future Shell v5 third ten-round hardening preserved under 1.4.4: protected routes, overflow fail-closed, single PWA owner and prior contracts PASS\n";
