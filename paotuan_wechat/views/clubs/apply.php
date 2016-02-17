<?php 
use common\component\CustomHelper;
use yii\helpers\ArrayHelper;
use paotuan_wechat\component\Util;
use paotuan_wechat\controllers\ClubsController;
use paotuan_wechat\models\ClubMember;
header("Content-type: text/html; charset=utf-8");
if(!$user){
    echo "<h1>微信授权超时，请稍候重试</h1>";
    exit;
}
if(is_null($club)){
	header("/notfound.html");
	exit;
}
$needpay = null;
$new=false;
if(is_null($member)){
	$new=true;
	if($user!=null&&$user->userInfo!=null){
		$member = new ClubMember();
		$member =Util::copyUserInfoToMember($member, $user->userInfo);
		$id_back = $member["id_copy_back"];
		$member=ArrayHelper::toArray($member);
		$member["id_copy_back"]=$id_back;
	}else{
	    $member = array();
	}
}else{
	$needpay=$member->getNeedpay();
	$member =Util::copyUserInfoToMember($member, $user->userInfo);
	$id_back = $member["id_copy_back"];
	$member=ArrayHelper::toArray($member);
	$member["id_copy_back"]=$id_back;
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title><?=$club->club_name?></title>
<?=Util::getMainJsCss()?>
<?=Util::getFileUploadJs();?>
<?=Util::getWechatJs();?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<style type="text/css">
.stepTop{background-image:<?php if(empty($club->club_bgimage)):?>url(/image/paotuanzhuce/top.png)<?php else:?>url(<?=CustomHelper::CreateImageUrl($club->club_bgimage, "big640")?>)<?php endif;?>; }
 .i-red-12{
       color:red;
     }
     
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
	    jsApiList: ['chooseWXPay','closeWindow','chooseImage','uploadImage','previewImage','onMenuShareAppMessage','onMenuShareTimeline']
	});

   $.ajaxSetup({
	   type: "POST",
	   beforeSend:function(){
		    
		   },
	   error:function(req){
		   },
	   complete:function(req, textStatus){
		   $("#btnApply").button('reset');
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
	    title: <?=json_encode($club->club_name)?>, // 分享标题
	    desc: <?=json_encode($club->club_slogan)?>,
	    link: '<?=Yii::$app->request->hostInfo."/clubs/".$club->club_eng."/register"?>', // 分享链接
	    imgUrl:  '<?php if($club->club_bgimage){echo CustomHelper::CreateImageUrl($club->club_logo, "small80");}else{ echo Yii::$app->request->hostInfo."/image/paotuanzhuce/top.png";}?>', // 分享图标
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
	        title: <?=json_encode($club->club_name)?>, // 分享标题
	        link: '<?=Yii::$app->request->hostInfo."/clubs/".$club->club_eng."/register"?>', // 分享链接
	        imgUrl: '<?php if($club->club_bgimage){echo CustomHelper::CreateImageUrl($club->club_logo, "small80");}else{ echo Yii::$app->request->hostInfo."/image/paotuanzhuce/top.png";}?>', // 分享图标
	        success: function () { 
	            // 用户确认分享后执行的回调函数
	        },
	        cancel: function () { 
	            // 用户取消分享后执行的回调函数
	        }
	    });
	});

	function closeWechatWindow(){
		wx.closeWindow();
		}
	


	function getResCode(){
		   $("#regError").text("");
           var  phone = $.trim($("#cell").val());
           if(phone==""){
          	 $("#cell").focus();
               return;
           }
           disableBtn("#veriCode",$("#veriCode").text());
           $.post("/clubs/<?=$club->club_eng?>/getcellcode?"+new Date().getTime(),{cell:phone,uid:$.trim($("#uid").val())},function(data){
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


     function apply(){
    	 checkCell();
    	  if($("#registerForm").data('amui.validator').isFormValid()){

             var number = $("input[type='number']");
             var checkNumber=true;
             $.each(number,function(i,n){
                   var text = $(n).parent().children("label");
                   var val = $.trim($(n).val());
                   if(val!=""&&val!=null&&!$.isNumeric(val)){
                	   alertMsg(text.text()+"只能为数字");
                	   checkNumber=false;
                       return;
                       }
                   
                 });
               if(!checkNumber){
                    return;
                   }
        	  
    		  //$("#registerForm").attr("action","/clubs/<?=$club->club_eng?>/apply?t="+new Date().getTime());
    		  //$("#registerForm").submit();
    		  //return;
        	  $("#btnApply").button("loading");
       	   $.post("/clubs/<?=$club->club_eng?>/apply?t="+new Date().getTime(),$("#registerForm").serialize(),function(data){
            	   if(data.status==1){
           		  $("#btnApply").button('reset');
           		 $("#member_id").val(data.id);
           		     if(data.result=="needconfirm"){
           		    	//document.location.reload();
           		    	alertMsg("您的入会请求已提交，等待审核中！",function(){
           		    	 $("#btnApply").addClass("am-disabled");
           		    	//document.location = document.location+"#reload";
           		    	location.replace("http://"+document.domain+"/clubs/<?=$club->club_eng?>/register#reload");
          		    	document.location.reload();
               		    	});
               		     }else if(data.result=="success"){
               		    	 //$("#alert-content").text("信息更新完成");
               		    	// $("#appleAlert").modal();
               		    	alertMsg("信息更新完成",function(){
               		    	 $("#btnApply").addClass("am-disabled");
               		    	   <?php if($new):?>
               		    	    document.location="/wechat/#/clubs/members?club_eng=<?=$club->club_eng?>";
               		    	    <?php else:?>
               		    	    //document.location = document.location+"#reload";
               		    	    location.replace("http://"+document.domain+"/clubs/<?=$club->club_eng?>/register#reload");
                   		    	document.location.reload();
                   		    	<?php endif;?>
                   		    	});
               		    	
               		    	// alert(data.id);
               		    	 //$.post("/clubs/<?=$club->club_eng?>/userinfo?t="+new Date().getTime(),{id:data.id},function(data){
                                //  $("#userinfo").html(data);
                                //  $(window).smoothScroll({position:$("#userinfo").position().top});
                   		    	// });
                   		     }else if(data.result=="needpay"){
                   		    	 $("#btnApply").addClass("am-disabled");
                   		    	 document.location = document.location+"#reload";
                   		    	 document.location.reload();
                       		     }
               	   }else if(data.status==0){
               	        $("#msg").text(data.msg);
               	        $("#msg").show();
               	     $(window).smoothScroll({position:$("#msg").position().top});
                   	   }
                });
            }
           return false;
         }

     function alertMsg(msg,callback){
    	 $("#alert-content").text(msg);
	    	 $("#appleAlert").on("closed.modal.amui",callback);
	    	 $("#appleAlert").modal();
         }

   function payInNewPage(){
	     var feestr=$("#fee_circle").val();
	     $("#attach").val(feestr);
	     $("#gd").val('<?=$club->club_name?>,会费  ￥<?=$club->member_fee?>');
	     $("#payForm").submit();
	   }

   function modifyShow(){
	       $("#info").hide()
           $("#reg").show();
	   }

   function infoShow(){
	   $("#userinfo").show();
	   }

   function checkRegister(){
	   <?php if(empty($user->user_cell)):?>
	     document.location = "/bind?uid=<?=$user->uid?>&"+new Date().getTime(); 
	   <?php else:?>
	   $("#info").hide()
       $("#reg").show();
       $("input").removeAttr("disabled");
       $("select").removeAttr("disabled");
       $("button").removeAttr("disabled");
	   <?php endif;?>
	   }


   $(function(){
	 	 
         if(location.hash=="#reload"){
//              if($("#infoBtn").position()){
//             		$(window).smoothScroll({position:$("#infoBtn").position().top});
//                  }else{
//                 	 $(window).smoothScroll({position:$("#modifyBtn").position().top});
//                }
        	 infoShow();
           }
         <?php if(!$member["id"]):?>
         $("#info").hide()
         $("#reg").show();
         $("input").attr("disabled",true);
         $("select").attr("disabled",true);
         $("button").attr("disabled",true);
         <?php endif;?>
         $(window).smoothScroll();
	   });
   </script>
   
   
   
</head>

<body>
	<?php //include 'header.php';?>
	<!-- 
  <ul class="am-list">
     
        	<?php if(!empty($club->club_url)):?>
        	   <li >
	   <?php if(Util::isUrl($club->club_url)):?>
	     <a href="<?=trim($club->club_url)?>" class="icon-color"><i class="ion-home icon-color"></i> 主页</a>
	     <?php else:?>
	       <a href="javascript:void(0);" onclick="$('#club_url').modal();" class="icon-color"> <i class="ion-home icon-color"></i> 主页</a>
	   <?php endif;?>
	    </li>
	<?php endif;?>
       
        
         
        	<?php if(!empty($club->club_desc)):?>
        	  <li>
	   <?php if(Util::isUrl($club->club_desc)):?>
	     <a href="<?=trim($club->club_desc)?>" class="icon-color"><i class="ion-android-document icon-color"></i> 介绍</a>
	     <?php else:?>
	       <a href="/wechat/#/clubs/intro?club_eng=<?=$club->club_eng?>" class="icon-color"> <i class="ion-android-document icon-color"></i> 介绍</a>
	   <?php endif;?>
	    </li>     
	<?php endif;?>
          
        <?php if(!empty($club->club_charter)):?>
         <li>
	   <?php if(Util::isUrl($club->club_charter)):?>
	     <a href="<?=trim($club->club_charter)?>" class="icon-color"><i class="ion-ios-list icon-color"></i> 章程</a>
	     <?php else:?>
	       <a href="javascript:void(0);" onclick="$('#club_charter').modal();" class="icon-color"><i class="ion-ios-list icon-color"></i> 章程</a>
	   <?php endif;?>
	   </li>
	<?php endif;?>
  </ul>
  -->
	<div  id="userinfo" style="display:block;overflow: hidden;">
	<?php include dirname(__FILE__).'/userinfo.php';?>
	</div>
	
<?php if($needpay!=null):?>
<form action="/wxpay/index?showwxpaytitle=1" style="display: none" method="post" id="payForm">
  <input type="text" name="openid" value="<?=$openid?>">
  <input type="text" name="uid" value="<?=$user->uid?>">
  <input type="text" name="club_id" value="<?=$club->clubid?>">
   <input type="text" name="goodDesc" value=""  id="gd">
    <input type="text" name="orderid" id="orderid" value="<?=$needpay->id?>">
    <input type="text" name="total_fee" value="<?php echo $club->member_fee*100;?>"  id="tf">
    <input type="text" name="attach" value=""  id="attach">
    <input type="text" name="notify_url" value="<?php echo \Yii::$app->params["club_pay_notify"];?>">
    <input type="text" name="paytype" value="club_member_fee">
 </form>
 <?php endif;?>
 <div class="am-modal am-modal-alert" tabindex="-1" id="appleAlert">
  <div class="am-modal-dialog">
    <div class="am-modal-hd">提示</div>
    <div class="am-modal-bd" id="alert-content">
    </div>
    <div class="am-modal-footer">
      <span class="am-modal-btn">确定</span>
    </div>
  </div>
</div>

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


<div class="am-modal am-modal-alert" tabindex="-1" id="club_desc">
  <div class="am-modal-dialog">
   <div class="am-modal-bd">
      <?=$club->club_desc?>
      </div>
      </div>
</div>

<div class="am-modal am-modal-alert" tabindex="-1" id="club_url">
       <textarea rows="" cols="" readonly="readonly" style="border: none;width: 100%;overflow: visible;"><?=$club->club_url?></textarea>
</div>

<div class="am-modal am-modal-alert" tabindex="-1" id="club_charter">
       <textarea rows="" cols="" readonly="readonly" style="border: none;width: 100%;overflow: visible;"><?=$club->club_charter?></textarea>
</div>

<div class="am-modal am-modal-loading am-modal-no-btn" tabindex="-1" id="modal-loading">
  <div class="am-modal-dialog">
    <div class="am-modal-hd"></div>
    <div class="am-modal-bd">
      <span class="am-icon-spinner am-icon-spin"></span>
    </div>
  </div>
</div>
<?php if(empty($userInfo->user_cell)||empty($userInfo->userInfo->user_cell)):?>
<div class="am-popup" id="bind-popup">
  <div class="am-popup-inner">
    <div class="am-popup-hd">
      <h4 class="am-popup-title">用户身份确认</h4>
      <span data-am-modal-close
            class="am-close">&times;</span>
    </div>
    <div class="am-popup-bd" id="bindContent">
      ...
    </div>
  </div>
</div>
<?php endif;?>
	<div data-am-widget="navbar" class="am-navbar am-cf am-navbar-default "
id="">
  <ul class="am-navbar-nav am-cf am-avg-sm-3">
      <li>
      <a href="home" data-ignore="true" id="memberToHome">
        <span class="ion-home am-icon-sm"></span>
        <span class="am-navbar-label">跑团</span>
      </a>
    </li>
  </ul>
</div>
<footer data-am-widget="footer" class="am-footer am-footer-default">  <div class="am-g am-g-fixed">
  			<div class="am-u-sm-3"> <hr class="am-divider am-divider-default"
/></div>
  			<div class="am-u-sm-6 am-text-center" ><?=$club->club_name?>&nbsp;<span class="am-icon-copyright"></span> <?=date("Y")?></div>
  				<div class="am-u-sm-3"> <hr class="am-divider am-divider-default"
/></div>
	        </div></footer>
	         <form action="/clubs/<?=$club->club_eng?>/uploadimg" target="upFrame" method="post" enctype="multipart/form-data" id="uploadForm" style="display:none">
	         </form>
	        <iframe id="upFrame" name="upFrame" onload="upLoad();" style="display:none"></iframe>
</body>
</html>
