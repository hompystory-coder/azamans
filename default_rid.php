<div class="sub_box_content">

   <?php if($d['news']['banner_kind11']==1): //이미지형이면?>
   <div class="ad-banner-sub">
      <div class="wrap_ad sub_banner_1">
         <ul class="list_ad">
            <?php $_i=0; $NCD = getDbArray($table['newsbanner'],'site='.$s.' and position=11 and auth=2 and device=1','*','gid','asc',0,1)?>
            <?php while($N=db_fetch_array($NCD)):?>
			<li data-toggle="tooltip" title="<?php echo $N['name']?>"><a href="<?php echo $N['url'] ? $N['url'] : '#.'?>" <?php if($N['target']=="_blank"):?>target="_blank<?php endif?>"><img src="<?php echo $g['s'].'/modules/news/upload/banner/'.$N['upload']?>" alt="" title="" width="898" height="150" class="news-banner-stat" id="<?php echo $N['uid']?>"></a></li>
			<?php $_i++; endwhile?>
	     </ul>

	  </div>

   </div>

   <?php elseif($d['news']['banner_kind11']==2): //텍스트형이면?>
   <?php $_i=0; $NCD = getDbArray($table['newsbanner'],'site='.$s.' and position=11 and auth=2 and device=1','*','gid','asc',0,1)?>
   <?php while($N=db_fetch_array($NCD)):?>
   <div class="ad-banner-side-txt"><a href="<?php echo $N['url'] ? $N['url'] : '#.'?>" class="news-banner-stat" id="<?php echo $N['uid']?>"><?php echo $N['adtext']?></a></div>
   <?php $_i++; endwhile?>

   <?php elseif($d['news']['banner_kind11']==3): //스크립트형이면?>
   <?php $_i=0; $NCD = getDbArray($table['newsbanner'],'site='.$s.' and position=11 and auth=2 and device=1','*','gid','asc',0,1)?>
   <?php while($N=db_fetch_array($NCD)):?>
   <div class="ad-banner-side-script"><?php $banner_code_file = $g['path_module'].'news/upload/banner/code/'.$N['uid'].'_code.php'; if(is_file($banner_code_file)) include $banner_code_file; ?></div>
   <?php $_i++; endwhile?>

   <?php endif?>
   <!--서브배너1-->

   <!--정렬영역-->
   <div class="sort_zone">
      <ul id="sort_code" class="sort">
	     <li class="sort_name">
	        <span class="srt">
               <span class="badge badge-default"><?php echo number_format($NUM)?>개 (<span class="page-num" id="page-num"><?php echo $p ? $p : 1?></span>/<?php echo $TPG?> 페이지)</span>
	        </span>
	     </li>

	     <li class="sort_search">
		    <!-- 통합검색 -->
		    <fieldset class="ht-search">
	        <form  action="<?php echo $g['s']?>/" method="get" name="searchForm" id="searchForm">
		    <input type="hidden" name="r" value="<?php echo $r?>">
		    <input type="hidden" name="m" value="<?php echo $m?>">
		    <input type="hidden" name="c" value="<?php echo $c?>">
		    <input type="hidden" name="mod" value="search">
		    <input type="hidden" name="style" value="<?php echo $style?>" id="style-select">
		    <input type="hidden" name="sort" value="<?php echo $sort?>">
		    <input type="hidden" name="orderby" value="<?php echo $orderby?>">
		    <input type="hidden" name="recnum" value="<?php echo $recnum?>">
		    <input type="hidden" name="p" value="<?php echo $p?>">
		    <input type="hidden" name="id" value="<?php echo $id?>">
		    <input type="hidden" name="where" value="subject">
		    <input type="text" name="gkey" id="skey" value="<?php echo stripslashes($gkey)?>" placeholder="검색 할 내용을 입력하세요.">
		    <button type="submit" id="submitButton">기사검색</button>
		    </form>
		    <!---
		    <dl>
		       <dt>인기검색어</dt>
			   <dd>
			   <a href="#.">알쓸</a>, <a href="#.">평창</a>, <a href="#.">알쓸다정</a>							
			   </dd>
		    </dl>
		    --->
		   </fieldset>
		   <!-- 통합검색 -->
		   <a href="#." onclick="getReset('<?php echo $g['s']?>/?r=<?php echo $r?>&m=<?php echo $m?>&mod=<?php echo $mod?>&id=<?php echo $id?>')" class="btn btn-default btn-sm" style="position:relative;top:10px;margin-left:10px;">초기화</a>
		 </li>

	     <li class="sort_code">
		 <a href="#." class="<?php if($style=="list"):?>selected<?php endif?>" id="list-style" onclick="getNewsStyle('list', 'news-box');">리스트형</a>
		 <span class="dot"><img src="<?php echo $g['img_module_skin']?>/cat_vline.png"></span>
		 <a href="#." class="<?php if($style=="thumb"):?>selected<?php endif?>" id="thumb-style" onclick="getNewsStyle('thumb', 'news-box');">썸네일형</a>
		 </li>

      </ul>
      
   </div>
   <!--정렬영역-->

	<!--뉴스 리스트-->
	<div class="content-box" id="news-box">

       <?php foreach($NEWSLIST as $R):?>

	   <?php
		//$news_content = news_blog_data ('../../../../../../../', $R['uid'], $R['account'], $R['d_regis']);
        $hcontent = strip_tags($R['content']);
        $dcontent = str_replace('&nbsp;', '', $hcontent);				
		?>
	   <?php 
	   if(preg_match('/MSIE/i',$browser)) { //익스 이면
       $newsimg = $R['eximg'];
	   }else{
       $newsimg = $R['newsimg'];
	   }
	   ?>
	   <?php 
	   if($R['site']==$s) {
       $cdata = $c;
	   }else{
       $cdata = "11";
	   }
	   ?>
	   
	   <?php 
	   //그룹확인
	   $_MGN = getDbData($table['s_mbrdata'],"site='".$R['site']."' and id='".$R['id']."'",'*');
			  
	   //rb_s070342_mbrgroup  '코리안포털뉴스', 386, 's070342',
	   $_CG =  getUidData("rb_".$r."_mbrgroup",$_MGN['mygroup']);			  
	   //그룹확인
	   ?>	 	   

       <?php if($newsimg):?>
	   <!--썸네일 기본형-->
	   <div class="default_thumb" id="news-post-<?php echo $R['uid']?>">
          <div class="thumb-box">
	      <a href="<?php echo $NEWS_VIEW.$R['uid']?>"><div class="thumb" style="background-image: url('<?php echo $newsimg?>');"></div></a>
	      </div>

          <div class="desc">
             <div class="st"><a href="<?php echo $NEWS_VIEW.$R['uid']?>"><?php echo getStrCut($R['subject'],80,'...')?></a></div>
             <div class="cont"><a href="<?php echo $NEWS_VIEW.$R['uid']?>"><?php echo getStrCut($dcontent,150,'...')?></a></div>

			 <?php if($R['site']==325 || $R['site']==396 || $R['site']==383 || $R['site']==383 || $R['site']==389 || $R['site']==416 || $R['site']==400 || $R['site']==447 || $R['site']==432 || $R['site']==435 || $R['site']==463 || $R['site']==464): //경기농촌관광신문 //misik.net ////heartdayrest.com?>
			 		
			 <?php 
			 //rb_s194702_mbrgroup
			 //$M = getDbData($table['s_mbrdata'],"id='".$R['id']."'",'*');
			 $M = getDbData($table['s_mbrdata'],"id='".$R['id']."'",'*');
			 $G = getUidData('rb_'.$r.'_mbrgroup',$M['mygroup']);
			 ?>						
             <div class="ninfo"><?php echo getDateFormat($R['d_regis'],"Y-m-d H:i:s")?> / <?php echo $R['name']?> <?php echo $G['name']?></div>						
						
			 <?php else:?>	
			 <div class="ninfo"><?php echo getDateFormat($R['d_regis'],"Y-m-d H:i:s")?> / <?php echo $R['name']?><?php if($R['site']==$s && $R['site']=="365")://디지털배움뉴스?> <?php echo $_CG['name']?><?php else:?>기자<?php endif?></div>
			 <?php endif?>
	      </div>

	      <div class="clearfix"></div>

	   </div>
	   <!--썸네일 기본형-->

	   <?php else:?>

	   <!--리스트 기본형-->
       <div class="default_list" id="news-post-<?php echo $R['uid']?>">
          <div class="st">
	         <a href="<?php echo $NEWS_VIEW.$R['uid']?>"><?php echo getStrCut($R['subject'],40,'...')?></a>
          </div>

          <div class="desc">
	         <a href="<?php echo $NEWS_VIEW.$R['uid']?>"><?php echo getStrCut($dcontent,250,'...')?></a>

			 <?php if($R['site']==325 || $R['site']==396 || $R['site']==383 || $R['site']==383 || $R['site']==389 || $R['site']==416 || $R['site']==400 || $R['site']==447 || $R['site']==432 || $R['site']==435 || $R['site']==463 || $R['site']==464): //경기농촌관광신문 //misik.net ////heartdayrest.com?>
			 		
			 <?php 
			 //rb_s194702_mbrgroup
			 //$M = getDbData($table['s_mbrdata'],"id='".$R['id']."'",'*');
			 $M = getDbData($table['s_mbrdata'],"id='".$R['id']."'",'*');
			 $G = getUidData('rb_'.$r.'_mbrgroup',$M['mygroup']);
			 ?>						
             <div class="ninfo"><?php echo getDateFormat($R['d_regis'],"Y-m-d H:i:s")?> / <?php echo $R['name']?> <?php echo $G['name']?></div>						
						
			 <?php else:?>	
			 <div class="ninfo"><?php echo getDateFormat($R['d_regis'],"Y-m-d H:i:s")?> / <?php echo $R['name']?><?php if($R['site'] == $s && $R['site']=="365")://디지털배움뉴스?> <?php echo $_CG['name']?><?php else:?>기자<?php endif?></div>
			 <?php endif?>
	      </div>
       </div>
       <!--리스트 기본형-->

	   <?php endif?>

       <?php endforeach?>

	   <?php if(!$NUM):?>
       <div class="none"><i class="fa fa-file-text-o" aria-hidden="true"></i> 등록된 뉴스가 없습니다.</div>
	   <?php endif?>

	</div>
	<!--뉴스 리스트-->

 </div>
<!--콘텐츠-->


