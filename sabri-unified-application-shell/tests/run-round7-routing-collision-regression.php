<?php
/** Round 7: route registry, precedence and collision regression. */
$root = dirname( __DIR__ );
$fail = static function ( $message ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); };
$read = static function ( $path ) use ( $root, $fail ) {
    $data = file_get_contents( $root . '/' . $path );
    if ( false === $data ) { $fail( 'Cannot read ' . $path ); }
    return $data;
};
$defaults = $read( 'includes/class-defaults.php' );
$nav      = $read( 'includes/class-navigation.php' );
$hard     = $read( 'includes/class-future-shell-v5-eleventh-hardening.php' );
$route    = $read( 'includes/class-route-security.php' );

$messages_start = strpos( $defaults, "'messages'     => array(" );
$messages_end   = strpos( $defaults, "'notifications' => array(", $messages_start );
$messages = substr( $defaults, $messages_start, $messages_end - $messages_start );
if ( false !== strpos( $messages, "'sabri_network'" ) ) { $fail( 'Messages still accepts generic Network shortcode.' ); }
if ( false === strpos( $messages, "'sabri_messages'" ) || false === strpos( $messages, "'sabri_communication'" ) ) { $fail( 'Dedicated Messages shortcode evidence missing.' ); }

$reels_start = strpos( $defaults, "'reels'        => array(" );
$reels_end   = strpos( $defaults, "'pdf_library'", $reels_start );
$reels = substr( $defaults, $reels_start, $reels_end - $reels_start );
if ( false === strpos( $reels, "'post_type'  => 'srl_reel'" ) || false !== strpos( $reels, "'svw_video'" ) ) { $fail( 'Reels archive evidence is not File 11 canonical.' ); }

foreach ( array( 'sabri_shell_navigation_route_collision', 'route_identity', 'sabri_shell_slug_page_collision' ) as $needle ) {
    if ( false === strpos( $nav, $needle ) ) { $fail( 'Navigation collision guard missing: ' . $needle ); }
}
if ( false === strpos( $nav, 'unset( $items[ $key ] )' ) ) { $fail( 'Colliding canonical navigation routes do not fail closed.' ); }
foreach ( array( 'sabri_shell_page_contracts', 'reject_cross_provider_page_map_collisions', 'sabri_shell_page_map_collision' ) as $needle ) {
    if ( false === strpos( $hard, $needle ) ) { $fail( 'Cross-provider page-map collision guard missing: ' . $needle ); }
}
if ( false === strpos( $hard, '$contracts[ $key ] = array();' ) ) { $fail( 'Conflicting provider maps do not fail closed.' ); }
if ( false === strpos( $route, 'validated_path' ) || false === strpos( $route, 'rawurldecode' ) ) { $fail( 'Strict validated override path guard regressed.' ); }

$precedence = array( 'configured_page_id', 'page_shortcode', 'post_type_archive', 'slug_match', 'validated_url_override' );
$last = -1;
foreach ( $precedence as $needle ) {
    $pos = strpos( $nav, $needle );
    if ( false === $pos || $pos <= $last ) { $fail( 'Canonical route precedence regressed at ' . $needle ); }
    $last = $pos;
}

echo "Round 7 canonical routing/collision regression PASS\n";
