        <?php
		  //$fserverurl = '../../../../../../var/www/html/news_update/';
		  $fserverurl = $g['path_module'].'news/upload/news/content/'.$r.'/news_update/';

		  //echo $fserverurl;

		  if (!is_dir($fserverurl))
          {
		  mkdir($fserverurl,0707);
		  @chmod($fserverurl,0707);
          }

           $nowday = date("Y-m-d H:i:s");

          $side_content_file1 = $fserverurl.$r.'_news_side_content1.txt';
          $side_content_file2 = $fserverurl.$r.'_news_side_content2.txt';
          $side_content_file3 = $fserverurl.$r.'_news_side_content3.txt';
          $side_content_file4 = $fserverurl.$r.'_news_side_content4.txt';

          //파일1
          if (file_exists($side_content_file1)) {
            $side_modified1 = date ("Y-m-d H:i:s", filemtime($side_content_file1));
          }else{

          //파일없으면 생성
          $fp = fopen($side_content_file1,'w');
          fwrite($fp, 1);
          fclose($fp);
         @chmod($side_content_file1,0707);
          //파일없으면 생성
		  }

          //파일2
          if (file_exists($side_content_file2)) {
            $side_modified2 = date ("Y-m-d H:i:s", filemtime($side_content_file2));
          }else{

          //파일없으면 생성
          $fp = fopen($side_content_file2,'w');
          fwrite($fp, 1);
          fclose($fp);
         @chmod($side_content_file2,0707);
          //파일없으면 생성
		  }

          //파일3
          if (file_exists($side_content_file3)) {
            $side_modified3 = date ("Y-m-d H:i:s", filemtime($side_content_file3));
          }else{

          //파일없으면 생성
          $fp = fopen($side_content_file3,'w');
          fwrite($fp, 1);
          fclose($fp);
         @chmod($side_content_file3,0707);
          //파일없으면 생성
		  }

          //파일4
          if (file_exists($side_content_file4)) {
            $side_modified4 = date ("Y-m-d H:i:s", filemtime($side_content_file4));
          }else{

          //파일없으면 생성
          $fp = fopen($side_content_file4,'w');
          fwrite($fp, 1);
          fclose($fp);
         @chmod($side_content_file4,0707);
          //파일없으면 생성
		  }

          $Side_Minute1 = (int)((strtotime($nowday) - strtotime($side_modified1)) / 60);
          $Side_Minute2 = (int)((strtotime($nowday) - strtotime($side_modified2)) / 60);
          $Side_Minute3 = (int)((strtotime($nowday) - strtotime($side_modified3)) / 60);
          $Side_Minute4 = (int)((strtotime($nowday) - strtotime($side_modified4)) / 60);

if($Side_Minute1 > 2 && $d['news']['news_side_content_recnum_1']) {
$newsside1 = getNewsSide ('pc', $d['news']['news_skin'], $d['news']['news_side_content_share_1'], $d['news']['share_type'], 1, 'sidetab_1', $s, $r, $d['news']['news_side_content_menu_1'], $d['news']['news_side_content_recnum_1'], 50);
unlink($side_content_file1);
}

if($Side_Minute2 > 4 && $d['news']['news_side_content_recnum_2']) {
 $newsside2 = getNewsSide ('pc', $d['news']['news_skin'], $d['news']['news_side_content_share_2'], $d['news']['share_type'], 1, 'sidetab_2', $s, $r, $d['news']['news_side_content_menu_2'], $d['news']['news_side_content_recnum_2'], 50);
unlink($side_content_file2);
}

if($Side_Minute3 > 6 && $d['news']['news_side_content_recnum_3']) {
$newsside3 = getNewsSide ('pc', $d['news']['news_skin'], $d['news']['news_side_content_share_3'], $d['news']['share_type'], 1, 'side_1', $s, $r, $d['news']['news_side_content_menu_3'], $d['news']['news_side_content_recnum_3'], 50);
unlink($side_content_file3);
}

if($Side_Minute4 > 8 && $d['news']['news_side_content_recnum_4']) {
$newsside4 = getNewsSide ('pc', $d['news']['news_skin'], $d['news']['news_side_content_share_4'], $d['news']['share_type'], 1, 'side_2', $s, $r, $d['news']['news_side_content_menu_4'], $d['news']['news_side_content_recnum_4'], 100);
unlink($side_content_file4);
}

$_NEWS_VIEW = $g['url_root'].'/news/'
?>

<!--콘텐츠-->
<div class="side_box_content">

   <!--사이드배너1-->
   <?php if($d['news']['news_side_banner_display_1']):?>

   <?php
   $b_today = date("Ymd");
   if($r=="s151712") {
   $b14_sql = 'site='.$s.' and position=14 and auth=2 and device=1';
   $b14_sql .= ' and d_start<='.$b_today.' and d_finish>='.$b_today;
   }else{
   $b14_sql = 'site='.$s.' and position=14 and auth=2 and device=1';
   }
   ?>

   <?php if($d['news']['banner_kind14']==1): //이미지형이면?>
   <div class="ad-banner-side">
      <div class="wrap_ad side_banner_1">
         <ul class="list_ad">
            <?php $_i=0;  $_NCD  = getDbSelect($table['newsbanner'],$b14_sql.' order by rand() limit 8','*');?>
            <?php while($_N=db_fetch_array($_NCD)):?>
			<li data-toggle="tooltip" title="<?php echo $_N['name']?>"><a href="<?php echo $_N['url'] ? $_N['url'] : '#.'?>" <?php if($_N['target']):?>target="<?php echo $_N['target']?>"<?php endif?>><img src="<?php echo getBannerImage($_N['upload'])?>" alt="<?php echo $_N['name']?>" width="275" class="news-banner-click" id="<?php echo $_N['uid']?>"></a></li>
			<?php $_i++; endwhile?>
	     </ul>

		 <div class="btn_page hide">
		 <a href="#." class="prev"><img src="<?php echo $g['img_layout']?>/btn_ad_arrow_prev.png"></a>
		 <a href="#." class="next"><img src="<?php echo $g['img_layout']?>/btn_ad_arrow_prev.png"></a>
	     </div>

	  </div>

   </div>

   <?php elseif($d['news']['banner_kind14']==2): //텍스트형이면?>
   <?php $_i=0; $_NCD = getDbArray($table['newsbanner'],$b14_sql,'*','gid','asc',0,1)?>
   <?php while($_N=db_fetch_array($_NCD)):?>
   <div class="ad-banner-side-txt"><a href="<?php echo $_N['url'] ? $_N['url'] : '#.'?>" <?php if($_N['target']):?>target="<?php echo $_N['target']?>"<?php endif?> class="news-banner-click" id="<?php echo $_N['uid']?>"><?php echo $_N['adtext']?></a></div>
   <?php $_i++; endwhile?>

   <?php elseif($d['news']['banner_kind14']==3): //스크립트형이면?>
   <?php $_i=0; $_NCD = getDbArray($table['newsbanner'],$b14_sql,'*','gid','asc',0,1)?>
   <?php while($_N=db_fetch_array($_NCD)):?>
   <div class="ad-banner-side-script"><?php ?> <?php $banner_code_file = $g['path_module'].'news/upload/banner/code/'.$_N['uid'].'_code.php'; if(is_file($banner_code_file)) include $banner_code_file; ?>?></div>
   <?php $_i++; endwhile?>

   <?php endif?>

   <?php endif?>
   <!--사이드배너1-->

   <!--뉴스 슬라이드-->
   <?php if($d['news']['news_side_content_display_1'] || $d['news']['news_side_content_display_2']):?>

			   <!--콘텐츠 내용-->
               <div class="detail_tab" id="detail_tab">

			     <!--탭타이틀-->
 		         <div class="wrap_detail_tab" id="wrap_detail_tab">
			        <div class="inner">
				       <ul class="list_detail_tab">
					      <?php if($d['news']['news_side_content_display_1']):?> 
					      <li class="on" style="margin-right:5px;"><button type="button" class="tab_detail tab_1"><?php echo $d['news']['news_side_content_title_1']?></button></li>
						  <?php endif?>
					      <?php if($d['news']['news_side_content_display_2']):?>
					      <li class="<?php if(!$d['news']['news_side_content_display_2']):?>on<?php endif?>"  style="margin-right:0px;"><button type="button" class="tab_detail tab_2"><?php echo $d['news']['news_side_content_title_2']?></button></li>
						  <?php endif?>
					   </ul>
    		        </div>
    	         </div>
			     <!--탭타이틀-->

				 <!--  탭1 내용  -->
			     <?php include $g['path_module'].'news/upload/news/content/'.$r.'/pc/side1/'.$r.'_sidetab_1_news.php';?>
                 <?php if($d['news']['news_side_content_display_1']):?>                
    	         <div class="bg_box_shadow detail_tab_cont show"  style="height:<?php echo 84*count($SUID)?>px;">
    		        <div class="inner_border">
				       <!-- Editor를 통한 HTML 입력 영역 -->
				       <div class="cont_edit_view">

			           <!--탭 콘텐츠 썸네일형-->
                       <?php foreach ($SUID as $_N):?>			 
	                   <?php 
	                   if($_N['site']==$s) {
                      $cdata = $_N['cdata'];
	                  }else{
                      $cdata = "11";
	                  }
	                  ?>
				       <?php
				       if(preg_match('/MSIE/i',$browser)) { //익스 이면
                       $newsimg = $_N['eximg'];
				       }else{
                       $newsimg = $_N['newsimg'];
				       }
			           ?>

					   <?php if($newsimg):?>
			           <div class="tab_default_thumb">
                          <div class="thumb-box">
				             <a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><div class="thumb" style="background-image: url('<?php echo $newsimg?>');"></div></a>
				          </div>

                          <div class="desc">
                             <div class="st"><a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><?php echo getStrCut($_N['subject'],40,'...')?></a></div>
                             <div class="cont"><?php echo getStrCut(strip_tags($_N['news_content']),50,'...')?></div>
				          </div>

				          <div class="clearfix"></div>

			           </div>
					   <?php else:?>

			           <div class="tab_default_list">

						  <div class="desc" style="width:100%;">
                             <div class="st"><a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><?php echo getStrCut($_N['subject'],40,'...')?></a></div>
                             <div class="cont"><?php echo getStrCut(strip_tags($_N['news_content']),100,'...')?></div>
				          </div>

			           </div>

					   <?php endif?>
                       <?php endforeach?>
			           <!--탭 콘텐츠 썸네일형-->


					   </div>
				       <!-- //Editor를 통한 HTML 입력 영역 -->
                    </div>
    	         </div>
                 <?php endif?>
				 <!--  탭1 내용  -->

				 <!--  탭2 내용  -->
			     <?php include $g['path_module'].'news/upload/news/content/'.$r.'/pc/side1/'.$r.'_sidetab_2_news.php';?>
                 <?php if($d['news']['news_side_content_display_2']):?>                
    	         <div class="bg_box_shadow detail_tab_cont"  style="height:<?php echo 84*count($SUID)?>px;">
    		        <div class="inner_border">
				       <!-- Editor를 통한 HTML 입력 영역 -->
				       <div class="cont_edit_view">

			           <!--탭 콘텐츠 썸네일형-->
                       <?php foreach ($SUID as $_N):?>			 
	                   <?php 
	                   if($_N['site']==$s) {
                      $cdata = $_N['cdata'];
	                  }else{
                      $cdata = "11";
	                  }
	                  ?>
				       <?php
				       if(preg_match('/MSIE/i',$browser)) { //익스 이면
                       $newsimg = $_N['eximg'];
				       }else{
                       $newsimg = $_N['newsimg'];
				       }
			           ?>
					   <?php if($newsimg):?>
			           <div class="tab_default_thumb">
                          <div class="thumb-box">
				             <a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><div class="thumb" style="background-image: url('<?php echo $newsimg?>');"></div></a>
				          </div>

                          <div class="desc">
                             <div class="st"><a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><?php echo getStrCut($_N['subject'],40,'...')?></a></div>
                             <div class="cont"><?php echo getStrCut(strip_tags($_N['news_content']),50,'...')?></div>
				          </div>

				          <div class="clearfix"></div>

			           </div>
					   <?php else:?>

			           <div class="tab_default_list">

						  <div class="desc" style="width:100%;">
                             <div class="st"><a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><?php echo getStrCut($_N['subject'],40,'...')?></a></div>
                             <div class="cont"><?php echo getStrCut(strip_tags($_N['news_content']),100,'...')?></div>
				          </div>

			           </div>

					   <?php endif?>
                       <?php endforeach?>
			           <!--탭 콘텐츠 썸네일형-->


					   </div>
				       <!-- //Editor를 통한 HTML 입력 영역 -->
                    </div>
    	         </div>
                 <?php endif?>
				 <!--  탭2 내용  -->

               </div>
			   <!--콘텐츠 내용-->

   <?php endif?>
   <!--뉴스 슬라이드-->

   <!--콘텐츠 내용-->

   <!--콘텐츠 내용-->

   <!--사이드배너2-->
   <?php if($d['news']['news_side_banner_display_2']):?>

   <?php
   $b_today = date("Ymd");
   if($r=="s151712") {
   $b15_sql = 'site='.$s.' and position=15 and auth=2 and device=1';
   $b15_sql .= ' and d_start<='.$b_today.' and d_finish>='.$b_today;
   }else{
   $b15_sql = 'site='.$s.' and position=15 and auth=2 and device=1';
   }
   ?>

   <?php if($d['news']['banner_kind15']==1): //이미지형이면?>
   <div class="ad-banner-side">
      <div class="wrap_ad side_banner_2">
         <ul class="list_ad">
			<?php $_i=0;  $_NCD  = getDbSelect($table['newsbanner'],$b15_sql.' order by rand() limit 8','*');?>
            <?php while($_N=db_fetch_array($_NCD)):?>
			<li data-toggle="tooltip" title="<?php echo $_N['name']?>"><a href="<?php echo $_N['url'] ? $_N['url'] : '#.'?>" <?php if($_N['target']):?>target="<?php echo $_N['target']?>"<?php endif?>><img src="<?php echo getBannerImage($_N['upload'])?>" alt="<?php echo $_N['name']?>" width="275" class="news-banner-click" id="<?php echo $_N['uid']?>"></a></li>
			<?php $_i++; endwhile?>
	     </ul>

		 <div class="btn_page hide">
		 <a href="#." class="prev"><img src="<?php echo $g['img_layout']?>/btn_ad_arrow_prev.png"></a>
		 <a href="#." class="next"><img src="<?php echo $g['img_layout']?>/btn_ad_arrow_prev.png"></a>
	     </div>

	  </div>

   </div>

   <?php elseif($d['news']['banner_kind15']==2): //텍스트형이면?>
   <?php $_i=0; $_NCD = getDbArray($table['newsbanner'],$b15_sql,'*','gid','asc',0,1)?>
   <?php while($_N=db_fetch_array($_NCD)):?>
   <div class="ad-banner-side-txt"><a href="<?php echo $_N['url'] ? $_N['url'] : '#.'?>" <?php if($_N['target']):?>target="<?php echo $_N['target']?>"<?php endif?> class="news-banner-click" id="<?php echo $_N['uid']?>"><?php echo $_N['adtext']?></a></div>
   <?php $_i++; endwhile?>

   <?php elseif($d['news']['banner_kind15']==3): //스크립트형이면?>
   <?php $_i=0; $_NCD = getDbArray($table['newsbanner'],$b15_sql,'*','gid','asc',0,1)?>
   <?php while($_N=db_fetch_array($_NCD)):?>
   <div class="ad-banner-side-script"><?php ?> <?php $banner_code_file = $g['path_module'].'news/upload/banner/code/'.$_N['uid'].'_code.php'; if(is_file($banner_code_file)) include $banner_code_file; ?>?></div>
   <?php $_i++; endwhile?>

   <?php endif?>

   <?php endif?>
   <!--사이드배너2-->

   <?php if($d['news']['news_side_title_display_3']):?>
   <div class="title-box">
      <div class="text"><a href="#."><?php echo $d['news']['news_side_content_title_3']?></a></div>
      <div class="more"><a href="<?php echo $g['s']?>/?r=<?php echo $r?>&c=<?php echo $d['news']['news_side_content_menu_3']?>" class="btn_more">더보기</a></div>
   </div>
   <?php endif?>

   <!--썸네일 타이틀 3단형-->
   <?php if($d['news']['news_side_content_display_3']):?>
   <div class="thumb_title_list_box">

		<?php include $g['path_module'].'news/upload/news/content/'.$r.'/pc/side1/'.$r.'_side_1_news.php';?>
        <?php foreach ($SUID as $_N):?>	 
				 
	    <?php 
	    if($_N['site']==$s) {
        $cdata = $_N['cdata'];
	    }else{
        $cdata = "11";
	    }
	  ?>
	  <?php
	  if(preg_match('/MSIE/i',$browser)) { //익스 이면
      $newsimg = $_N['eximg'];
	  }else{
      $newsimg = $_N['newsimg'];
	  }
	 ?>
	  <?php if($newsimg):?>
      <div class="thumb_title">
         <div class="tit-box">
            <a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><?php echo getStrCut($_N['subject'],40,'...')?></a>
		 </div>

         <div class="thumb-box">
		    <a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><div class="thumb" style="background-image: url('<?php echo $newsimg?>');"></div></a>
		 </div>

         <div class="desc">
            <div class="cont">
		    <?php echo getStrCut(strip_tags($_N['news_content']),50,'...')?>
		    </div>
		 </div>

	  </div>
	  <?php else: //이미지 없으면?>

      <div class="list_title">
         <div class="tit-box">
            <a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><?php echo getStrCut($_N['subject'],40,'...')?></a>
		 </div>

         <div class="desc">
            <div class="cont">
		    <?php echo getStrCut(strip_tags($_N['news_content']),50,'...')?>
		    </div>
		 </div>

	  </div>

	  <?php endif?>



	<?php endforeach?>

   </div>
   <?php endif?>
   <!--썸네일 타이틀 3단형-->

   <?php if($d['news']['news_side_title_display_4']):?>
   <div class="title-box">
      <div class="text"><a href="#."><?php echo $d['news']['news_side_content_title_4']?></a></div>
      <div class="more"><a href="<?php echo $g['s']?>/?r=<?php echo $r?>&c=<?php echo $d['news']['news_side_content_menu_4']?>" class="btn_more">더보기</a></div>
   </div>
   <?php endif?>

   <!--썸네일 타이틀 2단형-->
   <?php if($d['news']['news_side_title_display_4']):?>
   <div class="thumb_title_small_box">

		<?php include $g['path_module'].'news/upload/news/content/'.$r.'/pc/side1/'.$r.'_side_2_news.php';?>
        <?php foreach ($SUID as $_N):?>			 
				 
	    <?php 
	    if($_N['site']==$s) {
        $cdata = $_N['cdata'];
	    }else{
        $cdata = "11";
	    }
	  ?>
		<?php
		if(preg_match('/MSIE/i',$browser)) { //익스 이면
        $newsimg = $_N['eximg'];
		}else{
        $newsimg = $_N['newsimg'];
	    }
	  ?>
	  <?php if($newsimg):?>
      <div class="thumb_title_small">
         <div class="tit-box">
            <a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><?php echo getStrCut($_N['subject'],40,'...')?></a>
		 </div>

         <div class="thumb-box">
		    <a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><div class="thumb" style="background-image: url('<?php echo $newsimg?>');"></div></a>
	     </div>

         <div class="desc">
            <div class="cont">
			<?php echo getStrCut(strip_tags($_N['news_content']),50,'...')?>
			</div>
		 </div>

		 <div class="clearfix"></div>

	  </div>

	  <?php else: //이미지 없으면?>

      <div class="list_title_small">
         <div class="tit-box">
            <a href="<?php echo $_NEWS_VIEW.$_N['uid']?>"><?php echo getStrCut($_N['subject'],40,'...')?></a>
		 </div>

         <div class="desc">
            <div class="cont">
			<?php echo getStrCut(strip_tags($_N['news_content']),100,'...')?>
			</div>
		 </div>

	  </div>

	  <?php endif?>



      <?php endforeach?>


   </div>
   <?php endif?>
   <!--썸네일 타이틀 2단형-->

   <!--사이드배너3-->
   <?php if($d['news']['news_side_banner_display_3']):?>

   <?php
   $b_today = date("Ymd");
   if($r=="s151712") {
   $b16_sql = 'site='.$s.' and position=16 and auth=2 and device=1';
   $b16_sql .= ' and d_start<='.$b_today.' and d_finish>='.$b_today;
   }else{
   $b16_sql = 'site='.$s.' and position=16 and auth=2 and device=1';
   }
   ?>

   <?php if($d['news']['banner_kind16']==1): //이미지형이면?>
   <div class="ad-banner-side">
      <div class="wrap_ad side_banner_3">
         <ul class="list_ad">
			<?php $_i=0;  $_NCD  = getDbSelect($table['newsbanner'],$b16_sql.' order by rand() limit 8','*');?>
            <?php while($_N=db_fetch_array($_NCD)):?>
			<li data-toggle="tooltip" title="<?php echo $_N['name']?>"><a href="<?php echo $_N['url'] ? $_N['url'] : '#.'?>" <?php if($_N['target']):?>target="<?php echo $_N['target']?>"<?php endif?>><img src="<?php echo getBannerImage($_N['upload'])?>" alt="<?php echo $_N['name']?>" width="275" class="news-banner-click" id="<?php echo $_N['uid']?>"></a></li>
			<?php $_i++; endwhile?>
	     </ul>

		 <div class="btn_page hide">
		 <a href="#." class="prev"><img src="<?php echo $g['img_layout']?>/btn_ad_arrow_prev.png"></a>
		 <a href="#." class="next"><img src="<?php echo $g['img_layout']?>/btn_ad_arrow_prev.png"></a>
	     </div>

	  </div>

   </div>

   <?php elseif($d['news']['banner_kind16']==2): //텍스트형이면?>
   <?php $_i=0; $_NCD = getDbArray($table['newsbanner'],$b16_sql,'*','gid','asc',0,1)?>
   <?php while($_N=db_fetch_array($_NCD)):?>
   <div class="ad-banner-side-txt"><a href="<?php echo $_N['url'] ? $_N['url'] : '#.'?>" <?php if($_N['target']):?>target="<?php echo $_N['target']?>"<?php endif?> class="news-banner-click" id="<?php echo $_N['uid']?>"><?php echo $_N['adtext']?></a></div>
   <?php $_i++; endwhile?>

   <?php elseif($d['news']['banner_kind16']==3): //스크립트형이면?>
   <?php $_i=0; $_NCD = getDbArray($table['newsbanner'],$b16_sql,'*','gid','asc',0,1)?>
   <?php while($_N=db_fetch_array($_NCD)):?>
   <div class="ad-banner-side-script"><?php ?> <?php $banner_code_file = $g['path_module'].'news/upload/banner/code/'.$_N['uid'].'_code.php'; if(is_file($banner_code_file)) include $banner_code_file; ?>?></div>
   <?php $_i++; endwhile?>

   <?php endif?>

   <?php endif?>
   <!--사이드배너3-->

</div>
<!--콘텐츠-->


