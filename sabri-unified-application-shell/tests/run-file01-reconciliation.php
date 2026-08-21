<?php
require __DIR__ . '/bootstrap.php';

use Sabri\UnifiedShell\Defaults;
use Sabri\UnifiedShell\File01ReconciliationAdapter;
use Sabri\UnifiedShell\Navigation;
use Sabri\UnifiedShell\Settings;

if ( ! function_exists( 'get_post_type' ) ) {
	function get_post_type( $id ) {
		return ! empty( $GLOBALS['test_post_status'][ absint( $id ) ] ) ? 'page' : false;
	}
}

require_once dirname( __DIR__ ) . '/includes/class-file01-reconciliation-adapter.php';

$failures = array();
$assert = static function ( $condition, $message ) use ( &$failures ) {
	if ( ! $condition ) {
		$failures[] = $message;
		echo "FAIL: {$message}\n";
	} else {
		echo "PASS: {$message}\n";
	}
};

$map = File01ReconciliationAdapter::route_map();
$assert( 12 === count( $map ), 'File20 owns exactly the twelve legacy shell-route handoffs discovered by the live File01 reconciliation audit.' );
$assert( ! isset( $map['home'], $map['news'] ), 'File20 does not steal File21 Home/News reconciliation ownership.' );
$assert( 'video_wall' === $map['videos']['destination'] && 'pdf_library' === $map['pdf']['destination'], 'Legacy File01 keys translate to the canonical File20 destination keys.' );

$GLOBALS['test_options']['spf_page_map'] = array(
	'home' => 162,
	'news' => 163,
	'founder' => 164,
	'learn' => 165,
	'encyclopedia' => 166,
	'doctors' => 167,
	'clinic' => 168,
	'videos' => 169,
	'reels' => 170,
	'pdf' => 171,
	'radar' => 172,
	'ai' => 173,
	'network' => 174,
	'marketplace' => 175,
);
foreach ( range( 162, 175 ) as $page_id ) {
	$GLOBALS['test_post_status'][ $page_id ] = 'publish';
	$GLOBALS['test_permalinks'][ $page_id ] = 'https://example.test/page-' . $page_id . '/';
}

$home_plan = array( 'accepted' => true, 'owner_module' => 'file-21', 'command_version' => '1.0.0' );
$assert( $home_plan === File01ReconciliationAdapter::plan( $home_plan, array( 'legacy_key' => 'home', 'page_id' => 162, 'target_owners' => array( 'file-20', 'file-21' ) ) ), 'An earlier File21 accepted plan is preserved byte-for-byte.' );
$assert( null === File01ReconciliationAdapter::plan( null, array( 'legacy_key' => 'home', 'page_id' => 162, 'target_owners' => array( 'file-20', 'file-21' ) ) ), 'File20 declines Home when no earlier owner plan is present.' );

$context = array(
	'legacy_key' => 'founder',
	'page_id' => 164,
	'owned_by_file01_legacy' => true,
	'target_owners' => array( 'file-20', 'file-21' ),
);
$plan = File01ReconciliationAdapter::plan( null, $context );
$assert( is_array( $plan ) && ! empty( $plan['accepted'] ) && 'file-20' === $plan['owner_module'], 'Founder legacy mapping receives an explicit File20 accepted owner plan.' );
$assert( 'founder' === $plan['route_key'] && 'file-03' === $plan['content_owner'], 'File20 adopts only the Founder shell route reference while preserving File03 native content ownership.' );

$invalid = File01ReconciliationAdapter::plan( null, array( 'legacy_key' => 'learn', 'page_id' => 9999, 'target_owners' => array( 'file-20', 'file-21' ) ) );
$assert( is_array( $invalid ) && empty( $invalid['accepted'] ), 'Missing/unpublished legacy pages fail closed instead of receiving a false accepted plan.' );

$plan_hash = str_repeat( 'a', 64 );
$action = array(
	'action' => 'reconcile_legacy_mapping',
	'legacy_key' => 'founder',
	'page_id' => 164,
	'owned' => true,
	'owner_plan' => $plan,
	'local_apply' => 'mark_quarantined_after_owner_ack',
);
$receipt = File01ReconciliationAdapter::execute( null, $action, $plan_hash );
$assert( is_array( $receipt ) && ! empty( $receipt['success'] ) && 'file-20' === $receipt['owner_module'], 'Execute returns a bounded File20 reconciliation receipt.' );
$assert( 164 === absint( Settings::get()['navigation']['founder']['page_id'] ), 'Execute persists the legacy Page ID into File20-owned navigation state before File01 can remove spf_page_map.' );
$assert( 64 === strlen( $receipt['state_hash'] ) && 'file20_restore_navigation_route' === $receipt['rollback_command'], 'Receipt is integrity-bound and advertises the reversible File20 rollback command.' );

$replay = File01ReconciliationAdapter::execute( null, $action, $plan_hash );
$assert( $receipt['receipt_id'] === $replay['receipt_id'] && ! empty( $replay['success'] ), 'Execute is idempotent for the same File01 plan/key/Page-ID binding.' );

unset( $GLOBALS['test_options']['spf_page_map'] );
Navigation::invalidate_cache();
$founder_item = Navigation::resolve_item( 'founder', Defaults::destinations()['founder'], Settings::get()['navigation']['founder'] );
$assert( 'configured_page_id' === $founder_item['reason'] && 'https://example.test/page-164/' === $founder_item['url'], 'After File01 legacy-map removal the same route remains available from File20-owned configured Page-ID state.' );

$tampered = File01ReconciliationAdapter::rollback( null, $receipt, str_repeat( 'b', 64 ) );
$assert( is_array( $tampered ) && empty( $tampered['success'] ), 'Rollback refuses a receipt bound to a different File01 plan hash.' );

$rolled_back = File01ReconciliationAdapter::rollback( null, $receipt, $plan_hash );
$assert( is_array( $rolled_back ) && ! empty( $rolled_back['success'] ) && 'rolled_back' === $rolled_back['status'], 'Rollback restores the exact pre-reconciliation File20 navigation row.' );
$assert( 0 === absint( Settings::get()['navigation']['founder']['page_id'] ), 'Rollback removes the injected Founder Page ID when no File20 row existed before reconciliation.' );

$rolled_back_replay = File01ReconciliationAdapter::rollback( null, $receipt, $plan_hash );
$assert( is_array( $rolled_back_replay ) && ! empty( $rolled_back_replay['success'] ) && ! empty( $rolled_back_replay['idempotent_replay'] ), 'Rollback is idempotent after the receipt is already rolled back.' );

$GLOBALS['test_options']['sabri_shell_settings'] = array(
	'navigation' => array(
		'learn' => array( 'page_id' => 777, 'label' => 'Custom Learn', 'enabled' => true ),
	),
);
$GLOBALS['test_post_status'][777] = 'publish';
$GLOBALS['test_permalinks'][777] = 'https://example.test/custom-learn/';
$GLOBALS['test_options']['spf_page_map'] = array( 'learn' => 165 );
$learn_plan = File01ReconciliationAdapter::plan( null, array( 'legacy_key' => 'learn', 'page_id' => 165, 'target_owners' => array( 'file-20', 'file-21' ) ) );
$learn_action = array( 'action' => 'reconcile_legacy_mapping', 'legacy_key' => 'learn', 'page_id' => 165, 'owned' => true, 'owner_plan' => $learn_plan );
$learn_hash = str_repeat( 'c', 64 );
$learn_receipt = File01ReconciliationAdapter::execute( null, $learn_action, $learn_hash );
$assert( 165 === absint( Settings::get()['navigation']['learn']['page_id'] ), 'Reconciliation may temporarily replace an existing File20 route Page ID with the File01 legacy binding.' );
$learn_rollback = File01ReconciliationAdapter::rollback( null, $learn_receipt, $learn_hash );
$learn_after = get_option( 'sabri_shell_settings', array() );
$assert( ! empty( $learn_rollback['success'] ) && 777 === absint( $learn_after['navigation']['learn']['page_id'] ) && 'Custom Learn' === $learn_after['navigation']['learn']['label'], 'Rollback restores the exact prior File20 navigation row instead of resetting it to defaults.' );

$store = get_option( File01ReconciliationAdapter::RECEIPTS_OPTION, array() );
$assert( is_array( $store ) && count( $store ) <= File01ReconciliationAdapter::MAX_RECEIPTS, 'Reconciliation receipt evidence is retained in a bounded File20-owned store.' );

if ( $failures ) {
	echo "\n" . count( $failures ) . " File01 reconciliation test(s) failed.\n";
	exit( 1 );
}

echo "\nAll File01 reconciliation adapter tests passed.\n";
