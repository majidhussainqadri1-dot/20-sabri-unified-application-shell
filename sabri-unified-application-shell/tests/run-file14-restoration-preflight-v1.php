<?php
/** Forty direct File20 owner-restore dry-run preflight tests; never deploys or writes SQL. */
define( 'ABSPATH', __DIR__ );
require __DIR__ . '/../includes/class-file14-restoration-preflight-v1.php';
use Sabri\UnifiedShell\File14RestorationPreflightV1 as Contract;
$baseline = array(
    'placement_key'=>'global_clinic_primary', 'row_version'=>3, 'status'=>'active',
    'block_key'=>'trust_copy_a', 'route_key'=>'global_clinic',
    'audience'=>'public', 'priority'=>0, 'starts_at'=>null, 'ends_at'=>null,
    'slot_key'=>'global_clinic_primary',
    'public_id'=>'12345678-1234-4123-8123-123456789abc'
);
$current = $baseline;
$current['row_version'] = 4;
$current['block_key'] = 'trust_copy_b';
$canonical = array(
    'contract'=>Contract::CONTRACT,
    'experiment'=>'12345678-1234-4123-8123-123456789abd',
    'approved_by'=>7, 'placement'=>'global_clinic_primary',
    'baseline'=>$baseline,'current'=>$current,
    'snapshot_hash'=>hash('sha256',json_encode($baseline)),
    'request_context'=>'file14-experiment-restore-review'
);
$cases=array();
for($i=0;$i<3;$i++){
    $p=$canonical;
    $p['current']['block_key']='trust_copy_b'.$i;
    $cases[]=array($p,true);
}
for($i=0;$i<37;$i++){
    $p=$canonical;
    // Make all 37 adversarial payloads independently distinguishable.
    $p['current']['block_key']='varying_preview_'.$i;
    switch($i%21){
        case 0:$p['approved_by']=0;break;
        case 1:$p['approved_by']='7';break;
        case 2:$p['experiment']='not-uuid';break;
        case 3:$p['contract']='future.v2';break;
        case 4:$p['placement']='unsafe/path';break;
        case 5:$p['snapshot_hash']=str_repeat('0',64);break;
        case 6:$p['baseline']['row_version']=0;break;
        case 7:$p['baseline']['row_version']='3';break;
        case 8:$p['current']['row_version']=5;break;
        case 9:$p['current']['row_version']=3;break;
        case 10:$p['current']['public_id']='different-owner-id';break;
        case 11:$p['current']['route_key']='other';break;
        case 12:$p['current']['slot_key']='other';break;
        case 13:$p['request_context']='file20-shell';break;
        case 14:$p['current']['placement_key']='other';break;
        case 15:$p['current']['medical_notes']='sensitive';break;
        case 16:unset($p['approved_by']);break;
        case 17:$p['baseline']['audience']='';break;
        case 18:$p['current']['priority']='1';break;
        case 19:$p['current']['starts_at']='tomorrow';break;
        case 20:$p['current']['starts_at']='2026-10-20 00:00:00';$p['current']['ends_at']='2020-10-20 00:00:00';break;
    }
    // Where version metadata is the fault, recompute the snapshot hash so
    // invalid versions cannot pass solely through an unrelated bad digest.
    if(in_array($i%21,array(6,7,17),true)){
        $p['snapshot_hash']=hash('sha256',json_encode($p['baseline']));
    }
    $cases[]=array($p,false);
}
if(count($cases)!==40){exit(2);}
foreach($cases as $i=>$pair){
    $r=Contract::validate($pair[0]);
    $expected=$pair[1];
    if(!is_array($r)||$r['shape_valid']!==$expected||$r['cas_write_executable']!==false
      ||$r['owner_authorized']!==false||$r['staging_accepted']!==false){
        fwrite(STDERR,'File20 CAS case '.($i+1).' failed'.PHP_EOL);
        exit(1);
    }
    echo 'File20 CAS preflight '.sprintf('%02d',$i+1).'/40 PASS'.PHP_EOL;
}
echo 'File20 CAS preflight 40/40 PASS; executing owner-approved CAS RESTORATION is NOT implemented or accepted'.PHP_EOL;
