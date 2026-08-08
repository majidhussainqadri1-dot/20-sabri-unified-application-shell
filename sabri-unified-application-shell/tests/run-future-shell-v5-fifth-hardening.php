<?php
$root   = dirname( __DIR__ );
$fifth  = file_get_contents( $root . '/includes/class-future-shell-v5-fifth-hardening.php' );
$main   = file_get_contents( $root . '/sabri-unified-application-shell.php' );
$future = file_get_contents( $root . '/includes/class-future-shell-v5.php' );
$fail   = array();
$checks = array(
    'release 1.4.9 preserves fifth hardening' => false !== strpos( $main, '* Version: 1.4.9' ) && false !== strpos( $main, "define( 'SABRI_SHELL_VERSION', '1.4.9' );" ),
    'fifth hardening loaded' => false !== strpos( $main, 'class-future-shell-v5-fifth-hardening.php' ) && false !== strpos( $main, 'FutureShellV5FifthHardening::register();' ),
    'contract 1.0.5' => false !== strpos( $fifth, "CONTRACT_VERSION = '1.0.5'" ),
    'old final evaluator retired' => false !== strpos( $fifth, "remove_filter( 'sabri_shell_future_feature_enabled', array( FutureShellV5Hardening::class, 'narrow_feature_enablement' ), 999999 )" ),
    'new final evaluator at last priority' => false !== strpos( $fifth, "add_filter( 'sabri_shell_future_feature_enabled', array( __CLASS__, 'final_feature_enablement' ), PHP_INT_MAX, 3 )" ),
    'five exact states' => false !== strpos( $fifth, "case 'disabled':" ) && false !== strpos( $fifth, "case 'internal':" ) && false !== strpos( $fifth, "case 'staging':" ) && false !== strpos( $fifth, "case 'limited':" ) && false !== strpos( $fifth, "case 'general':" ),
    'explicit internal principal contract' => false !== strpos( $fifth, 'sabri_shell_future_internal_principal_allowed' ) && false !== strpos( $fifth, 'get_current_user_id()' ),
    'internal default remains manager-or-explicit-contract' => false !== strpos( $fifth, "current_user_can( 'manage_options' )" ) && false !== strpos( $fifth, 'false,' ),
    'REST configuration remains manager-only' => false !== strpos( $future, "current_user_can( 'manage_options' )" ) && false !== strpos( $future, "'/future/features'" ),
    'invalid state fails closed' => false !== strpos( $fifth, 'default:' ) && false !== strpos( $fifth, 'return false;' ),
    'no foreign backend' => false === strpos( $fifth, 'CREATE TABLE' ) && false === strpos( $fifth, 'dbDelta(' ) && false === strpos( $fifth, 'INSERT INTO' ),
);
foreach ( $checks as $name => $ok ) { if ( ! $ok ) { $fail[] = $name; } }
if ( $fail ) { fwrite( STDERR, "Future Shell v5 fifth hardening FAIL: " . implode( '; ', $fail ) . "\n" ); exit(1); }
echo "Future Shell v5 fifth hardening preserved under 1.4.9: exact five-state rings, explicit internal-principal contract, manager-only configuration and no foreign backend PASS\n";
