<?php
/**
 * Integration detection and authoritative platform contracts.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects companion modules without duplicating their backends.
 */
final class Integrations {
	const SHORTCODE_CACHE_KEYS_OPTION = 'sabri_shell_shortcode_cache_keys';
	const MAX_SHORTCODE_CACHE_KEYS    = 64;
	/**
	 * Request-level detection cache.
	 *
	 * @var array<string,mixed>|null
	 */
	private static $detected = null;

	/** @var array<string,string> Request-level shortcode lookup cache. */
	private static $shortcode_lookup = array();

	/** @var array<string,array<int,int>> Collision evidence keyed by shortcode set. */
	private static $shortcode_collisions = array();

	/**
	 * Detect integration status.
	 *
	 * @return array<string,mixed>
	 */
	public static function detect() {
		if ( is_array( self::$detected ) ) {
			return self::$detected;
		}

		$settings = Settings::get();
		self::$detected = array(
			'notifications'         => shortcode_exists( 'sabri_notifications' ) || shortcode_exists( 'sabri_notification_bell' ) || ( class_exists( 'SUN_Utils' ) && is_callable( array( 'SUN_Utils', 'page_url' ) ) ) || ! empty( $settings['integrations']['urls']['notifications'] ),
			'network'               => shortcode_exists( 'sabri_network' ) || class_exists( 'SN_Activator' ) || (int) get_option( 'sn_network_page_id', 0 ) > 0,
			'messages'              => shortcode_exists( 'sabri_messages' ) || shortcode_exists( 'sabri_network' ) || class_exists( 'SN_Activator' ) || ! empty( $settings['integrations']['urls']['messages'] ),
			'marketplace'           => shortcode_exists( 'sabri_marketplace' ) || class_exists( 'SMP_Activator' ) || (int) get_option( 'smp_marketplace_page_id', 0 ) > 0,
			'appointments'          => shortcode_exists( 'swc_my_appointments' ) || shortcode_exists( 'swc_request_appointment' ) || ! empty( self::page_id( 'appointments' ) ) || ! empty( $settings['integrations']['urls']['appointments'] ),
			'language'              => '' !== self::language_switcher(),
			'clinic_post_types'     => self::existing_post_types( array( 'doctor', 'clinic', 'global_clinic', 'sabri_clinic' ) ),
			'configured_urls'       => $settings['integrations']['urls'],
		);

		return self::$detected;
	}

	/**
	 * Clear request-level integration cache.
	 *
	 * @return void
	 */
	public static function invalidate_cache() {
		self::$detected = null;
		self::$shortcode_lookup = array();
		self::$shortcode_collisions = array();
		$keys = get_option( self::SHORTCODE_CACHE_KEYS_OPTION, array() );
		if ( is_array( $keys ) ) {
			foreach ( array_slice( array_values( array_unique( array_map( 'sanitize_key', $keys ) ) ), 0, self::MAX_SHORTCODE_CACHE_KEYS ) as $key ) {
				if ( 0 === strpos( $key, 'sabri_shell_shortcode_page_' ) ) { delete_transient( $key ); }
			}
		}
		delete_option( self::SHORTCODE_CACHE_KEYS_OPTION );
	}

	/** Track bounded dynamic shortcode transients so cache invalidation/uninstall is complete. */
	private static function register_shortcode_cache_key( $key ) {
		$key = sanitize_key( (string) $key );
		if ( 0 !== strpos( $key, 'sabri_shell_shortcode_page_' ) ) { return; }
		$keys = get_option( self::SHORTCODE_CACHE_KEYS_OPTION, array() );
		$keys = is_array( $keys ) ? array_values( array_unique( array_filter( array_map( 'sanitize_key', $keys ) ) ) ) : array();
		if ( ! in_array( $key, $keys, true ) ) {
			$keys[] = $key;
			update_option( self::SHORTCODE_CACHE_KEYS_OPTION, array_slice( $keys, -self::MAX_SHORTCODE_CACHE_KEYS ), false );
		}
	}

	/**
	 * Authoritative page-map contracts from companion modules.
	 *
	 * @return array<string,array<int,array{0:string,1:string}>>
	 */
	public static function page_contracts() {
		$contracts = array(
			'home'          => array( array( 'spf_page_map', 'home' ) ),
			'news'          => array( array( 'spf_page_map', 'news' ) ),
			'founder'       => array( array( 'spf_page_map', 'founder' ), array( 'spd_page_map', 'founder' ) ),
			'learn'         => array( array( 'spf_page_map', 'learn' ) ),
			'encyclopedia'  => array( array( 'spf_page_map', 'encyclopedia' ) ),
			'doctors'       => array( array( 'spf_page_map', 'doctors' ), array( 'sdd_page_map', 'directory' ), array( 'spd_page_map', 'doctors' ) ),
			'clinic'        => array( array( 'spf_page_map', 'clinic' ), array( 'swc_page_map', 'clinic' ) ),
			'video_wall'    => array( array( 'spf_page_map', 'videos' ), array( 'svw_page_map', 'wall' ) ),
			'reels'         => array( array( 'spf_page_map', 'reels' ), array( 'srl_page_map', 'feed' ) ),
			'pdf_library'   => array( array( 'spf_page_map', 'pdf' ), array( 'spl_page_map', 'library' ) ),
			'radar'         => array( array( 'spf_page_map', 'radar' ), array( 'srf_page_map', 'radar' ) ),
			'ai'            => array( array( 'spf_page_map', 'ai' ), array( 'sai_page_map', 'guide' ) ),
			'appointments'  => array( array( 'swc_page_map', 'patient' ), array( 'swc_page_map', 'request' ), array( 'swc_page_map', 'doctor' ) ),
			'profile'       => array( array( 'spd_page_map', 'profile' ) ),
			'login'         => array( array( 'sa_page_map', 'login' ) ),
			'signup'        => array( array( 'sa_page_map', 'signup' ) ),
			'complete'      => array( array( 'sa_page_map', 'complete' ) ),
			'forgot'        => array( array( 'sa_page_map', 'forgot' ) ),
			'create'        => array( array( 'snp_page_map', 'publish' ) ),
			'network'       => array( array( 'sn_page_map', 'network' ) ),
			'messages'      => array( array( 'sn_page_map', 'messages' ) ),
		);

		return apply_filters( 'sabri_shell_page_contracts', $contracts );
	}

	/**
	 * Resolve a page ID from companion option maps and standalone module options.
	 *
	 * @param string $key Contract key.
	 * @return int
	 */
	public static function page_id( $key ) {
		$key       = sanitize_key( $key );
		$contracts = self::page_contracts();
		if ( ! empty( $contracts[ $key ] ) ) {
			foreach ( $contracts[ $key ] as $contract ) {
				$map = get_option( $contract[0], array() );
				if ( is_array( $map ) && ! empty( $map[ $contract[1] ] ) ) {
					$id = absint( $map[ $contract[1] ] );
					if ( self::published_page_url( $id ) ) {
						return $id;
					}
				}
			}
		}

		$standalone = array(
			'network'     => 'sn_network_page_id',
			'messages'    => 'sn_network_page_id',
			'marketplace' => 'smp_marketplace_page_id',
		);
		if ( isset( $standalone[ $key ] ) ) {
			$id = absint( get_option( $standalone[ $key ], 0 ) );
			if ( self::published_page_url( $id ) ) {
				return $id;
			}
		}

		return 0;
	}

	/**
	 * Resolve a companion-managed page URL.
	 *
	 * @param string $key Contract key.
	 * @return string
	 */
	public static function page_url( $key ) {
		$id = self::page_id( $key );
		return $id ? self::published_page_url( $id ) : '';
	}

	/**
	 * Resolve platform login or signup URL before falling back to WordPress core.
	 *
	 * @param string $kind login|signup|forgot|complete.
	 * @param string $redirect Optional safe redirect.
	 * @return string
	 */
	public static function auth_url( $kind, $redirect = '' ) {
		$kind     = sanitize_key( $kind );
		$redirect = self::same_site_url( $redirect );
		$url      = self::page_url( $kind );
		$shortcodes = array(
			'login'    => array( 'sabri_auth_login', 'sabri_login' ),
			'signup'   => array( 'sabri_auth_signup', 'sabri_register' ),
			'forgot'   => array( 'sabri_auth_forgot_password' ),
			'complete' => array( 'sabri_auth_complete_profile' ),
		);
		if ( ! $url && ! empty( $shortcodes[ $kind ] ) ) {
			$url = self::find_page_by_shortcodes( $shortcodes[ $kind ] );
		}

		if ( 'login' === $kind ) {
			if ( $url && $redirect ) {
				$url = add_query_arg( 'redirect_to', $redirect, $url );
			}
			return $url ? $url : wp_login_url( $redirect );
		}
		if ( 'signup' === $kind ) {
			return $url ? $url : ( get_option( 'users_can_register' ) ? wp_registration_url() : '' );
		}
		if ( 'forgot' === $kind ) {
			return $url ? $url : wp_lostpassword_url( $redirect );
		}

		return $url;
	}

	/**
	 * Resolve public profile URL.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function profile_url( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return '';
		}
		$assertions = self::membership_assertions( $user_id );
		if ( ! self::assertions_allow_public_identity( $assertions ) ) {
			return '';
		}

		foreach ( array(
			array( 'SDD_Helpers', 'profile_url' ),
			array( 'SPD_Helpers', 'profile_url' ),
		) as $provider ) {
			if ( class_exists( $provider[0] ) && is_callable( $provider ) ) {
				$url = call_user_func( $provider, $user_id );
				$url = self::same_site_url( $url );
				if ( $url ) {
					return $url;
				}
			}
		}

		$url  = self::page_url( 'profile' );
		$user = get_userdata( $user_id );
		if ( $url && $user ) {
			return self::same_site_url( add_query_arg( 'user', $user->user_nicename, $url ) );
		}
		$url = self::find_page_by_shortcodes( array( 'sabri_profile', 'sabri_member_profile' ) );
		if ( $url && $user ) {
			return self::same_site_url( add_query_arg( 'user', $user->user_nicename, $url ) );
		}

		/* Never fall back to the generic WordPress author archive: File 03 owns
		 * the public profile projection and may intentionally withhold it. */
		return '';
	}

	/**
	 * Resolve the moderated platform composer URL.
	 *
	 * @return string
	 */
	public static function create_url() {
		$url = self::page_url( 'create' );
		if ( ! $url ) {
			$url = self::find_page_by_shortcodes( array( 'sabri_publish_form' ) );
		}
		$url = apply_filters( 'sabri_shell_create_url', $url );
		return self::same_site_url( $url );
	}

	/**
	 * Resolve destination URLs owned by standalone modules.
	 *
	 * @param string $key Destination key.
	 * @return string
	 */
	public static function destination_url( $key ) {
		$key = sanitize_key( $key );
		if ( 'notifications' === $key && class_exists( 'SUN_Utils' ) && is_callable( array( 'SUN_Utils', 'page_url' ) ) ) {
			$url = \SUN_Utils::page_url();
			if ( $url ) {
				$url = self::same_site_url( $url );
				if ( $url ) { return $url; }
			}
		}
		if ( 'messages' === $key && class_exists( 'SN_Activator' ) && is_callable( array( 'SN_Activator', 'messages_url' ) ) ) {
			$url = \SN_Activator::messages_url();
			if ( $url ) {
				$url = self::same_site_url( $url );
				if ( $url ) { return $url; }
			}
		}
		if ( 'network' === $key && class_exists( 'SN_Activator' ) && is_callable( array( 'SN_Activator', 'network_url' ) ) ) {
			$url = \SN_Activator::network_url();
			if ( $url ) {
				$url = self::same_site_url( $url );
				if ( $url ) { return $url; }
			}
		}
		if ( 'messages' === $key && class_exists( 'SN_Activator' ) && is_callable( array( 'SN_Activator', 'network_url' ) ) ) {
			$url = \SN_Activator::network_url();
			if ( $url ) {
				$url = self::same_site_url( $url );
				if ( $url ) { return $url; }
			}
		}
		if ( 'marketplace' === $key && class_exists( 'SMP_Activator' ) && is_callable( array( 'SMP_Activator', 'marketplace_url' ) ) ) {
			$url = self::same_site_url( \SMP_Activator::marketplace_url() );
			if ( $url ) { return $url; }
		}

		$url = self::page_url( $key );
		if ( $url ) {
			return self::same_site_url( $url );
		}

		$shortcodes = array(
			'notifications' => array( 'sabri_notifications' ),
			'network'       => array( 'sabri_network' ),
			'messages'      => array( 'sabri_network', 'sabri_messages' ),
			'marketplace'   => array( 'sabri_marketplace' ),
			'appointments'  => array( 'swc_my_appointments', 'swc_request_appointment' ),
		);
		return ! empty( $shortcodes[ $key ] ) ? self::same_site_url( self::find_page_by_shortcodes( $shortcodes[ $key ] ) ) : '';
	}

	/**
	 * Return File 00 membership assertions without duplicating identity state.
	 *
	 * @param int $user_id User ID.
	 * @return array<string,mixed>
	 */
	public static function membership_assertions( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! class_exists( 'SMC_Contracts' ) || ! is_callable( array( 'SMC_Contracts', 'assertions' ) ) ) {
			return array( '_contract_error' => true );
		}

		try {
			$assertions = \SMC_Contracts::assertions( $user_id );
		} catch ( \Throwable $error ) {
			unset( $error );
			return array( '_contract_error' => true );
		}
		if ( ! is_array( $assertions ) ) {
			return array( '_contract_error' => true );
		}
		$contract = isset( $assertions['contract_version'] ) ? (string) $assertions['contract_version'] : '';
		$subject  = isset( $assertions['user_id'] ) ? absint( $assertions['user_id'] ) : 0;
		if ( $subject !== $user_id || ! self::valid_semver( $contract ) || version_compare( $contract, '1.1.2', '<' ) ) {
			return array( '_contract_error' => true );
		}
		return $assertions;
	}

	/** Whether File 00 has placed the exact subject in a terminal/restricted state. */
	private static function assertions_have_hard_block( array $assertions ) {
		return ! empty( $assertions['_contract_error'] )
			|| ! empty( $assertions['suspended'] )
			|| in_array(
				(string) ( $assertions['status'] ?? '' ),
				array( 'rejected', 'suspended', 'expired', 'appeal_review', 'erasure_pending', 'invalid_application' ),
				true
			);
	}

	/** Whether File 00 permits a current public identity projection. */
	private static function assertions_allow_public_identity( array $assertions ) {
		return ! self::assertions_have_hard_block( $assertions )
			&& ! empty( $assertions['approved'] )
			&& ! empty( $assertions['eligible'] )
			&& ! empty( $assertions['identity_evidence_current'] );
	}

	/** Whether File 00 permits a privileged action in the current session. */
	private static function assertions_allow_privileged_action( array $assertions ) {
		return self::assertions_allow_public_identity( $assertions )
			&& ! empty( $assertions['two_factor_ready'] )
			&& ! empty( $assertions['session_two_factor'] )
			&& ! empty( $assertions['sensitive_action_ready'] );
	}

	/**
	 * Whether a user is the authoritative Founder identity.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_founder( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return false;
		}
		$assertions = self::membership_assertions( $user_id );
		return self::assertions_allow_public_identity( $assertions )
			&& 'founder' === ( $assertions['account_class'] ?? '' );
	}

	/**
	 * Whether File 00 currently grants trusted publishing authority.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_trusted_publisher( $user_id ) {
		$user_id    = absint( $user_id );
		$assertions = self::membership_assertions( $user_id );
		if ( ! empty( $assertions ) ) {
			if ( ! self::assertions_allow_privileged_action( $assertions ) ) {
				return false;
			}
			if ( ! empty( $assertions['can_publish'] ) ) {
				return true;
			}
			return 'administrator' === ( $assertions['account_class'] ?? '' ) && user_can( $user_id, 'manage_options' );
		}

		return false;
	}

	/**
	 * Whether a doctor is verified and eligible for public shell discovery.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_verified_doctor( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return false;
		}
		$assertions = self::membership_assertions( $user_id );
		if ( ! self::assertions_allow_public_identity( $assertions ) || empty( $assertions['professional_verified'] ) ) {
			return false;
		}
		if ( array_key_exists( 'public_profile_allowed', $assertions ) && empty( $assertions['public_profile_allowed'] ) ) {
			return false;
		}
		if ( 'doctor' !== ( $assertions['membership_type'] ?? '' ) || ( empty( $assertions['can_practice'] ) && empty( $assertions['can_publish'] ) ) ) {
			return false;
		}
		if ( class_exists( 'SPD_Verification_Adapter' ) && is_callable( array( 'SPD_Verification_Adapter', 'directory_eligible' ) ) ) {
			return (bool) \SPD_Verification_Adapter::directory_eligible( $user_id );
		}
		return true;
	}

	/**
	 * Whether a user may reach the platform composer.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function can_publish( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return false;
		}

		$assertions = self::membership_assertions( $user_id );
		if ( ! empty( $assertions ) ) {
			if ( ! self::assertions_allow_privileged_action( $assertions ) ) {
				return false;
			}
			if ( ! empty( $assertions['can_publish'] ) ) {
				return true;
			}
			return 'administrator' === ( $assertions['account_class'] ?? '' ) && user_can( $user_id, 'manage_options' );
		}

		return false;
	}

	/**
	 * Public doctor data from File 03 approved projections only.
	 *
	 * @param int $user_id User ID.
	 * @return array<string,string>
	 */
	public static function doctor_public_data( $user_id ) {
		$user_id = absint( $user_id );
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return array();
		}

		$founder          = self::is_founder( $user_id );
		$approved_fields  = array();
		$file03_available = class_exists( 'SPD_Verification_Adapter' )
			&& is_callable( array( 'SPD_Verification_Adapter', 'directory_eligible' ) )
			&& is_callable( array( 'SPD_Verification_Adapter', 'approved_fields' ) );

		if ( ! $file03_available ) {
			return array();
		}
		if ( ! $founder && ! \SPD_Verification_Adapter::directory_eligible( $user_id ) ) {
			return array();
		}
		$approved_fields = \SPD_Verification_Adapter::approved_fields( $user_id );
		$approved_fields = is_array( $approved_fields ) ? $approved_fields : array();
		if ( empty( $approved_fields ) ) {
			return array();
		}

		$data = array(
			'name'      => (string) ( $approved_fields['display_name'] ?? '' ),
			'profile'   => self::profile_url( $user_id ),
			'country'   => (string) ( $approved_fields['country'] ?? '' ),
			'city'      => (string) ( $approved_fields['city'] ?? '' ),
			'clinic'    => (string) ( $approved_fields['clinic'] ?? '' ),
			'fee'       => (string) ( $approved_fields['fee'] ?? '' ),
			'currency'  => (string) ( $approved_fields['currency'] ?? '' ),
			'timings'   => (string) ( $approved_fields['timings'] ?? ( $approved_fields['hours'] ?? '' ) ),
			'languages' => (string) ( $approved_fields['languages'] ?? '' ),
			'specialty' => (string) ( $approved_fields['specialty'] ?? ( $approved_fields['specialization'] ?? '' ) ),
			'phone'     => '',
			'whatsapp'  => '',
		);

		// File 20 must never infer public professional data from raw profile or
		// membership metadata. Only File 03's explicit approved projection may
		// populate professional fields.

		$contact_allowed = false;
		if ( class_exists( 'SPD_Helpers' ) && is_callable( array( 'SPD_Helpers', 'can_show_contact' ) ) ) {
			$contact_allowed = (bool) \SPD_Helpers::can_show_contact( $user_id, $founder );
		}
		$contact_allowed = $contact_allowed && (bool) apply_filters( 'sabri_shell_public_contact_allowed', true, $user_id, $data );
		if ( $contact_allowed ) {
			$data['phone']    = (string) ( $approved_fields['phone'] ?? '' );
			$data['whatsapp'] = (string) ( $approved_fields['whatsapp'] ?? '' );
		}

		$filtered = apply_filters( 'sabri_shell_doctor_public_data', $data, $user_id );
		if ( is_array( $filtered ) ) {
			// Extension callbacks may refine approved fields but cannot add a new
			// undeclared public-data channel (for example email or identity data).
			$data = array_merge( $data, array_intersect_key( $filtered, $data ) );
		}
		if ( ! $contact_allowed ) {
			$data['phone']    = '';
			$data['whatsapp'] = '';
		}
		foreach ( $data as $key => $value ) {
			$value = is_scalar( $value ) ? (string) $value : '';
			$data[ $key ] = 'profile' === $key
				? ( function_exists( 'esc_url_raw' ) ? esc_url_raw( $value ) : $value )
				: ( function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $value ) : strip_tags( $value ) );
		}
		return $data;
	}

	/**
	 * Render a real language switcher when a supported integration is available.
	 *
	 * @return string
	 */
	public static function language_switcher() {
		if ( function_exists( 'pll_the_languages' ) ) {
			$html = pll_the_languages( array( 'echo' => 0, 'show_names' => 1, 'hide_if_empty' => 0 ) );
			return is_string( $html ) ? $html : '';
		}
		if ( function_exists( 'icl_get_languages' ) ) {
			$languages = icl_get_languages( 'skip_missing=0' );
			if ( is_array( $languages ) && $languages ) {
				$html = '<ul class="sabri-shell-language-list">';
				foreach ( $languages as $language ) {
					if ( empty( $language['url'] ) || empty( $language['native_name'] ) ) {
						continue;
					}
					$html .= '<li><a href="' . esc_url( $language['url'] ) . '">' . esc_html( $language['native_name'] ) . '</a></li>';
				}
				return $html . '</ul>';
			}
		}
		if ( shortcode_exists( 'weglot_switcher' ) ) {
			return (string) do_shortcode( '[weglot_switcher]' );
		}
		return '';
	}


	/**
	 * Consume File 07/native verified-doctor discovery without role-label scans.
	 *
	 * @param int $limit Maximum results.
	 * @return array<int,int>
	 */
	public static function verified_doctor_user_ids( $limit = 5 ) {
		$limit = max( 1, min( 20, absint( $limit ) ) );
		$ids = apply_filters( 'sabri_shell_verified_doctor_user_ids', array(), $limit );
		if ( ! is_array( $ids ) ) { return array(); }
		$out = array();
		foreach ( array_values( array_unique( array_map( 'absint', $ids ) ) ) as $user_id ) {
			if ( $user_id && self::is_verified_doctor( $user_id ) && self::doctor_public_data( $user_id ) ) {
				$out[] = $user_id;
			}
			if ( count( $out ) >= $limit ) { break; }
		}
		return $out;
	}


	/** Accept only same-site HTTP(S) URLs for internal platform destinations. */
	public static function same_site_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) { return ''; }
		if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
			$url = home_url( $url );
		}
		$clean = esc_url_raw( $url, array( 'http', 'https' ) );
		if ( ! $clean ) { return ''; }
		$parts = wp_parse_url( $clean );
		$home  = wp_parse_url( home_url( '/' ) );
		if ( ! is_array( $parts ) || ! is_array( $home ) || empty( $parts['host'] ) || empty( $home['host'] ) ) { return ''; }
		$scheme = isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : '';
		$home_scheme = isset( $home['scheme'] ) ? strtolower( (string) $home['scheme'] ) : '';
		$port = isset( $parts['port'] ) ? absint( $parts['port'] ) : ( 'https' === $scheme ? 443 : 80 );
		$home_port = isset( $home['port'] ) ? absint( $home['port'] ) : ( 'https' === $home_scheme ? 443 : 80 );
		if ( ! in_array( $scheme, array( 'http', 'https' ), true ) || $scheme !== $home_scheme || strtolower( (string) $parts['host'] ) !== strtolower( (string) $home['host'] ) || $port !== $home_port ) { return ''; }
		return wp_validate_redirect( $clean, '' );
	}

	/** Strict bounded semantic-version shape for executable contracts. */
	private static function valid_semver( $version ) {
		return is_string( $version ) && 1 === preg_match( '/^\\d+\\.\\d+\\.\\d+(?:[-+][0-9A-Za-z.-]+)?$/', $version );
	}

	/**
	 * Return existing post types from a candidate list.
	 *
	 * @param array<int,string> $candidates Candidate slugs.
	 * @return array<int,string>
	 */
	private static function existing_post_types( array $candidates ) {
		$existing = array();
		foreach ( $candidates as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				$existing[] = $post_type;
			}
		}
		return $existing;
	}

	/**
	 * Find a published page containing any shortcode.
	 *
	 * @param array<int,string> $shortcodes Shortcodes.
	 * @return string
	 */
	public static function find_page_by_shortcodes( array $shortcodes ) {
		$shortcodes = array_values( array_unique( array_filter( array_map( 'sanitize_key', $shortcodes ) ) ) );
		$shortcodes = array_values( array_filter( $shortcodes, 'shortcode_exists' ) );
		if ( empty( $shortcodes ) ) {
			return '';
		}
		sort( $shortcodes, SORT_STRING );
		$signature = implode( '|', $shortcodes );
		if ( array_key_exists( $signature, self::$shortcode_lookup ) ) {
			return self::$shortcode_lookup[ $signature ];
		}

		$epoch = class_exists( Navigation::class ) ? absint( get_option( Navigation::CACHE_EPOCH_OPTION, 1 ) ) : 1;
		$cache_key = 'sabri_shell_shortcode_page_' . md5( $signature . '|' . $epoch );
		self::register_shortcode_cache_key( $cache_key );
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) && isset( $cached['status'] ) ) {
			if ( 'unique' === $cached['status'] && ! empty( $cached['url'] ) ) {
				self::$shortcode_lookup[ $signature ] = self::same_site_url( $cached['url'] );
				return self::$shortcode_lookup[ $signature ];
			}
			self::$shortcode_lookup[ $signature ] = '';
			return '';
		}

		$per_page = 100;
		$max_batches = 10; // At most 1,000 Pages per compatibility scan.
		$matches = array();
		$scan_complete = false;
		for ( $page_number = 1; $page_number <= $max_batches; $page_number++ ) {
			$pages = get_posts(
				array(
					'post_type'              => 'page',
					'post_status'            => 'publish',
					'posts_per_page'         => $per_page,
					'paged'                  => $page_number,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'orderby'                => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
				)
			);
			if ( empty( $pages ) ) {
				$scan_complete = true;
				break;
			}
			foreach ( $pages as $page ) {
				$content = isset( $page->post_content ) ? (string) $page->post_content : '';
				foreach ( $shortcodes as $shortcode ) {
					if ( has_shortcode( $content, $shortcode ) ) {
						$matches[ absint( $page->ID ) ] = true;
						break;
					}
				}
				if ( count( $matches ) > 1 ) { break 2; }
			}
			if ( count( $pages ) < $per_page ) {
				$scan_complete = true;
				break;
			}
		}

		$ids = array_keys( $matches );
		if ( count( $ids ) > 1 ) {
			self::$shortcode_collisions[ $signature ] = array_map( 'absint', $ids );
			set_transient( $cache_key, array( 'status' => 'collision' ), 10 * MINUTE_IN_SECONDS );
			do_action( 'sabri_shell_shortcode_page_collision', $shortcodes, self::$shortcode_collisions[ $signature ] );
			self::$shortcode_lookup[ $signature ] = '';
			return '';
		}
		if ( 1 !== count( $ids ) || ! $scan_complete ) {
			set_transient( $cache_key, array( 'status' => $scan_complete ? 'none' : 'incomplete' ), 10 * MINUTE_IN_SECONDS );
			self::$shortcode_lookup[ $signature ] = '';
			return '';
		}

		$url = self::published_page_url( $ids[0] );
		$url = self::same_site_url( $url );
		set_transient( $cache_key, array( 'status' => $url ? 'unique' : 'invalid', 'url' => $url ), 10 * MINUTE_IN_SECONDS );
		self::$shortcode_lookup[ $signature ] = $url;
		return $url;
	}

	/** Return request-level shortcode collision evidence for diagnostics. */
	public static function shortcode_collisions() {
		return self::$shortcode_collisions;
	}

	/**
	 * Return a URL only for a published page.
	 *
	 * @param int $page_id Page ID.
	 * @return string
	 */
	private static function published_page_url( $page_id ) {
		$page_id = absint( $page_id );
		if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
			return '';
		}
		if ( function_exists( 'get_post_type' ) && 'page' !== get_post_type( $page_id ) ) {
			return '';
		}
		$url = get_permalink( $page_id );
		return $url ? self::same_site_url( $url ) : '';
	}
}
