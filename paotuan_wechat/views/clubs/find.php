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
<title>发现跑团</title>
 <link href="/css/paotuanzhuce/app.css?<?=time()?>" type="text/css" rel="stylesheet"/>
 <link rel="stylesheet" href="/css/amazeui.min.css"/>
 <link href="/css/wechat.css?<?=time()?>" type="text/css" rel="stylesheet"/>
 <link href="/css/ionicons/css/ionicons.min.css" type="text/css"	rel="stylesheet" />
<script type="text/javascript" src="/js/jquery.min.js"></script>
 <script src="/js/amazeui.min.js"></script>
<script type="text/javascript" src="/js/paotuanzhuce/app.js?<?=time()?>"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="/js/handlebars.min.js"></script>
<script id="club-template" type="text/x-handlebars-template">  
    {{#each clubs}}
      <li class="am-g-fixed am-list-item-desced am-list-item-thumbed am-list-item-thumb-left ">
       <table style="table-layout: fixed;width: 100%"><tr><td style="width:66px">
        <div class="am-list-thumb thumb-circle" >
            <img src="http://xiaoi.b0.upaiyun.com/{{club_logo}}"/>
        </div>
        </td><td>
        <div class=" am-list-main">
          <h3 class="am-list-item-hd am-text-truncate">
           {{#if club_eng}}
            <a href="/clubs/{{club_eng}}/home" class="">{{club_name}}</a>
          {{else}}
             {{club_name}}
           {{/if}}
          </h3>
          <div class="am-list-item-text"><i class="ion-ios-people-outline icon-color" ></i> {{members}}</div>
        </div>
        </td></tr></table>
      </li>
{{/each}}
</script>
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
	  


     function alertMsg(msg,callback){
    	 $("#alert-content").text(msg);
	    	 $("#appleAlert").on("closed.modal.amui",callback);
	    	 $("#appleAlert").modal();
         }

     function findClub(){
             var name=$.trim($("#name").val());
             if(name!=""){
            	 $("#loadingBtn").button("loading");
              	$("#loadingBtn").show();
              	$("#findResult").empty();
                  $.post("/clubs/findclub",{name:name},function(data){
                		 $("#loadingBtn").hide();
                		 if(data.clubs.length==0){
                			 $("#findResult").html("<div  style='font-size:14px;margin-top:33px;width:100%;text-align:center;color:graytext'><small>没有找到您要查找的跑团</small></div>");
                    		 }else{
                		    var source   = $("#club-template").html();  
                      	     var template = Handlebars.compile(source); 
                      	     $("#findResult").html(template(data));
                      	     $("img").on("error",function(){
                                     $(this).attr("src","/image/club_default.png");
                          	     });
                    		 }
                      });
                 }
             return false;
         }

   $(function(){
	   
	   });
   </script>
   
   
   
</head>

<body>
<div class="am-container">
  <div class="am-u-sm-centered">
    <form class="am-form" method="post" onsubmit="return findClub();">
      <fieldset class="am-form-set">
        <div class="am-form-group am-form-icon am-form-feedback">
     <input type="search" class="am-form-field am-round am-center" placeholder="请输入跑团名称" style="margin-top:14px" id="name" name="name"/><span class="am-icon-search" style="color: graytext" onclick="findClub();"></span>
   </div>
   <a href="/clubs/new" class="icon-color" style="text-decoration:underline;">创建我的跑团</a>
      </fieldset>
    </form>
  </div>
  <div data-am-widget="list_news" class="am-list-news am-list-news-default">
  <div class="am-list-news-bd">
    <ul class="am-list" id="findResult">
      
    </ul>
  </div>
</div>
 <a  id="loadingBtn" style="display: none"  class="am-btn am-btn-link btn-loading-example am-btn-block" data-am-loading="{spinner: 'circle-o-notch', loadingText: '加载中...'}">重新加载</a>

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

<script>
	app.showAndHide();
</script>