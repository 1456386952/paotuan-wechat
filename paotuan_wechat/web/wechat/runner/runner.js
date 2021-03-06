var runnerApp = angular.module('runnerApp', ['ngRoute', 'infinite-scroll']);

runnerApp
		.config(function($routeProvider, $locationProvider) {
			$routeProvider
					.when(
							'/runner/me',
							{
								templateUrl: 'runner/me.html?'
										+ new Date().getTime(),
								controller: 'MeContorller',
								resolve: {
									userInfo: function($route, LoginService,
											$rootScope) {
										if ($rootScope.user) {
											return {
												user: $rootScope.user
											};
										} else {
											return LoginService.userInfo(null, true);
										}
									}
								}
							})
					.when(
							'/runner/mileage',
							{
								templateUrl: 'runner/mileage.html?'
										+ new Date().getTime(),
								controller: 'MeMileageContorller',
								resolve: {
									mileageBase: function(RunnerService) {
										return RunnerService.getMileageBase();
									},
									userInfo: function($route, LoginService,
											$rootScope) {
										if ($rootScope.user) {
											return {
												user: $rootScope.user
											};
										} else {
											var user = LoginService.userInfo(null, true);
											user.then(function(data) {
												$rootScope.user = data.user;
											});
											return user;
										}
									}
								}
							})
					.when(
							'/runner/clubs',
							{
								templateUrl: 'clubs/clubs.html?'
										+ new Date().getTime(),
								controller: 'MeClubsContorller',
								resolve: {
									userInfo: function($route, LoginService,
											$rootScope) {
										if ($rootScope.user) {
											return {
												user: $rootScope.user
											};
										} else {
											var user = LoginService.userInfo(null, true);
											user.then(function(data) {
												$rootScope.user = data.user;
											});
											return user;
										}
									}
								}
							})
					.when(
							'/runner/clubs/default',
							{
								resolve: {
									defaultClub: function($route, RunnerService,
											$rootScope, $location) {
										RunnerService.getDefaultClub().then(function(data) {
											if (data.set_default) {
												$location.url("/clubs/home?club_eng=" + data.club_eng);
											} else {
												$location.url("/runner/clubs");
											}
										});
									}
								}
							})
					.when(
							'/runner/acts',
							{
								templateUrl: 'activity/activityList.html?'
										+ new Date().getTime(),
								controller: 'MeActsContorller',
								resolve: {
									userInfo: function($route, LoginService,
											$rootScope) {
										if ($rootScope.user) {
											return {
												user: $rootScope.user
											};
										} else {
											var user = LoginService.userInfo(null, true);
											user.then(function(data) {
												$rootScope.user = data.user;
											});
											return user;
										}
									}
								}
							})
					.when(
							'/runner/bind',
							{
								templateUrl: 'runner/bind.html?'
										+ new Date().getTime(),
								controller: 'MeBindContorller',
								resolve: {
									bindData: function($route, RunnerService,
											$rootScope) {
										return RunnerService.checkBind();
									},
									userInfo: function($route, LoginService,
											$rootScope) {
										if ($rootScope.user) {
											return {
												user: $rootScope.user
											};
										} else {
											var user = LoginService.userInfo(null, true);
											user.then(function(data) {
												$rootScope.user = data.user;
											});
											return user;
										}
									}
								}
							})
					.when(
							'/runner/credits',
							{
								templateUrl: 'runner/credits.html?'
										+ new Date().getTime(),
								controller: 'MeCreditsContorller',
								resolve: {
									userInfo: function($route, LoginService,
											$rootScope) {
										if ($rootScope.user) {
											return {
												user: $rootScope.user
											};
										} else {
											var user = LoginService.userInfo(null, true);
											user.then(function(data) {
												$rootScope.user = data.user;
											});
											return user;
										}
									}
								}
							})
					.when(
							'/runner/creditsInfo',
							{
								templateUrl: 'runner/creditsInfo.html?'
										+ new Date().getTime(),
								controller: 'MeCreditsInfoContorller',
								resolve: {
									club: function($route, ClubService,
											$rootScope) {
										if ($rootScope.club) {
											return $rootScope.club;
										} else {
											return ClubService
													.getClubBase($route.current.params["club_eng"]);
										}
									},
									userInfo: function($route, LoginService,
											$rootScope) {
										if ($rootScope.user) {
											return {
												user: $rootScope.user
											};
										} else {
											var user = LoginService.userInfo(null, true);
											user.then(function(data) {
												$rootScope.user = data.user;
											});
											return user;
										}
									}
								}
							})
					.when(
							'/runner/sign',
							{
								templateUrl: 'runner/sign.html',
								controller: 'MeSignContorller',
								resolve: {
									userInfo: function($route, RunnerService,
											$rootScope) {
										return RunnerService.getTarget();
									}
								}
							})
					.when(
							'/runner/info',
							{
								templateUrl: 'runner/info.html?' + new Date().getTime(),
								controller: 'MeInfoContorller',
								resolve: {
									userInfo: function($route, RunnerService,
											$rootScope) {
										return RunnerService.getInfoAll();
									},
									script: function($rootScope, UtilService) {
										UtilService.createFileUploadScript();
									}
								}
							}).otherwise({
				templateUrl: "/wechat/notfound.html"
			});
		});

runnerApp.factory('RunnerService', function($http, $q, $rootScope, UtilService) {
	var service = {
		getMileageBase: function() {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/memileage",
				data: {
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getRecentmileages: function(offset, limit) {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/recentmileages",
				data: {
					offset: offset,
					limit: limit,
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getClubs: function() {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/clubs",
				data: {
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getOwnClubs: function() {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/ownclubs",
				data: {
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getActs: function(offset, limit) {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/acts",
				data: {
					offset: offset,
					limit: limit,
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getChartData: function(type) {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/chartdata",
				data: {
					type: type,
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		deleteMileage: function(id) {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/deleterecent",
				data: {
					id: id
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getCredits: function() {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/credits",
				data: {
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getCreditsInfo: function(offset, limit, club_eng) {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/creditsinfo",
				data: {
					offset: offset,
					limit: limit,
					club_eng: club_eng,
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		bindXP: function(xp_no) {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/xpbind",
				data: {
					xp_no: xp_no,
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		checkBind: function() {
			var deferred = $q.defer();
			$.ajax({
				url: "/bind/runbind",
				data: {
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		setDefaultClub: function(cm_id) {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/defaultclub",
				data: {
					openid: $rootScope.openid,
					cm_id: cm_id
				},
				success: function(data) {
					deferred.resolve(data);
				},
				complete: function() {
					hideLoading();
				}
			});
			return deferred.promise;
		},
		getDefaultClub: function() {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/getdefaultclub",
				data: {
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		setTarget: function(target) {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/settarget",
				data: {
					openid: $rootScope.openid,
					target: target
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getTarget: function() {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/metarget",
				data: {
					openid: $rootScope.openid,
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		sign: function(postData) {
			postData += "&openid=" + $rootScope.openid;
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/sign",
				data: postData,
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getInfoAll: function() {
			var deferred = $q.defer();
			$.ajax({
				url: "/site/userinfoall",
				data: {
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		updateInfo: function(postData) {
			var deferred = $q.defer();
			$.ajax({
				url: "/runners/updateuserinfo",
				data: postData,
				success: function(data) {
					deferred.resolve(data);
				},
				complete: function() {
					hideLoading();
				}
			});
			return deferred.promise;
		},
		bindChip: function(chipNo) {
			var deferred = $q.defer();
			$.ajax({
				url: "/bind/bindchip",
				data: {
					chipNo: chipNo,
					openid: $rootScope.openid
				},
				success: function(data) {
					deferred.resolve(data);
				},
				complete: function() {
					hideLoading();
				}
			});
			return deferred.promise;
		}
	}
	return service;
});

runnerApp.directive('userHeader', function($timeout) {
	return {
		restrict: 'A',
		templateUrl: '/wechat/runner/header.html',
		link: function(scope, element, attr) {
		}
	};
});

runnerApp.controller('MeContorller', function($scope, $routeParams, WxService,
		userInfo, $rootScope) {
	$rootScope.user = userInfo.user;

	//缓存用户信息
	store.set('userInfo', userInfo.user);
	console.log(store.get('userInfo'));

	WxService.shareObj.title = "我的个人中心";
	WxService.shareObj.desc = "和未来的自己一起跑步去";
	WxService.shareObj.link = location.href;
	WxService.shareObj.imgUrl = "http://" + document.domain + "/image/email_verify/logo.png";
});

runnerApp.controller('MeInfoContorller', function($scope, $routeParams, WxService, RunnerService, UtilService,
		userInfo, $rootScope) {
	$rootScope.user = userInfo.user;
	$scope.userInfo = $rootScope.user.info;
	$.each($("input[date-type]"), function(i, n) {
		if ($(this).attr("date-max") == "now") {
			$(this).attr("max", new Date());
		}
		initDateTimePicker(null, "#" + this.id, $(this).attr("date-type"));
	});
	$.each($("select"), function(i, n) {
		$.each($(n).children(), function(x, y) {
			if ($(y).attr("value") == $scope.userInfo[n.id]) {
				$(y).attr("selected", true);
			}
		});
	});

	$scope.id_types = ["其他", "身份证", "护照", "台胞证", "港澳通行证"];
	$scope.user_genders = ["其他", "男", "女", "其他"];

	$scope.$on('ngRenderFinished', function(ngRenderFinishedEvent) {
//			$.each($("input[type='file']"),function(i,n){
//				if($(n).attr("multiple")){
//					fileuploadMultiNg($(n).attr("id"),function(images){
//						if(images.length>0){
//							$scope.submitData($(n).attr("id"),images,true,true);
//						}else{
//							hideLoading();
//						}
//					},false,true,false)
//				}else{
//					fileuploadNg($(n).attr("id"),false,function(images){
//						if(images.length>0){
//							$scope.submitData($(n).attr("id"),images[0].image,false,true);
//						}else{
//							hideLoading();
//						}
//					},false);
//				}
//			});
	});

	$scope.showTextAreaModify = function(name, type, title, v) {
		UtilService.clearProModalValue();
		UtilService.setProTextAreaModalValue(v);
		UtilService.showTextAreaPrompt(title, null, function() {
			var value = UtilService.getProTextAreaModalValue();
			if (value != "" && value != v) {
				UtilService.closeProModal();
				$scope.submitData(name, value, false, false, true);
			}
			return false;
		});
	}

	$scope.showModify = function(name, type, title, v) {
		$scope.inputType = type;
		$scope.inputTitle = title;
		$rootScope.modalInputValue = v;
		if (name == "user_cell") {
			$rootScope.need_bind_code = true;
		} else {
			$rootScope.need_bind_code = false;
		}
		UtilService.setPromptInputType(type);
		UtilService.showPrompt(title, null, function() {
			var value = UtilService.getProModalValue();
			if (value == "") {
				// UtilService.showPromptError("不能为空");
				UtilService.closeProModal();
				return;
			}
			if (value == v) {
				UtilService.closeProModal();
			} else {
				var check = false;
				switch (type) {
					case 'number':
						if ($.isNumeric(value)) {
							value = parseInt(value);
							if (parseInt(value) <= 0) {
								UtilService.showPromptError("请输入正确的数字");
								return false;
							} else {
								check = true;
							}
						} else {
							UtilService.showPromptError("请输入正确的数字");
							return false;
						}
						break;
					case 'tel':
						if (!$rootScope.REG_CELL.test(value)) {
							UtilService.showPromptError("请输入正确的手机号");
							return false;
						} else {
							if (name == "user_cell") {
								if ($.trim($("#bind_code_modal").val()) != '') {
									UtilService.checkCell(value).then(function(data) {
										if (data.status == 1) {
											UtilService.closeProModal();
											UtilService.showConfirm("手机号已存在是否覆盖之前信息?", null, function() {
												UtilService.bindCell(value, $.trim($("#bind_code_modal").val())).then(function(data) {
													if (data.status == 1) {
														$scope.userInfo[name] = value;
														UtilService.closeProModal();
													} else {
														UtilService.showPromptError(data.msg);
													}
												});
											});
										} else {
											UtilService.bindCell(value, $.trim($("#bind_code_modal").val())).then(function(data) {
												if (data.status == 1) {
													$scope.userInfo[name] = value;
													UtilService.closeProModal();
												} else {
													UtilService.showPromptError(data.msg);
												}
											});
										}
									});
									return false;
								}
							} else {
								check = true;
							}
						}
						break;
					default:
						check = true;
				}
				if (check) {
					UtilService.closeProModal();
					$scope.submitData(name, value, false, false, false);
				}
			}
			return false;
		});
		UtilService.setProModalValue(v);
	}

	$scope.uploadComplete = function(images, file) {
		if ($("#" + file).attr("multiple")) {
			if (images.length > 0) {
				$scope.submitData(file, images, true, true);
			} else {
				hideLoading();
			}
		} else {
			if (images.length > 0) {
				$scope.submitData(file, images[0].image, false, true);
			} else {
				hideLoading();
			}
		}
	}

	$scope.submitData = function(name, data, isMult, img, isArea) {
		if ($("#" + name + "_value").length > 0) {
			if (isArea) {
				$("#sub_data_textarea").attr("name", "UserInfo[" + name + "]");
				$("#sub_data_textarea").html($("#" + name + "_value").val());
			} else {
				$("#sub_data").attr("name", "UserInfo[" + name + "]");
				$("#sub_data").val($("#" + name + "_value").val());
			}

		} else {
			if (isArea) {
				$("#sub_data_textarea").attr("name", "UserInfo[" + name + "]");
				$("#sub_data_textarea").html(data);
			} else {
				$("#sub_data").attr("name", "UserInfo[" + name + "]");
				$("#sub_data").val(data);
			}
		}
		showLoading();
		RunnerService.updateInfo($("#userForm").serialize() + "&openid=" + $rootScope.openid).then(function(rData) {
			if (rData.status == 1) {
				if (img) {
					if (isMult) {
						$scope.userInfo.papers[name] = new Array();
						$.each(data, function(x, y) {
							$scope.userInfo.papers[name].push({url: $rootScope.STATIC_IMG_PRE + y.image});
						});
					} else {
						$scope.userInfo.papers[name] = $rootScope.STATIC_IMG_PRE + data;
					}
				} else {
					$scope.userInfo[name] = data;
				}
			} else if (rData.status == 2) {
				$scope.showModify(name, $scope.inputType, $scope.inputTitle, data);
				UtilService.showPromptError(rData.msg);
			}
		});
	}
	WxService.shareObj.title = "我的个人中心";
	WxService.shareObj.desc = "和未来的自己一起跑步去";
	WxService.shareObj.link = location.href;

	WxService.shareObj.imgUrl = "http://" + document.domain + "/image/email_verify/logo.png";
});

runnerApp.controller('MeSignContorller', function($scope, $routeParams, WxService, UtilService,
		RunnerService, userInfo, $rootScope) {
	$rootScope.user = userInfo.user;
	$scope.isSet = userInfo.isSet;
	$scope.current_month = userInfo.current_month.mileage;
	$scope.target = userInfo.target;
	WxService.shareObj.title = "跑步打卡";
	WxService.shareObj.desc = "和未来的自己一起跑步去";
	WxService.shareObj.link = location.href;
	WxService.shareObj.imgUrl = "http://" + document.domain + "/image/logo_80.png";

	$scope.dateChoose = function(type) {
		var btn = $(event.target);
		$.each(btn.parent().parent().find("button"), function(i, n) {
			if ($(n).hasClass("btn-active")) {
				$(n).removeClass("btn-active");
			}
		});
		btn.addClass("btn-active");
		$("#mileage_date").val(type);
	}
//	UtilService.createFileUploadScript(function(){
//		fileuploadMultiNg("mileage_image",function(data){
//			   $("#img-right").hide();
//				$("#mileage_image").hide();
//				$("#mileage_image_value").val();
//				$("#file-cover").attr("src","/image/paotuanzhuce/minus.png");
//				$("#file-cover").one("click",function(){
//				    	 $("#img-right").show();
//				    	 $("#file-cover").attr("src","/image/paotuanzhuce/step.png");
//				    	 $("#mileage_image").next().remove();
//				    	 $("#mileage_image").show();
//					   });
//		},true);
//	});

	$scope.uploadComplete = function(images) {
		$("#img-right").hide();
		$("#mileage_image").hide();
		$("#file-cover").attr("src", "/image/paotuanzhuce/minus.png");
		$("#file-cover").one("click", function() {
			$("#img-right").show();
			$("#file-cover").attr("src", "/image/paotuanzhuce/step.png");
			$("#mileage_image").next().remove();
			$("#mileage_image").show();
			$("#mileage_image_value").val("");
		});
	}

	$scope.sign = function() {
		var mile = $.trim($("#mileage").val());
		if ($.isNumeric(mile)) {
			var mile = parseFloat(mile);
			if (mile <= 0) {
				UtilService.showMessage("跑量必须大于0", null, null);
				return;
			}
			var hours = parseInt($("#hours").val());
			var minutes = parseInt($("#minutes").val());
			var seconds = parseInt($("#seconds").val());
			$("#duration").val(hours * 60 * 60 + minutes * 60 + seconds);
			showLoading();
			RunnerService.sign($("#mileForm").serialize()).then(function(data) {
				if (data.status == 1) {
					window.location = "/runners/mileageinfo/" + data.id;
				} else {
					UtilService.showMessage(data.msg, null, null);
				}
				hideLoading();
			});
		}
	}

	$scope.edit_target = function() {
		UtilService.setPromptInputType("number");
		UtilService.showPrompt("请输入本月目标跑量", null, function() {
			var no = UtilService.getProModalValue();
			if (no == "") {
				return false;
			}
			if ($.isNumeric(no) && no > 0) {
				RunnerService.setTarget(no).then(function(data) {
					if (data.status == 1) {
						$scope.isSet = true;
						$scope.target = data.data;
						UtilService.closeProModal();
					} else {
						UtilService.showPromptError(data.msg);
					}
				});
			} else {
				UtilService.showPromptError("跑量必须为数字并且大于0");
				return false;
			}
		});
	};
	for (var i = 0; i != 61; i++) {
		$("#minutes").append("<option value=" + i + ">" + (i < 10 ? "0" + i : i) + "</option>");
		$("#seconds").append("<option value=" + i + ">" + (i < 10 ? "0" + i : i) + "</option>");
	}
});

runnerApp.controller('MeBindContorller', function($scope, $routeParams,
		WxService, UtilService, RunnerService, $rootScope, bindData) {
	WxService.shareObj.title = "我的个人中心";
	WxService.shareObj.desc = "和未来的自己一起跑步去";
	WxService.shareObj.link = "http://" + document.domain + "/wechat/#/runner/me";
	WxService.shareObj.imgUrl = "http://" + document.domain + "/image/email_verify/logo.png";
	if (bindData.codoonbind != null) {
		$scope.codoon_bind = true;
		$scope.codoon_nick = bindData.codoonbind.nick_name;
		$scope.codoon_bind_time = bindData.codoonbind.create_time;
	}
	if (bindData.hupubind != null) {
		$scope.hupu_bind = true;
		$scope.hupu_nick = bindData.hupubind.nick_name;
		$scope.hupu_bind_time = bindData.hupubind.create_time;
	}
	if (bindData.edoonbind != null) {
		$scope.edoon_bind = true;
		$scope.edoon_nick = bindData.edoonbind.nick_name;
		$scope.edoon_bind_time = bindData.edoonbind.create_time;
	}
	if (bindData.xiaomibind != null) {
		$scope.xiaomi_bind = true;
		$scope.xiaomi_nick = bindData.xiaomibind.nick_name;
		$scope.xiaomi_bind_time = bindData.xiaomibind.create_time;
	}

	$scope.chips = bindData.chipbind;
	$scope.xp_add = function() {
		UtilService.setPromptInputType("text");
		UtilService.showPrompt("请输入个人芯片号", null, function() {
			var no = UtilService.getProModalValue();
			if (no == "") {
				return true;
			}
			$scope.no = no;
			var rep = UtilService.getProModalValueRepeat();
			if (rep != no) {
				UtilService.showPromptError("两次输入的芯片号不一致");
				return false;
			}
			RunnerService.bindChip(no).then(function(data) {
				if (data.status == -1) {
					UtilService.showPromptError("芯片已绑定");
				}
				if (data.status == -2) {
					UtilService.showPromptError("不存在的芯片号");
				}
				if (data.status == 0) {
					UtilService.showPromptError(data.msg);
				}
				if (data.status == 1) {
					$scope.chips.push(data.data);
					UtilService.closeProModal();
				}
			});

//    		showLoading();
//    		RunnerService.bindXP(no).then(function(data){
//    			UtilService.showMessage("该芯片号不存在!",null,null);
//    			hideLoading();
//    		});
			return false;
		});
		UtilService.showProValRepeat();
	}
});

runnerApp.controller('MeCreditsContorller', function($scope, $routeParams,
		WxService, $rootScope, RunnerService) {
	WxService.shareObj.title = "我的积分";
	WxService.shareObj.desc = "和未来的自己一起跑步去";
	WxService.shareObj.link = location.href;
	WxService.shareObj.imgUrl = "http://" + document.domain + "/image/email_verify/logo.png";
	$scope.credits = [];
	$("#loadingEl").show();
	RunnerService.getCredits().then(function(data) {
		$("#loadingEl").hide();
		$scope.credits = data;
		$scope.credits_length = $scope.credits.length;
	});
});

runnerApp.controller('MeCreditsInfoContorller', function($scope, $routeParams,
		WxService, $rootScope, RunnerService, club) {
	if (!$routeParams.club_eng) {
		return;
	}
	WxService.shareObj.title = "我的积分";
	WxService.shareObj.desc = "和未来的自己一起跑步去";
	WxService.shareObj.link = "http://" + document.domain + "/wechat/#/runner/credits";
	WxService.shareObj.imgUrl = "http://" + document.domain + "/image/email_verify/logo.png";
	$scope.club = club;
	$scope.logs = [];
	$scope.offset = 0;
	$scope.limit = Math.ceil($(window).height() / 50);
	$scope.latest = 0;
	$scope.loadMore = function() {
		if ($scope.latest == 0 || $scope.latest != $scope.offset) {
			$("#loadingEl").show();
			$scope.latest = $scope.offset;
			RunnerService.getCreditsInfo($scope.offset, $scope.limit,
					$routeParams.club_eng).then(function(data) {
				$("#loadingEl").hide();
				$scope.offset += data.length;
				$scope.logs = $scope.logs.concat(data);
				$scope.logs_length = $scope.logs.length;
			});
		}
	}
});

runnerApp.controller('MeActsContorller', function($scope, $routeParams,
		WxService, RunnerService, $rootScope) {
	WxService.shareObj.title = "我的活动";
	WxService.shareObj.desc = "和未来的自己一起跑步去";
	WxService.shareObj.link = location.href;
	WxService.shareObj.imgUrl = "http://" + document.domain + "/image/email_verify/logo.png";
	$scope.acts = [];
	$scope.offset = 0;
	$scope.limit = Math.ceil($(window).height() / 80);
	$scope.latest = 0;
	$scope.loadMore = function() {
		if ($scope.latest == 0 || $scope.latest != $scope.offset) {
			$("#loadingEl").show();
			$scope.latest = $scope.offset;
			RunnerService.getActs($scope.offset, $scope.limit).then(
					function(data) {
						$("#loadingEl").hide();
						$scope.offset += data.length;
						$.each(data, function(i, n) {
							var has = false;
							$.each($scope.acts, function(x, y) {
								if (y.act_id == n.act_id) {
									has = true;
									return false;
								}
							});
							if (!has) {
								n.delay = i + 1;
								if (n.act_image) {
									n.image = n.act_image.split(",")[0];
								}
								$scope.acts.push(n);
							}
						});
						$scope.act_count = $scope.acts.length;
					});
		}
	}

});

runnerApp.controller('MeClubsContorller', function($scope, $routeParams,
		WxService, RunnerService, ClubService, UtilService, $rootScope) {
	WxService.shareObj.title = "我的跑团";
	WxService.shareObj.desc = "和未来的自己一起跑步去";
	WxService.shareObj.link = location.href;
	WxService.shareObj.imgUrl = "http://" + document.domain + "/image/email_verify/logo.png";
	$scope.clubs = [];
	$("#loadingEl").show();
	$scope.me = true;
	$scope.search = false;
	RunnerService.getClubs().then(function(data) {
		if (data) {
			$scope.clubs = data;
			$scope.clubs_length = data.length;
			$.each($scope.clubs, function(i, n) {
				n.delay = i + 1;
			});
			if ($scope.clubs_length > 0) {
				$scope.default_club = {};
				$.extend($scope.default_club, data[0]);
				if ($scope.default_club.is_default == 0) {
					UtilService.showMessage("您还没有设置默认跑团，请设置默认跑团.", null, null);
				}
			}
			if ($scope.default_club) {
				$('[data-toggle="tooltip"]').tooltip({title: "设置默认跑团的时间间隔为:" + $scope.default_club.set_default_interval + "天"});
			}
			$scope.me = true;
		}
		$("#loadingEl").hide();
	});

	$scope.$on('ngRepeatRenderFinished', function(ngRepeatRenderFinishedEvent) {
		var width = 0;
		var padding_right = 0;
		$.each($(".list-item-title"), function(i, n) {
			width = $(n).width();
			padding_right = $(n).css("padding-right");
			if (padding_right) {
				padding_right = parseInt(padding_right.substring(0, padding_right.length - 2));
			}
			$("#comp_text_length").addClass($(n).attr("class"));
			$("#comp_text_length").text($(n).text());
			if ($("#comp_text_length").width() < width) {
				$(n).css("width", $("#comp_text_length").width() + padding_right + "px");
			} else {
				$(n).addClass("text-truncate ");
			}
		});
	});

	$scope.searchClubs = function() {
		var name = $.trim($("#searchText").val());
		if (name != "") {
			$scope.clubs = new Array();
			ClubService.searchKey = name;
			location.href = "/wechat/#/clubs/search";
		}
	};

	$scope.setDefault = function(cm_id, club_name, interval) {
		event.preventDefault();
		UtilService.showConfirm("确定要设置<b>'" + club_name + "'</b>为默认跑团吗?", null, function() {
			showLoading();
			RunnerService.setDefaultClub(cm_id).then(function(data) {
				if (data.status == 1) {
					$.each($scope.clubs, function(i, n) {
						if (n.member_id == cm_id) {
							n.is_default = 1;
							n.next_set_default_time = data.next_set_default_time;
							n.can_set = false;
							$.extend($scope.default_club, n);
						} else if (n.is_default == 1) {
							n.is_default = 0;
							n.next_set_default_time = null;
						}
					});
				}
			});
		});
	}

});

runnerApp.controller('MeMileageContorller', function($scope, $routeParams,
		WxService, ChartService, UtilService, RunnerService, $rootScope,
		mileageBase) {
	WxService.shareObj.title = "我的跑量";
	WxService.shareObj.desc = "和未来的自己一起跑步去";
	WxService.shareObj.link = location.href;
	WxService.shareObj.imgUrl = "http://" + document.domain + "/image/email_verify/logo.png";
	$scope.mileageBase = mileageBase.mileage;
	$scope.chart;
	if ($scope.mileageBase.count <= 0)
		return;
	UtilService.loadChartScript(function() {
		require(['echarts', 'echarts/chart/bar', // 使用柱状图就加载bar模块，按需加载
			'echarts/chart/line' // 使用柱状图就加载bar模块，按需加载
		], function(ec) {
			// 基于准备好的dom，初始化echarts图表
			$scope.chart = ec.init(document.getElementById('mileage_chart'));
			// 为echarts对象加载数据
			$scope.chart.setOption(ChartService.mileageChartOption);
			$scope.getChartData("seven");
			window.onresize = $scope.chart.resize;
		});
	});

	$scope.$on('ngRenderFinished', function(ngRenderFinishedEvent) {
		$scope.totalWidth = $("#run_line").parent().width();
		$scope.runAnimate();
	});

	$scope.runAnimate = function() {
		$("#run_img").attr("src", "/image/paotuanzhuce/run.gif");
		$("#run_img").css("margin-left", 0);
		$("#run_num").css("width", 0);
		$("#run_line").css("width", 0);
		var width = 0;
		if ($scope.mileageBase.finish_percent >= 100) {
			width = $scope.totalWidth;
			$scope.mileageBase.finish_percent = 100;
		} else {
			width = $scope.totalWidth * $scope.mileageBase.finish_percent / 100;
		}
		if (width > 52) {
			$("#run_img").animate({marginLeft: width - 52 + "px"}, 2000, "swing", function() {
				$("#run_img").attr("src", "/image/paotuanzhuce/run.png");
			});
			$("#run_num").animate({width: width + "px"}, 2000, "swing");
		} else {
			$("#run_img").animate({marginLeft: "0px"}, 2000, "swing", function() {
				$("#run_img").attr("src", "/image/paotuanzhuce/run1.png");
			});
			$("#run_num").animate({width: "0px"}, 2000, "swing");
		}

		$("#run_line").animate({width: width}, 2000, "swing");
	};

	$scope.edit_target = function() {
		UtilService.setPromptInputType("number");
		UtilService.showPrompt("请输入本月目标跑量", null, function() {
			var no = UtilService.getProModalValue();
			if (no == "") {
				return false;
			}
			if ($.isNumeric(no) && no > 0) {
				RunnerService.setTarget(no).then(function(data) {
					if (data.status == 1) {
						$scope.mileageBase.month_target = data.data;
						$scope.mileageBase.finish_percent = ($scope.mileageBase.curren_month / $scope.mileageBase.month_target).toFixed(2) * 100;
						UtilService.closeProModal();
						$scope.runAnimate();
					} else {
						UtilService.showPromptError(data.msg);
					}

				});
			} else {
				UtilService.showPromptError("跑量必须为数字并且大于0");
				return false;
			}
		});
	};

	$scope.offset = 0;
	$scope.limit = Math.ceil($(window).height() / 90);
	$scope.mileages = [];
	$scope.latest = 0;
	$scope.loadMore = function() {
		if ($scope.latest == 0 || $scope.latest != $scope.offset) {
			$("#loadingEl").show();
			$scope.latest = $scope.offset;
			RunnerService.getRecentmileages($scope.offset, $scope.limit).then(
					function(data) {
						$("#loadingEl").hide();
						$.each(data, function(i, n) {
							n.mileage.delay = i + 1;
							var has = false;
							$.each($scope.mileages, function(x, y) {
								if (y.id == n.mileage.id) {
									has = true;
									return false;
								}
							});
							if (!has) {
								$scope.mileages.push(n.mileage);
								$scope.offset++;
							}

						});
					});
		}
	}

//	if(!$scope.initLoad){
//		$scope.loadMore();
//	}

	$scope.type = "seven";
	$scope.getChartData = function(type) {
		$scope.type = type;
		$scope.chart.showLoading();
		RunnerService.getChartData(type).then(
				function(data) {
					var current = $("#" + type);
					$.each(current.parent().children(), function(i, n) {
						if (current.attr("id") != n.id) {
							$(n).removeClass("active");
						}
					});
					current.addClass("active");
					ChartService.mileageChartOption.xAxis[0].data = $
							.parseJSON(data.xAxis);
					ChartService.mileageChartOption.series[0].data = $
							.parseJSON(data.series);
					$scope.chart.clear();
					$scope.chart.setOption(ChartService.mileageChartOption);
					$scope.chart.hideLoading();
				});
	}
	$scope.deleteMileage = function(id, mileage) {
		event.preventDefault();
		UtilService.showConfirm("确定要删除该打卡记录吗?", null, function() {
			showLoading();
			RunnerService.deleteMileage(id).then(function(data) {
				if (data.status == 1) {
					$scope.mileageBase.count--;
					$scope.mileageBase.mileage -= mileage;
					$.each($scope.mileages, function(i, n) {
						if (n.id == id) {
							$scope.mileages.splice(i, 1);
							return false;
						}
					});
				}
				hideLoading();
			});
		});
	}
});
