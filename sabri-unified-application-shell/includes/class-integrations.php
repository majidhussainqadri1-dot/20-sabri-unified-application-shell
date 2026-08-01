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
	/**
	 * Request-level detection cache.
	 *
	 * @var array<string,mixed>|null
	 */
	private static $detected = null;

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
		$roles    = self::roles();

		self::$detected = array(
			'notifications'         => self::has_configured_function( 'notifications', $settings ) || shortcode_exists( 'sabri_notifications' ) || shortcode_exists( 'sabri_notification_bell' ) || ( class_exists( 'SUN_Utils' ) && is_callable( array( 'SUN_Utils', 'page_url' ) ) ) || ! empty( $settings['integrations']['urls']['notifications'] ),
			'network'               => self::has_configured_function( 'network', $settings ) || shortcode_exists( 'sabri_network' ) || class_exists( 'SN_Activator' ) || (int) get_option( 'sn_network_page_id', 0 ) > 0,
			'messages'              => self::has_configured_function( 'messages', $settings ) || shortcode_exists( 'sabri_messages' ) || shortcode_exists( 'sabri_network' ) || class_exists( 'SN_Activator' ) || ! empty( $settings['integrations']['urls']['messages'] ),
			'marketplace'           => shortcode_exists( 'sabri_marketplace' ) || class_exists( 'SMP_Activator' ) || (int) get_option( 'smp_marketplace_page_id', 0 ) > 0,
			'appointments'          => self::has_configured_function( 'appointments', $settings ) || shortcode_exists( 'swc_my_appointments' ) || shortcode_exists( 'swc_request_appointment' ) || ! empty( self::page_id( 'appointments' ) ) || ! empty( $settings['integrations']['urls']['appointments'] ),
			'language'              => '' !== self::language_switcher(),
			'doctor_roles'          => array_values( array_intersect( self::doctor_role_candidates(), $roles ) ),
			'verified_doctor_roles' => array_values( array_intersect( self::verified_doctor_role_candidates(), $roles ) ),
			'clinic_post_types'     => self::existing_post_types( array( 'doctor', 'clinic', 'global_clinic', 'sabri_clinic' ) ),
			'configured_functions'  => $settings['integrations']['functions'],
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
		$kind = sanitize_key( $kind );
		$url  = self::page_url( $kind );
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
		if ( class_exists( 'SDD_Helpers' ) && is_callable( array( 'SDD_Helpers', 'profile_url' ) ) ) {
			return (string) \SDD_Helpers::profile_url( $user_id );
		}
		if ( class_exists( 'SPD_Helpers' ) && is_callable( array( 'SPD_Helpers', 'profile_url' ) ) ) {
			return (string) \SPD_Helpers::profile_url( $user_id );
		}

		$url  = self::page_url( 'profile' );
		$user = get_userdata( $user_id );
		if ( $url && $user ) {
			return add_query_arg( 'user', $user->user_nicename, $url );
		}
		$url = self::find_page_by_shortcodes( array( 'sabri_profile', 'sabri_member_profile' ) );
		return $url ? $url : get_author_posts_url( $user_id );
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
		return (string) apply_filters( 'sabri_shell_create_url', $url );
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
				return (string) $url;
			}
		}
		if ( in_array( $key, array( 'network', 'messages' ), true ) && class_exists( 'SN_Activator' ) && is_callable( array( 'SN_Activator', 'network_url' ) ) ) {
			$url = \SN_Activator::network_url();
			if ( $url ) {
				return (string) $url;
			}
		}
		if ( 'marketplace' === $key && class_exists( 'SMP_Activator' ) && is_callable( array( 'SMP_Activator', 'marketplace_url' ) ) ) {
			$url = \SMP_Activator::marketplace_url();
			if ( $url ) {
				return (string) $url;
			}
		}

		$url = self::page_url( $key );
		if ( $url ) {
			return $url;
		}

		$shortcodes = array(
			'notifications' => array( 'sabri_notifications' ),
			'network'       => array( 'sabri_network' ),
			'messages'      => array( 'sabri_network', 'sabri_messages' ),
			'marketplace'   => array( 'sabri_marketplace' ),
			'appointments'  => array( 'swc_my_appointments', 'swc_request_appointment' ),
		);
		return ! empty( $shortcodes[ $key ] ) ? self::find_page_by_shortcodes( $shortcodes[ $key ] ) : '';
	}

	/**
	 * Whether a user is the authoritative Founder.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_founder( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return false;
		}
		if ( function_exists( 'smc_is_founder' ) && smc_is_founder( $user_id ) ) {
			return true;
		}
		if ( get_user_meta( $user_id, '_smc_official_founder', true ) ) {
			return true;
		}
		return $user_id === absint( get_option( 'spf_founder_user_id', 0 ) );
	}

	/**
	 * Whether a user has trusted publishing authority.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_trusted_publisher( $user_id ) {
		$user_id = absint( $user_id );
		if ( self::is_founder( $user_id ) ) {
			return true;
		}
		if ( function_exists( 'smc_is_trusted_publisher' ) && smc_is_trusted_publisher( $user_id ) ) {
			return true;
		}
		return (bool) get_user_meta( $user_id, '_smc_trusted_publisher', true );
	}

	/**
	 * Whether a user is a verified doctor under current or legacy contracts.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_verified_doctor( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return false;
		}
		if ( self::is_founder( $user_id ) ) {
			return true;
		}
		if ( get_user_meta( $user_id, '_smc_doctor_verified', true ) ) {
			return true;
		}
		if ( class_exists( 'SPD_Helpers' ) && is_callable( array( 'SPD_Helpers', 'verification_status' ) ) && 'verified' === \SPD_Helpers::verification_status( $user_id ) ) {
			return true;
		}
		$user = get_userdata( $user_id );
		return $user && (bool) array_intersect( self::verified_doctor_role_candidates(), (array) $user->roles );
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
		if ( user_can( $user_id, 'manage_options' ) || self::is_trusted_publisher( $user_id ) ) {
			return true;
		}
		return self::is_verified_doctor( $user_id ) && ( user_can( $user_id, 'edit_posts' ) || user_can( $user_id, 'smc_submit_knowledge' ) );
	}

	/**
	 * Public doctor data for shell panels without owning a duplicate database.
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

		$data = array(
			'name'      => (string) $user->display_name,
			'profile'   => self::profile_url( $user_id ),
			'country'   => '',
			'city'      => '',
			'clinic'    => '',
			'fee'       => '',
			'currency'  => '',
			'timings'   => '',
			'languages' => '',
			'specialty' => '',
			'phone'     => '',
			'whatsapp'  => '',
		);

		if ( class_exists( 'SPD_Helpers' ) && is_callable( array( 'SPD_Helpers', 'get' ) ) ) {
			$data['country']   = (string) \SPD_Helpers::get( $user_id, 'country' );
			$data['city']      = (string) \SPD_Helpers::get( $user_id, 'city' );
			$data['clinic']    = (string) \SPD_Helpers::get( $user_id, 'clinic' );
			$data['languages'] = (string) \SPD_Helpers::get( $user_id, 'languages' );
			$data['specialty'] = (string) \SPD_Helpers::get( $user_id, 'specialty' );
			$data['phone']     = (string) \SPD_Helpers::get( $user_id, 'phone' );
			$data['whatsapp']  = (string) \SPD_Helpers::get( $user_id, 'whatsapp' );
		}

		if ( function_exists( 'smc_get_profile' ) ) {
			$profile = smc_get_profile( $user_id );
			if ( is_array( $profile ) ) {
				foreach ( array( 'country', 'city', 'phone', 'whatsapp' ) as $field ) {
					if ( empty( $data[ $field ] ) && ! empty( $profile[ $field ] ) ) {
						$data[ $field ] = (string) $profile[ $field ];
					}
				}
				if ( empty( $data['languages'] ) && ! empty( $profile['preferred_language'] ) ) {
					$data['languages'] = (string) $profile['preferred_language'];
				}
			}
		}

		global $wpdb;
		if ( ( function_exists( 'smc_get_profile' ) || defined( 'SMC_VERSION' ) ) && isset( $wpdb ) && is_object( $wpdb ) ) {
			$credentials = $wpdb->get_row( $wpdb->prepare( "SELECT specialization, fee, currency, languages FROM {$wpdb->prefix}smc_professional_credentials WHERE user_id = %d LIMIT 1", $user_id ), ARRAY_A );
			$clinic      = $wpdb->get_row( $wpdb->prepare( "SELECT name, phone, whatsapp, hours FROM {$wpdb->prefix}smc_clinics WHERE owner_user_id = %d AND status = 'approved' ORDER BY id DESC LIMIT 1", $user_id ), ARRAY_A );
			if ( is_array( $credentials ) ) {
				$data['specialty'] = $data['specialty'] ?: (string) ( $credentials['specialization'] ?? '' );
				$data['fee']       = (string) ( $credentials['fee'] ?? '' );
				$data['currency']  = (string) ( $credentials['currency'] ?? '' );
				$data['languages'] = $data['languages'] ?: (string) ( $credentials['languages'] ?? '' );
			}
			if ( is_array( $clinic ) ) {
				$data['clinic']   = $data['clinic'] ?: (string) ( $clinic['name'] ?? '' );
				$data['phone']    = $data['phone'] ?: (string) ( $clinic['phone'] ?? '' );
				$data['whatsapp'] = $data['whatsapp'] ?: (string) ( $clinic['whatsapp'] ?? '' );
				$data['timings']  = (string) ( $clinic['hours'] ?? '' );
			}
		}

		foreach ( $data as $key => $value ) {
			$data[ $key ] = is_scalar( $value ) ? (string) $value : '';
		}
		return apply_filters( 'sabri_shell_doctor_public_data', $data, $user_id );
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
	 * Get available role names.
	 *
	 * @return array<int,string>
	 */
	public static function roles() {
		if ( ! function_exists( 'wp_roles' ) ) {
			return array();
		}
		$roles = wp_roles();
		return $roles && ! empty( $roles->roles ) && is_array( $roles->roles ) ? array_keys( $roles->roles ) : array();
	}

	/**
	 * Candidate doctor roles across Membership Core and legacy modules.
	 *
	 * @return array<int,string>
	 */
	public static function doctor_role_candidates() {
		return array( 'sabri_doctor', 'sabri_verified_doctor', 'sabri_doctor_pending', 'sabri_doctor_verified', 'doctor', 'verified_doctor', 'approved_doctor', 'founder' );
	}

	/**
	 * Candidate verified doctor roles.
	 *
	 * @return array<int,string>
	 */
	public static function verified_doctor_role_candidates() {
		return array( 'sabri_verified_doctor', 'sabri_doctor_verified', 'verified_doctor', 'approved_doctor' );
	}

	/**
	 * Determine whether a configured callback exists.
	 *
	 * @param string              $key Integration key.
	 * @param array<string,mixed> $settings Settings.
	 * @return bool
	 */
	private static function has_configured_function( $key, array $settings ) {
		$function = isset( $settings['integrations']['functions'][ $key ] ) ? $settings['integrations']['functions'][ $key ] : '';
		return $function && function_exists( $function );
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
	private static function find_page_by_shortcodes( array $shortcodes ) {
		if ( empty( $shortcodes ) ) {
			return '';
		}
		$pages = get_posts(
			array(
				'post_type'              => 'page',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		foreach ( $pages as $page ) {
			foreach ( $shortcodes as $shortcode ) {
				if ( has_shortcode( (string) $page->post_content, $shortcode ) ) {
					return (string) get_permalink( $page );
				}
			}
		}
		return '';
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
		$url = get_permalink( $page_id );
		return $url ? (string) $url : '';
	}
}
