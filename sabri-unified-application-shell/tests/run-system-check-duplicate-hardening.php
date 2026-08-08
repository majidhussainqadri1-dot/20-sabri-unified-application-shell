<?php
/** Static regression for locale-independent duplicate-shell truth. */
declare(strict_types=1);
$root = dirname( __DIR__ );
$main = (string) file_get_contents( $root . '/sabri-unified-application-shell.php' );
$dup  = (string) file_get_contents( $root . '/includes/class-system-check-duplicate-hardening.php' );
$fail = array();
$assert = static function ( $ok, $label ) use ( &$fail ): void { if ( ! $ok ) { $fail[] = $label; } };
$assert( false !== strpos( $main, 'class-system-check-duplicate-hardening.php' ) && false !== strpos( $main, 'SystemCheckDuplicateHardening::register();' ), 'duplicate hardening loaded/registered' );
$assert( false !== strpos( $dup, "add_filter( 'sabri_shell_system_check_report'" ) && false !== strpos( $dup, 'PHP_INT_MAX' ), 'final report correction hook' );
$assert( false !== strpos( $dup, "get_option( 'active_plugins'" ) && false !== strpos( $dup, "'duplicate-shell'" ), 'structured active-plugin detection' );
$assert( false !== strpos( $dup, "\$row['status'] = \$matches ? 'fail' : 'pass'" ), 'status derives from structured matches not translated display text' );
$assert( false !== strpos( $dup, "\$row['severity'] = \$matches ? 'critical' : 'info'" ), 'severity derives from structured matches' );
if ( $fail ) { fwrite( STDERR, "System Check duplicate hardening FAIL: " . implode( '; ', $fail ) . "\n" ); exit(1); }
echo "System Check duplicate-shell health is locale-independent PASS\n";
