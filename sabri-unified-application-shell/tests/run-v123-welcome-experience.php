<?php
/**
 * File 20 V12.3 welcome privacy/eligibility contract regressions.
 * No real user/device data and no production writes.
 */
require __DIR__ . '/bootstrap.php';
require_once dirname( __DIR__ ) . '/includes/class-welcome-experience.php';

use Sabri\UnifiedShell\WelcomeExperience;

$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: " . $message . PHP_EOL );
		exit( 1 );
	}
	$checks++;
};

$default = WelcomeExperience::defaults();
$assert( false === $default['enabled'] && 30 === $default['frequency_days'], 'must be disabled by default and >=30 days' );
$assert( array( '/' ) === $default['eligible_paths'], 'home-only default placement' );
$assert( false === WelcomeExperience::public_eligible(), 'no Founder approval or activation cannot expose public intro' );

$config = WelcomeExperience::sanitize( array(
	'enabled' => true,
	'frequency_days' => 1,
	'title' => '<script>bad</script>Sabri Welcome',
	'eligible_paths' => array( '/', '/news/', '/account', '/clinical/visit', '//other-host', '/../escape', '/appointment/123', '/settings/profile' ),
), $default );
$assert( true === $config['enabled'], 'operator request preserved but separately approval-gated' );
$assert( 30 === $config['frequency_days'], 'frequency cannot be shortened by an input option' );
$english = WelcomeExperience::localized_panel_copy( $default, 'en_US' );
$urdu = WelcomeExperience::localized_panel_copy( $default, 'ur_PK' );
$arabic = WelcomeExperience::localized_panel_copy( $default, 'ar_SA' );
$assert( 'Explore platform' === $english['continue'] && 'en' === $english['language'], 'American English remains canonical base.' );
$assert( false !== strpos( $urdu['title'], 'صابری' ) && 'چھوڑ دیں' === $urdu['skip'], 'Urdu title and Skip are independently localized.' );
$assert( false !== strpos( $arabic['title'], 'مرحبًا' ) && 'إغلاق' === $arabic['close'], 'Arabic copy and Close are independently localized.' );
$assert( 'en' === WelcomeExperience::localized_panel_copy( $default, 'fr_FR' )['language'], 'Unsupported locale uses approved English fallback.' );
$custom = $default;
$custom['title_ur'] = 'درست شدہ عنوان';
$assert( 'درست شدہ عنوان' === WelcomeExperience::localized_panel_copy( $custom, 'ur' )['title'], 'Founder-editable Urdu override renders without changing English.' );
$assert( $default['title'] === WelcomeExperience::localized_panel_copy( $custom, 'en_US' )['title'], 'Localized copy never mutates source English text.' );

$assert( null === WelcomeExperience::utc_timestamp( '' ), 'Empty UTC schedule is unbounded.' );
$assert( 1794744000 === WelcomeExperience::utc_timestamp( '2026-11-15 12:00:00' ), 'UTC time is interpreted independently of server timezone.' );
$assert( false === WelcomeExperience::utc_timestamp( '2026-02-30 12:00:00' ), 'Nonexistent calendar date is not accepted.' );
$assert( false === WelcomeExperience::utc_timestamp( 'tomorrow' ), 'Relative dates are forbidden.' );
$assert( false === WelcomeExperience::utc_timestamp( '2026-11-15 12:00 GMT+5' ), 'Ambiguous non-UTC strings are forbidden.' );
$invalid_schedule = WelcomeExperience::sanitize( array( 'start_at' => 'tomorrow' ), $default );
$assert( '__invalid_utc__' === $invalid_schedule['start_at'], 'Invalid stored schedules remain visibly fail-closed.' );

$assert( false === strpos( $config['title'], '<script>' ), 'copy is sanitized' );
$assert( array( '/', '/news' ) === $config['eligible_paths'], 'sensitive, deceptive and traversal paths rejected' );

$assert( WelcomeExperience::route_eligible( '/', $config ), 'eligible public root' );
$assert( WelcomeExperience::route_eligible( '/news/', $config ), 'explicitly approved eligible path' );
$assert( ! WelcomeExperience::route_eligible( '/news/?token=private', $config ), 'deep-link query suppresses auto intro' );
$assert( ! WelcomeExperience::route_eligible( '/appointment/123', $config ), 'appointment suppression' );
$assert( ! WelcomeExperience::route_eligible( '/account', $config ), 'account suppression' );
$assert( ! WelcomeExperience::route_eligible( '/clinical/visit', $config ), 'clinical suppression' );
$assert( ! WelcomeExperience::route_eligible( '/news/%2e%2e/account', $config ), 'encoded traversal suppressed' );
$assert( '' === WelcomeExperience::normalize_path( '/news/%252e%252e/account' ), 'double-encoded traversal is rejected' );
$assert( '' === WelcomeExperience::normalize_path( '/news/%252faccount' ), 'double-encoded separator is rejected' );
$assert( ! WelcomeExperience::route_eligible( '//external.example/', $config ), 'network-path reference suppressed' );
$assert( ! WelcomeExperience::route_eligible( '/news/#section', $config ), 'fragment links not auto-eligible' );
$assert( WelcomeExperience::manual_requested( '/?sabri_welcome=manual' ), 'explicit manual replay home URL is recognized' );
$assert( ! WelcomeExperience::manual_requested( '/?sabri_welcome=manual&token=123' ), 'manual replay rejects extra query arguments' );
$assert( ! WelcomeExperience::manual_requested( '/clinical?sabri_welcome=manual' ), 'manual replay cannot bypass sensitive-route suppression' );
$assert( WelcomeExperience::sensitive_path( '/emergency/help' ), 'emergency path always suppressed' );
$assert( WelcomeExperience::sensitive_path( '/EMERGENCY/HELP' ), 'case-insensitive emergency route always suppressed' );
$assert( WelcomeExperience::sensitive_path( '/WP-LOGIN.PHP' ), 'case-folded login is denied' );
$assert( WelcomeExperience::sensitive_path( '/booking' ), 'booking route always suppressed' );
$assert( ! WelcomeExperience::sensitive_path( '/encyclopedia' ), 'public reference content not globally excluded' );

$GLOBALS['test_options'][ WelcomeExperience::OPTION ] = $config;
$assert( false === WelcomeExperience::public_eligible(), 'Founder gate remains deny-by-default even when configuration enabled' );
$contract = WelcomeExperience::contract();
$assert( 'file-20' === $contract['owner'] && 'file-25' === $contract['visual_owner'], 'canonical ownership declaration' );
$assert( false === $contract['public_eligible'] && 'compatibility-only' === $contract['legacy_file13'], 'legacy File13 cannot be a public owner' );

$source = file_get_contents( dirname( __DIR__ ) . '/includes/class-four-plan-harmonization.php' );
$assert( false === strpos( $source, "add_action( 'wp_body_open', array( __CLASS__, 'invoke_welcome_intro' )" ), 'retired public File13 invocation not registered' );
$assert( false !== strpos( $source, 'WelcomeExperience::register();' ), 'new canonical owner registered once' );

echo "File 20 V12.3 welcome source/route/security contract: {$checks} passed" . PHP_EOL;
