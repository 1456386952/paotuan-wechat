<?php
use paotuan_wechat\component\Util;
header ( "Content-type: text/html; charset=utf-8" );
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>确认付款</title>

<link rel="stylesheet" type="text/css" href="/appframe/icons.css">
<link rel="stylesheet" type="text/css" href="/appframe/af.ui.css">
<link rel="stylesheet" type="text/css" href="/css/amazeui.min.css">
<link rel="stylesheet" type="text/css" href="/css/wechat.css">
<script src="/js/jquery.min.js"></script>
<script type="text/javascript" charset="utf-8"
	src="/appframe/fastclick.js"></script>
<script type="text/javascript" charset="utf-8"
	src="/appframe/appframework.ui.min.js"></script>
<script type="text/javascript"
	src='http://res.wx.qq.com/open/js/jweixin-1.0.0.js'></script>
<script type="text/javascript">
    $.afui.useOSThemes=false;
    $.afui.animateHeader(true);
    $.afui.loadDefaultHash=false;
    $.afui.manageHistory=false
    $.afui.isAjaxApp=false;

    $.ajaxSetup({
 	   type: "POST",
 	   beforeSend:function(){
 		   },
 	   error:function(req){
 		   },
 	   complete:function(req, textStatus){
 		  hideLoading();
 		   },
 		   statusCode: {500: function() {
 			   }  
 	          }
 	   });
	   
    $(function(){

        });

    //if($.os.ios)

	  wx.config({
		    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
		    appId: '<?=$signPackage["appId"];?>',
		    timestamp: <?=$signPackage["timestamp"];?>,
		    nonceStr: '<?=$signPackage["nonceStr"];?>',
		    signature:'<?=$signPackage["signature"];?>',
		    jsApiList: ['chooseWXPay','closeWindow']
		});

	  wx.error(function(res){
		});

		//调用微信JS api 支付
		function jsApiCall(params)
		{
			wx.chooseWXPay({
				timestamp:params.timeStamp, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
    			nonceStr:params.nonceStr, // 支付签名随机串，不长于 32 位
   				 package:params.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
   				 signType:params.signType, // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
    			paySign:params.paySign, // 支付签名
			    success: function (res) {
			    	 if(res.err_msg){
		                    alert("发起支付失败");
				    }else{
					    $("#payBtn").text("支付成功，正在为您跳转...");
					    $("#payBtn").attr("disabled",true);
					    $("#qr").hide();
				    	 setTimeout(changeStatus,3000);
					    }
			    },
			    fail:function(){
			    	callNativePay();
			    	return;
				    }
			});
		}

		function test(){
			$.afui.showMask('数据处理中。。');
			}

		function changeStatus(){
			 var paytype = '<?=$params->post("paytype")?>';
		      if(paytype=="club_member_fee"){
			         $.post("/clubs/<?=time()?>/memberpaydone",{uid:'<?=$params->post ( "uid" );?>',club_id:'<?=$params->post ( "club_id" );?>'},function(data){
			        	 backUrl();
				         });
			      }else{
			    	     backUrl();
				      }
			}
		function backUrl(){
			  var backUrl = "<?=$_SERVER['HTTP_REFERER']?>";
              if(backUrl!=""){
                 	 window.location.replace(backUrl+"#<?=$params->post ( "hash" );?>");
                  }
			}
		

		function callpay()
		{
			showLoading();
			$.post("/wxpay/order",{orderid:'<?=Yii::$app->request->post ( "orderid" )?>',openid:'<?=Yii::$app->request->post ( "openid" )?>',paytype:'<?=$params->post("paytype")?>',trade_type:"JSAPI"},function(data){
                           
        if(data.status==1){
                         alert("订单已支付！");
                         $("#payBtn").text("正在为您跳转...");
                         $("#payBtn").attr("disabled",true);
 					     $("#qr").hide();
 					    backUrl();
                       }else{
                    	     jsApiCall($.parseJSON(data.jsApiParameters));
                           }
				});
			   // jsApiCall();
		}

		function callNativePay(){
			showLoading();
			 $("#payBtn").hide();
			$.post("/wxpay/order",{orderid:'<?=Yii::$app->request->post ( "orderid" )?>',openid:'<?=Yii::$app->request->post ( "openid" )?>',paytype:'<?=$params->post("paytype")?>',trade_type:"NATIVE"},function(data){
                 if(data.status==1){
                       alert("订单已支付！");
                       $("#payBtn").text("正在为您跳转...");
                       $("#payBtn").show();
                       $("#payBtn").attr("disabled",true);
					     $("#qr").hide();
					     backUrl();
                     }else if(data.status==0){
                    	// $("#payBtn").hide();
                    	 setQrcode(data.code_url);
                       }else if(data.status==2){
                    	   alert("订单已取消或已删除！");
                           $("#payBtn").text("正在为您跳转...");
                           $("#payBtn").show();
    					     $("#qr").hide();
    					     $("#payBtn").attr("disabled",true);
    					     backUrl();
                           }
				});
			}

		function checkPay(){
			showLoading();
			  setTimeout(function(){
			$.post("/wxpay/checkpay",{orderid:'<?=Yii::$app->request->post ( "orderid" )?>',paytype:'<?=$params->post("paytype")?>'},function(data){
                if(data.status==1){
                	 $("#payBtn").text("正在为您跳转...");
                	 $("#payBtn").show();
                	  $("#payBtn").attr("disabled",true);
				     $("#qr").hide();
				     backUrl();
                    }else{
                        alert("您还没有完成支付！");
                      }
				})},2000);
			}

		function setQrcode(url){
			var qr = qrcode(10, 'M');
	 		qr.addData(url);
	 		qr.make();
// 	 		var dom=document.createElement('DIV');
// 	 		dom.innerHTML = qr.createImgTag();
// 	 		var element=document.getElementById("qrcode");
// 	 		element.appendChild(dom);
	 		$("#qrcode").html(qr.createImgTag());
	 		  $("#qrcode").append('<br>"长按二维码->识别图中二维码"或者"长按二维码->保存图片->返回微信扫一扫"');
	 		$("#qrcode").append('<br><button class="am-btn am-btn-warning am-radius" onclick="checkPay();" data-ignore="True">支付已完成</button> ');
	 		
			}
          function showLoading(){
        	  $("#xiaoi-loading").show();
        	  var loadingEl = $("#xiaoi-loading").children()[0];
        	  $(loadingEl).css("margin-top", ($(window).height()-$(loadingEl).outerHeight())/2+"px");	  
        	  }
          function hideLoading(){
        	  $("#xiaoi-loading").hide();
              }
	</script>
</head>
<body class="ios7" style="font-family:Arial,'Times New Roman', Times, Kai, 'Kaiti SC', KaiTi, BiauKai, 'FontAwesome',Sans-serif">
	<div class="view" id="mainView">
		<div class="pages">
			<div class="panel" id="main" selected="true">
				<ul class="list" style="margin-top: 14px;color:graytext;font-size:.8em">
					<li>
						<div class="am-g">
						<div style="line-height: 44px">
							<div class="am-u-sm-4" style="vertical-align: center;">付款金额</div>
							<div class="am-u-sm-8">
								<span class="am-fr" style="font-size: 2em;font-weight: normal;color:black;">￥<?=$params->post ( "total_fee" )/100;?></span>
							</div>
							</div>
							<hr data-am-widget="divider" style=""
								class="am-divider am-divider-default" />
							<div class="am-u-sm-4">付款项目</div>
							<div class="am-u-sm-8">
							    <?php foreach (explode(",", $params->post("goodDesc")) as $gd):?>
								<span class="am-fr"><?=$gd?></span><br>
								<?php endforeach;?>
							</div>
							<?php if($params->post("expire_time")):?>
							<hr data-am-widget="divider" style=""
								class="am-divider am-divider-default" />
							<div class="am-u-sm-4"><font color="red">过期日期</font></div>
							<div class="am-u-sm-8">
								<span class="am-fr"><font color="red"><?=$params->post("expire_time")?></font></span><br>
							</div>
							<?php endif;?>
								<hr data-am-widget="divider" style=""
								class="am-divider am-divider-default" />
								<div class="am-container">
								<button class="am-btn am-btn-success am-radius am-btn-block" onclick="callpay();" id="payBtn" data-ignore="True">支付</button>
							  </div>
							  <br>
								<div>
								  支付说明:<br>
								  1、报名订单请在<font color="red">过期日期</font>前完成支付<br>
								  2、如遇<font color="red">支付问题(不允许跨号支付等)</font>请使用<a href="javascript:void(0);" id="qrcodePay" onclick="callNativePay();" data-ignore="True">二维码支付</a>
								</div>
							
						</div>
						<div class="am-container" style="margin-top:14px" id="qr">
					 	<div align="center" id="qrcode">
	                     </div>
	                  </div>
					</li>
				</ul>

			</div>
		</div>
	</div>
	<div id="xiaoi-loading" style="width: 100%;background-color: rgba(0,0,0,0);z-index:9999999;height:100%;position: fixed;top: 0;left: 0;text-align: center;display:none">
		    <div style="background-color:black;opacity:0.7;width: 150px;padding:14px;border-radius:8px;display: inline-block;margin-top:10%"><img alt="" src="/image/loading.gif" style="width: 50px"></div>
		</div>
	<script src="/js/qrcode.js"></script>
	<script>
	    callpay();
		var url = "<?php echo $product_url;?>";
// 		//参数1表示图像大小，取值范围1-10；参数2表示质量，取值范围'L','M','Q','H'
// 		var qr = qrcode(10, 'M');
// 		qr.addData(url);
// 		qr.make();
// 		var dom=document.createElement('DIV');
// 		dom.innerHTML = qr.createImgTag();
// 		var element=document.getElementById("qrcode");
// 		element.appendChild(dom);
	</script>
</body>
</html>
