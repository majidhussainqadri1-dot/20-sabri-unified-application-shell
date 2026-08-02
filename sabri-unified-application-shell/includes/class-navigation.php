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

/**
 * Resolves shell navigation against authoritative platform destinations.
 */
final class Navigation {
	const CACHE_EPOCH_OPTION = 'sabri_shell_navigation_cache_epoch';

	/**
	 * Request-level resolved cache.
	 *
	 * @var array<string,array<string,mixed>>|null
	 */
	private static $resolved = null;

	/**
	 * Register cache invalidation hooks.
	 *
	 * @return void
	 */
	public static function register_cache_hooks() {
		add_action( 'update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'invalidate_cache' ) );
		foreach ( array( 'spf_page_map', 'spd_page_map', 'sdd_page_map', 'swc_page_map', 'svw_page_map', 'srl_page_map', 'spl_page_map', 'srf_page_map', 'sai_page_map', 'sa_page_map', 'snp_page_map', 'sn_network_page_id', 'smp_marketplace_page_id', 'page_on_front', 'page_for_posts', 'show_on_front', 'permalink_structure' ) as $option ) {
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

	/**
	 * Invalidate all logical navigation caches.
	 *
	 * @return void
	 */
	public static function invalidate_cache() {
		self::$resolved = null;
		Integrations::invalidate_cache();
		$epoch = absint( get_option( self::CACHE_EPOCH_OPTION, 1 ) );
		update_option( self::CACHE_EPOCH_OPTION, $epoch + 1, false );
		delete_transient( Defaults::NAV_CACHE_KEY );
	}

	/**
	 * Get resolved navigation.
	 *
	 * @return array<string,array<string,mixed>>
	 */
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

		uasort(
			$items,
			static function ( $a, $b ) {
				return (int) $a['order'] <=> (int) $b['order'];
			}
		);

		set_transient( $cache_key, $items, HOUR_IN_SECONDS );
		self::$resolved = $items;
		return $items;
	}

	/**
	 * Resolve one navigation item using controlled precedence.
	 *
	 * @param string              $key Destination key.
	 * @param array<string,mixed> $destination Destination defaults.
	 * @param array<string,mixed> $config Admin config.
	 * @return array<string,mixed>
	 */
	public static function resolve_item( $key, array $destination, array $config ) {
		$label = ! empty( $config['label'] ) ? $config['label'] : $destination['label'];
		$order = isset( $config['order'] ) ? (int) $config['order'] : ( isset( $destination['order'] ) ? (int) $destination['order'] : 999 );
		$item  = array(
			'key'        => $key,
			'label'      => $label,
			'url'        => '',
			'reason'     => 'unresolved',
			'order'      => $order,
			'group'      => isset( $destination['group'] ) ? $destination['group'] : 'platform',
			'visibility' => isset( $destination['visibility'] ) ? $destination['visibility'] : 'public',
		);

		$page_id = isset( $config['page_id'] ) ? absint( $config['page_id'] ) : 0;
		$url     = self::published_page_url( $page_id );
		if ( $url ) {
			$item['url']    = $url;
			$item['reason'] = 'configured_page_id';
			return $item;
		}

		$url = Integrations::destination_url( $key );
		if ( ! $url ) {
			$url = Integrations::page_url( $key );
		}
		if ( $url ) {
			$item['url']    = $url;
			$item['reason'] = 'companion_contract';
			return $item;
		}

		$shortcodes = array();
		if ( ! empty( $config['shortcode'] ) ) {
			$shortcodes[] = sanitize_key( $config['shortcode'] );
		}
		if ( ! empty( $destination['shortcodes'] ) && is_array( $destination['shortcodes'] ) ) {
			$shortcodes = array_merge( $shortcodes, $destination['shortcodes'] );
		}
		$url = self::find_page_by_shortcodes( array_filter( array_unique( $shortcodes ) ) );
		if ( $url ) {
			$item['url']    = $url;
			$item['reason'] = 'page_shortcode';
			return $item;
		}

		$post_type = isset( $destination['post_type'] ) ? sanitize_key( $destination['post_type'] ) : '';
		if ( $post_type && 'post' !== $post_type && post_type_exists( $post_type ) ) {
			$archive = get_post_type_archive_link( $post_type );
			if ( $archive ) {
				$item['url']    = $archive;
				$item['reason'] = 'post_type_archive';
				return $item;
			}
		}

		if ( 'post' === $post_type ) {
			$posts_page = absint( get_option( 'page_for_posts' ) );
			$url        = self::published_page_url( $posts_page );
			if ( $url ) {
				$item['url']    = $url;
				$item['reason'] = 'posts_page';
				return $item;
			}
		}

		$slugs = array();
		if ( ! empty( $config['slug'] ) ) {
			$slugs[] = sanitize_title( $config['slug'] );
		}
		if ( ! empty( $destination['slugs'] ) && is_array( $destination['slugs'] ) ) {
			$slugs = array_merge( $slugs, $destination['slugs'] );
		}
		$url = self::find_page_by_slugs( array_filter( array_unique( $slugs ) ) );
		if ( $url ) {
			$item['url']    = $url;
			$item['reason'] = 'slug_match';
			return $item;
		}

		if ( ! empty( $config['url_override'] ) ) {
			$url = Settings::sanitize_url( $config['url_override'] );
			if ( $url ) {
				$item['url']    = $url;
				$item['reason'] = 'url_override';
				return $item;
			}
		}

		if ( 'home' === $key ) {
			$item['url']    = home_url( '/' );
			$item['reason'] = 'home_url';
		}

		return $item;
	}

	/**
	 * Return URL for a published page.
	 *
	 * @param int $page_id Page ID.
	 * @return string
	 */
	private static function published_page_url( $page_id ) {
		if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
			return '';
		}
		$url = get_permalink( $page_id );
		return $url ? (string) $url : '';
	}

	/**
	 * Find a published page containing any shortcode.
	 *
	 * @param array<int,string> $shortcodes Shortcode names.
	 * @return string
	 */
	private static function find_page_by_shortcodes( array $shortcodes ) {
		return Integrations::find_page_by_shortcodes( $shortcodes );
	}

	/**
	 * Find a published page by slug candidates.
	 *
	 * @param array<int,string> $slugs Slug candidates.
	 * @return string
	 */
	private static function find_page_by_slugs( array $slugs ) {
		foreach ( $slugs as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page && 'publish' === get_post_status( $page ) ) {
				return (string) get_permalink( $page );
			}
		}
		return '';
	}

	/**
	 * Check whether URL should be marked active.
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	public static function is_active_url( $url ) {
		$current = self::current_url_path();
		$target  = wp_parse_url( $url, PHP_URL_PATH );
		if ( ! $target ) {
			$target = '/';
		}
		return untrailingslashit( $current ) === untrailingslashit( $target );
	}

	/**
	 * Current request path.
	 *
	 * @return string
	 */
	private static function current_url_path() {
		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$path    = wp_parse_url( $request, PHP_URL_PATH );
		return $path ? (string) $path : '/';
	}
}
