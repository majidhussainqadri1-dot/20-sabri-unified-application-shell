<?php
require __DIR__ . '/bootstrap.php';

use Sabri\UnifiedShell\Defaults;
use Sabri\UnifiedShell\HomeFeed;
use Sabri\UnifiedShell\Integrations;
use Sabri\UnifiedShell\Layout;
use Sabri\UnifiedShell\Navigation;
use Sabri\UnifiedShell\Settings;

$failures = array();
$assert = static function ( $condition, $message ) use ( &$failures ) {
	if ( ! $condition ) {
		$failures[] = $message;
		echo "FAIL: {$message}\n";
	} else {
		echo "PASS: {$message}\n";
	}
};

$dest = Defaults::destinations();
$settings_snapshot = Settings::get();
$assert( isset( $settings_snapshot['integrations'], $settings_snapshot['navigation'], $settings_snapshot['layout'] ), 'Empty stored settings preserve the associative default schema.' );
$assert( in_array( 'slc_learning_home', $dest['learn']['shortcodes'], true ), 'Learn uses the real File 05 shortcode.' );
$assert( in_array( 'he_encyclopedia_home', $dest['encyclopedia']['shortcodes'], true ), 'Encyclopedia uses the real File 06 shortcode.' );
$assert( in_array( 'sdd_doctors_directory', $dest['doctors']['shortcodes'], true ), 'Doctors uses the real File 07 shortcode.' );
$assert( in_array( 'swc_worldwide_clinic', $dest['clinic']['shortcodes'], true ), 'Clinic uses the real File 08 shortcode.' );
$GLOBALS['test_shortcodes'][] = 'sabri_notification_bell';
Integrations::invalidate_cache();
$assert( ! empty( Integrations::detect()['notifications'] ), 'The real File 19 notification bell contract is detected.' );

$merged = Settings::deep_merge( array( 'roles' => array( 'administrator', 'editor' ) ), array( 'roles' => array( 'sabri_verified_doctor' ) ) );
$assert( array( 'sabri_verified_doctor' ) === $merged['roles'], 'Sequential settings lists replace instead of retaining stale roles.' );
$emptied = Settings::deep_merge( array( 'roles' => array( 'administrator', 'editor' ) ), array( 'roles' => array() ) );
$assert( array() === $emptied['roles'], 'An empty submitted list removes every stale list item.' );

$GLOBALS['test_options']['spf_page_map'] = array( 'home' => 101 );
$GLOBALS['test_post_status'][101] = 'publish';
$GLOBALS['test_permalinks'][101] = 'https://example.test/platform-home/';
Integrations::invalidate_cache();
Navigation::invalidate_cache();
$item = Navigation::resolve_item( 'home', $dest['home'], Defaults::settings()['navigation']['home'] );
$assert( 'https://example.test/platform-home/' === $item['url'] && 'companion_contract' === $item['reason'], 'Companion page-map contract precedes guessed slugs.' );

$GLOBALS['test_options']['spf_page_map'] = array();
Integrations::invalidate_cache();
$news = Navigation::resolve_item( 'news', $dest['news'], Defaults::settings()['navigation']['news'] );
$assert( '' === $news['url'], 'Unresolved News does not silently route to Home.' );

$GLOBALS['test_options']['snp_page_map'] = array();
$assert( '' === Integrations::create_url(), 'Create has no unsafe wp-admin composer fallback.' );
$GLOBALS['test_options']['sa_page_map'] = array( 'login' => 303 );
$GLOBALS['test_post_status'][303] = 'publish';
$GLOBALS['test_permalinks'][303] = 'https://example.test/account-login/';
$assert( 'https://example.test/account-login/?redirect_to=https%3A%2F%2Fexample.test%2Fprivate%2F' === Integrations::auth_url( 'login', 'https://example.test/private/' ), 'Platform login page precedes the WordPress core login fallback.' );
$GLOBALS['test_options']['snp_page_map'] = array( 'publish' => 202 );
$GLOBALS['test_post_status'][202] = 'publish';
$GLOBALS['test_permalinks'][202] = 'https://example.test/create-publication/';
$assert( 'https://example.test/create-publication/' === Integrations::create_url(), 'Create resolves the moderated publication page.' );

$_SERVER['REQUEST_URI'] = '/account-login/';
$assert( Layout::is_excluded_request(), 'Actual account-login route uses minimal layout.' );
$_SERVER['REQUEST_URI'] = '/complete-profile/';
$assert( Layout::is_excluded_request(), 'Actual complete-profile route uses minimal layout.' );
$_SERVER['REQUEST_URI'] = '/encyclopedia/';
$assert( ! Layout::is_excluded_request(), 'Public knowledge routes remain shell eligible.' );

$fallbacks = Layout::content_target_fallbacks();
$assert( ! in_array( '.wp-site-blocks', $fallbacks, true ) && ! in_array( '#page', $fallbacks, true ) && ! in_array( '.site', $fallbacks, true ), 'Theme root wrappers are excluded from content targeting.' );
$assert( HomeFeed::authoritative_feed_present( '[sabri_news_home]' ), 'Authoritative File 04 feed suppresses shell feed insertion.' );

$GLOBALS['test_users'][7] = new WP_User( 7, array( 'sabri_doctor' ), 'Verified Doctor' );
$GLOBALS['test_user_meta'][7]['_smc_doctor_verified'] = 1;
$assert( Integrations::is_verified_doctor( 7 ), 'Membership Core verification metadata is honored.' );

if ( $failures ) {
	echo "\n" . count( $failures ) . " test(s) failed.\n";
	exit( 1 );
}

echo "\nAll corrective contract tests passed.\n";
