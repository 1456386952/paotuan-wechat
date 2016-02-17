<?php
use yii\helpers\ArrayHelper;
use paotuan_wechat\component\Util;
use paotuan_wechat\models\ClubMember;
use paotuan_wechat\models\ClubConfig;
use common\component\CustomHelper;
$configs = $club->configs;
$cons = array();
foreach($configs as $config){
	$cons[$config->col_name] =$config; 
}
?>

<script type="text/javascript">

function fileUpload(file,type){
	if(file.value==""||!checkImg(file))return;
	$("#uploadForm").empty();
	$("#uploadForm").append(file);
    $("#uploadForm").append("<input type='hidden' name='img_type' value='"+file.id+"'/>");
    $("#uploadForm").submit();
    var fileClone = $(file).clone();
	 fileClone.removeAttr("required");
	$("#"+file.id+"_div").append(fileClone);
    $("#modal-loading").modal();
}

function checkImg(file){
	var ex = file.value.substring(file.value.lastIndexOf(".")+1);
	var img =["jpg","jpeg","png","gif","bmp"];
	if($.inArray(ex.toLowerCase(),img)==-1){
		var fileClone = $(file).clone();
		$(file).remove();
		$("#"+file.id+"_div").append(fileClone);
		 $("#alert-content").text("文件类型错误，请选择图片");
    	 $("#appleAlert").modal();
    	 return false;
		}
	return true;
}

function upLoad(){
	var r = $("#upFrame").contents().find("#uploadResult");
	if(r.length==1){
		  var result = $.parseJSON(r.text());
		  if(result.status==1){
			     var id = result.id;
			     $("#"+id+"_pre").attr("src","http://xiaoi.b0.upaiyun.com/"+result.image);
			     $("#"+id+"_pre").show();
			     $("#"+id+"_value").val(result.image);
			     $("#modal-loading").modal("close");
			  }else{
				  $("#alert-content").text("文件上传错误,请稍候重试");
    		    	 $("#appleAlert").modal();
				  }
		}else{
	        $("#modal-loading").modal("close");
		}
}

function checkCell(){
   var oldCell =  $.trim($("#cell_old").val());
   var cell = $.trim($("#cell").val());
	if(oldCell!=null&&cell!=null&&cell!=oldCell){
		 $("#reg-code_div").show();
		 if($.trim($("#reg-code").val())==""){
			 $("#reg-code").focus();
			 }
	}else{
		$("#reg-code_div").hide();
		}
}


</script>


  <section data-am-widget="accordion" class="am-accordion am-accordion-gapped" id="status_sec"
data-am-accordion='{  }'>
   <div class="am-panel am-panel-default" style="border:none">
<div class="am-panel-hd">
      <h4 class="am-panel-title" data-am-collapse="{target: '#status-1'}">
                            状态:
                            <?php if(!$new):?>
                            <span id="status_desc" style="font-weight: normal;">
                            <?php if($member["member_status"]==ClubMember::STATUS_RE):?>
 									不通过
							<?php elseif($member["member_status"]==ClubMember::STATUS_WAIT):?>
									 待审核
							<?php elseif($member["member_status"]==ClubMember::STATUS_PAY):?>
                                                                                                                             待支付
                            <?php elseif($member["member_status"]==ClubMember::STATUS_NORMAL):?>
									已确认      
							<?php elseif($member["member_status"]==ClubMember::STATUS_SIMPLE):?>
									一般会员          
							<?php elseif($member["member_status"]==ClubMember::STATUS_PAY_WAIT_FOR_DONE):?>
									支付确认中                                                                     
        					<?php endif;?>
        					</span>
        					<?php else:?>
        					 <font color="red">未加入</font>  <span class="am-badge am-badge-success am-fr" style="color:#fff;padding:0 14px 0 14px" onclick="checkRegister();">加入</span>
        					<?php endif;?>
        					
      </h4>
    </div>
    
     <?php if($member["member_status"]==ClubMember::STATUS_NORMAL&&$club->member_fee>0):?>
    <div id="status-1" class="am-panel-collapse am-collapse am-in">
      <div class="am-panel-bd">
      <div class="am-g am-g-fixed">
         <div class="am-u-sm-5">缴费日期:</div>
         <div class="am-u-sm-7">
           <?=$newlypay->trade_time?>
         </div>
       </div>
      </div>
    </div>
    <?php endif;?>
    
    <?php if($member["member_status"]==ClubMember::STATUS_PAY):?>
    <div id="status-1" class="am-panel-collapse am-collapse am-in">
      <div class="am-panel-bd">
      <div class="am-g am-g-fixed">
        <?php if($newlypay!=null):?>
         <div class="am-u-sm-5">
                                           上次缴费日期:
         </div>
         <div class="am-u-sm-7">
               <?=$newlypay->trade_time?>
         </div>
        <?php endif;?>
         <div class="am-u-sm-7"><?php if($club->fee_circle==1):?>
        <label class="am-radio am-secondary">
        <input type="radio" id="fee_circle" name="fee_circle" value="<?=$club->fee_circle?>" data-am-ucheck checked="checked">年费&nbsp;￥<?=$club->member_fee?>元
         </label>
         <?php elseif($club->fee_circle==2):?>
        <label class="am-radio am-secondary">
        <input type="radio" id="fee_circle" name="fee_circle" value="<?=$club->fee_circle?>" data-am-ucheck checked="checked"checked="checked">季费&nbsp;￥<?=$club->member_fee?>元
         </label>
         <?php elseif($club->fee_circle==3):?>
        <label class="am-radio am-secondary">
        <input type="radio" id="fee_circle" name="fee_circle" value="<?=$club->fee_circle?>" data-am-ucheck checked="checked">月费&nbsp;￥<?=$club->member_fee?>元
         </label>
         <?php elseif($club->fee_circle==4):?>
        <label class="am-radio am-secondary">
        <input type="radio" id="fee_circle" name="fee_circle" value="<?=$club->fee_circle?>" data-am-ucheck checked="checked">一次性费用&nbsp;￥<?=$club->member_fee?>元
         </label>
      <?php endif;?></div>
         <div class="am-u-sm-5">
           <button class="am-btn am-btn-success am-btn-sm" onclick="payInNewPage();">&nbsp;支&nbsp;付&nbsp;</button>
         </div>
      </div>
          
      
      </div>
    </div>
    <?php endif;?>
  </div>
</section>


<div  id="reg" style="display:none">
     <section data-am-widget="accordion" class="am-accordion am-accordion-gapped"
data-am-accordion='{  }'>
  <div class="am-alert am-alert-warning" data-am-alert id="msg" style="display: none">
</div>
</section>
      <form class="am-form"  style="margin-top:10px;" id="registerForm"   method="post" data-am-validator>
  <input type="hidden" name="ClubMember[openid]" id="openid" value="<?=$openid?>"/>
<input type="hidden" name="ClubMember[uid]" value="<?=$user->uid?>" id="uid">
<input type="hidden" name="ClubMember[club_id]" value="<?=$club->clubid?>">
<input type="hidden" name="id" value="<?=$member["id"]?>" id="member_id">
<section data-am-widget="accordion" class="am-accordion am-accordion-gapped"
data-am-accordion='{  }'>
  <div class="am-panel am-panel-default" style="border:none" id="base-panel">
<div class="am-panel-hd">
      <h4 class="am-panel-title" data-am-collapse="{target: '#base-1'}">
                            基本信息 
      </h4>
    </div>
    <div id="base-1" class="am-panel-collapse am-collapse am-in">
      <div class="am-panel-bd" id="base-bd">
       <?php if(key_exists("passport_name", $cons)&&$cons["passport_name"]->visible==1):?>
        <div class="am-form-group">
                <label  for="name">姓名:<?php if($cons["passport_name"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
            <input id="name"  <?php if($cons["passport_name"]->optional==1):?>required<?php endif;?>  class="am-form-field am-radius" name="ClubMember[passport_name]" value="<?=$member["passport_name"]?>" placeholder="请填写您的姓名！" type="text">
            </div>
            <?php endif;?>
            
            <?php if(key_exists("cell", $cons)&&$cons["cell"]->visible==1):?>  
          <div class="am-form-group">
              <span class="am-help" id="regError">
                    </span>
                <label for="cell"> 手机:<?php if($cons["cell"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                <input type="hidden" value="<?=$member["cell"]?>" id="cell_old">
                    <input <?php if($cons["cell"]->optional==1):?>required<?php endif;?> id="cell" name="ClubMember[cell]" value="<?=$member["cell"]?>" class="am-form-field am-radius" placeholder="请填写您的手机号码！" type="number" pattern="^1[3|4|5|7|8][0-9]\d{4,8}$" onblur="checkCell();">                                                    
               </div>
               
               <div class="am-form-group" id="reg-code_div" style="display:none">
 
 <label for="reg-code">验证码:<span class="i-red-12"> *</span></label>
    <div class="am-input-group">
     <input type="number" class="am-form-field"  id="reg-code" name="code" placeholder="请输入验证码" value="" required  maxlength="6" style="width:9em">
        <button class="am-btn am-btn-danger" style="width:8em" type="button" onclick="getResCode();" id="veriCode">获取验证码</button>
    </div>
    </div>
            <?php endif;?>
            
              <?php if(key_exists("gender", $cons)&&$cons["gender"]->visible==1):?>  
            <div class="am-form-group">  
                <label for="gender">性别:<?php if($cons["gender"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?></label>
                    <select id="gender"  name="ClubMember[gender]" class="am-form-field am-radius">
                        <option value="1" <?php if($member["gender"]||$member["gender"]=="1"){echo "selected=\"selected\"";}?>>男</option>
                        <option value="2" <?php if($member["gender"]=="2"){echo "selected=\"selected\"";}?>>女</option>
                        <option value="3" <?php if($member["gender"]=="3"){echo "selected=\"selected\"";}?>>其他</option>
                    </select>
            </div>
            <?php endif;?>
            </div>
            </div></div>
            
            <div class="am-panel am-panel-default" style="border:none" id="bm-panel">
    <div class="am-panel-hd">
      <h4 class="am-panel-title" data-am-collapse="{target: '#bm-1'}">
                              报名信息
      </h4>
    </div>
    <div id="bm-1" class="am-panel-collapse am-collapse am-in">
      <div class="am-panel-bd" id="bm-bd">
      
         <?php if(key_exists("nationality", $cons)&&$cons["nationality"]->visible==1):?>
          <div class="am-form-group">
                <label  for="nationality">
                   <?=$cons["nationality"]->col_title ?>:<?php if($cons["nationality"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                    <input <?php if($cons["nationality"]->optional==1):?>required<?php endif;?> id="nationality" name="ClubMember[nationality]" value="<?=$member["nationality"]?>" placeholder="请填写您的国籍！" class="form-control input-xlarge i-check" type="text" >
            </div>
            
            <?php endif;?>
            
              <?php if(key_exists("city", $cons)&&$cons["city"]->visible==1):?>
             <div class="am-form-group">  
                <label class="control-label" for="city"> <?=$cons["city"]->col_title ?>:<?php if($cons["city"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?></label>
                    <input <?php if($cons["city"]->optional==1):?>required<?php endif;?> type="text" id="city" name="ClubMember[city]" class="am-form-field am-radius" value="<?=$member["city"]?>" placeholder="请填写来自的城市！">
            </div>
            <?php endif;?>
             <?php if(key_exists("birthday", $cons)&&$cons["birthday"]->visible==1):?>
            <div class="am-form-group">  
                <label for="birthday"><?=$cons["birthday"]->col_title ?>(如1985-01-01):<?php if($cons["birthday"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?></label>
                <input <?php if($cons["birthday"]->optional==1):?>required <?php endif;?> type="date" id="birthday" name="ClubMember[birthday]" class="am-form-field am-radius" value="<?=$member["birthday"]?>"  placeholder="请选择您的出生日期" />
            </div>
            <?php endif;?>
            
             <?php if(key_exists("id_type", $cons)&&$cons["id_type"]->visible==1):?>
              <div class="am-form-group">
                <label for="id_type">
                   <?=$cons["id_type"]->col_title ?>:<?php if($cons["id_type"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                    <select id="id_type" name="ClubMember[id_type]" class="am-form-field am-radius" <?php if($cons["id_type"]->optional==1):?>required<?php endif;?>>
                        <option value="1" <?php if($member["id_type"]=="1"){echo "selected=\"selected\"";}?>>身份证</option>
                        <option value="2" <?php if($member["id_type"]=="2"){echo "selected=\"selected\"";}?>>护照</option>
                        <option value="3" <?php if($member["id_type"]=="3"){echo "selected=\"selected\"";}?>>台胞证</option>
                        <option value="4" <?php if($member["id_type"]=="4"){echo "selected=\"selected\"";}?>>港澳通行证</option>
                        <option value="0" <?php if($member["id_type"]=="0"){echo "selected=\"selected\"";}?>>其它</option>
                    </select>
            </div>
            <?php endif;?>
            
             <?php if(key_exists("id_number", $cons)&&$cons["id_number"]->visible==1):?>
            <div class="am-form-group">
                <label for="id_number">
           <?=$cons["id_number"]->col_title ?>:<?php if($cons["id_number"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                    <input <?php if($cons["id_number"]->optional==1):?>required<?php endif;?> id="id_number" name="ClubMember[id_number]" value="<?=$member["id_number"]?>" class="am-form-field am-radius" placeholder="请正确输入您的相关证件号码，用于购买保险！" type="text">
            </div>  
            <?php endif;?>
            
             <?php if(key_exists("email", $cons)&&$cons["email"]->visible==1):?>
            <div class="am-form-group">
                <label for="email">
                    <?=$cons["email"]->col_title ?>:<?php if($cons["email"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                    <input <?php if($cons["email"]->optional==1):?>required<?php endif;?> id="email" name="ClubMember[email]" value="<?=$member["email"]?>" class="am-form-field am-radius" placeholder="请填写您的电子邮箱！" type="email">
            </div>
            <?php endif;?>
            
             <?php if(key_exists("address", $cons)&&$cons["address"]->visible==1):?>
            <div class="am-form-group">  
                <label  for="address"> <?=$cons["address"]->col_title ?>:<?php if($cons["address"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?></label>
                    <input <?php if($cons["address"]->optional==1):?>required<?php endif;?> type="text" id="address" name="ClubMember[address]" class="am-form-field am-radius" value="<?=$member["address"]?>" placeholder="请填写您的通讯地址！">
            </div>
            <?php endif;?>
          
             <?php if(key_exists("shirt_size", $cons)&&$cons["shirt_size"]->visible==1):?>
             <div class="am-form-group">
                <label for="shirt_size">
                <?=$cons["shirt_size"]->col_title ?>:<?php if($cons["shirt_size"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                    <select <?php if($cons["shirt_size"]->optional==1):?>required<?php endif;?> name="ClubMember[shirt_size]" id="shirt_size" class="am-form-field am-radius">
                        <option  value="XS" <?php if($member["shirt_size"]=="XS"){echo "selected=\"selected\"";}?>>XS</option>
                        <option  value="S" <?php if($member["shirt_size"]=="S"){echo "selected=\"selected\"";}?>>S</option>
                        <option value="M" <?php if($member["shirt_size"]=="M"){echo "selected=\"selected\"";}?>>M</option>
                        <option  value="L" <?php if($member["shirt_size"]=="L"){echo "selected=\"selected\"";}?>>L</option>
                        <option  value="XL" <?php if($member["shirt_size"]=="XL"){echo "selected=\"selected\"";}?>>XL</option>
                        <option  value="XXL" <?php if($member["shirt_size"]=="XXL"){echo "selected=\"selected\"";}?>>XXL</option>
                        <option  value="XXXL" <?php if($member["shirt_size"]=="XXXL"){echo "selected=\"selected\"";}?>>XXXL</option>
                    </select>
            </div>
            <?php endif;?>
            
             <?php if(key_exists("emerge_name", $cons)&&$cons["emerge_name"]->visible==1):?>
            <div class="am-form-group">  
                <label for="emerge_name"><?=$cons["emerge_name"]->col_title ?>:<?php if($cons["emerge_name"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?></label>
                    <input <?php if($cons["emerge_name"]->optional==1):?>required<?php endif;?> type="text" id="emerge_name" value="<?=$member["emerge_name"]?>" name="ClubMember[emerge_name]" placeholder="请输入您的紧急联系人姓名！" class="am-form-field am-radius">
            </div>
            <?php endif;?>
            
             <?php if(key_exists("emerge_ship", $cons)&&$cons["emerge_ship"]->visible==1):?>
             <div class="am-form-group">  
                <label for="emerge_ship"><?=$cons["emerge_ship"]->col_title ?>:<?php if($cons["emerge_ship"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?></label>
                    <input <?php if($cons["emerge_ship"]->optional==1):?>required<?php endif;?> type="text" class="am-form-field am-radius" value="<?=$member["emerge_ship"]?>" id="emergencyship" name="ClubMember[emerge_ship]" placeholder="请输入您与紧急联系人的关系！">
            </div>
            <?php endif;?>
            <?php if(key_exists("emerge_cell", $cons)&&$cons["emerge_cell"]->visible==1):?>
             <div class="am-form-group">  
                <label  for="emerge_cell">
                <?=$cons["emerge_cell"]->col_title ?>:<?php if($cons["emerge_cell"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                    <input <?php if($cons["emerge_cell"]->optional==1):?>required<?php endif;?> type="number" class="am-form-field am-radius" value="<?=$member["emerge_cell"]?>" id="emerge_cell" name="ClubMember[emerge_cell]" placeholder="请输入您的紧急联系人电话！">
            </div>
            <?php endif;?>
            
                  <?php if(key_exists("id_image", $cons)&&$cons["id_image"]->visible==1):?>
                    <div class="am-form-group am-form-file" id="id_image_div">
                    <br>
                    <label for="id_image"> <i class="am-icon-cloud-upload"></i>  <?=$cons["id_image"]->col_title ?><?php if($cons["id_image"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?></label>
                 <input type="hidden"  name="ClubMember[id_image]"  id="id_image_value" value="<?=$member["id_image"]?>">
                <input type="file" <?php if($cons["id_image"]->optional==1&&$member["id_image"]==null):?> required<?php endif;?> name="file[]" id="id_image" onchange="">
                 <img alt="" src="<?=CustomHelper::CreateImageUrl($member["id_image"],"small80")?>" id="id_image_pre" style="width:50px;" onerror="this.style.display='none'" >
            </div>
            <?php endif;?>
            
             <?php if(key_exists("id_image", $cons)&&$cons["id_image"]->visible==1):?>
                    <div class="am-form-group am-form-file" id="id_copy_back_div">
                    <br>
                <label  for="id_copy_back">
                <i class="am-icon-cloud-upload"></i> 身份证复印件反面:<?php if($cons["id_image"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                <input type="file" <?php if($cons["id_image"]->optional==1&&$member["id_copy_back"]==null):?> required<?php endif;?> name="file[]" id="id_copy_back" >
                <img alt="" src="<?=CustomHelper::CreateImageUrl($member["id_copy_back"],"small80")?>" id="id_copy_back_pre" style="width:50px;" onerror="this.style.display='none'" >
                <input type="hidden"  name="ClubMember[id_copy_back]"  id="id_copy_back_value" value="<?=$member["id_copy_back"]?>">
            </div>
            <?php endif;?>
            
      </div>
    </div>
  </div>
  
            
            
   <div class="am-panel am-panel-default" style="border:none" id="pt-panel">
<div class="am-panel-hd">
      <h4 class="am-panel-title" data-am-collapse="{target: '#pt-1'}">
                              跑团
      </h4>
    </div>
    <div id="pt-1" class="am-panel-collapse am-collapse am-in">
      <div class="am-panel-bd" id="pt-bd">
      
      <?php if(key_exists("run_age", $cons)&&$cons["run_age"]->visible==1):?>
<div class="am-form-group" id="run_age_div">  
                <label for="run_age">
                <?=$cons["run_age"]->col_title ?>:<?php if($cons["run_age"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
             <select id="run_age" <?php if($cons["run_age"]->optional==1):?>required<?php endif;?>  name="ClubMember[run_age]" placeholder="请输入您的跑龄！" class="am-form-field am-radius">
                <option value="一年以内" <?php if($member["run_age"]=="一年以内"){echo "selected=\"selected\"";}?>>一年以内</option>
                 <option value="1" <?php if($member["run_age"]=="1"){echo "selected=\"selected\"";}?>>1</option>
                  <option value="2" <?php if($member["run_age"]=="2"){echo "selected=\"selected\"";}?>>2</option>
                   <option value="3" <?php if($member["run_age"]=="3"){echo "selected=\"selected\"";}?>>3</option>
                    <option value="4" <?php if($member["run_age"]=="4"){echo "selected=\"selected\"";}?>>4</option>
                    <option value="5" <?php if($member["run_age"]=="5年或以上"){echo "selected=\"selected\"";}?>>5年或以上</option>
             </select>
            </div>
            <?php endif;?>
            
            <?php if(key_exists("join_day", $cons)&&$cons["join_day"]->visible==1):?>
              <div class="am-form-group" id="join_day_div">
                <label for="join_day">
                   <?=$cons["join_day"]->col_title ?>(如1985-01-01):<?php if($cons["join_day"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                    <input type="date" <?php if($cons["join_day"]->optional==1):?>required<?php endif;?> id="join_day" name="ClubMember[join_day]" class="am-form-field am-radius" value="<?=$member["join_day"]?>" placeholder="请选择您的入会日期！">
            </div>
            <?php endif;?>
            
            <?php if(key_exists("sub_group", $cons)&&$cons["sub_group"]->visible==1):?>
            <div class="am-form-group" id="sub_group_div">
                <label for="sub_group">
                   <?=$cons["sub_group"]->col_title ?>:<?php if($cons["sub_group"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                    <select id="sub_group" name="ClubMember[sub_group]" class="am-form-field am-radius" <?php if($cons["sub_group"]->optional==1):?>required<?php endif;?>>
                        <?php foreach($club->subs as $sub):?>
                            <?php if($sub->id==$member["sub_group"]):?>
                            <option  value="<?=$sub->id?>" selected="selected"><?=$sub->sub_name?></option>
                            <?php else:?>
                            <option  value="<?=$sub->id?>"><?=$sub->sub_name?></option>
                            <?php endif;?>
                        <?php endforeach;?>
                    </select>
                    </div>
                    <?php endif;?>
                    
           <?php if(key_exists("marathon_max_score", $cons)&&$cons["marathon_max_score"]->visible==1):?>
         <div class="am-form-group" id="marathon_max_score_div">
                <label for="marathon_max_score">
                    <?=$cons["marathon_max_score"]->col_title ?>:<?php if($cons["marathon_max_score"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                
                 <?php if($cons["marathon_max_score"]->col_type== ClubConfig::TYPE_TEXT):?>
                 <input type="text" <?php if($cons["marathon_max_score"]->optional==1):?>required<?php endif;?> id="marathon_max_score" name="ClubMember[marathon_max_score]" class="am-form-field am-radius" value="<?=$member["marathon_max_score"]?>" placeholder="" >
                   <?php elseif($cons["marathon_max_score"]->col_type== ClubConfig::TYPE_TIME):?>
                    <input type="time" <?php if($cons["marathon_max_score"]->optional==1):?>required<?php endif;?> id="marathon_max_score" name="ClubMember[marathon_max_score]" class="am-form-field am-radius" value="<?=$member["marathon_max_score"]?>" placeholder="" >
                   <?php  endif;?>
                   
                    </div>
                    <?php endif;?>
                    
                 <?php if(key_exists("half_marathon_max_score", $cons)&&$cons["half_marathon_max_score"]->visible==1):?>   
             <div class="am-form-group" id="half_marathon_max_score_div">
                <label for="half_marathon_max_score">
                     <?=$cons["half_marathon_max_score"]->col_title ?>:<?php if($cons["half_marathon_max_score"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                 <?php if($cons["half_marathon_max_score"]->col_type== ClubConfig::TYPE_TEXT):?>
                 <input <?php if($cons["half_marathon_max_score"]->optional==1):?>required<?php endif;?> type="text" id="half_marathon_max_score" name="ClubMember[half_marathon_max_score]" class="am-form-field am-radius" value="<?=$member["half_marathon_max_score"]?>" placeholder="" >
                  <?php elseif($cons["half_marathon_max_score"]->col_type== ClubConfig::TYPE_TIME):?>
                <input <?php if($cons["half_marathon_max_score"]->optional==1):?>required<?php endif;?> type="time" id="half_marathon_max_score" name="ClubMember[half_marathon_max_score]" class="am-form-field am-radius" value="<?=$member["half_marathon_max_score"]?>" placeholder="" >
                <?php endif;?>
                    </div>
                    <?php endif;?>
                    
                    <?php if(key_exists("ten_km_max_score", $cons)&&$cons["ten_km_max_score"]->visible==1):?>   
                    <div class="am-form-group" id="ten_km_max_score_div">
                <label for="ten_km_max_score">
                     <?=$cons["ten_km_max_score"]->col_title ?>:<?php if($cons["ten_km_max_score"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                 <?php if($cons["ten_km_max_score"]->col_type== ClubConfig::TYPE_TEXT):?>
                 <input type="text"  <?php if($cons["ten_km_max_score"]->optional==1):?>required<?php endif;?> id="ten_km_max_score" name="ClubMember[ten_km_max_score]" class="am-form-field am-radius" value="<?=$member["ten_km_max_score"]?>" placeholder="" >
                   <?php elseif($cons["ten_km_max_score"]->col_type== ClubConfig::TYPE_TIME):?>
                    <input type="time"  <?php if($cons["ten_km_max_score"]->optional==1):?>required<?php endif;?> id="ten_km_max_score" name="ClubMember[ten_km_max_score]" class="am-form-field am-radius" value="<?=$member["ten_km_max_score"]?>" placeholder="" >
                    <?php endif;?>
                    </div>
                <?php endif;?>
                    </div>
                    </div></div>
                    
 <div class="am-panel am-panel-default" style="border:none" id="other-panel">
<div class="am-panel-hd">
      <h4 class="am-panel-title" data-am-collapse="{target: '#other-1'}">
                              其他
      </h4>
    </div>
    <div id="other-1" class="am-panel-collapse am-collapse am-in">
      <div class="am-panel-bd" id="other-bd">
      <?php if(key_exists("blood_type", $cons)&&$cons["blood_type"]->visible==1):?>   
         <div class="am-form-group" id="blood_type_div">
                <label for="blood_type">
                  <?=$cons["blood_type"]->col_title ?>:<?php if($cons["blood_type"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                <select  id="blood_type" <?php if($cons["blood_type"]->optional==1):?>required<?php endif;?> name="ClubMember[blood_type]"  placeholder="请填写您的血型！" class="am-form-field am-radius">
                   <option value="A" <?php if($member["blood_type"]=="A"){echo "selected=\"selected\"";}?>>A</option>
                   <option value="B" <?php if($member["blood_type"]=="B"){echo "selected=\"selected\"";}?>>B</option>
                   <option value="AB" <?php if($member["blood_type"]=="AB"){echo "selected=\"selected\"";}?>>AB</option>
                   <option value="O" <?php if($member["blood_type"]=="O"){echo "selected=\"selected\"";}?>>O</option>
                   <option value="other" <?php if($member["blood_type"]=="other"){echo "selected=\"selected\"";}?>>其他</option>
                </select>
            </div>
            <?php endif;?>
            
             <?php if(key_exists("shoe_size", $cons)&&$cons["shoe_size"]->visible==1):?>  
             <div class="am-form-group" id="shoe_size_div">
                <label for="shoe_size">
                  <?=$cons["shoe_size"]->col_title ?>:<?php if($cons["shoe_size"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                <select <?php if($cons["shoe_size"]->optional==1):?>required<?php endif;?> id="shoe_size" name="ClubMember[shoe_size]"   class="am-form-field am-radius">
                <?php for($i=35;$i<=48;$i++):?>
                   <option value="<?=$i?>" <?php if($member["shoe_size"]==$i){echo "selected=\"selected\"";}?>><?=$i?></option>
                <?php endfor;?>
                </select>
            </div>
            <?php endif;?>
                    
                    <?php if(key_exists("health_check_certificate", $cons)&&$cons["health_check_certificate"]->visible==1):?>
                    <div class="am-form-group" id="health_check_certificate_div">
                <label  for="health_check_certificate">
                  <?=$cons["health_check_certificate"]->col_title ?>:<?php if($cons["health_check_certificate"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label><br>
                 <input type="hidden"  name="ClubMember[health_check_certificate]"  id="health_check_certificate_value" value=""<?=$member["health_check_certificate"]?>>
                <input type="file" <?php if($cons["health_check_certificate_value"]->optional==1&&$member["health_check_certificate"]==null):?>required<?php endif;?> name="file[]" id="health_check_certificate">
                 <img alt="" src="<?=CustomHelper::CreateImageUrl($member["health_check_certificate"],"small80")?>" id="health_check_certificate_pre" style="width:50px;height:50px;margin-top: 14px" onerror="this.style.display='none'" >
            </div>
            <?php endif;?>
            
               <?php if(key_exists("marathon_score_certificate", $cons)&&$cons["marathon_score_certificate"]->visible==1):?>
                    <div class="am-form-group" id="marathon_score_certificate_div">
                <label  for="marathon_score_certificate">
                  <?=$cons["marathon_score_certificate"]->col_title ?>:<?php if($cons["marathon_score_certificate"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label><br>
                <input type="hidden"  name="ClubMember[marathon_score_certificate]"  id="marathon_score_certificate_value" value="<?=$member["marathon_score_certificate"]?>">
                <input type="file" <?php if($cons["marathon_score_certificate"]->optional==1&&$member["marathon_score_certificate"]==null):?>required<?php endif;?> name="file[]" id="marathon_score_certificate" >
                 <img alt="" src="<?=CustomHelper::CreateImageUrl($member["marathon_score_certificate"],"small80")?>" id="marathon_score_certificate_pre" style="width:50px;height:50px;margin-top: 14px" onerror="this.style.display='none'" >
            </div>
            <?php endif;?>
            
            <?php if(key_exists("cross_race_score_certificate", $cons)&&$cons["cross_race_score_certificate"]->visible==1):?>
                    <div class="am-form-group" id="cross_race_score_certificate_div">
                <label  for="cross_race_score_certificate">
                  <?=$cons["cross_race_score_certificate"]->col_title ?>:<?php if($cons["cross_race_score_certificate"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label><br>
                 <input type="hidden"  name="ClubMember[cross_race_score_certificate]"  id="cross_race_score_certificate_value" value="<?=$member["cross_race_score_certificate"]?>">
                <input type="file" <?php if($cons["cross_race_score_certificate"]->optional==1&&$member["cross_race_score_certificate"]==null):?>required<?php endif;?> name="file[]" id="cross_race_score_certificate">
                 <img alt="" src="<?=CustomHelper::CreateImageUrl($member["cross_race_score_certificate"],"small80")?>" id="cross_race_score_certificate_pre" style="width:50px;height:50px;margin-top: 14px" onerror="this.style.display='none'" >
            </div>
            <?php endif;?>
            
                    <?php if(key_exists("height", $cons)&&$cons["height"]->visible==1):?>  
                     <div class="am-form-group" id="height_div">
                <label for="height">
              <?=$cons["height"]->col_title ?>:<?php if($cons["height"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                    <input  id="height" <?php if($cons["height"]->optional==1):?>required<?php endif;?>  name="ClubMember[height]" value="<?=$member["height"]?>" placeholder="请填写您的身高［cm］！" class="am-form-field am-radius" type="number">
            </div>
            <?php endif;?>
             <?php if(key_exists("weight", $cons)&&$cons["weight"]->visible==1):?>  
             <div class="am-form-group" id="weight_div">
                <label  for="weight">
                <?=$cons["weight"]->col_title ?>:<?php if($cons["weight"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                    <input type="number" id="weight" <?php if($cons["weight"]->optional==1):?>required<?php endif;?> name="ClubMember[weight]" value="<?=$member["weight"]?>" placeholder="请填写您的体重[kg]！" class="am-form-field am-radius" >
            </div>
            <?php endif;?>
            
             <?php if(key_exists("medical_history", $cons)&&$cons["medical_history"]->visible==1):?>  
            <div class="am-form-group" id="medical_history_div">
                <label  for="medical_history">
                     <?=$cons["medical_history"]->col_title ?>:<?php if($cons["medical_history"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                <textarea <?php if($cons["medical_history"]->optional==1):?>required<?php endif;?> rows="" cols="" id="medical_history" name="ClubMember[medical_history]"><?=$member["medical_history"]?></textarea>
            </div>
            <?php endif;?>
            
               <?php if(key_exists("company_info", $cons)&&$cons["company_info"]->visible==1):?>  
            <div class="am-form-group" id="company_info_div">
                <label  for="company_info">
                     <?=$cons["company_info"]->col_title ?>:<?php if($cons["company_info"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                <input id="company_info" <?php if($cons["company_info"]->optional==1):?>required<?php endif;?> name="ClubMember[company_info]" value="<?=$member["company_info"]?>"  class="am-form-field am-radius" type="text">
            </div>
            <?php endif;?>
            
             <?php if(key_exists("job_title", $cons)&&$cons["job_title"]->visible==1):?>  
            <div class="am-form-group" id="job_title_div">
                <label  for="job_title">
                     <?=$cons["job_title"]->col_title ?>:<?php if($cons["job_title"]->optional==1):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                <input id="job_title" <?php if($cons["job_title"]->optional==1):?>required<?php endif;?> name="ClubMember[job_title]" value="<?=$member["job_title"]?>"  class="am-form-field am-radius" type="text">
            </div>
            <?php endif;?>
            
             <?php for($i=1;$i<=8;$i++):?>
                  <?php if(array_key_exists("reserved_field_$i", $cons)&&$cons["reserved_field_$i"]->visible==1):?>
                  <?php 
                      $required = ($cons["reserved_field_$i"]->optional==1);
                     $values = str_replace("，", ",", $cons["reserved_field_$i"]->col_list_values);
                     $values = str_replace("`", ",", $values);
                     $values =explode(",", $values);
                  ?>
                   <div class="am-form-group" id="reserved_field_<?=$i?>_div">
                <label for="sub_group">
                 <?=$cons["reserved_field_$i"]->col_title ?>:<?php if($required):?><span class="i-red-12"> *</span><?php endif;?>
                </label>
                <?php if($cons["reserved_field_$i"]->col_type== ClubConfig::TYPE_TEXT):?>
                  <textarea <?php if($required):?>required<?php endif;?> rows="" id="<?="reserved_field_$i"?>" name="ClubMember[<?="reserved_field_$i"?>]" cols=""><?=$member["reserved_field_$i"]?></textarea>
                <?php elseif ($cons["reserved_field_$i"]->col_type== ClubConfig::TYPE_LIST):?>
                <select <?php if($required):?>required<?php endif;?> id="<?="reserved_field_$i"?>" name="ClubMember[<?="reserved_field_$i"?>]">
                          <?php foreach ($values as $value):?>
                            <?php if($member["reserved_field_$i"]==$value):?>
                            <option value="<?=$value?>" selected="selected"><?=$value?></option>
                            <?php else:?>
                            <option value="<?=$value?>"><?=$value?></option>
                            <?php endif;?>
                          <?php endforeach;?>
                </select>
                <?php elseif ($cons["reserved_field_$i"]->col_type== ClubConfig::TYPE_DATE):?>
                <input <?php if($required):?>required<?php endif;?> type="date" id="<?="reserved_field_$i"?>" name="ClubMember[<?="reserved_field_$i"?>]" value="<?=$member["reserved_field_$i"]?>">
                <?php elseif ($cons["reserved_field_$i"]->col_type== ClubConfig::TYPE_FILE):?>
                 <br>
                <input type="hidden"  name="ClubMember[<?="reserved_field_$i"?>]"  id="<?="reserved_field_$i"?>_value" value="<?=$member["reserved_field_$i"]?>">
                <input type="file" <?php if($required&&$member["reserved_field_$i"]==null):?>required<?php endif;?> id="<?="reserved_field_$i"?>" name="file[]">
                <img alt="" src="<?=CustomHelper::CreateImageUrl($member["reserved_field_$i"],"small80")?>" id="<?="reserved_field_$i"?>_pre" style="width:50px;height:50px;margin-top: 14px" onerror="this.style.display='none'" >
                <?php elseif($cons["reserved_field_$i"]->col_type== ClubConfig::TYPE_TIME):?>
                <input type="time" id="<?="reserved_field_$i"?>" name="ClubMember[<?="reserved_field_$i"?>]" value="<?=$member["reserved_field_$i"]?>">
                <?php endif;?>
               </div>
                  <?php endif;?>
             <?php endfor;?>
      </div>
   </div>
</div>
</section>
   </form>
   
   <?php if(trim($club->extra_desc)):?>
   <section data-am-widget="accordion" class="am-accordion am-accordion-gapped"
data-am-accordion='{  }'>
<div class="am-panel am-panel-default" style="border:none" id="exd-panel">
<div class="am-panel-hd">
      <h4 class="am-panel-title" data-am-collapse="{target: '#exd-content'}">
                           更多说明
      </h4>
    </div>
    <div id="exd-content" class="am-panel-collapse am-collapse am-in">
      <div class="am-panel-bd" id="exd-bd">
<pre><?=$club->extra_desc?></pre>
</div>
</div>
</div>
</section>
<?php endif;?>
 <section data-am-widget="accordion" class="am-accordion am-accordion-gapped"
data-am-accordion='{  }'>
<?php if(!$new):?>
<div class="am-g">
    <div class="am-u-sm-6"><button type="button" class="am-btn am-btn-primary am-btn-block" data-am-loading="{spinner: 'circle-o-notch'}" id="btnApply" onclick="apply();">提交</button></div>
 <div class="am-u-sm-6"><button type="button" class="am-btn am-btn-primary am-btn-warning am-btn-block"   onclick="<?php if($new):?>$('#userinfo').toggle();$('#regBtn').toggleClass('r_1_ex');$('#reg').toggle();<?php else:?>$('#info').show();$('#reg').hide();$(window).smoothScroll({position:$('#userinfo').position().top})<?php endif;?>">取消</button></div>
</div>
<?php else:?>
<button type="button" class="am-btn am-btn-primary am-btn-block" data-am-loading="{spinner: 'circle-o-notch'}" id="btnApply" onclick="apply();">提交</button></div>
<?php endif;?>
</section>
</div>


           
  <div id="info">
<section data-am-widget="accordion" class="am-accordion am-accordion-gapped"
data-am-accordion='{  }'>
 <button type="button" class="am-btn am-btn-primary am-radius  am-btn-block" id="modifyBtn" onclick="modifyShow();">修改信息</button>
</section>

<section data-am-widget="accordion" class="am-accordion am-accordion-gapped"
data-am-accordion='{  }'>
      
  <div class="am-panel am-panel-default" style="border:none" id="info-base-panel">
<div class="am-panel-hd">
      <h4 class="am-panel-title" data-am-collapse="{target: '#info-base-1'}">
                            基本信息
      </h4>
    </div>
    <div id="info-base-1" class="am-panel-collapse am-collapse am-in">
      <div class="am-panel-bd">
      
      <ul class="am-list am-list-static">
         <?php if(key_exists("passport_name", $cons)&&$cons["passport_name"]->visible==1):?>  
      <li>
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4">姓名:</div>
  			<div class="am-u-sm-8"><?=$member["passport_name"]?></div>
	</div>
      </li>
      <?php endif;?>
      
       <?php if(key_exists("cell", $cons)&&$cons["cell"]->visible==1):?> 
      <li>
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4">手机:</div>
  			<div class="am-u-sm-8"><?=$member["cell"]?></div>
	</div>
      </li>
      <?php endif;?>
        <?php if(key_exists("gender", $cons)&&$cons["gender"]->visible==1):?> 
       <li>
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4">性别:</div>
  			<div class="am-u-sm-8"><?php switch($member["gender"]){case 1:echo "男";break;case 2:echo "女";break;case 3:echo "其他";break;}?></div>
	</div>
	  </li>
	  <?php endif;?>
      </ul>
      </div>
      </div>
      </div>
      
      
       <div class="am-panel am-panel-default" style="border:none" id="info-bm-panel">
<div class="am-panel-hd" style="border:none">
      <h4 class="am-panel-title" data-am-collapse="{target: '#info-bm-1'}">
                           报名信息
      </h4>
    </div>
    <div id="info-bm-1" class="am-panel-collapse am-collapse am-in">
      <div class="am-panel-bd" id="info-bm-bd">
    <ul class="am-list am-list-static">
      <?php if(key_exists("nationality", $cons)&&$cons["nationality"]->visible==1):?>  
          <li>
        <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["nationality"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["nationality"]?></div>
	</div>
                    
      </li>
      <?php endif;?>
      
         <?php if(key_exists("city", $cons)&&$cons["city"]->visible==1):?>  
 <li>
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["city"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["city"]?></div>
	</div>
      </li>
      <?php endif;?>
      
      <?php if(key_exists("birthday", $cons)&&$cons["birthday"]->visible==1):?>  
      <li>
       <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["birthday"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["birthday"]?></div>
	</div>
                   
      </li>
      <?php endif;?>
      
      <?php if(key_exists("id_type", $cons)&&$cons["id_type"]->visible==1):?>  
       <li>
       <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["id_type"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?php switch ($member["id_type"]){
  				case 1:echo "身份证";break;
  				case 2:echo "护照";break;
  				case 3:echo "台胞证";break;
  				case 4:echo "港澳通行证";break;
  				case 0:echo "其他";break;
  			}?></div>
	</div>
      </li>
      <?php endif;?>
      
       <?php if(key_exists("id_number", $cons)&&$cons["id_number"]->visible==1):?>  
      <li>
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["id_number"]->col_title?>:</div>
  			<div class="am-u-sm-8"> <?=$member["id_number"]?></div>
	</div>
      </li>
      <?php endif;?>
     
       <?php if(key_exists("email", $cons)&&$cons["email"]->visible==1):?>  
      <li>
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["email"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["email"]?></div>
	</div>
      </li>
      <?php endif;?>
      
           <?php if(key_exists("address", $cons)&&$cons["address"]->visible==1):?>  
       <li>
       <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["address"]->col_title?>:</div>
  			<div class="am-u-sm-8"> <?=$member["address"]?></div>
	</div>
      </li>
      <?php endif;?>

        <?php if(key_exists("shirt_size", $cons)&&$cons["shirt_size"]->visible==1):?>  
      <li>
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["shirt_size"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["shirt_size"]?></div>
	</div>
      </li>
      <?php endif;?>
      
       <?php if(key_exists("emerge_name", $cons)&&$cons["emerge_name"]->visible==1):?>  
       <li>
       <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["emerge_name"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["emerge_name"]?></div>
	</div>
       </li>
       <?php endif;?>
       
            <?php if(key_exists("emerge_ship", $cons)&&$cons["emerge_ship"]->visible==1):?>  
       <li>
       <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["emerge_ship"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["emerge_ship"]?></div>
	</div>
       </li>
       <?php endif;?>
       
         <?php if(key_exists("emerge_cell", $cons)&&$cons["emerge_cell"]->visible==1):?>  
        <li>
       <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["emerge_cell"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["emerge_cell"]?></div>
	</div>
       </li>
       <?php endif;?>
     <?php if(key_exists("id_image", $cons)&&$cons["id_image"]->visible==1):?>  
        <li>
       <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["id_image"]->col_title?>:</div>
  			<div class="am-u-sm-8">
  			<ul data-am-widget="gallery" class="am-gallery am-avg-sm-2 am-gallery-imgbordered" data-am-gallery="{pureview: 1}">
  <li>
    <div class="am-gallery-item">
      <img src="<?=CustomHelper::CreateImageUrl($member["id_image"], "img_logo")?>" data-rel="<?=CustomHelper::CreateImageUrl($member["id_image"])?>" onerror="this.style.display='none'"/>
    </div>
  </li>
  <li>
    <div class="am-gallery-item">
     <img src="<?=CustomHelper::CreateImageUrl($member["id_copy_back"], "img_logo")?>" data-rel="<?=CustomHelper::CreateImageUrl($member["id_copy_back"])?>" onerror="this.style.display='none'"/>
    </div>
  </li>
  </ul>
  			</div>
	</div>
       </li>
       <?php endif;?>
    </ul> 
   </div>
</div>
</div>

   <div class="am-panel am-panel-default" style="border:none" id="info-pt-panel">
<div class="am-panel-hd">
      <h4 class="am-panel-title" data-am-collapse="{target: '#info-pt-1'}">
                          跑团
      </h4>
    </div>
    <div id="info-pt-1" class="am-panel-collapse am-collapse am-in">
      <div class="am-panel-bd">
      
       <ul class="am-list am-list-static">
        <?php if(key_exists("run_age", $cons)&&$cons["run_age"]->visible==1):?>  
      <li id="info_run_age">
       <div class="am-g am-g-fixed" >
  			<div class="am-u-sm-4"><?=$cons["run_age"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["run_age"]?></div>
	</div>
      </li>
      <?php endif;?>
         <?php if(key_exists("join_day", $cons)&&$cons["join_day"]->visible==1):?>  
          <li id="info_join_day">
<div class="am-g am-g-fixed" >
  			<div class="am-u-sm-4"><?=$cons["join_day"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["join_day"]?></div>
	</div>
                            
      </li>
      <?php endif;?>
      
      <?php if(key_exists("sub_group", $cons)&&$cons["sub_group"]->visible==1):?>  
       <li id="info_sub_group">
       <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["sub_group"]->col_title?>:</div>
  			<div class="am-u-sm-8">
  			<?php if($member["sub_group"]==-1):?>
  			                        总部
  			<?php else:?>
  			<?php foreach($club->subs as $sub):?>
                            <?php if($sub->id==$member["sub_group"]):?>
                                <?=$sub->sub_name?>
                            <?php endif;?>
                        <?php endforeach;?>
                 <?php endif;?>
  			</div>
	</div>
      </li>
      <?php endif;?>
       <?php if(key_exists("marathon_max_score", $cons)&&$cons["marathon_max_score"]->visible==1):?>  
       <li id="info_marathon_max_score">
       <div class="am-g am-g-fixed" >
  			<div class="am-u-sm-4"><?=$cons["marathon_max_score"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["marathon_max_score"]?></div>
	</div>
      </li>
      <?php endif;?>
       <?php if(key_exists("half_marathon_max_score", $cons)&&$cons["half_marathon_max_score"]->visible==1):?>  
      <li id="info_half_marathon_max_score">
       <div class="am-g am-g-fixed" >
  			<div class="am-u-sm-4"><?=$cons["half_marathon_max_score"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["half_marathon_max_score"]?></div>
	</div>
      </li>
      <?php endif;?>
      <?php if(key_exists("ten_km_max_score", $cons)&&$cons["ten_km_max_score"]->visible==1):?>  
       <li id="info_ten_km_max_score">
       <div class="am-g am-g-fixed" >
  			<div class="am-u-sm-4"><?=$cons["ten_km_max_score"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["ten_km_max_score"]?></div>
	  </div>
      </li>
      <?php endif;?>
      </ul>
     </div>
   </div>
   </div>
   
   
      <div class="am-panel am-panel-default" style="border:none" id="info-qt-panel">
<div class="am-panel-hd">
      <h4 class="am-panel-title" data-am-collapse="{target: '#info-qt-1'}">
                          其他
      </h4>
    </div>
    <div id="info-qt-1" class="am-panel-collapse am-collapse am-in" style="border:none;box-sizing:none">
      <div class="am-panel-bd" >
       <ul class="am-list am-list-static">
       <?php if(key_exists("blood_type", $cons)&&$cons["blood_type"]->visible==1):?>  
        <li id="info_blood_type">
      <div class="am-g am-g-fixed" >
  			<div class="am-u-sm-4"><?=$cons["blood_type"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["blood_type"]?></div>
	</div>
      </li>
       <?php endif;?>   
      <?php if(key_exists("shoe_size", $cons)&&$cons["shoe_size"]->visible==1):?>  
      <li id="info_shoe_size">
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["shoe_size"]->col_title?>:</div>
  			<div class="am-u-sm-8"><?=$member["shoe_size"]?></div>
	</div>
                     
      </li>
      <?php endif;?>
      <?php if(key_exists("health_check_certificate", $cons)&&$cons["health_check_certificate"]->visible==1):?>  
       <li id="info_health_check_certificate">
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["health_check_certificate"]->col_title?>:</div>
  			<div class="am-u-sm-8"><img src="<?=CustomHelper::CreateImageUrl($member["health_check_certificate"], "small80")?>" onerror="this.style.display='none'"/></div>
	</div>
      </li>
      	<?php endif;?>
      	
      <?php if(key_exists("marathon_score_certificate", $cons)&&$cons["marathon_score_certificate"]->visible==1):?>  
       <li id="info_marathon_score_certificate">
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["marathon_score_certificate"]->col_title?>:</div>
  			<div class="am-u-sm-8"><img src="<?=CustomHelper::CreateImageUrl($member["marathon_score_certificate"], "small80")?>" onerror="this.style.display='none'"/></div>
	</div>
      </li>
      <?php endif;?>
       <?php if(key_exists("cross_race_score_certificate", $cons)&&$cons["cross_race_score_certificate"]->visible==1):?>  
       <li id="info_cross_race_score_certificate">
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["cross_race_score_certificate"]->col_title?>:</div>
  			<div class="am-u-sm-8"><img src="<?=CustomHelper::CreateImageUrl($member["cross_race_score_certificate"], "small80")?>" onerror="this.style.display='none'"/></div>
	</div>
      </li>
      <?php endif;?>
       <?php if(key_exists("height", $cons)&&$cons["height"]->visible==1):?>  
         <li id="info_height">
       <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["height"]->col_title?>:</div>
  			<div class="am-u-sm-8"> <?=$member["height"]?></div>
	</div>
      </li>
      <?php endif;?>
      <?php if(key_exists("weight", $cons)&&$cons["weight"]->visible==1):?> 
      <li id="info_weight">
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["weight"]->col_title?>:</div>
  			<div class="am-u-sm-8">  <?=$member["weight"]?></div>
	</div>
                   
      </li>
      <?php endif;?>
      
      <?php if(key_exists("medical_history", $cons)&&$cons["medical_history"]->visible==1):?> 
        <li id="info_medical_history">
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["medical_history"]->col_title?>:</div>
  			<div class="am-u-sm-8">  <?=$member["medical_history"]?></div>
	</div>
                   
      </li>
      <?php endif;?>
      
      <?php if(key_exists("company_info", $cons)&&$cons["company_info"]->visible==1):?> 
        <li id="info_company_info">
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["company_info"]->col_title?>:</div>
  			<div class="am-u-sm-8">  <?=$member["company_info"]?></div>
	</div>
                   
      </li>
      <?php endif;?>
      
         <?php if(key_exists("job_title", $cons)&&$cons["job_title"]->visible==1):?> 
        <li id="info_job_title">
      <div class="am-g am-g-fixed">
  			<div class="am-u-sm-4"><?=$cons["job_title"]->col_title?>:</div>
  			<div class="am-u-sm-8">  <?=$member["job_title"]?></div>
	</div>
                   
      </li>
      <?php endif;?>
      
        <?php for($i=1;$i<=8;$i++):?>
                  <?php if(array_key_exists("reserved_field_$i", $cons)&&$cons["reserved_field_$i"]->visible==1):?>
                   <li id="info_<?="reserved_field_$i"?>">
       <div class="am-g am-g-fixed">
         <div class="am-u-sm-4"><?=$cons["reserved_field_$i"]->col_title ?>:</div>
         <div class="am-u-sm-8">
            <?php if($cons["reserved_field_$i"]->col_type== ClubConfig::TYPE_FILE):?> 
               <img src="<?=CustomHelper::CreateImageUrl($member["reserved_field_$i"], "small80")?>" onerror="this.style.display='none'"/>
                <?php else:?>
                <?=$member["reserved_field_$i"]?>
                <?php endif;?>
            </div>
            </div>
              </li>
                  <?php endif;?>
           <?php endfor;?>
       </ul>
      </div>
      </div>
    </div>
    </section>
      </div>
<?=Util::getWechatJs()?> 
<script type="text/javascript">
var hasPro=false;
if($.trim($("#base-bd").html())==""){
	$("#base-panel").hide();
	$("#info-base-panel").hide();
}else{
	hasPro=true;
}

if($.trim($("#pt-bd").html())==""){
	$("#pt-panel").hide();
	$("#info-pt-panel").hide();
}else{
	hasPro=true;
}

if($.trim($("#other-bd").html())==""){
	$("#other-panel").hide();
	$("#info-qt-panel").hide();
}else{
	hasPro=true;
}

if($.trim($("#bm-bd").html())==""){
	$("#bm-panel").hide();
	$("#info-bm-panel").hide();
}else{
	hasPro=true;
}

if(hasPro){
	$("#modifyBtn").show();
}else{
	$("#modifyBtn").hide();
}

$("#status_sec").css("margin-top","0px");

$.each($("input[type='file']"),function(i,n){
	fileupload(n.id);
});
//document.getElementById("exd").style.height = document.getElementById("exd").scrollHeight+"px";
</script>