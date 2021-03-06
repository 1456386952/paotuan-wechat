var actApp = angular.module('actApp', [ 'ngRoute' ]);

actApp.config(function($routeProvider, $locationProvider) {
	$routeProvider
			// route for the home page
			.when(
					'/activity/list',
					{
						templateUrl : 'activity/activityList.html?'+new Date().getTime(),
						controller : 'actListController',
						resolve : {
							club : function($route, ClubService,$rootScope) {
								if($rootScope.club){
									return $rootScope.club;
								}else{
									if($route.current.params["club_eng"]){
										return ClubService
										.getClubBase($route.current.params["club_eng"]);
									}
								}
							}
						}
					})
			.when(
					'/activity/new',
					{
						templateUrl : 'activity/new.html?'+new Date().getTime(),
						controller : 'actNewController',
						resolve : {
							club:function($route,ClubService,$rootScope){
								if($rootScope.club){
									return $rootScope.club;
								}else{
								return ClubService
										.getClubBase($route.current.params["club_eng"]);
								}
							},
							locationList:function($route,ActService){
								return ActService.getLocationList($route.current.params["club_eng"]);
							},
							script:function($rootScope,UtilService){
								UtilService.createQQMapScript();
							}
						}
					})
			.when(
					'/activity/publishSuccess',
					{
						templateUrl : 'activity/publishSuccess.html?'+new Date().getTime(),
						controller : 'actPublishSuccessController',
						resolve : {
							actInfo : function($route, ActService) {
								var act = ActService.actInfo($route.current.params["act_id"]);
						act.then(function(data){
							if(!data.act.owner){
								location.replace("http://"+document.domain+"/wechat/#/activity/info?act_id="+data.act.act_id);
							    return;
							}
						});
						return act;
							}
						}
					})
			.when(
					'/activity/info',
					{
						templateUrl : 'activity/info.html?'+new Date().getTime(),
						controller : 'actInfoController',
						resolve : {
							actInfo : function($route, ActService) {
								return ActService
										.actInfo($route.current.params["act_id"]);
							},
							script:function($rootScope,UtilService){
								UtilService.createFileUploadScript();
							}
						}
					})
			.when(
					'/activity/regSuccess',
					{
						templateUrl : 'activity/regSuccess.html?'+new Date().getTime(),
						controller : 'actCheckSuccessController',
						resolve : {
							actInfo : function($route, ActService) {
								var act = ActService
										.actInfo($route.current.params["act_id"]);
								act.then(function(data){
									if(!data.act.isReg){
										location.replace("http://"+document.domain+"/wechat/#/activity/info?act_id="+data.act.act_id);
									    return;
									}
								});
								return act;
							}
						}
					})
			.when('/activity/checkin', {
				templateUrl : 'activity/checkin.html',
				controller : 'actCheckInController',
			})
			.when(
					'/activity/checkSuccess',
					{
						templateUrl : 'activity/checkSuccess.html?'+new Date().getTime(),
						controller : 'actCheckSuccessController',
						resolve : {
							actInfo : function($route, ActService) {
								var act = ActService
								.actInfo($route.current.params["act_id"]);
						     act.then(function(data){
						    	 if(!data.act.isCheck){
								location.replace("http://"+document.domain+"/wechat/#/activity/info?act_id="+data.act.act_id);
							    return;
						    	 }
							});
						return act;
							}
						}
					})
					.when('/activity/map', {
						templateUrl : 'activity/map.html',
						controller : 'actMapController',
						resolve : {
							club : function($route, ClubService,$rootScope) {
								if($rootScope.club){
									return $rootScope.club;
								}else{
								return ClubService
										.getClubBase($route.current.params["club_eng"]);
								}
							}
						}
					})
					.when('/activity/users', {
						templateUrl : 'activity/activityUsers.html?'+new Date().getTime(),
						controller : 'actUsersController',
						resolve : {
							actInfo : function($route, ActService) {
								return ActService
										.actInfo($route.current.params["act_id"]);
							},
							users:function($route, ActService){
								return ActService.getActUsers($route.current.params["act_id"]);
							}
						}
					})
					.otherwise({
						templateUrl: "/wechat/notfound.html"
			});
});

actApp.factory('ActService', function($http, $q, $rootScope,UtilService,LoginService,ClubService) {
	var service = {
		getLocationList : function(club_eng) {
			var deferred = $q.defer();
			$.post("/activity/clublocation", {
				club_eng : club_eng,openid : $rootScope.openid
			}, function(data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		canNew:function(club_eng,async,callBack){
			var deferred = $q.defer();
			 $.ajax({
				 beforeSend:function(){
					 showLoading();
				 },
				 url:"/activity/cannew",
				 data:{openid:$rootScope.openid,club_eng:club_eng},
				 async:async,
				 success:function(data){
					 switch (data.status){
					  case 1:{
					    	$rootScope.canNew=true;
							location.href="/wechat/#/activity/new?club_eng="+club_eng;

							//获取用户信息，跳转页面至登录页面
							 // var userInfo = store.get('userInfo');
							 // location.href = "http://wechat.paobuqu.com/mobile/#/new/activity?club_eng=" + $rootScope.club_eng
							 // + '&uid=' + userInfo.uid + '&type=newact';

					        break;
					    }
					    case 0:{
					    	UtilService.showMessage(data.msg,null,null);
					        break;
					    }
					    case -1:{
					    	console.log($rootScope.club_eng)
					    	UtilService.showMessage("您需要关注跑步去才能继续!<div class='container-fluid text-center'><img src='/image/qrcode_258.jpg'></div>");
					        break;
					    }
					    case -2:{
					    	UtilService
							.showMessage(
									"加入跑团才能发起活动，是否现在加入?",
									null,
									function() {
										location.href = "/clubs/"
												+ $rootScope.club.club_eng
												+ "/register";
									});
					        break;
					    }
					    case -3:{
					    	UtilService.showMessage(
									"只有团长和管理员才能发起活动!",
									null,null);
					        break;
					    }
					    case -4:{
					    	document.location = "/bind?redirect="+encodeURIComponent("http://"+document.domain+"/wechat/#/activity/new?club_eng="+$rootScope.club.club_eng);
					        break;
					    }
					 }
					 if($.isFunction(callBack)){
						 callBack(data);
					 }
					deferred.resolve(data);
				 }
			 });
			return deferred.promise;
		},
		getCheckUsers : function(act_id) {
			var deferred = $q.defer();
			$.post("/activity/checkusers", {
				activity_id : act_id
			}, function(data) {
				deferred.resolve(data);
			});c
			return deferred.promise;
		},
		getRegUsers : function(act_id) {
			var deferred = $q.defer();
			$.post("/activity/regusers", {
				activity_id : act_id
			}, function(data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		getActUsers : function(act_id) {
			var deferred = $q.defer();
			$.post("/activity/actusers", {
				activity_id : act_id
			}, function(data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		actInfo : function(act_id) {
			var deferred = $q.defer();
			$.post("/activity/info", {
				activity_id : act_id,
				openid : $rootScope.openid
			}, function(data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		reg : function(act_id,postData) {
			postData+="&openid="+$rootScope.openid+"&activity_id="+act_id;
			var deferred = $q.defer();
			$.post("/activity/reg",postData, function(data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		cancelReg : function(act_id) {
			 var deferred = $q.defer(); 
    		 $.ajax({
  				 url:"/activity/cancelreg",
  				 data:{activity_id:act_id,
  					   openid:$rootScope.openid
  					   },
  				 success:function(data){
	    			 deferred.resolve(data);
  				 }
  			 });
    		 return deferred.promise;
		},
		checkInviteCode : function(act_id,code) {
			var deferred = $q.defer();
			$.post("/activity/checkinvitecode",{
				activity_id : act_id,
				code:code,
				openid : $rootScope.openid
			}, function(data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		getCode : function(params) {
			var deferred = $q.defer();
			$.post("/activity/gencheckcode/" + params.act_id, params, function(
					data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		getQrCode : function(act_id) {
			var deferred = $q.defer();
			$.post("/activity/genqrcode", {
				activity_id : act_id,
				openid : $rootScope.openid
			}, function(data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		gpsCheckIn : function(act_id, lat, lng) {
			var deferred = $q.defer();
			$.post("/activity/gpscheckin", {
				activity_id : act_id,
				lat : lat,
				lng : lng,
				openid : $rootScope.openid
			}, function(data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		checkIn : function(code,async) {
			var deferred = $q.defer();
			 $.ajax({
 				 url:"/activity/codecheckin?code=" + code,
 				 data:{openid:$rootScope.openid},
 				 async:async,
 				 success:function(data){
 					deferred.resolve(data);
 				 }
 			 });
			return deferred.promise;
		},
		getClubList : function(club_eng,offset,limit) {
			var deferred = $q.defer();
			$.post("/activity/listbyclub", {
				club_eng:club_eng,
				offset:offset,
			     limit:limit,
				openid : $rootScope.openid
			}, function(data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		shakeCheckIn : function(ticket,act_id) {
			var deferred = $q.defer();
			 $.ajax({
 				 url:"/activity/shakcheckin",
 				 data:{ticket:ticket,openid:$rootScope.openid,activity_id:act_id},
 				 success:function(data){
 					deferred.resolve(data);
 				 }
 			 });
			return deferred.promise;
		},
		getShakeActList:function(ticket,async){
			var deferred = $q.defer();
			 $.ajax({
				 url:"/activity/shakeactlist",
				 data:{ticket:ticket,openid:$rootScope.openid},
				 async:async,
				 success:function(data){
					deferred.resolve(data);
				 }
			 });
			return deferred.promise;
		},
		saveLocation:function(data){
			data.openid = $rootScope.openid;
			var deferred = $q.defer();
			 $.ajax({
				 url:"/activity/savelocation",
				 data:data,
				 success:function(data){
					deferred.resolve(data);
				 }
			 });
			return deferred.promise;
		},
		deleteLocation:function(id){
			var deferred = $q.defer();
			 $.ajax({
				 url:"/activity/deletelocation",
				 data:{
					 openid : $rootScope.openid,
					 location_id:id
				 },
				 success:function(data){
					deferred.resolve(data);
				 }
			 });
			return deferred.promise;
		},
		cancel:function(act_id){
			var deferred = $q.defer();
			 $.ajax({
				 url:"/activity/cancel",
				 data:{activity_id:act_id,openid:$rootScope.openid},
				 success:function(data){
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

actApp.controller('actNewController', function($scope, $routeParams, ActService,ClubService,
		MapService, $rootScope,UtilService,club,locationList,WxService) {
	if (club) {
		$rootScope.club =club; 
//		ActService.getLocationList($rootScope.club.clubid).then(function(data){
//			$scope.data = data;
//			$scope.locations =new Array();
//			$.each(data.data,function(i,n){
//				$scope.locations.push(n);
//			});
//			$scope.location_length=$scope.locations.length;
//		});
		$scope.data = locationList;
		$scope.locations =new Array();
		$.each(locationList.data,function(i,n){
			$scope.locations.push(n);
		});
		    $scope.location_length=$scope.locations.length;
		    $scope.location_length=$scope.locations.length;
			$scope.club_id = $rootScope.club.clubid;
			$scope.ip =$scope.data.ip; 
	}
	
	$scope.deleteLocation=function(id){
			   event.stopPropagation();
			UtilService.showConfirm("确定要删除该地点吗?", null, function() {
				showLoading();
				ActService.deleteLocation(id).then(function(data){
					if (data.status == 1) {
						$.each($scope.locations, function(i, n) {
							if (n.id == id) {
								$scope.locations.splice(i, 1);
								return false;
							}
						});
					}
					hideLoading();
				});
		});
	}
		// <script charset="utf-8"
		// src="http://map.qq.com/api/js?v=2.exp&key=C36BZ-WNZRV-6EVPM-UD4MA-JDH45-GAB7P"></script>
		// <script charset="utf-8"
		// src="http://map.qq.com/api/js?v=2.exp&libraries=geometry&key=C36BZ-WNZRV-6EVPM-UD4MA-JDH45-GAB7P"></script>
    
	
	$scope.initMap=function(lat,lng){
		if(lat==null||lng==null){
			lat=31.231592;
			lng = 121.478577;
		}
		$scope.map = MapService.createMap("mapContainer",lat,lng,$scope);
		if ($scope.map) {
			$scope.marker = MapService.getMarker($scope.map);
			$scope.info = MapService.getInfo($scope.map);
			$scope.label = MapService.getLabel($scope.map);
			$scope.$emit("initMapFinished",{map:$scope.map,marker:$scope.marker,info:$scope.info,label:$scope.label});
			if($scope.ip){
				 var cityLocation=MapService.getCityLocation($scope.map,function(latLng){
					 lat =latLng.lat;
					 lng = latLng.lng;
					  MapService.setMapMarker($scope.marker,lat,lng,$scope.info);
					 MapService.geocoder(function(address){
							$("#locationLabel").text(address);
							$("#location").val(address);
							$("#lat").val(lat);
							$("#lng").val(lng);
							MapService.setLabel($scope.label, $scope.marker,address);
						},latLng.lat,latLng.lng);
				 });
				 cityLocation.searchCityByIP($scope.ip);
			}else{
			   MapService.setMapMarker($scope.marker,lat,lng,$scope.info);
			}
			MapService.geocoder(function(address){
				$("#locationLabel").text(address);
				$("#location").val(address);
				$("#lat").val(lat);
				$("#lng").val(lng);
				MapService.setLabel($scope.label, $scope.marker,address);
			},lat,lng);
			var listener = qq.maps.event.addListener(
					$scope.map,
				    'click',
				    function(event) {
						$("#lat").val(event.latLng.getLat());
						$("#lng").val(event.latLng.getLng());
						MapService.setMapMarker($scope.marker,event.latLng.getLat(),event.latLng.getLng(),$scope.info);
						MapService.geocoder(function(address){
							$("#locationLabel").text(address);
							$("#location").val(address);
							MapService.setLabel($scope.label, $scope.marker,address);
						},event.latLng.getLat(),event.latLng.getLng());
					}
				);
		}
	}
	
	$scope.$on('ngRenderFinished', function(ngRenderFinishedEvent) {
			$.each($("#act_location").children(), function(i, n) {
				 if($.trim($(n).text())==""){
					 $(n).remove();
				 }
			});
			
			 $("#actForm").validation({reqmark:false});
			
			UtilService.createFileUploadScript(function(){
				fileuploadMultiNg("act_image",function(data){
					$("#carousel-act").css("background-color","inherit");
					var images = $("#act_image_value").val().split(",");
					if(images.length>1){
						$("#carousel-act").children(".carousel-control").show();
					}else{
						$("#carousel-act").children(".carousel-control").hide();
					}
					$("#carousel-act").children(".carousel-indicators").remove();
					$("#carousel-act").children(".carousel-inner").remove();
					 var source   = $("#image-carousel-template").html();  
			     	   var template = Handlebars.compile(source);
			     	  $("#carousel-act").prepend(template(images)); 
			     	 $('#carousel-act').carousel();
				},false);
			});
			
	});
	
	$scope.showMap=function(){ 
		$scope.initMap(31.231592,121.478577);	  
	  $("#mapModal").modal("show");  
	   $(".modal-backdrop").hide();
	}  
	
	$scope.submitForm = function(isValid) {
		 if ($("#actForm").valid()==false){
			   var firstError = $(".has-error")[0];
			   var top=$(firstError).children("input[type!='hidden']").offset().top;
				   UtilService.scrollTo(top); 
		     }else{
		    	 if (!$("#act_image_value").val()) {
						// $scope.image_error = "请选择活动背景";
						UtilService.showMessage("请选择活动背景",null,null);
						return;
					}
					if($("#start_time").val()>=$("#end_time").val()){
						UtilService.showMessage("开始时间必须小于结束时间",null,null);
						return;
					}
					if(!$("#act_location").val()){
						UtilService.showMessage("请选择约跑地点",null,null);
						return ;
					}

					$("#act_start_time").val($("#act_date").val()+" "+$("#start_time").val());
					$("#act_end_time").val($("#act_date").val()+" "+$("#end_time").val());
					ActService.canNew($rootScope.club.club_eng,false,function(data){
						if(data.status>0){
							$.post("/activity/publish", $("#actForm").serialize() + "&openid="
									+ $rootScope.openid, function(data) {
								hideLoading();
								if (data.status == 1) {
									location.href = "/wechat/#/activity/publishSuccess?act_id="
											+ data.act.act_id;
								}
							});
						}else{
							hideLoading();
						}
					});
					
		     }
	}

	$scope.saveLocation=function(){
		if($("#lat").val()==""||$("#lng").val()==""||$("#location").val()==""){
			$("#alert").text("请选择地点");
			$("#alert").show();
			$("#location_name").focus();
			return ;
		}
		if($.trim($("#location_name").val()).length>20){
			$("#alert").text("名称最大长度为20");
			$("#alert").show();
			$("#location_name").focus();
			return ;
		}
		
		$("#subMapBtn").button("loading");
		ActService.saveLocation($("#locationForm").serialize()).then(function(data){
			if(data.status==1){
			    $('#mapModal').modal('hide');
			    data.data.can_delete=true;
			    $scope.locations.unshift(data.data);
			}else{
				$("#alert").text(data.msg);
				$("#alert").show();
				$("#location_name").focus();
			}
			$("#subMapBtn").button("reset");
		});
	}
	
	$scope.locationInfo=function(){
		var id=$("#act_location").val();
		$.each($scope.locations,function(i,n){
			if(n.id==id){
				WxService.locationInfo(parseFloat(n.lat),parseFloat(n.lng),n.name,n.location);
				return false;
			}
		});
		
	}
	
});

actApp.controller('actInfoController', function($scope, $routeParams,
		ActService, MapService, $rootScope, actInfo,WxService,UtilService) {
	// actInfo.then(function(data){
	if(!actInfo.act.act_id){
		$scope.$emit('notfound');
	}
	$scope.act = actInfo.act;
	$rootScope.club = actInfo.club;
	$scope.location = actInfo.location;
	if (actInfo.act.act_image) {
		$scope.hasImage = true;
		$scope.act.images = actInfo.act.act_image.split(",");
		$scope.act.imagesCount=	$scope.act.images.length;
	} else {
		$scope.hasImage = false;
	}
	WxService.shareObj.title="活动:"+actInfo.act.act_title;
	WxService.shareObj.desc="日期:"+$scope.act.date+"\n时间:"+$scope.act.act_start_time+"\n集合地点:"+$scope.location.name;
	WxService.shareObj.link="http://"+document.domain+"/wechat/#/activity/info?act_id="+$scope.act .act_id;
	WxService.shareObj.imgUrl="http://xiaoi.b0.upaiyun.com/"+$scope.act.images[0]+$rootScope.WX_COVER;
	if($scope.act.act_status==0){
		return;
	}
	
	$scope.ismember = actInfo.ismember;
	$scope.credits = actInfo.credits;
	if($scope.act.canReg&&$scope.act.rang_limit==1&&!$scope.ismember){
		UtilService.showConfirm("您需要加入跑团才能报名，是否现在加入?",null,function(){
			location.href="/wechat/#/clubs/register?club_eng="+$rootScope.club.club_eng;
		});
	}
	$scope.showReg = false;
	$scope.act.regs = actInfo.act.regs;
	$scope.act.checkins = actInfo.act.checkins;
	$scope.configs = actInfo.configs;
	$scope.order = actInfo.order;
	$scope.needBind=actInfo.needBind;
	ActService.getActUsers($scope.act.act_id).then(function(data){
		$scope.users = data.users;
	});
	
//	$scope.act.act_date =$scope.act.act_start_time.split(" ")[0];
//	$scope.act.act_start =$scope.act.act_start_time.split(" ")[1];
//	$scope.act.act_end =$scope.act.act_end_time.split(" ")[1];
	
	
	
	$scope.cancelReg=function(){
		if($scope.act.canRegCancel){
			if($scope.act.needContact){
				UtilService.showMessage("请联系管理员!<a href='http://mp.weixin.qq.com/s?__biz=MzA5OTExNTIwOA==&mid=210894022&idx=1&sn=519b4169d2e1125a05f018feaf4f930c#rd'>点击查看联系方式</>");
			}else{
				$("#cancel_reg_btn").button("loading");
				ActService.cancelReg($scope.act.act_id).then(function(data){
					if(data.status==1){
						$scope.act.canReg=true;
						$scope.act.canRegCancel=false;
						$scope.act.isReg=false;
						$.each($scope.users,function(i,n){
							if(n.uid==data.uid){
								$scope.users.splice(i,1);
								$scope.act.reg_num--;
								if($scope.act.reg_submit_num>0){
									$scope.act.reg_submit_num--;
								}
							}
						});
					}
				});
			}
		}
	}
	
	  $scope.$on('ngFormRepeatRenderFinished', function(ngFormRepeatRenderFinishedEvent,data) {
		  $.each($("input"),function(i,n){
			  if($(n).attr("type")=="file"){
				  fileuploadNg(n.id);
			  }
			
			  if($(n).attr("isRequired")=="1"||$(n).attr("isRequired")=="true"){
				  $(n).attr("required",true);
				  $(n).attr("check-type", $(n).attr("check-type")+" required");
			  }
			 
		  });
		  
		  
		  $.each($("select"),function(i,n){
			  var sel= $(n);
			  var id = $(n).attr("id");
               $.each($scope.configs,function(x,y){
				  if(y.col_name==id){
					  $.each(y.col_list_values,function(m,v){
						  if(v!=null&&$.trim(v)!=""){
							  if(v.selected==1){
								  sel.append('<option  value="'+v.value+'" selected="selected">'+v.text+'</option>'); 
							  }else{
								  sel.append('<option  value="'+v.value+'">'+v.text+'</option>'); 
							  } 
						  }
					  });
				  }
			  });
				if($(n).attr("isSelected")==1){
				  $(n).attr("selected","selected");
			     }
			  });
		  
		  $.each($("input[date-type]"),function(i,n){
			  if($(this).attr("date-max")=="now"){
				  $(this).attr("max",new Date());
			    }
			  initDateTimePicker(null,"#"+this.id,$(this).attr("date-type"));
		  });
		  
		  $("#regForm").validation({reqmark:false});
	  });

	$scope.reg = function() {
		 if($scope.needBind){
			 location.href = "/bind?redirect="+encodeURIComponent("http://"+document.domain+"/wechat/#/activity/info?act_id="+$scope.act.act_id);
		      return;
		 }
		 if($scope.act.canReg&&$scope.act.invite_code!=null&&$.trim($scope.act.invite_code)!=""&&!$scope.checkInvite){
			 UtilService.setPromptInputType("text");
			 UtilService.showPrompt("请输入邀请码",null,function(){
    			 var code = UtilService.getProModalValue();
    			 if($.trim(code)!=""){
    				 ActService.checkInviteCode($scope.act.act_id,code).then(function(data){
        				 if(data.status==1){
        					 $scope.checkInvite=true;
        					 UtilService.closeProModal();
        					 $scope.regSubmit();
        				 }else{
        					 UtilService.showPromptError("邀请码错误");
        				 }
        			 });
    			 }
    			 return false;
    		 });
    	 }else{
    		 if($scope.configs.length>0&&!$scope.showReg){
					$scope.showReg=true;
				}else{
					$scope.regSubmit();
				}
    	 }
	}
	
	$scope.regSubmit=function(){
		 if(!$scope.showReg&&$scope.configs.length>0){
				$scope.showReg=true;
				return;
			}
		 if($scope.showReg||$scope.configs.length==0){
			 if ($("#regForm").valid()==false){
				  //$(".has-error").children("input[type!='hidden']")[0].focus();
				   var firstError = $(".has-error")[0];
				   var top=$(firstError).children("input[type!='hidden']").offset().top;
					   UtilService.scrollTo(top); 
			     }else{
			 		$("#reg_btn").button("loading");
					ActService.reg($scope.act.act_id,$("#regForm").serialize()).then(
							function(data) {
								if (data.status == 1) {
									$scope.canReg=false;
									location.href = "/wechat/#/activity/regSuccess?act_id="
											+ $scope.act.act_id;
									return;
								} else if(data.status==2) {
									$scope.order = data.order;
									$scope.act.needPay=true;
									$scope.act.canReg=false;
									$scope.showReg=false;
									UtilService.showConfirm("该活动需要收费，是否现在支付?",null,function(){
										$scope.pay();
									});
									$scope.act.canRegCancel = true;
								}else{
									UtilService.showMessage(data.msg,null,null);
								}
								$("#reg_btn").button("reset");
							});
				   } 
			    }
	}
	
	  $scope.pay=function(){
		  $("#hash").val(window.location.hash.substring(1));
		  $("#payForm").submit();
	  }
	
	$scope.checkin=function(){
		if($routeParams.ticket){
			$("#regBtn").button("loading");
			ActService.shakeCheckIn($routeParams.ticket,$scope.act.act_id).then(function(data){
				if(data.status==1){
					location.href = "/wechat/#/activity/checkSuccess?act_id="
						+ data.act_id;
					return;
				}else{
					UtilService.showMessage(data.msg,null,null);
				}
				$("#regBtn").button("reset");
			});
		}else{
			location.href="/wechat/#/activity/checkin";
		}
	}
	
	$scope.locationInfo=function(){
		WxService.locationInfo($scope.location.lat,$scope.location.lng,$scope.location.name,$scope.location.location);
	}
	
	$scope.checkUsers=function(){
		ActService.getCheckUsers($scope.act.act_id).then(function(data){
			$scope.users = data;
			$("#userListModalLabel").text("已签到");
			$("#userListModal").modal("show");
		});
	
	}
	
	$scope.regUsers=function(){
		ActService.getRegUsers($scope.act.act_id).then(function(data){
			$scope.users = data;
			$("#userListModalLabel").text("已报名");
			$("#userListModal").modal("show");
		});
	}
	
	$scope.explode=function(){
		$("#pre_desc").height($("#pre_desc")[0].scrollHeight);
		$("#pre_desc").css("max-height",$("#pre_desc")[0].scrollHeight);
		$("#pre_desc_explode").hide();
	}
	
	$scope.$on('ngRenderFinished', function(ngRenderFinishedEvent) {
		$("#pre_desc").html($scope.act.act_desc);
		if($("#pre_desc")[0].scrollHeight>$("#pre_desc").height()){
			$("#pre_desc_explode").show();
		}
	});
	
});

actApp.controller(
				'actCheckInController',
				function($scope, $routeParams, ActService, MapService,
						$rootScope, UtilService, WxService) {
					$scope.qrCheckIn = function() {
						WxService.scanQRCode();
					}
					$scope.checkIn = function() {
						var code = $("#checkCode").val();
						if (code && $.trim(code) != "") {
							$("#codeCheckInBtn").button("loading");
							ActService
									.checkIn($.trim(code),true)
									.then(
											function(data) {
												if (data.status == 1) {
													location.href = "/wechat/#/activity/checkSuccess?act_id="
															+ data.act_id;
													return;
												} else {
													UtilService
															.showMessage(data.msg);
												}
												$("#codeCheckInBtn").button(
														"reset");
											});
						}
					}

				});

actApp.controller('actCheckSuccessController', function($scope, $routeParams,
		ActService, MapService, $rootScope, UtilService, WxService, actInfo,$location) {
	$scope.act = actInfo.act;
	$rootScope.club = actInfo.club;
	$scope.location = actInfo.location;
	
	WxService.shareObj.title=$scope.act.act_title;
	//地址没有的时候将name赋值为空
	if (!$scope.location) {
		WxService.shareObj.desc="日期:"+$scope.act.date+"\n时间:"+$scope.act.act_start_time+"\n集合地点:"+"";
	} else {
		WxService.shareObj.desc="日期:"+$scope.act.date+"\n时间:"+$scope.act.act_start_time+"\n集合地点:"+$scope.location.name;
	}
	WxService.shareObj.link="http://"+document.domain+"/wechat/#/activity/info?act_id="+$scope.act.act_id;
	WxService.shareObj.imgUrl="http://xiaoi.b0.upaiyun.com/"+actInfo.act.act_image.split(",")[0]+$rootScope.WX_COVER;

	if (actInfo.act.act_image) {
		$scope.hasImage = true;
		$scope.act.image = actInfo.act.act_image.split(",")[0];
	} else {
		$scope.hasImage = false;
	}
});

actApp.controller('actUsersController', function($scope, $routeParams,
		ActService, MapService, $rootScope, UtilService, WxService, actInfo,users) {
	if(!actInfo.act.act_id){
		$scope.$emit('notfound');
	}else{
		$scope.users = users.users;
		$scope.act = users.act;
		$rootScope.club =users.club; 
	}
});


actApp.controller('actMapController', function($scope, $routeParams,
		ActService, MapService, $rootScope, UtilService, WxService,club) {
	$scope.map = MapService.createMap("mapContainer", 31.231592,121.478577);
	$rootScope.club = club.club;
	if ($scope.map) {
		$scope.marker = MapService.getMarker($scope.map);
		$scope.info = MapService.getInfo($scope.map);
		$scope.label = MapService.getLabel($scope.map);
		var listener = qq.maps.event.addListener(
				$scope.map,
			    'click',
			    function(event) {
					$("#lat").val(event.latLng.getLat());
					$("#lng").val(event.latLng.getLng());
					MapService.setMapMarker($scope.marker,event.latLng.getLat(),event.latLng.getLng(),$scope.info);
					MapService.geocoder(function(address){
						$("#locationLabel").text(address);
						$("#location_name").val(address);
						$("#location").val(address);
						MapService.setLabel($scope.label, $scope.marker,address+'<br><a class="button button-action button-pill button-tiny" onclick="choose();">确定</a>');
					},event.latLng.getLat(),event.latLng.getLng());
				}
			);
	}
	
	$scope.saveLocation=function(){
		if($.trim($("#location_name").val()).length>20){
			$("#alert").text("名称最大长度为20");
			$("#alert").show();
			$("#location_name").focus();
			return ;
		}
		ActService.saveLocation($("#locationForm").serialize()).then(function(){
			location.href="/wechat/#/activity/new?club_eng="+$rootScope.club.club_eng;
			  $("#chooseModal").modal('hide');
		});
	}
	
});

actApp.controller('actListController', function($scope, $routeParams,
		ActService, MapService, $rootScope, UtilService, WxService,club) {
	$scope.acts = [];
	$scope.act_start_time_ids=[];
	$scope.newids=[];
	$scope.act_start_time;
	if(club){
		$rootScope.club = club;
		$scope.act_start_time=UtilService.getLocalActivityDate($rootScope.club.club_eng);
		$scope.act_start_time_ids = UtilService.getLocalActivityIds($rootScope.club.club_eng);
	}
	 $scope.offset=0;
	 $scope.limit=Math.ceil($(window).height()/80);
	  $scope.latest = 0;
	$scope.loadMore=function(){
		if($routeParams.ticket){
			 $("#loadingEl").show();
			ActService.getShakeActList($.trim($routeParams.ticket)).then(function(data){
				  $("#loadingEl").hide();
				if(data.status==1){
					$scope.acts=data.data;
					if(!$scope.acts||$scope.acts.length==0){
						UtilService
						.showMessage("暂无可签到的活动",null,null);
						return;
					}
					if($scope.acts.length==1){
						       showLoading();
								ActService.shakeCheckIn($routeParams.ticket,$scope.acts[0].act_id).then(function(data){
									if(data.status==1){
										location.replace("http://"+document.domain+"/wechat/#/activity/checkSuccess?act_id="+ data.act_id);
									}else{
										hideLoading();
										UtilService.showMessage(data.msg,null,function(){
											location.replace("http://"+document.domain+"/wechat/#/activity/checkin");
										});
									}
								});
								return;
							}else{
								$scope.act_count =$scope.acts.length;
							}
				}else{
					UtilService
					.showMessage(data.msg,null,function(){
						location.replace("http://"+document.domain+"/wechat/#/activity/checkin");
					});
					return;
				}
			});
			$scope.ticket = $routeParams.ticket;
		}else{
			  if($scope.latest==0||$scope.latest!=$scope.offset){
				  $("#loadingEl").show();
				  $scope.latest = $scope.offset;
				  $scope.hasNew=false;
			ActService.getClubList($routeParams.club_eng,$scope.offset, $scope.limit).then(function(data){
				 $("#loadingEl").hide();
						$.each(data,function(i,n){
							var has =false;
							$.each($scope.acts,function(x,y){
								if(y.act_id==n.act_id){
									has=true;
									return false;
								}
							});
							if(!has){
								n.delay = i + 1;
								if (n.act_image) {
									n.image = n.act_image.split(",")[0];
								}
								$scope.acts.push(n);
							}
						});
						
						 if( $scope.offset==0&&$scope.acts.length>0){
							 $scope.start_time = $scope.acts[0].act_create_time;
						 }
						  $scope.offset+=  $scope.limit;
				          $scope.act_count = $scope.acts.length;
// if(!$scope.hasNew&&$rootScope.club.club_eng){
// UtilService.setLocalActivityIds($scope.newids,$rootScope.club.club_eng);
// UtilService.setLocalActivityDate($scope.start_time,$rootScope.club.club_eng);
// }
			});
			  }
		}
	}
	// href="#/activity/info?act_id={{act.act_id}}&ticket={{ticket}}"
});

actApp.controller('actPublishSuccessController', function($scope, $routeParams,
		ActService, MapService, $rootScope, UtilService, WxService, actInfo) {
	$scope.act = actInfo.act;
	$rootScope.club = actInfo.club;
	$scope.location = actInfo.location;
	if (actInfo.act.act_image) {
		$scope.hasImage = true;
		$scope.act.image = actInfo.act.act_image.split(",")[0];
	} else {
		$scope.hasImage = false;
	}
	
	WxService.shareObj.title=actInfo.act.act_title;
	WxService.shareObj.desc="日期:"+$scope.act.date+"\n时间:"+$scope.act.act_start_time+"\n集合地点:"+$scope.location.name;
	WxService.shareObj.link="http://"+document.domain+"/wechat/#/activity/info?act_id="+$scope.act.act_id;
	WxService.shareObj.imgUrl="http://xiaoi.b0.upaiyun.com/"+actInfo.act.act_image.split(",")[0]+$rootScope.WX_COVER;
	
	$scope.$on('ngRenderFinished', function (ngRenderFinishedEvent) {
		UtilService.setQrCode("qrcodeContainer", "http://"
				+ document.domain + "/wechat/#/activity/qrcodeCheck?code="
				+ $scope.act.qrcode);
   });
	
	$scope.getCode = function() {
		$("#reGenCode").button('loading');
		ActService.getCode({
			act_id : $scope.act.act_id,
			openid : $rootScope.openid
		}).then(function(data) {
			if (data.status == 1) {
				$scope.act.check_code = data.code;
			} else {
				UtilService.showMessage(data.msg,null,null);
			}
			$("#reGenCode").button('reset');
		});
	}
	
	$scope.getQrCode = function() {
		$("#qr_btn").button('loading');
		ActService.getQrCode($scope.act.act_id).then(
				function(data) {
					if (data.status == 1) {
						$scope.act.qrcode = data.code;
						UtilService.setQrCode("qrcodeContainer", "http://"
								+ document.domain + "/activity/checkin?qrcode="
								+ $scope.act.qrcode);

					} else {
						UtilService.showMessage(data.msg,null,null);
					}
					$("#qr_btn").button('reset');
				});
	}
	
	$scope.cancel=function(){
		var sure=false;
		UtilService.showConfirm("确定要取消活动吗?",null,function(){
			showLoading();
			ActService.cancel($scope.act.act_id).then(function(data){
				if(data.status==1){
					   location.replace("http://"+document.domain+"/wechat/#/activity/list?club_eng="+$rootScope.club.club_eng);
				}else{
					UtilService.showMessage(data.msg,null,null);
				}
			 });
			});
       
	}

});


actApp.controller('actRegController', function($scope, $routeParams,
		ActService, MapService, $rootScope, UtilService, WxService, actInfo) {
	$scope.act = actInfo.act;
	$rootScope.club = actInfo.club;
	if (actInfo.act.act_image) {
		$scope.hasImage = true;
		$scope.act.image = actInfo.act.act_image.split(",")[0];
	} else {
		$scope.hasImage = false;
	}
});

