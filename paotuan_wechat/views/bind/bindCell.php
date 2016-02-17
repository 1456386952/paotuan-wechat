<?php 
use common\component\CustomHelper;
use yii\helpers\ArrayHelper;
use paotuan_wechat\component\Util;
use paotuan_wechat\controllers\ClubsController;
use paotuan_wechat\models\ClubMember;
header("Content-type: text/html; charset=utf-8");
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>身份确认</title>

<link href="/css/paotuanzhuce/app.css?<?=time()?>" type="text/css" rel="stylesheet"/>
 <link rel="stylesheet" href="/css/amazeui.min.css"/>
<script type="text/javascript" src="/js/jquery.min.js"></script>
 <script src="/js/amazeui.min.js"></script>
<script type="text/javascript" src="/js/paotuanzhuce/app.js"></script>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<style type="text/css">
     .am-form select,
.am-form textarea,
.am-form input[type="text"],
.am-form input[type="password"],
.am-form input[type="datetime"],
.am-form input[type="datetime-local"],
.am-form input[type="date"],
.am-form input[type="month"],
.am-form input[type="time"],
.am-form input[type="week"],
.am-form input[type="number"],
.am-form input[type="email"],
.am-form input[type="url"],
.am-form input[type="search"],
.am-form input[type="tel"],
.am-form input[type="color"],
.am-form-field {
  font-size: 1.2rem;
}

.am-form-group .am-btn{
font-size: 1.2rem;
}

.radio-font-size{
font-size: 1.2rem;
}

.registerWrap .r_1_ex:after{content:''; border:#fff solid; border-width:3px 3px 0 0; -webkit-transform:rotate(135deg);-moz-transform:rotate(135deg);transform:rotate(135deg); position: absolute; width: 10px; height: 10px;right:14px; top:12px;}

.pd10{
  padding-bottom:0px
}

.am-panel {
  -webkit-box-shadow:none;
          box-shadow: none;
}
</style>
   <script type="text/javascript">
   var base_url = '<?=\Yii::$app->params["site_url"]?>';
   var exstatus=false;
   wx.config({
	    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
	    appId: '<?=$signPackage["appId"];?>',
	    timestamp: <?=$signPackage["timestamp"];?>,
	    nonceStr: '<?=$signPackage["nonceStr"];?>',
	    signature:'<?=$signPackage["signature"];?>',
	    jsApiList: ['chooseWXPay','closeWindow','chooseImage','uploadImage']
	});

   $.ajaxSetup({
	   type: "POST",
	   beforeSend:function(){
		   },
	   error:function(req){
		   $("#alert-content-error").text("服务器异常，请稍候重试或联系管理员！");
	    	 $("#appleAlertError").modal();
		   },
	   complete:function(req, textStatus){
		  
		   },
		   statusCode: {500: function() {
			   $("#alert-content-error").text("服务器异常，请稍候重试或联系管理员！");
 		    	 $("#appleAlertError").modal();
 		    	$("#btnApply").button('reset');
			   }  
	          }
	   });
	  

   wx.error(function(res){
	     $.each(res,function(i,n){
                 //alert(i+"----"+n);
		     });
	});


	function closeWechatWindow(){
		wx.closeWindow();
		}

   function checkCell(){
		 if($("#bindForm").data('amui.validator').isFormValid()){
			 $("#msg").text("");
			 $("#msg").hide();

//  			 $("#bindForm").attr("action","/bind/bind?t="+new Date().getTime());
//  			$("#bindForm").submit();
//   			 return;
       	    $("#btnApply").button("loading");
       	    $.post("/bind/checkcell",$("#bindForm").serialize(),function(data){
                    if(data.status==0){
                    	bind();
                        }else{
                        $("#my-confirm").modal();    
                      }
           	    });
		 }
	   }

	function bind(){
		 if($("#bindForm").data('amui.validator').isFormValid()){
			 $("#msg").text("");
			 $("#msg").hide();

//  			 $("#bindForm").attr("action","/bind/bind?t="+new Date().getTime());
//  			$("#bindForm").submit();
//   			 return;
       	    $.post("/bind/bind?t="+new Date().getTime(),$("#bindForm").serialize(),function(data){
       	     $("#btnApply").button('reset');
                if(data.status==1){
                    	 var backUrl = "<?=$_SERVER['HTTP_REFERER']?>";
                    	 var hash="<?=Yii::$app->request->get("hash")?>";
                    	 var redirect ="<?=Yii::$app->request->get("redirect","")?>";
                    	if(redirect){
                        	  location.replace(redirect);
                        	  return;
                        	}
                    		 if(backUrl!=""){
                                 if(backUrl.indexOf("?")!=-1){
                                	    window.location.replace(backUrl+"#<?=Yii::$app->request->get("hash")?>");
                                     }else{
                                    	window.location.replace(backUrl+"#<?=Yii::$app->request->get("hash")?>");
                                      }
                                 }
                        }else{
                        	$("#msg").text(data.msg);
                   	        $("#msg").show();
                            }
           	    });
		 }
		 return false;
		}
	


	function getResCode(){
		   $("#regError").text("");
           var  phone = $.trim($("#cell").val());
           if(phone==""){
          	 $("#cell").focus();
               return;
           }
           disableBtn("#veriCode",$("#veriCode").text());
           $.post("/bind/getcellcode?"+new Date().getTime(),{cell:phone,uid:$.trim($("#uid").val())},function(data){
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



     function alertMsg(msg,callback){
    	 $("#alert-content").text(msg);
	    	 $("#appleAlert").on("closed.modal.amui",callback);
	    	 $("#appleAlert").modal();
         }


   $(function(){
         $(window).smoothScroll();
	   });
   </script>
   
   
   
</head>

<body>
<header data-am-widget="header" class="am-header am-header-default">
  <h1 class="am-header-title">
                 身份验证
  </h1>
</header>
<section data-am-widget="accordion" class="am-accordion am-accordion-basic"
data-am-accordion='{  }'>
  <div class="am-alert am-alert-warning" data-am-alert id="msg" style="display: none">
</div>
 <form class="am-form"  style="margin-top:10px;" id="bindForm"   method="post" data-am-validator>
 <div class="am-form-group am-form-icon">
   <i class="am-icon-mobile-phone"></i>
<input required id="cell" name="cell" value="" class="am-form-field am-radius" placeholder="请填写您的手机号码！" type="number" pattern="^1[3|4|5|7|8][0-9]\d{4,8}$">                 
</div>

    <div class="am-form-group am-input-group am-form-icon">
      <i class="am-icon-mobile-phone"></i>
     <input type="number" class="am-form-field"  id="reg-code" name="cellCode" placeholder="验证码" value="" required  maxlength="6" style="width:9em">
        <button class="am-btn am-btn-danger" style="width:8em" type="button" onclick="getResCode();" id="veriCode">获取验证码</button>
    </div>
      <span class="am-help" style="color:graytext;">欢迎您！您所请求的操作需要先完成身份验证。</span>  <br>
    <input type="hidden" name="uid" id="uid" value="<?=$uid?>">
</form>
<button class="am-btn am-btn-success am-btn-block" id="btnApply" onclick="checkCell();">下一步</button> 
</section>

<div class="am-modal am-modal-alert" tabindex="-1" id="appleAlertError">
  <div class="am-modal-dialog">
    <div class="am-modal-hd">错误</div>
    <div class="am-modal-bd" id="alert-content-error">
    </div>
    <div class="am-modal-footer">
      <span class="am-modal-btn">确定</span>
    </div>
  </div>
</div>

<div class="am-modal am-modal-confirm" tabindex="-1" id="my-confirm">
  <div class="am-modal-dialog">
    <div class="am-modal-hd">提示</div>
    <div class="am-modal-bd">
       手机号已存在,是否覆盖之前的信息?
    </div>
    <div class="am-modal-footer">
      <span class="am-modal-btn" onclick="$('#btnApply').button('reset'); $('#my-confirm').modal('close')">取消</span>
      <span class="am-modal-btn" onclick="bind();">确定</span>
    </div>
  </div>
</div>

</body>
</html>

<script>
	app.showAndHide();
</script>