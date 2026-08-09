<?php
/**
 * Latest Founder/central-plan harmonization for File 20.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Implements post-v4.1 governing directives without taking native-domain ownership.
 */
final class FourPlanHarmonization {
	const CONTRACT_VERSION       = '1.0.0';
	const FILE26_MIN_VERSION     = '1.0.0';
	const WELCOME_INTERVAL_DAYS  = 30;
	const WELCOME_USER_META      = 'sabri_shell_welcome_dismissed_at';
	const WELCOME_COOKIE         = 'sabri_shell_welcome_dismissed_at';
	const WELCOME_SESSION_COOKIE = 'sabri_shell_welcome_seen_session';
	const WELCOME_STORAGE_KEY    = 'sabriShellWelcomeDismissedAt';
	const WELCOME_SESSION_KEY    = 'sabriShellWelcomeSeenSession';

	/** @var bool Whether the current request has evaluated welcome eligibility. */
	private static $welcome_prepared = false;

	/** @var bool Whether File 13 may be invoked on this exact request. */
	private static $welcome_invoke = false;
	const MIGRATION_OPTION       = 'sabri_shell_four_plan_migration';

	/** Register current governing integrations. */
	public static function register() {
		add_filter( 'sabri_shell_contract_registry', array( __CLASS__, 'filter_contract_registry' ), 40 );
		add_filter( 'sabri_shell_business_policy', array( __CLASS__, 'enforce_business_policy' ), 999 );
		add_filter( 'sabri_shell_feature_ui_contracts', array( __CLASS__, 'feature_ui_contracts' ), 20 );
		add_filter( 'pre_update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'enforce_settings' ), 50, 2 );
		add_action( 'init', array( __CLASS__, 'migrate_settings' ), 3 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 120 );
		add_action( 'wp', array( __CLASS__, 'prepare_welcome_invocation' ), 98 );
		add_action( 'wp_body_open', array( __CLASS__, 'invoke_welcome_intro' ), 2 );
		add_action( 'wp_ajax_sabri_shell_welcome_dismiss', array( __CLASS__, 'record_welcome_dismissal' ) );
		add_action( 'wp_ajax_nopriv_sabri_shell_welcome_dismiss', array( __CLASS__, 'record_welcome_dismissal' ) );
	}

	/**
	 * Publish File 26 and current cross-feature UI boundaries.
	 *
	 * @param mixed $registry Existing registry.
	 * @return array<string,mixed>
	 */
	public static function filter_contract_registry( $registry ) {
		$registry = is_array( $registry ) ? $registry : array();
		$registry['26'] = array(
			'owner'            => 'Search Discovery and Ranking',
			'native_scope'     => 'federated-search-discovery-ranking-explanations-index-contracts',
			'file20_boundary'  => 'validated-header-search-mount-only',
			'criticality'      => 'shared-capability',
			'failure_behavior' => 'search-control-hidden-no-wordpress-fallback',
			'contract_version' => self::CONTRACT_VERSION,
			'cache_policy'     => 'owner-aware-bounded',
		);
		return $registry;
	}

	/**
	 * Enforce the latest single-free-tier and donor-neutral shell policy.
	 *
	 * File 20 does not own billing/donation state; this contract only prevents
	 * shell presentation from creating paid/donor privileges.
	 *
	 * @param mixed $policy Existing policy.
	 * @return array<string,mixed>
	 */
	public static function enforce_business_policy( $policy ) {
		$policy = is_array( $policy ) ? $policy : array();
		$policy['tier_model']          = 'single-free-tier';
		$policy['premium_privileges']  = false;
		$policy['donor_advantage']     = false;
		$policy['donor_ranking_bias']  = false;
		$policy['donation_optional']   = true;
		return $policy;
	}

	/**
	 * Declare UI-only integration points for newer directives.
	 *
	 * No route is invented here. Native owners must provide a versioned,
	 * authorized, same-origin destination before a surface may mount it.
	 *
	 * @param mixed $contracts Existing contracts.
	 * @return array<string,mixed>
	 */
	public static function feature_ui_contracts( $contracts ) {
		$contracts = is_array( $contracts ) ? $contracts : array();
		return array_replace(
			$contracts,
			array(
				'smail' => array(
					'owner' => 'file-17',
					'file20_role' => 'ui-entry-only',
					'backend_owned' => false,
				),
				'verified_file_transfer' => array(
					'owner' => 'file-17-cf-04',
					'file20_role' => 'ui-entry-only',
					'backend_owned' => false,
					'per_file_limit_bytes' => 1073741824,
				),
				'download_manager' => array(
					'owner' => 'native-owners-cf-04',
					'file20_role' => 'ui-entry-only',
					'backend_owned' => false,
				),
				'notes_documents_workspace' => array(
					'owner' => 'pending-approved-owner',
					'file20_role' => 'no-backend-no-route-until-owner-approved',
					'backend_owned' => false,
				),
				'image_audio_workspace' => array(
					'owner' => 'pending-approved-owner',
					'file20_role' => 'no-backend-no-route-until-owner-approved',
					'backend_owned' => false,
				),
			)
		);
	}

	/**
	 * Resolve and validate File 26's search contract.
	 *
	 * Provider contract shape:
	 * owner, version, url, query_param (optional), method (GET only).
	 *
	 * @return array<string,mixed>|array<int,mixed>
	 */
	public static function file26_search_contract() {
		$provided = apply_filters( 'sabri_shell_file26_search_contract', array() );
		if ( ! is_array( $provided ) ) {
			return array();
		}
		$owner = isset( $provided['owner'] ) ? sanitize_key( $provided['owner'] ) : '';
		if ( ! in_array( $owner, array( 'file-26', 'file-26-search-discovery-ranking', 'search-discovery-ranking' ), true ) ) {
			return array();
		}
		$version = isset( $provided['version'] ) ? sanitize_text_field( $provided['version'] ) : '';
		if ( 1 !== preg_match( '/^\d+\.\d+\.\d+$/', $version ) || version_compare( $version, self::FILE26_MIN_VERSION, '<' ) ) {
			return array();
		}
		$method = isset( $provided['method'] ) ? strtoupper( sanitize_text_field( $provided['method'] ) ) : 'GET';
		if ( 'GET' !== $method ) {
			return array();
		}
		$url = isset( $provided['url'] ) ? self::same_origin_url( $provided['url'] ) : '';
		if ( '' === $url ) {
			return array();
		}
		$query_param = isset( $provided['query_param'] ) ? sanitize_key( $provided['query_param'] ) : 'q';
		if ( '' === $query_param ) {
			$query_param = 'q';
		}
		return array(
			'owner'       => $owner,
			'version'     => $version,
			'url'         => $url,
			'query_param' => $query_param,
			'method'      => 'GET',
		);
	}

	/** Render the header search only through verified File 26. */
	public static function render_search() {
		$contract = self::file26_search_contract();
		if ( empty( $contract ) ) {
			return;
		}
		$query = isset( $_GET[ $contract['query_param'] ] ) ? sanitize_text_field( wp_unslash( $_GET[ $contract['query_param'] ] ) ) : '';
		echo '<form class="sabri-shell-search" role="search" method="get" action="' . esc_url( $contract['url'] ) . '" data-sabri-search-owner="file-26" data-sabri-search-version="' . esc_attr( $contract['version'] ) . '">';
		echo '<label class="screen-reader-text" for="sabri-shell-search-field">' . esc_html__( 'Search', 'sabri-unified-application-shell' ) . '</label>';
		echo '<input id="sabri-shell-search-field" type="search" name="' . esc_attr( $contract['query_param'] ) . '" value="' . esc_attr( $query ) . '" placeholder="' . esc_attr__( 'Search', 'sabri-unified-application-shell' ) . '">';
		echo '<button type="submit" aria-label="' . esc_attr__( 'Submit search', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&#8981;</span></button>';
		echo '</form>';
	}

	/** Enqueue latest structural guardrails and welcome-frequency client bridge. */
	public static function enqueue() {
		if ( Layout::MINIMAL === Layout::current_mode() ) {
			return;
		}
		wp_enqueue_style(
			'sabri-shell-four-plan-harmonization',
			SABRI_SHELL_URL . 'assets/css/four-plan-harmonization.css',
			array( 'sabri-shell-central-plan-v4' ),
			SABRI_SHELL_VERSION
		);
		wp_enqueue_script(
			'sabri-shell-four-plan-harmonization',
			SABRI_SHELL_URL . 'assets/js/four-plan-harmonization.js',
			array(),
			SABRI_SHELL_VERSION,
			true
		);
		wp_localize_script(
			'sabri-shell-four-plan-harmonization',
			'SabriShellFourPlan',
			array(
				'welcome' => array(
					'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
					'action'          => 'sabri_shell_welcome_dismiss',
					'nonce'           => wp_create_nonce( 'sabri_shell_welcome_dismiss' ),
					'storageKey'      => self::WELCOME_STORAGE_KEY,
					'sessionKey'      => self::WELCOME_SESSION_KEY,
					'intervalSeconds' => self::WELCOME_INTERVAL_DAYS * DAY_IN_SECONDS,
				),
			)
		);
	}

	/**
	 * Prepare welcome before template output so a session cookie can be written
	 * without a headers-sent race. Seeing the intro marks only this session;
	 * Skip/Close/Continue starts the separate 30-day suppression interval.
	 */
	public static function prepare_welcome_invocation() {
		if ( self::$welcome_prepared ) {
			return;
		}
		self::$welcome_prepared = true;
		self::$welcome_invoke   = false;

		if ( ! self::welcome_eligible() || ! has_action( 'sabri_shell_welcome_intro_invoke' ) ) {
			return;
		}

		self::$welcome_invoke = true;
		self::mark_welcome_seen_for_session();
	}

	/**
	 * Invoke File 13 only when this exact request was prepared as eligible.
	 * File 13 remains visual/content owner; File 20 fabricates no substitute.
	 */
	public static function invoke_welcome_intro() {
		if ( ! self::$welcome_invoke || ! has_action( 'sabri_shell_welcome_intro_invoke' ) ) {
			return;
		}
		$context = array(
			'contract_version' => self::CONTRACT_VERSION,
			'owner'            => 'file-20-frequency-control',
			'interval_days'    => self::WELCOME_INTERVAL_DAYS,
			'dismiss_action'   => 'sabri_shell_welcome_dismiss',
			'dismiss_nonce'    => wp_create_nonce( 'sabri_shell_welcome_dismiss' ),
			'storage_key'      => self::WELCOME_STORAGE_KEY,
			'session_key'      => self::WELCOME_SESSION_KEY,
		);
		do_action( 'sabri_shell_welcome_intro_invoke', $context );
	}

	/** Whether the current request is eligible for the welcome invocation. */
	public static function welcome_eligible() {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return false;
		}
		if ( Layout::MINIMAL === Layout::current_mode() || Layout::IMMERSIVE === Layout::current_mode() ) {
			return false;
		}
		$eligible = true;
		if ( function_exists( 'is_feed' ) && is_feed() ) {
			$eligible = false;
		}
		if ( function_exists( 'is_404' ) && is_404() ) {
			$eligible = false;
		}
		$eligible = (bool) apply_filters( 'sabri_shell_welcome_request_eligible', $eligible );
		if ( ! $eligible ) {
			return false;
		}
		if ( self::welcome_seen_this_session() ) {
			return false;
		}
		$last = self::welcome_last_dismissed_at();
		return $last <= 0 || ( time() - $last ) >= ( self::WELCOME_INTERVAL_DAYS * DAY_IN_SECONDS );
	}

	/** Record Skip/Close/Continue for logged-in users or guests. */
	public static function record_welcome_dismissal() {
		check_ajax_referer( 'sabri_shell_welcome_dismiss', 'nonce' );
		$timestamp = time();
		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), self::WELCOME_USER_META, $timestamp );
		} else {
			$secure = is_ssl();
			setcookie(
				self::WELCOME_COOKIE,
				(string) $timestamp,
				array(
					'expires'  => $timestamp + ( self::WELCOME_INTERVAL_DAYS * DAY_IN_SECONDS ),
					'path'     => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
					'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
					'secure'   => $secure,
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		}
		wp_send_json_success( array( 'dismissed_at' => $timestamp ) );
	}

	/** Enforce post-directive settings invariants on every write. */
	public static function enforce_settings( $new_value, $old_value ) {
		if ( ! is_array( $new_value ) ) {
			return $new_value;
		}
		if ( ! isset( $new_value['mobile'] ) || ! is_array( $new_value['mobile'] ) ) {
			$new_value['mobile'] = array();
		}
		$new_value['mobile']['bottom_nav'] = false;
		$new_value['visual_owner'] = 'file-25';
		$old_value = is_array( $old_value ) ? $old_value : array();
		if ( isset( $old_value['appearance'] ) ) {
			$new_value['appearance'] = $old_value['appearance'];
		} else {
			unset( $new_value['appearance'] );
		}
		return $new_value;
	}

	/** One-time idempotent migration from superseded shell settings. */
	public static function migrate_settings() {
		if ( (string) get_option( self::MIGRATION_OPTION, '' ) === SABRI_SHELL_VERSION ) {
			return;
		}
		$current = get_option( Defaults::OPTION_NAME, array() );
		$current = is_array( $current ) ? $current : array();
		if ( ! isset( $current['mobile'] ) || ! is_array( $current['mobile'] ) ) {
			$current['mobile'] = array();
		}
		$current['mobile']['bottom_nav'] = false;
		$current['visual_owner'] = 'file-25';
		$current = Settings::enforce_owned_invariants( $current );
		update_option( Defaults::OPTION_NAME, $current, false );
		update_option( self::MIGRATION_OPTION, SABRI_SHELL_VERSION, false );
		Navigation::invalidate_cache();
		Integrations::invalidate_cache();
	}

	/** Whether the intro was already invoked in this browser session. */
	private static function welcome_seen_this_session() {
		return isset( $_COOKIE[ self::WELCOME_SESSION_COOKIE ] )
			&& '1' === sanitize_text_field( wp_unslash( $_COOKIE[ self::WELCOME_SESSION_COOKIE ] ) );
	}

	/** Mark only this browser session as having seen the intro. */
	private static function mark_welcome_seen_for_session() {
		$_COOKIE[ self::WELCOME_SESSION_COOKIE ] = '1';
		if ( headers_sent() ) {
			return;
		}
		setcookie(
			self::WELCOME_SESSION_COOKIE,
			'1',
			array(
				'expires'  => 0,
				'path'     => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	/** Last welcome dismissal timestamp from the authoritative applicable state. */
	private static function welcome_last_dismissed_at() {
		if ( is_user_logged_in() ) {
			return absint( get_user_meta( get_current_user_id(), self::WELCOME_USER_META, true ) );
		}
		if ( isset( $_COOKIE[ self::WELCOME_COOKIE ] ) ) {
			return absint( wp_unslash( $_COOKIE[ self::WELCOME_COOKIE ] ) );
		}
		return 0;
	}

	/** Validate an absolute same-origin HTTP(S) URL. */
	private static function same_origin_url( $candidate ) {
		if ( ! is_string( $candidate ) || '' === trim( $candidate ) ) {
			return '';
		}
		$url = esc_url_raw( $candidate, array( 'http', 'https' ) );
		if ( '' === $url ) {
			return '';
		}
		$home   = wp_parse_url( home_url( '/' ) );
		$target = wp_parse_url( $url );
		if ( ! is_array( $home ) || ! is_array( $target ) ) {
			return '';
		}
		$home_scheme   = strtolower( isset( $home['scheme'] ) ? (string) $home['scheme'] : '' );
		$target_scheme = strtolower( isset( $target['scheme'] ) ? (string) $target['scheme'] : '' );
		$home_host     = strtolower( rtrim( isset( $home['host'] ) ? (string) $home['host'] : '', '.' ) );
		$target_host   = strtolower( rtrim( isset( $target['host'] ) ? (string) $target['host'] : '', '.' ) );
		$home_port     = isset( $home['port'] ) ? absint( $home['port'] ) : ( 'https' === $home_scheme ? 443 : 80 );
		$target_port   = isset( $target['port'] ) ? absint( $target['port'] ) : ( 'https' === $target_scheme ? 443 : 80 );
		if ( ! in_array( $target_scheme, array( 'http', 'https' ), true ) || $home_scheme !== $target_scheme || '' === $home_host || $home_host !== $target_host || $home_port !== $target_port ) {
			return '';
		}
		return $url;
	}
}
