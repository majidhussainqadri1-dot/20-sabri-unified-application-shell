<?php
/**
 * Real WordPress + real File20/File25 smoke, executed on an ephemeral CI DB.
 * It must never run against staging/production or mutate any user data.
 */
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	fwrite( STDERR, "This test requires an isolated WordPress CLI installation.\n" );
	exit( 2 );
}
$fail = static function ( $why ) {
	WP_CLI::error( '[V12.3] ' . $why );
};
$assert = static function ( $condition, $why ) use ( $fail ) {
	if ( ! $condition ) { $fail( $why ); }
};

$assert( defined( 'SABRI_SHELL_VERSION' ), 'File20 must actually be active.' );
$assert( defined( 'SABRI_PUBLIC_EXPERIENCE_VERSION' ), 'File25 must actually be active.' );
$assert( version_compare( PHP_VERSION, '8.3', '>=' ), 'PHP 8.3 baseline required.' );
$assert( class_exists( 'Sabri\\UnifiedShell\\WelcomeExperience' ), 'File20 welcome class autoload.' );
$assert( class_exists( 'Sabri\\PublicExperience\\Three_Plan_Corrections' ), 'File25 presentation class autoload.' );
$assert( class_exists( 'Sabri\\PublicExperience\\Design_System' ), 'File25 native owner class autoload.' );
$owner = 'Sabri\\UnifiedShell\\WelcomeExperience';
$visual = 'Sabri\\PublicExperience\\Design_System';
$assert( did_action( 'plugins_loaded' ) > 0, 'WordPress loaded plugins normally.' );
$assert( false !== has_action( 'wp_body_open', array( $owner, 'render' ) ), 'Actual body welcome placement registered.' );
$assert( false !== has_action( 'wp_footer', array( $owner, 'render' ) ), 'Actual footer fallback registered.' );
$assert( false !== has_filter( 'sabri_shell_file25_visual_contract', array( $visual, 'filter_shell_visual_contract' ) ),
	'Real File25 registered its unique visual contract.' );
$visual_contract = apply_filters( 'sabri_shell_file25_visual_contract', array() );
$assert( is_array( $visual_contract ) && 'file-25' === ( $visual_contract['owner'] ?? '' ),
	'Real File25 visual owner contract accepted.' );
$assert( ( $visual_contract['version'] ?? '' ) === $visual::CONTRACT_VERSION, 'File25 contract revision consistent.' );
$assert( false === has_action( 'sabri_shell_welcome_intro_invoke' ), 'Retired File13 public hook absent.' );
$defaults = $owner::defaults();
$assert( false === $defaults['enabled'] && 30 === $defaults['frequency_days'], 'Default disabled and 30 days.' );

$args = array(
	'invoked_by_file_20' => true,
	'title' => 'Welcome',
	'message' => 'Classical homeopathy education',
	'continue_url' => home_url( '/' ),
);
$markup = sabri_visual_experience_render_welcome( $args );
$assert( is_string( $markup ) && '' !== $markup, 'Real File25 rendered genuine owner-invoked markup.' );
foreach ( array( 'data-sabri-welcome-skip', 'data-sabri-welcome-dismiss', 'data-sabri-welcome-continue', 'aria-live="polite"' ) as $token ) {
	$assert( false !== strpos( $markup, $token ), 'Real markup must include ' . $token );
}
$assert( '' === sabri_visual_experience_render_welcome( array( 'title' => 'Uninvoked' ) ),
	'File25 cannot self-invoke without canonical File20.' );

$assert( null === $owner::utc_timestamp( '' ), 'No schedule is unbounded.' );
$assert( 1794744000 === $owner::utc_timestamp( '2026-11-15 12:00:00' ), 'UTC parser matches fixed epoch.' );
$assert( false === $owner::utc_timestamp( '2026-02-30 12:00:00' ), 'Invalid UTC calendar date rejected.' );
$assert( $owner::route_eligible( '/', $defaults ), 'Root home path is available as eligible route when configured.' );
$assert( ! $owner::route_eligible( '/wp-login.php', $defaults ), 'Login is never eligible.' );

// This is a fresh, isolated database. No guest or account records are touched.
$assert( false === get_option( $owner::APPROVAL_OPTION, false ), 'No Founder approval is fabricated by source CI.' );
$assert( ! $owner::public_eligible(), 'Without an authenticated Founder approval, no automatic public intro.' );

WP_CLI::success( sprintf(
	'V12.3 real WordPress %s / PHP %s / File20 %s / File25 %s smoke passed (source-only staging simulation)',
	get_bloginfo( 'version' ), PHP_VERSION, SABRI_SHELL_VERSION, SABRI_PUBLIC_EXPERIENCE_VERSION
) );
