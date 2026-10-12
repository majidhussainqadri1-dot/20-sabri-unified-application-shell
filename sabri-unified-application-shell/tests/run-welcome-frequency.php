<?php
namespace {
define('ABSPATH',__DIR__);define('SABRI_SHELL_URL','https://example.test/wp-content/plugins/shell/');define('SABRI_SHELL_VERSION','1.4.17');
$GLOBALS['logged_in']=false;$GLOBALS['user_id']=7;$GLOBALS['login_session_token']='session-7a';$GLOBALS['user_meta']=[];$GLOBALS['authorized']=true;$GLOBALS['erasure_hold']=false;$GLOBALS['erase_fail']=false;$GLOBALS['route_ok']=true;$GLOBALS['safe_mode']=false;$GLOBALS['styles']=[];
function __($s,$domain=''){return $s;}function add_action($hook,$callback,$priority=10){$GLOBALS['wp_actions'][$hook][$priority][]=$callback;}function add_filter($hook,$callback){$GLOBALS['wp_filters'][$hook]=$callback;}function apply_filters($n,$value){if($n==='sabri_shell_welcome_erasure_hold'){return $GLOBALS['erasure_hold'];}if($n==='sabri_shell_file25_visual_contract'){return $GLOBALS['file25_ready']??true?['owner'=>'file-25','tokens'=>['primary_color'=>'#087a4e']]:[];}return $value;}
function is_user_logged_in(){return $GLOBALS['logged_in'];}function current_user_can($x){return $GLOBALS['authorized'];}function get_current_user_id(){return $GLOBALS['user_id'];}
function get_user_meta($user,$key,$single=true){return $GLOBALS['user_meta'][$user][$key]??'';}function update_user_meta($user,$key,$value){$GLOBALS['writes']=($GLOBALS['writes']??0)+1;$GLOBALS['user_meta'][$user][$key]=$value;return true;}function delete_user_meta($user,$key){if($GLOBALS['erase_fail'])return false;unset($GLOBALS['user_meta'][$user][$key]);return true;}function get_user_by($type,$value){return $type==='email'&&$value==='person@example.test'?(object)['ID'=>7]:false;}
function wp_verify_nonce($nonce,$action){return $nonce==='valid'&&$action==='wp_rest';}function wp_unslash($x){return $x;}
function wp_parse_url($x,$part=-1){return parse_url($x,$part);}function home_url($p='/'){return 'https://example.test'.($GLOBALS['wp_install_prefix']??'/subsite').$p;}
function esc_url_raw($x){return $x;}function esc_attr__($x,$domain=''){return $x;}function rest_url($p){return 'https://example.test/wp-json/'.$p;}
function wp_create_nonce($x){return $x==='wp_rest'?'valid':'scope-nonce';}function wp_get_session_token(){return $GLOBALS['login_session_token'];}function wp_hash($data,$scheme='auth'){return hash_hmac('sha256',$data,'fixture-wordpress-secret-'.$scheme);}function absint($x){return abs((int)$x);}function wp_enqueue_style(...$args){$GLOBALS['styles'][]=$args[0];}
function wp_enqueue_script(...$args){}function wp_localize_script($h,$n,$data){$GLOBALS['localized']=$data;}
function register_rest_route($n,$p,$args){$GLOBALS['rest']=$args;}function rest_ensure_response($v){return $v;}
function sabri_visual_experience_render_welcome($args){return !empty($GLOBALS['file25_renderer_empty'])||empty($args['invoked_by_file_20'])?'':'<section data-sabri-welcome-skip>Welcome</section>';}
class WP_Error{private $code;public function __construct($code,$message='',$data=[]){$this->code=$code;}public function get_error_code(){return $this->code;}}
}
namespace Sabri\UnifiedShell {
class FourPlanHarmonization{public static function welcome_eligible(){return $GLOBALS['route_ok'];}}
class SafeMode{public static function constant_disabled(){return $GLOBALS['safe_mode'];}public static function emergency_disabled(){return $GLOBALS['safe_mode'];}}
require dirname(__DIR__).'/includes/class-welcome-frequency.php';
$assert=function($ok,$label){if(!$ok){fwrite(STDERR,"FAIL: $label\n");exit(1);}};
WelcomeFrequency::register();
$assert(isset($GLOBALS['wp_actions']['wp_body_open'][2][0]) && isset($GLOBALS['wp_actions']['wp_footer'][2][0]),'Welcome must register early body placement and pre-script footer fallback');
$assert(isset($GLOBALS['wp_filters']['wp_privacy_personal_data_exporters']) && isset($GLOBALS['wp_filters']['wp_privacy_personal_data_erasers']),'Owner must register both WP core personal-data lifecycle hooks');
$assert(!WelcomeFrequency::due(3000000,3000000-2591999),'No early recurrence');
$assert(WelcomeFrequency::due(3000000,3000000-2592000),'Exact 30-day recurrence');
$assert(WelcomeFrequency::due(3000000,3000100),'Future timestamp not permanent');
$_SERVER['REQUEST_URI']='/subsite/';
$assert(WelcomeFrequency::eligible_route(),'Canonical home eligibility');
foreach(['/subsite/?s=doctor','/subsite/?rest_route=/wp/v2','/subsite/?appointment=1','/subsite/?utm_source=campaign'] as $query_url) {
    $_SERVER['REQUEST_URI']=$query_url;
    $assert(!WelcomeFrequency::eligible_route(),'Homepage query deep-link suppressed: '.$query_url);
}
$_SERVER['REQUEST_URI']='/subsite/';
$subsite_cookie=WelcomeFrequency::guest_cookie_name();
$GLOBALS['wp_install_prefix']='';$root_cookie=WelcomeFrequency::guest_cookie_name();
$GLOBALS['wp_install_prefix']='/subsite';
$assert($subsite_cookie!==$root_cookie,'Distinct WordPress installations must have distinct guest cookie names');
$assert(strpos($subsite_cookie,WelcomeFrequency::GUEST_COOKIE.'_')===0,'Guest cookie must be correctly namespaced');
$GLOBALS['file25_ready']=false;$assert(!WelcomeFrequency::eligible_route(),'Missing or safe-mode File25 contract suppresses welcome');$GLOBALS['file25_ready']=true;
foreach(['/subsite/account','/subsite/login','/subsite/appointment','/subsite/clinical','/subsite/emergency','/subsite/news/post']as$path){$_SERVER['REQUEST_URI']=$path;$assert(!WelcomeFrequency::eligible_route(),'Task route excluded '.$path);}
$_SERVER['REQUEST_URI']='/subsite/';$GLOBALS['safe_mode']=true;
$assert(!WelcomeFrequency::eligible_route(),'Safe mode suppression');$GLOBALS['safe_mode']=false;
$GLOBALS['logged_in']=true;$GLOBALS['user_meta'][7][WelcomeFrequency::USER_META]=time()-120;
WelcomeFrequency::prepare();ob_start();WelcomeFrequency::render();$out=ob_get_clean();$assert(''===$out,'Recent member dismissal suppressed');
$prop=new \ReflectionProperty(WelcomeFrequency::class,'prepared');$prop->setAccessible(true);$prop->setValue(null,false);
// A guest cookie must never suppress an independent logged-in account.
$GLOBALS['user_id']=8;$_COOKIE[$subsite_cookie]=(string)time();
WelcomeFrequency::prepare();ob_start();WelcomeFrequency::render();$account8=ob_get_clean();
$assert(strpos($account8,'sabri-shell-welcome-host')!==false,'Account must not inherit guest or another account dismissal');
$prop->setValue(null,false);$GLOBALS['user_id']=7;unset($_COOKIE[WelcomeFrequency::GUEST_COOKIE]);
$assert(WelcomeFrequency::cookie_path()==='/subsite/','Guest cookie must use site-local WordPress path');
$GLOBALS['user_meta'][7][WelcomeFrequency::USER_META]=time()-2592100;WelcomeFrequency::prepare();
ob_start();WelcomeFrequency::render();$out=ob_get_clean();$assert(strpos($out,'sabri-shell-welcome-host')!==false,'Due welcome supplied by File25');
// Both theme hooks must not duplicate the canonical host/ID.
ob_start();call_user_func($GLOBALS['wp_actions']['wp_footer'][2][0]);$repeat=ob_get_clean();
$assert($repeat==='','Footer must not duplicate a Welcome already emitted in wp_body_open');
// Legacy theme: no body-open hook, but footer exists. It must still render once.
$prop->setValue(null,false);WelcomeFrequency::prepare();
ob_start();call_user_func($GLOBALS['wp_actions']['wp_footer'][2][0]);$footer_only=ob_get_clean();
$assert(substr_count($footer_only,'id="sabri-shell-welcome-host"')===1,'Footer-only theme must display exactly one Welcome');
ob_start();call_user_func($GLOBALS['wp_actions']['wp_body_open'][2][0]);$late_body=ob_get_clean();
$assert($late_body==='','Late body-open must not duplicate footer-rendered Welcome');
// A temporarily empty File25 response must not consume the one-render guard.
$prop->setValue(null,false);WelcomeFrequency::prepare();$GLOBALS['file25_renderer_empty']=true;
ob_start();WelcomeFrequency::render();$empty_render=ob_get_clean();
$GLOBALS['file25_renderer_empty']=false;
ob_start();call_user_func($GLOBALS['wp_actions']['wp_footer'][2][0]);$recovered_render=ob_get_clean();
$assert($empty_render==='' && strpos($recovered_render,'sabri-shell-welcome-host')!==false,'An empty File25 response must not permanently consume fallback');
WelcomeFrequency::enqueue();$assert(in_array('sabri-shell-welcome-owner',$GLOBALS['styles'],true),'Structural CSS enqueued');
$assert(($GLOBALS['localized']['loggedIn']??null)===true,'Member context');
$opaqueScope=$GLOBALS['localized']['scope']??'';
$assert(strpos($opaqueScope,'member-')===0 && strpos($opaqueScope,'user-7')===false && strpos($opaqueScope,'session-7a')===false,'Member browser scope is opaque and contains no raw actor ID/session token');
$assert($opaqueScope===WelcomeFrequency::member_session_scope(),'Same actor and login session must have stable welcome scope');
$GLOBALS['user_id']=8;$scopeOtherActor=WelcomeFrequency::member_session_scope();$GLOBALS['user_id']=7;
$assert($scopeOtherActor!==$opaqueScope,'Different logged-in accounts must not share a welcome session marker');
$GLOBALS['login_session_token']='session-7b';$scopeNewLogin=WelcomeFrequency::member_session_scope();$GLOBALS['login_session_token']='session-7a';
$assert($scopeNewLogin!==$opaqueScope,'New login session must have separate welcome suppression');
$assert(($GLOBALS['localized']['homeUrl']??null)==='https://example.test/subsite/','Continue target remains exact canonical home URL');
class Req{private $nonce;public function __construct($nonce){$this->nonce=$nonce;}public function get_header($key){return $this->nonce;}}
$bad=WelcomeFrequency::dismiss(new Req('invalid'));$assert($bad instanceof \WP_Error&&$bad->get_error_code()==='sabri_welcome_forbidden','Invalid CSRF rejected');
$good=WelcomeFrequency::dismiss(new Req('valid'));$assert(is_array($good)&&$good['saved']===true,'Authenticated preference persisted');
$writes=$GLOBALS['writes'];$again=WelcomeFrequency::dismiss(new Req('valid'));
$assert(is_array($again)&&$again['next_eligible_at']===$good['next_eligible_at'],'Repeat dismiss must retain original 30-day deadline');
$assert($GLOBALS['writes']===$writes,'Repeat dismiss must not rewrite user meta');
$GLOBALS['logged_in']=false;$bad=WelcomeFrequency::dismiss(new Req('valid'));$assert($bad instanceof \WP_Error,'Guest cannot mutate account meta');
$prop->setValue(null,false);
$_COOKIE[WelcomeFrequency::GUEST_COOKIE]=(string)time();unset($_COOKIE[$subsite_cookie]);
WelcomeFrequency::prepare();ob_start();WelcomeFrequency::render();$guest_eligible=ob_get_clean();
$assert(strpos($guest_eligible,'sabri-shell-welcome-host')!==false,'Legacy/root cookie must not suppress child installation');
$prop->setValue(null,false);$_COOKIE[$subsite_cookie]=(string)time();
WelcomeFrequency::prepare();ob_start();WelcomeFrequency::render();$guest_suppressed=ob_get_clean();
$assert($guest_suppressed==='','Own installation cookie suppresses welcome for 30 days');
// Privacy lifecycle belongs to File20: own user-meta only, not other fields.
$GLOBALS['user_meta'][7][WelcomeFrequency::USER_META]=1700000000;
$GLOBALS['user_meta'][7]['unrelated_meta']='retain';
$exporters=WelcomeFrequency::register_privacy_exporter(array());
$erasers=WelcomeFrequency::register_privacy_eraser(array());
$assert(isset($exporters['sabri-shell-welcome-preference']['callback'])&&isset($erasers['sabri-shell-welcome-preference']['callback']),'Native personal-data exporter and eraser both discoverable');
$export=WelcomeFrequency::export_personal_data('person@example.test');
$assert($export['done']===true&&count($export['data'])===1,'Known subject may export only one welcome preference record');
$assert(strpos($export['data'][0]['data'][0]['value'],'UTC')!==false,'Export timestamp must use explicit UTC context');
$assert(strpos(json_encode($export),'session-7a')===false&&strpos(json_encode($export),'person@example.test')===false,'Export must not contain session token or email');
$assert(WelcomeFrequency::export_personal_data('unknown@example.test')['data']===array(),'Unknown subject must not expose other account data');
$GLOBALS['erasure_hold']=true;
$held=WelcomeFrequency::erase_personal_data('person@example.test');
$assert($held['items_retained']===true&&$held['items_removed']===false,'Applicable legal hold must retain preference');
$assert(get_user_meta(7,WelcomeFrequency::USER_META,true)===1700000000,'Legal hold cannot silently delete data');
$GLOBALS['erasure_hold']=false;$GLOBALS['erase_fail']=true;
$failed=WelcomeFrequency::erase_personal_data('person@example.test');
$assert($failed['items_retained']===true&&$failed['items_removed']===false,'Failed erasure must report retention');
$GLOBALS['erase_fail']=false;
$erased=WelcomeFrequency::erase_personal_data('person@example.test');
$assert($erased['items_removed']===true&&!$erased['items_retained'],'Valid erasure removes owner-scoped preference');
$assert(get_user_meta(7,WelcomeFrequency::USER_META,true)===''&&get_user_meta(7,'unrelated_meta',true)==='retain','Erasure cannot delete unrelated user metadata');
$assert(WelcomeFrequency::export_personal_data('person@example.test')['data']===array(),'Erased preference must not remain exportable');
$assert(!WelcomeFrequency::erase_personal_data('person@example.test')['items_removed'],'Repeat erasure must be idempotent');
echo "File20 welcome frequency/dismissal source regression PASS\n";
}
