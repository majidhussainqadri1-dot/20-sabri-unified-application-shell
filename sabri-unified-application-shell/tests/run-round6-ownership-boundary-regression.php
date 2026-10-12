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

$welcome = $read( 'includes/class-welcome-frequency.php' );
$welcome_js = $read( 'assets/js/welcome-frequency.js' );
if ( false !== strpos( $harmonization, 'sabri_shell_welcome_intro_invoke' ) ) {
    $fail( 'Retired File13 public renderer hook must not be invoked from File20.' );
}
if ( false === strpos( $harmonization, 'WelcomeFrequency::register()' )
    || false === strpos( $welcome, "'owner' => 'file-20'" )
    || false === strpos( $welcome, 'PERIOD_SECONDS = 2592000' )
    || false === strpos( $welcome, 'get_user_meta(' )
    || false === strpos( $welcome, 'update_user_meta(' )
    || false === strpos( $welcome_js, 'localStorage' )
    || false === strpos( $welcome_js, 'sessionStorage' ) ) {
    $fail( 'File20 canonical frequency, account/guest/session ownership contract is incomplete.' );
}
if ( false === strpos( $harmonization, "apply_filters( 'sabri_shell_welcome_request_eligible'" ) ) {
    $fail( 'Shell-owned route/layout eligibility extension point is missing.' );
}
if ( false !== strpos( $uninstall, 'delete_user_meta(' ) ) {
    $fail( 'Uninstall must not destroy File20 welcome preference state without explicit purge.' );
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
