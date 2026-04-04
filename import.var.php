<?php
date_default_timezone_set('Asia/Seoul');

//���� ī����
$_nvar_path = $g['path_module'].'news/upload/newsnum';

if (!is_dir($_nvar_path))
{
		mkdir($_nvar_path,0707);
		@chmod($_nvar_path,0707);
}

$doday = date("Ymd");
   
if(!is_file($_nvar_path.'/'.$r.'_newsnum_'.$doday.'.var.php')) {
	
   $_csqlque = 'site='.$s.' and auth=1 and share=1';
   $NEWS_COUNT = getDbRows($table['newsindex'],$_csqlque);		

   $_cofile = $_nvar_path.'/'.$r.'_newsnum_'.$doday.'.var.php';

   $fp = fopen($_cofile,'w');
   fwrite($fp, "<?php\n");

   fwrite($fp, "\$d['news']['news_count'] = \"".${'NEWS_COUNT'}."\";\n");

   fwrite($fp, "?>");
   fclose($fp);
   @chmod($_cofile,0707);
   
   $params = array(
      'action'   => 'news_count',
      'sitecode'   => $s,
      'num'   => $NEWS_COUNT
   );

   postnews('https://www.eanews.kr/modules/news/action/a.news_count_num.php', $params);
   postnews('http://news.k-topnews.com/modules/news/action/a.news_count_num.php', $params);
   postnews('https://www.newsyonhap.com/modules/news/action/a.news_count_num.php', $params);
   postnews('https://www.spaceinews.com/modules/news/action/a.news_count_num.php', $params);	      
   postnews('https://www.youthassembly.kr/modules/news/action/a.news_count_num.php', $params);	   
   postnews('https://www.k-youtube.com/modules/news/action/a.news_count_num.php', $params);	   
   postnews('https://www.alphanews.kr.kr/modules/news/action/a.ajax_send_news_share_action.php', $params);
   postnews('https://www.changupnews.kr/modules/news/action/a.news_count_num.php', $params);   
   postnews('http://news.homp.kr/modules/news/action/a.ajax_send_news_share_action.php', $params);     
   postnews('http://news1.homp.kr/modules/news/action/a.news_count_num.php', $params);
   postnews('http://news2.homp.kr/modules/news/action/a.news_count_num.php', $params);		
   postnews('https://www.koreaiin.com/modules/news/action/a.news_count_num.php', $params);				

}
//����ī����

//�̹��� ���� ����
include_once $g['path_root'].'_img_server_var.php'; //���� ����;
define('NEWS_IMG_SERVER', $d['news']['img_server_url']);
//�̹��� ���� ����

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

$vip = $_SERVER['REMOTE_ADDR']; // ������ �ּҹ���

if(!isset($_SESSION['country_code'])) {

$_CTIP=db_fetch_array(db_query("select * from ip2nation WHERE ip < INET_ATON('{$vip}') ORDER BY ip DESC LIMIT 0,1 ",$DB_CONNECT));

$_SESSION['country_code'] = $_CTIP['country'];
}

// �̱� us
//�븸 th
//������, fr
//����, uk
//�ױ۷���
//�߱� cn
//���þ� ru

//if($_CTIP['country'] !="kr" && $_CTIP['country'] !="us" && $_CTIP['country'] !="fr" && $_CTIP['country'] !="th" && $_CTIP['country'] !="uk" && $_CTIP['country'] !="cn") {
//exit;
//}


//������
$browser = getBrowzer($_SERVER['HTTP_USER_AGENT']);

$_SEO = getDbData($table['s_seo'],'rel=0 and parent='.$_HS['uid'],'*');

//��������
$check_day = date("Ymd");
//$service_check = getDbData($table[$m.'service'],"d_day='".$check_day."'",'*');

$siteinfo= getDbData($table['newsmember'],'sitecode='.$s,'*');

$service_date = getDateFormat($siteinfo['d_finish'],'Y-m-d');
$service_todate = date("Y-m-d", time()); //���糯¥
$service_finish = ( strtotime($service_date) - strtotime($service_todate) ) / 86400; //���� ��������



//��������

if(isset($_SESSION['site_language'])==1) {
$mlang = "ko";
}elseif(isset($_SESSION['site_language'])==2) {
$mlang = "en";
}elseif(isset($_SESSION['site_language'])==3) {
$mlang = "jp";
}elseif(isset($_SESSION['site_language'])==4) {
$mlang = "cn";
}else {
$mlang = "ko";
}

if($_SERVER['SERVER_NAME'] == "news.hompystory.com" || $_SERVER['SERVER_NAME'] == "news.homp.kr") {
$NEWS_VIEW = $g['url_host'].'/?r='.$r.'&amp;newsuid=';
//$NEWS_VIEW = $g['url_host'].'/news/';
}else{
$NEWS_VIEW = $g['url_host'].'/news/';
}

$NEWS_PREVIEW = $g['url_host'].'/preview/';

$CLICK_URL = $g['url_host'].$_SERVER['REQUEST_URI'];

$menuact = explode("/",$c);
$submenu = getDbRows($menutable,'parent='.$menuact[0]);

$menucnt = count($menuact);

//Ȩ �и�
if($r =="home") {
$menutable = $table['s_menu'];
$pagetable = $table['s_page'];
$grouptable = $table['s_mbrgroup'];
}else{
$menutable = $table[$r.'_menu'];
$pagetable = $table[$r.'_page'];
$grouptable = $table[$r.'_mbrgroup'];
}

$url = "";
if (isset($_GET['url'])) {
   $url = rtrim($_GET['url'], '/');
   $url = filter_var($url, FILTER_SANITIZE_URL);
}
$params = explode('/', $url);
$counts = count($params);

//$params = isset($params) ? $params : '';

//echo $params[0];

//echo $g['url_layout'];


//��Ÿ����
if($m=="news" && $mod=="info") {

       if($m=="news" && $mod=="info" && $uid){

	   $MN = getUidData($table['footermenu'],$uid);
       $g['meta_tit'] = $MN['name'];

	  }else {

      $_SMENUS=getDbSelect($table['footermenu'],'site='.$s.' and hidden=0 and depth=1 order by gid asc','*');
	  while($_SM=db_fetch_array($_SMENUS)) {	

	  if($tab=="info-".$_SM['id'] || $parent== $_SM['id']){
      $g['meta_tit'] = $_SM['name'];
      }

	  if($_SM['is_child'] && $parent==$_SM['id']) { //2���޴�
      $_SMENUS2=getDbSelect($table['footermenu'],'site='.$s.' and parent='.$_SM['uid'].' and hidden=0 and depth=2 order by gid asc','*');
	  while($_SM2=db_fetch_array($_SMENUS2)) {		
     
	  if($tab=="info-".$_SM2['id']){
      $g['meta_tit'] = $_SM2['name'];
	  }

      } //endwhile
	  } //endif //2���޴�

      } //1���޴� endwhile

	  }

      $g['meta_type'] = 'website';
      $g['meta_key'] = $_SEO['keywords'];
      $g['meta_des'] = $_SEO['description'];
      $g['meta_bot'] = $_SEO['classification'];
      $g['meta_img'] = getMetaImage($_SEO['image_src']);
      $g['og_img'] = getMetaImage($_SEO['image_src']);	  
      $g['meta_url'] = $joint;
      $g['meta_date'] = getDateFormat($date['today'],"Y-m-d");
      $g['meta_update'] = getDateFormat($date['today'],"Y-m-d");

////////////////////
}elseif($m=="news" && $mod=="coupang_view" && $uid) {

$X = getUidData($table['newscoupang'],$uid);
$img_name = basename($R['product_img']);
$meta_img = explode(".",$img_name);
$g['meta_type'] = 'website';
$g['meta_img'] =  $X['product_img'];
$g['og_img'] =  $X['product_img'];
//$g['meta_img'] =  'https://www.ehom.kr/coupang/'.$R['product_id'].'/'.$meta_img[0].'.webp';//$X['product_img'];
$g['meta_tit']   = $X['product_name']." - ".$_HS['name'];
$g['meta_key'] = $X['keyword'];
$wr_content = str_replace("&nbsp;"," ",$X['product_name']);  // &nbsp;�� ��ĭ���� ��ü
$g['meta_des'] = getStrCut(strip_tags(nl2br($wr_content)),40,'');
$g['meta_url'] = $X['product_url'];
$g['tmeta_url'] = $g['url_host'].'/?r='.$r.'&m=news&mod=coupang_view&uid='.$X['uid'];
$g['meta_date'] = getDateFormat($X['d_regis'],"Y-m-d");
$g['meta_update'] = getDateFormat($X['d_regis'],"Y-m-d");

/////////////////////////


}elseif($m=="news" && isset($uid) || isset($params[1])) {

if($uid) {
$uid = $uid;
}elseif($params[1]) {
$uid = $params[1];
}

//230430 �߰�

       $N = getUidData($table['newsindex'],$uid);

	   if($N['uid']) {

 
		$indextable = $table['newsindex'];
		$datatable = $table['newsdata'];
		$contenttable = $table['newscontent'];
        $uploadtable = $table['newsnewsupload'];

	   }else{

		$indextable = $table['newsindexold'];
		$datatable = $table['newsdataold'];
		$contenttable = $table['newscontentold'];	
        $uploadtable = $table['newsnewsuploadold'];		
	   }

//230430 �߰�

if(isset($uid)) $X = getUidData($datatable,$uid);

$_C = getUidData($contenttable,$X['uid']);
$_IN = getUidData($indextable,$X['uid']);

//echo $params[0];
//echo $_CA;
$news_content = news_content_data ('../../../../../', $X['uid'], $X['account'], $X['d_regis']);
$news_blog = getStrCut(strip_tags($news_content),100,'...');

$g['meta_type'] = 'article';

$yp = explode(']',str_replace('[','',trim($X['youtube'])));

preg_match_all("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", $_C['content'], $matches);

if($matches[1][0]) {
$content_img = $matches[1][0];
}else{
$content_img = "";
}

$post_thumb = explode("&",$_IN['post_thumb']);

if($post_thumb[0]) {

$g['meta_img'] = $post_thumb[0];
$g['og_img'] = str_replace("https://hompgo.synology.me","https://www.ehom.kr",$post_thumb[0]);

}elseif($X['first_img']) { //��ǥ �̹��� ������

$U = getUidData($uploadtable,$X['first_img']);

$g['meta_img'] = "https://www.ehom.kr/news/".$U['folder']."/".$U['tmpname'];
$g['og_img'] = $g['url_root'].'/modules/news/upload/news/'.$U['account'].'/'.$U['folder'].'/'.$U['thumbname'];
//$g['meta_img'] = $g['url_root'].'/modules/news/upload/news/'.$U['account'].'/'.$U['folder'].'/'.$U['tmpname'];
//$g['fmeta_img'] = "https://www.ehom.kr/news/".$U['folder']."/".$U['tmpname'];

}elseif($X['upload']) {
$upx = explode(']',str_replace('[','',trim($X['upload'])));
$U = getUidData($uploadtable,$upx[0]);
if($s==234) { //�泲�������
$g['meta_img'] = "https://www.ehom.kr/news/".$U['folder']."/".$U['tmpname'];
}else{
$g['meta_img'] = "https://www.ehom.kr/news/".$U['folder']."/".$U['tmpname'];

//$g['meta_img'] = $U['url'].$U['folder'].'/modules/news/upload/news/'.$U['account'].'/'.$U['folder'].'/'.$U['tmpname'];
}
$g['og_img'] = $g['url_root'].'/modules/news/upload/news/'.$U['account'].'/'.$U['folder'].'/'.$U['thumbname'];

//221107 ���� �ѱ� ���ϸ����� ��ü
$g['meta_type'] = 'article';

/*
$_Z = getUidData($table['newsindex'],$X['uid']);

if($_Z['post_thumb']) { //�����
    
   $metaimg = explode("&",$_Z['post_thumb']);
   if($metaimg[2]) { //�ѱ����ϸ� �������
   $g['meta_img'] = $metaimg[2];
   }else{
   $g['meta_img'] = $metaimg[0];
   }

}elseif($U['caption']) { //�ѱ� ���ϸ� ������

   $imgname = basename($g['meta_img']);
   $tmp = explode('.', $imgname);
   $filename = $tmp[0];
   $caption = $U['caption'];
   $ext = $tmp[1];
    
   $g['meta_img'] = str_replace($imgname,$caption,$g['meta_img']);

}else{
$g['meta_img'] = $g['meta_img'];
}
*/
//221107 ���� �ѱ� ���ϸ����� ��ü

}elseif($yp[0]) {
$g['meta_img'] = "https://i.ytimg.com/vi/".$yp[0]."/hqdefault.jpg";
$g['og_img'] = "https://i.ytimg.com/vi/".$yp[0]."/hqdefault.jpg";

}elseif($content_img) {
$g['meta_img'] = $content_img;
$g['og_img'] = $content_img;


}else{
$g['meta_img'] = getOgImage($X['attach_image'],$X['upload'],$X['youtube'],$_C['content'],'jpg|jpeg|gif|png|bmp',$X['uid']);
$g['og_img'] = getOgImage($X['attach_image'],$X['upload'],$X['youtube'],$_C['content'],'jpg|jpeg|gif|png|bmp',$X['uid']);
}
//$g['meta_img'] = $X[''];

if($X['site']==16 && $X['category1']==22) {
$g['meta_tit']   = $X['subject'];
}else{
$g['meta_tit']   = $X['subject']." - ".$_HS['name'];
}

$g['meta_key'] = $X['tag'];
$g['meta_url'] = $g['url_root'].'/news/'.$X['uid'];
$g['meta_date'] = getDateFormat($X['d_regis'],"Y-m-d");
$g['meta_update'] = getDateFormat($X['d_regis'],"Y-m-d");

$wr_content = str_replace("&nbsp;"," ",$news_content);  // &nbsp;�� ��ĭ���� ��ü
$g['meta_des'] = getStrCut(strip_tags(nl2br($wr_content)),200,'');

$_CATA = getUidData('rb_'.$r.'_menu',$X['category1']);

}elseif($m=="bskrbbs" && $uid) {

$X = getUidData($table['bskrbbsdata'],$uid);

$g['meta_type'] = 'article';

//$g['meta_img'] =getUploadImage($m,$X['youtubes'],$X['photo'],$X['content'],'jpg|jpeg|gif|png',$X['d_regis']);
$meta_img =getUploadImage($m,$X['photo'],$X['content'],'jpg|jpeg|gif|png',$X['uid']);

$g['meta_img'] = $meta_img ? $meta_img : $_SEO['image_src'];
$g['og_img'] = $meta_img ? $meta_img : str_replace("http://","https://",$_SEO['image_src']);

$g['meta_tit']   = $X['subject']." - ".$_HS['name'];
$g['meta_key'] = $X['tag'];
$wr_content = str_replace("&nbsp;"," ",$X['content']);  // &nbsp;�� ��ĭ���� ��ü
$g['meta_des'] = getStrCut(strip_tags(nl2br($wr_content)),40,'');
$g['meta_url'] = $g['url_root'].'/?r='.$r.'&m='.$m.'&bid='.$bid.'&uid='.$X['uid'];
$g['meta_date'] = getDateFormat($X['d_regis'],"Y-m-d");
$g['meta_update'] = getDateFormat($X['d_modify'],"Y-m-d");

//echo $g['meta_img'];

}elseif($m=="bskrbbs" && $bid && !$uid) {

$X = getDbData($table['bskrbbslist'],"id='".$bid."'",'*');

$g['meta_type'] = 'article';
$g['meta_tit'] = $X['name']." - ".$_HS['name'];
$g['meta_key'] = $_SEO['keywords'];
$g['meta_des'] = $_SEO['description'];
$g['meta_bot'] = $_SEO['classification'];
$g['meta_img'] = $_SEO['image_src'];
$g['og_img'] = $_SEO['image_src'];
$g['meta_url'] = $g['url_root'].'/?r='.$r.'&m='.$m.'&bid='.$bid;
$g['meta_date'] = getDateFormat($date['today'],"Y-m-d");
$g['meta_update'] = getDateFormat($date['today'],"Y-m-d");

}elseif($menuact[0]) {

$X = getUidData($menutable,$menuact[0]);

$g['meta_type'] = 'website';
$g['meta_tit'] = $_SEO['title'].'-'.$X['name'];
$g['meta_key'] = $_SEO['keywords'];
$g['meta_des'] = $_SEO['description'];
$g['meta_bot'] = $_SEO['classification'];
$g['meta_img'] = $_SEO['image_src'];
$g['og_img'] = $_SEO['image_src'];
$g['meta_url'] = $g['url_root'].'/?r='.$r.'&c='.$c;
$g['meta_date'] = getDateFormat($date['today'],"Y-m-d");
$g['meta_update'] = getDateFormat($X['d_last'],"Y-m-d");

//echo $X['uid'];

}else{

$g['meta_type'] = 'website';
$g['meta_tit'] = $_SEO['title'];
$g['meta_key'] = $_SEO['keywords'];
$g['meta_des'] = $_SEO['description'];
$g['meta_bot'] = $_SEO['classification'];
$g['meta_img'] = $_SEO['image_src'];
$g['og_img'] = $_SEO['image_src'];
$g['meta_url'] = $g['url_root'];
$g['meta_date'] = getDateFormat($date['today'],"Y-m-d");
$g['meta_update'] = getDateFormat($date['today'],"Y-m-d");
}
//��Ÿ����

//echo getMetaImage($_SEO['image_src']);

//��������
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'.table.info.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'.table.info.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'.join.var.php'))  include_once $g['path_var'].'site/'.$r.'/'.$r.'.join.var.php';
//����Ȯ��

//������������
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'.editor.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'.editor.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_sns.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_sns.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_search.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_search.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_send.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_send.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_perm.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_perm.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_share.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_share.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_perm.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_perm.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'.watermark.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'.watermark.var.php';

//������Ų
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_skin_select.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_skin_select.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_skin_select_mobile.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_skin_select_mobile.var.php';

$d['layout']['news_skin'] = $d['layout']['news_skin'] ? $d['layout']['news_skin'] : 'skin1';
$d['layout']['news_skin_mobile'] = $d['layout']['news_skin_mobile'] ? $d['layout']['news_skin_mobile'] : 'skin1';

$s_skin_var =   $g['path_var'].'site/'.$r.'/'.$r.'_news_skin.var.php'; //���� ����
$d_skin_var =  $g['path_var'].'site/'.$r.'/'.$r.'_news_skin1.var.php'; //����� ����

if(!is_file($d_skin_var)){ //
   copy($s_skin_var, $d_skin_var);
   @chmod($d_skin_var,0707);
}

$s_skin_mobile_var =   $g['path_var'].'site/'.$r.'/'.$r.'_news_skin_mobile.var.php'; //���� ����
$d_skin_mobile_var =  $g['path_var'].'site/'.$r.'/'.$r.'_news_skin1_mobile.var.php'; //����� ����

if(!is_file($d_skin_mobile_var)){ //
   copy($s_skin_mobile_var, $d_skin_mobile_var);
   @chmod($d_skin_mobile_var,0707);
}

//��Ų2
$s_skin2_var =   $g['path_var'].'site/s165326/s165326_news_skin2.var.php'; //���� ����
$d_skin2_var =  $g['path_var'].'site/'.$r.'/'.$r.'_news_skin2.var.php'; //����� ����

if(!is_file($d_skin2_var)){ //
   copy($s_skin2_var, $d_skin2_var);
   @chmod($d_skin2_var,0707);
}

$s_skin2_mobile_var =   $g['path_var'].'site/s165326/s165326_news_skin2_mobile.var.php'; //���� ����
$d_skin2_mobile_var =  $g['path_var'].'site/'.$r.'/'.$r.'_news_skin2_mobile.var.php'; //����� ����

if(!is_file($d_skin2_mobile_var)){ //
   copy($s_skin2_mobile_var, $d_skin2_mobile_var);
   @chmod($d_skin2_mobile_var,0707);
}

if($d['layout']['news_skin']=="skin1") {

   if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_skin1.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_skin1.var.php';

}elseif($d['layout']['news_skin']=="skin2"){

   if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_skin2.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_skin2.var.php';

}

if($d['layout']['news_skin_mobile']=="skin1") {
   if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_skin1_mobile.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_skin1_mobile.var.php';
}elseif($d['layout']['news_skin_mobile']=="skin2"){
   if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_skin2_mobile.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_skin2_mobile.var.php';
}


if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_side.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_side.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_banner.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_banner.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_banner_price.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_banner_price.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_info.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_info.var.php';

//������
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_shop.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_shop.var.php';

//���α�����
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_blogpay.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_blogpay.var.php';

//����
if(is_file($g['path_var'].'site/'.$r.'/'.$r.'_news_coupang.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_coupang.var.php';

$playout = "skinx-skin";
$mlayout =  "skinx-skin";


//pc
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.intro.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.intro.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.header.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.header.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.layout.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.layout.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.slider.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.slider.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.main.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.main.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.sub.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.sub.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.footer.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.footer.var.php';

//mobile
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mintro.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mintro.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mheader.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mheader.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mlayout.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mlayout.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mslider.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mslider.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mmain.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mmain.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.msub.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.msub.var.php';
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mfooter.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$mlayout.'.mfooter.var.php';

//echo $d['layout']['lang_default'];

//�������� ó��

	$RJPath = $g['path_module'].'news/upload/news/content/'.$r.'/news_share/';

	if (!is_dir($RJPath))
	{    
	   mkdir($RJPath,0707);
	   @chmod($RJPath,0707);
	}

   	//����Ʈ ACCEPT
    $rjaFile = $RJPath.$r.'_accept_site.php';
	if(!is_file($rjaFile)) {

    $_que ='site='.$s;
    $ACCEPTSITE = array();
    $NCD = getDbArray($table['newsacceptsite'],$_que,'*','uid','desc',0,1);
	while($N=db_fetch_array($NCD)){

	     if($N['osite']) {
            $ACCEPTSITE[] = $N['osite'];
	     }	

	}

    $rjaFile = $RJPath.$r.'_accept_site.php';
    file_put_contents($rjaFile, '<?php $ACCEPTSITE = ' . var_export($ACCEPTSITE, true) . ';');
	@chmod($rjaFile,0707);
	}
	//����Ʈ ACCEPT

	//����Ʈ REJECT
    $rjsFile = $RJPath.$r.'_reject_site.php';
	if(!is_file($rjsFile)) {

    $_que ='site='.$s;
    $REJECTSITE = array();
    $NCD = getDbArray($table['newsrejectsite'],$_que,'*','uid','desc',0,1);
	while($N=db_fetch_array($NCD)){

	     if($N['osite']) {
            $REJECTSITE[] = $N['osite'];
	     }	

	}

    $rjsFile = $RJPath.$r.'_reject_site.php';
    file_put_contents($rjsFile, '<?php $REJECTSITE = ' . var_export($REJECTSITE, true) . ';');
	@chmod($rjsFile,0707);

	}
	//����Ʈ REJECT

	//���� REJECT
    $rjuFile = $RJPath.$r.'_reject_uid.php';
	if(!is_file($rjuFile)) {

    $_que ='site='.$s;
    $REJECTUID = array();
    $NCD = getDbArray($table['newsrejectuid'],$_que,'*','uid','desc',0,1);
	while($N=db_fetch_array($NCD)){

	     if($N['ouid']) {
            $REJECTUID[] = $N['ouid'];
	     }

	}

    $rjuFile = $RJPath.$r.'_reject_uid.php';
    file_put_contents($rjuFile, '<?php $REJECTUID = ' . var_export($REJECTUID, true) . ';');
	@chmod($rjuFile,0707);

	}
	//���� REJECT

//�������� ó��

//$_SESSION['site_language'] = ''; //�ʱ�ȭ
if($d['layout']['lang_eastasia']==1) { //������ ���

	///////////////////////////////////////////////////
//�⺻����
if($d['layout']['lang_default']) { //�⺻��� ������

    if($d['layout']['lang_default']=="lo") { //�⺻��� ������϶�
    
	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=2) { //������ ���ų� ����� �ƴϸ� �⺻ ������� ����
	   $_SESSION['site_language'] = 2;
	   }

    }elseif($d['layout']['lang_default']=="ms"){ //�⺻��� �����̽þ��϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=3) { //������ ���ų� �����̽þ� �ƴϸ� �⺻ �����̽þ����� ����
	   $_SESSION['site_language'] = 3;
	   }

    }elseif($d['layout']['lang_default']=="vi"){ //�⺻��� ��Ʈ���϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=4) { //������ ���ų� ��Ʈ���� �ƴϸ� �⺻ ��Ʈ������ ����
	   $_SESSION['site_language'] = 4;
	   }

    }elseif($d['layout']['lang_default']=="en"){ //�⺻��� �̱��϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=5) { //������ ���ų� �̱��� �ƴϸ� �⺻ �̱����� ����
	   $_SESSION['site_language'] = 5;
	   }

    }elseif($d['layout']['lang_default']=="in"){ //�⺻��� �ε����̻��϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=6) { //������ ���ų� �ε����̻� �ƴϸ� �⺻ �ε��׽þƷ� ����
	   $_SESSION['site_language'] = 6;
	   }

    }elseif($d['layout']['lang_default']=="jp"){ //�⺻��� �Ϻ� �϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=7) { //������ ���ų� �Ϻ��� �ƴϸ� �⺻ �Ϻ����� ����
	   $_SESSION['site_language'] = 7;
	   }

    }elseif($d['layout']['lang_default']=="cn"){ //�⺻��� �߱��϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=8) { //������ ���ų� �߱��� �ƴϸ� �⺻ �߱����� ����
	   $_SESSION['site_language'] = 8;
	   }

    }elseif($d['layout']['lang_default']=="km"){ //�⺻��� į������϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=9) { //������ ���ų� į����� �ƴϸ� �⺻ į����Ʒ� ����
	   $_SESSION['site_language'] = 9;
	   }

    }elseif($d['layout']['lang_default']=="tl"){ //�⺻��� �ʸ����϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=10) { //������ ���ų��ʸ����� �ƴϸ� �⺻ �ʸ������� ����
	   $_SESSION['site_language'] = 10;
	   }

    }elseif($d['layout']['lang_default']=="th"){ //�⺻��� �±��϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=11) { //������ ���ų� �±��� �ƴϸ� �⺻ �±����� ����
	   $_SESSION['site_language'] = 11;
	   }

    }elseif($d['layout']['lang_default']=="ko"){ //�⺻��� �ѱ��϶�

	   if(!isset($_SESSION['site_language']) && isset($_SESSION['site_language'])!=1) { //������ ���ų� �ѱ��� �ƴϸ� �⺻ �ѱ����� ����
	   $_SESSION['site_language'] = 1;
	   }

    }else{ //�⺻��� ������

	   $_SESSION['site_language'] = 1;
    }

}else{

   $_SESSION['site_language'] = 1;

}
//////////////////////////////////////////////////

}else{

//�⺻����
if($d['layout']['lang_default']) { //�⺻��� ������

    if($d['layout']['lang_en']==1 && $d['layout']['lang_default']=="en") { //�⺻��� �����϶�
    
	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=2) { //������ ���ų� ������ �ƴϸ� �⺻ �������� ����
	   $_SESSION['site_language'] = 2;
	   }

    }elseif($d['layout']['lang_jp']==1 && $d['layout']['lang_default']=="jp"){ //�⺻��� �Ϲ��϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=3) { //������ ���ų� �Ϲ��� �ƴϸ� �⺻ �Ϲ����� ����
	   $_SESSION['site_language'] = 3;
	   }

    }elseif($d['layout']['lang_cn']==1 && $d['layout']['lang_default']=="cn"){ //�⺻��� �߹��϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=4) { //������ ���ų� �߹��� �ƴϸ� �⺻ �߹����� ����
	   $_SESSION['site_language'] = 4;
	   }

    }elseif($d['layout']['lang_default']=="ko"){ //�⺻��� �ѱ��϶�

	   if(!$_SESSION['site_language'] && $_SESSION['site_language']!=1) { //������ ���ų� �߹��� �ƴϸ� �⺻ �߹����� ����
	   $_SESSION['site_language'] = 1;
	   }

    }else{ //�⺻��� ������

	   $_SESSION['site_language'] = 1;
    }

}else{

   $_SESSION['site_language'] = 1;

}

}


//echo $_SESSION['site_language'];

$my['admin'] = isset($my['admin']) ? $my['admin'] : ''; 
$my['id'] = isset($my['id']) ? $my['id'] : ''; 
$my['mygroup'] = isset($my['mygroup']) ? $my['mygroup'] : ''; 

// '���󿧼�Ÿ����', 395, 's100720', '' �߰� ���


if($my['admin']) {
$_HX = getDbData($table['s_site'],"uid=".$s,'*'); //�����̸�

}else{

$_HX = getDbData($table['s_site'],"uid=".$s." and mbrid='".$my['id']."'",'*');	

}



$_HK = getDbData($table['s_site'],"uid=".$s,'*'); //����Ʈ ���� Ȯ�ο�

//����
$_HR = getDbData($table['newsreporter'],"site=".$s." and auth=2 and id='".$my['id']."'",'*');

//�׷�
$_HG = getUidData($grouptable,$my['mygroup']);	


//�̸��������
if(isset($mobilepreview)=="Y"){
$r = $r;
}else{
$r = $r;
}

$_NEWSC = getDbData($table['newsconfig'],"site='".$_HS['uid']."'",'*');

$syndi_key = $_NEWSC['syndi_key'];
$kakao_key = $_NEWSC['kakao_key'];


//echo $g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.header.var.php';

$_SITE = getDbData($table['s_site'],"id='".$r."'",'*');


//��Ʃ��
if(is_file($g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.youtube.var.php')) include_once $g['path_var'].'site/'.$r.'/'.$r."_".$playout.'.youtube.var.php';

//��Ʃ�� ��������
$youtube_free_file = $g['path_var'].'site/'.$r.'/'.$r.'_youtube_free.php';
if(!is_file($youtube_free_file)) {
$fp = fopen($youtube_free_file,'w');
fwrite($fp, 1);
fclose($fp);
@chmod($youtube_free_file,0707);
}

$youtube_modified = date ("Y-m-d H:i:s", filemtime($youtube_free_file));
$youtube_nowday = date("Y-m-d H:i:s");

$youtube_gapMinute = (int)((strtotime($youtube_nowday) - strtotime($youtube_modified)) / 60);

//if($youtube_gapMinute > 5) { //5�и��� üũ

    //���� ��� ���� ����
   //�⺻�� �ſ� 30��
   //�����̾��� �ſ� 70�� 
   //�ɹ����� �ſ� 200��
   //������ �ſ� 600��
   $youtube_month = date("Ym");
   if($my['uid']) {
   $YOUTUBE_PAYMENT = getDbData($table['newsyoutubefreecredit'],"site='".$s."' and mbruid='".$my['uid']."' and paydate='".$youtube_month."'",'*');
   if(!$YOUTUBE_PAYMENT['uid']) {

       $GOODS = getDbData($table['newsmember'],"sitecode='".$s."'",'*');
       $RPT = getDbData($table['newsreporter'],"mbruid='".$my['uid']."'",'*'); //����

	   if($GOODS['mbruid']==$my['uid']) { //���

	   if($GOODS['parent']==2) { //�⺻��
       $credit = 30;
	   }elseif($GOODS['parent']==3) { //�����̾���
       $credit = 70;
	   }elseif($GOODS['parent']==4) { //�ɹ�����
       $credit = 200;
	   }elseif($GOODS['parent']==5) { //������
       $credit = 600;
	   }elseif($GOODS['parent']==6) { //â���� (�����̾����� ����)
       $credit = 70;
	   }elseif($GOODS['parent']==7) { //�⺻�� 10��
       $credit = 30;
	   }elseif($GOODS['parent']==8) { //�����̾��� 10��
       $credit = 70;
	   }elseif($GOODS['parent']==9) { //�ɹ��� 10��
       $credit = 200;
	   }elseif($GOODS['parent']==10) { //������ 10��
       $credit = 600;
	   }
	   
	   }elseif($RPT['mbruid']){ //�����̸�

       $credit = 5;

	   }else{ //�ƹ��͵� �ƴϸ�
       $credit = 0;
	   }

	   $use_credit = 0;

       $mingid         = getDbCnt($table['newsyoutubefreecredit'],'min(gid)','');
       $gid            = $mingid ? $mingid-1 : 100000000;
	   $auth = 1;
       $d_regis = date("YmdHis");
	   $mbruid = $my['uid'];
	   $id           = $my['id'];

       $QKEY = "site,gid,auth,mbruid,id,paydate,use_credit,credit,d_regis";
       $QVAL = "'$s','$gid','$auth','$mbruid','$id','$youtube_month','$use_credit','$credit','$d_regis'";
	   getDbInsert($table['newsyoutubefreecredit'],$QKEY,$QVAL);

  
       /*
       $YOUTUBE_CREDIT = getDbData($table['newsyoutubecredit'],"site='".$s."'",'*');
       
       $total_credit = $YOUTUBE_CREDIT['credit']+$credit;
	   $QVAL = "credit='$total_credit'";
	   getDbUpdate($table['newsyoutubecredit'],$QVAL,'site='.$s); 
	   */

       unlink($youtube_free_file);

   }

   } //my['uid']


//}
//��Ʃ�� ��������

		
?>
<?php 
if(isset($menuact[0])) $_S1 = getUidData($menutable,$menuact[0]);
if(isset($menuact[1])) $_S2 = getUidData($menutable,$menuact[1]);
if(isset($menuact[2])) $_S3 = getUidData($menutable,$menuact[2]);
?>	
