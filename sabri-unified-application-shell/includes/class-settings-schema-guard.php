<?php
/**
 * Settings schema boundary for File 20.
 *
 * Governing law permits future/unknown settings only inside a validated,
 * namespaced extension envelope. Historical File25 appearance evidence may be
 * retained, but it is bounded/sanitized and never treated as visual authority.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsSchemaGuard {
	const OPTION             = 'sabri_shell_settings';
	const EXTENSIONS_KEY     = 'extensions';
	const MAX_DEPTH          = 4;
	const MAX_ENTRIES        = 64;
	const MAX_STRING_LENGTH  = 500;

	/** Register late write pruning and safe read projection. */
	public static function register() {
		add_filter( 'pre_update_option_' . self::OPTION, array( __CLASS__, 'filter_write' ), PHP_INT_MAX - 5, 3 );
		add_filter( 'option_' . self::OPTION, array( __CLASS__, 'filter_read' ), PHP_INT_MAX, 2 );
	}

	/** Never expose arbitrary historical/foreign keys through File20 settings reads. */
	public static function filter_read( $value, $option = '' ) {
		unset( $option );
		return is_array( $value ) ? self::normalize( $value ) : $value;
	}

	/** Persist only the declared schema plus validated compatibility envelopes. */
	public static function filter_write( $value, $old_value, $option ) {
		unset( $old_value, $option );
		return is_array( $value ) ? self::normalize( $value ) : $value;
	}

	/**
	 * Normalize the complete File20 settings row.
	 *
	 * @param array<string,mixed> $value Raw/current settings.
	 * @return array<string,mixed>
	 */
	public static function normalize( array $value ) {
		$schema = Defaults::settings();
		$out    = array();

		foreach ( $schema as $key => $default ) {
			if ( array_key_exists( $key, $value ) ) {
				$out[ $key ] = self::normalize_known_value( $value[ $key ], $default, 0 );
			} else {
				$out[ $key ] = $default;
			}
		}

		/* File25 ownership marker is a known cross-file compatibility key. */
		$out['visual_owner'] = 'file-25';

		/* Legacy appearance is retained only as sanitized migration evidence. */
		if ( isset( $value['appearance'] ) && is_array( $value['appearance'] ) ) {
			$out['appearance'] = self::sanitize_bounded_value( $value['appearance'], 0 );
		}

		/* Unknown future values survive only inside an explicitly namespaced envelope. */
		$out[ self::EXTENSIONS_KEY ] = self::sanitize_extensions(
			isset( $value[ self::EXTENSIONS_KEY ] ) && is_array( $value[ self::EXTENSIONS_KEY ] ) ? $value[ self::EXTENSIONS_KEY ] : array()
		);

		return $out;
	}

	/** Normalize a value against the known default schema. */
	private static function normalize_known_value( $value, $default, $depth ) {
		if ( $depth > self::MAX_DEPTH ) {
			return $default;
		}
		if ( is_array( $default ) ) {
			if ( ! is_array( $value ) ) {
				return $default;
			}
			/* Empty/list defaults are validated bounded collections rather than maps. */
			if ( array() === $default || self::is_list( $default ) ) {
				return self::sanitize_bounded_value( $value, $depth + 1 );
			}
			$out = array();
			foreach ( $default as $key => $child_default ) {
				if ( array_key_exists( $key, $value ) ) {
					$out[ $key ] = self::normalize_known_value( $value[ $key ], $child_default, $depth + 1 );
				} else {
					$out[ $key ] = $child_default;
				}
			}
			return $out;
		}
		if ( is_bool( $default ) ) {
			return (bool) $value;
		}
		if ( is_int( $default ) ) {
			return (int) $value;
		}
		if ( is_float( $default ) ) {
			return (float) $value;
		}
		if ( is_string( $default ) ) {
			return self::bounded_text( $value );
		}
		return null === $value || is_scalar( $value ) ? $value : $default;
	}

	/** Validate namespaced extension buckets only. */
	private static function sanitize_extensions( array $extensions ) {
		$out = array();
		foreach ( array_slice( $extensions, 0, self::MAX_ENTRIES, true ) as $namespace => $payload ) {
			$namespace = strtolower( trim( (string) $namespace ) );
			/* A delimiter is mandatory so a generic key cannot masquerade as core schema. */
			if ( ! preg_match( '/^[a-z0-9][a-z0-9_-]{0,62}[-_][a-z0-9][a-z0-9_-]{0,62}$/', $namespace ) ) {
				continue;
			}
			$out[ $namespace ] = self::sanitize_bounded_value( $payload, 0 );
		}
		return $out;
	}

	/** Recursively bound and sanitize compatibility/extension values. */
	private static function sanitize_bounded_value( $value, $depth ) {
		if ( $depth > self::MAX_DEPTH ) {
			return null;
		}
		if ( is_array( $value ) ) {
			$out = array();
			foreach ( array_slice( $value, 0, self::MAX_ENTRIES, true ) as $key => $child ) {
				if ( is_int( $key ) ) {
					$out[] = self::sanitize_bounded_value( $child, $depth + 1 );
					continue;
				}
				$safe_key = sanitize_key( (string) $key );
				if ( '' === $safe_key ) {
					continue;
				}
				$out[ $safe_key ] = self::sanitize_bounded_value( $child, $depth + 1 );
			}
			return $out;
		}
		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
			return $value;
		}
		if ( is_string( $value ) || is_scalar( $value ) ) {
			return self::bounded_text( $value );
		}
		return null;
	}

	private static function bounded_text( $value ) {
		$text = sanitize_text_field( (string) $value );
		return function_exists( 'mb_substr' ) ? mb_substr( $text, 0, self::MAX_STRING_LENGTH ) : substr( $text, 0, self::MAX_STRING_LENGTH );
	}

	private static function is_list( array $value ) {
		if ( function_exists( 'array_is_list' ) ) {
			return array_is_list( $value );
		}
		return array() === $value || array_keys( $value ) === range( 0, count( $value ) - 1 );
	}
}
