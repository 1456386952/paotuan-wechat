<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>跑量打卡活动</title>
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
.content_center{
	width:95%;
	margin-left:auto;
	margin-right:auto;
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

	   });
   </script>



</head>

<body style="background-position:top;background-size:100%;">
<div class="stepTop stepTop-mileage-info" style="width:100%;">
    <div ><img src="/image/mileageaction.png"/></div>
    <div class="content_center" style="margin-top:10px;">跑友福利-跑量打卡换代金券活动说明</div>
    <div class="content_center" style="margin-top:10px;">1.活动规则：以跑友打卡记录为计算依据，每10公里=1元代金券</div>
    <div class="content_center" style="margin-top:8px;">2.活动时间：2015年5月19日-7月31日</div>
    <div class="content_center" style="margin-top:8px;">3.奖励结算时间：2015年6月10日，7月10日，8月10日</div>
    <div class="content_center" style="margin-top:8px;">4.代金券有效期：2015年12月31日前</div>
    <div class="content_center" style="margin-top:8px;">5.代金券使用说明:本次跑量打卡活动生成的代金券会发放到您的跑步去app的账户中，您可以在“跑步去app”的“我的”-“我的优惠券”中查看。仅限在跑步去app中抵扣报名参赛费用。一次报名只能使用一张优惠券,生成订单后默认优惠券已经使用,如果逾期未付款或者取消订单,优惠券同样视为已使用。</div>
    <div class="content_center" style="margin-top:8px;">6.跑步去运营团队会对跑量打卡记录进行审核，跑步去保留对本次跑量打卡奖励活动的最终解释权。</div>
    <div class="content_center">
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