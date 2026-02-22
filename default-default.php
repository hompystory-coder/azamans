    <!--content-->
    <div class="container-fluid<?php if($g['mobile'] && $pcmode != "Y"):?> mpad<?php endif?>" <?php if(!$g['mobile']):?>style="background:<?php if($d['layout']['sub_bg_display']):?><?php echo $d['layout']['sub_bg_color']?>;<?php endif?> <?php if($d['layout']['sub_bg_img_display']):?> url(<?php echo $g['path_var']?>site/bg/<?php echo $d['layout']['sub_bg_img']?>) <?php echo $d['layout']['sub_bg_style']?> 0 0;<?php endif?>padding-top:<?php echo $d['layout']['content_padding_top']?>px; padding-left:<?php echo $d['layout']['content_padding_left']?>px; padding-bottom:<?php echo $d['layout']['content_padding_bottom']?>px; padding-right:<?php echo $d['layout']['content_padding_right']?>px;margin-top:<?php echo $d['layout']['content_margin_top']?>px;"<?php endif?>>

    <!--콘텐츠 영역-->
    <div id="content-main" class="container<?php if($g['mobile'] && $pcmode != "Y"):?> mpad<?php endif?>" <?php if(!$g['mobile']):?>style="background:<?php if($d['layout']['content_bg_display']):?><?php echo $d['layout']['content_bg_color']?>;<?php endif?> <?php if($d['layout']['content_bg_img_display']):?> url(<?php echo $g['path_var']?>site/bg/<?php echo $d['layout']['content_bg_img']?>) <?php echo $d['layout']['content_bg_style']?> 0 0;<?php endif?>padding-top:<?php echo $d['layout']['content_padding_top']?>px; padding-left:<?php echo $d['layout']['content_padding_left']?>px; padding-bottom:<?php echo $d['layout']['content_padding_bottom']?>px; padding-right:<?php echo $d['layout']['content_padding_right']?>px;margin-top:<?php echo $d['layout']['content_margin_top']?>px;"<?php endif?>>

     <?php if($g['mobile'] && $pcmode != "Y" || $m=="search" || $mobilepreview=="Y"):?>
       <?php include __KIMS_CONTENT__ ?>

     <?php else: //모바일 아니면?>
	 <div class="main-content-box">

     <?php if($d['layout']['side_display']==1 && $m!="jinjjayo"): //사이드 사용할시 && 진자요 아닐시?>

      <!--왼쪽메뉴-->
      <?php if($d['layout']['side_position']=='left'):?>
	  <div class="left-side-box"><?php $layout_file = $g['dir_layout'].'/_includes/_side.php'; if(is_file($layout_file)) include $layout_file; ?></div>
	  <div class="right-content-box">
      <?php $layout_file = $g['dir_layout'].'/_pages/right_content_top.php'; if(is_file($layout_file)) include $layout_file; ?>
	  <?php if($m=="bskrbbs"):?>
      <?php $layout_file = $g['dir_layout'].'/_pages/location.php'; if(is_file($layout_file)) include $layout_file; ?>
	  <?php endif?>
	  <?php include __KIMS_CONTENT__ ?>
	  </div>

	  <div class="clearfix"></div>
	  <?php endif?>
	  <!--왼쪽메뉴-->

      <!--오른쪽메뉴-->
      <?php if($d['layout']['side_position']=='center'):?>
      <?php $layout_file = $g['dir_layout'].'/_pages/center_content_top.php'; if(is_file($layout_file)) include $layout_file; ?>
	  <?php if($m=="bskrbbs"):?>
      <?php $layout_file = $g['dir_layout'].'/_pages/location.php'; if(is_file($layout_file)) include $layout_file; ?>
	  <?php endif?>
	  <?php include __KIMS_CONTENT__ ?>
	  <?php endif?>
	  <!--오른쪽메뉴-->

      <!--오른쪽메뉴-->
      <?php if($d['layout']['side_position']=='right'):?>
	  <div class="left-content-box">
      <?php $layout_file = $g['dir_layout'].'/_pages/left_content_top.php'; if(is_file($layout_file)) include $layout_file; ?>
	  <?php if($m=="bskrbbs"):?>
      <?php $layout_file = $g['dir_layout'].'/_pages/location.php'; if(is_file($layout_file)) include $layout_file; ?>
	  <?php endif?>
	  <?php include __KIMS_CONTENT__ ?>
	  </div>

	  <div class="right-side-box"><?php $layout_file = $g['dir_layout'].'/_includes/_side.php'; if(is_file($layout_file)) include $layout_file; ?></div>

	  <div class="clearfix"></div>
	  <?php endif?>
	  <!--오른쪽메뉴-->

	 <?php else: //사이드 사용안할시?>
     <?php include __KIMS_CONTENT__ ?>
     <?php endif?>

     </div>
     <?php endif //모바일 아니면?>
     
     </div>
     <!--콘텐츠 영역-->

	</div><!--container-fluid-->

    <!--content-->