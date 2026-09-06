<?php
/** Round 5 route/dead-control truth regression. */
declare(strict_types=1);

$root = dirname( __DIR__ );
$integrations = (string) file_get_contents( $root . '/includes/class-integrations.php' );
$context = (string) file_get_contents( $root . '/includes/class-context-navigation.php' );
$admin = (string) file_get_contents( $root . '/admin/class-admin.php' );
$eleventh = (string) file_get_contents( $root . '/includes/class-future-shell-v5-eleventh-hardening.php' );

$failures = array();
$assert = static function ( $condition, $message ) use ( &$failures ) {
    echo ( $condition ? 'PASS: ' : 'FAIL: ' ) . $message . "\n";
    if ( ! $condition ) { $failures[] = $message; }
};

$assert(
    false === strpos( $integrations, "'messages'    => 'sn_network_page_id'" )
        && false === strpos( $integrations, "'messages' => 'sn_network_page_id'" ),
    'Messages page resolution cannot reuse the generic File17 Network page ID.'
);
$assert(
    false === strpos( $integrations, "'messages' === \$key && class_exists( 'SN_Activator' ) && is_callable( array( 'SN_Activator', 'network_url' ) )" ),
    'Messages destination resolution cannot fall back to SN_Activator::network_url().'
);
$assert(
    false === strpos( $integrations, "'messages'      => array( 'sabri_network'" )
        && false === strpos( $integrations, "'messages' => array( 'sabri_network'" ),
    'Messages shortcode discovery cannot treat sabri_network as a Messages surface.'
);
$assert(
    false !== strpos( $integrations, "'messages'      => array( 'sabri_messages', 'sabri_communication' )" ),
    'Messages shortcode discovery uses the dedicated File17 surfaces.'
);
$assert(
    false !== strpos( $integrations, "shortcode_exists( 'sabri_messages' ) || shortcode_exists( 'sabri_communication' )" ),
    'Messages integration detection requires dedicated shortcode evidence when no direct provider/page/configuration exists.'
);
$assert(
    false !== strpos( $eleventh, 'generic Network evidence is insufficient' ),
    'The integration implementation remains aligned with the established File17 diagnostic truth.'
);

$assert(
    false !== strpos( $context, "self::same_origin_url( (string) \$item['url'], \$home_url )" ),
    'Contextual section fallback validates candidates against the canonical Home origin.'
);
$assert(
    false === strpos( $context, "self::same_origin_url( (string) \$item['url'], '' )" ),
    'Contextual section candidates are no longer rejected through an origin-less validator call.'
);

$assert(
    false === strpos( $admin, 'safe_mode_controls' ),
    'Legacy Emergency action controls are retired from server-rendered Admin output, not merely hidden by JavaScript.'
);
$assert(
    false === strpos( $admin, 'name="action" value="sabri_shell_emergency"' ),
    'Base Admin no longer emits a duplicate Emergency mutation form.'
);
$assert(
    false !== strpos( $admin, 'hardened FutureShellV5EighthHardening controls are the sole server-' ),
    'Admin source documents the single hardened Emergency-control owner.'
);

if ( $failures ) {
    fwrite( STDERR, "Round 5 route truth regression: " . count( $failures ) . " failure(s).\n" );
    exit( 1 );
}

echo "\nRound 5 route truth regression PASS.\n";
