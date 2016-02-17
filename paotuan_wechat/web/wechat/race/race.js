var race = angular.module('race', [ 'ngRoute' ]);

race.config(function($routeProvider, $locationProvider) {
	$routeProvider
			.when(
					'/race/channel',
					{
						templateUrl : 'race/channel.html',
						controller : 'RaceChannelController',
						resolve : {
							channel : function($route, RaceService,$rootScope) {
								return RaceService.getChannel($route.current.params["channel_id"]);
							},
							register:function($route,RaceService){
								return RaceService.getRegister($route.current.params["channel_id"]);
							}
						}
					})
					.when(
					'/race/disclaimer',
					{
						templateUrl : 'race/disclaimer.html',
						controller : 'RaceDisclaimerController',
						resolve:{
							  act:function($route, RaceService,$rootScope){
								if(RaceService.race!=null){
									return RaceService.race;
								}else{
									return RaceService.getRaceDisclaimer($route.current.params["channel_id"]);
								}
							}
						}
					})
					.when(
					'/race/registerinfo',
					{
						templateUrl : 'race/register.html',
						controller : 'RaceRegisterController',
						resolve:{
							register:function($route,RaceService){
								return RaceService.getRegister($route.current.params["channel_id"]);
							}
						}
					})
					.when(
					'/race/list',
					{
						templateUrl : 'race/list.html',
						controller : 'RaceListController'
					})
					.when(
					'/race/runner/orders',
					{
						templateUrl : 'race/orderList.html',
						controller : 'RaceRunnerOrderController'
					})
					.when(
					'/race/orderinfo',
					{
						templateUrl : 'race/order_info.html',
						controller : 'RaceOrderInfoController',
						resolve : {
							order : function($route, RaceService,$rootScope) {
								return RaceService.orderInfo($route.current.params["order_id"]);
							}
						}
					})
					.when(
					'/race/runner/images',
					{
						templateUrl : 'race/racer_images.html',
						controller : 'RacerImagesController'
					})
					.otherwise({
						templateUrl: "/wechat/notfound.html"
			});
});

race.factory('RaceService', function($http, $q, $rootScope,UtilService,LoginService,ClubService) {
	var service = {
		 race:null,
		 read_disclaimer:false,
		getChannel : function(channel_id) {
			var deferred = $q.defer();
			$.ajax({
				url : "/channel/channelinfo",
				data : {
					channel_id : channel_id,
					openid : $rootScope.openid
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		checkCode:function(channel_id,invite_code){
			var deferred = $q.defer();
			$.ajax({
				url : "/channel/channelcode",
				data : {
					channel_id : channel_id,
					invite_code:invite_code,
					openid : $rootScope.openid
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getRaceDisclaimer:function(channel_id){
			var deferred = $q.defer();
			$.ajax({
				url : "/channel/racedisclaimer",
				data : {
					channel_id : channel_id,
					openid : $rootScope.openid
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getItems : function(channel_id) {
			var deferred = $q.defer();
			$.ajax({
				url : "/channel/items",
				data : {
					channel_id : channel_id,
					openid : $rootScope.openid
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getList : function(offset,limit,search_key) {
			var deferred = $q.defer();
			$.ajax({
				url : "/race/list",
				data : {
					openid : $rootScope.openid,
					offset:offset,
					limit:limit,
					search_key:search_key
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getRacerImages : function(runner_id) {
			var deferred = $q.defer();
			$.ajax({
				url : "/race/racerimages",
				data : {
					runner_id:runner_id
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		requestImages:function(data){
			var deferred = $q.defer();
			$.ajax({
				url : "/race/requestimages",
				data : data,
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getRegister : function(channel_id) {
			var deferred = $q.defer();
			$.ajax({
				url : "/channel/getregister",
				data : {
					openid : $rootScope.openid,
					channel_id : channel_id
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		orders : function(channel_id) {
			var deferred = $q.defer();
			$.ajax({
				url : "/channel/runnerorders",
				data : {
					openid : $rootScope.openid,
					channel_id : channel_id
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		orderSubmit : function(data) {
			var deferred = $q.defer();
			$.ajax({
				url : "/channel/ordersubmit",
				data :data,
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		orderCancel : function(order_id) {
			var deferred = $q.defer();
			$.ajax({
				url : "/channel/cancel",
				data :{
					order_id:order_id,
					openid : $rootScope.openid
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		orderInfo:function(order_id){
			var deferred = $q.defer();
			$.ajax({
				url : "/channel/orderinfo",
				data :{
					order_id:order_id,
					openid : $rootScope.openid
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		setItemNumText:function(item){
			   if(item.is_end){
				   item.num_text = "已结束";
			   }else{
				   item.canbuy_num=item.item_num_limit-item.item_buy_sum;   
				   if(item.canbuy_num<=0){
						item.num_text="已售完";
						item.sell_out=true;
					}else if(item.canbuy_num<10){
						item.num_text=item.canbuy_num;
					}else if(item.canbuy_num<20){
						item.num_text="仅有少量";
					}else{
						item.num_text=">20";
					}
			   }
		},
		updateRegister:function(postData){
			var deferred = $q.defer();
			$.ajax({
				url : "/channel/updateregister",
				data :postData,
				success : function(data) {
					deferred.resolve(data);
				},
				complete:function(){
					hideLoading();
				}
			});
			return deferred.promise;
		}
	};
	return service;
});

race.controller('RaceChannelController', function($location,$scope, $routeParams,$rootScope,UtilService,WxService,RaceService,channel,register) {
	$scope.channel = channel.channel;
	$scope.registerData= register;
	$scope.register =$scope.registerData.register; 
	$scope.register.user_cell1 = $scope.register.user_cell;
	$scope.register.passport_name1 =$scope.register.passport_name; 
	$scope.hasOrder = $scope.registerData.hasOrder;
	$scope.pay_order = $scope.registerData.reg_order;
	$scope.act = channel.act;
	RaceService.race = $scope.act;
	$scope.channel_status = channel.status;
	$scope.club=channel.club;
	$rootScope.club = $scope.club;
	$scope.regs =null;
	$scope.hotels = null;
	$scope.traffic = null;
	$scope.other = null;
	$scope.index=true;
	$scope.is_info=false;
	$scope.info_item=null;
	$scope.needBack=false;
	$scope.loading=true;
    $scope.items=null;
    $scope.items_length=0;
	$scope.amount=0;
	$scope.showReg=false;
	$scope.hasRegItem=false;
	$scope.register.certs_text="";
	$scope.contact_info=false;
	
	WxService.shareObj.title="报名|"+$scope.act.act_name;
	WxService.shareObj.desc=$scope.act.act_intro;
	WxService.shareObj.link=$location.absUrl();
	if($scope.act.act_image){
		WxService.shareObj.imgUrl="http://xiaoi.b0.upaiyun.com/"+$scope.act.act_image+$rootScope.WX_COVER;
	}else{
		WxService.shareObj.imgUrl=$location.host+"/image/paotuanzhuce/top.png";
	}
	
	if($scope.registerData.is_reg||$scope.registerData.is_submit){
		RaceService.read_disclaimer=true;
	}
	if(!RaceService.read_disclaimer){
		$location.path("/race/disclaimer");
		return;
	}
	
	if($scope.channel.limit_range&&!$scope.channel_status.isMember){
		UtilService.showMessage("您需要加入跑团才能报名!",null,function(){
			$location.url("/clubs/register?club_eng="+$rootScope.club.club_eng);
			$scope.$apply();
			return false;
		});
		return;
	}
	
	if($scope.channel_status.need_code&&!$scope.registerData.channelOrder&&!$scope.channel_status.end&&$scope.channel_status.start){
		 UtilService.setPromptInputType("text");
		UtilService.showPrompt("邀请码!",null,function(){
			var value =UtilService.getProModalValue();
			if(value&&value!=""){
				RaceService.checkCode($scope.channel.channelid,value).then(function(data){
					if(data.status==1){
						$scope.channel_status.need_code=false;
						UtilService.closeProModal();
					}else{
						UtilService.showPromptError("邀请码错误");
					}
				});
			}else{
				UtilService.showPromptError("请填写邀请码");
			}
			return false;
		},false);
	}
	
	if($scope.register.certs){
		 $.each($scope.register.certs,function(i,n){
			 $scope.register.certs_text+=","+n.paper_url;
		 });
		 if($scope.register.certs_text.length>0){
			 $scope.register.certs_text = $scope.register.certs_text.substring(1);
		 }
	}
	
	
	$scope.addItem=function(item){
		if(!$scope.items){
			$scope.items=[];
		}
		if(item.info_type!=0){
			var form = $("#contact_info")
			if(form.length>0){
				item.passport_name = $.trim($("#contact_info_name").val());
				item.cell = $.trim($("#contact_info_cell").val());
			}
		}
		var has=false;
		$.each($scope.items,function(i,n){
			if(item.item_type==0&&n.itemid!=item.itemid&&n.item_type==item.item_type){
				n.selected=false;
				$scope.items.splice(i,1);
				return false;
			}
			if(n.itemid==item.itemid){
				has=true;
			}
		});
		if(!has){
			if(item.info_type==0){
				$scope.items.unshift(item);
			}else{
				$scope.items.push(item);
			}
		}
		 $scope.items_length = $scope.items.length;
		$scope.calAmount();
		return true;
	}
	
	$scope.calAmount=function(){
		var amount=0;
		$.each($scope.items,function(i,n){
			if(n&&n.selected){
				amount+=parseFloat(n.item_price*n.num);
			}
		});
		$scope.amount = amount;
	}
	
	RaceService.getItems($scope.channel.channelid).then(function(data){
		if(data){
			$scope.regs = data.regs.length>0?data.regs:null;
			if($scope.regs){
				$.each($scope.regs,function(i,n){
					RaceService.setItemNumText(n);
					n.passport_name=$scope.register.passport_name1;
					n.user_cell = $scope.register.user_cell1;
					n.info_type=0;
					n.num=1;
				});
			}
			$scope.hotels = data.hotels.length>0?data.hotels:null;
			if($scope.hotels){
				$.each($scope.hotels,function(i,n){
					RaceService.setItemNumText(n);
					n.passport_name=$scope.register.passport_name1;
					n.user_cell = $scope.register.user_cell1;
					n.info_type=1;
					n.num=1;
				});
			}
			$scope.traffic = data.traffic.length>0?data.traffic:null;
			if($scope.traffic){
				$.each($scope.traffic,function(i,n){
					RaceService.setItemNumText(n);
					n.passport_name=$scope.register.passport_name1;
					n.user_cell = $scope.register.user_cell1;
					n.info_type=2;
					n.num=1;
				});
			}
			$scope.other = data.other.length>0?data.other:null;
			if($scope.other){
				$.each($scope.other,function(i,n){
					RaceService.setItemNumText(n);
					n.passport_name=$scope.register.passport_name1;
					n.user_cell = $scope.register.user_cell1;
					n.info_type=3;
					n.num=1;
				});
			}
		}
		$scope.loading=false;
	});
	
	$scope.sliders={};
	$scope.updateSlide=function(slide,data){
		var slider =  $scope.sliders[slide];
		if(data.set_per_order_num!=null&&data.set_per_order_num>0){
			if(data.canbuy_num<data.set_per_order_num){
				slider.slider("setAttribute","max",data.canbuy_num);
			}else{
				slider.slider("setAttribute","max",data.set_per_order_num);
			}
			
		}else{
			slider.slider("setAttribute","max",99);
		}
		 slider.slider("setValue",data.num,true,true);
	}
	
	$scope.info=function(type,obj){
		$scope.index=false;
		if($scope.channel_status.end||!$scope.channel_status.start){
			return;
		}
		switch(type){
			case 0:
				if($scope.registerData.need_bind){
					 location.href = "/bind?redirect="+encodeURIComponent("http://"+document.domain+"/wechat/#/race/channel?channel_id="+$scope.channel.channelid);
				      return;
				}
				$scope.index=true;
				if($scope.registerData.is_reg||$scope.registerData.is_submit){
					return;
				}
				if(!obj.sell_out&&!obj.is_end){
					if(!obj.selected){
						obj.selected=true;
						$scope.addItem(obj);
						$scope.hasRegItem=true;
						$scope.register.courseid=obj.courseid;
					}
				}
				break;
			case 1:
				$(document).scrollTop(0);
				$scope.needBack=true;
				$scope.hotel_info=true;
				$scope.hotel = obj;
				$scope.info_item=obj;
				$scope.is_info=true;
				$scope.contact_info=true;
				$scope.updateSlide("hotel_num",obj);
				break;
			case 2:
				$(document).scrollTop(0);
				$scope.needBack=true;
				$scope.traffic_info=true;
				$scope.info_item=obj;
				$scope.is_info=true;
				$scope.contact_info=true;
				$scope.updateSlide("traffic_num",obj);
				break;
			case 3:
				$(document).scrollTop(0);
				$scope.needBack=true;
				$scope.other_info=true;
				$scope.is_info=true;
				$scope.info_item=obj;
				$scope.contact_info=true;
				$scope.updateSlide("other_num",obj);
				break;
				
		}
	}
	
	$scope.itemSelected=function(item,info){
		if($scope.registerData.need_bind){
			 location.href = "/bind?redirect="+encodeURIComponent("http://"+document.domain+"/wechat/#/race/channel?channel_id="+$scope.channel.channelid);
		      return;
		}
		if(!info){
			event.stopPropagation();
		}
		var canBack=false;
		item.selected=true;
		canBack = $scope.addItem(item);
     if(canBack){
  	   $scope.back(); 
     }
	}
	
	$scope.itemCancel= function(item){
		item.selected=false;
		item.num=0;
		$.each($scope.items,function(i,n){
			if(n.itemid==item.itemid){
				$scope.items.splice(i,1);
				return false;
			}
		});
		$scope.calAmount();
		if($scope.items.length==0){
			$scope.items=null;
		}else{
			 $scope.items_length = $scope.items.length;
		}
		$scope.info_item=null;
		$scope.back();
	}
	
	$scope.back = function(){
		$scope.index=true;
		$scope.needBack=false;
		$scope.hotel_info=false;
		$scope.traffic_info=false;
		$scope.other_info=false;
		$scope.showReg=false;
		$scope.is_info=false;
		$scope.contact_info=false;
	}
	
	$scope.cancelReg = function(){
		var btn =event.target.id;
		$("#"+btn).button('loading');
		RaceService.orderCancel($scope.pay_order).then(function(data){
			$("#"+btn).button('reset');
			if(data.status==1){
				if(data.need_reg){
					$scope.is_reg=false;
					$scope.registerData.is_submit=false;
					$scope.registerData.pay_count--;
					$scope.hasOrder--;
				}
			}else{
				UtilService.showMessage(data.msg,null,null);
			}
		});
	}
	
	$scope.orders=function(){
		$location.path("/race/runner/orders");   
	}

	$scope.reg=function(){
		var items = "";
		var nums="";
		var submitData = [];
		$.each($scope.items,function(i,n){
			if(n.selected){
				var obj = new Object();
				obj.itemid = n.itemid;
				obj.num = n.num;
				obj.passport_name = n.passport_name;
				obj.user_cell = n.user_cell;
				submitData.push(obj);
				items = items+","+n.itemid;
				nums+=","+n.num;
			}
		});
		if(items.length>0&&nums.length>0){
			items = items.substring(1);
			nums = nums.substring(1);
		}
		if($scope.registerData.is_reg||!$scope.hasRegItem){
			var btn =event.target.id;
			showLoading();
			RaceService.orderSubmit("formData="+JSON.stringify(submitData)+"&openid="+$rootScope.openid+"&channel_id="+$scope.channel.channelid).then(function(data){
				if(data.status==1){
//					$.each($scope.items,function(i,n){
//						if(n.selected){
//							n.item_buy_sum++;
//							RaceService.setItemNumText(n);
//							n.selected=false;
//						}
//					});
					$location.url("/race/orderinfo?order_id="+data.order);
				}else{
					UtilService.showMessage(data.msg,null,null);
				}
				hideLoading();
			});
		return;
	}
		
		
		
		if($("#regForm").valid()){
			var btn =event.target.id;
			showLoading();
			RaceService.orderSubmit($("#regForm").serialize()+"&formData="+JSON.stringify(submitData)+"&openid="+$rootScope.openid+"&channel_id="+$scope.channel.channelid).then(function(data){
				if(data.status==1){
					$location.url("/race/orderinfo?order_id="+data.order);
//					$scope.registerData.is_submit=true;
//					if(data.order){
//						$scope.hasOrder++;
//						$scope.registerData.pay_count++;
//					}else{
//						$scope.registerData.is_reg=true;
//					}
//					$.each($scope.items,function(i,n){
//						if(n.selected){
//							n.item_buy_sum++;
//							RaceService.setItemNumText(n);
//						}
//					});
				}
				hideLoading();
			});
		}else{
			 var firstError = $(".has-error")[0];
			 var top=$(firstError).children("input[type!='hidden']").offset().top;
			 UtilService.scrollTo(top); 
			$scope.index=false;
			$scope.needBack=false;
			$scope.showReg=true;
		}
	}
	
});

race.controller('RaceListController', function($scope, $routeParams,$rootScope,UtilService,WxService,RaceService) {
	  $scope.offset=0;
	  $limit = Math.ceil($(window).height()/77*5);
	  $scope.limit=$limit;
	  $scope.latest = 0;
	  $scope.search.key=null;
	$scope.loadMore=function(isNew){
		 $scope.no_data=false;
		if(isNew){
			$scope.list=[];
			 $scope.offset=0;
			 $scope.latest = 0;
		}
		if ($scope.latest == 0 || $scope.latest != $scope.offset) {
			$scope.loading=true;
			$scope.latest = $scope.offset;
			RaceService.getList($scope.offset, $scope.limit,$scope.search.key).then(function(data){
				$scope.loading=false;
				$scope.offset+=data.length;
				if(!$scope.list){
					$scope.list=[];
				}
				$.each(data,function(i,n){
					$scope.list.push(n);
				  });
				if($scope.list.length==0){
					$scope.no_data=true;
				}
				 $scope.loading=false;
			});
		}
		
	}
});

race.controller('RacerImagesController', function($scope, $routeParams,$rootScope,UtilService,WxService,RaceService) {
	 $scope.no_data=true;  
	if($routeParams.runner_id){
		  $scope.loading=true;
		  RaceService.getRacerImages($.trim($routeParams.runner_id)).then(function(data){
			  $scope.loading=false;
			  $scope.images = data;
			  if($scope.images&&$scope.images.length>0){
				  $scope.no_data=false;
			  }
		  });
	  }
	$scope.sendEmail=function(){
		if($("#email_form").valid()){
			if(!$scope.no_data){
				var btn = event.target.id;
				$rootScope.btnLoading(btn);
				RaceService.requestImages( {race_id:$scope.images[0].race_id,runner_id:$scope.images[0].runner_id,email:$.trim($("#email").val())}).then(function(data){
					if(data.status==1){
						$("#email").val("");
						UtilService.showMessage("您的下载请求已发送,处理完成后会发送到您所填写的邮箱",null,null);
					}else{
						UtilService.showMessage(data.msg,null,null);
					}
					$rootScope.btnReset(btn);
				});
			}
		}
	}
});

race.controller('RaceRunnerOrderController', function($location,$scope, $routeParams,$rootScope,UtilService,WxService,RaceService) {
	     $scope.no_data=true;  
		  $scope.loading=true;
		  $scope.hash=$location.url();
		  $scope.need_pays = null;
		  $scope.done= null;
		 RaceService.orders($routeParams.channel_id).then(function(data){
			 $scope.loading=false;
			 $scope.orders = data;
			 $.each($scope.orders,function(i,n){
				 if(n.need_pay){
					 if($scope.need_pays==null){
						 $scope.need_pays=[]; 
					 }
					  $scope.need_pays.push(n);
				 }else{
					 if($scope.done==null){
						 $scope.done=[]; 
					 }
					 $scope.done.push(n);
				 }
			 });
			 if($scope.orders&&$scope.orders.length>0){
				  $scope.no_data=false;
				  $scope.order_title=$scope.orders[0].order_title;
			  }
			 
		 });
		 
		 $scope.pay=function(order){
			     $("#gd").val(order.order_title);
			     $("#uid").val(order.uid);
			     $("#orderid").val(order.orderid);
			     $("#tf").val(order.actual_payment*100);
			     $("#ex_time").val(order.expire_time);
				$("#payForm").submit();
			}
});

race.controller('RaceOrderInfoController', function($location,$scope, $routeParams,$rootScope,UtilService,WxService,RaceService,order) {
	    $scope.order =order; 
	    $scope.hash=$location.url();
		$scope.cancelOrder = function(){
			event.preventDefault();
			var btn =event.target.id;
			$("#"+btn).button('loading');
			RaceService.orderCancel($scope.order.orderid).then(function(data){
				$("#"+btn).button('reset');
				if(data.status==1){
					$scope.order.cancel=true;
					$scope.order.need_pay=false;
				}else{
					UtilService.showMessage(data.msg,null,null);
				}
			});
		}
		$scope.pay=function(){
			$("#payForm").submit();
		}
});

race.controller('RaceOrderInfoController', function($location,$scope, $routeParams,$rootScope,UtilService,WxService,RaceService,order) {
    $scope.order =order; 
    $scope.hash=$location.url();
	$scope.cancelOrder = function(){
		event.preventDefault();
		var btn =event.target.id;
		$("#"+btn).button('loading');
		RaceService.orderCancel($scope.order.orderid).then(function(data){
			$("#"+btn).button('reset');
			if(data.status==1){
				$scope.order.cancel=true;
				$scope.order.need_pay=false;
			}else{
				UtilService.showMessage(data.msg,null,null);
			}
		});
	}
	$scope.pay=function(){
		$("#payForm").submit();
	}
});

race.controller('RaceDisclaimerController', function($location,$scope,$routeParams,$rootScope,UtilService,WxService,RaceService,act,$sce) {
  $scope.act=act;
  $scope.agree=function(){
	  RaceService.read_disclaimer=true;
	  $location.path("/race/channel");
  }
});

race.controller('RaceRegisterController', function($location,$scope,$routeParams,$rootScope,UtilService,WxService,RaceService,register,$sce) {
	$scope.registerData= register;
	$scope.register =$scope.registerData.register; 
	
	$scope.showTextAreaModify=function(name,type,title,v){
		UtilService.clearProModalValue();
		UtilService.setProTextAreaModalValue(v);
		 UtilService.showTextAreaPrompt(title,null,function(){
			var value = UtilService.getProTextAreaModalValue();
			if(value!=""&&value!=v){
				  UtilService.closeProModal();
				 $scope.submitData(name,value,false,false,true);
			}
			return false;
		 });
	}
	
 $scope.showModify=function(name,type,title,v){
	 if($scope.registerData.is_reg){
		return false; 
	 }
	 $scope.inputType=type;
	 $scope.inputTitle=title;
	 $rootScope.modalInputValue=v;
	 UtilService.setPromptInputType(type);
	 UtilService.showPrompt(title,null,function(){
		 var value = UtilService.getProModalValue();
		 if(value==""){
			// UtilService.showPromptError("不能为空");
			 UtilService.closeProModal();
			 return;
		 }
		 if(value==v){
			 UtilService.closeProModal();
		 }else{
			 var check=false;
			 switch(type){
			    case 'number':
			    	if($.isNumeric(value)){
			    		value =parseInt(value); 
			    		if(parseInt(value)<=0){
			    			UtilService.showPromptError("请输入正确的数字");
			    			return false;
			    		}else{
			    			check=true;
			    		}
			    	}else{
			    		UtilService.showPromptError("请输入正确的数字");
			    		return false;
			    	}
			    	break;
			    case 'tel':
			    	if(!$rootScope.REG_CELL.test(value)){
			    		UtilService.showPromptError("请输入正确的手机号");
			    		return false;
			    	}else{
			    		check=true;
			    	}
			    	break;
			    case 'email':
			    	if(!$rootScope.REG_EMAIL.test(value)){
			    		UtilService.showPromptError("请输入正确的邮箱");
			    		return false;
			    	}else{
			    		check=true;
			    	}
			    	break;
			    default:check=true;
			 }
			 if(check){
				UtilService.closeProModal();
				 $scope.submitData(name,value,false,false,false);
			 }
		 }
		 return false;
	 });
	 UtilService.setProModalValue(v);
 }
 
 $scope.submitData=function(name,data,isMult,img,isArea){
	 if($("#"+name+"_value").length>0){
		 if(isArea){
			 $("#sub_data_textarea").attr("name","Register["+name+"]");
			 $("#sub_data_textarea").html($("#"+name+"_value").val());  
		 }else{
			 $("#sub_data").attr("name","Register["+name+"]");
			 $("#sub_data").val($("#"+name+"_value").val());  
		 }
		
	 }else{
		 if(isArea){
			 $("#sub_data_textarea").attr("name","Register["+name+"]");
		    $("#sub_data_textarea").html(data);
		 }else{
			 $("#sub_data").attr("name","Register["+name+"]");
			 $("#sub_data").val(data); 
		 }
	 }
     showLoading();
	 RaceService.updateRegister($("#regForm").serialize()+"&openid="+$rootScope.openid).then(function(rData){
			if(rData.status==1){
				if(img){
					if(isMult){
						$scope.register.certs = [];
						$.each(data,function(x,y){
							$scope.register.certs.push({paper_url: $rootScope.STATIC_IMG_PRE+y.image});
						});
					}else{
						$scope.register[name] = $rootScope.STATIC_IMG_PRE+data;
					}
				}else{
					$scope.register[name] =data; 
				}
			}else if(rData.status==2){
				$scope.showModify(name, $scope.inputType, $scope.inputTitle,data);
				UtilService.showPromptError(rData.msg);
			}
		});
 }
 
	$scope.uploadComplete=function(images,file){
		if($("#"+file).attr("multiple")){
			if(images.length>0){
				$scope.submitData(file,images,true,true);
			}else{
				hideLoading();
			}
		}else{
			if(images.length>0){
				$scope.submitData(file,images[0].image,false,true);
			}else{
				hideLoading();
			}
		}
	}
 
	});
