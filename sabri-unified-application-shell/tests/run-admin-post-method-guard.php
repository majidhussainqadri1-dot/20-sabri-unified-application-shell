<?php
/** Regression for File20 §62: destructive admin actions must reject GET/non-POST. */
declare(strict_types=1);

namespace {
    define( 'ABSPATH', __DIR__ . '/' );
    $GLOBALS['test_hooks'] = array();
    $GLOBALS['test_underlying_calls'] = array( 'repair' => 0, 'rollback' => 0, 'emergency' => 0 );

    function __( $text ) { return $text; }
    function esc_html__( $text ) { return $text; }
    function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
    function wp_unslash( $value ) { return $value; }
    function is_admin() { return true; }
    function add_filter() { return true; }
    function add_action( $hook, $callback ) { $GLOBALS['test_hooks'][ $hook ] = $callback; return true; }
    function remove_action( $hook, $callback ) {
        if ( isset( $GLOBALS['test_hooks'][ $hook ] ) && $GLOBALS['test_hooks'][ $hook ] === $callback ) { unset( $GLOBALS['test_hooks'][ $hook ] ); return true; }
        return false;
    }
    final class TestWpDie extends \RuntimeException { public $response; public function __construct( $response ) { parent::__construct( 'wp_die' ); $this->response = $response; } }
    function wp_die( $message = '', $title = '', $args = array() ) { unset( $message, $title ); throw new TestWpDie( isset( $args['response'] ) ? (int) $args['response'] : 500 ); }
}

namespace Sabri\UnifiedShell {
    final class FutureShellV5EighthHardening {
        public static function handle_admin_repair() { ++$GLOBALS['test_underlying_calls']['repair']; }
        public static function handle_admin_rollback() { ++$GLOBALS['test_underlying_calls']['rollback']; }
        public static function handle_admin_emergency() { ++$GLOBALS['test_underlying_calls']['emergency']; }
    }
}

namespace {
    require dirname( __DIR__ ) . '/includes/class-admin-post-method-guard.php';
    use Sabri\UnifiedShell\AdminPostMethodGuard;
    use Sabri\UnifiedShell\FutureShellV5EighthHardening;

    $GLOBALS['test_hooks']['admin_post_sabri_shell_repair'] = array( FutureShellV5EighthHardening::class, 'handle_admin_repair' );
    $GLOBALS['test_hooks']['admin_post_sabri_shell_rollback'] = array( FutureShellV5EighthHardening::class, 'handle_admin_rollback' );
    $GLOBALS['test_hooks']['admin_post_sabri_shell_emergency'] = array( FutureShellV5EighthHardening::class, 'handle_admin_emergency' );

    $failures = array();
    $assert = static function ( $condition, $message ) use ( &$failures ) {
        echo ( $condition ? 'PASS: ' : 'FAIL: ' ) . $message . "\n";
        if ( ! $condition ) { $failures[] = $message; }
    };

    AdminPostMethodGuard::register();
    foreach ( array( 'repair', 'rollback', 'emergency' ) as $name ) {
        $hook = 'admin_post_sabri_shell_' . $name;
        $assert( isset( $GLOBALS['test_hooks'][ $hook ] ) && $GLOBALS['test_hooks'][ $hook ][0] === AdminPostMethodGuard::class, ucfirst( $name ) . ' admin-post dispatch is replaced by the POST guard.' );
    }

    $_SERVER['REQUEST_METHOD'] = 'GET';
    foreach ( array( 'repair', 'rollback', 'emergency' ) as $name ) {
        $method = 'handle_admin_' . $name;
        $blocked = false;
        try { AdminPostMethodGuard::$method(); }
        catch ( TestWpDie $error ) { $blocked = 405 === $error->response; }
        $assert( $blocked, ucfirst( $name ) . ' rejects a nonce-capable GET transport with HTTP 405 before the destructive handler.' );
        $assert( 0 === $GLOBALS['test_underlying_calls'][ $name ], ucfirst( $name ) . ' underlying mutation is not reached on GET.' );
    }

    $_SERVER['REQUEST_METHOD'] = 'POST';
    foreach ( array( 'repair', 'rollback', 'emergency' ) as $name ) {
        $method = 'handle_admin_' . $name;
        AdminPostMethodGuard::$method();
        $assert( 1 === $GLOBALS['test_underlying_calls'][ $name ], ucfirst( $name ) . ' POST reaches the existing capability+nonce hardened handler.' );
    }

    if ( $failures ) { exit( 1 ); }
    echo "\nFile20 destructive admin POST-method guard regression PASS.\n";
}
