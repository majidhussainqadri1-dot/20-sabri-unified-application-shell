<?php
/**
 * Corrective public layout bridge for File 21 managed publications.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Adds bounded, ownership-safe public layout corrections. */
final class LayoutCorrection {
	/** Register corrective assets without replacing theme or companion markup. */
	public static function register() {
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ), 35 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 40 );
	}

	/** Add a stable release/body marker. */
	public static function body_classes( $classes ) {
		if ( ! is_array( $classes ) ) {
			$classes = array();
		}
		$classes[] = 'sabri-shell-corrective-1-1-2';
		return array_values( array_unique( $classes ) );
	}

	/** Load only File 20-owned containment and target-recovery assets. */
	public static function enqueue() {
		if ( Layout::MINIMAL === Layout::current_mode() ) {
			return;
		}
		wp_enqueue_style(
			'sabri-shell-corrective-1-1-2',
			SABRI_SHELL_URL . 'assets/css/shell-corrective-1.1.1.css',
			array( 'sabri-shell' ),
			SABRI_SHELL_VERSION
		);
		wp_enqueue_script(
			'sabri-shell-corrective-1-1-2',
			SABRI_SHELL_URL . 'assets/js/shell-corrective-1.1.1.js',
			array( 'sabri-shell' ),
			SABRI_SHELL_VERSION,
			true
		);
	}
}
