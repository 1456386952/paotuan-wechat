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
<title><?=$club->club_name?></title>
<?=Util::getMainJsCss()?>
<?=util::getWechatJs()?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
 <script src="http://echarts.baidu.com/build/dist/echarts.js"></script>
<script id="rank-template" type="text/x-handlebars-template">  
{{#each mileages}}
<li style="padding-left:4px;padding-right:4px">
<table style="width: 100%;font-size: 1rem">
<tr><td class="{{#highlight rank}}{{/highlight}}" style="font-size:2rem;font-style: italic;width: 2rem">{{rank}}</td><td style="width: 48px"><img src="{{user.user_face}}" class="am-comment-avatar" style="width:48px;height:48px"></td>
<td style="color:graytext;padding-left: 14px"><span class="am-text-truncate" style="width:11rem;display:inline-block">{{user.nick_name}}</span><span class="am-fr">{{mileage}}</span><div style="background-color:#dddddd;width:100%;height: 4px"><div  style="width: {{percent}}%;height:100%;background-color:#fe4557"></div></div></td></tr>
  </table>
  </li>
{{/each}}
</script>
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

#chartTotalType a{
  padding:0;
  width:48px;
  border-left:none;
  border-top:none;
  border-bottom:none;
  border-right:thin inherit solid;
  background-color: inherit;
}

#chartTotalType a:last-child{
  border-right:none;
}

#chartTotalType .cur{
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

.m-info-km:after{
  content:"KM";
  font-size:1rem;
  margin-left:4px;
}

</style>
   <script type="text/javascript">
   var exstatus=false;
   wx.config({
	    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
	    appId: '<?=$signPackage["appId"];?>',
	    timestamp: <?=$signPackage["timestamp"];?>,
	    nonceStr: '<?=$signPackage["nonceStr"];?>',
	    signature:'<?=$signPackage["signature"];?>',
	    jsApiList: ['chooseWXPay','closeWindow','chooseImage','uploadImage','onMenuShareAppMessage','onMenuShareTimeline']
	});

   $.ajaxSetup({
	   type: "POST",
	   beforeSend:function(){
		    
		   },
	   error:function(req){
		   },
	   complete:function(req, textStatus){
		   hideLoading();
		   myChart.hideLoading();
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

	wx.ready(function(){
	    // 获取“分享给朋友”按钮点击状态及自定义分享内容接口
	    wx.onMenuShareAppMessage({
	    title: <?=json_encode($club->club_name)?>, // 分享标题
	    desc:<?=json_encode($club->club_slogan."\n这是一个值得加入的跑团")?>,
	    link: '<?=Yii::$app->request->hostInfo."/clubs/".$club->club_eng."/home"?>', // 分享链接
	    imgUrl: '<?php if($club->club_bgimage){echo CustomHelper::CreateImageUrl($club->club_logo, "small80");}else{ echo Yii::$app->request->hostInfo."/image/paotuanzhuce/top.png";}?>', // 分享图标
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
	        link: '<?=Yii::$app->request->hostInfo."/clubs/".$club->club_eng."/home"?>', // 分享链接
	        imgUrl: '<?php if($club->club_bgimage){echo CustomHelper::CreateImageUrl($club->club_logo, "small80");}else{ echo Yii::$app->request->hostInfo."/image/paotuanzhuce/top.png";}?>', // 分享图标
	        success: function () { 
	            // 用户确认分享后执行的回调函数
	        },
	        cancel: function () { 
	            // 用户取消分享后执行的回调函数
	        }
	    });
	});



     function alertMsg(msg,callback){
    	 $("#alert-content").text(msg);
	    	 $("#appleAlert").on("closed.modal.amui",callback);
	    	 $("#appleAlert").modal();
         }

     var ECharts;
     var myChart;
     var rankChart;
	 var option = {
    		 title : {
        		 show:false,
    		        text: '跑量(KM)'
    		    },
         tooltip: {
        	 trigger: 'axis',
             show: true
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
                 data : ""
             }
         ],
         yAxis : [
             {
                 type : 'value'
             }
         ],
         series : [
             {
                 name:"跑量",
                 type:"bar",
                 itemStyle:{
           		  normal: {
           			  color:'#01c66c'
           		    }
               	  },
                 "data":""
             }
         ]
     };

	 var rankOption = {
         tooltip: {
        	 trigger: 'axis',
             show: true
         },
          grid:{
        	  x:50,
        	  y:20,
        	  x2:14,
        	  y2:30
        	  },
         xAxis : [
             {
                 type:"value"
             }
         ],
         yAxis : [
             {
            	 type : 'category',
                 data : ["测试1","测试2"]
             }
         ],
         series : [
             {
                 "name":"跑量",
                 "type":"bar",
                 "data":[11,22]
             }
         ]
     };

     

     function getChartData(type){
		   myChart.showLoading();
		   var title="跑量(KM)"
				switch(type){
				case "year":
					$("#pl").text(" 最近年"+title);
					break;
			case "month":
				$("#pl").text(" 最近月"+title);
				break;
			case "week":
					$("#pl").text(" 最近周"+title);
					break;
				case "seven":
					$("#pl").text(" 最近日"+title);
					break;
				}
            $.post("/clubs/<?=$club->club_eng?>/chartdata?t="+new Date().getTime(),{clubid:<?=$club->clubid?>,type:type},function(data,status,req){
            	option.xAxis[0].data=$.parseJSON(data.xAxis);
            	option.series[0].data=$.parseJSON(data.series);
            	myChart.clear();
            	myChart.setOption(option); 
            	myChart.hideLoading();
                });
		}

		function getDataType(tab){
			  $cur = $(tab).children("a[class*='cur']").text();
			  return $.trim($cur);
			}


   $(function(){
	     Handlebars.registerHelper('highlight', function(value, options) {
		       if(value<=3){
			        return "icon-color";
			       }
     	    });
	   });
   </script>
   
   
   
</head>

<body>
	<?php include 'header.php';?>
	<ul class="am-list">
        <li>
	     <a href="register" class="icon-color" data-ignore="true"><i class="ion-home icon-color"></i> 跑团介绍  <small class="am-badge am-fr">加入跑团，查看个人信息</small></a>
        </li>
        <li>
        <a  href="members?<?=time()?>" class="icon-color" data-ignore="true">  <i class="ion-ios-people icon-color"></i> 成员列表 
          <span class="am-badge-thumbed am-fr">
           <?php foreach ($members as $m):?>
          <img  class="am-circle" src="<?=$m->user->user_face?>" style="width: 30px;">
          <?php endforeach;?>
          </span>
        </a>
        </li>
      </ul>
<ul class="am-list">
        <li style="border-top:1px #dedede solid">
        <a  href="mileages" class="icon-color" data-ignore="true"> <i class="ion-android-clipboard icon-color"></i> 跑量打卡<small class="am-badge am-fr"><?=$count?>次/<?=$total?>KM</small></a>
        </li>
      </ul>
  	 	<table style="line-height: 14px;vertical-align: middle;width: 100%">
  	 	<tr>
		   <td  class="content-title" style="padding-left: 14px;height:14px;width:40%"><span class="ion-stats-bars" id="pl">最近日跑量(KM)</span> </td>
   <td>
    <div class="am-btn-group am-btn-group-xs am-fr chart-title" id="chartTotalType">
    <a class="am-btn am-btn-default" href="javascript:void(0);" onclick="getChartData('year');">年</a>
    <a class="am-btn am-btn-default" href="javascript:void(0);" onclick="getChartData('month');">月</a>
    <a class="am-btn am-btn-default am-btn-xs" href="javascript:void(0);" onclick="getChartData('week');">周</a>
    <a class="am-btn am-btn-default am-btn-xs cur" href="javascript:void(0);" onclick="getChartData('seven');">日</a>
   </div>
	</td>
	</tr>
</table>
	 <div id="chart" style="width:100%;height:200px;display:block;padding: 0;"></div>
      <div style="height:22px"></div>
        
  	<div data-am-widget="navbar" class="am-navbar am-cf am-navbar-default "
id="">
  <ul class="am-navbar-nav am-cf am-avg-sm-3">
      <li>
      <a href="/runners/me" data-ignore="true">
        <span class="ion-android-contact am-icon-sm"></span>
        <span class="am-navbar-label">我的</span>
      </a>
    </li>
  </ul>
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

<div class="am-modal am-modal-alert" tabindex="-1" id="club_desc">
       <textarea rows="4" cols="" readonly="readonly" style="border: none;width: 100%;overflow: visible;"><?=$club->club_desc?></textarea>
</div>

<footer data-am-widget="footer" class="am-footer am-footer-default"> 
<!-- 
<div class="am-g am-g-fixed">
  			<div class="am-u-sm-3"> <hr class="am-divider am-divider-default"
/></div>
  			<div class="am-u-sm-6 am-text-center" ><?=$club->club_name?>&nbsp;<span class="am-icon-copyright"></span> <?=date("Y")?></div>
  				<div class="am-u-sm-3"> <hr class="am-divider am-divider-default"
/></div>
	        </div>
	            -->
	        </footer>
	     
	        
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
   getRankListData(null,true,true,'#defaultLoad','#rankList',defaultLimit);
   });

$("#chartTotalType a").on("click",function(){
    var items = $("#chartTotalType a");
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

    function initChart(ec){
          initMileage(ec);
          //initRank(ec);
        }

    function initMileage(ec){
    	    myChart = ec.init(document.getElementById('chart')); 
    	    getChartData("seven");
        }
    function  initRank(ec){
    	  rankChart = ec.init(document.getElementById('rankChart'));
    	   getRankChartData("seven");
        }

    require(
            [
                'echarts',
                'echarts/chart/bar', // 使用柱状图就加载bar模块，按需加载
                'echarts/chart/line' // 使用柱状图就加载bar模块，按需加载
            ],
            initChart
        );
    window.onresize = function(){
        myChart.resize();
        //rankChart.resize();
    }
  
   // getRecentMileages();
    
</script>