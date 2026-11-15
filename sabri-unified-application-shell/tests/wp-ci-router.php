<?php
/**
 * Ephemeral PHP built-in server router for source-only browser CI.
 * Never deployed as a WordPress plugin or production web server router.
 */
$root = getenv( 'SABRI_WP_CI_ROOT' );
$root = is_string( $root ) ? realpath( $root ) : false;
if ( false === $root || ! is_file( $root . '/index.php' ) ) {
	http_response_code( 503 );
	exit( 'CI WordPress root is missing.' );
}
$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
$request_path = is_string( $request_path ) ? rawurldecode( $request_path ) : '/';
$requested_file = realpath( $root . '/' . ltrim( $request_path, '/' ) );
if ( false !== $requested_file &&
	0 === strpos( $requested_file, $root . DIRECTORY_SEPARATOR ) &&
	is_file( $requested_file ) ) {
	return false;
}
require $root . '/index.php';
