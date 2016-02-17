<?php
use common\component\CustomHelper;
use yii\helpers\ArrayHelper;
use paotuan_wechat\component\Util;
header ( "Content-type: text/html; charset=utf-8" );
$r=0;
if($mi->mileage!=0){
	$r = ceil($mi->duration/$mi->mileage);
	$desc = "距离:".$mi->mileage."KM\n";
	if(!empty($mi->duration)&&$mi->duration!=0){
		$desc = $desc."用时:".Util::getTimeFromSec($mi->duration)."\n";
		$desc = $desc."配速:".Util::getTimeFromSec($r)."/KM";
	}
}
$imgURL="";
if(count($mi->albums)>0){
	$imgURL =CustomHelper::CreateImageUrl($mi->albums[0]->image_url,"small80");
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title><?=$mi->user->nick_name?></title>
<link href="/css/paotuanzhuce/app.css?<?=time()?>" type="text/css"
	rel="stylesheet" />
<link rel="stylesheet" href="/css/amazeui.min.css" />
<link href="/css/wechat.css?<?=time()?>" type="text/css"	rel="stylesheet" />
<link href="/css/ionicons/css/ionicons.min.css" type="text/css"	rel="stylesheet" />
<script type="text/javascript"
	src="/js/paotuanzhuce/jquery-1.9.1.min.js"></script>
<script src="/js/amazeui.min.js"></script>
<script type="text/javascript" src="/js/paotuanzhuce/app.js?<?=time()?>"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>

<!-- 日期控件 -->
<script type="text/javascript" src="/js/wechat.js?<?=time()?>"></script>
<script src="/datetime/mobiscroll_002.js" type="text/javascript"></script>
<script src="/datetime/mobiscroll_004.js" type="text/javascript"></script>
<link href="/datetime/mobiscroll_002.css" rel="stylesheet" type="text/css">
<link href="/datetime/mobiscroll.css" rel="stylesheet" type="text/css">
<script src="/datetime/mobiscroll.js" type="text/javascript"></script>
<script src="/datetime/mobiscroll_003.js" type="text/javascript"></script>
<script src="/datetime/mobiscroll_005.js" type="text/javascript"></script>
<link href="/datetime/mobiscroll_003.css" rel="stylesheet" type="text/css">

<style type="text/css">
.am-form select, .am-form textarea, .am-form input[type="text"],
	.am-form input[type="password"], .am-form input[type="datetime"],
	.am-form input[type="datetime-local"], .am-form input[type="date"],
	.am-form input[type="month"], .am-form input[type="time"], .am-form input[type="week"],
	.am-form input[type="number"], .am-form input[type="email"], .am-form input[type="url"],
	.am-form input[type="search"], .am-form input[type="tel"], .am-form input[type="color"],
	.am-form-field {
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

.am-panel {
	-webkit-box-shadow: none;
	box-shadow: none;
}

.icon-color{
  color:#cb352b
}

#chartType a{
  padding:0;
  width:48px;
  border-left:none;
  border-top:none;
  border-bottom:none;
  border-right:thin inherit solid;
  background-color: inherit;
}

#chartType a:last-child{
  border-right:none;
}

#chartType .cur{
    color:red;
}

.me-total {
text-align: center;
padding-top: 14px;
}

.me-total>div:first-child{
 border-right:thin #eee solid; 
}

.me-total .desc{
   color:graytext;
   font-size:1.2rem;
   display: inline-block;
    width: 100%;
}

.me-total .sum{
   font-size:2rem;
   display: inline-block;
      width: 100%;
}

.test{width:0; height:0; border:7px solid; 
border-color:rgba(0,0,0,0) rgba(0,0,0,0) rgba(0,0,0,0.6) rgba(0,0,0,0);
 position: absolute;
 top: -14px;
 right:14px
}

/*日期控制自定义控制*/
.dw-persp, .dwo{
    width:0px;
	height:0px;
	position:initial;
	z-index:0;
}
.dw{
	z-index:1200; 
}

/*弹出层表单控制*/
</style>
<script type="text/javascript">
   wx.config({
	    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
	    appId: '<?=$signPackage["appId"];?>',
	    timestamp: <?=$signPackage["timestamp"];?>,
	    nonceStr: '<?=$signPackage["nonceStr"];?>',
	    signature:'<?=$signPackage["signature"];?>',
	    jsApiList: ['onMenuShareTimeline','closeWindow','previewImage',"onMenuShareAppMessage"]
	});

   $.ajaxSetup({
	   type: "POST",
	   beforeSend:function(){
		    
		   },
	   error:function(req){
		   },
	   complete:function(req, textStatus){
		   $("#modal-loading").modal("close");
		   },
		   statusCode: {500: function() {
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

   wx.ready(function(){
	    // 获取“分享给朋友”按钮点击状态及自定义分享内容接口
	    wx.onMenuShareAppMessage({
	    title: <?=json_encode($mi->user->nick_name)?>+"的跑步打卡", // 分享标题
	    desc: <?=json_encode($desc)?>, // 分享描述
	    link: '<?=Yii::$app->request->absoluteUrl?>', // 分享链接
	    imgUrl: '<?=$imgURL?>', // 分享图标
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
	        title: "<?=$mi->user->nick_name?>的跑步打卡", // 分享标题
	        link: '<?=Yii::$app->request->absoluteUrl?>', // 分享链接
	        imgUrl: '<?=$imgURL?>', // 分享图标
	        success: function () { 
	            // 用户确认分享后执行的回调函数
	        },
	        cancel: function () { 
	            // 用户取消分享后执行的回调函数
	        }
	    });
	});


	function showError(text){
		$("#alert-content-error").text(text);
    	 $("#appleAlertError").modal();
		}

	function closeWechatWindow(){
		wx.closeWindow();
		}

   $(function(){
         $(window).smoothScroll();
         $(".stepTop-mileage-info").height($(window).height());

         //星之健身活动
         $(".action_xzjs").click(function(){
             var mileage_count = $(this).attr('data-count');
             if(mileage_count == 10)
             {
                 window.location.href="http://wx.stargym.com.cn/index.php?/tice/bmstep1";
//             	 $("#xzjs-modal").modal();
             }
             else if(mileage_count == 20)
             {
            	 $.post('/runners/xzjscoupon',function(data){
         		    if(data.status==1)
         		    {
         		    	showError(data.message);
         		    }
         		    else
         		    {
             		    $(".coupon_code").html(data.result);
         		    	$("#week-modal").modal();
         		    }
            	 });
             }
         });

//          $("#birthday").attr("min",new Date());
         initDateTimePicker(null,"#birthday","date");

         $("#reserve_time").attr("min",new Date());
         initDateTimePicker(null,"#reserve_time","date");
	   });
   </script>



</head>

<body style="background-image: url(/image/paotuanzhuce/bg_01.jpg);background-position:top;background-size:100%;">
	<div class="stepTop stepTop-mileage-info" >
		<div class="topInfo" style="">
		<div class="userPhoto"  style="background-image:url(<?=$mi->user->user_face?>);display:inline-block;background-size:contain"></div>
		   <div class="userName"><?=$mi->user->nick_name?></div>
		   <div class="am-center am-radius am-animation-scale-up" style="width: 80%;background-color:rgba(0,0,0,0.6);color:#FFF;padding:15px 0">
		     <div style="font-size: 1rem;padding:4px"><?=$mi->mileage_date?></div>
		     <div style="font-size:80px;font-weight: bold;line-height: 80px"><?=$mi->mileage?></div>
		     <div class="am-g" style="width:80%;line-height: 35px;font-size: 1rem;">
		       <div class="am-u-sm-4" ><hr data-am-widget="divider" style="border-top-color: white;background-color: white;" class="am-divider am-divider-default"/></div>
		        <div class="am-u-sm-4 km">KM</div>
		         <div class="am-u-sm-4"><hr data-am-widget="divider" style="border-top-color: white;background-color: white;"  class="am-divider am-divider-default"/></div>
		     </div>
		      <div class="am-g" style="width:80%;line-height: 35px;font-size: 1rem;">
		       <div class="am-u-sm-6" ><?php if( $r!=0):?><i class="ion-ios-timer-outline"></i> <?=Util::getTimeFromSec($r);?>/KM<?php endif;?></div>
		        <div class="am-u-sm-6"><?php if(!empty($mi->duration)):?><i class="ion-ios-stopwatch-outline"></i> <?=Util::getTimeFromSec($mi->duration);?><?php endif;?></div>
		     </div>
		     <?php if (($mileage_count == 10 || $mileage_count == 20)&&false):?>
		     <div class="am-g action_xzjs" data-count="<?=$mileage_count?>" style="width:88%;line-height: 35px;font-size: 17px;border-radius:5px;background-color:#0099da;cursor:pointer;">
		          <?php if ($mileage_count == 10):?>恭喜获得星之健身免费体测券<?php endif;?>
		          <?php if ($mileage_count == 20):?>恭喜获得星之健身免费周健身卡<?php endif;?>
		     </div>
		     <?php endif;?>
		      <div class="am-g" style="width:80%;font-size: 1rem">
		     <?php if($mi->from==2):?>
		                                   来自&nbsp;咕咚 
		     <?php endif;?>
		       <?php if($mi->from==3):?>
		                                   来自&nbsp;虎扑 
		     <?php endif;?>
		       <?php if($mi->from==4):?>
		                                   来自&nbsp;益动
		     <?php endif;?>
		     </div>
		  </div>
		   <ul data-am-widget="gallery" class="am-center am-gallery am-avg-sm-3 am-avg-md-5 am-avg-lg-7 am-gallery-default"   style="width:80%">
     <?php foreach ($mi->albums as $album):?>
     <?php
     $imgs = [];
     if(stripos($album->image_url,"http://")===false){
						$imgs["image_url"] = "http://xiaoi.b0.upaiyun.com".$album->image_url;
						$imgs["append"]="!m.pre";
					}else{
						$imgs["image_url"] = $album->image_url;
					}
	  ?>
  <li>
    <div class="am-gallery-item">
        <img src="<?=$imgs["image_url"].$imgs["append"]?>" data-rel="<?=$imgs["image_url"]?>" style="width: 60px;height: 60px" />
    </div>
  </li>

		        <?php endforeach;?>
		        </ul>
		</div>
		
		<div id="share" class="am-radius" style="text-align: center;position: absolute;top:10px;right:4px;background-color: rgba(0,0,0,0.6);font-size:1.5rem;padding:4px;color:white">
      <div class="test"></div>
                           点击菜单<br>分享给朋友们
     </div>
</div>


	<div data-am-widget="navbar" class="am-navbar am-cf am-navbar-default "
id="">
  <ul class="am-navbar-nav am-cf am-avg-sm-1">
      <li>
      <a href="/runners/mileages" class="">
        <span class="ion-android-clipboard am-icon-sm"></span>
        <span class="am-navbar-label">打卡</span>
      </a>
    </li>
  </ul>
</div>

<!-- 星之健身免费体测活动 -->
<!-- 
<div class="am-modal am-modal-no-btn" tabindex="-1" id="xzjs-modal" style="text-align: left;font-size:18px;width:90%;margin-left:auto;margin-right:auto;left:5%;">
    <div class="am-modal-dialog">
        <div class="am-panel am-panel-primary">
            <div class="am-panel-hd" style="text-align:center;">
                <span class="am-panel-title">
                                    星之健身免费体测
                </span>
                <a href="javascript: void(0)" class="am-close am-close-spin am-fr" data-am-modal-close>&times;</a>
            </div>
            <div class="am-panel-bd">
                <form class="am-form am-form-horizontal" method="post" id="xzjs_form" action="/runners/xzjsaction" data-am-validator>
                    <label for="item_name" class="am-form-label">姓名：</label>
                    <input type="text" id="passport_name" name="Partner[passport_name]" value="<?=$partner?$partner->passport_name:''?>" class="am-form-field" placeholder="姓名" <?php if ($partner):?>readonly<?php endif;?> required >
                    <label for="cell" class="am-form-label" style="margin-top:10px;">手机号：</label>
                    <input type="text" id="cell" name="Partner[cell]" class="am-form-field js-pattern-mobile" value="<?=$partner?$partner->cell:''?>" placeholder="手机号" <?php if ($partner):?>readonly<?php endif;?> required >
                    <label for="birthday" class="am-form-label" style="margin-top:10px;">生日：</label>
                    <input type="text" <?php if (!$partner):?>id="birthday"<?php endif;?> name="Partner[birthday]" class="am-form-field" value="<?=$partner?$partner->birthday:''?>" placeholder="生日"  readonly required >
                    <label for="address" class="am-form-label" style="margin-top:10px;">预约驿站：</label>
                    <select name="Partner[address]" class="am-form-field" <?php if ($partner):?>disabled="disabled"<?php endif;?> required >
                        <?php $address = \Yii::$app->params['xzjs_address'];?>
                        <?php if ($address):?>
                        <?php foreach ($address as $add):?>
                            <?php if ($partner && $partner->address == $add):?>
                                <option value="<?=$add?>" selected="selected"><?=$add?></option>
                            <?php else:?>
                                <option value="<?=$add?>"><?=$add?></option>
                            <?php endif;?>
                        <?php endforeach;?>
                        <?php endif;?>
                    </select>
                    <label for="reserve_time" class="am-form-label" style="margin-top:10px;">预约时间：</label>
                    <input type="text" <?php if (!$partner):?>id="reserve_time"<?php endif;?> name="Partner[reserve_time]" class="am-form-field" value="<?=$partner?$partner->reserve_time:''?>" placeholder="预约时间" readonly required >
                    <button type="button" <?php if ($partner):?>disabled="disabled"<?php else:?>id="xzjs-btn"<?php endif;?> class="am-btn am-btn-primary am-btn-block" style="margin-top:10px;">提交</button>
                </form>
            </div>
        </div>
    </div>
</div> -->

<!-- 星之健身周健身卡活动 -->
<div class="am-modal am-modal-no-btn" tabindex="-1" id="week-modal" style="text-align: left;font-size:18px;">
    <div class="am-modal-dialog">
        <div class="am-panel am-panel-primary">
            <div class="am-panel-hd" style="text-align:center;">
                <span class="am-panel-title">
                                    星之健身免费周健身卡
                </span>
                <a href="javascript: void(0)" class="am-close am-close-spin am-fr" data-am-modal-close>&times;</a>
            </div>
            <div class="am-panel-bd" style="text-align:center;">
                <div style="width:68%;margin-left:auto;margin-right:auto;border:1px solid #0099da;text-align:center;border-radius:7px;font-size:19px;margin-top:10px;margin-bottom:8px;">
                    <div style="border-right:1px solid #0099da;height:100%;width:50%;float:left;color:#000000;">优惠码</div>
                    <div class="coupon_code" style="color:#0099da;"></div>
                </div>
                <div style="margin-top:20px;font-size:15px;text-align:left;color:#999999;">
                                    使用说明：1. 本卡使用时间为办卡之日起连续7天； 2. 办卡持有人需凭法定有效证件（身份证，护照，驾驶证）至指定门店中任意一家办理登记，仅限在该登记门店享受服务； 3. 星之现有会员无法使用，已办理登记的健身七天卡持有人不可再次在星之健身俱乐部所有门店使用同种类健身七天卡； 4. 持卡人可连续七天在使用门店的普通区域使用所有免费项目,不含游泳； 5. 持卡人必须年满16周岁，来俱乐部运动请自备运动服装和毛巾等沐浴用品。
                </div>
            </div>
        </div>
    </div>
</div>


<div class="am-modal am-modal-alert" tabindex="-1" id="appleAlertError">
	<div class="am-modal-dialog">
		<div class="am-modal-hd">错误</div>
		<div class="am-modal-bd" id="alert-content-error"></div>
		<div class="am-modal-footer">
			<span class="am-modal-btn">确定</span>
		</div>
	</div>
</div>
</body>
</html>

<script>
    if ($.AMUI && $.AMUI.validator) {
        $.AMUI.validator.patterns.mobile = /^\s*1\d{10}\s*$/;
      }
	app.showAndHide();
	 $(".am-gallery-item").on("click", "img",function(event){
		 var urls = new Array();
		 $.each($(".am-gallery-item").children("img"),function(i,n){
			 urls.push($(this).attr("data-rel"));
			 });
    	  wx.previewImage({
    	    current:$(this).attr("data-rel"), // 当前显示的图片链接
    	    urls: urls // 需要预览的图片链接列表
    	   });
        });

     $("#share").addClass("am-animation-shake");
     setTimeout(function(){
    	  $("#share").removeClass("am-animation-shake");
    	 $("#share").addClass("am-animation-slide-top am-animation-reverse");
         },3000);
    
     $(function(){
    	 $("#xzjs-btn").on('click',function(e){
    		 var reg = /^1[3|4|5|8][0-9]\d{4,8}$/;
        	 if(!reg.test($.trim($('#cell').val())))
        	 {
            	 
            	 return false;
        	 }
        	 $F = $("#xzjs_form");
        	 if(!$F.data('amui.validator').isFormValid()){
               	return false;
             }
    		 $.post($F.attr('action'),$F.serialize(),function(data){
    			$("#modal-loading").modal("close");
 			    if(data.status == 1)
 			    {
 			    	$("#alert-content-error").text("服务器异常，请稍候重试或联系管理员！");
 			    }
 			    else
 			    {
 			    	$("#xzjs-modal").modal("close");
 			    }
 			 });
         });
     });
</script>