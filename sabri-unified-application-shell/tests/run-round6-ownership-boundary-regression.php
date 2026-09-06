<?php
/** Round 6: canonical ownership and source-of-truth regression. */
$root = dirname( __DIR__ );
$fail = static function ( $message ) {
    fwrite( STDERR, "FAIL: {$message}\n" );
    exit( 1 );
};
$read = static function ( $path ) use ( $root, $fail ) {
    $full = $root . '/' . $path;
    $data = is_file( $full ) ? file_get_contents( $full ) : false;
    if ( false === $data ) { $fail( 'Cannot read ' . $path ); }
    return $data;
};

$harmonization = $read( 'includes/class-four-plan-harmonization.php' );
$client        = $read( 'assets/js/four-plan-harmonization.js' );
$central       = $read( 'includes/class-central-plan-contract.php' );
$uninstall     = $read( 'uninstall.php' );

foreach ( array(
    'WELCOME_USER_META', 'WELCOME_COOKIE', 'WELCOME_SESSION_COOKIE',
    'WELCOME_STORAGE_KEY', 'WELCOME_SESSION_KEY', 'sabri_shell_welcome_dismiss',
    'update_user_meta(', 'get_user_meta(', 'setcookie(',
) as $forbidden ) {
    if ( false !== strpos( $harmonization, $forbidden ) ) {
        $fail( 'File 20 still owns Welcome Intro preference/session state: ' . $forbidden );
    }
}
foreach ( array( 'localStorage', 'sessionStorage', 'data-sabri-welcome-dismiss', 'sabri:welcome-dismissed' ) as $forbidden ) {
    if ( false !== strpos( $client, $forbidden ) ) {
        $fail( 'File 20 client still persists or consumes File 13 Welcome state: ' . $forbidden );
    }
}
if ( false === strpos( $harmonization, "'preference_state_owner' => 'file-13'" ) ) {
    $fail( 'File 13 preference-state ownership is not explicit in the shell invocation contract.' );
}
if ( false === strpos( $harmonization, "apply_filters( 'sabri_shell_welcome_request_eligible'" ) ) {
    $fail( 'Shell-owned route/layout eligibility extension point is missing.' );
}
if ( false !== strpos( $uninstall, 'sabri_shell_welcome_dismissed_at' ) ) {
    $fail( 'File 20 uninstall still deletes File 13-owned welcome state.' );
}
if ( false === strpos( $central, "'bootstrap-registry-contracts-activation-shared-conventions'" )
    || false === strpos( $central, "'consume-foundation-registry-no-shell-or-search-truth'" ) ) {
    $fail( 'Base canonical File 01-B metadata still claims Search ownership/truth.' );
}
if ( false !== strpos( $central, 'bootstrap-registry-search-federation' )
    || false !== strpos( $central, 'consume-registry-and-mount-search' ) ) {
    $fail( 'Stale File 01-B Search ownership language remains in base canonical registry.' );
}

echo "Round 6 canonical ownership/source-of-truth regression PASS\n";
