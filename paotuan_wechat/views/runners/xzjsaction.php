<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>星之健身活动</title>
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

.action{
	background-color:#0099da;
	color:white;
	cursor:pointer;
	width:60px;
	height:30px;
	line-height:30px;
	text-align:center;
	float:right;
	margin-top:12px;
}
.nothing{
	background-color:#DEDEDE;
	color:white;
	width:60px;
	height:30px;
	line-height:30px;
	text-align:center;
	float:right;
	margin-top:12px;
}
.content_center{
	width:95%;
	margin-left:auto;
	margin-right:auto;
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

<body style="background-position:top;background-size:100%;">
<div class="stepTop stepTop-mileage-info" style="width:100%;">
    <div ><img src="/image/xzjsaction.png"/></div>
    <div class="content_center" style="border-bottom:1px solid #DEDEDE;height:50px;line-height:50px;">
        <div style="font-size:15px;float:left;">打卡满10次奖励：星之健身免费体验机会</div>
        <?php if ($mileage_count >= 10):?>
            <div data-count="10" class="action_xzjs action">前往</div>
        <?php else :?>
            <div class="nothing">未获得</div>
        <?php endif;?>
    </div>
    <div class="content_center" style="border-bottom:1px solid #DEDEDE;height:50px;line-height:50px;">
        <div style="font-size:15px;float:left;">打卡满20次奖励：星之健身免费周健身卡</div>
        <?php if ($mileage_count >= 20):?>
            <div data-count="20" class="action_xzjs action">查看</div>
        <?php else :?>
            <div class="nothing">未获得</div>
        <?php endif;?>
    </div>
    <div class="content_center">
        <div style="margin-top:10px;font-weight:700;">和未来的我一起</div>
        <div style="margin-top:10px;font-size:15px;">
            <div style="margin-top:10px;">1. 活动规则：以跑友打卡次数为激活奖励依据，该活动期间内奖励不可重复获得</div>
            <div style="margin-top:8px;">2. 活动时间：2015年6月1日-7月31日</div>
            <div style="margin-top:8px;">3. 适用对象:在上海的跑友</div>
            <div style="margin-top:8px;">4. 活动奖励有效期：2015年12月31日前</div>
            <div style="margin-top:8px;">5. 跑步去运营团队保留对本次跑量打卡奖励活动的最终解释权。</div>
            <div style="margin-top:10px;font-weight:700;">关于星之健身免费体测机会</div>
            <div style="margin-top:10px;">1. 健康驿站是2015年跑步去与星之健身合作的跑者健身服务试点项目 </div>
            <div style="margin-top:8px;">2. 作为跑步去的合作伙伴，星之健身将旗下商业门店及百姓健身房开放为上海跑友的健康驿站，进行体能测试及健身咨询服务。</div>
            <div style="margin-top:8px;">3. 体能测试的各项目数据经上海体育科学研究所专家支持验证，操作简单，数据图表直观易懂，可作为跑友检验跑步及健身成效的重要参考。</div>
            <div style="margin-top:10px;font-weight:700;">关于免费门店周卡使用说明</div>
            <div style="margin-top:10px;">1. 本卡使用时间为办卡之日起连续7天；</div>
            <div style="margin-top:8px;">2. 办卡持有人需凭法定有效证件（身份证，护照，驾驶证）至指定门店中任意一家办理登记，仅限在该登记门店享受服务；</div>
            <div style="margin-top:8px;">3. 星之现有会员无法使用，已办理登记的健身七天卡持有人不可再次在星之健身俱乐部所有门店使用同种类健身七天卡；</div>
            <div style="margin-top:8px;">4. 持卡人可连续七天在使用门店的普通区域使用所有免费项目,不含游泳；</div>
            <div style="margin-top:8px;">5. 持卡人必须年满16周岁，来俱乐部运动请自备运动服装和毛巾等沐浴用品。</div>
        </div>
        <div style="text-align:center;margin-top:10px;">
            <img src="/image/erweima.png" style="width:48%;height:auto;"/>
        </div>
        <div style="text-align:center;margin-top:10px;">
            <span>长按二维码关注<span style="color:#0099da;">跑步去</span>，获取更多活动信息</span>
        </div>
        <div style="height:20px;"></div>
    </div>
    <div id="share" class="am-radius" style="text-align: center;position: absolute;top:10px;right:4px;background-color: rgba(0,0,0,0.6);font-size:1.5rem;padding:4px;color:white">
      <div class="test"></div>
                           点击菜单<br>分享给朋友们
    </div>
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