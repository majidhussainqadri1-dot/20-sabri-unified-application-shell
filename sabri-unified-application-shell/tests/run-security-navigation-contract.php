<?php
/**
 * V12.1 §12.5: File02's real rewrite and File24's actual published shortcode
 * must be authenticated separately before insertion into existing More nav.
 * All providers below are MOCKS; this is not staging or Live evidence.
 */
namespace Sabri\Platform\Security\Guidance {
	class SecurityGuidance { public function renderPublic() { return ''; } }
}
namespace {
	require_once __DIR__ . '/bootstrap.php';
	if ( ! defined( 'MINUTE_IN_SECONDS' ) ) { define( 'MINUTE_IN_SECONDS', 60 ); }
	class SAUTH_Canonical_Routes {
		public static function render_safety() {}
		public static function route_url( $url, $key ) {
			return 'safety' === $key ? home_url( '/account/safety/' ) : $url;
		}
	}
	class SAUTH_Account_Safety { public static function render() { return ''; } }
	define( 'SAUTH_VERSION', '1.8.7' );
	function has_action( $key, $callback = false ) {
		return isset( $GLOBALS['test_owner_hooks'][ $key ] ) ? $GLOBALS['test_owner_hooks'][ $key ] : false;
	}
	function has_filter( $key, $callback = false ) {
		return isset( $GLOBALS['test_owner_filters'][ $key ] ) ? $GLOBALS['test_owner_filters'][ $key ] : false;
	}
	require_once dirname( __DIR__ ) . '/includes/class-security-navigation.php';
	$assert = static function ( $valid, $message ) {
		if ( ! $valid ) { fwrite( STDERR, "File02/File24 nav FAIL: {$message}\n" ); exit( 1 ); }
	};
	$base = array( 'home' => array( 'url' => 'https://example.test/', 'key' => 'home' ) );
	$assert( $base === \Sabri\UnifiedShell\SecurityNavigation::augment( $base ), 'Missing owners must never create ghost menu links.' );
	foreach ( array(
		'https://outside.test/account/safety/',
		'http://example.test/account/safety/',
		'https://example.test/account/safety/?token=private',
		'https://example.test/account/safety/#secret',
		'https://example.test/%2faccount/safety/',
		'https://example.test/%252faccount/safety/',
		'https://example.test/../account/safety/',
		'https://example.test/account/../safety/'
	) as $url ) {
		$assert( '' === \Sabri\UnifiedShell\SecurityNavigation::public_url( $url ), 'Unsafe URL must fail closed.' );
	}
	$GLOBALS['test_options']['sauth_safety_route_ready'] = '1';
	$GLOBALS['test_options']['sauth_rewrite_version'] = SAUTH_VERSION;
	$GLOBALS['test_shortcodes'][] = 'sabri_auth_account_safety';
	$GLOBALS['test_owner_hooks']['template_redirect'] = 6;
	$GLOBALS['test_owner_filters']['sauth_canonical_route_url'] = 10;
	$GLOBALS['test_filter_overrides']['sauth_canonical_route_url'] = static function ( $url, $key ) {
		return SAUTH_Canonical_Routes::route_url( $url, $key );
	};
	$got = \Sabri\UnifiedShell\SecurityNavigation::augment( $base );
	$assert( isset( $got['account_safety'] ) && ! isset( $got['security_guidance'] ), 'Only verified File02 rewrite should appear.' );
	$GLOBALS['test_options']['sauth_rewrite_version'] = 'stale';
	$assert( $base === \Sabri\UnifiedShell\SecurityNavigation::augment( $base ), 'Stale File02 rewrite version must fail closed.' );
	$GLOBALS['test_options']['sauth_rewrite_version'] = SAUTH_VERSION;
	$GLOBALS['shortcode_tags']['sabri_security_guidance'] = array( new \Sabri\Platform\Security\Guidance\SecurityGuidance(), 'renderPublic' );
	$GLOBALS['test_shortcodes'][] = 'sabri_security_guidance';
	$GLOBALS['test_posts'] = array( (object) array( 'ID' => 123, 'post_content' => '[sabri_security_guidance]' ) );
	$GLOBALS['test_post_status'][123] = 'publish';
	$GLOBALS['test_permalinks'][123] = 'https://example.test/security-guidance/';
	$got = \Sabri\UnifiedShell\SecurityNavigation::augment( $base );
	$assert( isset( $got['account_safety'], $got['security_guidance'] ), 'Verified File02 and File24 must share only the existing More destination set.' );
	$GLOBALS['shortcode_tags']['sabri_security_guidance'] = array( new \Sabri\Platform\Security\Guidance\SecurityGuidance(), 'notTheOwnerMethod' );
	$got = \Sabri\UnifiedShell\SecurityNavigation::augment( $base );
	$assert( ! isset( $got['security_guidance'] ), 'Replaced shortcode handler must immediately withdraw File24 link.' );
	$GLOBALS['shortcode_tags']['sabri_security_guidance'] = array( new \Sabri\Platform\Security\Guidance\SecurityGuidance(), 'renderPublic' );
	$GLOBALS['test_permalinks'][123] = 'https://example.test/account/safety/';
	\Sabri\UnifiedShell\Integrations::invalidate_cache();
	$got = \Sabri\UnifiedShell\SecurityNavigation::augment( $base );
	$assert( ! isset( $got['account_safety'] ) && ! isset( $got['security_guidance'] ), 'Route collision between owners must hide both new claims.' );
	$GLOBALS['test_permalinks'][123] = 'https://example.test/security-guidance/';
	\Sabri\UnifiedShell\Integrations::invalidate_cache();
	$collision = $base;
	$collision['support'] = array( 'url' => 'https://example.test/security-guidance/', 'key' => 'support' );
	$got = \Sabri\UnifiedShell\SecurityNavigation::augment( $collision );
	$assert( ! isset( $got['security_guidance'] ), 'Never silently reuse an existing canonical destination.' );
	echo "V12.1 File02/File24 native public security navigation PASS\n";
}
