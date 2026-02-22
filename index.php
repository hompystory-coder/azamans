<?php
//==============================================================================
// SESSION 설정
//------------------------------------------------------------------------------
@ini_set("session.use_trans_sid", 0);    // PHPSESSID를 자동으로 넘기지 않음
@ini_set("url_rewriter.tags",""); // 링크에 PHPSESSID가 따라다니는것을 무력화함 (해뜰녘님께서 알려주셨습니다.)

session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/session');

if (isset($SESSION_CACHE_LIMITER))
    @session_cache_limiter($SESSION_CACHE_LIMITER);
else
    @session_cache_limiter("no-cache, must-revalidate");

ini_set("session.cache_expire", 180); // 세션 캐쉬 보관시간 (분)
ini_set("session.gc_maxlifetime", 10800); // session data의 garbage collection 존재 기간을 지정 (초)
ini_set("session.gc_probability", 1); // session.gc_probability는 session.gc_divisor와 연계하여 gc(쓰레기 수거) 루틴의 시작 확률을 관리합니다. 기본값은 1입니다. 자세한 내용은 session.gc_divisor를 참고하십시오.
ini_set("session.gc_divisor", 100); // session.gc_divisor는 session.gc_probability와 결합하여 각 세션 초기화 시에 gc(쓰레기 수거) 프로세스를 시작할 확률을 정의합니다. 확률은 gc_probability/gc_divisor를 사용하여 계산합니다. 즉, 1/100은 각 요청시에 GC 프로세스를 시작할 확률이 1%입니다. session.gc_divisor의 기본값은 100입니다.

session_set_cookie_params(0, '/');
ini_set("session.cookie_domain", '');

session_start();

//ddos 차단
function check_and_block_repeated_requests() {
    
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $current_page = $_SERVER['REQUEST_URI'];

    $key = $user_ip . '_' . $current_page;

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = array('timestamp' => time(), 'count' => 0);
    }

    // Check if user has accessed the page within the last 60 seconds
    if (time() - $_SESSION[$key]['timestamp'] < 60) {
        $_SESSION[$key]['count'] += 1;
    } else {
        $_SESSION[$key] = array('timestamp' => time(), 'count' => 0);
    }

    // If user accessed the page more than 10 times in the last 60 seconds, block them
    if ($_SESSION[$key]['count'] > 10) {
        die('You have been temporarily blocked due to excessive requests.');
    }
}

check_and_block_repeated_requests();
//ddos 차단

header("Content-type:text/html;charset=utf-8");
//header('X-Frame-Options: SAMEORIGIN'); 
//Header ('X-Frame-Options DENY');
//Header ('X-Content-Type-Options nosniff');
//Header ('X-XSS-Protection 1; mode=block');
$mediabaro_request = "/?r=s151712&amp;m=news&amp;mod=info&amp;tab=info-inquiry&amp;parent=inquiry";
if($_SERVER[ "REQUEST_URI" ]==$mediabaro_request) {
$mediabaro_replace = str_replace("&amp;","&",$mediabaro_request);
$mediabaro_url = $g['s'].$mediabaro_replace;
header('Location: '.$mediabaro_url);
}

$REQUEST_URI = $_SERVER['REQUEST_URI'];
$idxx = explode("/",$REQUEST_URI);	
// Array ( [0] => [1] => news [2] => 292598 [3] => 192976 )

/*
if($idxx[1]=="news" && $idxx[3]) {
	  
   //print_r($idxx);
   
   if($_SERVER['SERVER_PORT'] == 80)  {
   $_newsurl = "http://".$_SERVER['HTTP_HOST']."/news/".$idxx[2];	
   }else{
   $_newsurl = "https://".$_SERVER['HTTP_HOST']."/news/".$idxx[2];
   }	
   
   //echo $_newsurl;
	
   header('Location: '.$_newsurl);
   exit;
}
*/


$protocol = isset($_SERVER["HTTPS"]) ? 1 : 0;

//echo $protocol;

define('HTTPS', $protocol);

if($_SERVER['SERVER_NAME'] != "news.homp.kr" && $_SERVER['SERVER_NAME'] != "skin1.homp.kr" && $_SERVER['SERVER_NAME'] != "skin2.homp.kr" && $_SERVER['SERVER_NAME'] != "skin3.homp.kr" && $_SERVER['SERVER_NAME'] != "skin4.homp.kr" && $_SERVER['SERVER_NAME'] != "skin5.homp.kr" && $_SERVER['SERVER_NAME'] != "skin6.homp.kr" && $_SERVER['SERVER_NAME'] != "skin7.homp.kr" && $_SERVER['SERVER_NAME'] != "skin8.homp.kr" && $_SERVER['SERVER_NAME'] != "skin9.homp.kr" && $_SERVER['SERVER_NAME'] != "skin10.homp.kr" && $_SERVER['SERVER_NAME'] != "skin11.homp.kr" && $_SERVER['SERVER_NAME'] != "skin12.homp.kr" && $_SERVER['SERVER_NAME'] != "sisadays.homp.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: http://www.".$str.$REQUEST_URI);
   }

}

define('__KIMS__',true);


if(!get_magic_quotes_gpc())
{
	if (is_array($_GET))
		foreach($_GET as $_tmp['k'] => $_tmp['v'])
			if (is_array($_GET[$_tmp['k']]))
				foreach($_GET[$_tmp['k']] as $_tmp['k1'] => $_tmp['v1']) 
					$_GET[$_tmp['k']][$_tmp['k1']] = ${$_tmp['k']}[$_tmp['k1']] = addslashes($_tmp['v1']); 
			else $_GET[$_tmp['k']] = ${$_tmp['k']} = addslashes($_tmp['v']);
	if (is_array($_POST))
		foreach($_POST as $_tmp['k'] => $_tmp['v'])
			if (is_array($_POST[$_tmp['k']]))
				foreach($_POST[$_tmp['k']] as $_tmp['k1'] => $_tmp['v1']) 
					$_POST[$_tmp['k']][$_tmp['k1']] = ${$_tmp['k']}[$_tmp['k1']] = addslashes($_tmp['v1']);
			else $_POST[$_tmp['k']] = ${$_tmp['k']} = addslashes($_tmp['v']);
}
else {
	if (!ini_get('register_globals'))
	{
		extract($_GET);
		extract($_POST);
	}
}

$d = array();

//if(isset($_GET['r'])) {
//$_SESSION['account'] = $_GET['r'];
//}

//$r = $_SESSION['account'];

$url = "";
if (isset($_GET['url'])) {
   $url = rtrim($_GET['url'], '/');
   $url = filter_var($url, FILTER_SANITIZE_URL);
}
$url_params = explode('/', $url);
$url_counts = count($url_params);

if(HTTPS ==0) {


}


$NEWS_ROOT = './';

$g = array(
	'path_root'   => $NEWS_ROOT,
	'path_app'   => $NEWS_ROOT.'application/',
	'path_core'   => $NEWS_ROOT.'_core/',
	'path_var'    => $NEWS_ROOT.'_var/',
	'path_tmp'    => $NEWS_ROOT.'_tmp/',
	'path_layout' => $NEWS_ROOT.'layouts/',
	'path_module' => $NEWS_ROOT.'modules/',
	'path_widget' => $NEWS_ROOT.'widgets/',
	'path_switch' => $NEWS_ROOT.'switches/',
	'path_plugin' => $NEWS_ROOT.'plugins/',
	'path_page'   => $NEWS_ROOT.'pages/',
	'path_file'   => $NEWS_ROOT.'files/'
);

$g['time_split'] = explode(' ',microtime());
$g['time_start'] = $g['time_split'][0]+$g['time_split'][1];
$g['time_srnad'] = $g['time_split'][1].substr($g['time_split'][0],2,6);

if (!is_file($g['path_var'].'db.info.php'))
{
	include $g['path_root'].'/_install/'.($install?$install:'main').'.php';
	exit;
}

require $g['path_var'].'db.info.php';
require $g['path_var'].'table.info.php';


require $g['path_var'].'switch.var.php';
require $g['path_var'].'plugin.var.php';
require $g['path_module'].'admin/var/var.system.php';

$g['url_file'] = str_replace('/index.php','',$_SERVER['SCRIPT_NAME']);
$g['url_host'] = 'http'.($_SERVER['HTTPS']=='on'?'s':'').'://'.$_SERVER['HTTP_HOST'];
$g['url_http'] = $g['url_host'].($d['admin']['http_port']&&$d['admin']['http_port']!=80?':'.$d['admin']['http_port']:'');
$g['url_sslp'] = 'https://'.$_SERVER['HTTP_HOST'].($_SERVER['HTTPS']!='on'&&$d['admin']['ssl_port']?':'.$d['admin']['ssl_port']:'');
$g['url_root'] = $g['url_http'].$g['url_file'];
$g['ssl_root'] = $g['url_sslp'].$g['url_file'];

//국가 확인
if(!$_SESSION['site_country']) {

   // 한글 방문자
   if(preg_match('/ko/', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
   $_SESSION['site_country'] = "1";
   }
   // 일본어 방문자
   //elseif(preg_match('/jp/', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
   //$_SESSION['site_country'] = "japan";
   //}
   // 중국어 방문자
   //elseif(preg_match('/zh/', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
   //$_SESSION['site_country'] = "china";
   //}
   // 중국어 방문자
   //elseif(preg_match('/zh-ch/', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
   //$_SESSION['site_country'] = "china";
   //}
   // 중국어 방문자
   //elseif(preg_match('/zh-hk/', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
   //$_SESSION['site_country'] = "china";
   //}
   // 영문 방문자
   else  {
   $_SESSION['site_country'] = "2";
   }

}

define('SITE_COUNTRY', $_SESSION['site_country']);
//국가 확인

//echo SITE_COUNTRY;

$protocol = isset($_SERVER["HTTPS"]) ? 1 : 0;

//echo $protocol;

define('HTTPS', $protocol);
//define('IMGURL', 'https://'.$_SERVER['SERVER_NAME']);
define('IMGURL', 'https://www.eanews.kr/');

//http://www.xn--289ak2ihzp79e9oay23d.xn--3e0b707e/

if($_SERVER['SERVER_NAME'] == "www.xn--289ak2ihzp79e9oay23d.xn--3e0b707e") {

   if($url_params[0] && $url_params[1]) {
   header("location: http://www.ecnews.kr/".$url_params[0].'/'.$url_params[1]);
   }else{
  header("location: http://www.ecnews.kr/".$REQUEST_URI);
   }

}

if($_SERVER['SERVER_NAME'] == "xn--v69ao7jiogrwrlqf.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
}

if($_SERVER['SERVER_NAME'] == "patrontimes.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "new-magazine.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "www.new-magazine.com") {

   if($_SERVER['SERVER_PORT'] == 80) {
   $str = $_SERVER['SERVER_NAME'];
   header("location: https://".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "xn--289a1ms3quthb6mwpdis4a.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "xn--289a76kutgrtgv8bv5g3a29zs5b.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "bunews.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "devtimes.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "lifetimenews.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "emotoonnews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "hswemagazine.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "timeofkairos.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "easyschool.ai.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "sallimji.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "kairnews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "koreamagazine.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "muhucimin.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "jinhakin.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "misik.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "edu-focus.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "baumnews.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "budongsanissue.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "jijachenews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "allrevenews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}


if($_SERVER['SERVER_NAME'] == "shift.or.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "kfestival.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "koreanportalnews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "braintoktoknews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "kidsnews.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "esgtimes.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "brainpluslife.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "aiethicsnews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "jonathanbooktrip.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}


if($_SERVER['SERVER_NAME'] == "woman-grow.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}


if($_SERVER['SERVER_NAME'] == "xn--2f1b3b555b93f.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "soft-journal.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "chnews.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "heartdayrest.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "kntv.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "i2mnews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}


if($_SERVER['SERVER_NAME'] == "moregood.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "ailifemaker.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "k-hongiktimes.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "careeronnews.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "soundnect.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "xn--p50bp6an2ch9xbld28dr0dbrv.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "psydev.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "mindecho.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "storynlink.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "wisefocus.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "coanews.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "donghwa.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "everfitnews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "xn--vg1b03zi5amgu2x.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "realvalue.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "aipedia.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "botongmedia.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "lifenjob.news") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "ildabom.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "k-aspa.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "okinawapost.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "aiarchijournal.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "creativeknews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "koreakdksdkkiiin.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "kjob.news") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "xn--2n1bp39aka07dq61b.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "teacherin.io.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "thesilver.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "familytrip.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "k-ldnews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "bakeryilbo.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "samrangnews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "kcsnews.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "100hij.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "inkwononair.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "k-historyland.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "changelifetimes.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "hdh365.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "discovery-me.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "xn--vk1bu2qz0gqkcotao45d.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "happyrecipe.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "kstudynews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "supervs.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "nexlearn.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "vitalsciencejournal.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "booktrip.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "smevnews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "smevnews.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "xn--tv-o81i327d4zfzyd.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "kcggarden.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}
////////////////////////
if($_SERVER['SERVER_NAME'] == "wwsnews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "allinnews.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "dentalmedical.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}


if($_SERVER['SERVER_NAME'] == "hypnopsych.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "mediabaro.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "toptvkorea.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "edumine.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "bizfocus.ai.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "ssicho.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "leaderskr.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "leaderskr.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "newbs.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "koreahealthtimes.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "newstam.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "visiondailynews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "ainewstv.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "retimes.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "mediaullim.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "imaginarypocus.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "makerinsight.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "wellnesslifenews.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "essaytimes.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "globalkorea.net") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "digiedutimes.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "realedun.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "peoplesociety.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "bizpost.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "xn--tv-o81is98db3hkjw.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}

if($_SERVER['SERVER_NAME'] == "cbnf.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
   header("location: https://www.".$str.$REQUEST_URI);
   }
   
}
////////////////////////////////////////////////


if($_SERVER['SERVER_NAME'] == "nplnews.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
	   
      if($_SERVER['SERVER_PORT'] == 80)  {
         if (!preg_match('/www/', $str) == true) {
         header("location: https://www.".$str.$REQUEST_URI);
         }else{
         header("location: https://".$str.$REQUEST_URI);		  
	     }
      }
   }
}


if($_SERVER['SERVER_NAME'] == "tomatowavenews.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
	   
      if($_SERVER['SERVER_PORT'] == 80)  {
         if (!preg_match('/www/', $str) == true) {
         header("location: https://www.".$str.$REQUEST_URI);
         }else{
         header("location: https://".$str.$REQUEST_URI);		  
	     }
      }
   }
}


if($_SERVER['SERVER_NAME'] == "www.nplnews.kr") {

   $str = $_SERVER['SERVER_NAME'];
	   
      if($_SERVER['SERVER_PORT'] == 80)  {
         header("location: https://".$str.$REQUEST_URI);		  
      }
}

if($_SERVER['SERVER_NAME'] == "www.iijanews.com") {

   $str = $_SERVER['SERVER_NAME'];
	   
      if($_SERVER['SERVER_PORT'] == 80)  {
         header("location: https://".$str.$REQUEST_URI);		  
      }
}


if($_SERVER['SERVER_NAME'] == "tourmagazine.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
	   
      if($_SERVER['SERVER_PORT'] == 80)  {
         if (!preg_match('/www/', $str) == true) {
         header("location: https://www.".$str.$REQUEST_URI);
         }else{
         header("location: https://".$str.$REQUEST_URI);		  
	     }
      }
   }
}


if($_SERVER['SERVER_NAME'] == "www.tourmagazine.co.kr") {

   $str = $_SERVER['SERVER_NAME'];
	   
      if($_SERVER['SERVER_PORT'] == 80)  {
         header("location: https://".$str.$REQUEST_URI);		  
      }
}

if($_SERVER['SERVER_NAME'] == "xn--n-in8em82berc89f.com") {

   $str = $_SERVER['SERVER_NAME'];
   if (!preg_match('/www/', $str) == true) {
	   
      if($_SERVER['SERVER_PORT'] == 80)  {
         if (!preg_match('/www/', $str) == true) {
         header("location: https://www.".$str.$REQUEST_URI);
         }else{
         header("location: https://".$str.$REQUEST_URI);		  
	     }
      }
   }
}

if($_SERVER['SERVER_NAME'] == "www.xn--n-in8em82berc89f.com") {

   $str = $_SERVER['SERVER_NAME'];
	   
      if($_SERVER['SERVER_PORT'] == 80)  {
         header("location: https://".$str.$REQUEST_URI);		  
      }
}


require $g['path_core'].'function/db.mysql.func.php';
require $g['path_core'].'function/sys.func.php';

if($url_params[0]=="sitemap" || $url_params[0]=="rss" || $url_params[0]=="zum") {

$DB_CONNECT = isConnectedToDB($DB);

require 'application/libs/application.php';
require 'application/libs/controller.php';
$app = new Application();


}else{

foreach(getSwitchInc('start') as $_switch) include $_switch;
require $g['path_core'].'engine/main.engine.php';

//네이버 신디케이션 연동된것
if($newsuid) {
$uid   = $newsuid;

$N = getUidData($table['newsdata'],$newsuid);
$cdata  = $N['cdata'];

if($N['site']==$s){ 
$menuc = $cdata;
}else{
$menuc = "11";
}

getLink($g['s'].'/?r='.$r.'&m=news&mod=view&c='.$menuc.'&uid='.$newsuid,'parent.','','');

}
//네이버 신디케이션 연동된것

//리셀러 id
$_RS = getDbData($table['newsreseller'],"id='".$reseller."'",'*');
if($_RS['id']) {
$_SESSION['resellerid'] = $_RS['id'];
}
//리셀러 id

if ($keyword)
{
	$keyword = trim($keyword);
	$_keyword= stripslashes(htmlspecialchars($keyword));
}
if (!$p) $p = 1;
if (!is_dir($g['path_module'].$m)) $m = $g['sys_module'];
$g['dir_module'] = $g['path_module'].$m.'/';
$g['url_module'] = $g['s'].'/modules/'.$m;

if ($a) require $g['path_core'].'engine/action.engine.php';
//미납연장 정보 확인
$_NX = getDbData($table['newsmember'],"sitecode='".$s."'",'*');

if (!$my['admin'] && ($_HS['open'] > 1 || $_NX['step']==8)) require $g['path_core'].'engine/siteopen.engine.php';
if (!$s && $m != 'admin') getLink($g['s'].'/?m=admin&module='.$g['sys_module'].'&nosite=Y','','','');
//미납연장 정보 확인

if($url_params[0]) $g['main'] = 'test';
elseif($modal) $g['main'] = $g['path_module'].$modal.'.php';
else include $g['dir_module'].'main.php';

if ($m=='admin' || $iframe=='Y') $d['layout']['php'] = $_HM['layout'] = '_blank/default.php';
else {
	if (!$g['mobile']||$_SESSION['pcmode']=='Y') $d['layout']['php'] = $prelayout ? $prelayout.'.php' : ($_HM['layout'] ? $_HM['layout'] : $_HS['layout']);
	else $d['layout']['php'] = $prelayout ? $prelayout.'.php' : ($_HS['m_layout'] ? $_HS['m_layout'] : ($_HM['layout'] ? $_HM['layout'] : $_HS['layout']));
}

$d['layout']['dir'] = dirname($d['layout']['php']);
//$g['dir_layout'] = $g['path_layout'].$d['layout']['dir'].'/';
$g['dir_layout'] = 'layouts/'.$d['layout']['dir'].'/';

$g['url_layout'] = $g['s'].'/layouts/'.$d['layout']['dir'];
$g['img_layout'] = $g['url_layout'].'/_images';

define('__KIMS_CONTENT__',$g['path_core'].'engine/content.engine.php');

if($my['admin'] && (!$_SERVER['HTTP_REFERER'] || $panel=='Y') && $panel!='N' && !$iframe && !is_file($g['dir_layout'].'_var/nopanel.txt')) 
{
	include $g['path_core'].'engine/adminpanel.engine.php';
}
else
{
	foreach($g['switch_1'] as $_switch) include $_switch;

	if ($m!='admin')
	{
		$sitephp_file = $g['path_var'].'sitephp/'.$_HS['uid'].'.php'; if(is_file($sitephp_file)) include $sitephp_file;
		if($_HS['buffer'])
		{
			$g['buffer']=true;
			ob_start('ob_gzhandler');
		}
	}

	$g['location']	= getLocation(0);
	$g['browtitle'] = getPageTitile();
	include $NEWS_ROOT.'layouts/'.$d['layout']['dir'].'/_includes/_import.control.php';
	include $g['path_layout'].$d['layout']['php'];
	foreach($g['switch_4'] as $_switch) include $_switch;
	echo "\n".'<!-- v.'.$d['admin']['version'].' / Runtime : '.round(getCurrentDate()-$g['time_start'],3).' -->';
	//if($g['buffer']) ob_end_flush();
}

db_close($DB_CONNECT);

}
?>