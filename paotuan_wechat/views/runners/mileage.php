<?php
use common\component\CustomHelper;
use yii\helpers\ArrayHelper;
use paotuan_wechat\component\Util;
header ( "Content-type: text/html; charset=utf-8" );
if ($user==null) {
	 echo "<h1>微信授权超时，请稍候重试</h1>";
	 exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>打卡</title>
<?=Util::getMainJsCss();?>
<?=Util::getFileUploadJs();?>
<?=Util::getWechatJs()?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<style type="text/css">

.i-red-12 {
	color: red;
}

.am-form select, .am-form textarea, .am-form input[type="text"],
	.am-form input[type="password"], .am-form input[type="datetime"],
	.am-form input[type="datetime-local"], .am-form input[type="date"],
	.am-form input[type="month"], .am-form input[type="time"], .am-form input[type="week"],
	.am-form input[type="number"], .am-form input[type="email"], .am-form input[type="url"],
	.am-form input[type="search"], .am-form input[type="tel"], .am-form input[type="color"],
	.am-form-field {
	font-size: 1.2rem;
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

.am-panel {
	-webkit-box-shadow: none;
	box-shadow: none;
}

.plus .minusleft {
	width: 50px;
	height: 50px;
	margin-left: 10px;
	background-image: url(/image/paotuanzhuce/minus.png);
	background-size: cover;
}

.plus .minusleft .plusFile {
	width: 100%;
	height: 100%;
	opacity: 0;
}

/*活动翻滚样式*/
* {
    margin: 0;
    padding: 0;
}
.scrollNews {
    width: 100%;
    height: 20px;
    overflow: hidden;
    background: #FFFFFF;
    border: 0px solid #AAAAAA;
}
.scrollNews ul {
    padding: 2px 5px 5px 25px;
}
.scrollNews ul li {
    height: 20px;
    list-style-type: none;
    font-size: small;
}
a {
    text-decoration: none;
}
</style>
<script type="text/javascript">
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
	    title: "跑步打卡", // 分享标题
	    desc: "和未来的自己一起跑步去", // 分享描述
	    link: '<?=Yii::$app->request->absoluteUrl?>', // 分享链接
	    imgUrl: "http://"+document.domain+"/image/logo_80.png", // 分享图标
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
	        title: "跑步打卡", // 分享标题
	        link: '<?=Yii::$app->request->absoluteUrl?>', // 分享链接
	        imgUrl: "http://"+document.domain+"/image/logo_80.png", // 分享图标
	        success: function () { 
	            // 用户确认分享后执行的回调函数
	        },
	        cancel: function () { 
	            // 用户取消分享后执行的回调函数
	        }
	    });
	});

	function showError(text,callBack){
		$("#alert-content-error").text(text);
		if($.isFunction(callBack)){
			$("#appleAlertError").on("closed.modal.amui",callBack);
			}
				 $("#appleAlertError").modal();
		}

	function closeWechatWindow(){
		wx.closeWindow();
		}

	function fileUpload(file,type){
		if(file.value=="")return;
		if(file.files.length>9){
			 $("#alert-content").text("最多选择9个图片");
	    	 $("#appleAlert").modal();
	    	 return;
			}
		//checkImg(file);
		$("#uploadForm").empty();
		$("#uploadForm").append(file);
	    $("#uploadForm").append("<input type='hidden' name='img_type' value='mileage'/>");
	    $("#uploadForm").append("<input type='hidden' name='file_id' value='"+file.id+"'/>");
	    $("#uploadForm").submit();
	   // var fileClone = $(file).clone();
		 //fileClone.removeAttr("required");
		//$("#"+file.id+"_div").append(fileClone);
	   // $("#modal-loading").modal({closeViaDimmer:false});
	    showLoading();
	}

	function checkImg(file){
		//var ex = file.value.substring(file.value.lastIndexOf(".")+1);
		//var img =["jpg","jpeg","png","gif","bmp"];
		//if($.inArray(ex.toLowerCase(),img)==-1){
		  
			var fileClone = $(file).clone();
			$(file).remove();
			$("#"+file.id+"_div").append(fileClone);
			 //$("#alert-content").text("文件类型错误，请选择图片");
	    	 //$("#appleAlert").modal();
	    	//// return false;
			//}
		//return true;
	}

	function upLoad(){
		var r = $("#upFrame").contents().find(".uploadResult");
		var tips =  '<div class="plusRigh" id="img-right">有图有真相</div>';
		var del = '<i class="am-icon-minus-circle">123</i>';
		if(r.length>0){
			$.each(r,function(i,n){
				  var result = $.parseJSON($(n).text());
				  if(result.status==1){
					  var id = result.id;
					  $("#img-right").hide();
					 // var img = '<img alt="" src="http://xiaoi.b0.upaiyun.com/'+result.image+'" id="mileage_image_pre_'+i+'" style="width: 60px; height: 60px;margin-left: 14px"'+
						//'onerror="this.style.display=\'none\'" class="img_pre">';
						//$("#mileage_image_div").after(img);
						//$(img).after(del);
						var img = ' <li><div class="am-gallery-item" >'+
      '<a href="http://xiaoi.b0.upaiyun.com/'+result.image+'"class="">'+
        '<img src="http://xiaoi.b0.upaiyun.com/'+result.image+'!80X80" style="width:60px;height:60px"/></a></div>'+
        '<input type="hidden" value="'+result.image+'" name="mileage_image[]"/></li>'
             $("#img_pre").append(img);
						//$("#mileage_image_div").after(img);
					     $("#"+id+"_value").val(result.image);
					     $("#img-right").hide();
					     //$("#modal-loading").modal("close");
					     hideLoading();
					  }else{
						  $("#alert-content").text("文件上传错误,请稍候重试");
		    		    	 $("#appleAlert").modal();
						  }
				});
			 $("#mileage_image_div").removeClass("plusLeft fl");
			 $("#mileage_image_div").addClass("minusleft fl");
			 $("#mileage_image_div").one("click",function(){
				   $("#img_pre").empty();
			    	$(this).append('<input	type="file" value="" class="plusFile" id="mileage_image" multiple="multiple" name="file[]" onchange="fileUpload(this);">');
			    	 $("#img-right").show();
			    	 $("#mileage_image_div").removeClass("minusleft fl");
			    	 $("#mileage_image_div").addClass("plusLeft fl");
				   });
			}else{
		       // $("#modal-loading").modal("close");
		        hideLoading();
		        $("#mileage_image_div").append('<input	type="file" value="" class="plusFile" id="mileage_image" multiple="multiple" name="file[]" onchange="fileUpload(this);">');
		        $("#img-right").show();
			}
	}

   function sign(){
	     if(checkForm()){
	    	   var mile = parseFloat($("#mileage").val());
	    	   var hours =parseInt($("#hours").val());
	    	   var minutes = parseInt($("#minutes").val());
	    	   var seconds = parseInt($("#seconds").val());
	           $("#duration").val(hours*60*60+minutes*60+seconds);
	           
// 	           $("#mileForm").attr("action","/runners/sign?t="+new Date().getTime());
// 	           $("#mileForm").submit();
// 	           return;
	           $("#modal-loading").modal();
	    	   $.post("/runners/sign?t="+new Date().getTime(),$("#mileForm").serialize(),function(data){
                      if(data.status==1){
                            window.location = "/runners/mileageinfo/"+data.id;
                          }else{
                        	   showError(data.msg);
                              }
		    	   });
		     }
	   }

   function checkForm(){
         var mile = $.trim($("#mileage").val());
         if(mile==""||!$.isNumeric(mile)||parseFloat(mile)<=0){
        	 showError("请填写正确的里程数！");
        	 return false;
            }
         return true;
	   }

   function cancel(){
	   <?php if(empty($_SERVER['HTTP_REFERER'])):?>
	    closeWechatWindow();
	   <?php else:?>
         history.back();
	   <?php endif;?>
	   }

   $(function(){
         $(window).smoothScroll();
          $(".tabList").children(".tabItem").on("click",function(){
        	    var items = $(".tabList").children(".tabItem");
        	    var curren = $(this);
        	    curren.addClass("cur");
        	    var date=$("#mileage_date");
        	    $("#mileage_date").val(curren.attr("id"));
        	   $.each(items,function(i,n){
                      if($(n).text()!=curren.text()){
                    	    $(n).removeClass("cur");
                          }
            	   });
              });
	   });


   //活动翻滚
   $(function () {
       var settime;
       $(".scrollNews").hover(function () {
           clearInterval(settime);
       }, function () {
           settime = setInterval(function () {
               var $first = $(".scrollNews ul:first");     //选取div下的第一个ul 而不是li；
               var height = $first.find("li:first").height();      //获取第一个li的高度，为ul向上移动做准备；
               $first.animate({ "marginTop": -height + "px" }, 600, function () {
                   $first.css({ marginTop: 0 }).find("li:first").appendTo($first); //设置上边距为零，为了下一次移动做准备
               });
           }, 3000);
       }).trigger("mouseleave");       //trigger()方法的作用是触发被选元素的制定事件类型
   });
   </script>



</head>

<body>
<?php include "header.php"?>
<!-- 星之健身活动 
<div class="scrollNews" style="border-bottom:1px solid #DEDEDE;height:40px;">
    <ul>
        <li style="height:40px;line-height:40px;padding-left:10px;" onclick="window.location.href='/runners/operatactive?t=<?=time()?>'">
            <a href="javascript:void(0);">星之健身打卡奖励活动说明</a>
        </li>
        <li style="height:40px;line-height:40px;padding-left:10px;" onclick="window.location.href='/runners/mileageactive?t=<?=time()?>'">
            <a href="javascript:void(0);">跑友福利-跑量打卡换代金券</a>
        </li>
    </ul>
</div>
-->

<div class="am-alert am-alert-warning" data-am-alert id="msg" style="display: none">
</div>
		<section class="sectionMain" style="margin:14px ">
		<?php if(!$target||$target==0):?>
			<a class="am-btn am-btn-default" href="/wechat/#/runner/mileage" style="color:red;background-color: transparent;border:none;padding: 0 0 8px 0">本月目标跑量还未设置,点我设置</a>
<?php endif;?>
		<form  method="post" id="mileForm">
			<div class="totalSumWrap">
				<input type="number" required="required" class="totalSum" name="Mileage[mileage]" placeholder="0.0" id="mileage">
				<span class="km">KM</span>
			</div>
			<div class="tabList">
				<a id="byestoday" class="tabItem" href="javascript:;">前天</a> <a id="yestoday" class="tabItem" href="javascript:;">昨天</a> <a id="today" href="javascript:;" class="cur tabItem">今天</a>
				<input type="hidden" name="Mileage[mileage_date]" id="mileage_date" value="today">
			</div>
			<div class="tabContent">
				<div class="tabContentEle">
					<div class="tabTop">
						<div class="tabFlex">
							<select  id="hours">
								<option value="0" selected="selected">时</option>
								<?php for($i=0;$i<=24;$i++):?>
								   <option value="<?=$i?>"><?php if($i<10){echo "0$i";}else{echo $i;}?></option>
								<?php endfor;?>
							</select>
						</div>
						<div class="tabFlex">
							<select  id="minutes">
								<option value="0" selected="selected">分</option>
								<?php for($i=0;$i<=60;$i++):?>
								   <option value="<?=$i?>"><?php if($i<10){echo "0$i";}else{echo $i;}?></option>
								<?php endfor;?>
							</select>
						</div>
						<div class="tabFlex">
							<select  id="seconds">
								<option value="0" selected="selected">秒</option>
								<?php for($i=0;$i<=60;$i++):?>
								   <option value="<?=$i?>"><?php if($i<10){echo "0$i";}else{echo $i;}?></option>
								<?php endfor;?>
							</select>
						</div>
                     <input type="hidden" name="Mileage[duration]" id="duration">
					</div>
					<div class="plus clearfix" style="position: relative;">
						<div class="am-form-file">
						     <img id="file-cover" src="/image/paotuanzhuce/step.png" style="width: 50px;height:50px">
						     <input type="hidden" id="mileage_image_value" name="mileage_image">
						     <input	type="file" class="plusFile" id="mileage_image" name="file[]"  style="width: 50px;height:50px"  accept="image/gif,image/png,image/jpg,image/jpeg,image/bmp" multiple="multiple">
						</div>
						 <div  id="img-right" style="line-height: 50px;vertical-align: middle;left:60px;position: absolute;top:0">有图有真相</div>
					</div>
				</div>
			</div>
</form>
			<div class="btnBottom" style="margin-top:14px;width: 100%;text-align: center;">
				<input type="button" class="am-btn am-btn-primary am-btn-block" value="保存" onclick="sign();">
			</div>
			<div style="height:14px"></div>
		</section>
		<!-- 
		<div data-am-widget="navbar" class="am-navbar am-cf am-navbar-default "
id="">
  <ul class="am-navbar-nav am-cf am-avg-sm-1">
      <li>
      <a href="/runners/me?t=<?=time()?>">
        <span class="ion-android-contact am-icon-sm"></span>
        <span class="am-navbar-label">我的</span>
      </a>
    </li>
  </ul>
</div>	
 -->

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

		<form action="/runners/uploadimg" target="upFrame" method="post"
			enctype="multipart/form-data" id="uploadForm" style="display:none">
		</form>
		<iframe id="upFrame" name="upFrame" onload="upLoad();"
			style="display:none;width: 100%"></iframe>
			
<script>
fileuploadMulti("mileage_image",function(data){
		 $("#img-right").hide();
		$("#mileage_image").hide();
		$("#file-cover").attr("src","/image/paotuanzhuce/minus.png");
		$("#file-cover").one("click",function(){
		    	 $("#img-right").show();
		    	 $("#file-cover").attr("src","/image/paotuanzhuce/step.png");
		    	 $("#mileage_image").next().remove();
		    	 $("#mileage_image").show();
			   });
		});
</script>
</body>
</html>

