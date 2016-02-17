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
<link href="/css/paotuanzhuce/app.css?<?=time()?>" type="text/css"
	rel="stylesheet" />
<link rel="stylesheet" href="/css/amazeui.min.css" />
<link href="/css/wechat.css?<?=time()?>" type="text/css"	rel="stylesheet" />
<link href="/css/ionicons/css/ionicons.min.css" type="text/css"	rel="stylesheet" />
<script type="text/javascript"
	src="/js/paotuanzhuce/jquery-1.9.1.min.js"></script>
<script src="/js/amazeui.min.js"></script>
<script src="/js/handlebars.min.js"></script>
<script type="text/javascript" src="/js/paotuanzhuce/app.js"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
 <script src="http://echarts.baidu.com/build/dist/echarts.js"></script>
<script id="recent-template" type="text/x-handlebars-template">  
{{#each mileages}}
<article class="am-comment">
                  <img src="{{mileage.user.user_face}}" class="am-comment-avatar"/>
                 <div class="am-comment-main">
    <header class="am-comment-hd">
      <div class="am-comment-meta am-cf am-g am-fixed"  style="padding:4px 0 4px 0">
      <div class="am-u-sm-6" >{{mileage.user.nick_name}}</div>
       <div class="am-u-sm-6" style="text-align: right">{{mileage.mileage_date}}</div>
        </div>
    </header>
    <div class="am-comment-bd {{#from mileage.from}}{{/from}}">
    <a href="/runners/mileageinfo/{{mileage.id}}" class="am-btn am-btn-default am-btn-block">
 <table style="width: 100%"><tr><td style="width: 48px"><img src="{{#if mileage.albums}}http://xiaoi.b0.upaiyun.com/{{mileage.albums}}!80X80{{else}}/image/paotuanzhuce/list-default-pic.jpg{{/if}}"  width="48" height="48"></td>
<td class="m-info-km" style="text-align: left;font-size:20px;">{{mileage.mileage}}</td><td>{{#if mileage.pace}}<i class="ion-ios-stopwatch-outline"> {{mileage.pace}}{{/if}}</td><td align="right">{{#if mileage.format_duration}}<i class="ion-ios-timer-outline"> {{mileage.format_duration}}{{/if}}</td></tr>
  </table>
</a>
    </div>
  </div>
 </article>
{{/each}}
</script>
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
    color:#009ada;
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
    color:#009ada;
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

.am-comment-main{
  position: relative;
}
.codoon:after{
	content:"来自 咕咚";
	width:200px;
    font-size: 10px;
    top:72px;
    position: absolute;
    left:4px;
    color: graytext;
}

.edoon:after{
	content:"来自 益动GPS";
	width:200px;
    font-size: 10px;
    top:72px;
    position: absolute;
    left:4px;
    color: graytext;
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
		   $("#modal-loading").modal("close");
		   $("#loadMore").button("reset");
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
	    link: '<?=Yii::$app->request->hostInfo."/clubs/".$club->club_eng."/mileages"?>', // 分享链接
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
	        link: '<?=Yii::$app->request->hostInfo."/clubs/".$club->club_eng."/mileages"?>', // 分享链接
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
           			  color:'#009ada'
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
					$("#pl").text(" 年"+title);
					break;
			case "month":
				$("#pl").text(" 月"+title);
				break;
			case "week":
					$("#pl").text(" 周"+title);
					break;
				case "seven":
					$("#pl").text(" 日"+title);
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

		var rankType="seven";
     function getRankChartData(type){
    	 rankType = type;
    	 var title="排行榜"
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
    	 rankChart.showLoading();
          $.post("/clubs/<?=$club->club_eng?>/getrank?t="+new Date().getTime(),{type:type},function(data,status,req){
          	rankOption.yAxis[0].data=$.parseJSON(data.yAxis);
            rankOption.series[0].data=$.parseJSON(data.series);
          	rankChart.clear();
          	rankChart.setOption(rankOption); 
          	rankChart.hideLoading();
              });
		}

     var rankOffset=0;
     var no1=0;
     var defaultLimit=6;
     var moreLimit=11;
     var type="seven";
     var rankType="seven";
     function getRankListData(moreType,isDefault,isNew,loadBtn,appendto,limit){
    	 rankType = moreType;
    	 if(isNew){
    		 rankOffset=0;
    		 if(isDefault){
    		 $("#showMore_btn").hide();
    		 }
    		 $(appendto).empty();
        }
    	 var title="排行榜"
        	 if(moreType==null){
        	    type = getDataType("#chartType");
        	 }else{
                 type=moreType;
            	 }
    			switch(type){
    			case "年":
    				type="year";
    				 if(moreType==null){
    				   $("#tjt").text(" 年"+title);
    				 }
    				$("#tjt_more").text(" 年"+title);
    				break;
    		case "月":
    			type="month";
    			 if(moreType==null){
    			$("#tjt").text(" 月"+title);
    			 }
    			$("#tjt_more").text(" 月"+title);
    			break;
    		case "周":
    			    type="week";
    				 if(moreType==null){
    				$("#tjt").text(" 周"+title);
    				 }
    				$("#tjt_more").text(" 周"+title);
    				break;
    			case "日":{
    				  type="seven";
    					 if(moreType==null){
    				$("#tjt").text(" 日"+title);
    					 }
    				$("#tjt_more").text(" 日"+title);
    				break;
    			}
    		}
    		
    	 $(loadBtn).button("loading");
    	 $(loadBtn).show();
         $.ajax({
               url:"/clubs/<?=$club->club_eng?>/getranklist?t="+new Date().getTime(),
               data:{type:type,offset:rankOffset,limit:limit},
               success:function(data){
            	   if(data.length>0&&rankOffset==0){
                 	  no1 =data[0].mileage;
                       }
               	  if(data.length==limit){
             		  rankOffset = rankOffset+limit-1;
                	   $(loadBtn).button("reset");
               	       $(loadBtn).show();
                	      data.pop();
                	      if(isDefault){
                    	      $("#showMore_btn").show();
                    	  }
                    }else{
                    	  if(isDefault){
                 	     $("#showMore_btn").hide();
                    	  }
                    	   $(loadBtn).hide();
                        }
                    if(isDefault){
                     	  $(loadBtn).hide();
                       }
               	    var source   = $("#rank-template").html();  
                	  var template = Handlebars.compile(source); 
                	  if(!isNew){
                	     $(appendto).append(template({mileages:data}));
                	    }else{
                	    	$(appendto).html(template({mileages:data}));
                	    	  if(data.length==0){
                           	   $(appendto).html("<div  style='font-size:14px;margin-top:33px;width:100%;text-align:center;color:graytext'><small>暂无排名</small></div>");
                               }
                    	    }
                   }
             });
		}

		function getDataType(tab){
			  $cur = $(tab).children("a[class*='cur']").text();
			  return $.trim($cur);
			}

		function showMoreRankInfo(){
               $("#rankListMore").modal();
               $('#rankListMore').on('opened.modal.amui', function(){
            	   $("#"+type+"_nav").click();
            	 });
               rankOffset=0;
			}
		

   $(function(){
	     Handlebars.registerHelper('highlight', function(value, options) {
		       if(value<=3){
			        return "icon-color";
			       }
     	    });

	     Handlebars.registerHelper('from', function(value, options) {
		       if(value==2){
			        return "codoon";
			       }
		       if(value==4){
			        return "edoon";
			       }
   	    });
	   getRankListData(null,true,true,"#defaultLoad","#rankList",defaultLimit);
	   
	   });
   </script>
   
   
   
</head>

<body>
	<?php //include 'header.php';?>
<section class="registerWrap">
<button class="am-btn am-btn-default am-btn-block" <?php if($total>0):?> onclick="getRecentMileages(false);"<?php endif;?> style="background-color: transparent;border: none;padding:0;line-height:50px">
  		<table style="border: none;width: 100%">
		<tr>
			<td style="border-left: 0;border-right: 0;border-top:0"  width="25%" align="right">
			<img src="/image/paotuanzhuce/mileage_check.png" style="width: 30px">
			</td>
			<td style="border-left: 0;border-top:0"  width="25%" align="left">
			<h5 style="margin:10px 0 0 4px" ><?=$count?>次</h5></td>
			<td style="border-left:thin #eee solid"  width="25%" align="right">
			<img src="/image/paotuanzhuce/paoliang.png" style="width: 30px">
			</td>
			<td style="border-right:0;border-left: 0;;border-top:0"  width="25%" align="left">
			<h5  style="margin:10px 0 0 4px"><?=$total?>Km
			 </h5>
			 </td>
		</tr>
	</table>
  	</button>
  	<hr>
  	 	<table style="line-height: 14px;vertical-align: middle;width: 100%">
  	 	<tr>
		   <td  class="content-title" style="padding-left: 14px;height:14px;width:30%;color:#000"><span class="am-icon-bar-chart main-color"></span><span  id="pl">日跑量(KM)</span> </td>
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
	 <hr>
  	<table style="line-height: 14px;vertical-align: middle;width:100%">
  	<tr>
	<td class="content-title" style="padding-left: 14px;height:14px;width:30%;color:#000"><span class="ion-trophy main-color" ></span><span id="tjt"> 排行榜</span> </td>
   <td style="height:14px">
    <div class="am-btn-group am-btn-group-xs am-fr chart-title" id="chartType" >
    <a class="am-btn am-btn-default" href="javascript:void(0);" >年</a>
    <a class="am-btn am-btn-default" href="javascript:void(0);">月</a>
    <a class="am-btn am-btn-default am-btn-xs" href="javascript:void(0);">周</a>
    <a class="am-btn am-btn-default am-btn-xs cur" href="javascript:void(0);">日</a>
   </div>
  </td>
  </tr>
</table>
<!--  
 <div id="rankChart" style="width:100%;height:200px;display:block;padding: 0;"></div>
	 <hr>
-->	 
	 
  <div style="width:100%;display:block;padding: 0 14px 0 14px;">
  <ul class="am-list am-list-static" id="rankList">

  </ul>
  <button class="am-btn am-btn-link am-btn-block " data-am-loading="{spinner: 'circle-o-notch', loadingText: '加载中...'}" id="defaultLoad" onclick="getRankListData(true,true,'#defaultLoad','#rankList',defaultLimit);">重新加载</button>
  <button class="am-btn am-btn-link am-btn-block " data-am-loading="{spinner: 'circle-o-notch', loadingText: '加载中...'}" id="showMore_btn" onclick="showMoreRankInfo();" style="display: none">查看更多</button>
  </div>
	 <hr>
  	
  	<div class="am-g am-cf am-g-collapse" style="line-height: 1.2rem;vertical-align: middle;">
		   <div class="am-u-sm-4 content-title" style="padding-left: 14px;color:#000"><span class="ion-android-clipboard am-icon-sm main-color"></span><span> 最近打卡</span> </div>
	</div>
	<sestion class="am-comments-list" id="recent-list">
	      <?php for($i=0;$i!=count($mileages);$i++):?>
	      	 <?php if($i<=5):?>
	      	 	<article class="am-comment">
                  <img src="<?=$mileages[$i]->user->user_face?>" class="am-comment-avatar"/>
                 <div class="am-comment-main">
    <header class="am-comment-hd">
      <div class="am-comment-meta am-cf am-g am-fixed"  style="padding:4px 0 4px 0">
      <div class="am-u-sm-6 am-text-truncate" ><?=$mileages[$i]->user->nick_name?></div>
       <div class="am-u-sm-6" style="text-align: right"><?=$mileages[$i]->mileage_date?></div>
    </header>
    <div class="am-comment-bd <?php if($mileages[$i]->from==2):?>codoon<?php endif;?> <?php if($mileages[$i]->from==4):?>edoon<?php endif;?>">
    <a href="/runners/mileageinfo/<?=$mileages[$i]->id?>" class="am-btn am-btn-default am-btn-block" style="padding-left:0;text-align: left;">
 <table style="width: 100%"><tr><td style="width: 48px"><img src="<?php if(count($mileages[$i]->albums)>0):?><?=CustomHelper::CreateImageUrl($mileages[$i]->albums[0]->image_url,"small80")?><?php else:?>/image/paotuanzhuce/list-default-pic.jpg<?php endif;?>"  width="48" height="48"></td>
<td class="m-info-km" style="text-align: left;font-size:20px"><?=round($mileages[$i]->mileage,2)?></td><td><?php if(!empty($mileages[$i]->pace)):?><i class="ion-ios-stopwatch-outline"> <?=$mileages[$i]->pace?><?php endif;?></td><td align="right"><?php if(!empty($mileages[$i]->format_duration)):?><i class="ion-ios-timer-outline"> <?=$mileages[$i]->format_duration?><?php endif;?></td></tr>
  </table>
</a>
    </div>
  </div>
              </article>
	      	 <?php endif;?>
	     <?php endfor;?>
	    <?php if(count($mileages)>5):?>
	     <button class="am-btn am-btn-link am-btn-block" data-am-loading="{spinner: 'circle-o-notch', loadingText: '加载中...'}" onclick="getRecentMileages(false);">查看更多</button>
       <?php endif;?>
       </section>
       
</section>

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
  
   <div id="rankListMore"  class="am-popup">
  	<div class="am-popup-inner">
  	  <div class="am-popup-hd">
		 <span class="ion-trophy am-popup-title content-title" id="tjt_more"> 排行榜</span>
        <span data-am-modal-close
            class="am-close">&times;</span>
       </div>
        <div class="am-popup-bd">
        <div data-am-widget="tabs" id="rankMore-tabs" class="am-tabs am-tabs-default" style="margin:0;line-htight:22px">
  <ul class="am-tabs-nav am-cf" id="rankMore-nav" style="width: 80%;margin-left: 10%;height:22px;background-color: transparent;">
    <li>
       <a  id="year_nav" style="padding:0;line-height:22px" class="am-round" onclick='getRankListData("年",false,true,"#loadMoreRankList","#rankMore-list",moreLimit);'>年</a> 
    </li>
    <li>
       <a id="month_nav" style="padding:0;line-height:22px" class="am-round" onclick='getRankListData("月",false,true,"#loadMoreRankList","#rankMore-list",moreLimit);'>月</a> 
    </li>
    <li>
    <a  id="week_nav" style="padding:0;line-height:22px" class="am-round" onclick='getRankListData("周",false,true,"#loadMoreRankList","#rankMore-list",moreLimit);'>周</a> 
    </li>
    <li class="am-active" >
    <a id="seven_nav" style="padding:0;line-height:22px" class="am-round" onclick='getRankListData("日",false,true,"#loadMoreRankList","#rankMore-list",moreLimit);'>日</a> 
    </li>
  </ul>
  <div class="am-tabs-bd" style="border:none;padding-top:14px">
   <ul class="am-list am-list-static" id="rankMore-list">
       </ul>
  </div>
       <button class="am-btn am-btn-link am-btn-block" data-am-loading="{spinner: 'circle-o-notch', loadingText: '加载中...'}" onclick="getRankListData(rankType,false,false,'#loadMoreRankList','#rankMore-list',moreLimit);" id="loadMoreRankList" >查看更多</button>
  	</div>
  	</div>
  	
  </div>
  </div>
 
  
  	<div data-am-widget="navbar" class="am-navbar am-cf am-navbar-default "
id="">
  <ul class="am-navbar-nav am-cf am-avg-sm-3"  style="background-color: #009ada">
      <li>
      <?php if($isMember):?>
      <a href="/runners/mileages" class="">
        <span class="ion-android-clipboard am-icon-sm"></span>
        <span class="am-navbar-label">打卡</span>
      </a>
      <?php else:?>
       <a href="/clubs/<?=$club->club_eng?>/register" class="">
        <span class="ion-person-add am-icon-sm"></span>
        <span class="am-navbar-label">加入</span>
      </a>
      <?php endif;?>
    </li>
      <li>
      <a href="/runners/me">
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
	app.showAndHide();
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
    
    var offset=0;
    function getRecentMileages(append){
	$("#loadMore").button("loading");
	if(!append){
	        $('#recentMileages').modal();
	}
            $.post("/clubs/<?=$club->club_eng?>/recentmileages?t="+new Date().getTime(),{clubid:<?=$club->clubid?>,offset:offset,limit:8},function(data){
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
            	  $("#minfo-list").append(template({mileages:data}));
            	 // $("#recent-list").append(template({mileages:data}));
                });
		}

    $('#rankMore-nav').tabs({noSwipe: 1});
    $('#rankMore-tabs').tabs({noSwipe: 1});
  
   // getRecentMileages();
    
</script>