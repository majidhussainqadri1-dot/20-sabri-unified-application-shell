<?php
/**
 * Test fixture: emits the exact generated service worker source for Node VM.
 * This file must never be packaged or called by WordPress runtime.
 */
require __DIR__ . '/bootstrap.php';
require_once dirname( __DIR__ ) . '/includes/class-future-shell-v5.php';
if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data ) { return json_encode( $data ); }
}
$ref = new ReflectionMethod( Sabri\UnifiedShell\FutureShellV5::class, 'service_worker_source' );
$ref->setAccessible( true );
echo $ref->invoke( null );
