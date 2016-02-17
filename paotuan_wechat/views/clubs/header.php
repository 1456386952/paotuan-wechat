<?php use common\component\CustomHelper;?>
<!--  
<header class="stepTop" style="background-image:<?php if(empty($club->club_bgimage)):?>url(/image/paotuanzhuce/top.png)<?php else:?>url(<?=CustomHelper::CreateImageUrl($club->club_bgimage, "big640")?>)<?php endif;?>;">
		
		<div class="topInfo">
		
		<div class="am-center userPhoto"  style="background-image:url(<?=CustomHelper::CreateImageUrl($club->club_logo, "small80")?>);display:inline-block;"></div>
		   <div class="userName"><?=$club->club_name?></div>
		   <div class="prodesc"><?=$club->club_slogan?></div>
		</div>
		
		
</header>
-->
<ul data-am-widget="gallery" class="am-gallery am-gallery-overlay" data-am-gallery="{ pureview: {target:'a'} }" style="padding:0;margin: 0">
  <li  style="padding:0">
 <div class="am-gallery-item">
 <a href="<?php if(empty($club->club_bgimage)):?>/image/paotuanzhuce/top.png<?php else:?><?=CustomHelper::CreateImageUrl($club->club_bgimage)?><?php endif;?>">
        <img src="<?php if(empty($club->club_bgimage)):?>/image/paotuanzhuce/top.png<?php else:?><?=CustomHelper::CreateImageUrl($club->club_bgimage)?><?php endif;?>"/>
        <?php if(!empty(trim($club->club_slogan))):?>
        <h3 class="am-gallery-title"><?=$club->club_slogan?></h3>
        <?php endif;?>
        </a>
    </div>
    </li>
</ul>