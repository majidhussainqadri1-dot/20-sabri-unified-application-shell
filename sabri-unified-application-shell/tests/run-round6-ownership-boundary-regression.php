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
$welcome       = $read( 'includes/class-welcome-experience.php' );
$device        = $read( 'assets/js/welcome-experience.js' );

foreach ( array(
    'WELCOME_USER_META', 'WELCOME_COOKIE', 'WELCOME_SESSION_COOKIE',
    'WELCOME_STORAGE_KEY', 'WELCOME_SESSION_KEY', 'sabri_shell_welcome_dismiss',
    'update_user_meta(', 'get_user_meta(', 'setcookie(',
) as $forbidden ) {
    if ( false !== strpos( $harmonization, $forbidden ) ) {
        $fail( 'Legacy File20 harmonization must not duplicate the new owner: ' . $forbidden );
    }
}
foreach ( array( 'localStorage', 'sessionStorage', 'data-sabri-welcome-dismiss', 'sabri:welcome-dismissed' ) as $forbidden ) {
    if ( false !== strpos( $client, $forbidden ) ) {
        $fail( 'Retired File20 client must not duplicate the canonical welcome controller: ' . $forbidden );
    }
}
if ( false !== strpos( $harmonization, "'preference_state_owner' => 'file-13'" ) ||
     false === strpos( $harmonization, 'WelcomeExperience::register();' ) ) {
    $fail( 'The obsolete File13 state owner was not replaced with canonical File20 ownership.' );
}
if ( false === strpos( $welcome, 'STORAGE_KEY' ) ||
     false === strpos( $welcome, 'self::approval_valid( $config )' ) ||
     false === strpos( $welcome, 'self::route_eligible( $uri, $config )' ) ) {
    $fail( 'File20 welcome approval/device/route boundary is missing.' );
}
if ( false === strpos( $device, 'localStorage' ) || false === strpos( $device, 'sessionStorage' ) ||
     false === strpos( $device, 'data-sabri-welcome-skip' ) ) {
    $fail( 'File20 missing canonical per-device dismissal with session suppression.' );
}
if ( false !== strpos( $harmonization, "add_action( 'wp_body_open', array( __CLASS__, 'invoke_welcome_intro' )" ) ) {
    $fail( 'Legacy File13 public invocation must not register.' );
}
if ( false !== strpos( $uninstall, 'sabri_shell_welcome_dismissed_at' ) ) {
    $fail( 'File20 must not touch File13 legacy state.' );
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
