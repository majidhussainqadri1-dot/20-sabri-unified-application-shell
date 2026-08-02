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

$GLOBALS['test_users'][7] = new WP_User( 7, array( 'sabri_doctor_verified' ), 'Verified Doctor' );
$GLOBALS['test_user_meta'][7]['_smc_doctor_verified'] = 1;
$GLOBALS['test_membership_assertions'][7] = array(
	'user_id'               => 7,
	'contract_version'      => '1.1.2',
	'account_class'         => 'member',
	'membership_type'       => 'doctor',
	'approved'              => true,
	'eligible'              => true,
	'identity_evidence_current' => true,
	'two_factor_ready'      => true,
	'session_two_factor'    => true,
	'sensitive_action_ready'=> true,
	'professional_verified' => true,
	'can_publish'           => true,
	'can_practice'          => true,
	'public_profile_allowed'=> true,
	'suspended'             => false,
);
$GLOBALS['test_directory_eligible'][7] = true;
$GLOBALS['test_approved_fields'][7] = array(
	'display_name' => 'Verified Doctor',
	'country'      => 'Pakistan',
	'specialty'    => 'Classical Homeopathy',
	'phone'        => '+923001234567',
	'whatsapp'     => '+923001234567',
);
$GLOBALS['test_public_contact'][7] = false;
$assert( Integrations::is_verified_doctor( 7 ), 'File 03 directory eligibility governs public verified-doctor discovery.' );
$assert( Integrations::can_publish( 7 ), 'File 00 current-session publishing assertion authorizes a verified doctor.' );
$private_contact = Integrations::doctor_public_data( 7 );
$assert( '' === $private_contact['phone'] && '' === $private_contact['whatsapp'], 'Doctor phone and WhatsApp remain hidden without explicit File 03 public-contact consent.' );
$GLOBALS['test_public_contact'][7] = true;
$public_contact = Integrations::doctor_public_data( 7 );
$assert( '+923001234567' === $public_contact['phone'] && '+923001234567' === $public_contact['whatsapp'], 'Approved contact fields render only after explicit File 03 public-contact consent.' );
$GLOBALS['test_filter_overrides']['sabri_shell_doctor_public_data'] = static function ( $data ) {
	$data['email'] = 'private@example.test';
	$data['country'] = '<b>Pakistan</b>';
	return $data;
};
$filtered_public = Integrations::doctor_public_data( 7 );
$assert( ! array_key_exists( 'email', $filtered_public ), 'Public doctor projection filters cannot introduce undeclared private fields.' );
$assert( 'Pakistan' === $filtered_public['country'], 'Public doctor projection filter values are sanitized.' );
unset( $GLOBALS['test_filter_overrides']['sabri_shell_doctor_public_data'] );

$GLOBALS['test_users'][8] = new WP_User( 8, array( 'sabri_doctor_verified' ), 'Suspended Doctor' );
$GLOBALS['test_user_meta'][8]['_smc_doctor_verified'] = 1;
$GLOBALS['test_membership_assertions'][8] = array(
	'user_id'               => 8,
	'contract_version'      => '1.1.2',
	'account_class'         => 'member',
	'membership_type'       => 'doctor',
	'approved'              => false,
	'eligible'              => false,
	'identity_evidence_current' => false,
	'two_factor_ready'      => false,
	'session_two_factor'    => false,
	'sensitive_action_ready'=> false,
	'professional_verified' => true,
	'can_publish'           => false,
	'can_practice'          => false,
	'public_profile_allowed'=> false,
	'suspended'             => true,
);
$GLOBALS['test_directory_eligible'][8] = false;
$assert( ! Integrations::is_verified_doctor( 8 ), 'A stale verified meta flag cannot expose a suspended doctor.' );
$assert( ! Integrations::can_publish( 8 ), 'A stale trusted role or capability cannot bypass File 00 suspension.' );
$assert( array() === Integrations::doctor_public_data( 8 ), 'A suspended/private doctor produces no public profile projection.' );

$GLOBALS['test_users'][9] = new WP_User( 9, array( 'founder' ), 'Founder' );
$GLOBALS['test_membership_assertions'][9] = array(
	'user_id'               => 9,
	'contract_version'   => '1.1.2',
	'account_class'      => 'founder',
	'approved'           => true,
	'eligible'           => true,
	'identity_evidence_current' => true,
	'two_factor_ready'   => true,
	'session_two_factor' => true,
	'sensitive_action_ready' => true,
	'can_publish'        => true,
	'suspended'          => false,
);
$assert( Integrations::can_publish( 9 ), 'Canonical Founder publishing remains authorized only through current File 00 assertions.' );

$GLOBALS['test_users'][10] = new WP_User( 10, array( 'administrator' ), 'Administrator' );
$GLOBALS['test_user_caps'][10]['manage_options'] = true;
$GLOBALS['test_membership_assertions'][10] = array(
	'user_id'               => 10,
	'contract_version'   => '1.1.2',
	'account_class'      => 'administrator',
	'institutional_account' => true,
	'approved'           => true,
	'eligible'           => true,
	'identity_evidence_current' => true,
	'two_factor_ready'   => true,
	'session_two_factor' => true,
	'sensitive_action_ready' => true,
	'can_publish'        => false,
	'suspended'          => false,
);
$assert( Integrations::can_publish( 10 ), 'A valid institutional Administrator may reach the composer without bypassing File 00 status and 2FA.' );

$GLOBALS['test_users'][13] = new WP_User( 13, array( 'founder' ), 'Expired Founder' );
$GLOBALS['test_membership_assertions'][13] = $GLOBALS['test_membership_assertions'][9];
$GLOBALS['test_membership_assertions'][13]['user_id'] = 13;
$GLOBALS['test_membership_assertions'][13]['status'] = 'expired';
$assert( ! Integrations::can_publish( 13 ), 'Terminal File 00 status blocks publishing even when stale allow booleans remain true.' );
$GLOBALS['test_users'][14] = new WP_User( 14, array( 'founder' ), 'Stale Session Founder' );
$GLOBALS['test_membership_assertions'][14] = $GLOBALS['test_membership_assertions'][9];
$GLOBALS['test_membership_assertions'][14]['user_id'] = 14;
$GLOBALS['test_membership_assertions'][14]['sensitive_action_ready'] = false;
$assert( ! Integrations::can_publish( 14 ), 'Missing File 00 sensitive-action assurance blocks composer access.' );

$GLOBALS['test_users'][11] = new WP_User( 11, array( 'sabri_doctor_verified' ), 'Private Contact Doctor' );
$GLOBALS['test_membership_assertions'][11] = $GLOBALS['test_membership_assertions'][7];
$GLOBALS['test_membership_assertions'][11]['user_id'] = 11;
$GLOBALS['test_directory_eligible'][11] = true;
$GLOBALS['test_approved_fields'][11] = array( 'display_name' => 'Private Contact Doctor' );
$GLOBALS['test_profiles'][11] = array( 'phone' => '+923009999999', 'whatsapp' => '+923009999999', 'country' => 'Pakistan' );
$GLOBALS['test_public_contact'][11] = false;
$raw_profile_contact = Integrations::doctor_public_data( 11 );
$assert( '' === $raw_profile_contact['phone'] && '' === $raw_profile_contact['whatsapp'], 'Raw Membership profile contact never becomes public data without the File 03 consent contract.' );


$GLOBALS['test_users'][12] = new WP_User( 12, array( 'sabri_doctor_verified' ), 'Stale Legacy Doctor' );
$GLOBALS['test_user_meta'][12]['_smc_doctor_verified'] = 1;
$GLOBALS['test_user_meta'][12]['_smc_trusted_publisher'] = 1;
$GLOBALS['test_user_caps'][12]['edit_posts'] = true;
$GLOBALS['test_membership_assertions'][12] = array();
$GLOBALS['test_directory_eligible'][12] = false;
$assert( ! Integrations::is_verified_doctor( 12 ), 'Legacy verified metadata cannot substitute for File 00 and File 03 authority.' );
$assert( ! Integrations::is_trusted_publisher( 12 ), 'Legacy trusted-publisher metadata cannot substitute for current File 00 assertions.' );
$assert( ! Integrations::can_publish( 12 ), 'Composer access fails closed when the identity authority is unavailable.' );
$assert( array() === Integrations::doctor_public_data( 12 ), 'Public doctor data fails closed when both approved File 03 projection and valid File 00 assertions are unavailable.' );

if ( $failures ) {
	echo "\n" . count( $failures ) . " test(s) failed.\n";
	exit( 1 );
}

echo "\nAll corrective contract tests passed.\n";
