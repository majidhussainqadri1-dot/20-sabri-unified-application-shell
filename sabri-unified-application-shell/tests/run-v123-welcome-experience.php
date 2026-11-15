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
$assert( false === strpos( $config['title'], '<script>' ), 'copy is sanitized' );
$assert( array( '/', '/news' ) === $config['eligible_paths'], 'sensitive, deceptive and traversal paths rejected' );

$assert( WelcomeExperience::route_eligible( '/', $config ), 'eligible public root' );
$assert( WelcomeExperience::route_eligible( '/news/', $config ), 'explicitly approved eligible path' );
$assert( ! WelcomeExperience::route_eligible( '/news/?token=private', $config ), 'deep-link query suppresses auto intro' );
$assert( ! WelcomeExperience::route_eligible( '/appointment/123', $config ), 'appointment suppression' );
$assert( ! WelcomeExperience::route_eligible( '/account', $config ), 'account suppression' );
$assert( ! WelcomeExperience::route_eligible( '/clinical/visit', $config ), 'clinical suppression' );
$assert( ! WelcomeExperience::route_eligible( '/news/%2e%2e/account', $config ), 'encoded traversal suppressed' );
$assert( ! WelcomeExperience::route_eligible( '//external.example/', $config ), 'network-path reference suppressed' );
$assert( ! WelcomeExperience::route_eligible( '/news/#section', $config ), 'fragment links not auto-eligible' );
$assert( WelcomeExperience::sensitive_path( '/emergency/help' ), 'emergency path always suppressed' );
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
