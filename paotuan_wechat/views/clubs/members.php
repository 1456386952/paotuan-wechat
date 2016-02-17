<?php 
use common\component\CustomHelper;
use yii\helpers\ArrayHelper;
use paotuan_wechat\component\Util;
use paotuan_wechat\controllers\ClubsController;
header("Content-type: text/html; charset=utf-8");
if(is_null($club)){
	echo "<h1>跑团信息不存在！</h1>";
	exit;
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title><?=$club->club_name?></title>
<?=Util::getMainJsCss();?>
<?=Util::getWechatJs()?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script id="members-template" type="text/x-handlebars-template">  
        {{#each members}}  
  			<li style="color:graytext">
    			<div class="am-gallery-item">
        			<a href="/runners/userinfo/{{uid}}"><img class="am-circle" src="{{user_face}}!80X80" style="width: 60px;height: 60px" /></a>
					<span style="font-size:10px;width:70px;white-space:nowrap;overflow: hidden;text-overflow:ellipsis;display:inline-block;"><span class="am-icon-{{#getgender gender}}{{/getgender}}"></span> {{nick_name}}</span>
				</div>
 			 </li>
	  {{/each}}  
</script>
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
		   $("#loadMore").button("reset");
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


	function closeWechatWindow(){
		wx.closeWindow();
		}
	
     function alertMsg(msg,callback){
    	 $("#alert-content").text(msg);
	    	 $("#appleAlert").on("closed.modal.amui",callback);
	    	 $("#appleAlert").modal();
         }

     var offset=0;
 	function getMembers(){
		//$("#recentMileages").show();
	$("#loadMore").button("loading");
           $.post("/clubs/<?=$club->club_eng?>/getmembers?t="+new Date().getTime(),{clubid:"<?=$club->clubid?>",offset:offset,limit:26},function(data){
                if(data.members.length==26){
            	   offset = offset+25;
            	   $("#loadMore").button("reset");
            	   $("#loadMore").show();
            	    data.members.pop();
                   }else{
                	   offset=0;
                	   $("#loadMore").hide();
                       }
            
           	    var source   = $("#members-template").html();  
            	  var template = Handlebars.compile(source);
            	  $("#members").append(template(data));
                });
		}

	function home(){
            if($("#home")){
            	$.afui.loadContent("#home",false,false,"up");
                }
		}

   $(function(){
         $(window).smoothScroll();
         getMembers();
         Handlebars.registerHelper('getgender', function(value, options) {
        	    switch(value){
        	    case 1:
            	    return 'male';
        	    case 2:
            	    return 'female';
        	    }
        	});
	   });
   </script>
   
   
   
</head>

<body>
<div class="view" id="mainView">
   <div class="pages">
     <div class="panel"  style="padding: 0">
	<?php include 'header.php';?>
	<ul class="am-list">
        <li>
        <a  href="register" class="icon-color" data-ignore="true">共<?=count($club->members)?>名会员 <small class="am-badge am-fr" style="margin-right:22px">入会，修改我的会员信息</small></a>
        </li>
    </ul>
<ul data-am-widget="gallery" class="am-gallery am-avg-sm-4 am-avg-md-7 am-avg-lg-10 am-gallery-default" id="members" style="width:100%;text-align: center;">
	<li style="color:graytext">
    			<div class="am-gallery-item">
        			<a href="/runners/userinfo/<?=$clubOwner->uid?>" class="am-member-icon-owner"><img class="am-circle" src="<?=$clubOwner->user_face?>!80X80" style="width: 60px;height: 60px" /></a>
					<span style="font-size:10px;width:70px;white-space:nowrap;overflow: hidden;text-overflow:ellipsis;display:inline-block;"><span class="am-icon-<?php if($clubOwner->user_gender==1):?>male<?php else:?>female<?php endif;?>"></span> <?=$clubOwner->nick_name?></span>
				</div>
 			 </li>
</ul>
 <button class="am-btn am-btn-link am-btn-block" data-am-loading="{spinner: 'circle-o-notch', loadingText: '加载中...'}" onclick="getMembers();" id="loadMore" >查看更多</button>

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
</div>
</div>
</div>

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

<div class="am-modal am-modal-loading am-modal-no-btn" tabindex="-1" id="modal-loading">
  <div class="am-modal-dialog">
    <div class="am-modal-hd"></div>
    <div class="am-modal-bd">
      <span class="am-icon-spinner am-icon-spin"></span>
    </div>
  </div>
</div>
</body>
</html>
