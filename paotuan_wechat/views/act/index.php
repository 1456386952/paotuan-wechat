<?php
use common\component\CustomHelper;
use common\models\Item;
use yii\helpers\ArrayHelper;
use common\models\OrderMaster;
use paotuan_wechat\component\Util;
use yii\base\Request;
use paotuan_wechat\models\Club;
header ( "Content-type: text/html; charset=utf-8" );
if(!$user){
	echo "<h1>微信授权超时，请稍候重试</h1>";
	exit;
}
if(!$act){
		header("/notfound.html");
	exit;
}
$hasOrder=false;
$canPay = false;
$modify=true;
if($order!=null){
	$hasOrder=true;
	if($order->order_status==OrderMaster::STATUS_WAIT_PAY){
		$canPay=true;
		
	}
	if($order->order_status!=OrderMaster::STATUS_CANCEL){
		$modify=false;
	}
}

$cur = null;
$hotel=array();
$traffic=array();
$other=array();
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title><?=$act->act_name?></title>
<style type="text/css">
  .am-form-group{
  }
  

</style>
<?=util::getMainJsCssWithAppFramework()?>
<?=Util::getFileUploadJs();?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<?=Util::getWechatJs()?>
<script id="hotel-template" type="text/x-handlebars-template">  
	<ul data-am-widget="gallery" class="am-gallery am-gallery-overlay" data-am-gallery="{ pureview: {target:'a'} }" style="padding:0">
  <li  style="padding:0">
 <div class="am-gallery-item">
 <a >
  <img src="http://xiaoi.b0.upaiyun.com/{{item_pic_url}}" style="height:200px"/>
        <h3 class="am-gallery-title">{{item_name}}</h3>
        </a>
    </div>
    </li>
</ul>
<div class="am-container">
<div class="content-title" style="font-size:1.5rem"><span class="am-icon-hotel"> {{hotel_type}}</span><span class="am-fr">￥{{item_price}}</span></div>
<hr>
<div class="content-title" style="font-size:1.5rem"><span class="ion-ios-location am-icon-sm"> {{address}}</span></div>
<hr>
<div class="content-title" style="font-size:1.5rem"><span class=" am-icon-sm"> 距起点:{{distance}}m</span></div>
<hr>
<div class="content-title" style="font-size:1.5rem"><span class="ion-ios-telephone am-icon-sm"> {{cell}}</span></div>
<hr>
<?php if($modify):?>
{{#if set_per_order_num}}
<div class="content-title" style="line-height:1.5rem; vertical-align: middle;font-size:1.5rem;display:{{#if out}}none{{/if}} {{#if end}}none{{/if}}"> 数量:{{#if status}} {{else}}<a class="am-icon-btn ion-minus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#ff0000;width:30px" href="javascript:void(0);" onclick="rangeMinus('#hotel-{{itemid}}-num');"></a><input type="range"   id="hotel-{{itemid}}-num" min="1" max="{{set_per_order_num}}" value="{{#if item_num}}{{item_num}}{{else}}1{{/if}}" onchange="numChange(this,this.value,{{item_num_limit}},{{item_buy_sum}})"/><a class="am-icon-btn ion-plus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#5eb95e;width:30px" href="javascript:void(0);" onclick="rangePlus('#hotel-{{itemid}}-num');"></a>{{/if}}<span>{{#if item_num}}{{item_num}}{{else}}1{{/if}}</span>(限购{{set_per_order_num}})<br><hr></div>
{{else}}
<div class="content-title" style="font-size:1.5rem;display:{{#if out}}none{{/if}} {{#if end}}none{{/if}}"> 数量:{{#if status}} {{else}}<a class="am-icon-btn ion-minus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#ff0000;width:30px" href="javascript:void(0);" onclick="rangeMinus('#hotel-{{itemid}}-num');"></a><input type="range"  {{#if out}}readonly{{/if}} {{#if end}}readonly{{/if}} id="hotel-{{itemid}}-num" min="1" max="{{canbuy}}" value="{{#if item_num}}{{item_num}}{{else}}1{{/if}}" onchange="numChange(this,this.value,{{item_num_limit}},{{item_buy_sum}})"/><a class="am-icon-btn ion-plus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#5eb95e;width:30px" href="javascript:void(0);" onclick="rangePlus('#hotel-{{itemid}}-num');"></a>{{/if}} <span>{{#if item_num}}{{item_num}}{{else}}1{{/if}}</span><br><hr></div>
{{/if}}
{{#if status}}
<a  class="am-btn am-btn-success am-center am-btn-block am-round  {{#if out}}am-disabled{{/if}} {{#if end}}am-disabled{{/if}}" href="javascript:void(0);" onclick="itemUnCheck('#hotel_{{itemid}}',true);">取消预订</a>
{{else}}<a  class="am-btn am-btn-success am-center am-btn-block am-round {{#if out}}am-disabled{{/if}} {{#if end}}am-disabled{{/if}}" href="javascript:void(0);" onclick="itemCheck('#hotel_{{itemid}}',true);">{{#if out}}已售完 {{else}}{{#if end}}已过期
{{else}}预订{{/if}}
{{/if}}
      </a>
{{/if}}
<?php else:?>
{{#if status}}
<div class="content-title" style="font-size:1.5rem">数量: {{#if item_num}}{{item_num}}{{else}}1{{/if}}</div>
{{/if}}
<?php endif;?>
<div>
</script>
<script id="traffic-template" type="text/x-handlebars-template">  
	<ul data-am-widget="gallery" class="am-gallery am-gallery-overlay" data-am-gallery="{ pureview: {target:'a'} }" style="padding:0">
  <li  style="padding:0">
 <div class="am-gallery-item">
 <a >
  <img src="http://xiaoi.b0.upaiyun.com/{{item_pic_url}}" style="height:200px"/>
        </a>
    </div>
    </li>
</ul>
<div class="am-container">
<div class="content-title" style="font-size:1.5rem"><span> {{item_name}}</span><span class="am-fr">￥{{item_price}}</span></div>
<hr>
<div class="content-title" style="font-size:1.5rem"><span class="ion-ios-location am-icon-sm"> 上车地点:{{address}}</span></div>
<hr>
<div class="content-title" style="font-size:1.5rem"><span class="ion-clock am-icon-sm"> 发车时间:{{item_time}}</span></div>
<hr>
<div class="content-title" style="font-size:1.5rem"><span class="ion-ios-telephone am-icon-sm"> {{cell}}</span></div>
<hr>
<?php if($modify):?>
{{#if set_per_order_num}}
<div class="content-title" style="font-size:1.5rem;display:{{#if out}}none{{/if}} {{#if end}}none{{/if}}"> 数量:{{#if status}} {{else}}<a class="am-icon-btn ion-minus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#ff0000;width:30px" href="javascript:void(0);" onclick="rangeMinus('#traffic-{{itemid}}-num');"></a><input type="range"  id="traffic-{{itemid}}-num" min="1" max="{{set_per_order_num}}" value="{{#if item_num}}{{item_num}}{{else}}1{{/if}}" onchange="numChange(this,this.value,{{item_num_limit}},{{item_buy_sum}})"/><a class="am-icon-btn ion-plus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#5eb95e;width:30px" href="javascript:void(0);" onclick="rangePlus('#traffic-{{itemid}}-num');"></a>{{/if}} <span>{{#if item_num}}{{item_num}}{{else}}1{{/if}}</span> (限购{{set_per_order_num}})<br><hr></div>
{{else}}
<div class="content-title" style="font-size:1.5rem;display:{{#if out}}none{{/if}} {{#if end}}none{{/if}}"> 数量:{{#if status}} {{else}}<a class="am-icon-btn ion-minus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#5eb95e;width:30px" href="javascript:void(0);" onclick="rangePlus('#traffic-{{itemid}}-num');"></a><input type="range"  {{#if out}}readonly{{/if}} {{#if end}}readonly{{/if}} id="traffic-{{itemid}}-num" min="1" max="{{canbuy}}" value="{{#if item_num}}{{item_num}}{{else}}1{{/if}}" onchange="numChange(this,this.value,{{item_num_limit}},{{item_buy_sum}})"/><a class="am-icon-btn ion-plus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#5eb95e;width:30px" href="javascript:void(0);" onclick="rangePlus('#traffic-{{itemid}}-num');"></a>{{/if}} <span>{{#if item_num}}{{item_num}}{{else}}1{{/if}}</span><br><hr></div>
{{/if}}
{{#if status}}
<a  class="am-btn am-btn-success am-center am-btn-block am-round  {{#if out}}am-disabled{{/if}} {{#if end}}am-disabled{{/if}}" href="javascript:void(0);" onclick="itemUnCheck('#traffic_{{itemid}}',true);">取消预订</a>
{{else}}<a  class="am-btn am-btn-success am-center am-btn-block am-round {{#if out}}am-disabled{{/if}} {{#if end}}am-disabled{{/if}}" href="javascript:void(0);" onclick="itemCheck('#traffic_{{itemid}}',true);">{{#if out}}已售完 {{else}}{{#if end}}已过期
{{else}}预订{{/if}}
{{/if}}
      </a>
{{/if}}
<?php else:?>
<div class="content-title" style="font-size:1.5rem">数量: {{#if item_num}}{{item_num}}{{else}}1{{/if}}</div>
<?php endif;?>
<div>
</script>

<script id="other-template" type="text/x-handlebars-template">  
	<ul data-am-widget="gallery" class="am-gallery am-gallery-overlay" data-am-gallery="{ pureview: {target:'a'} }" style="padding:0">
  <li  style="padding:0">
 <div class="am-gallery-item">
 <a >
  <img src="http://xiaoi.b0.upaiyun.com/{{item_pic_url}}" style="height:200px"/>
        </a>
    </div>
    </li>
</ul>
<div class="am-container">
<div class="content-title" style="font-size:1.5rem"><span> {{item_name}}</span><span class="am-fr">￥{{item_price}}</span></div>
<hr>
<div class="content-title" style="font-size:1.5rem"><p>{{item_desc}}</p></div>
<hr>
<?php if($modify):?>
{{#if set_per_order_num}}
<div class="content-title" style="font-size:1.5rem;display:{{#if out}}none{{/if}} {{#if end}}none{{/if}}"> 数量:{{#if status}} {{else}}<a class="am-icon-btn ion-minus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#5eb95e;width:30px" href="javascript:void(0);" onclick="rangeMinus('#other-{{itemid}}-num');"></a><input type="range"  id="other-{{itemid}}-num" min="1" max="{{set_per_order_num}}" value="{{#if item_num}}{{item_num}}{{else}}1{{/if}}" onchange="numChange(this,this.value,{{item_num_limit}},{{item_buy_sum}})"/><a class="am-icon-btn ion-plus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#5eb95e;width:30px" href="javascript:void(0);" onclick="rangePlus('#other-{{itemid}}-num');"></a>{{/if}} <span>{{#if item_num}}{{item_num}}{{else}}1{{/if}}</span> (每单限购{{set_per_order_num}})<br><hr></div>
{{else}}
<div class="content-title" style="font-size:1.5rem;display:{{#if out}}none{{/if}} {{#if end}}none{{/if}}"> 数量:{{#if status}} {{else}}<a class="am-icon-btn ion-minus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#5eb95e;width:30px" href="javascript:void(0);" onclick="rangeMinus('#other-{{itemid}}-num');"></a><input type="range"  {{#if out}}readonly{{/if}} {{#if end}}readonly{{/if}} id="other-{{itemid}}-num" min="1" max="{{canbuy}}" value="{{#if item_num}}{{item_num}}{{else}}1{{/if}}" onchange="numChange(this,this.value,{{item_num_limit}},{{item_buy_sum}})"/><a class="am-icon-btn ion-plus minfo-icon-btn am-icon-sm" style="position: relative;top:3px;color:#5eb95e;width:30px" href="javascript:void(0);" onclick="rangePlus('#other-{{itemid}}-num');"></a>{{/if}} <span>{{#if item_num}}{{item_num}}{{else}}1{{/if}}</span><br><hr></div>
{{/if}}
{{#if status}}
<a  class="am-btn am-btn-success am-center am-btn-block am-round  {{#if out}}am-disabled{{/if}} {{#if end}}am-disabled{{/if}}" href="javascript:void(0);" onclick="itemUnCheck('#other_{{itemid}}',true);">取消预订</a>
{{else}}<a  class="am-btn am-btn-success am-center am-btn-block am-round {{#if out}}am-disabled{{/if}} {{#if end}}am-disabled{{/if}}" href="javascript:void(0);" onclick="itemCheck('#other_{{itemid}}',true);">{{#if out}}已售完 {{else}}{{#if end}}已过期
{{else}}预订{{/if}}
{{/if}}
      </a>
{{/if}}
<?php else:?>
<div class="content-title" style="font-size:1.5rem">数量: {{#if item_num}}{{item_num}}{{else}}1{{/if}}</div>
<?php endif;?>
<div>
<div>
</script>
<style type="text/css">

.i-red-12 {
	color: red;
}

input[type="radio"],
input[type="checkbox"] {
  display: inline-block;
}

.am-form-group .am-btn {
	font-size: 1.2rem;
}

.radio-font-size {
	font-size: 1.2rem;
}

.registerWrap .r_1_ex:after {
	content: '';
	border: #fff solid;
	border-width: 3px 3px 0 0;
	-webkit-transform: rotate(135deg);
	-moz-transform: rotate(135deg);
	transform: rotate(135deg);
	position: absolute;
	width: 10px;
	height: 10px;
	right: 14px;
	top: 12px;
}

.pd10 {
	padding-bottom: 0px
}

.icon-color{
	color:#cb352b;
}

.afPopup {
    display: block;
    border:1px solid rgba(158,158,158,255);
    border-radius:10px;
    padding:0;
    text-align: center;
    width:100%;
    position: absolute;
    z-index: 1000000;
    top: 0;
    height:400px;
    overflow:auto;
    color:inherit;
    background:rgba(249,249,249,1);
    text-align:center;
}

</style>
<script type="text/javascript">

var store = $.AMUI.store;
if (!store.enabled) {
	  alert('您的浏览器不支持本地存储,请关闭“无痕浏览”模式');
}

<?php if($hasOrder):?>
   <?php if(!$modify):?>
    	store.forEach(function(key, val) {
   	     if(val&&val.itemid){
   	    	store.remove(key);
   		   }
   	})
     <?php endif;?>
   <?php foreach ($order->detail as $detail):?>
   <?php $tmp = ArrayHelper::toArray($detail->itemInfo);
         $tmp["status"]=1;
          $tmp["item_num"]=$detail->item_num;
         if($order->order_status!=OrderMaster::STATUS_NORMAL&&$order->order_status!=OrderMaster::STATUS_WAIT_PAY){
         	if ($detail->itemInfo->item_status == Item::STATUS_END || strtotime ( $detail->itemInfo->item_end ) < time ()) {
         		 $tmp["status"]=0;
         		 
         	}
         	if ($detail->itemInfo->item_num_limit > 0 && $detail->itemInfo->item_num_limit - $detail->itemInfo->item_buy_sum < $detail->item_num) {
         		 $tmp["status"]=0;
         	}
         	
         }
        
   ?>
               var orderItem = <?=json_encode($tmp)?>;
               if(!store.get('act-item-'+orderItem.itemid)){
            	   <?php if(!$modify):?>
            	   store.set('act-item-'+orderItem.itemid,orderItem);
            	   <?php endif;?>
                }
             <?php 
            if($detail->itemInfo->item_type==Item::TYPE_REGISTER){
         		$cur = $detail->itemInfo;
         	}
         	if($detail->itemInfo->item_type==Item::TYPE_HOTEL){
         		array_push($hotel, $detail);
         	}
         	if($detail->itemInfo->item_type==Item::TYPE_OTHER){
         		array_push($other, $detail);
         	}
         	if($detail->itemInfo->item_type==Item::TYPE_TRAFFIC){
         		array_push($traffic, $detail);
         	}
         	?>
             <?php endforeach;?>
<?php endif;?>
$.afui.useOSThemes=false;
$.afui.loadDefaultHash=false;
$.afui.isAjaxApp=false;
   wx.config({
	    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
	    appId: '<?=$signPackage["appId"];?>',
	    timestamp: <?=$signPackage["timestamp"];?>,
	    nonceStr: '<?=$signPackage["nonceStr"];?>',
	    signature:'<?=$signPackage["signature"];?>',
	    jsApiList: ['chooseWXPay','closeWindow','chooseImage','uploadImage','previewImage','onMenuShareAppMessage','onMenuShareTimeline']
	});

   wx.ready(function(){
	    // 获取“分享给朋友”按钮点击状态及自定义分享内容接口
	    wx.onMenuShareAppMessage({
	    title: <?=json_encode($act->act_name)?>, // 分享标题
	    desc: <?=json_encode($act->act_intro)?>,
	    link: '<?=Yii::$app->request->hostInfo."/act/".$act->actid."?channelid=".$channelId?>', // 分享链接
	    imgUrl:  '<?php if($act->act_image){echo CustomHelper::CreateImageUrl($act->act_image, "small80");}else{ echo Yii::$app->request->hostInfo."/image/paotuanzhuce/top.png";}?>', // 分享图标
	    type: 'link', // 分享类型,music、video或link，不填默认为link
	    dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
	    success: function () { 
	    },
	    cancel: function () { 
	        // 用户取消分享后执行的回调函数
	    }
	  });
	    // 获取“分享到朋友圈”按钮点击状态及自定义分享内容接口
	    wx.onMenuShareTimeline({
	        title: <?=json_encode($act->act_name)?>, // 分享标题
	        link: '<?=Yii::$app->request->hostInfo."/act/".$act->actid."?channelid=".\Yii::$app->request->get("channelid")?>', // 分享链接
	        imgUrl: '<?php if($act->act_image){echo CustomHelper::CreateImageUrl($act->act_image, "small80");}else{ echo Yii::$app->request->hostInfo."/image/paotuanzhuce/top.png";}?>', // 分享图标
	        success: function () { 
	            // 用户确认分享后执行的回调函数
	        },
	        cancel: function () { 
	            // 用户取消分享后执行的回调函数
	        }
	    });
	});

   $.ajaxSetup({
	   type: "POST",
	   beforeSend:function(){
		    
		   },
	   error:function(req){
		   },
	   complete:function(req, textStatus){
		   hideLoading();
		   $("#modal-loading").modal("close");
		   $("#inviteBtn").button("reset");  
		   },
		   statusCode: {500: function() {
			   $.afui.unblockUI();
			   $.afui.hideMask()
			    $("#alert-content-error").text("服务器异常，请稍候重试或联系管理员！");
 		    	 $("#appleAlertError").modal();
			   }  
	          }
	   });
	  

   wx.error(function(res){
	     $.each(res,function(i,n){
                 //alert(i+"----"+n);
		     });
	});

	function showError(text){
		$("#alert-content-error").text(text);
    	 $("#appleAlertError").modal();
		}

    function alertMsg(msg,callback){
   	 $("#alert-content").text(msg);
   	         if($.isFunction(callback)){
	    	 $("#appleAlert").on("closed.modal.amui",callback);
   	         }
	    	 $("#appleAlert").modal();
        }

	function closeWechatWindow(){
		wx.closeWindow();
		}

	function fileUpload(file,type){
		if(file.value==""||!checkImg(file))return;
		$("#uploadForm").empty();
		$("#uploadForm").append(file);
	    $("#uploadForm").append("<input type='hidden' name='img_type' value='"+file.id+"'/>");
	    $("#uploadForm").append("<input type='hidden' name='file_id' value='"+file.id+"'/>");
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
		var r = $("#upFrame").contents().find(".uploadResult");
		if(r.length>0){
			  var result = $.parseJSON($(r[0]).text());
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

	function getResCode(){
		   $("#regError").text("");
        var  phone = $.trim($("#cell").val());
        if(phone==""){
       	 $("#cell").focus();
            return;
        }
        disableBtn("#veriCode",$("#veriCode").text());
        $.post("/act/getcellcode/<?=$act->actid?>?"+new Date().getTime(),{cell:phone},function(data){
              if(data.status==0){
             	 $("#alert-content-error").text(data.msg);
  		    	 $("#appleAlertError").modal();
                  }
            });
        return false;
	}

	function disableBtn(btn,text){
	  	  var sec=30;
	        $(btn).addClass("am-disabled");
	        var timer = window.setInterval(function(){
	      	  $(btn).addClass("am-disabled");
	              $(btn).text(text+"("+--sec+")");
	            
	              if(sec==0){
	           	   $(btn).text(text);
	           	   $(btn).removeClass("am-disabled");
	           	   window.clearInterval(timer);
	                  }
	            }, 1000);
	      }

    function rangeMinus(range){
          if($(range).val()>1){
        	  $(range).val( $(range).val()-1);
        	  $(range).trigger("onchange");
              }
        }

   function rangePlus(range){
	      $(range).val( parseInt($(range).val())+1);
 	      $(range).trigger("onchange");
	   }


    function apply(){
   	 checkCell();
   	  if($("#registerForm").data('amui.validator').isFormValid()){
   		 showLoading();
      	   $.post("/act/apply/<?=$act->actid?>?t="+new Date().getTime(),$("#registerForm").serialize(),function(data){
           	   if(data.status==1){
          		  $("#btnApply").button('reset');
          		 $("#registerid").val(data.id);
          		  $("#reg_text").text("查看个人信息");
          		  $("#reg_name").html($.trim($("#name").val()));
                  $.afui.loadContent("#main",false,false,"slide");
              	   }else if(data.status==0){
              	        $("#msg").text(data.msg);
              	        $("#msg").show();
              	        $(window).smoothScroll({position:$("#msg").position().top});
                  	   }
               });
           }
          return false;
        }

    function reg_load(){
          var item = $("#regList").children("li[checked]").data("data");
          $("#courseid").val(item.courseid);
          $("#channelid").val(item.channelid);
          $("body").scrollTop(0);
        }

    function hotelInfo(id){
    	itemInfo(id,"#hotelList","#hotel-info","#hotel-template");
       }

    function otherInfo(id){
    	   itemInfo(id,"#otherList","#other-info","#other-template");
        }

    function trafficInfo(id){
    	itemInfo(id,"#trafList","#traffic-info","#traffic-template");
        }

    function itemInfo(id,ul,show,template){
    	 showLoading();;
   		  $(show).empty();
            $.post("/act/item/"+id+"?"+new Date().getTime(),function(data){
                if(data.out||data.end){
                    <?php if($modify):?>
                	itemUnCheck(show.split("-")[0]+"_"+id,false);
                	<?php endif;?>
                    }
                if(store.get("act-item-"+id)){
                	data.status=1;
                	data.item_num = store.get("act-item-"+id).item_num;
                  }
                if(data.item_num_limit>0){
                data.canbuy = data.item_num_limit-data.item_buy_sum;
                }else{
                    data.item_num_limit=0;
                	data.canbuy =999;
                    }
           	     var source  = $(template).html();  
           	      var templateHTML = Handlebars.compile(source); 
           	      $(show).html(templateHTML(data));
           	     $.afui.loadContent(show.split("-")[0]+"-info",false,false,"slide");
           	   $("#traffic-info").contents().find("img").on("error",function(){
 	    	       $(this).attr("src","/image/traffic1.jpg");
 	    	  });
           	$("#other-info").contents().find("img").on("error",function(){
	    	       $(this).attr("src","/image/traffic1.jpg");
	    	  });
           	$("#hotel-info").contents().find("img").on("error",function(){
	    	       $(this).attr("src","/image/traffic1.jpg");
	    	  });
                });
       }

    function numChange(input,value,total,buy){
           if(total>0&&total-buy<value){
        	   alertMsg("数量不足，剩余"+(total-buy));
                   $(input).val(total-buy);
                   $(input).next().next().text(total-buy);
                   return false;
               }else{
            	   $(input).next().next().text(value);
                   }
        }

    function main_load(){
    	  var total = 0;
    	 <?php if($hasOrder&&$order->order_status==OrderMaster::STATUS_NORMAL):?>
    	 $("#total").text("￥<?=$order->actual_payment?>");
    	 return;
    	 <?php endif;?>
        var items="";
        var nums="";
           $.each($("div[class='checked']"),function(i,n){
                  var item = $(n).parent().data("data");
                  var num =1;
                  if(store.get("act-item-"+item.itemid)){
                	  num = store.get("act-item-"+item.itemid).item_num;
                    }
                  if(num){
                  total+=parseFloat(item.item_price*num);
                  }else{
                      num=1;
                	  total+=parseFloat(item.item_price);
                      }
                  items+=","+item.itemid;
                  nums+=","+num;
               });
          if(items!=""&&nums!=null){
              items = items.substring(1);
              nums = nums.substring(1);
              $("#items").val(items);
              $("#nums").val(nums);
              }
          if(total<0||items==""){
        	  $("#btnOrder").hide();
              }else{
            	  $("#btnOrder").show();
                  }
          $("#total").text("￥"+total);
        }

    function submitOrder(btn){
        if($("#registerid").val()==""){
        	alertMsg("请先填写个人信息",function(){ 
            	$.afui.loadContent("#register",false,false,"slide");
            	  $("#reg_fdset").width($(window).width());
            	});
        	   return;
            }
        showLoading();
        $(btn).hide();
        var orderid=$("#orderid");
        var postData={items:$("#items").val(),nums:$("#nums").val(),channelid:$("#channelid").val()};
        if(orderid){
        	postData.orderid=orderid.val();
            }
        postData.registerid=$("#registerid").val();
         $.post("/act/ordersubmit/<?=$act->actid?>?"+new Date().getTime(),postData,function(data){
                if(data.status==1){
                    if(data.data.actual_payment<=0){
                         var location =window.location;
                        	 window.location.replace(location+"#reload");
                             window.location.reload(); 
                         return;
                        }
                	  $("#payorderid").val(data.data.orderid);
                	  $("#tf").val(data.data.actual_payment*100);
                       $("#gd").val('<?=$act->act_name?>,'+getCheckItemsName());
                       $("#et").val(data.data.expire_time);
                       $("#attach").val(data.data.orderid+"_<?=$user->userInfo->user_cell?>_"+$("#registerid").val());
                       $("#payForm").submit();
                    }else{
                    	$(btn).show();
                    	  $("#alert-content-error").text(data.msg);
          		    	  $("#appleAlertError").modal();
                        }
             });
        }

    function pay(){
              <?php if($canPay):?>
             $("#payorderid").val(<?=$order->orderid?>);
        	 $("#tf").val('<?=$order->actual_payment*100?>');
        	 $("#attach").val('<?=$order->orderid."_".$user->userInfo->user_cell."_".$register->registerid?>');
        	  $("#gd").val('<?=$act->act_name?>,'+getCheckItemsName());
        	  $("#et").val('<?=$order->expire_time?>');
        	  $("#payForm").submit();
              <?php endif;?>
        }

    function cancelPay(){
    	showLoading();
    	   $.post("/act/cancelpay/<?=$register->registerid?>?"+new Date().getTime(),function(data){
               if(data.status==1){
                  	alertMsg("订单已取消！",function(){
                  		store.forEach(function(key, val) {
                  		     if(val&&val.itemid){
                  		    	val.status=0;
                  			  }
                  		})
                      	$("footer").hide();
                  		document.location.reload();
                      	});
                   }else{
                   	  $("#alert-content-error").text(data.msg);
         		    	  $("#appleAlertError").modal();
                       }
            });
        }

    function getCheckItemsName(){
    	 var gds = "";
  	   $.each($("div[class='checked']"),function(i,n){
             var item = $(n).parent().data("data");
             gds+=","+item.item_name+"￥"+item.item_price;
          });
        if(gds!=""){
  	      gds = gds.substring(1);
         }
        return gds;
        }

    function itemCheck(el){
    	   var item = $(el).data("data");
    	   var item1 ={status:1};
    	   var item1 = jQuery.extend(item1,item);
        	   var num = checkNum(el.split("_")[0]+"-"+item1.itemid+"-num");
        	   if(num){
        		   item1.item_num = num;
            	 }else{
                        return;
                	 }
    	   store.set("act-item-"+item1.itemid,item1);
    	   if(!$(el).parent().children("li").attr("multiple")){
    		   $.each($(el).parent().children("li"),function(i,n){
                   if("#"+n.id!=el){
                         $(n).children(".checked").addClass("unChecked");
                         $(n).removeAttr("checked");
                         var tmp =$(n).data("data");
                         store.remove("act-item-"+tmp.itemid);
                       }
                 });
        	   }
          
           $(el).children(".unChecked").addClass("checked");
    	   $(el).children(".unChecked").removeClass("unChecked");
    	   $.afui.loadContent("#main",false,false,"slide");
    	   
    }

    function checkNum(input){
        var num = $(input).val();
            if($.isNumeric(num)&&parseInt(num)>0){
            	$(input).val(parseInt(num));
                 return parseInt(num);
             }else{
                   alert("请输入正确的数量");
                  return false;
                 }
        }
    function itemUnCheck(el,load){
    	$(el).removeAttr("checked");
  	    var item = $(el).data("data");
  	    var item1 = store.get("act-item-"+item.itemid);
     	store.remove("act-item-"+item.itemid);
   	    $(el).children(".checked").addClass("unChecked");
  	    $(el).children(".checked").removeClass("checked");
  	    if(load){
  	      $.afui.loadContent("#main",false,false,"slide");
  	    }
      }

    function checkRegister(){
 	   <?php if(empty($user->user_cell)):?>
 	     document.location = "/bind?uid=<?=$user->uid?>&"+new Date().getTime(); 
 	   <?php else:?>
 	      $.afui.loadContent("#register",false,false,"slide");
 	   <?php endif;?>
 	   }

   function submitCers(){
	       if($("#certs_value").val()){
	    	  showLoading();
		       $.post("/act/uploadcerts/<?=$act->actid?>",{certs:$("#certs_value").val()},function(data){
                       if(data.status==1){
                    	   alertMsg("上传完成");
                    	   $.afui.loadContent("#main",false,false,"slide");
                           }else{
                        	   $("#alert-content-error").text("上传失败，请稍后重试");
            		    	     $("#appleAlertError").modal();
                               }
			       });
		       }
           return false;
	   }
     
	$(function(){
		$("#veriCode").height($("#reg-code").height());
	});
   </script>



</head>

<body>

<?php if($channel->invite_code&&!$register->registerid):?>
<div class="am-modal am-modal-prompt" tabindex="-1" id="my-prompt">
  <div class="am-modal-dialog">
    <div class="am-modal-hd">请填写邀请码</div>
    <div class="am-modal-bd">
    <div class="am-alert am-alert-danger" id="invite_error" data-am-alert="" style="display: none"><p>邀请码错误</p></div>
      <input type="text" class="am-modal-prompt-input" name="invite_code" id="invite_code">
    </div>
    <div class="am-modal-footer">
      <span class="am-btn am-btn-link" id="inviteBtn" data-am-loading="{spinner: 'circle-o-notch', loadingText: ''}"  onclick="checkInviteCode();">提交</span>
    </div>
  </div>
</div>
<script type="text/javascript">
$('#my-prompt').modal({
	closeViaDimmer: 0
  });
 
       
  function checkInviteCode(){
	  $("#invite_error").hide();
	  if($.trim($("#invite_code").val())!=""){
		  $("#inviteBtn").button("loading");
		  $("#invite_error").hide();
		  $.post("/act/channelcode/<?=$channel->channelid ?>",{invite_code:$.trim($("#invite_code").val())},function(data){
                  if(data&&data.status==1){
                	        $('#my-prompt').modal("close");
                      }else{
                             $("#invite_error").show();
                          }
			  }); 
		  }
    	   
	  }
   </script>
<?php endif;?>


<div class="view" id="mainView">
<div class="pages">
<?php if($channel->limit_range==1&&$club&&$club->clubid!=1&&$club->club_type==Club::CLUB_TYPE_CLUB&&!$member):?>
	<div class="panel" id ="join-info">
  <ul data-am-widget="gallery" class="am-gallery am-gallery-overlay" data-am-gallery="{ pureview: {target:'a'} }" style="padding:0;margin: 0">
  <li  style="padding:0">
 <div class="am-gallery-item">
 <a href="<?php if(empty($club->club_bgimage)):?>/image/paotuanzhuce/top.png<?php else:?><?=CustomHelper::CreateImageUrl($club->club_bgimage)?><?php endif;?>">
        <img src="<?php if(empty($club->club_bgimage)):?>/image/paotuanzhuce/top.png<?php else:?><?=CustomHelper::CreateImageUrl($club->club_bgimage)?><?php endif;?>" style="max-height: 200px"/>
        <?php if(!empty(trim($club->club_slogan))):?>
        <h3 class="am-gallery-title"><?=$club->club_slogan?></h3>
        <?php endif;?>
        </a>
    </div>
    </li>
</ul>
<div style="padding:14px">
<br>
              该报名通道由'<?=$club->club_name?>'创建，成为会员后才能报名!
              </div>
<div class="am-g" style="margin-top: 100px">
<a href="/clubs/<?=$club->club_eng?>/register" data-ignore="True" class="am-btn-block am-btn am-btn-success am-fr am-round">加入</a>
</div>
	</div>
<?php endif;?>



<?php if(!empty(trim($act->disclaimer))&&!$register->registerid):?>
	<div class="panel" id ="mz-info">
	<article class="am-article">
  <div class="am-article-hd" style="text-align: center;">
    <h1 class="am-article-title">免责声明</h1>
  </div>
  <div class="am-article-bd">
    <?=str_ireplace("\n", "<br>", $act->disclaimer)?>
  </div>
</article>
<div class="am-g">
<div class="am-u-sm-6">
<a class="am-btn-block am-btn am-round am-btn-default am-btn-xs" onclick="closeWechatWindow();" >不同意</a>
</div>
<div class="am-u-sm-6">
<a href="#main" class="am-btn-block am-btn am-btn-success am-fr am-round am-btn-xs">同意</a>
</div>
</div>
	</div>
<?php endif;?>



<div class="panel" id="main"  style="padding:0" data-load="main_load" selected="true">
      <?php include "header.php"?>
<section class="registerWrap" style="padding-bottom:60px">
	<div class="pd10">
	   <a href="<?=$act->act_detail_url?>" class="r_1 animats_1" data-ignore="true">赛事介绍</a>
	   <!-- 
	    <a  class="r_1 animats_1" >我要报名</a>
	     -->
              <div class="am-g">
                <div class="am-u-sm-6" ><span class="ion-ios-location icon-color"></span> <font style="font-size: 1.2rem">地点:<?=$act->country->chn_name?>·<?=$act->city->chn_name?></font></div>
                <div class="am-u-sm-6" style="text-align: right;border-left: thin #dddddd solid"><span class="am-icon-calendar icon-color"></span> <font style="font-size: 1.2rem">时间:<?=$act->act_day?></font></div>
              </div>
              <hr>
	     <div style="width: 100%;text-align: center;">
	     <table style="text-align: center;display: inline-block;">
	      <tr><td class="step-circle<?php if($register->registerid):?>-active<?php endif;?>">提交信息</td>
	      	  <td class="step-circle-link<?php if($register->registerid):?>-active<?php endif;?>">---------</td>
	      	  <td class="step-circle<?php if($canPay||($hasOrder&&$order->order_status==OrderMaster::STATUS_NORMAL)):?>-active<?php endif;?>">支付订单</td>
	      	  <td class="step-circle-link<?php if($canPay||($hasOrder&&$order->order_status==OrderMaster::STATUS_NORMAL)):?>-active<?php endif;?>">---------</td>
	      	  <td class="step-circle<?php if($hasOrder&&$order->order_status==OrderMaster::STATUS_NORMAL):?>-active<?php endif;?>">报名成功</td>
	      </tr>
	      <tr><td class="step-num<?php if($register->registerid):?>-active<?php endif;?>">1</td><td></td><td class="step-num<?php if($canPay||($hasOrder&&$order->order_status==OrderMaster::STATUS_NORMAL)):?>-active<?php endif;?>">2</td><td></td><td class="step-num<?php if($hasOrder&&$order->order_status==OrderMaster::STATUS_NORMAL):?>-active<?php endif;?>">3</td><td></td></tr>
	     </table>
	     </div>
	    	<div class="am-panel am-panel-default" id="regList_panel">
  <div class="am-panel-hd"> <div class="content-title"><span class="ion-compose am-icon-sm"> 比赛报名</span></div></div>
  <div class="am-panel-bd">
   <ul class="list" id="regList">
		        <?php foreach($items as $item):?>
  				   <?php if($item->item_type==Item::TYPE_REGISTER):?>
  				     <li class="am-g no-icon" id="reg_<?=$item->itemid?>" checked="true" <?php if($item->item_num_limit>0&&$item->item_num_limit-$item->item_buy_sum>0):?>canSelected="true"<?php endif;?> info="false">
  				    <a>
  				     <table>
  				      <tr>
  				      <td>
  				      
  				      </td>
  				      <td>
  				        <span class="am-list-item-hd "><?=$item->item_name?></span>
        				 <div class="am-list-item-text">
        				 <?php if($item->item_num_limit>0):?>
        				 <?php if($item->item_num_limit-$item->item_buy_sum<=0):?>
        				 已售完
        				 <?php elseif($item->item_num_limit-$item->item_buy_sum<10):?>
       					  数量:<?=$item->item_num_limit-$item->item_buy_sum?>
       					  <?php elseif($item->item_num_limit-$item->item_buy_sum<20):?>
       					  	  数量:仅有少量
       					 <?php else:?>
       					  	  数量:>20
       					<?php endif;?>
       					<?php endif;?>
       					</div>
  				      </td>
  				      </tr>
  				     </table>
  				     <span class="af-badge" style="color:#cb352b;"> ￥<?=$item->item_price?></span>
  				    </a>
  				     <div class="unChecked">
  				     <button><span class="ion-ios-checkmark-outline  am-icon-sm" ></span></button>
  				      </div>
     			    </li>
     			   <script>
                       $("#reg_<?=$item->itemid?>").data("data",<?=json_encode(ArrayHelper::toArray($item))?>);

          			   </script>
  				   <?php endif;?>
		        <?php endforeach;?>
		        	</ul>
		        	<?php if($item->item_num_limit==0||$item->item_num_limit-$item->item_buy_sum>0):?>
		       <ul class="list" style="margin-top:14px;">
		          <li class="am-btn-success am-radius"><a id="register_btn" onclick="checkRegister();"><table style="width: 100%"><tr><td id="reg_text"><?php if(!empty($register->registerid)):?>查看个人信息<?php else:?>填写个人信息<?php endif;?></td><td id="reg_name" align="right"><?php if(!empty($register->registerid)):?><?=$register->passport_name?><?php endif;?></td></tr></table></a></li>
		       </ul>
		       <?php endif;?>
  </div>
</div>

  	<div class="am-panel am-panel-default" id="hotelList_panel">
  <div class="am-panel-hd"> <div class="content-title"><span class="am-icon-building-o" style="vertical-align: -10%;"> 酒店住宿</span> </div></div>
  <div class="am-panel-bd">
   <ul class="list"  id="hotelList">
		        <?php foreach($items as $item):?>
  				   <?php if($item->item_type==Item::TYPE_HOTEL):?>
  				     <li class="am-g no-icon" id="hotel_<?=$item->itemid?>" canSelected="true" multiple>
  				     <a href="javascript:void(0);" onclick="hotelInfo(<?=$item->itemid?>)">
  				     <table>
  				     <tr>
  				      <td><img class="thumbed" alt="" src="<?=CustomHelper::CreateImageUrl($item->item_pic_url,"small80")?>"></td>
  				      <td>
  				       <span class="am-list-item-hd "><?=$item->item_name?></span>
        				 <div class="am-list-item-text">
       					<?=$item->hotel_type?>
       					</div>
       					 <div class="am-list-item-text">
       					<?php if($item->distance<300):?>距起点:&lt;300m
       					<?php else:?>
       					距起点:<?=$item->distance?>m
       					<?php endif;?>
       					</div>
  				      </td>
  				     </tr>
  				     </table>
  				    
     			             <span class="af-badge" style="color:#cb352b;"> ￥<?=$item->item_price?></span>
     			    </a>
     			    <div class="unChecked">
  				     <button><span class="ion-ios-checkmark-outline  am-icon-sm" ></span></button>
  				      </div>
     			    </li>
     			     <script>
                       $("#hotel_<?=$item->itemid?>").data("data",<?=json_encode(ArrayHelper::toArray($item))?>);
          			   </script>
  				   <?php endif;?>
		        <?php endforeach;?>
    </ul>
  </div>
</div>

  	<div class="am-panel am-panel-default" id="trafList_panel">
  <div class="am-panel-hd"> <div class="content-title"><span class="ion-model-s am-icon-sm"> 交通出行</span></div></div>
  <div class="am-panel-bd">
   <ul class="list" id="trafList">
		        <?php foreach($items as $item):?>
  				   <?php if($item->item_type==Item::TYPE_TRAFFIC):?>
  				     <li class="am-g no-icon" canSelected="true" id="traffic_<?=$item->itemid?>" multiple>
  				      <a href="javascript:void(0);" onclick="trafficInfo(<?=$item->itemid?>)">
  				     <table><tr>
  				       <td>
  				       <img class="thumbed" alt="" src="<?=CustomHelper::CreateImageUrl($item->item_pic_url,"small80")?>">
  				       </td>
  				     <td>
  				     <span class="am-list-item-hd "><?=$item->item_name?></span>
        				 <div class="am-list-item-text">
       					<?=$item->hotel_type?>
       					</div>
       					 <div class="am-list-item-text">
       					<?php if($item->item_num_limit>0):?>
       					 <?php if($item->item_num_limit-$item->item_buy_sum<=0):?>
        				 已售完
        				 <?php elseif($item->item_num_limit-$item->item_buy_sum<10):?>
       					  数量:<?=$item->item_num_limit-$item->item_buy_sum?>
       					  <?php elseif($item->item_num_limit-$item->item_buy_sum<20):?>
       					  	  数量:仅有少量
       					 <?php else:?>
       					  	  数量:>20
       					<?php endif;?>
       					<?php endif;?>
       					</div>
  				     </td>
  				     </tr></table>
     			             <span class="af-badge" style="color:#cb352b;"> ￥<?=$item->item_price?></span>
     			     </a>
     			     <div class="unChecked">
  				     <button><span class="ion-ios-checkmark-outline  am-icon-sm" ></span></button>
  				      </div>
     			      <input type="hidden" value="<?=$item->item_price?>" id="traffic_value"/>
     			      <script>
                       $("#traffic_<?=$item->itemid?>").data("data",<?=json_encode(ArrayHelper::toArray($item))?>);
          			   </script>
     		        </li>
  				   <?php endif;?>
		        <?php endforeach;?>
		        	</ul>
  </div>
</div>

<div class="am-panel am-panel-default" id="otherList_panel">
  <div class="am-panel-hd"> <div class="content-title"><span>其他</span></div></div>
  <div class="am-panel-bd">
   <ul class="list" id="otherList">
		        <?php foreach($items as $item):?>
  				   <?php if($item->item_type==Item::TYPE_OTHER):?>
  				     <li class="am-g no-icon" canSelected="true" id="other_<?=$item->itemid?>" multiple>
  				     <a href="javascript:void(0);" onclick="otherInfo(<?=$item->itemid?>)">
  				     <table><tr>
  				       <td>
  				       <img class="thumbed" alt="" src="<?=CustomHelper::CreateImageUrl($item->item_pic_url,"small80")?>">
  				       </td>
  				     <td>
  				     <span class="am-list-item-hd "><?=$item->item_name?></span>
       					 <div class="am-list-item-text">
       					<?php if($item->item_num_limit>0):?>
        				  <?php if($item->item_num_limit-$item->item_buy_sum<=0):?>
        				 已售完
        				 <?php elseif($item->item_num_limit-$item->item_buy_sum<10):?>
       					  数量:<?=$item->item_num_limit-$item->item_buy_sum?>
       					  <?php elseif($item->item_num_limit-$item->item_buy_sum<20):?>
       					  	  数量:仅有少量
       					 <?php else:?>
       					  	  数量:>20
       					<?php endif;?>
       					<?php endif;?>
       					</div>
  				     </td>
  				     </tr></table>
     			             <span class="af-badge" style="color:#cb352b;"> ￥<?=$item->item_price?></span>
     			     </a>
     			     <div class="unChecked">
  				     <button><span class="ion-ios-checkmark-outline  am-icon-sm" ></span></button>
  				      </div>
     			      <input type="hidden" value="<?=$item->item_price?>" id="otherList_value"/>
     			      <script>
                       $("#other_<?=$item->itemid?>").data("data",<?=json_encode(ArrayHelper::toArray($item))?>);
          			   </script>
     		        </li>
  				   <?php endif;?>
		        <?php endforeach;?>
		        	</ul>
  </div>
</div>
    <?php if($act->actid==1058):?>
      <div class="am-panel-bd">
     <ul class="list" style="margin-top:14px;">
		   <li class="am-btn-success am-radius"><a href="#bujiao">补交完赛证明</a></li>
     </ul>
     </div>
	<?php endif;?>
	 <a href="<?=$act->official_url?>" class="r_1 animats_1" data-ignore="true">官方主页</a>
	</div>
	</section>
	<footer class="am-g" style="background-color: rgba(0,0,0,0.6);position: fixed;bottom: 0px;vertical-align: middle;line-height: 44px">
        <div class="am-u-sm-2" style="font-size:1rem;color:white;text-align: right">金额</div> 
          <div class="am-u-sm-4" style="font-size:2rem;color:white" id="total"></div> 
               <?php if($canPay):?>
           <div class="am-u-sm-3" style="text-align:right"><button class="am-btn am-btn-danger  am-btn-xs" onclick="cancelPay();">取消订单</button></div>
             <div class="am-u-sm-3" style="text-align:right"><button class="am-btn am-btn-danger  am-btn-xs" onclick="pay();">去支付</button></div>
               <?php else:?>
                  <?php if($hasOrder&&$order->order_status==OrderMaster::STATUS_NORMAL):?>
                   <div class="am-u-sm-3" style="text-align:right"><span class="am-badge am-badge-success" style="font-size:1.5rem">已报名</span></div>
                  <div class="am-u-sm-3" style="padding:0 0 0 4px"><button onclick="$.afui.loadContent('#order-info')" class="am-btn am-btn-danger  am-btn-xs">查看订单</button></div>
 	   <?php else:?>
                             <div class="am-u-sm-6" style="text-align:right"><button id="btnOrder"  class="am-btn am-btn-danger am-btn-xs" onclick="submitOrder(this);">提交订单</button></div>
                  <?php endif;?>
             <?php endif?>
    </footer>
	</div>
	
	<div class="panel" id="register" data-load="reg_load">
<form action="" class="am-form" data-am-validator id="registerForm">
<input type="hidden" name="Register[actid]" id="actid" value="<?=$act->actid?>">
<input type="hidden" name="Register[courseid]" id="courseid">
<input type="hidden" name="Register[channelid]" id="channelid" value="<?=$channelId?>">
<input type="hidden" name="registerid" id="registerid" value="<?=$register->registerid?>">
  <fieldset id="reg_fdset" <?php if($hasOrder&&$order->order_status==OrderMaster::STATUS_NORMAL):?>disabled<?php endif;?>>
    <legend>报名信息</legend>
    <div class="am-alert am-alert-warning" data-am-alert id="msg" style="display: none">
</div>

<div class="am-form-group">
                <label  for="name"  style="text-align: left">姓名:<span class="i-red-12"> *</span></label>
            <input id="name"   name="Register[passport_name]" value="<?=$register["passport_name"]?>" placeholder="请填写您的姓名！" type="text" required>
        </div>
        <div class="am-form-group">
              <span class="am-help" id="regError">
                    </span>
                <label for="cell"> 手机:<span class="i-red-12"> *</span>
                </label>
                <input type="hidden" value="<?=$register->user_cell?>" id="cell_old">
                    <input required id="cell" name="Register[user_cell]" value="<?=$register->user_cell?>" class="am-form-field am-radius" placeholder="请填写您的手机号码！" type="number" pattern="^1[3|4|5|7|8][0-9]\d{4,8}$" onblur="checkCell();">                                                    
               </div>
           <div class="am-form-group" id="reg-code_div" style="display:none;text-align: left">
 <label for="reg-code">验证码:<span class="i-red-12"> *</span></label>
    <div class="am-input-group" style="width: 100%">
     <input type="number" class="am-form-field am-radius"  id="reg-code" name="code" placeholder="请输入验证码" value="" required  maxlength="6" style="width:9em;">
        <button class="am-btn am-btn-danger" style="width:8em" type="button" onclick="getResCode();" id="veriCode">获取验证码</button>
    </div>
    </div>
    
    <div class="am-form-group">
                <label> 性别:<span class="i-red-12"> *</span></label><br>
                <select name="Register[user_gender]" class="am-form-field am-radius">
                <option value="1" <?php if($register->user_gender==1):?>selected="selected"<?php endif;?>>男</option>
                 <option value="2" <?php if($register->user_gender==2):?>selected="selected"<?php endif;?>>女</option>
                </select>
      </div>
       <div class="am-form-group">
                <label  for="nationality">国籍
                  <span class="i-red-12"> *</span>
                </label>
                    <input required class="am-form-field am-radius" id="nationality" name="Register[nationality]" value="<?=$register->nationality?$register->nationality:"中国"?>" placeholder="请填写您的国籍！" type="text">
            </div>
             <div class="am-form-group">
                <label  for="birthday">出生日期(如1985-01-01)<span class="i-red-12"> *</span></label>
                <input required class="am-form-field am-radius" id="birthday" name="Register[birthday]" value="<?=$register->birthday?>" placeholder="请选择您的生日"  type="date">
            </div>
             <div class="am-form-group">
                <label  for="id_type">证件类型
                  <span class="i-red-12"> *</span>
                </label>
                 <select id="id_type" name="Register[id_type]" class="am-form-field am-radius" required>
                        <option value="1" <?php if($register->id_type=="1"){echo "selected=\"selected\"";}?>>身份证</option>
                        <option value="2" <?php if($register->id_type=="2"){echo "selected=\"selected\"";}?>>护照</option>
                        <option value="3" <?php if($register->id_type=="3"){echo "selected=\"selected\"";}?>>台胞证</option>
                        <option value="4" <?php if($register->id_type=="4"){echo "selected=\"selected\"";}?>>港澳通行证</option>
                        <option value="0" <?php if($register->id_type=="0"){echo "selected=\"selected\"";}?>>其它</option>
                    </select>
            </div>
            
            <div class="am-form-group am-form-file">
                <label  for="id_number">证件号码
                  <span class="i-red-12"> *</span>
                </label>
                 <input required class="am-form-field am-radius" id="id_number" name="Register[id_number]" value="<?=$register->id_number?>" placeholder="请填写您的身份证号码！" type="text">
            </div>
            <?php if($act->actid!=1305):?> 
            <div class="am-form-group" id="id_copy_div">
               <p style="margin-bottom:4px"> 身份证复印件正面<span class="i-red-12"> *</span></p>  
               <input type="hidden"  name="Register[id_copy]"  id="id_copy_value" value="<?=$register->id_copy?>">
                <input type="file" <?php if($register->id_copy==null):?> required<?php endif;?> name="file" id="id_copy"  accept="image/gif,image/png,image/jpg,image/jpeg,image/bmp">
                 <img alt="" src="<?=CustomHelper::CreateImageUrl($register->id_copy,"small80")?>" id="id_copy_pre" style="width:50px;height:50px;margin-top:4px" onerror="this.style.display='none'" >
            </div>
            <?php endif?>
              <br>
              <!--
             <div class="am-form-group" id="id_copy_back_div">
                  <p style="margin-bottom:4px"> 身份证复印件反面<span class="i-red-12"> *</span></p>  
                <input type="hidden"  name="Register[id_copy_back]"  id="id_copy_back_value" value="<?=$register->id_copy_back?>">
                <input type="file" <?php if($register->id_copy_back==null):?> required<?php endif;?> name="file" id="id_copy_back"  accept="image/gif,image/png,image/jpg,image/jpeg,image/bmp" >
                  <img alt="" src="<?=CustomHelper::CreateImageUrl($register->id_copy_back,"small80")?>" id="id_copy_back_pre" style="width:50px;height:50px;margin-top:4px" onerror="this.style.display='none'" >
            </div>
              -->
             <div class="am-form-group">
                <label  for="user_email">邮箱
                  <span class="i-red-12"> *</span>
                </label>
                 <input required class="am-form-field am-radius" id="user_email" name="Register[user_email]" value="<?=$register->user_email?>" placeholder="请填写您的邮箱！" type="email">
            </div>
            <div class="am-form-group">
                <label  for="address">通讯地址
                  <span class="i-red-12"> *</span>
                </label>
                 <input required class="am-form-field am-radius" id="address" name="Register[address]" value="<?=$register->address?>" placeholder="请填写您的通讯地址" type="text">
            </div>
             <div class="am-form-group">
                <label  for="emerge_name">紧急联系人
                  <span class="i-red-12"> *</span>
                </label>
                 <input required class="am-form-field am-radius" id="emerge_name" name="Register[emerge_name]" value="<?=$register->emerge_name?>" placeholder="紧急联系人姓名" type="text">
            </div>
             <div class="am-form-group">
                <label  for="emerge_ship">关系
                  <span class="i-red-12"> *</span>
                </label>
                 <input required class="am-form-field am-radius" id="emerge_ship" name="Register[emerge_ship]" value="<?=$register->emerge_ship?>" placeholder="关系" type="text">
            </div>
             <div class="am-form-group">
                <label  for="emerge_cell">联系方式
                  <span class="i-red-12"> *</span>
                </label>
                 <input required class="am-form-field am-radius" id="emerge_cell" name="Register[emerge_cell]" value="<?=$register->emerge_cell?>" placeholder="联系方式" type="text">
            </div>
            <div class="am-form-group">
                <label for="tshirt_size">
                                                               上衣尺码:<span class="i-red-12"> *</span>
                </label>
                    <select required name="Register[tshirt_size]" id="tshirt_size" class="am-form-field am-radius">
                        <option  value="XS" <?php if($register->tshirt_size=="XS"){echo "selected=\"selected\"";}?>>XS</option>
                        <option  value="S" <?php if($register->tshirt_size=="S"){echo "selected=\"selected\"";}?>>S</option>
                        <option value="M" <?php if($register->tshirt_size=="M"){echo "selected=\"selected\"";}?>>M</option>
                        <option  value="L" <?php if($register->tshirt_size=="L"){echo "selected=\"selected\"";}?>>L</option>
                        <option  value="XL" <?php if($register->tshirt_size=="XL"){echo "selected=\"selected\"";}?>>XL</option>
                        <option  value="XXL" <?php if($register->tshirt_size=="XXL"){echo "selected=\"selected\"";}?>>XXL</option>
                        <option  value="XXXL" <?php if($register->tshirt_size=="XXXL"){echo "selected=\"selected\"";}?>>XXXL</option>
                    </select>
            </div>
            
              <div class="am-form-group">
                <label  for="medical_history">既往病史
                </label>
                 <textarea class="am-form-field am-radius" id="medical_history" name="Register[medical_history]" value="<?=$register->medical_history?>" placeholder="请填写既往病史"></textarea>
            </div>
           <!--
             <div class="am-form-group" id="medical_report_div">
              <p style="margin-bottom:4px"> 体检证明<span class="i-red-12"> *</span></p>  
                <input type="hidden"  name="Register[medical_report]"  id="medical_report_value" value="<?=$register->medical_report?>">
                <input type="file"  name="file" id="medical_report"  accept="image/gif,image/png,image/jpg,image/jpeg,image/bmp" >
                 <img alt="" src="<?=CustomHelper::CreateImageUrl($register->medical_report,"small80")?>" id="medical_report_pre" style="width:50px;height:50px;margin-top:4px"  onerror="this.style.display='none'" >
            </div>
          -->
          
           <?php if($act->actid==1234):?>0
            <div class="am-form-group am-form-file">
             <label for="certs" style="margin-top:14px"> <i class="am-icon-cloud-upload"></i> 点击上传完赛证明<span class="i-red-12"> *</span></label>
                 <input type="hidden"  name="Register[certs]"  id="certs_value" >
                 <input  type="file" required  name="file[]" id="certs" style="height:30px" accept="image/gif,image/png,image/jpg,image/jpeg,image/bmp" multiple="multiple">
            </div>
            <?php endif?>
    </fieldset>
    </form>
     <?php if(!$hasOrder||$order->order_status==OrderMaster::STATUS_CANCEL||$order->order_status==OrderMaster::STATUS_WAIT_PAY):?>
     <div class="am-g">
       <div class="am-u-sm-6"><button type="button" class="am-btn am-btn-success am-center am-btn-block am-round" data-am-loading="{spinner: 'circle-o-notch'}" id="btnApply" onclick="apply();">保存</button></div>
        <div class="am-u-sm-6"><a href="#main" class="am-btn am-btn-default am-center am-btn-block am-round">取消</a></div>
     </div>
     <?php endif;?>
	</div>
	<div class="panel" id ="hotel-info"  style="padding:0">
	
	</div>
	<div class="panel" id ="traffic-info"style="padding:0">
	
	</div>
	
	<div class="panel" id ="other-info" style="padding:0">
	
	</div>
	<!--  
	<div class="panel" id ="bujiao" style="padding:0">
	     <form action="" class="am-form" data-am-validator onsubmit="return submitCers();">
  <fieldset>
    <legend>上传完赛证明</legend>
     <div class="am-form-group am-form-file">
             <label for="certs"> <i class="am-icon-cloud-upload"></i> 点击上传完赛证明</label>
                 <input type="hidden"  name="certs_img"  id="certs_value" >
                 <input  type="file"  name="file[]" id="certs" style="height:30px" accept="image/gif,image/png,image/jpg,image/jpeg,image/bmp" multiple="multiple">
            </div>
            <br>
             <br>
             <input type="submit" class="am-btn am-btn-success am-center am-btn-block" value="提交">
 </fieldset>
   
 </form>
	</div>
	
	-->
	
	<div class="panel" id ="order-info"  style="padding:0">
	 <fieldset style="margin-top:14px">
    <legend style="margin-bottom: 0"><span class="ion-ios-paper-outline am-icon-sm icon-color"> 报名订单</span></legend>
    <ul class="am-list am-list-static  order-info">
     <li class="am-g am-g-collapse">
       <div class="am-u-sm-4">报名号</div><div class="am-u-sm-8" style="font-size:2rem;color:black;font-weight: bold;"><?=$register->registerid?></div>
     </li>
     <li class="am-g am-g-collapse">
       <div class="am-u-sm-4">订单号</div><div class="am-u-sm-8"><?=$register->order->trade_no?></div>
     </li>
     <li class="am-g am-g-collapse">
       <div class="am-u-sm-4">订单金额</div><div class="am-u-sm-8">￥<?=$register->order->amount?></div>
     </li>
     <li class="am-g am-g-collapse">
       <div class="am-u-sm-4">报名方式</div><div class="am-u-sm-8"><?php if($register->order->payment_type=="微信支付wechat"):?>微信报名<?php else:?>APP报名<?php endif;?></div>
     </li>
    </ul>
       <div data-am-widget="list_news" class="am-list-news am-list-news-default">
       <div class="am-list-news-hd am-cf">
       <span class="ion-ios-list-outline am-icon-sm icon-color"> 订单内容</span>
       </div>
     <ul class="am-list am-list-static order-info" style="margin-top:0">
     <?php if($cur):?>
     <li class="am-g am-g-collapse">
       <div class="am-u-sm-3">科目</div><div class="am-u-sm-6 am-text-truncate"><?=$cur->item_name?></div><div class="am-u-sm-3">￥<?=$cur->item_price?></h3></div>
     </li>
     <?php endif;?>
       <?php if(count($hotel)>0):?>
        <?php foreach ($hotel as $h):?>
     <li class="am-g am-g-collapse">
       <div class="am-u-sm-3">酒店</div><div class="am-u-sm-6 am-text-truncate">
       <a href="javascript:void(0);" onclick="hotelInfo(<?=$h->itemInfo->itemid?>);"><?=$h->itemInfo->item_name?></a>
       </div>
       <div class="am-u-sm-3">￥<?=$h->item_price."×".$h->item_num?></div>
     </li>
      <?php endforeach;?>
     <?php endif;?>
         <?php if(count($traffic)>0):?>
         <?php foreach ($traffic as $t):?>
     <li class="am-g am-g-collapse">
     <div class="am-u-sm-3">交通</div>
       <div class="am-u-sm-6 am-text-truncate">
         <a href="javascript:void(0);" onclick="trafficInfo(<?=$t->itemInfo->itemid?>);"><?=$t->itemInfo->item_name?></a>
       </div>
       <div class="am-u-sm-3">￥<?=$t->item_price."×".$t->item_num?></div>
     </li>
      <?php endforeach;?>
     <?php endif;?>
     
      <?php if(count($other)>0):?>
        <?php foreach ($other as $o):?>
     <li class="am-g am-g-collapse">
       <div class="am-u-sm-3">其他</div><div class="am-u-sm-6 am-text-truncate">
        <a href="javascript:void(0);" onclick="otherInfo(<?=$o->itemInfo->itemid?>);"><?=$o->itemInfo->item_name?></a>
       </div>
       <div class="am-u-sm-3">￥<?=$o->item_price."×".$o->item_num?></div>
     </li>
     <?php endforeach;?>
     <?php endif;?>
    </ul>
    </div>
  </fieldset>
	</div>
	
	</div>
</div>

		<div class="am-modal am-modal-alert" tabindex="-1" id="appleAlert">
			<div class="am-modal-dialog">
				<div class="am-modal-hd">提示</div>
				<div class="am-modal-bd" id="alert-content"></div>
				<div class="am-modal-footer">
					<span class="am-modal-btn">确定</span>
				</div>
			</div>
		</div>
		
		
		<div class="am-modal am-modal-alert" tabindex="-1"
			id="appleAlertError">
			<div class="am-modal-dialog">
				<div class="am-modal-hd">错误</div>
				<div class="am-modal-bd" id="alert-content-error"></div>
				<div class="am-modal-footer">
					<span class="am-modal-btn">确定</span>
				</div>
			</div>
		</div>

		<div class="am-modal am-modal-loading am-modal-no-btn" tabindex="-1"
			id="modal-loading">
			<div class="am-modal-dialog">
				<div class="am-modal-hd"></div>
				<div class="am-modal-bd">
					<span class="am-icon-spinner am-icon-spin"></span>
				</div>
			</div>
		</div>
		

		<form action="/act/uploadimg/123" target="upFrame" method="post"
			enctype="multipart/form-data" id="uploadForm" style="display:none">
		</form>
		<iframe id="upFrame" name="upFrame" onload="upLoad();"
			style="display:none;width: 100%"></iframe>
 <input type="hidden" id="items">
  <input type="hidden" id="nums">
 <?php if($hasOrder):?>
 <input type="hidden" id="orderid" value="<?=$order->orderid?>">
 <?php endif;?>

 
 <form action="/wxpay/index?showwxpaytitle=1" method="post" id="payForm" style="display: none">
<input type="text" name="openid" value="<?=Yii::$app->session["openid"]?>">
<input type="text" name="goodDesc"  id="gd">
<input type="text" name="orderid"  id="payorderid" value="">
<input type="text" name="total_fee"  id="tf">
<input type="text" name="expire_time"  id="et">
<input type="text" name="attach" id="attach">
<input type="text" name="notify_url" value="<?=Yii::$app->request->hostInfo?>/wxpay/registernotify">
</form>
 
</body>
</html>
	<script id="certs-template" type="text/x-handlebars-template"> 
			<ul data-am-widget="gallery" class="am-gallery am-avg-sm-4
  				am-avg-md-6 am-avg-lg-8 am-gallery-default" data-am-gallery="{ pureview: 1}">
					{{#each images}} 
						<li>
    						<div class="am-gallery-item">
        							<img src="{{pre}}" data-rel="{{image}}" style="width:50px;height:50px"/>
   							 </div>
  						</li>
  					{{/each}}
					</ul>
				</script>

<script type="text/javascript">

	$("#otherList_panel").contents().find("img").on("error",function(){
           $(this).attr("src","/image/other_default.png");
		});

	$("#hotelList_panel").contents().find("img").on("error",function(){
           $(this).attr("src","/image/hotel_default.png");
		});

	$("#trafList_panel").contents().find("img").on("error",function(){
        $(this).attr("src","/image/traffic_default.png");
	});

	store.forEach(function(key, val) {
	     if(val&&val.itemid){
	    	  $("#hotel_"+val.itemid).attr("checked",true);
              $("#hotel_"+val.itemid).children(".unChecked").addClass("checked");
              $("#hotel_"+val.itemid).children(".unChecked").removeClass("unChecked");
              $("#traffic_"+val.itemid).attr("checked",true);
              $("#traffic_"+val.itemid).children(".unChecked").addClass("checked");
              $("#traffic_"+val.itemid).children(".unChecked").removeClass("unChecked");
              $("#reg_"+val.itemid).attr("checked",true);
              $("#reg_"+val.itemid).children(".unChecked").addClass("checked");
              $("#reg_"+val.itemid).children(".unChecked").removeClass("unChecked");
              $("#other_"+val.itemid).attr("checked",true);
              $("#other_"+val.itemid).children(".unChecked").addClass("checked");
              $("#other_"+val.itemid).children(".unChecked").removeClass("unChecked");
		   }
	})
	
    if($("#regList").children().children("div[class='checked']").length==0){
    	  var el =$("#regList").children().children("div[class='unChecked']")[0];
    	    $(el).parent().attr("checked",true);
            $(el).addClass("checked");
            $(el).removeClass("unChecked");
        }

	main_load();

	$.each($("ul"),function(i,n){
          if($(n).children("li").length==0){
                $("#"+n.id+"_panel").hide();
              }
		});
    	
	$("li[canSelected]").children("a").on("click",function(){
		var curr = $(this).parent();
		curr.attr("checked",true);
		var item = curr.data("data");
		<?php if($modify):?>
		if(curr.attr("info")=="false"){
			var item1={};
			 $.extend(item1,item);
	    	store.set("act-item-"+item.itemid,item1);
			curr.children(".unChecked").addClass("checked");
			curr.children(".unChecked").removeClass("unChecked");
		}
		<?php endif;?>
          var lies = curr.parent().children("li");
          $.each(lies,function(i,n){
                 if(n.id!= curr.attr("id")){
                	 if(curr.attr("info")=="false"){
                			<?php if($modify):?>
                	   store.remove("act-item-"+$(n).data("data").itemid);
                	   $(n).children(".checked").addClass("unChecked");
                       $(n).children(".checked").removeClass("checked");
                       main_load();
                       <?php endif;?>
                	 }
                     $(n).removeAttr("checked");
                  
                   }
              });
		});

	var dispatchPanelEvent=function(fnc,myPanel){
	    if (typeof fnc === "string" && window[fnc]) {
	        return window[fnc](myPanel);
	    }
	};
	$(document).on("panelload",function(e){
	   var hasLoad=$(e.target).attr("data-load");

	   return dispatchPanelEvent(hasLoad,e.target);
	});

	$(document).on("panelunload",function(e){
	   var hasLoad=$(e.target).attr("data-unload");

	   return dispatchPanelEvent(hasLoad,e.target);
	});
	//fileupload("id_copy_back");
	fileupload("id_copy");
	//fileupload("medical_report");
	fileuploadMulti("certs");

	var source   = $("#certs-template").html();  
	  var template = Handlebars.compile(source);
	  $("#certs").next().remove();
	  <?php 
	  $images=array();
	      foreach ($certs as $cert){
	      	array_push($images, ["pre"=>$cert->paper_url,"image"=>$cert->paper_url]);
	      }
 	   ?>
 	   var images = <?=json_encode($images)?>;
 	   if(images&&images.length>0){
 		  $("#certs").removeAttr("required");
 	 	   }
	  $("#certs").after(template({images:images})); 
	  $.AMUI.gallery.init();
</script>