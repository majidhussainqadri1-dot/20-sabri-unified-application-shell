<?php
/** Regression for File20 section 63/65 settings schema boundary. */
declare(strict_types=1);

namespace {
    define( 'ABSPATH', __DIR__ . '/' );
    function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) ); }
    function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
    function add_filter() {}
}

namespace Sabri\UnifiedShell {
    final class Defaults {
        public static function settings() {
            return array(
                'schema_version' => 5,
                'enabled' => true,
                'header' => array( 'enabled' => true, 'platform_title' => 'Sabri' ),
                'layout' => array( 'excluded_page_ids' => array(), 'per_page_overrides' => array() ),
            );
        }
    }
}

namespace {
    require dirname( __DIR__ ) . '/includes/class-settings-schema-guard.php';
    use Sabri\UnifiedShell\SettingsSchemaGuard;

    $failures = array();
    $assert = static function ( $condition, $message ) use ( &$failures ) {
        echo ( $condition ? 'PASS: ' : 'FAIL: ' ) . $message . "\n";
        if ( ! $condition ) { $failures[] = $message; }
    };

    $raw = array(
        'schema_version' => 4,
        'enabled' => 1,
        'header' => array( 'enabled' => true, 'platform_title' => '<b>Sabri Shell</b>', 'foreign_html' => '<script>x()</script>' ),
        'layout' => array( 'excluded_page_ids' => array( 4, 7 ), 'per_page_overrides' => array( 11 => 'minimal' ), 'foreign' => 'drop' ),
        'evil_top_level' => '<script>alert(1)</script>',
        'visual_owner' => 'attacker',
        'appearance' => array( 'primary' => '<b>#087A4E</b>', 'nested' => array( 'css' => '<style>x</style>' ) ),
        'extensions' => array(
            'bad' => array( 'x' => 1 ),
            'future_module' => array( 'html' => '<b>safe text</b>', 'count' => 3 ),
            'vendor-contract' => array( 'enabled' => true ),
        ),
    );

    $clean = SettingsSchemaGuard::normalize( $raw );
    $assert( ! array_key_exists( 'evil_top_level', $clean ), 'Unknown top-level settings are removed rather than silently persisted.' );
    $assert( ! array_key_exists( 'foreign_html', $clean['header'] ), 'Unknown nested keys inside fixed core schema are removed.' );
    $assert( ! array_key_exists( 'foreign', $clean['layout'] ), 'Unknown layout keys cannot expand File20 structural authority.' );
    $assert( 'Sabri Shell' === $clean['header']['platform_title'], 'Known string values are sanitized as text.' );
    $assert( 'file-25' === $clean['visual_owner'], 'File25 visual ownership marker cannot be overwritten by settings input.' );
    $assert( ! isset( $clean['extensions']['bad'] ), 'Extension keys must be explicitly namespaced.' );
    $assert( 'safe text' === $clean['extensions']['future_module']['html'], 'Namespaced future settings are recursively sanitized.' );
    $assert( true === $clean['extensions']['vendor-contract']['enabled'], 'Valid namespaced scalar extension values survive.' );
    $assert( '#087A4E' === $clean['appearance']['primary'], 'Legacy appearance survives only as bounded sanitized migration evidence.' );
    $assert( array( 4, 7 ) === $clean['layout']['excluded_page_ids'], 'Known dynamic list values remain available to the declared schema.' );
    $assert( 'minimal' === $clean['layout']['per_page_overrides'][11], 'Known dynamic map values remain available to the declared schema.' );

    if ( $failures ) { exit( 1 ); }
    echo "\nFile20 settings schema guard regression PASS.\n";
}
