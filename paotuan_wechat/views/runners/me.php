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
<title> <?php if($infoUser&&$infoUser->uid!=$user->uid):?><?=$user->nick_name?><?php else:?>我<?php endif;?></title>
<?=Util::getMainJsCss()?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
 <script src="http://echarts.baidu.com/build/dist/echarts.js"></script>
 
 
<style type="text/css">

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

.am-comment-avatar{
 float: left;
  width: 70px;
  height: 32px;
  border-radius:0;
  margin-left:4px;
  margin-top:10px
}

.am-comment-main {
  position: relative;
  margin-left:64px;
  border: 1px solid #dedede;
  border-radius: 0;
  width:80%;
}

.m-info-km:after{
  content:"KM";
  font-size:1rem;
  margin-left:4px;
}

.am-list>li:first-child{
  border-top: none;
}

.am-club-list{
  margin-left: 33px
}

.am-g-fixed {
  max-width:700px;

}

</style>

<script id="recent-template" type="text/x-handlebars-template">  

{{#each mileages}}
<div class="am-panel am-panel-default" style="margin-left:14px;margin-right:14px" id="minfo-{{mileage.id}}">
  <div class="am-panel-hd am-comment-meta am-cf" style="line-height:26px;vertical-align: middle;background-color:transparent;color:graytext">
{{mileage.mileage_date}}
       <span class="am-fr">{{#if mileage.canDelete}}
<a class="am-icon-btn  ion-ios-close-empty minfo-icon-btn" href="javascript:void(0);" id="minfo-a-{{mileage.id}}" onclick="deleteRecent({{mileage.id}},{{mileage.mileage}});"></a>
{{/if}}</span>
</div>
  <div class="am-panel-bd am-comment-bd">
    <a href="/runners/mileageinfo/{{mileage.id}}" class="am-btn am-btn-default am-btn-block">
 <table style="width: 100%"><tr><td style="width: 48px"><img src="{{#if mileage.albums}}http://xiaoi.b0.upaiyun.com/{{mileage.albums}}{{else}}/image/paotuanzhuce/list-default-pic.jpg{{/if}}"  width="48" height="48"></td>
<td class="m-info-km" style="text-align: left;font-size:20px;padding-left:10px">{{mileage.mileage}}</td><td>{{#if mileage.pace}}<i class="ion-ios-stopwatch-outline"> {{mileage.pace}}{{/if}}</td><td align="right">{{#if mileage.format_duration}}<i class="ion-ios-timer-outline"> {{mileage.format_duration}}{{/if}}</td></tr>
  </table>
</a>
  </div>
</div>
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
		   myChart.hideLoading();
		   },
		   statusCode: {500: function() {
			    $("#alert-content-error").text("服务器异常，请稍候重试或联系管理员！");
 		    	 $("#appleAlertError").modal();
			   },
			   408:function(){
				   $("#alert-content-error").text("请求超时，请稍候重试.");
	 		    	 $("#appleAlertError").modal();
				   }  
	          }
	   }); 
	  

   wx.error(function(res){
	     $.each(res,function(i,n){
                 //alert(i+"----"+n);
		     });
	});

	function showError(text){
		$("#alert-content-error").text(text);
    	 $("#appleAlertError").modal();
		}

	function closeWechatWindow(){
		wx.closeWindow();
		}
	 var myChart ;
	 var option = {
    		 title : {
        		 show:false,
    		        text: '跑量(KM)'
    		    },
         tooltip: {
        	 trigger: 'axis',
             show: true
         },
         toolbox:{
        	 show : false,
             feature:{
         	  dataZoom : {
         	        show : true,
         	        title : {
         	            dataZoom : '区域缩放',
         	            dataZoomReset : '区域缩放后退'
         	        }
         	    }
              }
          },
          grid:{
        	  x:40,
        	  y:20,
        	  x2:14,
        	  y2:30
        	  },
         xAxis : [
             {
                 type : 'category',
                 data : <?=$chartData["xAxis"]?>
             }
         ],
         yAxis : [
             {
                 type : 'value'
             }
         ],
         series : [
             {
                 "name":"跑量",
                 "type":"bar",
                 itemStyle:{
              		  normal: {
              			  color:'#009ada'
              		    }
                  	  },
                 "data": <?=$chartData["series"]?>
             }
         ]
     };
     var charType="seven";
	function getChartData(type){
		charType=type;
		myChart.showLoading();
		var title="跑量(KM)"
		switch(type){
		case "year":
			$("#tjt").text(" 年"+title);
			break;
	case "month":
		$("#tjt").text(" 月"+title);
		break;
	case "week":
			$("#tjt").text(" 周"+title);
			break;
		case "seven":
			$("#tjt").text(" 日"+title);
			break;
		}
            $.post("/runners/chartdata?t="+new Date().getTime(),{type:type,uid:<?=$user->uid?>},function(data,status,req){
            	option.xAxis[0].data=$.parseJSON(data.xAxis);
            	option.series[0].data=$.parseJSON(data.series);
            	myChart.clear();
            	myChart.setOption(option); 
            	myChart.hideLoading();
                });
		}

	var offset=0;

	function getRecentMileages(append){
		//$("#recentMileages").show();
	$("#loadMore").button("loading");
		if(!append){
				$('#recentMileages').modal();
				 // $(window).smoothScroll({position:$("#m-info-title").offset().top});
				offset=0;
			}
            $.post("/runners/recentmileages?t="+new Date().getTime(),{offset:offset,limit:8,uid:<?=$user->uid?>},function(data){
                if(data.length==8){
            	   offset = offset+7;
            	   $("#loadMore").button("reset");
            	   $("#loadMore").show();
            	      data.pop();
                   }else{
                	   $("#loadMore").hide();
                       }
        
           	    var source   = $("#recent-template").html();  
            	  var template = Handlebars.compile(source); 
            	  if(!append){
            		  $("#minfo-list").empty();
                	  }
            	  $("#minfo-list").append(template({mileages:data}));
            	 $.AMUI.gallery.init();
                });
		}

	function deleteRecent(id,mileage){
	
		$.ajax({
			url:"/runners/deleterecent?t="+new Date().getTime(),
			data:{id:id},
			beforeSend:function(){
				$("#minfo-a-"+id).removeClass("ion-ios-close-empty");
				$("#minfo-a-"+id).addClass("am-icon-spinner am-icon-spin");
				},
			success:function(data){
				if(data.status==1){
	                  $("#minfo-"+id).remove();
	                  $("#count").text(parseInt($("#count").text())-1);
	                  $("#total").text(parseFloat($("#total").text())-mileage);
	                  getChartData(charType);
	                }
				},
			  complete:function(){
				  $("#minfo-a-"+id).addClass("ion-ios-close-empty");
					$("#minfo-a-"+id).removeClass("am-icon-spinner am-icon-spin");
				  }
			});
		}

	

   $(function(){
	   });

   </script>



</head>

<body>

<!-- 第三方账号绑定 
<div style="position:absolute;right:0px;top:5px;">
    <div style="font-size:15px;">
        <img src="/image/bind/codoon.png" style="width:20px;height:20px;"/>咕咚
        <a href="/codoon/user" style="border-radius:4px;border:1px solid gray;color:black;padding:2px;margin-left:10px;font-size:14px;">绑定</a>
    </div>
    <?php // if ($codoonbind):?>
    <div>
        <img src="/image/bind/codoon.png" style="width:20px;height:20px;"/>
        <?=$codoonbind->nick_name?>
        <span>绑定时间：<?=date('Y.m.d',strtotime($codoonbind->create_time))?></span>
    </div>
    <?php //endif;?>
</div>
------------->

	<?php include "header.php"?>
  	<ul class="am-list">
        <li>
        <a href="javascript:void(0);"  onclick="getRecentMileages(false);" class="icon-color" > <i class="ion-ios-calculator-outline icon-color"></i> 跑量统计<small class="am-badge am-fr"><?=count($mileages)?>次/<?=$total?>KM</small></a>
        </li>
      </ul>
  	<table style="line-height: 14px;vertical-align: middle;width: 100%">
  	 	<tr>
		   <td  class="content-title" style="padding-left: 14px;height:14px;width:30%"><span class="ion-stats-bars" id="pl">日跑量(KM)</span> </td>
   <td>
    <div class="am-btn-group am-btn-group-xs am-fr chart-title" id="chartType">
    <a class="am-btn am-btn-default" href="javascript:void(0);" onclick="getChartData('year');">年</a>
    <a class="am-btn am-btn-default" href="javascript:void(0);" onclick="getChartData('month');">月</a>
    <a class="am-btn am-btn-default am-btn-xs" href="javascript:void(0);" onclick="getChartData('week');">周</a>
    <a class="am-btn am-btn-default am-btn-xs cur" href="javascript:void(0);" onclick="getChartData('seven');">日</a>
   </div>
	</td>
	</tr>
</table>
 <div id="chart" style="width:100%;height:200px;display:block;padding: 0;"></div>

  <?php if(!$infoUser||$infoUser->uid==$user->uid):?>
   <ul class="am-list">
        <li style="border-top:1px #dedede solid">
        <a href="/clubs/new" class="icon-color" > <i class="ion-android-people icon-color"></i> 我的跑团<small class="am-badge am-fr" style="margin-right:22px">创建我的跑团</small></a>
        </li>
  </ul>
	<?php if(count($clubs)>0):?>
	
	 <ul class="am-list am-club-list am-list-item-thumb-left">
	  <?php foreach($clubs as $club):?>
       <?php if($club):?>
        <li class="am-list-item-thumbed icon-color">
        <?php if(trim($club->club_eng)):?>
        <a href="/clubs/<?=$club->club_eng?>/home" class="icon-color">
            <img class="thumb-circle"  alt="" src="<?=CustomHelper::CreateImageUrl($club->club_logo, "small80")?>" onerror="this.src='/image/club_default.png'">
             <span class="am-text-truncate am-list-item-thumbed-title"> <?=$club->club_name?></span>
             <small class="am-badge am-fr"><?=count($club->members)?>人</small>
        </a>
        <?php else:?>
         <img class="thumb-circle"  alt="" src="<?=CustomHelper::CreateImageUrl($club->club_logo, "small80")?>" onerror="this.src='/image/club_default.png'">
             <span class="am-text-truncate am-list-item-thumbed-title"> <?=$club->club_name?></span>
             <small class="am-badge am-fr"><?=count($club->members)?>人</small>
        <?php endif;?>
        </li>
        <?php endif;?>
        <?php endforeach;?>
  </ul>
	
<?php else:?>
 <!--  
  <div class="color:graytext"> 您还没有加入跑团!</div>
  -->
<?php endif;?>
<div class="am-container" style="text-align: center;padding-top:20px;padding-bottom:20px">
<a class="am-btn am-radius icon-color" style="border:thin #cb352b solid" href="/clubs/find"> <i class="ion-android-compass am-icon-sm"></i> 发现新跑团</a>
</div>
 <?php endif;?>
 
 <div id="recentMileages"  class="am-popup">
  	<div class="am-popup-inner">
  	  <div class="am-popup-hd">
		 <span class="ion-android-clipboard  am-popup-title content-title"> 最近打卡</span>
        <span data-am-modal-close
            class="am-close">&times;</span>
       </div>
        <div class="am-popup-bd">
        <sestion class="am-comments-list" id="minfo-list">
       </sestion>
       <button class="am-btn am-btn-link am-btn-block" data-am-loading="{spinner: 'circle-o-notch', loadingText: '加载中...'}" onclick="getRecentMileages(true);" id="loadMore" >查看更多</button>
  	</div>
  	</div>
  	
  </div>
  
<div data-am-widget="navbar" class="am-navbar am-cf am-navbar-default "
id="">
  <ul class="am-navbar-nav am-cf am-avg-sm-4">
    <li>
      <a href="/runners/mileages" class="">
        <span class="ion-android-clipboard am-icon-sm"></span>
        <span class="am-navbar-label">打卡</span>
      </a>
       </li>
      <?php if($infoUser&&$infoUser->uid!=$user->uid):?>
         <li>
      <a href="/runners/me">
        <span class="ion-android-contact am-icon-sm"></span>
        <span class="am-navbar-label">我的</span>
      </a>
      </li>
      <?php endif;?>
   
  </ul>
</div>		
		

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
		

</body>
</html>

<script>
$("#chartType a").on("click",function(){
	    var items = $("#chartType a");
	    var curren = $(this);
	    curren.addClass("cur");
	   $.each(items,function(i,n){
          if($(n).text()!=curren.text()){
        	    $(n).removeClass("cur");
              }
	    });
	   });

	require.config({
        paths: {
            echarts: 'http://echarts.baidu.com/build/dist'
        }
    });

    require(
            [
                'echarts',
                'echarts/chart/bar', // 使用柱状图就加载bar模块，按需加载
                'echarts/chart/line' // 使用柱状图就加载bar模块，按需加载
            ],
            function (ec) {
                // 基于准备好的dom，初始化echarts图表
                 myChart = ec.init(document.getElementById('chart')); 
                
                // 为echarts对象加载数据 
                myChart.setOption(option); 
                window.onresize = myChart.resize;
            }
        );
    
</script>