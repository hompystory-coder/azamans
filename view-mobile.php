<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard-dynamic-subset.css" />
<link href="https://cdn.jsdelivr.net/gh/sunn-us/SUIT/fonts/variable/woff2/SUIT-Variable.css" rel="stylesheet">
<style>
<?php // Dynamic CSS patch codes. ?>
<?php if( $g['mobile'] ): // 모바일 기본 폰트크기 적용?>
.news-view {font-size:14px;}
.news-view .viewbox .subject h3 {font-size:17px;}
.news-input {font-size:14px;}
<?php else:?>
<?php if( $_COOKIE['myFontFamily'] ):	// 사용자 폰트패밀리 적용?>
.news-view .viewbox .content-box { font-family:<?php echo stripslashes($_COOKIE['myFontFamily'])?>; }
<?php endif?>
<?php endif?>
<?php if( $_COOKIE['myFontSize'] ): 	// 사용자 폰트크기 적용?>
.news-view .viewbox .content-box { font-size:<?php echo $_COOKIE['myFontSize']?>; }
<?php endif?>

#upload-file-list {width:100%;overflow:hidden;margin-top:10px;margin-bottom:15px;}
#upload-file-list ul {margin:0;padding:0;}
#upload-file-list ul li{overflow:hidden;width:100%;padding:0px 0 0px 0;margin-bottom:5px;}

#upload-file-list ul li.list-group-item {border:none;border-radius:none;}
#upload-file-list ul li .listbox{padding:0;height:35px;background:#efefef;border:#ccc 1px solid;}
#upload-file-list ul li .listbox .name{float:left;padding-left:8px;padding-top:0px;font-size:12px;}
#upload-file-list ul li .listbox .name a {position:relative;top:0px;font-size:12px;color:#666;text-decoration:none;}
#upload-file-list ul li .listbox .name a:hover{text-decoration:underline;}
#upload-file-list ul li .listbox .name i {position:relative;top:0px;font-size:12px;color:#666;}

#attach-upload-list {display:block;}
#attach-upload-list ul {margin:0;padding:0;}
#attach-upload-list ul li{overflow:hidden;width:100%;padding:5px 0 5px 0;margin-bottom:2px;}

#attach-upload-list ul li .listbox{padding:0px 5px 5px 5px;}
#attach-upload-list ul li .listbox .name{float:left;padding-left:5px;padding-top:0px;font-size:12px;}
#attach-upload-list ul li .listbox .txt{float:right;position:relative;top:0px;font-size:12px;}
#attach-upload-list ul li .listbox .txt .pd10{margin-right:5px;}
</style>

<style type="text/css">
         .news-view .w_article .article_cont_area .sns-box2{width:100%;overflow:hidden;text-align:right;height:45px;line-height:35px;margin-bottom:15px;}
         .news-view .w_article .article_cont_area .sns-box2 .sns{width:100%;display:table;text-align:center;}
         .news-view .w_article .article_cont_area .sns-box2 .sns-button{border-radius:3px;display:inline-block;cursor:pointer;margin-right:5px;background-size: cover;}
         .news-view .w_article .article_cont_area .sns-box2 .sns-button img{overflow:hidden;}
         .news-view .w_article .article_cont_area .sns-box2 .sns-button:last-child{margin-right:0px;}
         .news-view .w_article .article_cont_area .sns-box2 .sns-pos-left{float:left;}
         .news-view .w_article .article_cont_area .sns-box2 .sns-pos-right{float:right;}
         .news-view .w_article .article_cont_area .sns-box2 .sns-pos-center{display:table;margin:0 auto;}

		 /*작성자 정보*/
         .news-view .w_article .w_article_title{border-bottom:solid 5px #3a3a3a;}
         .news-view .w_article .line{width:100%;overflow:hidden;height:5px;border-bottom:#dadada 1px solid;}
         .news-view .w_article .main_text{line-height:33px; margin-bottom:15px;padding-bottom:40px;border-bottom:#efefef 1px solid; word-wrap: break-word;}
         .news-view .w_article .article_cont_area .write-box {width:100%;overflow:hidden;border-bottom:#efefef 1px solid;padding-bottom:15px;}
		 .news-view .w_article .article_cont_area .write-box .left{float:left;}
		 .news-view .w_article .article_cont_area .write-box .left .name{font-weight:bold; font-size:14px; color:#0071bc; padding-right:15px;}
         .news-view .w_article .article_cont_area .write-box .left .email{font-weight:bold; font-size:14px; color:#0071bc; padding-right:25px;}
		 .news-view .w_article .article_cont_area .write-box .right{float:right;}
         .news-view .w_article .article_cont_area .write-box .right .date{font-size:14px; color:#969696;}
         .news-view .w_article .article_cont_area .write-box .right .date span{display:inline-block; margin:0 15px 0 7px;}
         .news-view .w_article .article_cont_area .write-box .right .date .share{float:right;cursor:pointer;position:relative;top:2px;}
		 /*작성자 정보*/

         /* 기사하단 배너배너*/
        .ad-banner-bottom {width:100%;overflow:hidden;}
        .ad-banner-bottom .wrap_ad {position:relative;float:right;}
        .ad-banner-bottom .wrap_ad .list_ad {overflow:hidden;width:100%;}
        .ad-banner-bottom .wrap_ad .list_ad a {display:block;}
        .ad-banner-bottom .wrap_ad .btn_page {position:absolute;top:12px;right:3px;overflow:hidden;float:left;width:30px;height:15px;}
        .ad-banner-bottom .wrap_ad .btn_page a,
        .ad-banner-bottom .wrap_ad .btn_page a {overflow:hidden;float:left;width:30px;height:15px;font-size:0;line-height:0;text-indent:-9999px}
        .ad-banner-bottom .wrap_ad .btn_page a.prev {overflow:hidden;float:left;width:14px;height:15px;font-size:0;line-height:0;background:url('<?php echo $g['url_root']?>/layouts/skinx-skin/_images/btn_ad_arrow_prev.png') no-repeat 0 0;}
        .ad-banner-bottom .wrap_ad .btn_page a.next {overflow:hidden;float:left;width:15px;height:15px;font-size:0;line-height:0;background:url('<?php echo $g['url_root']?>/layouts/skinx-skin/_images/btn_ad_arrow_next.png') no-repeat 0 0;}

           /*왼쪽 배너 텍스트*/
          .ad-banner-bottom-txt {width:100%;height:20px;overflow:hidden;}
          .ad-banner-bottom-txt a{font-weight:bold;font-size:14px;font-family:'맑은 고딕','Malgun Gothic',나눔고딕,돋움,Dotum,굴림,Gulim,'Apple SD Gothic Neo',sans-serif;color:#333;}

          /*왼쪽 배너  스크립트*/
        .ad-banner-bottom-script {width:100%;margin-bottom:20px;overflow:hidden;}

/*기본형 list*/
.mobile-box {width:100%;overflow:hidden;}
.mobile-box .default_list {width:100%;height:127px;padding: 0px;overflow:hidden;margin-bottom:15px;border-bottom:#dfdfdf 1px solid;padding-bottom:0px;}
.mobile-box .default_list .st {width: 100%;height:20px;margin-bottom: 17px; display: block; text-overflow:ellipsis;overflow:hidden;white-space:nowrap;}
.mobile-box .default_list .st a { letter-spacing: -0.05em; font-size: 16px; line-height: 16px; text-decoration: none;color: rgb(31, 33, 35); letter-spacing: -0.6px;font-weight:bold;font-family:'맑은 고딕','Malgun Gothic',나눔고딕,돋움,Dotum,굴림,Gulim,'Apple SD Gothic Neo',sans-serif;} 
.mobile-box .default_list .st a:hover {text-decoration: underline;} 
.mobile-box .default_list .desc {width: 100%;text-overflow:ellipsis;overflow:hidden;white-space:normal;}
.mobile-box .default_list .desc a {color: rgb(119, 119, 119); text-decoration: none;letter-spacing: -0.05em;font-size: 13px; line-height: 22px;color: rgb(119, 119, 119); text-decoration: none;} 
.mobile-box .default_list .desc a:hover {text-decoration: underline;} 

/* 기본형 thumb*/
.mobile-box .default_thumb {width:100%;height:155px;padding: 0px;overflow:hidden;margin-bottom:15px;}

.mobile-box .default_thumb .st {width:100%;height:20px;margin-bottom:5px;text-overflow:ellipsis;overflow:hidden;white-space:nowrap;}
.mobile-box .default_thumb .st a {font-size: 16px; line-height: 16px;color: rgb(31, 33, 35); text-decoration: none;font-weight:bold;font-family:'맑은 고딕','Malgun Gothic',나눔고딕,돋움,Dotum,굴림,Gulim,'Apple SD Gothic Neo',sans-serif;} 
.mobile-box .default_thumb .st a:hover {text-decoration: underline;} 

.mobile-box .default_thumb .thumb-box {position:relative;margin: 0px 0px 0px 0px; padding: 5px; overflow: hidden; border: 1px solid rgb(223, 223, 223); width: 130px; height: 112px; box-sizing: border-box; float: left;}
.mobile-box .default_thumb .thumb-box .thumb {width: 118px; height: 100px; display: block; overflow: hidden; background-position: center center; background-size: cover; background-repeat: no-repeat; background-attachment: initial; background-origin: initial; background-clip: initial;}
.mobile-box .default_thumb .thumb-box .thumb a {margin-bottom: 7px; display: block; overflow: hidden; height: 24px; font-size: 17px; line-height: 24px;color: #000;} 
.mobile-box .default_thumb .thumb-box .thumb a:hover {text-decoration: underline;} 
.mobile-box .default_thumb .thumb-box .icon-photo {position:absolute;top:7px;left:10px;filter:alpha(opacity=60);opacity:.6;} 
.mobile-box .default_thumb .thumb-box .icon-movie {position:absolute;top:7px;left:10px;} 

.mobile-box .default_thumb .desc {float:right;width:200px;height:112px;}
.mobile-box .default_thumb .desc .cont {width:100%;height:112px;text-overflow:ellipsis;overflow:hidden;white-space:normal;}
.mobile-box .default_thumb .desc .cont a {text-decoration: none;letter-spacing: -0.05em;font-size: 13px; line-height: 24px;color: rgb(119, 119, 119);} 
.mobile-box .default_thumb .desc .cont a:hover {text-decoration: underline;} 

/*쿠팡상품검색*/
.coupang-box {width:100%;overflow:hidden;margin-top:15px;}
.coupang-box .default_coupang {width: 47.5%; border-bottom-color: rgb(240, 240, 240); border-bottom-width: 2px; border-bottom-style: solid; float: left; position: relative;}
.coupang-box .default_coupang .shop {border-width: 1px; border-style: solid; border-color: rgb(213, 212, 208) rgb(213, 212, 208) rgb(192, 191, 188); width: 99%; height: 310px; display: block; background-color: rgb(255, 255, 255);}
.coupang-box .default_coupang span.shop {border-width: 1px; border-style: solid; border-color: rgb(213, 212, 208) rgb(213, 212, 208) rgb(192, 191, 188); padding: 0; width: 99%; display: block; background-color: rgb(255, 255, 255); -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-right-colors: none; -moz-border-top-colors: none; border-image: none;}
.coupang-box .default_coupang span.shop:hover {border:2px solid #ef2011;}

.coupang-box .default_coupang .shop .photo-box {left: 7px; top: 7px; width: 95%; height: 203px; display: block; position: absolute; overflow: hidden; border: 0px solid rgb(223, 223, 223);  box-sizing: border-box;}
.coupang-box .default_coupang .shop .photo-box .photo {width:95%;height:243px;border: 0px solid rgb(223, 223, 223); color: rgb(51, 51, 51); letter-spacing: -0.6px; background-color: rgb(255, 255, 255);text-align:center;background-position: center center; background-size: cover; background-repeat: no-repeat; background-attachment: initial; background-origin: initial; background-clip: initial;}

.coupang-box .default_coupang .shop .shop_desc {left: 7px; top: 215px; width: 95%; overflow: hidden; display: block; position: absolute;}
.coupang-box .default_coupang .shop .shop_desc .shop_tit {width: 100%;height:24px; line-height: 24px;letter-spacing: -1px; overflow: hidden; display: block; white-space: nowrap; -ms-text-overflow: ellipsis;}
.coupang-box .default_coupang .shop .shop_desc .shop_tit a{color: rgb(51, 51, 51); line-height: 24px;letter-spacing: -1px; overflow: hidden; font-size: 13px; font-weight: bold;font-family:'맑은 고딕','Malgun Gothic',나눔고딕,돋움,Dotum,굴림,Gulim,'Apple SD Gothic Neo',sans-serif; display: block; white-space: nowrap; -ms-text-overflow: ellipsis;}
.coupang-box .default_coupang .shop .shop_desc .shop_tit:hover {text-decoration:underline;}

.coupang-box .default_coupang .shop .shop_opt {left: 7px; top: 242px; width: 93%; overflow: hidden; display: block; position: absolute;}
.coupang-box .default_coupang .shop .shop_opt .shop_price {float:left;display:block;overflow:hidden;height:18px;width:50%;font-size:13px;font-family:Tahoma;font-weight:bold;line-height:16px;color:#ef2011;white-space:nowrap;letter-spacing:-1px;text-overflow:ellipsis;white-space:nowrap;word-wrap:normal;}
.coupang-box .default_coupang .shop .shop_opt .shop_rocket {float:right;width:49%;text-align:right;font-size:13px;font-family:Tahoma;font-weight:bold;line-height:16px;color:#333;white-space:nowrap;letter-spacing:-1px;text-overflow:ellipsis;white-space:nowrap;word-wrap:normal;}


.coupang-box .default_coupang .shop .shop_btn {left: 7px; top: 270px; width: 96%; overflow: hidden; display: block; position: absolute;text-align:center;}

.coupang-box .default_coupang .shop .shop_desc .standardinfo {display:block;overflow:hidden;height:13px;width:100%;font-size:13px;line-height:13px;color:#999;white-space:nowrap;letter-spacing:-1px;text-overflow:ellipsis;white-space:nowrap;word-wrap:normal;}

.coupang-box .guide {width:100%;overflow:hidden;text-align:center;font-size:12px;}

/*유튜브*/
.sec:not(.none) { position:relative; padding:20px; background:#fff; border:1px solid #ddd; margin-top:10px; }
.sec[class*=" sec-2news"],
.sec[class*=" sec-3news"] { display:flex; justify-content: space-between; height:600px; }
.sec[class*=" sec-3news"] > div { width:calc(33.33% - 15px); }

.sec[class*=" sec-create-news"] { display:flex; justify-content: space-between; height:680px; }
.sec[class*=" sec-create-news"] > div { width:calc(33.33% - 15px); }

.sec .title { position:relative; font-weight:600; /*padding-left:10px; */}
.sec .title:before { content:""; position:absolute; left:0; top:4px; bottom:4px; width:4px; border-radius:4px; /*background:var(--main-color2);*/ }
.sec .title:not(.min) { font-size:1.375rem; line-height:1.2; margin-bottom:10px; /*padding-left:12px;*/ }

.sec .title a { position:absolute; right:0; bottom:0; font-size:1rem; color:#aaa; padding-right:10px; }
.sec .title a:before, .sec .title a:after { content:""; position:absolute; right:2px; width:1px; height:6px; background:#aaa;}
.sec .title a:before { transform:rotate(-45deg); top:1px; }
.sec .title a:after { transform:rotate(45deg); bottom:1px; }

/*추가*/
.sec2:not(.none) { position:relative; padding-left:20px; padding-right:20px;}

.title-bar {position:absolute;left:0px;width:4px;border-radius:5px;margin-right:12px;background:var(--main-color2);}
.title-text {padding-left:10px;}
.title-bar-19 {height:19px;}
.title-bar-16 {height:16px;}
.title-bar-14 {height:14px;}

.titlt-round {position:absolute;left:0px;}
.title-round-13{left:0; top:3px; width:13px; height:13px; background:#fff; border:3px solid var(--main-color2); border-radius:99px; }

/* 문의하기 */
#question-write .question-write {padding: 20px 10px 10px; border:#dfdfdf solid 1px; background:rgb(250, 250, 250); margin-bottom:10px;font-size:12px;}
#question-write .question-write.inner {padding:0; border:0; padding-top:10px; background:rgb(250, 250, 250);}
#question-write .question-write .top {font-weight:bold;font-size:12px;}
#question-write .question-write .top span {font-weight:normal; font-family:dotum;font-size:12px; color:#888; margin-left:5px;}
#question-write .question-write .denied {font-weight:bold; padding-bottom:10px; color:#888; }
#question-write .question-write .middle {width:100%; display:table;font-size:12px;margin: 5px 0 10px;}
#question-write .question-write .middle .left {float:left;display:table-cell;}
#question-write .question-write .middle .right {float:right;display:table-cell;}
#question-write .question-write .middle .nomember {margin-top:10px; margin-bottom:10px;}
#question-write .question-write .bottom {width:100%; display:table;font-size:12px;}
#question-write .question-write .bottom .left {float:left;display:table-cell;}
#question-write .question-write .bottom .left label {float:left;font-weight:normal; color:#666666;}
#question-write .question-write .bottom .left label input[type="checkbox"] {position:relative; top:2px;}
#question-write .question-write .bottom .right {float:right;display:table-cell;}

.news-url-copy {display:inline-block;border:#dfdfdf 1px solid;width:40px;height:40px;position:relative;top:0px;margin-right:5px;padding-top:0px;padding-top:0px;cursor:pointer;text-align:center;vertical-align:middle;}
.news-url-copy i{position:relative;top:2px;}
</style>

<?php
include_once $g['path_var'].'site/'.$r.'/'.$r.'_img_server_var.php'; //원본 파일;
define('NEWS_IMG_SERVER', $d['news']['img_server_url']);

?>

<script src="https://developers.kakao.com/sdk/js/kakao.min.js"></script>

<div class="news-view">

   <!--viewbox-->
   <div class="w_article">

	     <!--배너 기사 상단 -->
         <?php
         $_banner_sql_36 = 'site='.$s.' and position=36 and auth=2 and device=2';
	     ?>

         <?php  $BNUM36  = getDbRows($table['newsbanner'],$_banner_sql_36);?>

		 <?php if($BNUM36):?>

		 <?php $NCD  = getDbSelect($table['newsbanner'],$_banner_sql_36.' order by rand() limit 1','*');?>
         <?php while($N=db_fetch_array($NCD)):?>

         <?php if($N['kind']==1): //이미지형이면?>

	     <div style="width:100%;overflow:hidden;margin-bottom:10px;text-align:center;">
		    <a href="<?php echo $N['url'] ? $N['url'] : '#.'?>" <?php if($N['target']=="_blank"):?>target="_blank"<?php endif?>><img src="<?php echo NEWS_IMG_SERVER?>/banner/<?php echo $N['upload']?>" alt="<?php echo $N['name']?>" width="100%"></a>
		 </div>

		 <?php elseif($N['kind']==2): //텍스트형?>

		 <div class="ad-banner-bottom-txt"><a href="<?php echo $N['url'] ? $N['url'] : '#.'?>" <?php if($N['target']=="_blank"):?>target="_blank"<?php endif?> class="news-banner-click" id="<?php echo $N['uid']?>"><?php echo $N['adtext']?></a></div>

		 <?php elseif($N['kind']==3): //스크립트형?>
         <div class="ad-banner-bottom-script" style="width:100%;margin-bottom:10px;text-align:center;"><?php echo $N['adscript']?></div>
	     <?php endif?>

		 <?php endwhile?>

	     <?php endif?>
	     <!--배너 기사 상단 -->
        
		 <!------------------------------------------------------------------------------------------>
		 <!-- 기사 제목 -->
		 <div class="w_article_title">
			
		      <?php 	$_A = getDbData('rb_news_ad',"gid='".$R['gid']."' and site='".$R['site']."'",'*');?>
			  <!-- 제목 -->
			  <h3 id="vmNewsTitle"><?php echo $R['subject']?> <?php if($_A['ad']):?><img src="https://www.ehom.kr/ad.png"><?php endif?></h3>
			  <!-- 소제목 / 없으면 비노출 -->

			  <?php if($R['subtitle1']):?><h4 id="subNewsTitle1"><strong><?php echo$R['subtitle1']?></strong></h4><?php endif?>
			  <?php if($R['subtitle2']):?><h4 id="subNewsTitle2"><strong><?php echo$R['subtitle2']?></strong></h4><?php endif?>
			  <?php if($R['subtitle3']):?><h4 id="subNewsTitle3"><strong><?php echo$R['subtitle3']?></strong></h4><?php endif?>
			
		 </div>
		 <div class="line"></div>
		 <!--// 기사 제목 -->

         <!------------------------------------------------------------------------------------------>
	     <!--기사내용-->
         <div class="w_article_cont">

 	        <!--기사본문-->
            <div class="article_cont_area">

 	           <!--기사본문내용-->
               <div class='main_text' itemprop="articleBody" id="vContent">
			   
			   <?php if($r=="s151546" || $r=="s111122"):?>
			   <div style="width:100%;overflow:hidden;padding:10px 0px 10px 0px;text-align:center;margin-bottom:15px;color:red;">이 포스팅은 쿠팡 파트너스 활동의 일환으로, 이에 따른 일정액의 수수료를 제공받습니다.</div>
			   <?php endif?>			   

	           <!--기사 배너 1-->
               <?php
               $_banner_sql_34 = 'site='.$R['site'].' and position=34 and auth=2 and device=2';
	           ?>

               <?php  $BNUM34  = getDbRows($table['newsbanner'],$_banner_sql_34);?>

		       <?php if($BNUM34):?>

			   <?php $NCD  = getDbSelect($table['newsbanner'],$_banner_sql_34.' order by rand() limit 1','*');?>
               <?php while($N=db_fetch_array($NCD)):?>

               <?php if($N['kind']==1): //이미지형이면?>

	           <div style="width:100%;overflow:hidden;margin-bottom:10px;text-align:center;">
				        <a href="<?php echo $N['url'] ? $N['url'] : '#.'?>" <?php if($N['target']=="_blank"):?>target="_blank"<?php endif?>><img src="<?php echo NEWS_IMG_SERVER?>/banner/<?php echo $N['upload']?>" alt="<?php echo $N['name']?>" width="100%"></a>
			   </div>

			   <?php elseif($N['kind']==2): //텍스트형?>

			   <div class="ad-banner-bottom-txt"><a href="<?php echo $N['url'] ? $N['url'] : '#.'?>" <?php if($N['target']=="_blank"):?>target="_blank"<?php endif?> class="news-banner-click" id="<?php echo $N['uid']?>"><?php echo $N['adtext']?></a></div>

			   <?php elseif($N['kind']==3): //스크립트형?>
               <div class="ad-banner-bottom-script" style="width:100%;margin-bottom:10px;text-align:center;"><?php echo $N['adscript']?></div>
	           <?php endif?>

			   <?php endwhile?>

	          <?php endif?>
	          <!--기사 배너 1 -->
			  
	           <?php 
			   $OS = getUidData($table['newsindex'],$R['uid']);
			   $OS1 = getDbData($table['newsvsite'],"sitecode=".$R['site'],'*');	
			   ?>
	           <?php $DM = getDbData('rb_news_url',"site=".$R['site'],'*');?>
                   
		       <?php
		       if($DM['https']) {
               $http = "https://www.";
		       }else{
               $http = "http://www.";
		       }	
		
		       $OD = getDbData($table['newssite'],"sitecode=".$R['site'],'*');	
		
		       $L = getDbData($table['newslike'],"news_site='".$R['site']."' and news_uid='".$R['uid']."'",'*');
	           ?>
			   
	           <?php
	           $OD = getDbData($table['newssite'],"sitecode=".$R['site'],'*');
	  
	           if($s==$R['site']) {
	           $osite = "http://www.".$OS1['domain']."/news/".$R['uid'];
	           $osite = str_replace("www.www.","www.",$osite);		  
	           }else{
			   $osite = "http://www.".$OS1['domain']."/news/".$R['post_id'] ? $R['post_id'] : $R['uid'] ;
	           $osite = str_replace("www.www.","www.",$osite);
	           }
	           ?>
			   
			  <?php 
			  //그룹확인
			  $_MGN = getDbData($table['s_mbrdata'],"site='".$R['site']."' and id='".$R['id']."'",'*');
			  
			  //rb_s073906_mbrgroup  '디지털배움뉴스', 365, 's073906',
	          $_CG =  getUidData("rb_".$r."_mbrgroup",$_MGN['mygroup']);			  
			  //그룹확인
			  ?>		   
	  
	           <!--문의하기-->
			   <?php if($r!="s083107" && $r!="s105543"): //민중.kr, 영남시사투데이(http://www.ystoday.co.kr)?>			   
               <div class="alert alert-warning" role="alert" style="margin-top:0;">
               <?php if($R['site'] != $s):?>기사 제공처 : <a href="http://<?php echo $OS1['domain'].'/news/'.($R['post_id'] ? $R['post_id'] : $R['uid']) ?>" target="_blank"><?php echo $OS['site_name']?></a> /<?php endif?> 등록기자: <?php echo $R['name'] ?>
			   
			   <?php if($R['site']==$s && $R['site']=="365"):?> <?php echo $_CG['name']?>
			   <?php elseif($R['site']==$s && $R['site']=="366"):?> <?php echo $_CG['name']?>
			   <?php elseif($R['site']==$s && $R['site']==383):?> <?php echo $_CG['name']?>
			   <?php elseif($R['site']==$s && $R['site']==396):?> <?php echo $_CG['name']?>
			   <?php elseif($R['site']==$s && $R['site']==416):?> <?php echo $_CG['name']?>
			   <?php elseif($R['site']==$s && $R['site']==400):?> <?php echo $_CG['name']?>
			   <?php elseif($R['site']==$s && $R['site']==447):?> <?php echo $_CG['name']?>			   
			   <?php elseif($R['site']==$s && $R['site']==432):?> <?php echo $_CG['name']?>
			   <?php elseif($R['site']==$s && $R['site']==435):?> <?php echo $_CG['name']?>
			   <?php elseif($R['site']==$s && $R['site']==463):?> <?php echo $_CG['name']?>			   
			   <?php elseif($R['site']==$s && $R['site']==464):?> <?php echo $_CG['name']?>			   
			   <?php else:?> 기자<?php endif?> 
			   
			   [<span class="question-regis" style="cursor:pointer;" data-toggle="collapse" data-target="#news-question-form">기자에게 문의하기</span>] <i class="fa fa-thumbs-up good-click" style="cursor:pointer;" onclick="gbWrite('good', '<?php echo $R['uid']?>');"></i>&nbsp;<span class="news-good<?php if(!$L['good']):?> hide<?php endif?>" id="news-good-<?php echo $R['uid']?>"><?php echo number_format($L['good'])?></span> / <i class="fa fa-thumbs-down bad-click" style="cursor:pointer;" onclick="gbWrite('bad', '<?php echo $R['uid']?>');"></i>&nbsp;<span class="news-bad<?php if(!$L['bad']):?> hide<?php endif?>" id="news-bad-<?php echo $R['uid']?>"><?php echo number_format($L['bad'])?></span>

	           <!--form-->	  
	           <div class="collapse" id="news-question-form" style="margin-top:15px;">
	  
	              <div id="question-write">

                     <div class="question-write">

	                 <form name="questionForm" method="post" action="<?php echo $g['s']?>/" target="_action_frame_<?php echo $m?>" onsubmit="return questionWrite(this);">
	                 <input type="hidden" name="r" value="" class="<?php echo $r?>" />
	                 <input type="hidden" name="m" value="layoutconfig" />
	                 <input type="hidden" name="news_uid" value="<?php echo $R['uid']?>" />
	                 <input type="hidden" name="mbruid" value="<?php echo $R['mbruid']?>" />
	                 <input type="hidden" name="ipaddress" value="<?php echo $_SERVER['REMOTE_ADDR']?>" id="ip-address" />		 
	                 <input type="hidden" name="a" value="question_regis" />	
	
	                 <div class="top">
		             해당 기사에 관련하여 문의하기에 남겨주시면 "<?php echo $R['name']?>"기자에게 전송됩니다
	                 </div>
	
	                 <div class="middle">
		    
		                <div class="left">
		                   <div style="float:left;height:34px;width:100px;background:#eaeaea;border:#cacaca 1px solid;padding-left:10px;padding-top:5px;padding-right:10px;"><i class="fa fa-user fa-2x" aria-hidden="true"></i><span class="hidden-xs" style="position:relative;top:-4px;">&nbsp;이름 </span></div>			   
		                   <input type="text" name="name" value="<?php echo $Q['name']?>" class="form-control bskr-input" placeholder="이름" style="float:left;width:150px;border-radius:0;position:relative;left:-1px;">
		                </div>
		
		                <!--연락처 추가-->
		                <div class="right"style="padding-bottom:5px;">		
		                   <div style="float:left;height:34px;background:#eaeaea;border:#cacaca 1px solid;padding-left:10px;padding-top:5px;padding-right:10px;"><i class="fa fa-mobile fa-2x" aria-hidden="true"></i><span class="hidden-xs" style="position:relative;top:-4px;">&nbsp;연락처 </span></div>
		                   <input type="text" name="tel_1" value=""  class="form-control bskr-input numOnly" placeholder="전화번호" maxlength="4" style="float:left;width:60px;border-radius:0px;position:relative;left:-1px;"><span class="rb-divider" style="float:left;">-</span>
		                   <input type="text" name="tel_2" value=""  class="form-control bskr-input numOnly" placeholder="전화번호" maxlength="4" style="float:left;width:60px;border-radius:0px;"><span class="rb-divider" style="float:left;">-</span>
		                   <input type="text" name="tel_3" value=""  class="form-control bskr-input numOnly" placeholder="전화번호" maxlength="4" style="float:left;width:60px;border-radius:0px;">
                        </div>		   
		                <!--연락처 추가-->
		                <input type="text" name="subject" value="" class="form-control bskr-input" placeholder="제목" style="border-radius:0px;margin-bottom:5px;">
		   
		                <textarea name="content" class="form-control bskr-input"  id="cmt-write-content" style="border-radius:0px;height:120px;"></textarea>
	                 </div>

	                 <div class="bottom" style="margin-bottom:10px;">
		                <div class="left">

		                   <div style="float:left;height:34px;background:#eaeaea;border:#cacaca 1px solid;padding-left:10px;padding-top:5px;padding-right:10px;"><i class="fa fa-envelope fa-2x" aria-hidden="true"></i><span class="hidden-xs" style="position:relative;top:-4px;">&nbsp;이메일 </span></div>
			               <input type="text" name="email" class="form-control bskr-input" placeholder="이메일" style="float:left;width:200px;border-radius:0;position:relative;left:-1px;">

		                </div>
		                <div class="right">
			              <img src="" id="captcha-img" style="float:left;"><button type="button" class="btn btn-outline-secondary" style="float:left;height:34px;border:#cacaca 1px solid;"><i class="fa fa-undo refresh-new-code" onclick="refreshNewCode();"></i></button><input type="text" name="captcha_text" value="" class="form-control bskr-input" placeholder="보안코드" style="float:left;width:80px;position:relative;left:-1px;border-radius:0px;">
		                </div>
			
	                 </div>
					 
	                 <div class="bottom" style="text-align:center;border-top:#dfdfdf 1px solid;padding-top:15px;">
			         <button type="submit" class="btn btn-info btn-sm"><i class="glyphicon glyphicon-ok"></i>&nbsp;확인</button>			
	                 </div>						 
					 
	                 </form>

                     </div>
                     <!--cwrite-->

                  </div>	
	  
	           </div>
	           <!--form-->
	  
	  
               </div>
			   <?php endif?>			   
               <!--문의하기-->	 							  
               
               <?php
              //$news_content = news_content_data ('../../../../../', $R['uid'], $R['account'], $R['d_regis']);
			   //echo getNewsContents($R['account'], $news_content, $R['html']);

			  $_C = getUidData($contenttable,$R['uid']);
			   //echo getNewsContents($R['account'], $_C['content'], $R['html']);
			   
			   $news_content =  getNewsContents($R['account'], $_C['content'], $R['html']);
			   
	          //날짜확인 240724
	          $d_this_year = date("Y");
	
	          $d_create = $R['d_regis'];
	
	          $d_create_year = substr($d_create,0,4); //2024
	
	          if($d_create_year < $d_this_year) { //2024 이전
              $d_img_domain = "https://www.ehom.co.kr";
			  $o_img_domain = "https://www.ehom.kr";
	          }else{
              $d_img_domain = "https://www.ehom.kr";
			  $o_img_domain = "https://www.ehom.co.kr";			  
	          }
	          //날짜확인 240724
			  
			  $news_content = str_replace($o_img_domain,$d_img_domain,$news_content);	  
			  
			  echo $news_content;			   

               $attach_count = getDbRows($table['newsattach'],"parent='".$R['uid']."'");
			   ?>

			   <!--첨부파일-->
			   <?php if($attach_count):?>
			   <div style="width:100%;overflow:hidden;">

	           <div id="attach-upload-list" style="margin-top:10px;">
	              <ul class="list-group">
		 	            
                   <?php $ACD = getDbArray($table['newsattach'],'site='.$R['site'].' and parent='.$R['uid'],'*','gid','asc',0,$p);?>
				   <?php while($A=db_fetch_array($ACD)):?>
				   <li id="<?php echo $A['uid']?>" class="list-group-item">

				   <div class="listbox">
				      <span class="name pull-left"><i class="fa fa-file-o" aria-hidden="true"></i> <?php echo $A['name']?> (<?php echo getSizeFormat($A['size'],1)?>) </span>
				      <span class="pull-right txt">
					     <a href="<?php echo $g['s']?>/?r=<?php echo $r?>&amp;m=news&amp;a=attach_download&amp;uid=<?php echo $A['uid']?>" target="_action_frame_<?php echo $m?>" onclick="return confirm('정말로 다운로드하시겠습니까?');"><span class="badge" title="이 파일을 다운로드합니다"><i class="fa fa-download"></i></span></a>
				      </span>
						
				   </div>

				   </li>
                   <?php endwhile?>

                </ul>

		       </div>

			   </div>
			   <?php endif?>
			   <!--첨부파일-->

			   <?php $_FORM = getDbData($table['newsform'],"gid='".$R['gid']."'",'*');?>

		       <?php if($R['site']==237 && $_FORM['form']==1): //데일리 헬스?>
               <iframe src="http://www.newdailyhealth.co.kr/join_mobile.html" style="border:none;width:100%;height:320px;overflow-y:hidden;" title="Iframe Example" scrolling="no"></iframe>
		       <?php endif?>

	           <!--기사 배너 2-->
               <?php
               $_banner_sql_35 = 'site='.$R['site'].' and position=35 and auth=2 and device=2';
	           ?>

               <?php  $BNUM35  = getDbRows($table['newsbanner'],$_banner_sql_35);?>

		       <?php if($BNUM35):?>

			   <?php $NCD  = getDbSelect($table['newsbanner'],$_banner_sql_35.' order by rand() limit 1','*');?>
               <?php while($N=db_fetch_array($NCD)):?>

               <?php if($N['kind']==1): //이미지형이면?>

	           <div style="width:100%;overflow:hidden;margin-top:10px;text-align:center;">
				        <a href="<?php echo $N['url'] ? $N['url'] : '#.'?>" <?php if($N['target']=="_blank"):?>target="_blank"<?php endif?>><img src="<?php echo NEWS_IMG_SERVER?>/banner/<?php echo $N['upload']?>" alt="<?php echo $N['name']?>" width="100%"></a>
			   </div>

			   <?php elseif($N['kind']==2): //텍스트형?>

			   <div class="ad-banner-bottom-txt"><a href="<?php echo $N['url'] ? $N['url'] : '#.'?>" <?php if($N['target']=="_blank"):?>target="_blank"<?php endif?> class="news-banner-click" id="<?php echo $N['uid']?>"><?php echo $N['adtext']?></a></div>

			   <?php elseif($N['kind']==3): //스크립트형?>
               <div class="ad-banner-bottom-script" style="width:100%;margin-top:10px;text-align:center;"><?php echo $N['adscript']?></div>
	           <?php endif?>

			   <?php endwhile?>

	          <?php endif?>
	          <!--기사 배너 2 -->

              <!--쿠팡원래자리-->

               </div>
	           <!--기사본문내용-->

			   <!--작성자정보-->
			   <div class="write-box">				
				
					<div class="left">
					
				    <?php if(($s==325 && $R['site']==325) || ($s==396 && $R['site']==396) || ($s==383 && $R['site']==383) || ($s==325 && $R['site']==416) || ($s==416 && $R['site']==416) || ($s==400 && $R['site']==400) || ($s==447 && $R['site']==447) || ($s==432 && $R['site']==432) || ($s==435 && $R['site']==435) || ($s==463 && $R['site']==463) || ($s==464 && $R['site']==464)): //경기농촌관광신문 //misik.net //heartdayrest.com?>
						
						<?php 
						//rb_s194702_mbrgroup
						//$M = getDbData($table['s_mbrdata'],"id='".$R['id']."'",'*');
						$M = getDbData($table['s_mbrdata'],"id='".$R['id']."'",'*');
						$G = getUidData('rb_'.$r.'_mbrgroup',$M['mygroup']);
						?>
						<a class="name" href="<?php echo $g['s']?>/?r=<?php echo $_HS['id']?>&m=news&mod=rid&id=<?php echo $R['id']?>" rel="author"><?php echo $R['name']?> <?php echo $G['name']?></a> <a href="mailto:<?php echo $R['email']?>"><?php echo $R['email']?></a>
						
						<?php elseif($R['site']=="365"): //디지털배움뉴스?>
						
                        <a class="name" href="<?php echo $g['s']?>/?r=<?php echo $_HS['id']?>&m=news&mod=rid&id=<?php echo $R['id']?>" rel="author"><?php echo $R['name']?><?php if($R['site']==$s && $R['site']=="365"):?> <?php echo $_CG['name']?><?php elseif($R['site']==$s && $R['site']=="366"):?> <?php echo $_CG['name']?><?php else:?> 기자<?php endif?></a> <a href="mailto:<?php echo $R['email']?>"><?php echo $R['email']?></a>								
						
						<?php else:?>					
					
						<span style="display:none"><?php echo $R['name']?> <?php if($r!="s101939" && $r!="s151123"):?>기자<?php endif?> </span>
						<?php $EM = getDbData('rb_news_mail',"site=".$R['site']." and gid=".$R['gid'],'*');?>
						<a class="name" href="<?php echo $g['s']?>/?r=<?php echo $_HS['id']?>&m=news&mod=rid&id=<?php echo $R['id']?>" rel="author"><?php echo $R['name']?> <?php if($r!="s101939" && $r!="s151123"):?>기자<?php endif?> </a><?php if($s!=225): //더케이스타?><a href="mailto:<?php echo $R['email']?>"><?php echo $R['email']?></a><?php endif?>
						
						<?php endif?>
						
					</div>

					<div class="right">
				
				       <span class="date">
				
                        <?php
                        if($my['admin']) {
                        $_HX = getDbData($table['s_site'],"uid=".$s,'*'); //어드민이면

                        }else{
                        $_HX = getDbData($table['s_site'],"uid=".$s." and mbrid='".$my['id']."'",'*');
                        }
                        ?>	
					    
						<?php if($R['d_update']):?>
						<strong>최종수정</strong>
						<span><?php echo $R['d_update'] ? getDateFormat($R['d_update'],"Y.m.d H:i") : getDateFormat($R['d_regis'],"Y.m.d H:i")?></span>
						<?php endif?>
					
				       </span>

					</div>

					<div class="clearfix"></div>

			   </div>
			   <!--작성자정보-->

               <!--카피라이트-->
               <?php if($s != $R['site']):?>
		       <div class="sign-box" style="height:100px;border:#000000 0px solid;">
				  <?php $OS = getUidData($table['newsindex'],$R['uid']);?>
		          <?php $DM = getDbData('rb_news_url',"site=".$R['site'],'*');?>
                   
				   <?php
				   if($DM['https']) {
                   $http = "https://www.";
				   }else{
                   $http = "http://www.";
				   }
				   
				   ?>
				   
	              <?php
	              $OD = getDbData($table['newssite'],"sitecode=".$R['site'],'*');
				  
			      $post_id = $R['post_id'] ? $R['post_id'] : $R['uid'];					  
	  
	              if($s==$R['site']) {
	              $osite = "http://www.".$OD['domain']."/news/".$R['id'];
	              $osite = str_replace("www.www.","www.",$osite);		  
	              }else{
	              $osite = "http://www.".$OD['domain']."/news/".$post_id;
	              $osite = str_replace("www.www.","www.",$osite);
	              }
	              ?>				   

			      <div style="width:100%;overflow:hidden;margin-bottom:5px;"><p style="font-size:14px;color:#333;">RSS피드 기사제공처  : <?php if($R['site']==16 && $R['category']==22):?>얼리어답터뉴스<?php else:?><a href="http://<?php echo $OS1['domain'].'/news/'.($R['post_id'] ? $R['post_id'] : $R['uid'])?>" target="_blank"><?php echo $OS['site_name']?></a><?php endif?> / 등록기자: <?php echo $R['name'] ?></p>
                  <p style="font-size:14px;color:#333;"> 무단 전재 및 재배포금지</p>				  
				  </div>
                  <div style="width:100%;height:20px;margin-bottom:5px;"><p style="font-size:14px;color:#333;">해당기사의 문의는 기사제공처에게 문의</p></div>
		       </div>
			   <?php else:?>
		       <div class="sign-box">
			      <span>Copyrights ⓒ <?php echo $_HS['name']?>. 무단 전재 및 재배포금지</span>

			      <span class="pull-right">
                  <?php if($R['site']==325 || $R['site']==396 || $R['site']==383 || $R['site']==389 || $R['site']==416 || $R['site']==400 || $R['site']==447 || $R['site']==432): //경기농촌관광신문 //misik.net //heartdayrest.com?>
				  <?php 
				  //rb_s194702_mbrgroup
				  //$M = getDbData($table['s_mbrdata'],"id='".$R['id']."'",'*');
				  $M = getDbData($table['s_mbrdata'],"id='".$R['id']."'",'*');
				  $G = getUidData('rb_'.$r.'_mbrgroup',$M['mygroup']);
				  ?>
			      <a href="<?php echo $g['s']?>/?r=<?php echo $_HS['id']?>&m=news&mod=rid&id=<?php echo $R['id']?>"><?php echo $R['name']?> <?php echo $G['name']?> 뉴스보기</a>
						
				  <?php else:?>				  
				  
			      <a href="<?php echo $g['s']?>/?r=<?php echo $_HS['id']?>&m=news&mod=rid&id=<?php echo $R['id']?>"><?php echo $R['name']?><?php if($R['site']=="365"): //디지털배움뉴스?> <?php echo $_CG['name']?><?php elseif($r!="s101939" && $r!="s151123"):?>기자<?php endif?> 뉴스보기</a>
				  <?php endif?>
				  
				  </span>
		       </div>
		       <?php endif?>
               <!--카피라이트-->

			   <!--목록으로-->
			   <div class="sign-box">
			   <p class="text-center"><button type="button" class="btn btn-default" onclick="btnChange('<?php echo $g['s']?>/?r=<?php echo $r?>&c=<?php if($s==$R['site']):?><?php echo $R['cdata']?><?php else:?>11<?php endif?>&spos=<?php echo $R['uid']?>');">목록으로</button></p>
			   </div>
			   <!--목록으로-->

               <!--SNS 공유-->
		       <div class="sns-box2">

			   <!--sns-->
               <?php
               if($g['mobile']) {
			   $sns_size = 40;
			    }else{
               $sns_size = 30;
			   }
		       //이미지 추출
		       $kakaoimg = $d['layout']['mobile']['kakao_logo_img'] ? $g['url_root'].'/_var/site/kakao/'.$d['layout']['mobile']['kakao_logo_img'] : $g['url_root'].$g['path_module'].'booking/themes/default/image/default.png';
		       $snsimg    = $d['layout']['mobile']['shortcut_iconimg'] ? $g['url_root'].'/_var/site/shortcut/'.$d['layout']['mobile']['shortcut_iconimg'] : $g['url_root'].$g['path_module'].'booking/themes/default/image/default.png';

			   if($_SERVER['SERVER_NAME']=="www.enterstar.net") {
               $kakaokey = 'e5fb207f4334bfc2995d2e973ecd7dcb';
               }elseif($_SERVER['SERVER_NAME']=="www.safety24.kr") {
			   $kakaokey = 'ff3e4a630d0f9077cfe3c7f7761bb8ba';
			   }elseif($_SERVER['SERVER_NAME']=="www.ibntv.or.kr") {
			   $kakaokey = 'ff3e4a630d0f9077cfe3c7f7761bb8ba';

			   }elseif($_SERVER['SERVER_NAME']=="gmtimes.co.kr") {
			   $kakaokey = '4ecb244bd8de571712a00c3d2aa84b94';
			   }elseif($_SERVER['SERVER_NAME']=="www.gmtimes.co.kr") {
			   $kakaokey = '4ecb244bd8de571712a00c3d2aa84b94';

			   }elseif($_SERVER['SERVER_NAME']=="knana.kr") {
			   $kakaokey = 'c931aa52684f8571af64e9851aebfd59';

			   }elseif($_SERVER['SERVER_NAME']=="www.knana.kr") {
			   $kakaokey = 'c931aa52684f8571af64e9851aebfd59';

			   }else{
			   $kakaokey = $d['sns']['kakao_javascript'] ? $d['sns']['kakao_javascript'] : '949b4cbecbce0a4d57ef98b51794b793';
			   }

			   $kakao_title = $g['meta_tit'];
			   //$kakao_title = str_replace("'","&apos;",$kakao_title);
			   $kakao_title =str_replace("'", "\'", $kakao_title);


               //앰퍼샌드	&	&amp;
               //작은따옴표	'	&apos;
               //큰따옴표	"	&quot;
               //이상	>	&gt;
               //이하	<	&lt;
               $news_blog = news_blog_data ('../../../', $R['uid'], $R['account'], $R['d_regis']);		   
			   $band_body = $kakao_title.' '.getStrCut(strip_tags($news_blog),100,'...');

		       ?>
               <div class="sns sns-pos-center">
			   <span class="news-url-copy" id="<?php echo $g['url_root']?>/news/<?php echo $R['uid']?>" title="주소복사"><i class="fa fa-link fa-2x"></i></span>			   			   
		       <a href="javascript:SendSNS('facebook', '<?php echo $_HS['name']?>', '<?php echo $g['url_root'].'/news/'.$R['uid']?>','<?php echo $snsimg?>')" class="facebook sns-button"><span class="alternate"><img src="<?php echo $g['url_root']?>/layouts/skinx-skin/_images/snsimage/facebook.png" width="<?php echo $sns_size?>"></span></a>

		       <a href="javascript:SendSNS('twitter', '<?php echo $_HS['name']?>', '<?php echo $g['url_root'].'/news/'.$R['uid']?>','<?php echo $snsimg?>')" class="twitter sns-button"><span class="alternate"><img src="<?php echo $g['url_root']?>/layouts/skinx-skin/_images/snsimage/twitter.png" width="<?php echo $sns_size?>"></span></a>

                <!--
		       <a href="javascript:sendKakaoLink('949b4cbecbce0a4d57ef98b51794b793', '<?php echo $g['url_root'].'/news/'.$R['uid']?>', '<?php echo $_HS['name']?>', '<?php echo $kakaoimg?>')" class="kakaotalk sns-button"><span class="alternate"><img src="<?php echo $g['url_root']?>/layouts/skinx-skin/_images/snsimage/kakaotalk.png" width="<?php echo $sns_size?>"></span></a>
			   -->

               <a id="kakao-link-btn" href="javascript:;" class="kakaotalk sns-button"><span class="alternate"><img src="<?php echo $g['url_root']?>/layouts/skinx-skin/_images/snsimage/kakaotalk.png" width="<?php echo $sns_size?>"></span></a>

		       <a href="javascript:ShareKakaostory('<?php echo $kakaokey?>', '<?php echo $g['url_root'].'/news/'.$R['uid']?>', '<?php echo $_HS['name']?>')" class="kakaostory sns-button"><span class="alternate"><img src="<?php echo $g['url_root']?>/layouts/skinx-skin/_images/snsimage/kakaostory.png" width="<?php echo $sns_size?>"></span></a>

               <!--
			   <a href="javascript:SendSNS('google', '<?php echo $_HS['name']?>', '<?php echo $g['url_root'].'/news/'.$R['uid']?>','<?php echo $snsimg?>')" class="google sns-button"><span class="alternate"><img src="<?php echo $g['url_root']?>/layouts/skinx-skin/_images/snsimage/google.png" width="<?php echo $sns_size?>"></span></a>
			   -->

		       <a href="javascript:SendSNS('naverband', '', '<?php echo $g['url_root'].'/news/'.$R['uid']?>','<?php echo $snsimg?>')" class="band sns-button"><span class="alternate"><img src="<?php echo $g['url_root']?>/layouts/skinx-skin/_images/snsimage/naverband.png" width="<?php echo $sns_size?>"></span></a>

		       <a href="javascript:SendSNS('naverblog', '<?php echo $_HS['name']?>', '<?php echo $g['url_root'].'/news/'.$R['uid']?>','')" class="blog sns-button"><span class="alternate"><img src="<?php echo $g['url_root']?>/layouts/skinx-skin/_images/snsimage/naverblog.png" width="<?php echo $sns_size?>"></span></a>

		       <a href="javascript:SendSNS('naverline', '<?php echo $_HS['name']?>', '<?php echo $g['url_root'].'/news/'.$R['uid']?>','<?php echo $snsimg?>')" class="line sns-button"><span class="alternate"><img src="<?php echo $g['url_root']?>/layouts/skinx-skin/_images/snsimage/naverline.png" width="<?php echo $sns_size?>"></span></a>
			   <!--sns-->
			   </div>
			
		       </div>
               <!--SNS 공유-->

	        </div>
	        <!--기사본문-->

	     </div>
	     <!--기사내용-->

         <!--댓글영역-->
         <?php include $g['dir_module_skin'].'comment.php'?>
         <!--// 댓글영역-->
		 
              <!--쿠팡상품태그 박스--->
			  <?php
			 //echo "view-mobile.php";
	          //입력
              $tmp_coupang_partners_id = "AF8150630"; //eanews
              $tmp_coupang_store_access_key = "c70d5581-434b-4223-9c81-f72641545958"; //eanews
              $tmp_coupang_store_secret_key = "115b6ad08b30eeba54a624f2ed94ca3f0f18005d";	 //eanews
              $tmp_coupang_store_tag = $R['goods_tag'] ? $R['goods_tag'] : $d['news']['coupang_store_tag'];
		
	          include_once $g['path_var'].'site/'.$r.'/'.$r.'_news_coupang.var.php';
	
	          $coupang_store_access_key = $d['news']['coupang_store_access_key'] ? $d['news']['coupang_store_access_key'] : $tmp_coupang_store_access_key;
	          $coupang_store_secret_key = $d['news']['coupang_store_secret_key'] ? $d['news']['coupang_store_secret_key'] : $tmp_coupang_store_secret_key;
	   	   
	          $coupang_store_tag    = $d['news']['coupang_store_tag'] ? $d['news']['coupang_store_tag'] : $tmp_coupang_store_tag;
	          $coupang_partners_id = $d['news']['coupang_partners_id'];
			  
	          $exptag = explode(",",$coupang_store_tag);
	          shuffle($exptag); //랜덤
	   
	          //echo $exptag[0];			  

	          //쿠팡상품검색
	          if($coupang_store_tag && $coupang_partners_id && $coupang_store_access_key && $coupang_store_secret_key) { //http://www.renewtimes.com/
		
                 $coupang_dir = $g['path_module'].'news/upload/coupang/';		// 기본경로

                 if (!is_dir($coupang_dir))
                 {
		          mkdir($coupang_dir,0707);
		          @chmod($coupang_dir,0707);
                }		
		
                $fserverurl = $g['path_module'].'news/upload/coupang/'.$r.'/';		// 상품저장.

                if (!is_dir($fserverurl))
                {
		         mkdir($fserverurl,0707);
		         @chmod($fserverurl,0707);
                 }

                 $coupang_product_time = $fserverurl.$r.'_coupang_product.txt';

                 if(!is_file($coupang_product_time)) {
                 $fp = fopen($coupang_product_time,'w');
                 fwrite($fp, 1);
                 fclose($fp);
                 @chmod($coupang_product_time,0707);
                 }

                 if (file_exists($coupang_product_time)) {
                 $coupang_modified = date ("Y-m-d H:i:s", filemtime($coupang_product_time));
                 }

                 $coupang_nowday = date("Y-m-d H:i:s");

                 $coupang_gapMinute = (int)((strtotime($coupang_nowday) - strtotime($coupang_modified)) / 60);

	             if($coupang_gapMinute >= 5) { //5분마다
	   
	              //가져오기
                  date_default_timezone_set("GMT+0");

                  $datetime = date("ymd").'T'.date("His").'Z';
                  $method = "GET";

                  //상품수 최대 10개 limit=10
                  // (1분당 최대 50번 호출 가능합니다.)
                  $path = "/v2/providers/affiliate_open_api/apis/openapi/products/search?keyword=".urlencode(trim($exptag[0]))."&limit=10"; //검색어로 상품추출 GET 최대 상품 수는 10개 이며, 기본값은 10개 입니다.

                  $message = $datetime.$method.str_replace("?", "", $path);

                  // Replace with your own ACCESS_KEY and SECRET_KEY
                  $ACCESS_KEY = $coupang_store_access_key;
                  $SECRET_KEY = $coupang_store_secret_key;

                  $algorithm = "HmacSHA256";

                  $signature = hash_hmac('sha256', $message, $SECRET_KEY);

                  //print($message."\n".$SECRET_KEY."\n".$signature."\n");


                  $authorization  = "CEA algorithm=HmacSHA256, access-key=".$ACCESS_KEY.", signed-date=".$datetime.", signature=".$signature;

                  $url = 'https://api-gateway.coupang.com'.$path;

                  $strjson='
                  {
                   "coupangUrls": [
                   "https://www.coupang.com/np/search?component=&q=good&channel=user", 
                   "https://www.coupang.com/np/coupangglobal"
                   ]
                  }
                 ';

                  //print nl2br($strjson);

                  $curl = curl_init();        
                  curl_setopt($curl, CURLOPT_URL, $url);
                  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                  curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:  application/json;charset=UTF-8", "Authorization:".$authorization));        
                  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt($curl, CURLOPT_POSTFIELDS, $strjson);
                  $result = curl_exec($curl);
                  $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                  curl_close($curl);

                  //echo($httpcode);

                  //echo($result);

                  //받은 JSON데이터를 배열로 만듬 
                  $json_data = json_decode($result,true); 

                  //print_r($json_data['data']);
	   
	              $COUPANG_PRODUCT = $json_data['data'];
	   
	              //여기서 파일로 저장
                  $rFile = $fserverurl.$r.'_coupang_product.php';
                   file_put_contents($rFile, '<?php $COUPANG_PRODUCT = ' . var_export($COUPANG_PRODUCT, true) . ';');
	              @chmod($rFile,0707);
	              //여기서 파일로 저장		

                  unlink($coupang_product_time); //업데이트가 끝나면 파일제거 (새로 생성해야 하므로)
	   
                  if(!is_file($coupang_product_time)) {
                  $fp = fopen($coupang_product_time,'w');
                   fwrite($fp, 1);
                   fclose($fp);
                   @chmod($coupang_product_time,0707);
                    }

	                //가져오기

	                } //1분마다

	             }
	            //쿠팡상품검색
				$_k = 1;
				
                include_once $fserverurl.$r.'_coupang_product.php';				
                shuffle($COUPANG_PRODUCT['productData']); 						
			  ?>
              
			  <?php if(count($COUPANG_PRODUCT['productData'])):?>
			  <div class="coupang-box">
              <?php foreach($COUPANG_PRODUCT['productData'] as $val):?>
	          <?php
	          $product_img = $val['productImage']; 
              
			  if($d['news']['coupang_partners_id']) {
	          $product_url = str_replace("AF8150630",$d['news']['coupang_partners_id'],$val['productUrl']); 
			  }else{
	          $product_url = $val['productUrl'];
			  }
	          ?>

	          <!--쿠팡기본형-->
	          <div class="default_coupang" style="margin-right:<?php if($_k%2):?>15<?php else:?>0<?php endif?>px;margin-bottom:15px;">
   
		         <!--shop-->
                 <span class="shop">						
		            <div class="photo-box">
			         <a  href="<?php echo $product_url?>" target="_blank"><div class="photo" style="background-image: url('<?php echo $product_img?>');"></div></a>
			        </div>	

			         <!--shop_desc-->
			         <span class="shop_desc">
			            <span class="shop_tit"><a  href="<?php echo $product_url?>" target="_blank"><?php echo getStrCut($val['productName'],20,'...')?></a></span>
			         </span>
			         <!--shop_desc-->

			         <!--shop_desc-->
			         <span class="shop_opt">
				        <span class="shop_price"><?php echo number_format($val['productPrice'])?>원</span>
				        <span class="shop_rocket"></span>
			         </span>
			         <!--shop_desc-->

			         <!--shop_btn-->
			         <span class="shop_btn">
			         <div class="product-name hide"><?php echo $val['productName']?></div>
			         <div class="product-img hide"><?php echo $product_img?></div>
			         <a href="<?php echo $product_url?>" target="_blank" class="btn btn-default btn-sm product-url">자세히보기</a>
			         </span>
			         <!--shop_btn-->

		         </span>
		         <!--shop-->

	          </div>
	          <!--쿠팡 기본형-->
			  
			  <?php if($_k==8) break;?>			  

	          <?php $_k++; endforeach?>

			  <div class="guide">이 포스팅은 쿠팡 파트너스 활동의 일환으로, 이에 따른 일정액의 수수료를 제공받습니다.</div>

			  </div>
			  <?php endif?>
              <!--쿠팡상품태그 박스--->		 
		 
		 <!--C유튜브-->
		 
            <!--쇼츠-->
	        <?php include_once $g['dir_layout'].'_pages/shorts/var/'.$r.'_shorts.var.php'?>
	        <?php if($d['layout']['content_18_1_content_display']):?>
            <div class="news-shorts" style="width:100%;overflow:hidden;margin-bottom:15px;">

               <!--콘텐츠-->
               <link href="<?php echo $g['dir_layout'].'_pages/shorts/shorts-view.css';?>" rel="stylesheet">
               <?php include $g['dir_layout'].'_pages/shorts/shorts.php'?>
		       <!--콘텐츠-->

            </div>
	        <?php endif?>
            <!--쇼츠-->
			
            <!--비디오-->
		    <?php include_once $g['dir_layout'].'_pages/video/var/'.$r.'_video.var.php'?>
		    <?php if($d['layout']['content_16_1_content_display']):?>
            <div class="news-video" style="width:100%;overflow:hidden;margin-bottom:15px;">

               <!--콘텐츠-->
               <link href="<?php echo $g['dir_layout'].'_pages/video/video-view.css';?>" rel="stylesheet">
               <?php include $g['dir_layout'].'_pages/video/video.php'?>
		       <!--콘텐츠-->

            </div>
		    <?php endif?>
            <!--비디오-->			
			
		 
		 <!--C유튜브-->		
		 
         <!--GOODS-->
	     <?php include_once $g['dir_layout'].'_pages/goods/goods.var.php'?>
	     <?php if($d['layout']['content_19_1_content_display']):?>
         <div class="news-goods" style="width:100%;overflow:hidden;margin-bottom:15px;">

         <!--콘텐츠-->
         <link href="<?php echo $g['dir_layout'].'_pages/goods/goods.css';?>" rel="stylesheet">
         <?php include $g['dir_layout'].'_pages/goods/goods.php'?>
		 <!--콘텐츠-->

         </div>
	     <?php endif?>
         <!--GOODS-->	 
	  
         <!--SHARE-GOODS-->
	     <?php include_once $g['dir_layout'].'_pages/share-goods/share-goods.var.php'?>
	     <?php if($d['layout']['content_20_1_content_display']):?>
         <div class="news-share-goods" style="width:100%;overflow:hidden;margin-bottom:15px;">

         <!--콘텐츠-->
         <link href="<?php echo $g['dir_layout'].'_pages/share-goods/share-goods.css';?>" rel="stylesheet">
         <?php include $g['dir_layout'].'_pages/share-goods/share-goods.php'?>
		 <!--콘텐츠-->

         </div>
	     <?php endif?>
         <!--SHARE-GOODS-->	
		 
         <!--JINJJAYO-->
	     <?php include_once $g['dir_layout'].'_pages/jinjjayo/var/'.$r.'_jinjjayo.var.php';?>
	     <?php if($d['layout']['content_21_1_content_display']):?>
         <div class="news-jinjjayo" style="width:100%;overflow:hidden;margin-bottom:15px;">

         <!--콘텐츠-->
         <link href="<?php echo $g['dir_layout'].'_pages/jinjjayo/jinjjayo.css';?>" rel="stylesheet">
         <?php include $g['dir_layout'].'_pages/jinjjayo/jinjjayo.php'?>
		 <!--콘텐츠-->

         </div>
	     <?php endif?>
         <!--JINJJAYO-->			 

	     <!--배너 기사 하단 -->
         <?php
         $_banner_sql_30 = 'site='.$s.' and position=30 and auth=2 and device=2';
	     ?>

         <?php  $BNUM30  = getDbRows($table['newsbanner'],$_banner_sql_30);?>

		 <?php if($BNUM30):?>

		 <?php $NCD  = getDbSelect($table['newsbanner'],$_banner_sql_30.' order by rand() limit 1','*');?>
         <?php while($N=db_fetch_array($NCD)):?>

         <?php if($N['kind']==1): //이미지형이면?>

	     <div style="width:100%;overflow:hidden;margin-bottom:10px;text-align:center;">
		    <a href="<?php echo $N['url'] ? $N['url'] : '#.'?>" <?php if($N['target']=="_blank"):?>target="_blank"<?php endif?>><img src="<?php echo NEWS_IMG_SERVER?>/banner/<?php echo $N['upload']?>" alt="<?php echo $N['name']?>" width="100%"></a>
		 </div>

		 <?php elseif($N['kind']==2): //텍스트형?>

		 <div class="ad-banner-bottom-txt"><a href="<?php echo $N['url'] ? $N['url'] : '#.'?>" <?php if($N['target']=="_blank"):?>target="_blank"<?php endif?> class="news-banner-click" id="<?php echo $N['uid']?>"><?php echo $N['adtext']?></a></div>

		 <?php elseif($N['kind']==3): //스크립트형?>
         <div class="ad-banner-bottom-script" style="width:100%;margin-bottom:10px;text-align:center;"><?php echo $N['adscript']?></div>
	     <?php endif?>

		 <?php endwhile?>

	     <?php endif?>
	     <!--배너 기사 하단 -->


		 <!--하단 버튼-->
         <div class="bottom-button-box">
	        <div class="left-side"> </div>

			<div class="right-side">
			   <!---
			   <?php if($my['admin'] || $_HX['mbrid']):?>
			   <button type="button" class="btn btn-primary" onclick="btnChange('<?php echo $g['s']?>/?r=<?php echo $r?>&m=news&mod=regis&c=<?php echo $c?>');">뉴스등록</button>
			   <?php endif?>
			   --->
			</div>

			<div class="clearfix"></div>
	     </div>
	     <!--하단 버튼-->

	   <!---홈고 리스트---->
	   <div class="pc-box">
       <?php
		  $fserverurl = '../../../../../../../../../../var/www/html/news_update/';
          $news_mobile_homgo_file = $fserverurl.$r.'_news_mobile_homgo.txt';

          if (file_exists($news_mobile_homgo_file)) {
            $last_modified = date ("Y-m-d H:i:s", filemtime($news_mobile_homgo_file));
            //echo $last_modified;
            }
           $nowday = date("Y-m-d H:i:s");

          //$diff  = date("Y-m-d H:i:s" , strtotime($nowday."-60 minutes") );
          $gapMinute = (int)((strtotime($nowday) - strtotime($last_modified)) / 60);
          //$gapMinute."분 차이<br>";

		  if($gapMinute > 14400 && $_SESSION['country_code']=="kr") {		
          $newshomgo = getNewsHomgo ('mobile', $d['news']['mobile']['news_skin'], 'mobile_homgo', 16, $r, 22, 30, 250);
          unlink($news_mobile_homgo_file);	  
		  }
	   ?>
       <?php $mobile_homgo_file = $g['path_module'].'news/upload/news/content/'.$r.'/mobile/'.$d['news']['mobile']['news_skin'].'/'.$r.'_mobile_homgo_news.php'; if(is_file($mobile_homgo_file)) include $mobile_homgo_file; ?>

       <div style="width:100%;overflow:hidden;<?php if($my['admin']):?>height:500px;overflow-y:scroll;<?php else:?>height:0px;<?php endif?>">

       <?php $v=1; for($_i=0; $_i < count($SUID); $_i++):?>	
	   <?php $news_content = news_blog_data ('../../../../../../../', $SUID[$_i]['uid'], $SUID[$_i]['account'], $SUID[$_i]['d_regis']);?>

	   <?php 
	   if(preg_match('/MSIE/i',$browser)) { //익스 이면
       $newsimg = $SUID[$_i]['eximg'];
	   }else{
       $newsimg = $SUID[$_i]['newsimg'];
	   }

         $newsimg = str_replace("https://www.toplink.kr/upload/post/photo/",NEWS_IMG_SERVER."/toplink/photo/",$newsimg);
	   ?>
       <?php if($newsimg):?>
	   <!--썸네일 기본형-->
	   <div class="default_thumb">
          <div class="thumb-box">
	      <a href="<?php echo $NEWS_VIEW.$SUID[$_i]['uid']?>"><div class="thumb" style="background-image: url('<?php echo $newsimg?>');"></div></a>
	      </div>

          <div class="desc">
             <div class="st"><a href="<?php echo $NEWS_VIEW.$SUID[$_i]['uid']?>"><?php echo getStrCut($SUID[$_i]['subject'],80,'...')?></a></div>
             <div class="cont"><a href="<?php echo $NEWS_VIEW.$SUID[$_i]['uid']?>"><?php echo getStrCut(strip_tags($news_content),250,'...')?></a></div>
			 <div class="ninfo"><?php echo getDateFormat($SUID[$_i]['d_regis'],"Y-m-d H:i:s")?> / <?php echo $SUID[$_i]['name']?>기자</div>
	      </div>

	      <div class="clearfix"></div>

	   </div>
	   <!--썸네일 기본형-->

	   <?php else:?>

	   <!--리스트 기본형-->
       <div class="default_list" id="news-post-<?php echo $SUID[$_i]['uid']?>">
          <div class="st">
	         <a href="<?php echo $NEWS_VIEW.$SUID[$_i]['uid']?>"><?php echo getStrCut($SUID[$_i]['subject'],40,'...')?></a>
          </div>

          <div class="desc">
	         <a href="<?php echo $NEWS_VIEW.$SUID[$_i]['uid']?>"><?php echo getStrCut(strip_tags($news_content),250,'...')?></a>
			 <div class="ninfo"><?php echo getDateFormat($SUID[$_i]['d_regis'],"Y-m-d H:i:s")?> / <?php echo $SUID[$_i]['name']?>기자</div>
	      </div>
       </div>
       <!--리스트 기본형-->

	   <?php endif?>

       <?php endfor?>

       </div>

	   </div>
	   <!---홈고 리스트---->

   </div>
   <!--viewbox-->

</div>

<script src="<?php echo $g['url_root']?>/modules/news/plugin/popup/clippopup.js"></script>

<input type="hidden" name="meta_title" value="<?php echo $g['meta_tit']?>" id="meta-title">
<input type="hidden" name="meta_des" value="<?php echo $g['meta_des']?>" id="meta-desc">
<input type="hidden" name="meta_cnt" value="<?php echo $band_body?>" id="meta-content">

<script type="text/javascript">
//<![CDATA[

$(document).on("click", ".news-url-copy", function () {
	
  var news_url = this.id;
  //alert(news_url);

  var $temp = $("<input>");
  $("body").append($temp);

  $temp.val(news_url).select();

  document.execCommand("copy");
  $temp.remove();

    $('.news-view').pgpopup({
        type:'toast',                  // 팝업형태 (toast, layer, slide)
        msg:'복사 되었습니다.',             // 메시지
        padding:'15px',              // 여백
        width:'250',                // 토스트폭, %로 지정
        color:'#ffffff',               // 내용 글자색
        bgcolor:'#111111',        // 레이어 배경색, #111111 와 같이 헥사코드 이용
        transparency:'0.6',         // 투명도, 0.8 과 같이 값을 입력, 최대 1
        delay:'1000',                // 얼마의 시간이 지난뒤 사라지게 할것인지 , 1000 = 1초, toast와 slide에만 적용됨
        time:'1000',                 // 서서히 보여지는 시간, 1000 = 1초
        direction:'up'               // slide 팝업의 경우 어느방향에서 나타나게 할지의 여부(up,down)
    });


});

    $(function() { // 함수의 시작

    $("#vContent a[href^='http://']").attr("target","_blank");
    $("#vContent a[href^='https://']").attr("target","_blank");

     }); // 전체 함수 종료

function ConvertSystemSourcetoHtml(str){
 str = str.replace(/</g,"&lt;");
 str = str.replace(/>/g,"&gt;");
 str = str.replace(/\"/g,"&quot;");
 str = str.replace(/\'/g,"&#39;");
 str = str.replace(/\n/g,"<br />");
 return str;
}


var meta_tit = $("#meta-title").val();
var meta_des1 = $("#meta-desc").val();

var meta_des = ConvertSystemSourcetoHtml(meta_des1);

var meta_cnt = $("#meta-content").val();
var meta_content = ConvertSystemSourcetoHtml(meta_cnt);

function imgSize()
{
 
   var maxWidth = $(window).width();
   var photoarea = $(".attach-photo");
   var xWidth = maxWidth-20; //좌우 여백 20 제외
  
   photoarea.each(function() {
 
   var photo = $(this).find( ".photo" );

   if(photo.length) {

   //영역크기
   var phoWidth = $(this).width();
   var phoHeight = $(this).height();

   //이미지 크기
   var imgWidth   = $(this).find( ".photo" ).width();
   var imgHeight  = $(this).find( ".photo" ).height();
 
   //if(imgWidth > xWidth) { //큰경우만

  var ratio = xWidth / imgWidth * 100;
  var height = imgHeight * ratio / 100;

  $(this).css({"width":"100%"})
  //$(this).find( ".photo" ).css({"width":"url(https://i.ytimg.com/vi/"+thumbid+"/hqdefault.jpg)", "background-repeat" : "no-repeat", "background-position":"center center", "background-size":"cover"})

  //alert('test');
  $(this).parent( "div" ).css({"line-height":""+1+"", "text-indent" : ""+0+"", "margin-right":""+0+"", "margin-left":""+0+""})
  $(this).find( ".photo" ).css({"position":"relative","width":""+xWidth+"", "height" : ""+Math.floor(height)+"", "background-size":""+xWidth+"px "+Math.floor(height)+"px "})
  

  if($(this).find( ".photo" ).attr('id')) {
  var img_url = $(this).find( ".photo" ).attr('id');
  }else{
  var B_ground = $(this).find( ".photo" ).css("background-image"); 
  var result0 = B_ground.replace('url("', '');
  var img_url = result0.replace('")', '');
  }

//alert(img_url)

  $(this).find( ".photo" ).html ('<img class="news-img" style="position:absolute;top:5px;right:5px;display:block;" src="<?php echo $g['url_root']?>/<?php echo $g['dir_layout']?>_images/exp.png" id="'+ img_url +'" align="right" width="30px" />');

  var capWidth = xWidth;
  $(this).find( ".caption" ).css({"width":""+capWidth+""});
  $(this).find( ".caption" ).css({"textAlign":"center"});
 
  


   } // if photo

	   
   });
  
}

$(document).ready(function() {

imgSize()

$('.news-img').on('click', function(){
    simpleLightbox($(this).attr('id'));
});


});
//ready



function btnChange(url) {

//alert(url);
document.location.href = url;

}

function fontFamilyBSKR(layer, fontFamily)
{
	if( fontFamily )
		getId(layer).style.fontFamily = fontFamily;
	else
		getId(layer).style.fontFamily = 'gulim';	

	setCookie('myFontFamily', fontFamily, 30);
}
function fontResizeBSKR(layer,type)
{
	var l = getId(layer);
	var nSize = getCookie('myFontSize');
	nSize = nSize? nSize: '<?php echo ($BSKR['is_mobile'])? '14': '12'?>px';
	var iSize = parseInt(nSize.replace('px',''));
	
	if (type == '+') {
		if (iSize < 20) l.style.fontSize   = (iSize + 1) + 'px';
	}
	else {
		if (iSize > 10) l.style.fontSize = (iSize - 1) + 'px';
	}
	setCookie('myFontSize',l.style.fontSize,30);
}

//sns

//sns
    // // 사용할 앱의 JavaScript 키를 설정해 주세요.
    Kakao.init('<?php echo $kakaokey?>');
    // // 카카오링크 버튼을 생성합니다. 처음 한번만 호출하면 됩니다.
    Kakao.Link.createDefaultButton({
      container: '#kakao-link-btn',
      objectType: 'feed',
      content: {
        title: meta_tit,
        description: meta_des,
        imageUrl: '<?php echo $g['meta_img']?>',
        link: {
          mobileWebUrl: '<?php echo $g['meta_url']?>',
          webUrl: '<?php echo $g['meta_url']?>'
        }
      },
      social: {
        likeCount: 286,
        commentCount: 45,
        sharedCount: 845
      },
      buttons: [
        {
          title: '자세히 보기',
          link: {
            mobileWebUrl: '<?php echo $g['meta_url']?>',
            webUrl: '<?php echo $g['meta_url']?>'
          }
        },
        {
          title: '앱으로 보기',
          link: {
            mobileWebUrl: '<?php echo $g['meta_url']?>',
            webUrl: '<?php echo $g['meta_url']?>'
          }
        }
      ]
    });

function ShareKakaostory(strKey, strUrl, strTitle){
    InitKakao(strKey);

    Kakao.Story.share({
        url: strUrl,
        text: strTitle
    });
}
function sendKakaoLink(strKey, strUrl, strTitle, strImage) {
    if(!navigator.userAgent.match(/(iphone|ipod|ipad|android)/i)){
        alert('모바일에서 공유 가능합니다.');
        return;
    }

    InitKakao(strKey);

    Kakao.Link.sendTalkLink({
        label: strTitle
        ,image: {
                src: strImage,
                width: '300',
                height: '300'
              }
        //,webLink: {
        //    text: strTitle,
        //    url: strUrl
        //}
        ,webButton: {
            text: strTitle,
            url: strUrl
        }

    });

}

function SendSNS(sns, title, url, image)
{
    var o;
    var _url = encodeURIComponent(url);
    var _title = encodeURIComponent(title);
    var _br  = encodeURIComponent('\r\n');

    switch(sns)
    {
        case 'facebook':
            o = {
                method:'popup',
                height:600,
                width:600,
                url:'http://www.facebook.com/sharer/sharer.php?u=' + _url
            };
            break;

        case 'twitter':
            o = {
                method:'popup',
                height:600,
                width:600,
                url:'http://twitter.com/intent/tweet?text=' + _title + '&url=' + _url
            };
            break;

        case 'google':
            o = {
                method:'popup',
                height:600,
                width:600,
                url:'https://plus.google.com/share?url={' + _url + '}'
            };
            break;

        case 'naverline':

           if(!navigator.userAgent.match(/(iphone|ipod|ipad|android)/i)){
            alert('모바일에서 공유 가능합니다.');
            return;
            }
            o = {
                method:'popup',
                url:'http://line.me/R/msg/text/?' + _title + ' '+ _url
            };
            break;
/*
        case 'naverband':

           if(!navigator.userAgent.match(/(iphone|ipod|ipad|android)/i)){
            alert('모바일에서 공유 가능합니다.');
            return;
            }
            o = {
                method:'web2app',
                param:'create/post?text=' + _title + _br + _url,
                a_store:'itms-apps://itunes.apple.com/app/id542613198?mt=8',
                g_store:'market://details?id=com.nhn.android.band',
                a_proto:'bandapp://',
                g_proto:'scheme=bandapp;package=com.nhn.android.band'
            };
            break;
*/
		case 'naverband':
			o = {
                method:'popup',
                height:510,
                width:540,
                url:'http://band.us/plugin/share?body='+ meta_content +'&route='+ _url				
			};
			break;
        case 'naverblog':
                o = {
                method:'popup',
                height:600,
                width:600,
                url:'http://blog.naver.com/openapi/share?url=' + _url + '&title=' + _title
            };
            break;

        default:
            return false;
    }

    switch(o.method)
    {
        case 'popup':
            if( o.height > 0 && o.width > 0 ){
                window.open(o.url,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height='+o.height+',width='+o.width);
            }
            else{
                 window.open(o.url);
            }

          break;

        case 'web2app':
          if(navigator.userAgent.match(/android/i)){
              setTimeout(function(){ location.href = 'intent://' + o.param + '#Intent;' + o.g_proto + ';end'}, 100);
          }
          else if(navigator.userAgent.match(/(iphone)|(ipod)|(ipad)/i)){
              setTimeout(function(){ location.href = o.a_store; }, 200);
              setTimeout(function(){ location.href = o.a_proto + o.param }, 100);
          }
          else{
              alert('모바일에서 공유 가능합니다.');
          }
          break;
    }
}

//sns

//코드 새로고침
$(document).ready(function() {
	
refreshNewCode()

});
//ready

function refreshNewCode() {

      var today = new Date();

      var year = today.getFullYear();
      var month = ('0' + (today.getMonth() + 1)).slice(-2);
      var day = ('0' + today.getDate()).slice(-2);
      var hours = ('0' + today.getHours()).slice(-2); 
      var minutes = ('0' + today.getMinutes()).slice(-2);
      var seconds = ('0' + today.getSeconds()).slice(-2); 

      //var dateString = year + '-' + month  + '-' + day;
      //var timeString = hours + ':' + minutes  + ':' + seconds;
	  var ymdhis = year + month  + day + hours + minutes  + seconds;		
       
      $('#captcha-img').attr('src', rooturl+'/captcha.php?t=' + ymdhis);
							
}
//코드 새로고침

let locale = navigator.language || navigator.userLanguage;

switch (locale) {
	case 'ko':
    case 'ko-KR':
    	locale = 'ko';
        break;
	case 'en':
    case 'en-US':
    	locale = 'en';
        break;
}

//console.log(locale);

//문의하기 좋아요
function gbWrite(a, n) { //a = act good bad / n = this
	   
	   if(locale=="ko") { //한국만
		   
	   var act  = a;
	   var r  = raccount;
	   var news_uid    = n;
	   //var news_url = rooturl+'/news/'+news_uid;
	   
	   var ipaddress = $("#ip-address").val();
	   	   	   
	   var good_num = parseInt($('#news-good-'+news_uid).text())+1;
	   var bad_num = parseInt($('#news-bad-'+news_uid).text())+1;
	   
	   var like_cookie = getCookie('like_check_'+news_uid);
	   //var like_cookie_check = like_cookie.split('_')[1];	   
	   if(like_cookie != ipaddress) { //쿠키체크
	   console.log(like_cookie);
	      
	   if(act=="good") {
		$('#news-good-'+news_uid).text(good_num);
		$('#news-good-'+news_uid).removeClass('hide');
		}else{
		$('#news-bad-'+news_uid).text(bad_num);
		$('#news-bad-'+news_uid).removeClass('hide');		
	    }
		
 	   var url = rooturl+'/modules/layoutconfig/action/a.like_ajaxregis.php';

	    var param = "";
        param = "&act="+ act;
        param += "&r="+ raccount; 
        param += "&news_uid="+news_uid; 
        param += "&ipaddress="+ipaddress; 		
        //param += "&news_url="+news_url; 		

	    $.ajax({
        type: 'post',
        dataType: "json",
        url: url,
		data: param,
	    cache: false,
        //beforeSend: function() {
        //    $('#'+targ).append("<div class='loading'><img src='./modules/domechanggo/themes/_pc/default/image/ajax-loader.gif'></div>");
        //},
		
        success: function(data) {
						
			   /*
				//데이타 넣기
                if(data.act=="good") {
				$('#news-good-'+data.news_uid).text(data.good);
				}else{
				$('#news-bad-'+data.news_uid).text(data.bad);
				}
				*/
				

        },//success

        complete: function(){
						

		}, //complete

    }); // ajax
	
    setCookie('like_check_'+news_uid,ipaddress,10);
	
	}else{ //쿠키 있으면
		
	//console.log('이미 좋아요누룸');
	
	} //쿠키체크
	
	}//한국만

 } //


//문의하기
function questionWrite(f) {

	   if(locale=="ko") { //한국만

	   //if(!memberid) {
        //alert('로그인후 작성 가능합니다.');
		//return false;
	   //}
	   

	   if (f.name && f.name.value == '')
	   {
		alert('이름을 입력해 주세요. ');
		f.name.focus();
		return false;
	   }
	   
	   if( f.tel_1 && jQuery.trim(f.tel_1.value) == '' )
	   {
		alert('전화번호를 입력해 주세요.      ');
		f.tel_1.focus();
		return false;
	   }	   
	   
	   if( f.tel_2 && jQuery.trim(f.tel_2.value) == '' )
	   {
		alert('전화번호를 입력해 주세요.      ');
		f.tel_2.focus();
		return false;
	   }	 
	   
	   if( f.tel_3 && jQuery.trim(f.tel_3.value) == '' )
	   {
		alert('전화번호를 입력해 주세요.      ');
		f.tel_3.focus();
		return false;
	   }	  	   

	   if( f.subject && jQuery.trim(f.subject.value) == '' )
	   {
		alert('제목을 입력해 주세요.      ');
		f.subject.focus();
		return false;
	   }
       
	   if( f.content && jQuery.trim(f.content.value) == '' )
	   {
		alert('내용을 입력해 주세요.       ');
		f.content.focus();
		return false;
	   }
	   
	   if( f.email && jQuery.trim(f.email.value) == '' )
	   {
		alert('메일 주소를 입력해 주세요.       ');
		f.email.focus();
		return false;
	   }	   
	   	   
	   if(f.captcha_text.value == '') {
	   alert('보안코드를 입력해 주세요.');
	   f.captcha_text.focus();	   
	   return false;
	   }
	   
	   if(f.captcha_text.value != getCookie('captchastr')) {
	   alert('보안코드를 정확히 입력해 주세요.');
	   f.captcha_text.value = '';	   
	   f.captcha_text.focus();	   
	   return false;
	   }	   	   
	   	   
		if( confirm('정말 실행 하시겠습니까?    ') )
	    {
					
			
		}else{
		
			return false;
			
		}
		
		
	   }else{ //한국아니면
		
		   return false;
		   
	   }	 //한국아니면

 } //

//]]>
</script>