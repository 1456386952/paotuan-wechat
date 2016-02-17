 
  <?php 
  use common\component\CustomHelper;
  foreach ($data as $act):?>
      <!--缩略图在标题左边-->
      <li class="am-g am-list-item-desced am-list-item-thumbed am-list-item-thumb-left am-g-fixed">
        <div class="am-u-sm-4 am-list-thumb">
        <a>
            <img src="<?=CustomHelper::CreateImageUrl($act->act_logo, 'small80')?>">
            </a>
        </div>
        <div class=" am-u-sm-8 am-list-main">
          <h3 class="am-list-item-hd" style="">
            <a href="" class=""><?=$act->act_name?></a>
          </h3>
          <div class="am-list-item-text">
           <span class="am-icon-map-marker"></span> 
           <?php if(isset($act->country)&&!empty($act->country->country_logo)):?>
           <img src="<?=CustomHelper::CreateImageUrl($act->country->country_logo, 'img_logo')?>" style="width:30px"/>
          <?php endif;?>
          <?php if(isset($act->city)):?>
           <?=$act->city->chn_name?>
          <?php endif;?>
          <span class="am-icon-calendar" style="margin-left: 4px"></span>  <?=date('Y.m.d', strtotime($act->act_day))?>
          </div>
          </div>
      </li>
     <?php endforeach;?>