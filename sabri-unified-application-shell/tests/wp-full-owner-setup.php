<?php
/**
 * Isolated CI setup with the genuine File00 membership and File25 visual
 * implementations. No production identity or user record is accessed.
 */
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit( 2 ); }
$die = static function ( $error ) { WP_CLI::error( '[V12.3 Full Owner CI] ' . $error ); };
$assert = static function ( $test, $error ) use ( $die ) { if ( ! $test ) { $die( $error ); } };
$founder = get_user_by( 'login', 'ci_founder' );
$assert( $founder instanceof \WP_User, 'Isolated WordPress Founder fixture does not exist.' );
$user_id = (int) $founder->ID;
$assert( class_exists( 'SMC_Contracts' ), 'Actual File00 Membership plugin claims class must be loaded.' );
$claims = \SMC_Contracts::assertions( $user_id );
$assert( is_array( $claims ), 'File00 claims are unavailable.' );
WP_CLI::log( 'File00 Founder claims summary: ' . wp_json_encode( array(
	'class' => $claims['account_class'] ?? 'missing',
	'approved' => $claims['approved'] ?? null,
	'eligible' => $claims['eligible'] ?? null,
	'suspended' => $claims['suspended'] ?? null,
	'status' => $claims['status'] ?? 'missing',
) ) );
$assert( 'founder' === ( $claims['account_class'] ?? '' ), 'File00 canonical founder assertion missing.' );
$assert( ! empty( $claims['approved'] ) && ! empty( $claims['eligible'] ) &&
	empty( $claims['suspended'] ), 'File00 institution is not eligible for welcome approval.' );
wp_set_current_user( $user_id );
$owner = 'Sabri\\UnifiedShell\\WelcomeExperience';
$assert( class_exists( $owner ), 'File20 WelcomeExperience not loaded.' );
$assert( $owner::founder_authorized( $user_id ), 'File20 failed to honor actual File00 Founder claim.' );
$assert( $owner::operator_authorized( $user_id ), 'Native WordPress administrator + File00 claims did not permit admin drafting.' );
$assert( ! $owner::public_eligible(), 'Authenticated Founder must never receive an automatic welcome (F13-CEN-01).' );
$settings = \Sabri\UnifiedShell\Settings::get();
$settings['enabled'] = true;
$settings['emergency_disabled'] = false;
\Sabri\UnifiedShell\Settings::update_programmatically( $settings );
$readback = \Sabri\UnifiedShell\Settings::get();
$assert( ! empty( $readback['enabled'] ) && empty( $readback['emergency_disabled'] ),
	'Canonical File20 settings writer must preserve shell enablement.' );
$config = $owner::defaults();
$config['enabled'] = true;
$config['title'] = 'Welcome to Sabri Social Homeopathy Platform';
$config['message'] = 'A non-blocking welcome to classical homeopathy learning.';
update_option( $owner::OPTION, $config, false );
$verified = $owner::configuration();
$assert( ! empty( $verified['enabled'] ), 'File20 welcome config not persisted.' );
$signature = $owner::approval_signature( $verified );
$assert( is_string( $signature ) && 64 === strlen( $signature ), 'Genuine File25 visual/CSS approval digest unavailable.' );
update_option( $owner::APPROVAL_OPTION, array(
	'signer_id' => $user_id,
	'approved_at' => gmdate( 'c' ),
	'signature' => $signature,
), false );
$assert( $owner::approval_valid( $verified ), 'Real File00 Founder claim + genuine File25 render approval mismatched.' );
$assert( ! $owner::public_eligible(), 'Authenticated Founder cannot see approved automatic intro.' );
wp_set_current_user( 0 );
$_SERVER['REQUEST_URI'] = '/';
$assert( $owner::public_eligible(), 'Fresh anonymous visitor may see approved welcome at canonical home.' );
wp_set_current_user( $user_id );
$ur = $owner::localized_panel_copy( $verified, 'ur_PK' );
$ar = $owner::localized_panel_copy( $verified, 'ar_SA' );
$assert( false !== strpos( $ur['title'], 'صابری' ) && 'چھوڑ دیں' === $ur['skip'], 'Real File20 Urdu variant/labels missing.' );
$assert( false !== strpos( $ar['title'], 'مرحبًا' ) && 'إغلاق' === $ar['close'], 'Real File20 Arabic variant/labels missing.' );
$localized_markup = sabri_visual_experience_render_welcome( array(
	'invoked_by_file_20' => true,
	'title' => $ur['title'],
	'message' => $ur['message'],
	'continue_label' => $ur['continue'],
	'skip_label' => $ur['skip'],
	'close_label' => $ur['close'],
	'continue_url' => home_url( '/' ),
) );
$assert( false !== strpos( $localized_markup, 'چھوڑ دیں' ) && false !== strpos( $localized_markup, 'dir="auto"' ),
	'Genuine File25 bilingual markup not linked to File20 approved localized text.' );
$draft = $verified;
$draft['message_ur'] = 'متن میں تبدیلی';
update_option( $owner::OPTION, $draft, false );
$assert( ! $owner::approval_valid( $owner::configuration() ), 'Changing only Urdu approved copy must revoke prior Founder approval.' );
update_option( $owner::OPTION, $verified, false );
$assert( $owner::approval_valid( $owner::configuration() ), 'Restored byte-equivalent localization recovers exact approval digest.' );


WP_CLI::success( 'Full owner File00+File20+File25 isolated CI activation, claims and explicit fixture approval passed.' );
