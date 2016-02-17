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
<title>创建跑团</title>
 <link href="/css/paotuanzhuce/app.css?<?=time()?>" type="text/css" rel="stylesheet"/>
 <link rel="stylesheet" href="/css/amazeui.min.css"/>
 <link href="/css/wechat.css?<?=time()?>" type="text/css" rel="stylesheet"/>
 <link href="/css/ionicons/css/ionicons.min.css" type="text/css"	rel="stylesheet" />
<script type="text/javascript" src="/js/jquery.min.js"></script>
 <script src="/js/amazeui.min.js"></script>
<script type="text/javascript" src="/js/paotuanzhuce/app.js?<?=time()?>"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="/js/qrcode.js"></script>
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

.am-icon-search:before{
   color:#ddd
}
</style>
   <script type="text/javascript">

   wx.config({
	    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
	    appId: '<?=$signPackage["appId"];?>',
	    timestamp: <?=$signPackage["timestamp"];?>,
	    nonceStr: '<?=$signPackage["nonceStr"];?>',
	    signature:'<?=$signPackage["signature"];?>',
	    jsApiList: ['onMenuShareAppMessage','onMenuShareTimeline']
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

  function share(data){
	  var params={};
	    // 获取“分享给朋友”按钮点击状态及自定义分享内容接口
	    if($.isEmptyObject(data)){
	    	params.title = "跑团创建";
	    	params.desc = "创建属于自己的跑团";
	    	params.link = '<?=Yii::$app->request->hostInfo."/clubs/new"?>';
	    	params.imgUrl = '<?=Yii::$app->request->hostInfo."/image/email_verify/logo.png"?>';
	    }else{
	    	params.title = data.club.club_name;
	    	params.desc ='我在"跑步去"创建了自己的跑团\n'+data.club.club_slogan;
	    	params.link = data.club.url;
	    	params.imgUrl =  '<?=Yii::$app->request->hostInfo."/image/paotuanzhuce/top.png"?>';;
		 }
	    wx.onMenuShareAppMessage({
	    title: params.title, // 分享标题
	    desc: params.desc,
	    link: params.link, // 分享链接
	    imgUrl: params.imgUrl, // 分享图标
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
	        title: params.title, // 分享标题
	        link: params.link, // 分享链接
	        imgUrl: params.imgUrl, // 分享图标
	        success: function () { 
	            // 用户确认分享后执行的回调函数
	        },
	        cancel: function () { 
	            // 用户取消分享后执行的回调函数
	        }
	    });
	  }
   wx.ready(function(){
	   share({});
	});
	  


     function alertMsg(msg,callback){
    	 $("#alert-content").html(msg);
	    	 $("#appleAlert").on("closed.modal.amui",callback);
	    	 $("#appleAlert").modal();
         }


     function fileUpload(file){
 		if(file.value==""||!checkImg(file))return;
 		$("#uploadForm").empty();
 		$("#uploadForm").append(file);
 	    $("#uploadForm").append("<input type='hidden' name='img_type' value='club_logo'/>");
 	    $("#uploadForm").submit();
 	    var fileClone = $(file).clone();
 		$("#logo").append(fileClone);
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
 		if(r.length>0){
 			  var result = $.parseJSON($(r[0]).text());
 			  if(result.status==1){
 				     $("#club_logo_view").attr("src","http://xiaoi.b0.upaiyun.com/"+result.image+"!80X80");
                     $("#club_logo").val(result.image);
  				     $("#modal-loading").modal("close");
 				  }else{
 					  $("#alert-content").text("文件上传错误,请稍候重试");
 	    		    	 $("#appleAlert").modal();
 					  }
 			}else{
 		        $("#modal-loading").modal("close");
 			}
 	}

 	function createClub(){
 		 if($("#clubForm").data('amui.validator').isFormValid()){
 			$("#modal-loading").modal();
               $.post("/clubs/createclub",$("#clubForm").serialize(),function(data){
                      if(data.status==-1){
                    	  alertMsg("跑团名称已经存在.");
                    	  $("#club_name").focus();
                          }else if(data.status==-2){
                        	  alertMsg("跑团英文短名已经存在.");
                        	  $("#club_eng").focus();
                            }else if(data.status==1){
                            	share(data);
                                var qrCode = getQrcode(data.url);
                                $("#club-info").html("跑团创建完成！您可以扫描下面二维码或者点击链接进入跑团主页<br>"+qrCode+"<br><a href='"+data.url+"'>进入我的跑团</a><br>"+data.url);
                                $("#club-success").modal();
                            }else if(data.status==0){
                            	alertMsg(data.msg);
                                }
                   });
 	 		 }
 		return false;
 	 	}

 	function getQrcode(url){
		var qr = qrcode(10, 'M');
 		qr.addData(url);
 		qr.make();
//	 		var dom=document.createElement('DIV');
//	 		dom.innerHTML = qr.createImgTag();
//	 		var element=document.getElementById("qrcode");
//	 		element.appendChild(dom);
 		return qr.createImgTag();
		}

   </script>
   
   
   
</head>

<body>
	<?php if(!$subscribe):?>
	<div class="am-container">
		 <h3 class="am-text-primary" style="text-align: center;margin-top:14px;margin-bottom:4px">需要关注"跑步去"才能创建新跑团</h3>
		  <div style="width:100%;text-align: center"><img alt="" src="/image/qrcode_258.jpg"></div>
     <p>关注方法:<br>方式一:搜索微信号:i-am-runner。<br>方式二:长按二维码->识别图中二维码或者长按二维码->保存图片->返回微信扫一扫</p><br>
	</div>

<?php else:?>
<header class="stepTop" style="height:150px">
		<div class="topInfo" style="">
	   <div class="thumb-circle-logo am-center" id="logo"><img src="/image/club_default.png" id="club_logo_view"></div>
	   <!--  
		<div class="userPhoto" id="club_logo_view"  style="background-image:url('/image/club_default.png');display:inline-block;"></div>
		  -->
		   <div class="userName">
		   <small style="color:black;font-weight:normal;">点击上传跑团logo</small>
		   <input style="bottom: 0;left:0;height:124px;width:100%;z-index:1;display: inline-block;position: absolute;border: thin green solid;opacity:0"  type="file"  name="UploadForm[file]" onchange="fileUpload(this);" accept="image/gif,image/png,image/jpg,image/jpeg,image/bmp" >
		   </div>
		</div>
</header>
<div class="am-container">
    <form class="am-form" method="post" data-am-validator id="clubForm">
     <div class="am-form-group">
                <label  for="club_name"><span class="i-red-12">*</span>跑团名称:</label>
                    <input required class="am-form-field am-radius" id="club_name" name="club_name" value="" placeholder="给你的跑团起个名字吧" type="text" maxlength="60">
            </div>
     <div class="am-form-group">
                <label  for="nationality"><span class="i-red-12">*</span>跑团短名:(设置后不可更改)</label>
                    <input required class="am-form-field am-radius" id="club_eng" name="club_eng" value="" placeholder="输入英文或数字组成的短名" type="text" maxlength="20" pattern="^[_0-9a-zA-Z]+$">
            </div>
               <div class="am-form-group">
                <label  for="club_slogan">跑团口号:</label>
                    <input  class="am-form-field am-radius" id="club_slogan" name="club_slogan" value="" placeholder="一个好记的口号让大家记住你" type="text">
            </div>
            <input type="hidden" id="club_logo" name="club_logo"/>
             <input type="hidden" id="clubid" name="clubid"/>
    </form>
    <br>
    <button  class="am-btn am-block  am-radius am-btn-primary am-btn-block" onclick="createClub();">创建我的跑团</button>
</div>

 <div class="am-modal am-modal-alert" tabindex="-1" id="appleAlert">
  <div class="am-modal-dialog">
    <div class="am-modal-hd">提示</div>
    <div class="am-modal-bd" id="alert-content">
    <div align="center" id="qrcode">
	                     </div>
    </div>
    <div class="am-modal-footer">
      <span class="am-modal-btn">确定</span>
    </div>
  </div>
</div>

 <div  class="am-popup" id="club-success">
  	<div class="am-popup-inner">
  	  <div class="am-popup-hd">
		   <h4 class="am-popup-title">我的跑团</h4>
        <span data-am-modal-close
            class="am-close">&times;</span>
       </div>
        <div class="am-popup-bd" id="club-info" style="text-align: center">
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

<div class="am-modal am-modal-loading am-modal-no-btn" tabindex="-1" id="modal-loading">
  <div class="am-modal-dialog">
    <div class="am-modal-hd"></div>
    <div class="am-modal-bd">
      <span class="am-icon-spinner am-icon-spin"></span>
    </div>
  </div>
</div>

	<form action="/clubs/uploadimg" target="upFrame" method="post"
			enctype="multipart/form-data" id="uploadForm" style="display:none">
		</form>
		<iframe id="upFrame" name="upFrame" onload="upLoad();"
			style="display:none;width: 100%"></iframe>
<script>
	app.showAndHide();
</script>
<?php endif;?>
</body>
</html>

