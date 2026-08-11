<?php
define( 'ABSPATH', __DIR__ );
$GLOBALS['f20_options'] = array();
function __( $text ) { return $text; }
function add_filter() { return true; }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) ); }
function absint( $value ) { return abs( (int) $value ); }
function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['f20_options'] ) ? $GLOBALS['f20_options'][ $key ] : $default; }
function update_option( $key, $value ) { $GLOBALS['f20_options'][ $key ] = $value; return true; }
function wp_json_encode( $value ) { return json_encode( $value ); }
require dirname( __DIR__ ) . '/includes/class-defaults.php';
require dirname( __DIR__ ) . '/includes/class-file01-reconciliation-adapter.php';
use Sabri\UnifiedShell\File01ReconciliationAdapter;
$n=0;
$ok=function($c,$m)use(&$n){$n++;if(!$c){fwrite(STDERR,"FAIL: $m\n");exit(1);}};
$plan=File01ReconciliationAdapter::plan(null,array('legacy_key'=>'founder','page_id'=>164));
$ok(is_array($plan)&&true===$plan['accepted'],'Founder shell route is acknowledged.');
$ok('file-20'===$plan['owner_module'],'File 20 owns shell route acknowledgement.');
$ok(null===File01ReconciliationAdapter::plan(null,array('legacy_key'=>'home','page_id'=>162)),'Home content is left to File 21.');
$ok(null===File01ReconciliationAdapter::plan(null,array('legacy_key'=>'news','page_id'=>163)),'News content is left to File 21.');
$hash=str_repeat('a',64);
$action=array('legacy_key'=>'founder','page_id'=>164,'owner_plan'=>$plan);
$receipt=File01ReconciliationAdapter::execute(null,$action,$hash);
$ok(is_array($receipt)&&true===$receipt['success'],'Execution returns a success receipt.');
$ok('file-20'===$receipt['owner_module'],'Receipt is owner-bound.');
$ok(64===strlen($receipt['state_hash']),'Receipt has a bounded state hash.');
$again=File01ReconciliationAdapter::execute(null,$action,$hash);
$ok($again['receipt_id']===$receipt['receipt_id'],'Execution is idempotent.');
$rolled=File01ReconciliationAdapter::rollback(null,$receipt,$hash);
$ok(is_array($rolled)&&true===$rolled['success'],'Rollback succeeds.');
$replay=File01ReconciliationAdapter::rollback(null,$receipt,$hash);
$ok(is_array($replay)&&true===$replay['success']&&!empty($replay['idempotent_replay']),'Rollback is idempotent.');
echo "File 20 File01 reconciliation adapter assertions {$n}/{$n} PASS\n";
