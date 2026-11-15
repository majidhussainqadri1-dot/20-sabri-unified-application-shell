<?php
/**
 * Executable CAS audit and unresolved-gap fail-closed regression.
 * No WordPress install or live database is modified.
 */
require __DIR__ . '/bootstrap.php';
require_once dirname( __DIR__ ) . '/includes/class-welcome-experience.php';
use Sabri\UnifiedShell\WelcomeExperience;

function add_option( $key, $value ) { $GLOBALS['test_options'][ $key ] = $value; return true; }
function maybe_serialize( $value ) { return serialize( $value ); }
function maybe_unserialize( $value ) { return is_string( $value ) ? unserialize( $value, array( 'allowed_classes' => false ) ) : $value; }
function wp_cache_delete( $key, $group = '' ) { $GLOBALS['welcome_cache_deletes'][] = array( $key, $group ); return true; }
function wp_json_encode( $value ) { return json_encode( $value ); }

class WelcomeAuditDbDouble {
	public $options = 'wp_options';
	public $force_conflict = false;
	public $mutate_gap_on_delete = false;
	public $write_count = 0;
	public function prepare( $sql, ...$args ) { return array( $sql, $args ); }
	public function get_row( $statement, $mode ) {
		if ( empty( $GLOBALS['test_options'][ WelcomeExperience::AUDIT_OPTION ] ) && ! isset( $GLOBALS['test_options'][ WelcomeExperience::AUDIT_OPTION ] ) ) { return null; }
		return array( 'option_id' => 42, 'option_value' => serialize( $GLOBALS['test_options'][ WelcomeExperience::AUDIT_OPTION ] ) );
	}
	public function query( $statement ) {
		$this->write_count++;
		if ( $this->force_conflict ) { return 0; }
		$args = $statement[1];
		if ( 0 === strpos( $statement[0], 'DELETE FROM ' ) ) {
			if ( $this->mutate_gap_on_delete ) {
				$GLOBALS['test_options'][ WelcomeExperience::AUDIT_GAP_OPTION ]['event'] = 'concurrent_new_gap';
			}
			$persisted = serialize( $GLOBALS['test_options'][ WelcomeExperience::AUDIT_GAP_OPTION ] ?? array() );
			if ( WelcomeExperience::AUDIT_GAP_OPTION !== (string) $args[0] || (string) $args[1] !== $persisted ) {
				return 0;
			}
			unset( $GLOBALS['test_options'][ WelcomeExperience::AUDIT_GAP_OPTION ] );
			return 1;
		}
		$old = serialize( $GLOBALS['test_options'][ WelcomeExperience::AUDIT_OPTION ] );
		if ( (string) $args[3] !== $old ) { return 0; }
		$GLOBALS['test_options'][ WelcomeExperience::AUDIT_OPTION ] = unserialize( (string) $args[0], array( 'allowed_classes' => false ) );
		return 1;
	}
}

$GLOBALS['wpdb'] = new WelcomeAuditDbDouble();
$reflect = new ReflectionMethod( WelcomeExperience::class, 'append_audit' );
$reflect->setAccessible( true );
$assert = static function ( $test, $description ) {
	if ( ! $test ) { fwrite( STDERR, "FAIL: " . $description . PHP_EOL ); exit( 1 ); }
};
$assert( true === $reflect->invoke( null, array( 'event' => 'config_saved', 'at' => '2026-11-15T00:00:00Z', 'actor' => 91 ) ), 'First audit append commits.' );
$assert( 1 === count( $GLOBALS['test_options'][ WelcomeExperience::AUDIT_OPTION ] ), 'First audit entry is retained.' );
$assert( true === $reflect->invoke( null, array( 'event' => 'founder_approved', 'at' => '2026-11-15T00:01:00Z', 'actor' => 91 ) ), 'Concurrent-safe approval append commits.' );
$assert( 2 === count( $GLOBALS['test_options'][ WelcomeExperience::AUDIT_OPTION ] ), 'Both audit events retained.' );
$original = $GLOBALS['test_options'][ WelcomeExperience::AUDIT_OPTION ];
$GLOBALS['wpdb']->force_conflict = true;
$assert( false === $reflect->invoke( null, array( 'event' => 'config_saved', 'actor' => 20 ) ), 'Three rejected CAS writes fail rather than overwrite.' );
$assert( $original === $GLOBALS['test_options'][ WelcomeExperience::AUDIT_OPTION ], 'Concurrency failure cannot destroy prior audit.' );
$gap = get_option( WelcomeExperience::AUDIT_GAP_OPTION, false );
$assert( is_array( $gap ) && 'config_saved' === $gap['event'], 'Audit gap is persisted for manual reconciliation.' );
$assert( ! WelcomeExperience::public_eligible(), 'Sticky audit gap blocks public intro.' );
$assert( in_array( array( 'alloptions', 'options' ), $GLOBALS['welcome_cache_deletes'], true ),
	'Direct SQL audit update invalidated WordPress alloptions cache.' );
$assert( in_array( array( 'notoptions', 'options' ), $GLOBALS['welcome_cache_deletes'], true ),
	'Direct SQL audit update invalidated WordPress notoptions cache.' );
$GLOBALS['wpdb']->force_conflict = false;
$GLOBALS['test_membership_assertions'][91] = array(
	'user_id' => 91,
	'account_class' => 'founder',
	'approved' => true,
	'eligible' => true,
	'suspended' => false,
);
$GLOBALS['test_options'][ WelcomeExperience::APPROVAL_OPTION ] = array( 'signature' => 'old-approval' );
$repair = new ReflectionMethod( WelcomeExperience::class, 'reconcile_gap' );
$repair->setAccessible( true );
$assert( false === $repair->invoke( null, 17 ), 'Unapproved operator cannot repair an audit gap.' );
$assert( true === $repair->invoke( null, 91 ), 'Canonical Founder may append an explicit reconciliation record.' );
$assert( false === get_option( WelcomeExperience::AUDIT_GAP_OPTION, false ),
	'Sticky audit gap is removed only following a successful signed reconciliation.' );
$assert( false === get_option( WelcomeExperience::APPROVAL_OPTION, false ),
	'Founder approval invalidates after audit reconciliation.' );
$records = get_option( WelcomeExperience::AUDIT_OPTION, array() );
$last = end( $records );
$assert( 'audit_gap_reconciled' === ( $last['event'] ?? '' ) &&
		false === ( $last['historical_entries_reconstructed'] ?? true ),
	'Recovery records provenance rather than fabricating missing history.' );
$GLOBALS['test_options'][ WelcomeExperience::AUDIT_GAP_OPTION ] = array(
	'event' => 'first_gap', 'detected_at' => '2026-11-15T00:00:00Z',
);
$GLOBALS['wpdb']->mutate_gap_on_delete = true;
$assert( false === $repair->invoke( null, 91 ), 'Concurrent newer audit gap must not be deleted by a stale reconciliation.' );
$assert( 'concurrent_new_gap' === ( get_option( WelcomeExperience::AUDIT_GAP_OPTION, array() )['event'] ?? '' ),
	'Latest unreconciled incident retains a sticky block.' );
$GLOBALS['wpdb']->mutate_gap_on_delete = false;
echo "File20 V12.3 CAS audit: 17 checks passed" . PHP_EOL;
