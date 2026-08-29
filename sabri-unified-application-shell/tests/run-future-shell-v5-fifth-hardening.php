<?php
$root=dirname(__DIR__);$fifth=file_get_contents($root.'/includes/class-future-shell-v5-fifth-hardening.php');$main=file_get_contents($root.'/sabri-unified-application-shell.php');$future=file_get_contents($root.'/includes/class-future-shell-v5.php');$fail=[];$checks=[
'release 1.4.16 preserves fifth hardening'=>strpos($main,'* Version: 1.4.16')!==false&&strpos($main,"define( 'SABRI_SHELL_VERSION', '1.4.16' );")!==false,
'fifth hardening loaded'=>strpos($main,'FutureShellV5FifthHardening::register();')!==false,
'contract 1.0.5'=>strpos($fifth,"CONTRACT_VERSION = '1.0.5'")!==false,
'old final evaluator retired'=>strpos($fifth,"remove_filter( 'sabri_shell_future_feature_enabled', array( FutureShellV5Hardening::class, 'narrow_feature_enablement' ), 999999 )")!==false,
'new final evaluator'=>strpos($fifth,"add_filter( 'sabri_shell_future_feature_enabled', array( __CLASS__, 'final_feature_enablement' ), PHP_INT_MAX, 3 )")!==false,
'five states'=>strpos($fifth,"case 'disabled':")!==false&&strpos($fifth,"case 'internal':")!==false&&strpos($fifth,"case 'staging':")!==false&&strpos($fifth,"case 'limited':")!==false&&strpos($fifth,"case 'general':")!==false,
'internal principal contract'=>strpos($fifth,'sabri_shell_future_internal_principal_allowed')!==false,
'manager config'=>strpos($future,"current_user_can( 'manage_options' )")!==false&&strpos($future,"'/future/features'")!==false,
'invalid fails closed'=>strpos($fifth,'default:')!==false&&strpos($fifth,'return false;')!==false,
'no foreign backend'=>strpos($fifth,'CREATE TABLE')===false&&strpos($fifth,'dbDelta(')===false&&strpos($fifth,'INSERT INTO')===false];foreach($checks as$n=>$ok)if(!$ok)$fail[]=$n;if($fail){fwrite(STDERR,'Future Shell v5 fifth hardening FAIL: '.implode('; ',$fail)."\n");exit(1);}echo"Future Shell v5 fifth hardening preserved under 1.4.16 PASS\n";