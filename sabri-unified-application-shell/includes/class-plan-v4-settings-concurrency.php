<?php
/** Optimistic concurrency control for File 20 settings. */
namespace Sabri\UnifiedShell;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class PlanV4SettingsConcurrency {
    const VERSION_OPTION = 'sabri_shell_settings_row_version';
    private static $accepted_update = false;

    public static function register() {
        foreach ( array( 'sabri_shell_settings', 'sabri_unified_shell_settings' ) as $option ) {
            add_filter( 'pre_update_option_' . $option, array( __CLASS__, 'pre_update' ), 10, 3 );
        }
        add_action( 'updated_option', array( __CLASS__, 'after_update' ), 10, 3 );
        add_action( 'admin_footer', array( __CLASS__, 'inject_version_field' ) );
        add_filter( 'sabri_shell_settings_row_version', array( __CLASS__, 'current_version' ) );
    }

    public static function current_version() {
        return max( 1, absint( get_option( self::VERSION_OPTION, 1 ) ) );
    }

    public static function pre_update( $new_value, $old_value, $option ) {
        self::$accepted_update = false;
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) || empty( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) ) {
            return $new_value;
        }
        if ( ! isset( $_POST['sabri_shell_settings_row_version'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- existing settings handler owns CSRF validation.
            return $new_value;
        }
        $expected = absint( wp_unslash( $_POST['sabri_shell_settings_row_version'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $current = self::current_version();
        if ( $expected !== $current ) {
            add_settings_error( 'sabri_shell_settings', 'sabri_shell_settings_conflict', __( 'These settings changed in another session. Reload, compare and submit again.', 'sabri-unified-application-shell' ), 'error' );
            PlanV4Audit::record( 'settings_conflict', array( 'expected_version' => $expected, 'current_version' => $current ) );
            return $old_value;
        }
        self::$accepted_update = true;
        return $new_value;
    }

    public static function after_update( $option, $old_value, $value ) {
        if ( ! self::$accepted_update || ! in_array( $option, array( 'sabri_shell_settings', 'sabri_unified_shell_settings' ), true ) ) {
            return;
        }
        self::$accepted_update = false;
        self::record_change( (array) $old_value, (array) $value, 'settings-api' );
    }

    /**
     * Record an already-authorized File 20 programmatic settings mutation.
     * Recovery holds its own lock/nonce/capability gates; this method only
     * advances optimistic concurrency evidence after a real value change.
     */
    public static function record_programmatic_change( $old_value, $new_value, $reason ) {
        $old_value = is_array( $old_value ) ? $old_value : array();
        $new_value = is_array( $new_value ) ? $new_value : array();
        if ( $old_value === $new_value ) {
            return self::current_version();
        }
        return self::record_change( $old_value, $new_value, sanitize_key( (string) $reason ) );
    }

    private static function record_change( array $old_value, array $value, $reason ) {
        $next = self::current_version() + 1;
        update_option( self::VERSION_OPTION, $next, false );
        $changed = array_values( array_unique( array_merge( array_keys( $old_value ), array_keys( $value ) ) ) );
        PlanV4Audit::record(
            'settings_updated',
            array(
                'row_version'    => $next,
                'changed_groups' => array_slice( $changed, 0, 30 ),
                'reason'         => sanitize_key( (string) $reason ),
            )
        );
        PlanV4ContractHealth::invalidate();
        return $next;
    }

    public static function inject_version_field() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || false === strpos( (string) $screen->id, 'sabri' ) ) {
            return;
        }
        $version = self::current_version();
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
          document.querySelectorAll('form[action="options.php"], form[data-sabri-shell-settings]').forEach(function (form) {
            if (form.querySelector('input[name="sabri_shell_settings_row_version"]')) return;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'sabri_shell_settings_row_version';
            input.value = <?php echo wp_json_encode( (string) $version ); ?>;
            form.appendChild(input);
          });
        });
        </script>
        <?php
    }
}

PlanV4SettingsConcurrency::register();
