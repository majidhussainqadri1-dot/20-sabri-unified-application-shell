<?php
/**
 * Public shell renderer.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders balanced standalone shell components.
 */
final class Renderer {
	/**
	 * Once-only Notifications guard.
	 *
	 * @var bool
	 */
	private static $notifications_rendered = false;

	/**
	 * Register render hooks.
	 *
	 * @return void
	 */
	public static function register() {
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );
		add_action( 'wp', array( __CLASS__, 'claim_notifications_output' ), 20 );
		add_action( 'wp_enqueue_scripts', array( 'Sabri\\UnifiedShell\\Assets', 'enqueue' ) );
		add_action( 'wp_head', array( 'Sabri\\UnifiedShell\\Assets', 'print_custom_properties' ), 20 );
		add_action( 'wp_body_open', array( __CLASS__, 'render_shell_start' ), 5 );
		add_action( 'wp_footer', array( __CLASS__, 'render_shell_footer' ), 20 );
	}

	/**
	 * Prevent File 19 from rendering a second floating bell when the shell owns
	 * the single global notification output.
	 *
	 * @return void
	 */
	public static function claim_notifications_output() {
		if ( ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) || ! is_user_logged_in() ) {
			return;
		}
		if ( class_exists( 'SUN_Shortcodes' ) ) {
			remove_action( 'wp_footer', array( 'SUN_Shortcodes', 'floating_bell' ), 30 );
		}
		do_action( 'sabri_shell_claim_notifications_output' );
	}

	/**
	 * Add scoped body classes.
	 *
	 * @param array<int,string> $classes Existing classes.
	 * @return array<int,string>
	 */
	public static function body_classes( $classes ) {
		$mode      = Layout::current_mode();
		$classes[] = 'sabri-shell-layout-' . $mode;

		if ( Layout::MINIMAL !== $mode ) {
			$classes[] = 'sabri-shell-enabled';
		}

		$settings = Settings::get();
		$layout   = isset( $settings['layout'] ) && is_array( $settings['layout'] ) ? $settings['layout'] : array();
		$classes[] = ! empty( $layout['sticky_header'] ) ? 'sabri-shell-sticky-header' : 'sabri-shell-static-header';
		$classes[] = ! empty( $layout['compact_desktop'] ) ? 'sabri-shell-compact-desktop' : 'sabri-shell-standard-desktop';

		return array_values( array_unique( array_filter( array_map( 'strval', $classes ) ) ) );
	}

	/**
	 * Render header, nav, and desktop sidebars as standalone balanced components.
	 *
	 * @return void
	 */
	public static function render_shell_start() {
		$mode = Layout::current_mode();
		if ( ! in_array( $mode, array( Layout::TWO, Layout::THREE ), true ) ) {
			return;
		}

		$settings = Settings::get();
		$nav      = Navigation::resolved();
		$has_right_sidebar = Layout::THREE === $mode && ! empty( $settings['right_sidebar']['enabled'] ) && self::right_sidebar_has_modules( $settings, $nav );

		echo '<a class="sabri-shell-skip-link" href="#sabri-shell-main-content">' . esc_html__( 'Skip to main content', 'sabri-unified-application-shell' ) . '</a>';

		if ( ! empty( $settings['header']['enabled'] ) ) {
			self::render_header( $settings, $nav );
		}

		self::render_primary_nav( $nav );

		if ( $has_right_sidebar ) {
			echo '<button type="button" class="sabri-shell-context-button" data-sabri-drawer-trigger="sabri-shell-drawer-context" data-sabri-open-label="' . esc_attr__( 'Open context panel', 'sabri-unified-application-shell' ) . '" data-sabri-close-label="' . esc_attr__( 'Close context panel', 'sabri-unified-application-shell' ) . '" aria-controls="sabri-shell-drawer-context" aria-expanded="false" aria-label="' . esc_attr__( 'Open context panel', 'sabri-unified-application-shell' ) . '">' . esc_html__( 'Context', 'sabri-unified-application-shell' ) . '</button>';
		}

		if ( ! empty( $settings['left_sidebar']['enabled'] ) ) {
			self::render_left_sidebar( $settings, $nav, 'desktop' );
		}

		if ( $has_right_sidebar ) {
			self::render_right_sidebar( $settings, $nav );
		}

		/* No-JavaScript skip target: this anchor is after every File 20 chrome
		 * component and immediately before theme-owned page content. */
		echo '<span id="sabri-shell-main-content" class="sabri-shell-main-anchor" tabindex="-1"></span>';
	}

	/**
	 * Render mobile drawers and bottom navigation.
	 *
	 * @return void
	 */
	public static function render_shell_footer() {
		$mode = Layout::current_mode();
		if ( ! in_array( $mode, array( Layout::TWO, Layout::THREE ), true ) ) {
			return;
		}

		$settings = Settings::get();
		$nav      = Navigation::resolved();
		$has_right_sidebar = Layout::THREE === $mode && ! empty( $settings['right_sidebar']['enabled'] ) && self::right_sidebar_has_modules( $settings, $nav );

		if ( ! empty( $settings['mobile']['drawers'] ) ) {
			echo '<div class="sabri-shell-drawer-overlay" data-sabri-drawer-overlay hidden></div>';
			echo '<aside id="sabri-shell-drawer-nav" class="sabri-shell-drawer" role="dialog" aria-modal="true" aria-label="' . esc_attr__( 'Navigation menu', 'sabri-unified-application-shell' ) . '" aria-hidden="true" inert>';
			echo '<button type="button" class="sabri-shell-drawer-close" data-sabri-drawer-close aria-label="' . esc_attr__( 'Close menu', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&times;</span></button>';
			self::render_left_sidebar( $settings, $nav, 'drawer' );
			echo '</aside>';

			if ( $has_right_sidebar ) {
				echo '<aside id="sabri-shell-drawer-context" class="sabri-shell-drawer sabri-shell-drawer-context" role="dialog" aria-modal="true" aria-label="' . esc_attr__( 'Context panel', 'sabri-unified-application-shell' ) . '" aria-hidden="true" inert>';
				echo '<button type="button" class="sabri-shell-drawer-close" data-sabri-drawer-close aria-label="' . esc_attr__( 'Close context panel', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&times;</span></button>';
				self::render_right_sidebar( $settings, $nav, true );
				echo '</aside>';
			}
		}

		/* The superseded duplicate mobile bottom strip is intentionally not rendered. */
	}

	/**
	 * Render the global header.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return void
	 */
	private static function render_header( array $settings, array $nav ) {
		$title = ! empty( $settings['header']['platform_title'] ) ? $settings['header']['platform_title'] : __( 'Sabri Social Homeopathy Platform', 'sabri-unified-application-shell' );

		echo '<header class="sabri-shell-header" role="banner" data-sabri-shell-component="header">';
		echo '<div class="sabri-shell-header-inner">';
		echo '<button type="button" class="sabri-shell-icon-button sabri-shell-menu-button" data-sabri-drawer-trigger="sabri-shell-drawer-nav" data-sabri-open-label="' . esc_attr__( 'Open menu', 'sabri-unified-application-shell' ) . '" data-sabri-close-label="' . esc_attr__( 'Close menu', 'sabri-unified-application-shell' ) . '" aria-controls="sabri-shell-drawer-nav" aria-expanded="false" aria-label="' . esc_attr__( 'Open menu', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&#9776;</span></button>';
		echo '<a class="sabri-shell-brand" href="' . esc_url( home_url( '/' ) ) . '"><span class="sabri-shell-logo" aria-hidden="true"><span>S</span><span class="sabri-shell-logo-separator">|</span><span>H</span></span><span class="sabri-shell-brand-text">' . esc_html( $title ) . '</span></a>';

		if ( ! empty( $settings['header']['search'] ) ) {
			FourPlanHarmonization::render_search();
		}

		echo '<nav class="sabri-shell-header-actions" aria-label="' . esc_attr__( 'Account and platform actions', 'sabri-unified-application-shell' ) . '">';

		if ( CreateContract::visible_for_current_user() ) {
			$create_url = Integrations::create_url();
			echo '<a class="sabri-shell-action sabri-shell-create-action" href="' . esc_url( $create_url ) . '">' . esc_html__( 'Create', 'sabri-unified-application-shell' ) . '</a>';
		}

		if ( ! empty( $settings['header']['messages'] ) ) {
			self::render_header_action( 'messages', __( 'Messages', 'sabri-unified-application-shell' ), $nav, $settings );
		}

		if ( ! empty( $settings['header']['notifications'] ) ) {
			self::render_notifications_once( $nav, $settings );
		}

		if ( ! empty( $settings['header']['help'] ) ) {
			self::render_header_action( 'support', __( 'Help', 'sabri-unified-application-shell' ), $nav, $settings );
		}

		if ( ! empty( $settings['header']['language'] ) ) {
			$language_switcher = Integrations::language_switcher();
			if ( $language_switcher ) {
				echo '<details class="sabri-shell-language-switcher"><summary class="sabri-shell-action">' . esc_html__( 'Language', 'sabri-unified-application-shell' ) . '</summary>';
				echo wp_kses_post( $language_switcher );
				echo '</details>';
			}
		}

		self::render_profile_or_auth( $settings );

		echo '</nav>';
		echo '</div>';
		echo '</header>';
	}


	/**
	 * Render a simple resolved header action.
	 *
	 * @param string              $key Destination key.
	 * @param string              $label Label.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_header_action( $key, $label, array $nav, array $settings ) {
		if ( ! empty( $nav[ $key ] ) && ! self::item_visible_to_user( $nav[ $key ] ) ) {
			return;
		}
		$url = self::destination_url( $key, $nav, $settings );
		if ( ! $url ) {
			return;
		}

		echo '<a class="sabri-shell-action" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
	}

	/**
	 * Render Notifications exactly once.
	 *
	 * @param array<string,mixed> $nav Resolved nav.
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_notifications_once( array $nav, array $settings ) {
		if ( self::$notifications_rendered || ! is_user_logged_in() ) {
			return;
		}
		$url = self::destination_url( 'notifications', $nav, $settings );
		if ( ! $url ) {
			return;
		}
		self::$notifications_rendered = true;
		if ( shortcode_exists( 'sabri_notification_bell' ) ) {
			echo '<span class="sabri-shell-notifications" data-sabri-notifications-output="header">' . do_shortcode( '[sabri_notification_bell]' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- owned companion shortcode output.
			return;
		}
		echo '<a class="sabri-shell-action sabri-shell-notifications" data-sabri-notifications-output="header" href="' . esc_url( $url ) . '" aria-label="' . esc_attr__( 'Notifications', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&#128276;</span><span class="screen-reader-text">' . esc_html__( 'Notifications', 'sabri-unified-application-shell' ) . '</span></a>';
	}

	/**
	 * Render profile menu or auth links.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_profile_or_auth( array $settings ) {
		if ( empty( $settings['header']['profile'] ) ) {
			return;
		}
		if ( is_user_logged_in() ) {
			$user        = wp_get_current_user();
			$profile_url = Integrations::profile_url( $user->ID );
			echo '<details class="sabri-shell-profile">';
			echo '<summary>' . esc_html( $user->display_name ? $user->display_name : __( 'Profile', 'sabri-unified-application-shell' ) ) . '</summary>';
			if ( $profile_url ) {
				echo '<a href="' . esc_url( $profile_url ) . '">' . esc_html__( 'Profile', 'sabri-unified-application-shell' ) . '</a>';
			}
			$publishing_url = PublishingDashboardEntry::url();
			if ( $publishing_url ) {
				echo '<a href="' . esc_url( $publishing_url ) . '">' . esc_html( PublishingDashboardEntry::label() ) . '</a>';
			}
			$nav = Navigation::resolved();
			foreach ( array( 'settings' => __( 'Settings', 'sabri-unified-application-shell' ), 'support' => __( 'Support', 'sabri-unified-application-shell' ) ) as $key => $label ) {
				if ( ! empty( $nav[ $key ]['url'] ) && self::item_visible_to_user( $nav[ $key ] ) ) {
					echo '<a href="' . esc_url( $nav[ $key ]['url'] ) . '">' . esc_html( $label ) . '</a>';
				}
			}
			echo '<a href="' . esc_url( wp_logout_url( home_url( '/' ) ) ) . '">' . esc_html__( 'Log out', 'sabri-unified-application-shell' ) . '</a>';
			echo '</details>';
			return;
		}
		$redirect = self::safe_login_redirect();
		echo '<a class="sabri-shell-action" href="' . esc_url( Integrations::auth_url( 'login', $redirect ) ) . '">' . esc_html__( 'Log In', 'sabri-unified-application-shell' ) . '</a>';
		$signup_url = Integrations::auth_url( 'signup' );
		if ( $signup_url ) {
			echo '<a class="sabri-shell-action" href="' . esc_url( $signup_url ) . '">' . esc_html__( 'Sign Up', 'sabri-unified-application-shell' ) . '</a>';
		}
	}

	/**
	 * Render primary horizontal nav.
	 *
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return void
	 */
	private static function render_primary_nav( array $nav ) {
		$primary_keys = array( 'home', 'news', 'founder', 'learn', 'encyclopedia', 'doctors', 'clinic', 'video_wall', 'reels', 'pdf_library', 'radar', 'ai', 'network', 'marketplace' );
		$visible = array();
		foreach ( $primary_keys as $key ) {
			if ( ! empty( $nav[ $key ] ) && self::item_visible_to_user( $nav[ $key ] ) ) {
				$visible[] = $nav[ $key ];
			}
		}
		$direct = array_slice( $visible, 0, 6 );
		$more   = array_slice( $visible, 6 );
		echo '<nav class="sabri-shell-primary-nav" aria-label="' . esc_attr__( 'Primary navigation', 'sabri-unified-application-shell' ) . '" data-sabri-shell-component="primary-nav">';
		echo '<ul>';
		foreach ( $direct as $item ) {
			self::render_nav_item( $item );
		}
		if ( ! empty( $more ) ) {
			$more_active = false;
			foreach ( $more as $item ) {
				if ( Navigation::is_active_url( $item['url'] ) ) {
					$more_active = true;
					break;
				}
			}
			echo '<li class="sabri-shell-nav-more"><details><summary' . ( $more_active ? ' aria-current="page"' : '' ) . '>' . esc_html__( 'More', 'sabri-unified-application-shell' ) . '</summary><ul class="sabri-shell-nav-more-menu">';
			foreach ( $more as $item ) {
				self::render_nav_item( $item );
			}
			echo '</ul></details></li>';
		}
		echo '</ul>';
		echo '</nav>';
	}

	/**
	 * Render left sidebar or drawer content.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @param string              $variant desktop|drawer.
	 * @return void
	 */
	private static function render_left_sidebar( array $settings, array $nav, $variant ) {
		$classes = 'sabri-shell-left-sidebar';
		if ( 'drawer' === $variant ) {
			$classes .= ' sabri-shell-left-sidebar-drawer';
		}

		echo '<aside class="' . esc_attr( $classes ) . '" aria-label="' . esc_attr__( 'Sabri navigation', 'sabri-unified-application-shell' ) . '" data-sabri-shell-component="left-sidebar">';
		self::render_user_card();
		$publishing_url = PublishingDashboardEntry::url();
		if ( $publishing_url ) {
			echo '<nav class="sabri-shell-account-tools" aria-label="' . esc_attr__( 'Publishing', 'sabri-unified-application-shell' ) . '"><a href="' . esc_url( $publishing_url ) . '">' . esc_html( PublishingDashboardEntry::label() ) . '</a></nav>';
		}

		$groups = Defaults::groups();
		foreach ( $groups as $group_key => $group_label ) {
			if ( empty( $settings['left_sidebar']['groups'][ $group_key ] ) ) {
				continue;
			}

			$group_items = array_filter(
				$nav,
				static function ( $item ) use ( $group_key, $settings ) {
					return isset( $item['group'] ) && $group_key === $item['group'] && ! empty( $settings['left_sidebar']['items'][ $item['key'] ] );
				}
			);

			$group_items = array_filter( $group_items, array( __CLASS__, 'item_visible_to_user' ) );
			if ( empty( $group_items ) ) {
				continue;
			}

			echo '<section class="sabri-shell-sidebar-group">';
			echo '<h2>' . esc_html( $group_label ) . '</h2>';
			echo '<ul>';
			foreach ( $group_items as $item ) {
				if ( self::item_visible_to_user( $item ) ) {
					self::render_nav_item( $item );
				}
			}
			echo '</ul>';
			echo '</section>';
		}

		self::render_left_footer_links( $settings );
		echo '</aside>';
	}

	/**
	 * Render user or visitor card.
	 *
	 * @return void
	 */
	private static function render_user_card() {
		echo '<div class="sabri-shell-user-card">';
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			echo get_avatar( $user->ID, 48, '', '', array( 'class' => 'sabri-shell-avatar' ) );
			echo '<div><strong>' . esc_html( $user->display_name ) . '</strong><span>' . esc_html__( 'Signed in', 'sabri-unified-application-shell' ) . '</span></div>';
		} else {
			$redirect = self::safe_login_redirect();
			echo '<strong>' . esc_html__( 'Welcome', 'sabri-unified-application-shell' ) . '</strong>';
			echo '<p>' . esc_html__( 'Log in to access your account tools.', 'sabri-unified-application-shell' ) . '</p>';
			echo '<a href="' . esc_url( Integrations::auth_url( 'login', $redirect ) ) . '">' . esc_html__( 'Log In', 'sabri-unified-application-shell' ) . '</a>';
			if ( Integrations::auth_url( 'signup' ) ) {
				echo '<a href="' . esc_url( Integrations::auth_url( 'signup' ) ) . '">' . esc_html__( 'Create Account', 'sabri-unified-application-shell' ) . '</a>';
			}
		}
		echo '</div>';
	}

	/**
	 * Render footer links.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_left_footer_links( array $settings ) {
		$labels = array(
			'privacy'    => __( 'Privacy', 'sabri-unified-application-shell' ),
			'terms'      => __( 'Terms', 'sabri-unified-application-shell' ),
			'disclaimer' => __( 'Medical Disclaimer', 'sabri-unified-application-shell' ),
			'guidelines' => __( 'Community Guidelines', 'sabri-unified-application-shell' ),
			'contact'    => __( 'Contact', 'sabri-unified-application-shell' ),
			'whatsapp'   => __( 'WhatsApp', 'sabri-unified-application-shell' ),
		);

		echo '<nav class="sabri-shell-sidebar-footer" aria-label="' . esc_attr__( 'Footer links', 'sabri-unified-application-shell' ) . '">';
		foreach ( $labels as $key => $label ) {
			$url = isset( $settings['left_sidebar']['footer_mappings'][ $key ] ) ? Settings::sanitize_url( $settings['left_sidebar']['footer_mappings'][ $key ] ) : '';
			if ( $url ) {
				echo '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
			}
		}
		echo '</nav>';
	}

	/**
	 * Render a nav item.
	 *
	 * @param array<string,mixed> $item Item.
	 * @return void
	 */
	private static function render_nav_item( array $item ) {
		$active = Navigation::is_active_url( $item['url'] );
		echo '<li>';
		echo '<a href="' . esc_url( $item['url'] ) . '"' . ( $active ? ' aria-current="page"' : '' ) . '>';
		echo esc_html( $item['label'] );
		echo '</a>';
		echo '</li>';
	}

	/**
	 * Determine whether the right sidebar has real output for this request.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return bool
	 */
	private static function right_sidebar_has_modules( array $settings, array $nav ) {
		$context = self::right_sidebar_context( $settings );

		if ( 'home' === $context ) {
			return self::home_right_sidebar_has_modules( $settings, $nav );
		}

		if ( 'clinic' === $context ) {
			return self::clinic_directory_sidebar_has_modules( $settings );
		}

		return self::single_clinic_sidebar_has_modules( $settings );
	}

	/**
	 * Resolve the current right sidebar context.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return string
	 */
	private static function right_sidebar_context( array $settings ) {
		if ( function_exists( 'is_front_page' ) && is_front_page() ) {
			return 'home';
		}

		if ( self::is_clinic_directory( $settings ) ) {
			return 'clinic';
		}

		return 'single';
	}

	/**
	 * Home right sidebar availability.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return bool
	 */
	private static function home_right_sidebar_has_modules( array $settings, array $nav ) {
		$modules = $settings['right_sidebar']['home_modules'];

		if ( ! empty( $modules['founder'] ) && ! empty( $nav['founder']['url'] ) ) {
			return true;
		}
		if ( ! empty( $modules['announcement'] ) && ! empty( $settings['right_sidebar']['announcement'] ) ) {
			return true;
		}
		if ( ! empty( $modules['network'] ) && ! empty( $nav['network']['url'] ) && ! empty( Integrations::detect()['network'] ) ) {
			return true;
		}
		if ( ! empty( $modules['quick_access'] ) && self::has_quick_access( $nav ) ) {
			return true;
		}

		return self::has_missing_admin_modules( $settings, 'home' );
	}

	/**
	 * Clinic directory right sidebar availability.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return bool
	 */
	private static function clinic_directory_sidebar_has_modules( array $settings ) {
		$modules = $settings['right_sidebar']['clinic_modules'];

		if ( ! empty( $modules['appointments'] ) && ! empty( Integrations::detect()['appointments'] ) && self::destination_url( 'appointments', Navigation::resolved(), $settings ) ) {
			return true;
		}
		if ( ! empty( $modules['whatsapp'] ) && ! empty( $settings['integrations']['urls']['whatsapp'] ) ) {
			return true;
		}

		return self::has_missing_admin_modules( $settings, 'clinic' );
	}

	/**
	 * Single clinic right sidebar availability.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return bool
	 */
	private static function single_clinic_sidebar_has_modules( array $settings ) {
		$modules = $settings['right_sidebar']['single_modules'];
		$post_id  = function_exists( 'get_the_ID' ) ? absint( get_the_ID() ) : 0;
		$user_id  = self::current_doctor_user_id();

		if ( ! $post_id && ! $user_id ) {
			return self::has_missing_admin_modules( $settings, 'single' );
		}

		if ( ! empty( $modules['profile'] ) && self::has_single_profile_data( $post_id ) ) {
			return true;
		}
		if ( ! empty( $modules['appointment'] ) && ! empty( Integrations::detect()['appointments'] ) && self::destination_url( 'appointments', Navigation::resolved(), $settings ) ) {
			return true;
		}
		if ( ! empty( $modules['message'] ) && is_user_logged_in() && ! empty( Integrations::detect()['messages'] ) && self::destination_url( 'messages', Navigation::resolved(), $settings ) ) {
			return true;
		}
		if ( ! empty( $modules['contact'] ) && self::has_public_contact_data( $post_id ) ) {
			return true;
		}
		return self::has_missing_admin_modules( $settings, 'single' );
	}

	/**
	 * Whether missing module notices may render for administrators.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return bool
	 */
	private static function should_render_missing_admin_notice( array $settings ) {
		return empty( $settings['right_sidebar']['hide_missing'] ) && is_user_logged_in() && current_user_can( 'manage_options' );
	}

	/**
	 * Whether missing module notices have real content.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $context Context.
	 * @return bool
	 */
	private static function has_missing_admin_modules( array $settings, $context ) {
		return self::should_render_missing_admin_notice( $settings ) && ! empty( self::right_sidebar_missing_modules( $settings, $context ) );
	}

	/**
	 * Check quick access availability.
	 *
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return bool
	 */
	private static function has_quick_access( array $nav ) {
		foreach ( array( 'clinic', 'doctors', 'appointments', 'encyclopedia', 'support' ) as $key ) {
			if ( ! empty( $nav[ $key ] ) && self::item_visible_to_user( $nav[ $key ] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Resolve the doctor represented by the current public request.
	 *
	 * @return int
	 */
	private static function current_doctor_user_id() {
		/* File 20 must not infer a doctor subject from query strings, post authors or role labels. */
		$user_id = apply_filters( 'sabri_shell_current_doctor_user_id', 0 );
		$user_id = absint( $user_id );
		return $user_id && Integrations::is_verified_doctor( $user_id ) ? $user_id : 0;
	}

	/**
	 * Check single profile fields from authoritative user data.
	 *
	 * @param int $post_id Compatibility argument.
	 * @return bool
	 */
	private static function has_single_profile_data( $post_id ) {
		unset( $post_id );
		$data = Integrations::doctor_public_data( self::current_doctor_user_id() );
		foreach ( array( 'name', 'clinic', 'fee', 'timings', 'languages', 'specialty' ) as $field ) {
			if ( ! empty( $data[ $field ] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check public contact fields from authoritative user data.
	 *
	 * @param int $post_id Compatibility argument.
	 * @return bool
	 */
	private static function has_public_contact_data( $post_id ) {
		unset( $post_id );
		$data = Integrations::doctor_public_data( self::current_doctor_user_id() );
		return ! empty( $data['phone'] ) || ! empty( $data['whatsapp'] );
	}

	/**
	 * Render right sidebar.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @param bool                $inside_drawer Whether rendering in drawer.
	 * @return void
	 */
	private static function render_right_sidebar( array $settings, array $nav, $inside_drawer = false ) {
		$classes = 'sabri-shell-right-sidebar';
		if ( $inside_drawer ) {
			$classes .= ' sabri-shell-right-sidebar-drawer';
		}

		$context = self::right_sidebar_context( $settings );
		echo '<aside class="' . esc_attr( $classes ) . '" aria-label="' . esc_attr__( 'Context sidebar', 'sabri-unified-application-shell' ) . '" data-sabri-shell-component="right-sidebar" data-sabri-right-context="' . esc_attr( $context ) . '">';

		if ( 'home' === $context ) {
			self::render_home_right_sidebar( $settings, $nav );
		} elseif ( 'clinic' === $context ) {
			self::render_clinic_directory_sidebar( $settings );
		} else {
			self::render_single_clinic_sidebar( $settings );
		}

		if ( self::should_render_missing_admin_notice( $settings ) ) {
			self::render_missing_admin_panel( $settings, $context );
		}

		echo '</aside>';
	}

	/**
	 * Render home right sidebar modules using real data only.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return void
	 */
	private static function render_home_right_sidebar( array $settings, array $nav ) {
		$modules = $settings['right_sidebar']['home_modules'];

		if ( ! empty( $modules['founder'] ) && ! empty( $nav['founder']['url'] ) ) {
			self::render_panel( __( 'Founder', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $nav['founder']['url'] ) . '">' . esc_html( $nav['founder']['label'] ) . '</a></p>', 'home-founder' );
		}

		if ( ! empty( $modules['announcement'] ) && ! empty( $settings['right_sidebar']['announcement'] ) ) {
			self::render_panel( __( 'Announcement', 'sabri-unified-application-shell' ), '<p>' . esc_html( $settings['right_sidebar']['announcement'] ) . '</p>', 'home-announcement' );
		}

		if ( ! empty( $modules['network'] ) && ! empty( $nav['network']['url'] ) && ! empty( Integrations::detect()['network'] ) ) {
			self::render_panel( __( 'Network', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $nav['network']['url'] ) . '">' . esc_html__( 'Open Network', 'sabri-unified-application-shell' ) . '</a></p>', 'home-network' );
		}

		if ( ! empty( $modules['quick_access'] ) ) {
			self::render_quick_access_panel( $nav );
		}
	}

	/**
	 * Render an administrator-only notice for enabled modules with missing real data.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $context Context.
	 * @return void
	 */
	private static function render_missing_admin_panel( array $settings, $context ) {
		if ( ! self::should_render_missing_admin_notice( $settings ) ) {
			return;
		}

		$missing = self::right_sidebar_missing_modules( $settings, $context );
		if ( empty( $missing ) ) {
			return;
		}

		self::render_panel(
			__( 'Missing Modules', 'sabri-unified-application-shell' ),
			'<p>' . esc_html__( 'Enabled modules hidden because real data or integrations are unavailable:', 'sabri-unified-application-shell' ) . ' ' . esc_html( implode( ', ', $missing ) ) . '</p>',
			'missing-admin'
		);
	}

	/**
	 * List enabled modules that do not have real public data.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $context Context.
	 * @return array<int,string>
	 */
	private static function right_sidebar_missing_modules( array $settings, $context ) {
		$missing = array();
		if ( 'clinic' === $context ) {
			$modules = $settings['right_sidebar']['clinic_modules'];
			if ( ! empty( $modules['appointments'] ) && ( empty( Integrations::detect()['appointments'] ) || empty( self::destination_url( 'appointments', Navigation::resolved(), $settings ) ) ) ) {
				$missing[] = __( 'appointments', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['whatsapp'] ) && empty( $settings['integrations']['urls']['whatsapp'] ) ) {
				$missing[] = __( 'WhatsApp', 'sabri-unified-application-shell' );
			}
		} elseif ( 'single' === $context ) {
			$modules = $settings['right_sidebar']['single_modules'];
			$post_id = function_exists( 'get_the_ID' ) ? absint( get_the_ID() ) : 0;
			if ( ! empty( $modules['profile'] ) && ! self::has_single_profile_data( $post_id ) ) {
				$missing[] = __( 'profile data', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['appointment'] ) && ( empty( Integrations::detect()['appointments'] ) || empty( self::destination_url( 'appointments', Navigation::resolved(), $settings ) ) ) ) {
				$missing[] = __( 'appointment integration', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['message'] ) && ( empty( Integrations::detect()['messages'] ) || empty( self::destination_url( 'messages', Navigation::resolved(), $settings ) ) ) ) {
				$missing[] = __( 'message integration', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['contact'] ) && ! self::has_public_contact_data( $post_id ) ) {
				$missing[] = __( 'public contact fields', 'sabri-unified-application-shell' );
			}
		} else {
			$modules = $settings['right_sidebar']['home_modules'];
		}

		return $missing;
	}

	/**
	 * Render clinic directory sidebar.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_clinic_directory_sidebar( array $settings ) {
		$modules = $settings['right_sidebar']['clinic_modules'];
		if ( ! empty( $modules['appointments'] ) && ! empty( Integrations::detect()['appointments'] ) && self::destination_url( 'appointments', Navigation::resolved(), $settings ) ) {
			self::render_panel( __( 'Appointment Help', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( self::destination_url( 'appointments', Navigation::resolved(), $settings ) ) . '">' . esc_html__( 'Open appointment support', 'sabri-unified-application-shell' ) . '</a></p>', 'clinic-appointments' );
		}
		if ( ! empty( $modules['whatsapp'] ) && ! empty( $settings['integrations']['urls']['whatsapp'] ) ) {
			self::render_panel( __( 'WhatsApp Help', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $settings['integrations']['urls']['whatsapp'] ) . '">' . esc_html__( 'Open WhatsApp help', 'sabri-unified-application-shell' ) . '</a></p>', 'clinic-whatsapp' );
		}
	}

	/**
	 * Render single doctor or clinic sidebar.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_single_clinic_sidebar( array $settings ) {
		$modules = $settings['right_sidebar']['single_modules'];
		$user_id = self::current_doctor_user_id();
		$data    = $user_id ? Integrations::doctor_public_data( $user_id ) : array();
		$post_id = function_exists( 'get_the_ID' ) ? absint( get_the_ID() ) : 0;

		if ( ! empty( $modules['profile'] ) && $data ) {
			$title  = ! empty( $data['name'] ) ? $data['name'] : __( 'Doctor Profile', 'sabri-unified-application-shell' );
			$fields = array(
				'clinic'    => __( 'Clinic', 'sabri-unified-application-shell' ),
				'specialty' => __( 'Specialty', 'sabri-unified-application-shell' ),
				'languages' => __( 'Languages', 'sabri-unified-application-shell' ),
				'timings'   => __( 'Timings', 'sabri-unified-application-shell' ),
				'fee'       => __( 'Fee', 'sabri-unified-application-shell' ),
			);
			echo '<section class="sabri-shell-panel" data-sabri-right-module="single-profile"><h2>' . esc_html( $title ) . '</h2><dl>';
			foreach ( $fields as $key => $label ) {
				if ( empty( $data[ $key ] ) ) {
					continue;
				}
				$value = (string) $data[ $key ];
				if ( 'fee' === $key && ! empty( $data['currency'] ) ) {
					$value .= ' ' . $data['currency'];
				}
				echo '<dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $value ) . '</dd>';
			}
			echo '</dl>';
			if ( ! empty( $data['profile'] ) ) {
				echo '<p><a href="' . esc_url( $data['profile'] ) . '">' . esc_html__( 'Open full profile', 'sabri-unified-application-shell' ) . '</a></p>';
			}
			echo '</section>';
		}

		$appointment_url = self::destination_url( 'appointments', Navigation::resolved(), $settings );
		if ( ! empty( $modules['appointment'] ) && is_user_logged_in() && $appointment_url ) {
			$native_appointment = apply_filters( 'sabri_shell_doctor_appointment_url', $appointment_url, $user_id );
			$native_appointment = Integrations::same_site_url( $native_appointment );
			if ( $native_appointment ) {
				self::render_panel( __( 'Appointment', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $native_appointment ) . '">' . esc_html__( 'Request an appointment', 'sabri-unified-application-shell' ) . '</a></p>', 'single-appointment' );
			}
		}

		$message_url = self::destination_url( 'messages', Navigation::resolved(), $settings );
		if ( ! empty( $modules['message'] ) && is_user_logged_in() && $message_url ) {
			$native_message = apply_filters( 'sabri_shell_doctor_message_url', $message_url, $user_id );
			$native_message = Integrations::same_site_url( $native_message );
			if ( $native_message ) {
				self::render_panel( __( 'Message', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $native_message ) . '">' . esc_html__( 'Open messages', 'sabri-unified-application-shell' ) . '</a></p>', 'single-message' );
			}
		}

		if ( ! empty( $modules['contact'] ) && ( ! empty( $data['phone'] ) || ! empty( $data['whatsapp'] ) ) ) {
			$html = '<dl>';
			if ( ! empty( $data['phone'] ) ) {
				$html .= '<dt>' . esc_html__( 'Phone', 'sabri-unified-application-shell' ) . '</dt><dd><a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $data['phone'] ) ) . '">' . esc_html( $data['phone'] ) . '</a></dd>';
			}
			if ( ! empty( $data['whatsapp'] ) ) {
				$digits = preg_replace( '/\D+/', '', $data['whatsapp'] );
				$html  .= '<dt>' . esc_html__( 'WhatsApp', 'sabri-unified-application-shell' ) . '</dt><dd><a href="' . esc_url( 'https://wa.me/' . $digits ) . '" rel="noopener noreferrer">' . esc_html( $data['whatsapp'] ) . '</a></dd>';
			}
			$html .= '</dl>';
			self::render_panel( __( 'Public Contact', 'sabri-unified-application-shell' ), $html, 'single-contact' );
		}

	}

	/**
	 * Render quick access panel.
	 *
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return void
	 */
	private static function render_quick_access_panel( array $nav ) {
		$keys = array( 'clinic', 'doctors', 'appointments', 'encyclopedia', 'support' );
		$html = '<ul>';
		$count = 0;
		foreach ( $keys as $key ) {
			if ( empty( $nav[ $key ] ) || ! self::item_visible_to_user( $nav[ $key ] ) ) {
				continue;
			}
			$html .= '<li><a href="' . esc_url( $nav[ $key ]['url'] ) . '">' . esc_html( $nav[ $key ]['label'] ) . '</a></li>';
			$count++;
		}
		$html .= '</ul>';

		if ( $count ) {
			self::render_panel( __( 'Quick Access', 'sabri-unified-application-shell' ), $html, 'home-quick-access' );
		}
	}

	/**
	 * Safe login redirect using validated referrer/request URI/home fallback.
	 *
	 * @return string
	 */
	private static function safe_login_redirect() {
		$home     = home_url( '/' );
		$referrer = wp_get_referer();
		if ( $referrer ) {
			$validated = Integrations::same_site_url( $referrer );
			if ( $validated ) {
				return $validated;
			}
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		if ( is_string( $request_uri ) && 0 === strpos( $request_uri, '/' ) && 0 !== strpos( $request_uri, '//' ) ) {
			$validated = Integrations::same_site_url( home_url( $request_uri ) );
			if ( $validated ) {
				return $validated;
			}
		}

		return $home;
	}

	/**
	 * Whether current page is the configured clinic directory.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return bool
	 */
	private static function is_clinic_directory( array $settings ) {
		$page_id        = Layout::current_page_id();
		$clinic_page_id = ! empty( $settings['layout']['worldwide_clinic_page_id'] ) ? absint( $settings['layout']['worldwide_clinic_page_id'] ) : Integrations::page_id( 'clinic' );
		return $page_id && $clinic_page_id && $clinic_page_id === $page_id;
	}
}
