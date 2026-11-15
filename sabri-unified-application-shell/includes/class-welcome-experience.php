<?php
/**
 * File 20 canonical welcome eligibility, recurrence, and shell placement.
 * File 13 is legacy compatibility only; File 25 owns visual presentation.
 * Public activation is fail-closed until an explicit trusted Founder adapter
 * confirms approval and a privileged operator enables the configuration.
 *
 * @package SabriUnifiedApplicationShell
 */
namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class WelcomeExperience {
	const OPTION = 'sabri_shell_welcome_config';
	const AUDIT_OPTION = 'sabri_shell_welcome_audit';
	const APPROVAL_OPTION = 'sabri_shell_welcome_founder_approval';
	const APPROVAL_ACTION = 'sabri_shell_welcome_approve';
	const NONCE_ACTION = 'sabri_shell_welcome_save';
	const PREVIEW_ACTION = 'sabri_shell_welcome_preview';
	const STORAGE_KEY = 'sabri-shell-welcome-dismiss-until-v1';
	const DAYS = 30;
	/** Only one File 20 placement per response, even if both theme hooks fire. */
	private static $rendered = false;

	public static function register() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 125 );
		add_action( 'wp_body_open', array( __CLASS__, 'render' ), 3 );
		// Theme compatibility: scripts print later than this guarded fallback.
		add_action( 'wp_footer', array( __CLASS__, 'render' ), 2 );
		add_action( 'admin_menu', array( __CLASS__, 'register_admin' ), 99 );
		add_action( 'admin_post_sabri_shell_welcome_save', array( __CLASS__, 'save' ) );
		add_action( 'admin_post_' . self::APPROVAL_ACTION, array( __CLASS__, 'approve' ) );
		add_action( 'admin_post_' . self::PREVIEW_ACTION, array( __CLASS__, 'preview' ) );
		add_filter( 'sabri_shell_welcome_contract', array( __CLASS__, 'contract' ) );
		add_shortcode( 'sabri_welcome_manual_link', array( __CLASS__, 'manual_link' ) );
	}

	public static function defaults() {
		return array(
			'enabled' => false,
			'config_version' => 1,
			'copy_version' => 1,
			'title' => 'Welcome to Sabri Social Homeopathy Platform',
			'message' => 'A place to learn, connect, and explore classical homeopathy.',
			'start_at' => '',
			'end_at' => '',
			'eligible_paths' => array( '/' ),
			'frequency_days' => self::DAYS,
		);
	}

	/** Limit Unicode copy without splitting an Urdu/Arabic UTF-8 codepoint. */
	private static function limit_text( $text, $length ) {
		$text = (string) $text;
		if ( function_exists( 'mb_substr' ) ) { return mb_substr( $text, 0, $length, 'UTF-8' ); }
		if ( function_exists( 'wp_html_excerpt' ) ) { return wp_html_excerpt( $text, $length, '' ); }
		return substr( $text, 0, $length );
	}

	/** Only the File 20 owner may read and enforce this bounded preference. */
	public static function configuration() {
		$stored = get_option( self::OPTION, array() );
		return self::sanitize( is_array( $stored ) ? $stored : array(), is_array( $stored ) ? $stored : self::defaults() );
	}

	public static function sanitize( array $input, array $base ) {
		$value = array_replace( self::defaults(), $base );
		if ( array_key_exists( 'enabled', $input ) ) {
			$value['enabled'] = in_array( $input['enabled'], array( true, 1, '1', 'on', 'yes' ), true );
		}
		if ( isset( $input['title'] ) ) {
			$value['title'] = self::limit_text( sanitize_text_field( (string) $input['title'] ), 180 );
		}
		if ( isset( $input['message'] ) ) {
			$value['message'] = self::limit_text( sanitize_textarea_field( (string) $input['message'] ), 600 );
		}
		foreach ( array( 'start_at', 'end_at' ) as $field ) {
			if ( array_key_exists( $field, $input ) ) {
				$candidate = sanitize_text_field( (string) $input[ $field ] );
				$value[ $field ] = '' === $candidate || false !== strtotime( $candidate ) ? $candidate : '';
			}
		}
		if ( isset( $input['eligible_paths'] ) ) {
			$raw = is_array( $input['eligible_paths'] ) ? $input['eligible_paths'] : preg_split( '/[\r\n,]+/', (string) $input['eligible_paths'] );
			$paths = array();
			foreach ( (array) $raw as $path ) {
				if ( ! is_string( $path ) || '' === $path || '/' !== $path[0] || 0 === strpos( $path, '//' ) || false !== strpos( $path, '?' ) || false !== strpos( $path, '#' ) ) {
					continue;
				}
				$normalized = self::normalize_path( $path );
				if ( '' !== $normalized && ! self::sensitive_path( $normalized ) ) {
					$paths[] = $normalized;
				}
			}
			$value['eligible_paths'] = array_slice( array_values( array_unique( $paths ) ), 0, 20 );
		}
		$value['config_version'] = max( 1, absint( $base['config_version'] ?? 1 ) );
		$value['copy_version'] = max( 1, absint( $base['copy_version'] ?? 1 ) );
		// The V12.3 minimum cannot be reduced by a stored option or a preview.
		$value['frequency_days'] = self::DAYS;
		return $value;
	}

	public static function contract( $current = array() ) {
		$current = is_array( $current ) ? $current : array();
		$config = self::configuration();
		return array_replace( $current, array(
			'owner' => 'file-20',
			'version' => '1.0.0',
			'visual_owner' => 'file-25',
			'legacy_file13' => 'compatibility-only',
			'frequency_days' => self::DAYS,
			'configured_enabled' => ! empty( $config['enabled'] ),
			'public_eligible' => self::public_eligible(),
		) );
	}

	public static function normalize_path( $path ) {
		$path = rawurldecode( (string) $path );
		if ( false !== strpos( $path, '\\' ) || false !== strpos( $path, "\0" ) || false !== strpos( $path, '..' ) ) { return ''; }
		$path = '/' . ltrim( preg_replace( '#/+#', '/', $path ), '/' );
		return '/' === $path ? '/' : rtrim( $path, '/' );
	}

	public static function sensitive_path( $path ) {
		$prefixes = array(
			'/wp-admin', '/wp-login.php', '/wp-json', '/login', '/logout',
			'/register', '/signup', '/account', '/password', '/recovery',
			'/doctor/apply', '/doctor/application', '/doctor/verification',
			'/appointment', '/appointments', '/booking', '/checkout',
			'/payment', '/transaction', '/clinical', '/patient', '/emergency',
			'/consultation', '/messages', '/settings', '/welcome-intro-preview',
		);
		foreach ( $prefixes as $prefix ) {
			if ( $path === $prefix || 0 === strpos( $path, $prefix . '/' ) ) { return true; }
		}
		return false;
	}

	/** Resolve the actual public home path (including WordPress subdirectory). */
	public static function home_path() {
		$path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		return self::normalize_path( is_string( $path ) ? $path : '/' );
	}

	public static function route_eligible( $request_uri, array $config ) {
		$parts = wp_parse_url( (string) $request_uri );
		if ( ! is_array( $parts ) || isset( $parts['scheme'] ) || isset( $parts['host'] ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || ! empty( $parts['query'] ) || ! empty( $parts['fragment'] ) ) { return false; }
		$path = self::normalize_path( (string) ( $parts['path'] ?? '/' ) );
		if ( '' === $path || self::sensitive_path( $path ) ) { return false; }
		$base_path = self::home_path();
		foreach ( (array) $config['eligible_paths'] as $allowed ) {
			$allowed = self::normalize_path( $allowed );
			$absolute = '/' === $allowed ? $base_path : self::normalize_path( rtrim( $base_path, '/' ) . $allowed );
			if ( '' !== $absolute && $path === $absolute ) { return true; }
		}
		return false;
	}

	public static function public_eligible() {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { return false; }
		if ( defined( 'SABRI_SHELL_DISABLE_WELCOME' ) && SABRI_SHELL_DISABLE_WELCOME ) { return false; }
		if ( SafeMode::disabled() ) { return false; }
		if ( function_exists( 'is_feed' ) && is_feed() ) { return false; }
		if ( function_exists( 'is_404' ) && is_404() ) { return false; }
		if ( in_array( Layout::current_mode(), array( Layout::MINIMAL, Layout::IMMERSIVE ), true ) ) { return false; }
		$config = self::configuration();
		if ( empty( $config['enabled'] ) || empty( $config['eligible_paths'] ) ) { return false; }
		// Every activation requires a stored content-bound approval signed by
		// a current File 00 canonical Founder, never inferred from a WP role.
		if ( ! self::approval_valid( $config ) ) { return false; }
		if ( true !== apply_filters( 'sabri_shell_welcome_file24_allowed', true, $config ) ) { return false; }
		$now = time();
		if ( '' !== $config['start_at'] && ( $start = strtotime( $config['start_at'] ) ) !== false && $now < $start ) { return false; }
		if ( '' !== $config['end_at'] && ( $end = strtotime( $config['end_at'] ) ) !== false && $now >= $end ) { return false; }
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		if ( self::manual_requested( $uri ) ) { return self::route_eligible( self::home_path(), $config ); }
		return self::route_eligible( $uri, $config );
	}

	/**
	 * Manual replay is an explicit, single-parameter home request. It never
	 * overrides kill switches, Founder approval or sensitive-route exclusions.
	 * The browser controller will not read/write device state for this mode.
	 */
	public static function manual_requested( $request_uri ) {
		$parts = wp_parse_url( (string) $request_uri );
		return is_array( $parts )
			&& ! isset( $parts['host'] ) && ! isset( $parts['scheme'] )
			&& self::home_path() === self::normalize_path( $parts['path'] ?? '/' )
			&& 'sabri_welcome=manual' === (string) ( $parts['query'] ?? '' );
	}

	/** Optional accessible link for the Help/Introduction page. */
	public static function manual_link() {
		$config = self::configuration();
		if ( empty( $config['enabled'] ) || ! self::approval_valid( $config ) ) { return ''; }
		$url = add_query_arg( 'sabri_welcome', 'manual', home_url( '/' ) );
		return '<a class="sabri-welcome-manual-link" href="' . esc_url( $url ) . '">' .
			esc_html__( 'Show platform introduction', 'sabri-unified-application-shell' ) . '</a>';
	}

	private static function visual_available() {
		if ( ! defined( 'SABRI_PUBLIC_EXPERIENCE_VERSION' ) ||
			version_compare( (string) SABRI_PUBLIC_EXPERIENCE_VERSION, '0.15.0', '<' ) ||
			! defined( 'SABRI_PUBLIC_EXPERIENCE_DIR' ) ||
			! function_exists( 'sabri_visual_experience_render_welcome' ) ||
			! class_exists( 'Sabri\\PublicExperience\\Three_Plan_Corrections' ) ||
			! class_exists( 'Sabri\\PublicExperience\\Design_System' ) ) {
			return false;
		}
		$callback = array( 'Sabri\\PublicExperience\\Design_System', 'filter_shell_visual_contract' );
		if ( ! function_exists( 'has_filter' ) || false === has_filter( 'sabri_shell_file25_visual_contract', $callback ) ) {
			return false;
		}
		try {
			$renderer = ( new \ReflectionClass( 'Sabri\\PublicExperience\\Three_Plan_Corrections' ) )->getFileName();
			$canonical = realpath( SABRI_PUBLIC_EXPERIENCE_DIR . 'includes/class-three-plan-corrections.php' );
			if ( false === $canonical || realpath( $renderer ) !== $canonical ) { return false; }
		} catch ( \Throwable $error ) {
			unset( $error );
			return false;
		}
		$contract = apply_filters( 'sabri_shell_file25_visual_contract', array() );
		if ( ! is_array( $contract ) || 'file-25' !== (string) ( $contract['owner'] ?? '' ) ||
			! is_string( $contract['version'] ?? null ) ||
			! preg_match( '/^\d+\.\d+\.\d+$/', $contract['version'] ) ||
			version_compare( $contract['version'], '1.9.0', '<' ) ||
			version_compare( $contract['version'], '2.0.0', '>=' ) ||
			! is_array( $contract['tokens'] ?? null ) ) {
			return false;
		}
		foreach ( array( 'primary_color', 'text', 'surface_strong' ) as $token ) {
			if ( ! is_string( $contract['tokens'][ $token ] ?? null ) ||
				! preg_match( '/^#[0-9a-fA-F]{6}$/', $contract['tokens'][ $token ] ) ) {
				return false;
			}
		}
		return true;
	}

	private static function panel( array $config ) {
		if ( ! self::visual_available() ) { return ''; }
		return (string) sabri_visual_experience_render_welcome( array(
			'invoked_by_file_20' => true,
			'title' => $config['title'],
			'message' => $config['message'],
			'continue_label' => __( 'Explore platform', 'sabri-unified-application-shell' ),
			'close_label' => __( 'Close', 'sabri-unified-application-shell' ),
			'skip_label' => __( 'Skip', 'sabri-unified-application-shell' ),
			'continue_url' => home_url( '/' ),
		) );
	}

	public static function placement_css() {
		return '.sabri-shell-welcome[hidden]{display:none!important}.sabri-shell-welcome{position:fixed;inset-block-start:1rem;inset-inline:1rem;z-index:10000;pointer-events:none;max-block-size:calc(100dvh - 2rem);overflow:auto}.sabri-shell-welcome>.sabri-ui-welcome-panel{pointer-events:auto;max-inline-size:48rem;margin-inline:auto}@media(max-width:36rem){.sabri-shell-welcome{inset-block-start:.5rem;inset-inline:.5rem;max-block-size:calc(100dvh - 1rem)}}@media(prefers-reduced-motion:reduce){.sabri-shell-welcome,.sabri-shell-welcome *{animation:none!important;transition:none!important}}';
	}

	public static function enqueue() {
		try {
		if ( ! self::public_eligible() || ! self::visual_available() ) { return; }
		wp_register_style( 'sabri-shell-welcome-placement', false, array(), SABRI_SHELL_VERSION );
		wp_enqueue_style( 'sabri-shell-welcome-placement' );
		wp_add_inline_style( 'sabri-shell-welcome-placement', self::placement_css() );
		wp_enqueue_script( 'sabri-shell-welcome-controller', SABRI_SHELL_URL . 'assets/js/welcome-experience.js', array(), SABRI_SHELL_VERSION, true );
		wp_add_inline_script(
			'sabri-shell-welcome-controller',
			'window.SABRI_SHELL_WELCOME=' . wp_json_encode( array(
				'storageKey' => self::STORAGE_KEY . '-' . substr( hash( 'sha256', home_url( '/' ) ), 0, 12 ),
				'days' => self::DAYS,
				'copyVersion' => self::configuration()['copy_version'],
				'manual' => self::manual_requested( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/' ),
			) ) . ';',
			'before'
		);
		} catch ( \Throwable $error ) {
			unset( $error ); // Never let an optional intro asset block the public page.
		}
	}

	public static function render() {
		if ( self::$rendered ) { return; }
		try {
		if ( ! self::public_eligible() ) { return; }
		$panel = self::panel( self::configuration() );
		if ( '' === $panel ) { return; }
		self::$rendered = true;
		echo '<aside id="sabri-shell-welcome" class="sabri-shell-welcome" hidden aria-label="' . esc_attr__( 'Welcome to Sabri Homeopathy', 'sabri-unified-application-shell' ) . '" data-owner="file-20">';
		echo $panel; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- File 25 owns and escapes its visual markup.
		echo '</aside>';
		} catch ( \Throwable $error ) {
			unset( $error ); // Source/visual contract failure cannot hide the real page.
		}
	}

	public static function register_admin() {
		add_options_page(
			__( 'Sabri Welcome', 'sabri-unified-application-shell' ),
			__( 'Sabri Welcome', 'sabri-unified-application-shell' ),
			'manage_options',
			'sabri-shell-welcome',
			array( __CLASS__, 'admin_page' )
		);
	}

	private static function require_admin( $action ) {
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not authorized.', 'sabri-unified-application-shell' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( $action );
	}

	public static function admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$config = self::configuration();
		$approved = self::approval_valid( $config );
		echo '<div class="wrap"><h1>' . esc_html__( 'Welcome experience — File 20', 'sabri-unified-application-shell' ) . '</h1>';
		echo '<p>' . esc_html__( 'File 20 owns route/frequency state; File 25 owns visual presentation. Public activation also requires the independent canonical Founder approval assertion.', 'sabri-unified-application-shell' ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Founder approval:', 'sabri-unified-application-shell' ) . '</strong> ' . esc_html( $approved ? 'verified' : 'absent / blocked' ) . '</p>';
		echo '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post">';
		echo '<input type="hidden" name="action" value="sabri_shell_welcome_save"><input type="hidden" name="config_version" value="' . esc_attr( $config['config_version'] ) . '">';
		wp_nonce_field( self::NONCE_ACTION );
		echo '<p><label><input type="checkbox" name="enabled" value="1" ' . checked( $config['enabled'], true, false ) . '> ' . esc_html__( 'Request activation (approval gate remains mandatory)', 'sabri-unified-application-shell' ) . '</label></p>';
		foreach ( array( 'title' => 'Title', 'message' => 'Message', 'start_at' => 'Start (UTC)', 'end_at' => 'End (UTC)' ) as $key => $label ) {
			echo '<p><label>' . esc_html( $label ) . '<br><input class="regular-text" type="text" name="' . esc_attr( $key ) . '" value="' . esc_attr( $config[ $key ] ) . '"></label></p>';
		}
		echo '<p><label>' . esc_html__( 'Eligible public paths (one per line)', 'sabri-unified-application-shell' ) . '<br><textarea name="eligible_paths" rows="5" cols="55">' . esc_textarea( implode( "\n", $config['eligible_paths'] ) ) . '</textarea></label></p>';
		submit_button( __( 'Save guarded configuration', 'sabri-unified-application-shell' ) );
		echo '</form>';
		if ( self::founder_authorized( get_current_user_id() ) ) {
			echo '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post"><input type="hidden" name="action" value="' . esc_attr( self::APPROVAL_ACTION ) . '">';
			wp_nonce_field( self::APPROVAL_ACTION );
			submit_button( __( 'Approve current welcome copy and visual version as Founder', 'sabri-unified-application-shell' ), 'secondary' );
			echo '</form>';
		}
		echo '<p>' . esc_html__( 'A dismissal always suppresses automatic display for at least 30 days on that device. Preview never writes device preference.', 'sabri-unified-application-shell' ) . '</p>';
		foreach ( array( 'default', 'skipped', 'reduced', 'disabled', 'error' ) as $state ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=' . self::PREVIEW_ACTION . '&state=' . $state ), self::PREVIEW_ACTION );
			echo '<a class="button button-secondary" target="_blank" rel="noopener" href="' . esc_url( $url ) . '">' . esc_html( ucfirst( $state ) ) . '</a> ';
		}
		echo '</div>';
	}

	public static function save() {
		self::require_admin( self::NONCE_ACTION );
		if ( 'POST' !== (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			wp_die( 'POST required', '', array( 'response' => 405 ) );
		}
		$input = wp_unslash( $_POST );
		$expected = absint( $input['config_version'] ?? 0 );
		$old = get_option( self::OPTION, false );
		if ( false === $old ) {
			add_option( self::OPTION, self::defaults(), '', false );
			$old = get_option( self::OPTION, false );
		}
		$old = is_array( $old ) ? $old : self::defaults();
		$base = self::sanitize( $old, $old );
		if ( $expected !== absint( $base['config_version'] ) ) {
			wp_die( 'Configuration was modified in another session. Reload and retry.', '', array( 'response' => 409 ) );
		}
		$new = self::sanitize( array(
			'enabled' => isset( $input['enabled'] ) ? '1' : false,
			'title' => $input['title'] ?? '',
			'message' => $input['message'] ?? '',
			'start_at' => $input['start_at'] ?? '',
			'end_at' => $input['end_at'] ?? '',
			'eligible_paths' => $input['eligible_paths'] ?? '',
		), $base );
		$new['config_version'] = $expected + 1;
		$new['copy_version'] = absint( $base['copy_version'] ) + ( ( $base['title'] !== $new['title'] || $base['message'] !== $new['message'] ) ? 1 : 0 );
		global $wpdb;
		$changed = $wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->options} SET option_value=%s WHERE option_name=%s AND option_value=%s",
			maybe_serialize( $new ), self::OPTION, maybe_serialize( $old )
		) );
		if ( 1 !== $changed ) {
			wp_cache_delete( self::OPTION, 'options' );
			wp_die( 'Concurrent change detected; no configuration overwritten.', '', array( 'response' => 409 ) );
		}
		wp_cache_delete( self::OPTION, 'options' );
		delete_option( self::APPROVAL_OPTION ); // Any changed configuration invalidates the previous Founder approval.
		$audit = get_option( self::AUDIT_OPTION, array() );
		$audit = is_array( $audit ) ? $audit : array();
		$audit[] = array( 'at' => gmdate( 'c' ), 'actor' => get_current_user_id(), 'from' => $expected, 'to' => $new['config_version'], 'enabled' => $new['enabled'] );
		update_option( self::AUDIT_OPTION, array_slice( $audit, -100 ), false );
		wp_safe_redirect( admin_url( 'options-general.php?page=sabri-shell-welcome&updated=1' ) );
		exit;
	}


	/**
	 * Canonical File 00 verification for the actor making or retaining the
	 * visual/copy decision. A local WordPress administrator alone cannot
	 * impersonate Founder. File 00 MFA-retirement truth is not overridden.
	 */
	public static function founder_authorized( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! class_exists( 'SMC_Contracts' ) || ! is_callable( array( 'SMC_Contracts', 'assertions' ) ) ) {
			return false;
		}
		$assertions = \SMC_Contracts::assertions( $user_id );
		return is_array( $assertions )
			&& absint( $assertions['user_id'] ?? 0 ) === $user_id
			&& 'founder' === (string) ( $assertions['account_class'] ?? '' )
			&& true === (bool) ( $assertions['approved'] ?? false )
			&& true === (bool) ( $assertions['eligible'] ?? false )
			&& false === (bool) ( $assertions['suspended'] ?? true );
	}

	/**
	 * A change to File 25's actual renderer file invalidates the existing
	 * acceptance even if a package author forgot to bump its version.
	 */
	public static function approval_signature( array $config ) {
		if ( ! self::visual_available() ) { return ''; }
		try {
			$source = ( new \ReflectionClass( 'Sabri\\PublicExperience\\Three_Plan_Corrections' ) )->getFileName();
			$visual_hash = is_string( $source ) && is_readable( $source ) ? hash_file( 'sha256', $source ) : false;
			if ( ! is_string( $visual_hash ) || '' === $visual_hash ) { return ''; }
			return hash( 'sha256', wp_json_encode( array(
				'file20_version' => SABRI_SHELL_VERSION,
				'file25_version' => SABRI_PUBLIC_EXPERIENCE_VERSION,
				'file25_renderer_sha256' => $visual_hash,
				'config' => self::sanitize( $config, $config ),
				'frequency_days' => self::DAYS,
			) ) );
		} catch ( \Throwable $error ) {
			unset( $error );
			return '';
		}
	}

	public static function approval_valid( array $config ) {
		$approval = get_option( self::APPROVAL_OPTION, array() );
		if ( ! is_array( $approval ) || empty( $approval['signer_id'] ) || empty( $approval['signature'] ) ) { return false; }
		$expected = self::approval_signature( $config );
		return '' !== $expected
			&& is_string( $approval['signature'] )
			&& hash_equals( $expected, $approval['signature'] )
			&& self::founder_authorized( $approval['signer_id'] );
	}

	public static function approve() {
		self::require_admin( self::APPROVAL_ACTION );
		if ( 'POST' !== (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			wp_die( 'POST required', '', array( 'response' => 405 ) );
		}
		$user_id = get_current_user_id();
		if ( ! self::founder_authorized( $user_id ) ) {
			wp_die( 'A current canonical File 00 Founder assertion is required.', '', array( 'response' => 403 ) );
		}
		$config = self::configuration();
		$signature = self::approval_signature( $config );
		if ( '' === $signature ) {
			wp_die( 'File 25 source/visual owner is not available for approval.', '', array( 'response' => 503 ) );
		}
		update_option( self::APPROVAL_OPTION, array(
			'signer_id' => absint( $user_id ),
			'approved_at' => gmdate( 'c' ),
			'signature' => $signature,
		), false );
		wp_safe_redirect( admin_url( 'options-general.php?page=sabri-shell-welcome&approved=1' ) );
		exit;
	}

	public static function preview() {
		self::require_admin( self::PREVIEW_ACTION );
		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
		$state = sanitize_key( wp_unslash( $_GET['state'] ?? 'default' ) );
		if ( ! in_array( $state, array( 'default', 'skipped', 'reduced', 'disabled', 'error' ), true ) ) { $state = 'default'; }
		$panel = self::panel( self::configuration() );
		$show = ! in_array( $state, array( 'skipped', 'disabled', 'error' ), true ) && '' !== $panel;
		echo '<!doctype html><html lang="' . esc_attr( get_bloginfo( 'language' ) ) . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Welcome Preview</title><style>';
		echo self::placement_css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static CSS owned by this class.
		echo '.sabri-shell-welcome-preview-reduced *{animation:none!important;transition:none!important}';
		if ( class_exists( 'Sabri\\PublicExperience\\Three_Plan_Corrections' ) ) {
			echo \Sabri\PublicExperience\Three_Plan_Corrections::css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</style></head><body class="' . ( 'reduced' === $state ? 'sabri-shell-welcome-preview-reduced' : '' ) . '"><main><h1>Underlying content is immediately available</h1><p>Preview state: ' . esc_html( $state ) . ' (no public storage or session mutation)</p></main>';
		if ( $show ) {
			echo '<aside class="sabri-shell-welcome" style="position:relative;inset:auto;pointer-events:auto">';
			echo $panel; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- File25 escapes.
			echo '</aside>';
		}
		echo '</body></html>';
		exit;
	}
}
