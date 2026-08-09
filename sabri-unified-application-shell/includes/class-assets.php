<?php
/**
 * Asset registration.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Enqueues CSS and JavaScript only for public shell requests. */
final class Assets {
	/** Register public assets. */
	public static function enqueue() {
		if ( ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) ) {
			return;
		}

		wp_enqueue_style(
			'sabri-shell',
			SABRI_SHELL_URL . 'assets/css/shell.css',
			array(),
			SABRI_SHELL_VERSION
		);

		wp_enqueue_script(
			'sabri-shell',
			SABRI_SHELL_URL . 'assets/js/shell.js',
			array(),
			SABRI_SHELL_VERSION,
			true
		);

		$settings = Settings::get();
		$visual   = class_exists( CentralPlanContract::class )
			? CentralPlanContract::visual_contract()
			: array( 'status' => 'fallback', 'tokens' => array() );

		wp_localize_script(
			'sabri-shell',
			'SabriShell',
			array(
				'desktopBreakpoint' => 1024,
				'rightBreakpoint'   => 1200,
				'closeLabel'        => __( 'Close menu', 'sabri-unified-application-shell' ),
				'openLabel'         => __( 'Open menu', 'sabri-unified-application-shell' ),
				'closeContextLabel' => __( 'Close context panel', 'sabri-unified-application-shell' ),
				'openContextLabel'  => __( 'Open context panel', 'sabri-unified-application-shell' ),
				'contentSelector'   => $settings['layout']['theme_content_selector'],
				'contentCandidates' => Layout::content_target_candidates( $settings ),
				'layoutMode'        => Layout::current_mode(),
				'visualProvider'    => $visual['status'],
			)
		);
	}

	/** Print structural custom properties and optional theme visibility selectors. */
	public static function print_custom_properties() {
		if ( ! in_array( Layout::current_mode(), array( Layout::TWO, Layout::THREE ), true ) ) {
			return;
		}

		$settings  = Settings::get();
		$layout    = $settings['layout'];
		$selectors = array();

		if ( ! empty( $layout['hide_theme_header'] ) ) {
			$selectors[] = '.site-header';
			$selectors[] = 'header.wp-block-template-part';
		}

		if ( ! empty( $layout['hide_theme_footer'] ) ) {
			$selectors[] = '.site-footer';
			$selectors[] = 'footer.wp-block-template-part';
		}

		if ( ! empty( $layout['custom_hide_selectors'] ) ) {
			foreach ( array_map( 'trim', explode( ',', $layout['custom_hide_selectors'] ) ) as $selector ) {
				if ( '' !== $selector ) {
					$selectors[] = $selector;
				}
			}
		}

		?>
		<style id="sabri-shell-vars">
			body.sabri-shell-enabled {
				--sabri-shell-max-width: <?php echo esc_html( absint( $layout['max_width'] ) ); ?>px;
				--sabri-shell-left-width: <?php echo esc_html( absint( $layout['left_width'] ) ); ?>px;
				--sabri-shell-right-width: <?php echo esc_html( absint( $layout['right_width'] ) ); ?>px;
				--sabri-shell-gap: <?php echo esc_html( absint( $layout['gap'] ) ); ?>px;
			}
			<?php if ( ! empty( $selectors ) ) : ?>
			body.sabri-shell-enabled <?php echo implode( ', body.sabri-shell-enabled ', $selectors ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- selectors are validated by Settings. ?> {
				display: none !important;
			}
			<?php endif; ?>
		</style>
		<?php
	}
}
