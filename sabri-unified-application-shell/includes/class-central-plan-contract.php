<?php
/**
 * Central-plan v4 contract registry and File 25 visual-boundary adapter.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Publishes File 20 contracts without taking ownership of native domains. */
final class CentralPlanContract {
	const CONTRACT_VERSION  = '1.0.0';
	const FILE25_MIN_VERSION = '1.0.0';

	/** Register the v4 contract surface. */
	public static function register() {
		add_filter( 'sabri_shell_contract_registry', array( __CLASS__, 'filter_contract_registry' ), 20 );
		add_filter( 'sabri_shell_layout_contexts', array( __CLASS__, 'filter_layout_contexts' ), 20 );
		add_filter( 'sabri_shell_operational_states', array( __CLASS__, 'filter_operational_states' ), 20 );
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ), 99 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 100 );
		add_action( 'admin_init', array( __CLASS__, 'retire_appearance_tab' ), 30 );
		add_action( 'admin_head', array( __CLASS__, 'hide_appearance_tab' ) );
		add_filter( 'pre_update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'preserve_file25_owned_settings' ), 20, 2 );
	}

	/** Enqueue central-plan structural corrections after the base shell. */
	public static function enqueue() {
		if ( ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) ) {
			return;
		}
		wp_enqueue_style( 'sabri-shell-central-plan-v4', SABRI_SHELL_URL . 'assets/css/shell-central-plan-v4.css', array( 'sabri-shell' ), SABRI_SHELL_VERSION );
		$tokens = self::visual_tokens();
		$css = 'body.sabri-shell-enabled{'
			. '--sabri-shell-primary:' . $tokens['primary_color'] . ';'
			. '--sabri-shell-bg:' . $tokens['background'] . ';'
			. '--sabri-shell-surface:' . $tokens['surface'] . ';'
			. '--sabri-shell-surface-strong:' . $tokens['surface_strong'] . ';'
			. '--sabri-shell-text:' . $tokens['text'] . ';'
			. '--sabri-shell-muted:' . $tokens['muted'] . ';'
			. '--sabri-shell-border:' . $tokens['border'] . ';'
			. '--sabri-shell-focus:' . $tokens['focus'] . ';'
			. '--sabri-shell-radius:' . absint( $tokens['radius'] ) . 'px;'
			. '--sabri-shell-font-scale:' . self::safe_float( $tokens['font_scale'], 1.0 ) . ';'
			. '--sabri-shell-shadow:' . $tokens['shadow'] . ';}';
		wp_add_inline_style( 'sabri-shell-central-plan-v4', $css );
	}

	/** Add truthful provider markers and remove legacy File 20 appearance markers. */
	public static function body_classes( $classes ) {
		$classes = is_array( $classes ) ? $classes : array();
		$classes = array_values( array_filter( $classes, static function ( $class_name ) {
			return 0 !== strpos( (string) $class_name, 'sabri-shell-theme-' )
				&& 0 !== strpos( (string) $class_name, 'sabri-shell-density-' );
		} ) );
		if ( ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) ) {
			return array_values( array_unique( $classes ) );
		}
		$contract  = self::visual_contract();
		$classes[] = 'sabri-shell-central-plan-v4';
		$classes[] = 'sabri-shell-visual-' . sanitize_html_class( $contract['status'] );
		$classes[] = 'sabri-shell-density-' . sanitize_html_class( $contract['tokens']['density'] );
		return array_values( array_unique( $classes ) );
	}

	/** Merge File 20's canonical contracts into an existing registry. */
	public static function filter_contract_registry( $registry ) {
		return array_replace( is_array( $registry ) ? $registry : array(), self::canonical_contracts() );
	}

	/** Publish the exact four-mode context constitution. */
	public static function filter_layout_contexts( $contexts ) {
		return array_replace( is_array( $contexts ) ? $contexts : array(), array(
			'home'                 => array( 'mode' => Layout::THREE, 'owner' => 'file-21' ),
			'worldwide_clinic'     => array( 'mode' => Layout::THREE, 'owner' => 'file-08' ),
			'single_clinic_doctor' => array( 'mode' => Layout::THREE, 'owner' => 'files-03-08' ),
			'ordinary_public'      => array( 'mode' => Layout::TWO, 'owner' => 'native' ),
			'profile_timeline'     => array( 'mode' => Layout::TWO, 'owner' => 'files-03-25' ),
			'private_application'  => array( 'mode' => Layout::TWO, 'owner' => 'native' ),
			'authentication_task'  => array( 'mode' => Layout::MINIMAL, 'owner' => 'files-00-02-09' ),
			'system_recovery'      => array( 'mode' => Layout::MINIMAL, 'owner' => 'file-20' ),
			'video_live_reels'     => array( 'mode' => Layout::IMMERSIVE, 'owner' => 'files-10-11' ),
			'pdf_reader'           => array( 'mode' => Layout::IMMERSIVE, 'owner' => 'file-12' ),
		) );
	}

	/** Publish the operational-state vocabulary. */
	public static function filter_operational_states( $states ) {
		return array_replace( is_array( $states ) ? $states : array(), array(
			'healthy' => 'normal', 'degraded' => 'scoped-unavailable', 'safe-mode' => 'theme-native-fallback',
			'emergency-disabled' => 'shell-off-recovery-available', 'repair-required' => 'writes-limited-dry-run-repair',
			'unknown' => 'no-green-claim', 'incompatible' => 'affected-action-disabled', 'maintenance' => 'bounded-maintenance',
		) );
	}

	/** Resolve the File 25 visual contract or a continuity-only fallback. */
	public static function visual_contract() {
		$fallback = array( 'owner' => 'file-20-continuity-fallback', 'version' => self::CONTRACT_VERSION, 'status' => 'fallback', 'tokens' => self::fallback_visual_tokens() );
		$provided = apply_filters( 'sabri_shell_file25_visual_contract', array() );
		if ( ! is_array( $provided ) || ! self::valid_file25_contract( $provided ) ) {
			return $fallback;
		}
		return array(
			'owner' => sanitize_key( $provided['owner'] ), 'version' => sanitize_text_field( $provided['version'] ), 'status' => 'file25',
			'tokens' => self::sanitize_visual_tokens( $provided['tokens'], $fallback['tokens'] ),
		);
	}

	/** Return validated visual tokens. */
	public static function visual_tokens() {
		$contract = self::visual_contract();
		return $contract['tokens'];
	}

	/** Redirect the retired File 20 Appearance editor to Overview. */
	public static function retire_appearance_tab() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		if ( 'sabri-shell' === $page && 'appearance' === $tab ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'sabri-shell', 'tab' => 'overview', 'sabri_shell_visual_notice' => 'file25-owner' ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	/** Hide the retired tab only on the File 20 administration page. */
	public static function hide_appearance_tab() {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( is_admin() && 'sabri-shell' === $page ) {
			echo '<style id="sabri-shell-file25-admin-boundary">a.nav-tab[href*="tab=appearance"]{display:none!important}</style>';
		}
	}

	/** Preserve legacy appearance values against further File 20 writes. */
	public static function preserve_file25_owned_settings( $new_value, $old_value ) {
		if ( ! is_array( $new_value ) ) {
			return $new_value;
		}
		$old_value = is_array( $old_value ) ? $old_value : array();
		if ( isset( $old_value['appearance'] ) ) {
			$new_value['appearance'] = $old_value['appearance'];
		} else {
			unset( $new_value['appearance'] );
		}
		$new_value['visual_owner'] = 'file-25';
		return $new_value;
	}

	/** Canonical File 00-26 responsibility registry. */
	public static function canonical_contracts() {
		$rows = array(
			'00' => array( 'Sabri Membership Core', 'identity-authorization-guardian-entitlement', 'consume-current-claims', 'hard', 'fail-closed' ),
			'01-A' => array( 'Definitive Master Plan v3.0', 'governing-constitution', 'governed-only', 'governance', 'not-runtime' ),
			'01-B' => array( 'Sabri Platform Foundation', 'bootstrap-registry-search-federation', 'consume-registry-and-mount-search', 'required', 'bounded-last-known-or-unavailable' ),
			'02' => array( 'Authentication and Accounts', 'login-oauth-recovery-account-completion', 'minimal-task-layout', 'required', 'route-unavailable' ),
			'03' => array( 'Profiles and Doctors', 'public-profile-data', 'outer-shell-only', 'required', 'profile-unavailable' ),
			'04' => array( 'Legacy Publishing Adapter', 'migration-compatibility', 'suppress-writes-after-cutover', 'legacy', 'redirect-or-hidden' ),
			'05' => array( 'Learn Sabri Classical Homeopathy', 'learning-lessons-progress', 'two-column-route-and-slots', 'optional', 'hidden-or-unavailable' ),
			'06' => array( 'Homeopathy Encyclopedia', 'knowledge-entries-relations', 'two-column-route-search-entry', 'optional', 'hidden-or-unavailable' ),
			'07' => array( 'Doctors Directory and Discovery', 'verified-directory-projection', 'two-column-directory', 'optional', 'hidden-or-unavailable' ),
			'08' => array( 'Worldwide Clinic and Appointments', 'clinic-appointment-truth', 'three-column-context-and-actions', 'optional', 'hidden-or-unavailable' ),
			'09' => array( 'Global Doctor Onboarding and Verification', 'doctor-evidence-review', 'minimal-private-task-route', 'optional', 'restricted-or-unavailable' ),
			'10' => array( 'Video Wall and Live Broadcasting', 'recorded-live-media', 'immersive-route-and-exit', 'optional', 'hidden-or-unavailable' ),
			'11' => array( 'Reels and Short Video Discovery', 'reels-discovery', 'immersive-route-and-exit', 'optional', 'hidden-or-unavailable' ),
			'12' => array( 'PDF Library and Digital Reading', 'pdf-metadata-access-reader', 'library-two-column-reader-immersive', 'optional', 'hidden-or-unavailable' ),
			'13' => array( 'Sabri Welcome Intro Animation', 'first-visit-intro-preferences', 'eligibility-and-suppression-context', 'optional', 'intro-suppressed' ),
			'14' => array( 'Global Clinic USP and Conversion Integration', 'approved-clinic-cta', 'slots-only', 'optional', 'cta-hidden' ),
			'15' => array( 'Radar and Trend Intelligence', 'research-and-approved-trends', 'navigation-search-provider-state', 'optional', 'hidden-or-unavailable' ),
			'16' => array( 'Sabri Classical Homeopathy AI', 'source-linked-educational-ai', 'route-entitlement-placement', 'optional', 'hidden-or-unavailable' ),
			'17' => array( 'Communication Network', 'network-messages-calls', 'separate-routes-no-message-data', 'optional', 'hidden-or-unavailable' ),
			'18' => array( 'Marketplace', 'listing-seller-deal-truth', 'route-and-action-placement', 'optional', 'hidden-or-unavailable' ),
			'19' => array( 'Unified Notifications and Alerts', 'notification-entity-delivery', 'exactly-one-bell', 'optional', 'bell-hidden-no-local-store' ),
			'20' => array( 'Unified Application Shell', 'header-navigation-layout-routes-recovery', 'canonical-owner', 'self', 'theme-native-fallback' ),
			'21' => array( 'Complete Home and News Feed', 'posts-news-feed-newsroom', 'five-exact-slots-fallback-suppressed', 'core-provider', 'section-unavailable' ),
			'22' => array( 'Universal Post Composer', 'create-edit-orchestration', 'current-user-create-contract', 'required-for-create', 'create-hidden' ),
			'23' => array( 'Doctor and Founder Publishing Dashboard', 'private-publishing-operations', 'private-route-mount-only', 'optional', 'hidden-or-unavailable' ),
			'24' => array( 'Security Privacy Compliance and Resilience Center', 'assurance-risk-incidents', 'sanitized-events-native-controls-remain', 'assurance', 'unknown' ),
			'25' => array( 'Sabri Unified Global Visual Experience and Design System', 'design-tokens-profiles-timelines-visual-qa', 'consume-visual-contract-no-second-shell', 'visual-provider', 'continuity-fallback' ),
			'26' => array( 'Search Discovery and Ranking', 'federated-search-discovery-ranking-explanations-index-contracts', 'validated-header-search-mount-only', 'shared-capability', 'search-hidden-no-native-fallback' ),
		);
		$output = array();
		foreach ( $rows as $file => $row ) {
			$output[ $file ] = array( 'owner' => $row[0], 'native_scope' => $row[1], 'file20_boundary' => $row[2], 'criticality' => $row[3], 'failure_behavior' => $row[4], 'contract_version' => self::CONTRACT_VERSION, 'cache_policy' => 'owner-aware-bounded' );
		}
		return $output;
	}

	/** Validate a File 25 contract. */
	private static function valid_file25_contract( array $contract ) {
		if ( empty( $contract['owner'] ) || empty( $contract['version'] ) || empty( $contract['tokens'] ) || ! is_array( $contract['tokens'] ) ) {
			return false;
		}
		$owner = sanitize_key( $contract['owner'] );
		if ( ! in_array( $owner, array( 'file-25', 'sabri-public-experience', 'sabri-unified-global-visual-experience' ), true ) ) {
			return false;
		}
		$version = sanitize_text_field( $contract['version'] );
		return 1 === preg_match( '/^\d+\.\d+\.\d+$/', $version ) && version_compare( $version, self::FILE25_MIN_VERSION, '>=' );
	}

	/** Sanitize visual tokens against continuity fallbacks. */
	private static function sanitize_visual_tokens( array $tokens, array $fallback ) {
		$output = $fallback;
		foreach ( array( 'primary_color', 'background', 'surface', 'surface_strong', 'text', 'muted', 'border', 'focus' ) as $key ) {
			if ( isset( $tokens[ $key ] ) && preg_match( '/^#[0-9a-fA-F]{6}$/', (string) $tokens[ $key ] ) ) {
				$output[ $key ] = strtolower( (string) $tokens[ $key ] );
			}
		}
		if ( isset( $tokens['radius'] ) ) {
			$output['radius'] = min( 24, max( 0, absint( $tokens['radius'] ) ) );
		}
		if ( isset( $tokens['font_scale'] ) ) {
			$output['font_scale'] = min( 1.4, max( 0.8, self::safe_float( $tokens['font_scale'], 1.0 ) ) );
		}
		if ( isset( $tokens['density'] ) && in_array( $tokens['density'], array( 'comfortable', 'compact' ), true ) ) {
			$output['density'] = $tokens['density'];
		}
		if ( isset( $tokens['shadow'] ) ) {
			$shadow = sanitize_text_field( $tokens['shadow'] );
			if ( preg_match( '/^[0-9a-zA-Z#.,()\s%-]{1,120}$/', $shadow ) ) {
				$output['shadow'] = $shadow;
			}
		}
		return $output;
	}

	/** Continuity-only values; File 25 remains authoritative. */
	private static function fallback_visual_tokens() {
		return array(
			'primary_color' => '#15803d', 'background' => '#f7f7f7', 'surface' => '#ffffff', 'surface_strong' => '#f1f3f5',
			'text' => '#202124', 'muted' => '#5f6368', 'border' => '#d9dde2', 'focus' => '#0b57d0', 'radius' => 8,
			'font_scale' => 1.0, 'density' => 'comfortable', 'shadow' => '0 12px 30px rgba(32,33,36,0.12)',
		);
	}

	/** Safely coerce a numeric value to float. */
	private static function safe_float( $value, $fallback ) {
		return is_numeric( $value ) ? (float) $value : (float) $fallback;
	}
}
