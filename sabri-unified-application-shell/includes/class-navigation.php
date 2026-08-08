<?php
/**
 * Navigation resolution and cache invalidation.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Resolves shell navigation against authoritative platform destinations. */
final class Navigation {
	const CACHE_EPOCH_OPTION = 'sabri_shell_navigation_cache_epoch';

	/** @var array<string,array<string,mixed>>|null */
	private static $resolved = null;

	public static function register_cache_hooks() {
		add_action( 'update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'invalidate_cache' ) );
		foreach ( array( 'spf_page_map', 'spd_page_map', 'sdd_page_map', 'swc_page_map', 'svw_page_map', 'srl_page_map', 'spl_page_map', 'srf_page_map', 'sai_page_map', 'sa_page_map', 'snp_page_map', 'sn_page_map', 'sn_network_page_id', 'smp_marketplace_page_id', 'page_on_front', 'page_for_posts', 'show_on_front', 'permalink_structure' ) as $option ) {
			add_action( 'update_option_' . $option, array( __CLASS__, 'invalidate_cache' ) );
		}
		add_action( 'save_post_page', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'trashed_post', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'untrashed_post', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'deleted_post', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'registered_post_type', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'activated_plugin', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'deactivated_plugin', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'switch_theme', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'pll_language_defined', array( __CLASS__, 'invalidate_cache' ) );
	}

	public static function invalidate_cache() {
		self::$resolved = null;
		Integrations::invalidate_cache();
		$epoch = absint( get_option( self::CACHE_EPOCH_OPTION, 1 ) );
		update_option( self::CACHE_EPOCH_OPTION, $epoch + 1, false );
		delete_transient( Defaults::NAV_CACHE_KEY );
	}

	public static function resolved() {
		if ( is_array( self::$resolved ) ) {
			return self::$resolved;
		}

		$locale    = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
		$epoch     = absint( get_option( self::CACHE_EPOCH_OPTION, 1 ) );
		$cache_key = Defaults::NAV_CACHE_KEY . '_' . md5( $locale . '|' . $epoch );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			self::$resolved = $cached;
			return $cached;
		}

		$settings     = Settings::get();
		$destinations = apply_filters( 'sabri_shell_navigation_destinations', Defaults::destinations() );
		$items        = array();

		foreach ( $destinations as $key => $destination ) {
			$config = isset( $settings['navigation'][ $key ] ) && is_array( $settings['navigation'][ $key ] ) ? $settings['navigation'][ $key ] : array();
			if ( isset( $config['enabled'] ) && ! $config['enabled'] ) {
				continue;
			}
			$item = self::resolve_item( $key, $destination, $config );
			if ( ! empty( $item['url'] ) ) {
				$items[ $key ] = $item;
			}
		}

		uasort( $items, static function ( $a, $b ) { return (int) $a['order'] <=> (int) $b['order']; } );
		set_transient( $cache_key, $items, HOUR_IN_SECONDS );
		self::$resolved = $items;
		return $items;
	}

	/**
	 * Resolve exactly in the governing order:
	 * 1 configured/registered published Page ID;
	 * 2 published shortcode page;
	 * 3 canonical archive;
	 * 4 approved slug;
	 * 5 strict validated explicit override;
	 * 6 unavailable.
	 */
	public static function resolve_item( $key, array $destination, array $config ) {
		$label = ! empty( $config['label'] ) ? $config['label'] : $destination['label'];
		$order = isset( $config['order'] ) ? (int) $config['order'] : ( isset( $destination['order'] ) ? (int) $destination['order'] : 999 );
		$item  = array(
			'key'             => $key,
			'label'           => $label,
			'url'             => '',
			'reason'          => 'unavailable',
			'source_priority' => 6,
			'order'           => $order,
			'group'           => isset( $destination['group'] ) ? $destination['group'] : 'platform',
			'visibility'      => isset( $destination['visibility'] ) ? $destination['visibility'] : 'public',
		);

		/* Priority 1a: explicit File 20 configured published Page ID. */
		$page_id = isset( $config['page_id'] ) ? absint( $config['page_id'] ) : 0;
		$url = self::published_page_url( $page_id );
		if ( $url ) {
			$item['url'] = $url;
			$item['reason'] = 'configured_page_id';
			$item['source_priority'] = 1;
			return $item;
		}

		/* Priority 1b: a registered companion page-map is still Page-ID evidence. */
		$registered_page_id = Integrations::page_id( $key );
		$url = self::published_page_url( $registered_page_id );
		if ( $url ) {
			$item['url'] = $url;
			$item['reason'] = 'registered_page_map';
			$item['source_priority'] = 1;
			return $item;
		}

		/* WordPress front-page mapping is canonical Page-ID evidence for Home. */
		if ( 'home' === $key && 'page' === get_option( 'show_on_front' ) ) {
			$url = self::published_page_url( absint( get_option( 'page_on_front' ) ) );
			if ( $url ) {
				$item['url'] = $url;
				$item['reason'] = 'wordpress_front_page_id';
				$item['source_priority'] = 1;
				return $item;
			}
		}

		/* Priority 2: a published page containing a registered/detected shortcode. */
		$shortcodes = array();
		if ( ! empty( $config['shortcode'] ) ) {
			$shortcodes[] = sanitize_key( $config['shortcode'] );
		}
		if ( ! empty( $destination['shortcodes'] ) && is_array( $destination['shortcodes'] ) ) {
			$shortcodes = array_merge( $shortcodes, $destination['shortcodes'] );
		}
		$url = self::find_page_by_shortcodes( array_filter( array_unique( $shortcodes ) ) );
		if ( $url ) {
			$item['url'] = $url;
			$item['reason'] = 'page_shortcode';
			$item['source_priority'] = 2;
			return $item;
		}

		/* Priority 3: canonical post-type/WordPress posts archive. */
		$post_type = isset( $destination['post_type'] ) ? sanitize_key( $destination['post_type'] ) : '';
		if ( $post_type && 'post' !== $post_type && post_type_exists( $post_type ) ) {
			$archive = get_post_type_archive_link( $post_type );
			if ( $archive ) {
				$item['url'] = $archive;
				$item['reason'] = 'post_type_archive';
				$item['source_priority'] = 3;
				return $item;
			}
		}
		if ( 'post' === $post_type ) {
			$url = self::published_page_url( absint( get_option( 'page_for_posts' ) ) );
			if ( $url ) {
				$item['url'] = $url;
				$item['reason'] = 'posts_page';
				$item['source_priority'] = 3;
				return $item;
			}
		}
		if ( 'home' === $key && 'posts' === get_option( 'show_on_front' ) ) {
			$item['url'] = home_url( '/' );
			$item['reason'] = 'wordpress_posts_home_archive';
			$item['source_priority'] = 3;
			return $item;
		}

		/* Priority 4: approved slug candidates. */
		$slugs = array();
		if ( ! empty( $config['slug'] ) ) {
			$slugs[] = sanitize_title( $config['slug'] );
		}
		if ( ! empty( $destination['slugs'] ) && is_array( $destination['slugs'] ) ) {
			$slugs = array_merge( $slugs, $destination['slugs'] );
		}
		$url = self::find_page_by_slugs( array_filter( array_unique( $slugs ) ) );
		if ( $url ) {
			$item['url'] = $url;
			$item['reason'] = 'slug_match';
			$item['source_priority'] = 4;
			return $item;
		}

		/* Priority 5: explicit strict validated URL override only. */
		if ( ! empty( $config['url_override'] ) ) {
			$url = RouteSecurity::sanitize_override( $config['url_override'] );
			if ( $url ) {
				$item['url'] = $url;
				$item['reason'] = 'validated_url_override';
				$item['source_priority'] = 5;
				return $item;
			}
		}

		/* Priority 6: honest unavailable; never manufacture a dead/hash URL. */
		return $item;
	}

	private static function published_page_url( $page_id ) {
		if ( ! $page_id || 'publish' !== get_post_status( $page_id ) || 'page' !== get_post_type( $page_id ) ) {
			return '';
		}
		$url = get_permalink( $page_id );
		return $url ? (string) $url : '';
	}

	private static function find_page_by_shortcodes( array $shortcodes ) {
		return Integrations::find_page_by_shortcodes( $shortcodes );
	}

	private static function find_page_by_slugs( array $slugs ) {
		foreach ( $slugs as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page && 'page' === get_post_type( $page ) && 'publish' === get_post_status( $page ) ) {
				return (string) get_permalink( $page );
			}
		}
		return '';
	}

	public static function is_active_url( $url ) {
		$current = self::current_url_path();
		$target  = wp_parse_url( $url, PHP_URL_PATH );
		if ( ! $target ) { $target = '/'; }
		return untrailingslashit( $current ) === untrailingslashit( $target );
	}

	private static function current_url_path() {
		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$path    = wp_parse_url( $request, PHP_URL_PATH );
		return $path ? (string) $path : '/';
	}
}
