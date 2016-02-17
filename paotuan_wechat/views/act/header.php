<?php use common\component\CustomHelper;?>
<ul data-am-widget="gallery" class="am-gallery am-gallery-overlay" data-am-gallery="{ pureview: {target:'a'} }" style="padding:0">
  <li  style="padding:0">
 <div class="am-gallery-item">
 <a href="<?php if(empty($act->act_image)):?>/image/paotuanzhuce/top.png<?php else:?><?=CustomHelper::CreateImageUrl($act->act_image)?><?php endif;?>">
        <img src="<?php if(empty($act->act_image)):?>/image/paotuanzhuce/top.png<?php else:?><?=CustomHelper::CreateImageUrl($act->act_image, "big640")?><?php endif;?>" />
        <h3 class="am-gallery-title"><?=$act->act_name?></h3>
        </a>
    </div>
    </li>
</ul>