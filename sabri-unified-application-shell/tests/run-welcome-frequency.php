<?php
namespace {
define('ABSPATH',__DIR__);define('SABRI_SHELL_URL','https://example.test/wp-content/plugins/shell/');define('SABRI_SHELL_VERSION','1.4.17');
$GLOBALS['logged_in']=false;$GLOBALS['user_id']=7;$GLOBALS['user_meta']=[];$GLOBALS['authorized']=true;$GLOBALS['route_ok']=true;$GLOBALS['safe_mode']=false;$GLOBALS['styles']=[];
function __($s,$domain=''){return $s;}function add_action(...$args){}function add_filter(...$args){}function apply_filters($n,$value){if($n==='sabri_shell_file25_visual_contract'){return $GLOBALS['file25_ready']??true?['owner'=>'file-25','tokens'=>['primary_color'=>'#087a4e']]:[];}return $value;}
function is_user_logged_in(){return $GLOBALS['logged_in'];}function current_user_can($x){return $GLOBALS['authorized'];}function get_current_user_id(){return $GLOBALS['user_id'];}
function get_user_meta($user,$key,$single=true){return $GLOBALS['user_meta'][$user][$key]??'';}function update_user_meta($user,$key,$value){$GLOBALS['writes']=($GLOBALS['writes']??0)+1;$GLOBALS['user_meta'][$user][$key]=$value;return true;}
function wp_verify_nonce($nonce,$action){return $nonce==='valid'&&$action==='wp_rest';}function wp_unslash($x){return $x;}
function wp_parse_url($x,$part=-1){return parse_url($x,$part);}function home_url($p='/'){return 'https://example.test/subsite'.$p;}
function esc_url_raw($x){return $x;}function esc_attr__($x,$domain=''){return $x;}function rest_url($p){return 'https://example.test/wp-json/'.$p;}
function wp_create_nonce($x){return 'valid';}function absint($x){return abs((int)$x);}function wp_enqueue_style(...$args){$GLOBALS['styles'][]=$args[0];}
function wp_enqueue_script(...$args){}function wp_localize_script($h,$n,$data){$GLOBALS['localized']=$data;}
function register_rest_route($n,$p,$args){$GLOBALS['rest']=$args;}function rest_ensure_response($v){return $v;}
function sabri_visual_experience_render_welcome($args){return empty($args['invoked_by_file_20'])?'':'<section data-sabri-welcome-skip>Welcome</section>';}
class WP_Error{private $code;public function __construct($code,$message='',$data=[]){$this->code=$code;}public function get_error_code(){return $this->code;}}
}
namespace Sabri\UnifiedShell {
class FourPlanHarmonization{public static function welcome_eligible(){return $GLOBALS['route_ok'];}}
class SafeMode{public static function constant_disabled(){return $GLOBALS['safe_mode'];}public static function emergency_disabled(){return $GLOBALS['safe_mode'];}}
require dirname(__DIR__).'/includes/class-welcome-frequency.php';
$assert=function($ok,$label){if(!$ok){fwrite(STDERR,"FAIL: $label\n");exit(1);}};
$assert(!WelcomeFrequency::due(3000000,3000000-2591999),'No early recurrence');
$assert(WelcomeFrequency::due(3000000,3000000-2592000),'Exact 30-day recurrence');
$assert(WelcomeFrequency::due(3000000,3000100),'Future timestamp not permanent');
$_SERVER['REQUEST_URI']='/subsite/';
$assert(WelcomeFrequency::eligible_route(),'Canonical home eligibility');
$GLOBALS['file25_ready']=false;$assert(!WelcomeFrequency::eligible_route(),'Missing or safe-mode File25 contract suppresses welcome');$GLOBALS['file25_ready']=true;
foreach(['/subsite/account','/subsite/login','/subsite/appointment','/subsite/clinical','/subsite/emergency','/subsite/news/post']as$path){$_SERVER['REQUEST_URI']=$path;$assert(!WelcomeFrequency::eligible_route(),'Task route excluded '.$path);}
$_SERVER['REQUEST_URI']='/subsite/';$GLOBALS['safe_mode']=true;
$assert(!WelcomeFrequency::eligible_route(),'Safe mode suppression');$GLOBALS['safe_mode']=false;
$GLOBALS['logged_in']=true;$GLOBALS['user_meta'][7][WelcomeFrequency::USER_META]=time()-120;
WelcomeFrequency::prepare();ob_start();WelcomeFrequency::render();$out=ob_get_clean();$assert(''===$out,'Recent member dismissal suppressed');
$prop=new \ReflectionProperty(WelcomeFrequency::class,'prepared');$prop->setAccessible(true);$prop->setValue(null,false);
// A guest cookie must never suppress an independent logged-in account.
$GLOBALS['user_id']=8;$_COOKIE[WelcomeFrequency::GUEST_COOKIE]=(string)time();
WelcomeFrequency::prepare();ob_start();WelcomeFrequency::render();$account8=ob_get_clean();
$assert(strpos($account8,'sabri-shell-welcome-host')!==false,'Account must not inherit guest or another account dismissal');
$prop->setValue(null,false);$GLOBALS['user_id']=7;unset($_COOKIE[WelcomeFrequency::GUEST_COOKIE]);
$assert(WelcomeFrequency::cookie_path()==='/subsite/','Guest cookie must use site-local WordPress path');
$GLOBALS['user_meta'][7][WelcomeFrequency::USER_META]=time()-2592100;WelcomeFrequency::prepare();
ob_start();WelcomeFrequency::render();$out=ob_get_clean();$assert(strpos($out,'sabri-shell-welcome-host')!==false,'Due welcome supplied by File25');
WelcomeFrequency::enqueue();$assert(in_array('sabri-shell-welcome-owner',$GLOBALS['styles'],true),'Structural CSS enqueued');
$assert(($GLOBALS['localized']['loggedIn']??null)===true,'Member context');
class Req{private $nonce;public function __construct($nonce){$this->nonce=$nonce;}public function get_header($key){return $this->nonce;}}
$bad=WelcomeFrequency::dismiss(new Req('invalid'));$assert($bad instanceof \WP_Error&&$bad->get_error_code()==='sabri_welcome_forbidden','Invalid CSRF rejected');
$good=WelcomeFrequency::dismiss(new Req('valid'));$assert(is_array($good)&&$good['saved']===true,'Authenticated preference persisted');
$writes=$GLOBALS['writes'];$again=WelcomeFrequency::dismiss(new Req('valid'));
$assert(is_array($again)&&$again['next_eligible_at']===$good['next_eligible_at'],'Repeat dismiss must retain original 30-day deadline');
$assert($GLOBALS['writes']===$writes,'Repeat dismiss must not rewrite user meta');
$GLOBALS['logged_in']=false;$bad=WelcomeFrequency::dismiss(new Req('valid'));$assert($bad instanceof \WP_Error,'Guest cannot mutate account meta');
echo "File20 welcome frequency/dismissal source regression PASS\n";
}
