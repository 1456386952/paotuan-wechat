var _hmt = _hmt || [];
$.ajaxSetup({
	type: "POST",
	timeout: 30000,
	beforeSend: function() {

	},
	error: function(req) {
	},
	complete: function(req, textStatus) {
		// hideLoading();
		if (textStatus == "timeout") {
			//alert("服务器连接超时，请稍后重试!");
			hideLoading();
		}
		$("*[data-loading-text]").button("reset");
	},
	statusCode: {
		500: function() {
			location.replace("http://" + document.domain + "/500.html");
			hideLoading();
		},
		504: function() {
			//alert("服务器连接超时，请稍后重试!");
			hideLoading();
		},
		404: function() {
			location.replace("http://" + document.domain + "/notfound.html");
		}
	}
});

var wechatApp = angular.module('wechatApp', ['ngRoute', 'clubApp', 'actApp', 'runnerApp', 'marketing_club', 'analysisApp', 'race']);
angular.module('infinite-scroll').value('THROTTLE_MILLISECONDS', 2000);

wechatApp.run(function($rootScope, LoginService, WxService, ClubService,
		$location, UtilService, ActService, ConstService, $sce) {
	ConstService.initConst();
	/*var needLoginUrl = ["/clubs/home", "/activity/checkin", "/clubs/register", "/clubs/members",
	 "/activity/info", "/activity/regSuccess", "/activity/list", "/activity/new",
	 "/activity/checkSuccess", "/activity/qrcodeCheck", "/runner/me", "/activity/map", "/runner/mileage", "/runner/clubs"];*/
	var exceptUrl = ["/race/runner/images"];
	var ua = window.navigator.userAgent.toLowerCase();
	if (ua.match(/MicroMessenger/i) == 'micromessenger') {
		$rootScope.weixin = true;
	} else {
		$rootScope.weixin = false;
	}
	if (!$rootScope.wx_init) {
		WxService.initJsapi().then(
				function(data) {
					wx.config({
						debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
						appId: data.appId,
						timestamp: data.timestamp,
						nonceStr: data.nonceStr,
						signature: data.signature,
						jsApiList: ['onMenuShareAppMessage',
							'onMenuShareTimeline', "getLocation",
							"openLocation", "previewImage",
							"scanQRCode", "chooseImage", "uploadImage"]
					});
					wx.ready(function() {
						$rootScope.wx_init = true;
					});
					wx.error(function(res) {
						$rootScope.wx_init = false;
					});
				});
	}

	$rootScope.openid = LoginService.isLogin();
	$rootScope.$on('$locationChangeStart',
			function(evt, next, current) {
				$(".modal").modal("hide");
				// 网站统计
				var hm = $("#baidu_tongji");
				if (hm.length != 0) {
					hm.remove();
				}
				var hm = document.createElement("script");
				hm.src = "//hm.baidu.com/hm.js?a170efae1f2a6aa0873d31a2d0129e9c";
				hm.id = "baidu_tongji";
				document.body.appendChild(hm);
				showLoading();
				var params = "";
				$.each($location.$$search, function(i, n) {
					params = params + "@" + i + "=" + n;
				});
				if (params != "") {
					params = params.substring(1);
				}
				if ($.inArray($location.$$path, exceptUrl) == -1) {
					if (!$rootScope.openid) {
						var domain = document.domain;
						var path = window.location.pathname;
						var hash = window.location.hash.substring(1);
						WxService.getOauthUrl(path, $location.$$path + "?" + params, false);
						evt.preventDefault();
					} else {// 超时重新登录
						if ($location.$$path == "/activity/new" && !$rootScope.canNew) {
							ActService.canNew($location.$$search["club_eng"], false, function(data) {
								hideLoading();
								if (data.status <= 0) {
									evt.preventDefault();
								}
							});
							$rootScope.canNew = false;
						}

						if ($location.$$path == "/clubs/register") {
							var obj = {};
							LoginService.userInfo(obj, false);
							if (obj.data.status == 1) {
								if (obj.data.user.user_cell == null || $.trim(obj.data.user.user_cell) == "") {
									location.href = "/bind?redirect=" + encodeURIComponent("http://" + document.domain + "/wechat/#/clubs/register?club_eng=" + $location.$$search["club_eng"]);
									evt.preventDefault();
								}
							} else {
								UtilService.showMessage("获取用户信息失败，请稍后重试!", null, null);
								evt.preventDefault();
							}
						}

						if ($location.$$path == "/activity/qrcodeCheck") {
							var code = $location.$$search["code"];
							ActService.checkIn($.trim(code), false).then(
									function(data) {
										if (data.status == 1) {
											location.href = "/wechat/#/activity/checkSuccess?act_id=" + data.act_id;
										} else {
											UtilService.showMessage(data.msg);
										}
										evt.preventDefault();
									});
						}

						if ($location.$$path == "/activity/checkin") {
							if ($location.$$search["ticket"]) {
								location.href = "/wechat/#/activity/list?ticket=" + $location.$$search["ticket"];
								return;
							}
						}
					}
				}

				/*if ($location.$$path == "/activity/new") {
				 if ($("#map_api").length == 0) {
				 // var script = document.createElement("script");
				 // script.type = "text/javascript";
				 // script.src =
				 // "http://map.qq.com/api/js?v=2.exp&key=C36BZ-WNZRV-6EVPM-UD4MA-JDH45-GAB7P&callback=init";
				 // script.id = "map_api";
				 // document.body.appendChild(script);
				 }
				 }*/
			});

	$rootScope.$on('$routeChangeSuccess', function(evt, next, current) {
		$("body").scrollTop(0);
	});

	$rootScope.$on('notfound', function(notfoundEvent) {
		location.replace("http://" + document.domain + "/notfound.html");
	});

	$rootScope.$on('ngRenderFinished', function(ngRenderFinishedEvent, data) {
		hideLoading();
		$rootScope.header = new Object();
		$rootScope.slideMenus = new Object();
		var show = false;
		if ($location.$$path.indexOf("/runner") == 0 && $rootScope.user) {
			$rootScope.in_me = true;
			$rootScope.in_club = false;
			show = true;
			if ($location.$$path == "/runner/sign" || $location.$$path == "/runner/me") {
				show = false;
			}
			$rootScope.header.img = $rootScope.user.user_face;
			$rootScope.header.text = $rootScope.user.nick_name;
			$rootScope.slideMenus.title = $sce.trustAsHtml("<a class='btn-link' data-type='slidebar'  href=\"/wechat/#/runner/me\">我</a>");
			$rootScope.slideMenus.menus = [{href: "/wechat/#/runner/mileage", text: "我的跑量"},
				{href: "/wechat/#/runner/clubs", text: "我的跑团"},
				{href: "/wechat/#/runner/acts", text: "我的活动"},
				{href: "/wechat/#/runner/credits", text: "我的积分"},
				{href: "/wechat/#/runner/info", text: "我的个人资料"}
			];
		}

		if ($rootScope.club && ($location.$$path.indexOf("/clubs") == 0 || $location.$$path.indexOf("/activity") == 0 || $location.$$path.indexOf("/race") == 0) && $location.$$path != "/clubs/search") {
			$rootScope.in_club = true;
			$rootScope.in_me = false;
			show = true;
			if ($rootScope.club.club_logo) {
				$rootScope.header.img = $rootScope.STATIC_IMG_PRE + $rootScope.club.club_logo + "!mid";
			} else {
				$rootScope.header.img = false;
			}

			$rootScope.header.text = $rootScope.club.club_name;
			$rootScope.slideMenus.title = $sce.trustAsHtml($rootScope.club.club_name);
			$rootScope.slideMenus.menus = [{href: "/wechat/#/clubs/home?club_eng=" + $rootScope.club.club_eng, text: "主页"},
				{href: "/wechat/#/clubs/intro?club_eng=" + $rootScope.club.club_eng, text: "介绍"},
				{href: "/wechat/#/activity/list?club_eng=" + $rootScope.club.club_eng, text: "活动"},
				{href: "/clubs/" + $rootScope.club.club_eng + "/mileages", text: "跑量"},
				{href: "/wechat/#/runner/me", text: "我的"}
			];
		}

		if (show) {
			$("#header").show();
			$("div[ng-view]").css("min-height", $(window).height() - 50);
		} else {
			$("#header").hide();
			$("div[ng-view]").css("min-height", $(window).height());
		}

		$("#mapModal").modal("hide");
		wx.ready(function() {
			wx.onMenuShareAppMessage({
				title: WxService.shareObj.title, // 分享标题
				desc: WxService.shareObj.desc,
				link: WxService.shareObj.link, // 分享链接
				imgUrl: WxService.shareObj.imgUrl, // 分享图标
				type: 'link', // 分享类型,music、video或link，不填默认为link
				dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
				success: function() {
				},
				cancel: function() {
					// 用户取消分享后执行的回调函数
				}
			});
			// 获取“分享到朋友圈”按钮点击状态及自定义分享内容接口
			wx.onMenuShareTimeline({
				title: WxService.shareObj.title, // 分享标题
				link: WxService.shareObj.link, // 分享链接
				imgUrl: WxService.shareObj.imgUrl, // 分享图标
				success: function() {
					// 用户确认分享后执行的回调函数
				},
				cancel: function() {
					// 用户取消分享后执行的回调函数
				}
			});
		});
	});

});


wechatApp.factory('LoginService', function($http, $q, $rootScope) {
	var service = {
		login: function(code) {
			$.get("/site/login?code=" + code, function(data) {
				// localStorage.openid
				alert(data);
			});
		},
		isLogin: function() {
			if (store.get("openid")) {
				return store.get("openid");
			}
			return false;
		},
		checkCellCode: function(cell, code) {
			var deferred = $q.defer();
			$.ajax({
				url: "/bind/checkcode",
				data: {
					openid: $rootScope.openid,
					cell: cell,
					code: code
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getWechatUserInfo: function(async, object) {
			var deferred = $q.defer();
			$.ajax({
				url: "/wechat/getuserinfo",
				data: {
					openid: $rootScope.openid
				},
				async: async,
				success: function(data) {
					object.userInfo = $.parseJSON(data);
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		userInfo: function(obj, async) {
			var deferred = $q.defer();
			$.ajax({
				url: "/site/userinfo",
				data: {
					openid: $rootScope.openid
				},
				async: async,
				success: function(data) {
					if (obj != null) {
						obj.data = data;
					}
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		}
	};
	return service;
});

wechatApp.factory('ChartService', function($http, $q, $rootScope) {
	var service = {
		mileageChartOption: {
			title: {
				show: false,
				text: '跑量(KM)'
			},
			tooltip: {
				trigger: 'axis',
				show: true
			},
			grid: {
				x: 40,
				y: 20,
				x2: 14,
				y2: 30
			},
			xAxis: [
				{
					type: 'category',
					data: ""
				}
			],
			yAxis: [
				{
					type: 'value'
				}
			],
			series: [
				{
					name: "跑量",
					type: "bar",
					itemStyle: {
						normal: {
							color: '#02c66c'
						}
					},
					"data": ""
				}
			]
		}
	};
	return service;
});

wechatApp.factory('MapService', function($http, $q, $compile) {
	var service = {
		search: function(map, callback) {
			var search_key = $.trim($("#qqmap_search_key").val());
			if (search_key != "") {
				var geocoder = new qq.maps.Geocoder({
					complete: function(result) {
						map.setCenter(result.detail.location);
						if ($.isFunction(callback)) {
							callback(result.detail.location);
						}
					}
				});
				geocoder.getLocation(search_key);
			}
		},
		addSearchControl: function(map, scope) {
			var controlDiv = document.createElement("div");
			$(controlDiv).addClass("input-group no-radius");
			controlDiv.style.zIndex = 1;
			$(controlDiv).html($compile('<div class="input-group-addon no-padding no-radius"><button class="btn no-border no-radius" style="background-color:transparent" ng-click="myLocation();"><i class="fa fa-location-arrow"></i></button></div><input class="form-control" type="search" id="qqmap_search_key">' +
					'<div class="input-group-addon no-padding no-radius"><button class="btn no-border no-radius" ng-click="search();"><i class="fa fa-search"></i> 搜索</button>')(scope));
			map.controls[qq.maps.ControlPosition.TOP_CENTER].push(controlDiv);
		},
		createMap: function(container, lat, lng, scope) {
			var myLatlng = new qq.maps.LatLng(lat, lng);
			var myOptions = {
				zoom: 14,
				center: myLatlng,
				mapTypeId: qq.maps.MapTypeId.ROADMAP
			}
			if (document.getElementById(container)) {
				document.getElementById(container).style.display = "block";
				var map = new qq.maps.Map(document.getElementById(container),
						myOptions);
				this.addSearchControl(map, scope);
				return map;
			}
		},
		getCityLocation: function(map, callback) {
			return new qq.maps.CityService({
				map: map,
				complete: function(results) {
					map.setCenter(results.detail.latLng);
					if ($.isFunction(callback)) {
						callback(results.detail.latLng);
					}
				}
			});
		},
		getMarker: function(map) {
			return new qq.maps.Marker({
				map: map,
				animation: qq.maps.MarkerAnimation.DOWN,
			});
		},
		setMapMarker: function(marker, lat, lng, info) {
			marker.map.panTo(new qq.maps.LatLng(lat, lng));
			marker.setPosition(new qq.maps.LatLng(lat, lng));
		},
		getInfo: function(map) {
			return new qq.maps.InfoWindow({
				map: map
			});
		},
		setInfo: function(info, marker, infotext) {
			info.open();
			info.setContent('<div style="text-align:center;white-space:nowrap;'
					+ 'margin:0;">' + infotext + '</div>');
			info.setPosition(marker.getPosition());
		},
		getLabel: function(map) {
			return new qq.maps.Label({
				map: map,
			});
		},
		setLabel: function(label, marker, infotext) {
			label.setPosition(marker.getPosition());
			label.setContent(infotext);
		},
		setCircle: function(map, lat, lng, radius) {
			var circle = new qq.maps.Circle({
				map: map,
				center: new qq.maps.LatLng(lat, lng),
				radius: radius,
				strokeWeight: 1
			});
		},
		computeDistance: function(lat, lng, lat1, lng1) {
			var start = new qq.maps.LatLng(lat, lng);
			var end = new qq.maps.LatLng(lat1, lng1);
			return Math.round(qq.maps.geometry.spherical
					.computeDistanceBetween(start, end) * 10) / 10;
		},
		geocoder: function(callBack, lat, lng) {
			var geocoder = new qq.maps.Geocoder({
				complete: function(result) {
					if ($.isFunction(callBack)) {
						callBack(result.detail.address);
					}
				}
			});
			var coord = new qq.maps.LatLng(lat, lng);
			geocoder.getAddress(coord);
		}
	};
	return service;
});

wechatApp.factory('WxService', function($http, $q) {
	var service = {
		upload_images: new Array(),
		wx_serverids: [],
		wx_localids: [],
		shareObj: new Object(),
		getOauthUrl: function(url, hash, async) {
			var deferred = $q.defer();
			$.ajax({
				url: "/site/oauthurl",
				data: {redirectUrl: url + "$" + hash},
				async: async,
				success: function(data) {
					window.location.href = data;
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		initJsapi: function() {
			var deferred = $q.defer();
			$.post("/wechat/jsapiparams", {
				url: window.location.href.split('#')[0]
			}, function(data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		scanQRCode: function() {
			wx.scanQRCode({
				needResult: 0, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
				scanType: ["qrCode", "barCode"], // 可以指定扫二维码还是一维码，默认二者都有
				success: function(res) {
					var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
				}
			});
		},
		locationInfo: function(lat, lng, name, location) {
			wx.openLocation({
				latitude: lat, // 纬度，浮点数，范围为90 ~ -90
				longitude: lng, // 经度，浮点数，范围为180 ~ -180。
				name: name, // 位置名
				address: location, // 地址详情说明
				scale: 15, // 地图缩放级别,整形值,范围从1~28。默认为最大
				infoUrl: '' // 在查看位置界面底部显示的超链接,可点击跳转
			});
		},
		chooseImg: function(count, callBack, sizeType) {
			if (typeof (sourceType) == 'undefined') {
				sizeType = ['compressed'];
			}
			$this = this;
			wx.chooseImage({
				count: count, // 默认9
				sizeType: sizeType, // 可以指定是原图还是压缩图，默认二者都有
				sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
				success: function(res) {
					$this.wx_serverids = [];
					$this.wx_localids = [];
					$this.upload_images = [];
					var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
					if ($.isFunction(callBack)) {
						callBack(localIds);
					}
				},
				fail: function() {
					hideLoading();
					UtilService.showMessage("JSAPI调用错误", null, null);
				}
			});
		},
		uploadImg: function(localids, callBack, showProgress) {
			if (typeof (showProgress) == 'undefined') {
				showProgress = 1;
			}
			if (localids.length == 0) {
				return;
			}
			$this = this;
			var localid = localids.pop();
			wx.uploadImage({
				localId: localid.toString(), // 需要上传的图片的本地ID，由chooseImage接口获得
				isShowProgressTips: showProgress, // 默认为1，显示进度提示
				success: function(res) {
					var serverId = res.serverId; // 返回图片的服务器端ID
					$this.wx_serverids.push(serverId);
					$this.wx_localids.push(localid);
					if ($.isFunction(callBack)) {
						callBack(serverId);
					}
				},
				fail: function() {
					hideLoading();
					UtilService.showMessage("JSAPI调用错误", null, null);
				}
			});
		},
		getLocation: function(callBackFunction) {
			wx.getLocation({
				success: function(res) {
					var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
					var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
					var speed = res.speed; // 速度，以米/每秒计
					var accuracy = res.accuracy; // 位置精度
					if ($.isFunction(callBackFunction)) {
						callBackFunction(latitude, longitude);
					}

				},
				fail: function() {
					if ($.isFunction(callBackFunction)) {
						callBackFunction(null, null);
					}
				}
			});
		},
	};

	return service;
});

wechatApp.factory('ConstService', function($rootScope) {
	var service = {
		initConst: function() {
			$rootScope.STATIC_HOSTNAME = "http://resource.paobuqu.com";
			$rootScope.STATIC_IMG_PRE = "http://xiaoi.b0.upaiyun.com";
			$rootScope.WX_COVER = "!wx.cover";
			$rootScope.COVER = "!m.cover";
			$rootScope.REG_CELL = /^0?(13[0-9]|15[0-9]|17[0678]|18[0-9]|14[57])[0-9]{8}$/;
			$rootScope.REG_EMAIL = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i;
			$rootScope.id_types = ["其他", "身份证", "护照", "台胞证", "港澳通行证"];
			$rootScope.user_genders = ["其他", "男", "女", "其他"];
		}
	}
	return service;
});

wechatApp.factory('UtilService', function($http, $q, $rootScope) {
	var service = {
		LOCALSTORE_CLUB_MEMBER_START_DATE: "club_member_start_date",
		LOCALSTORE_CLUB_MEMBER_IDS: "club_member_ids",
		LOCALSTORE_ACT_START_DATE: "activity_start_date",
		LOCALSTORE_ACT_IDS: "activity_ids",
		showMessage: function(msg, hiddenCallback, sureCallBack) {
			$("#alertContent").html("");
			$("#alertContent").html(msg);
			if ($.isFunction(hiddenCallback)) {
				$("#alertModal").on("hidden.bs.modal", function(e) {
					hiddenCallback();
				});
			};
			$("#alertModal").find("#sureBtn").unbind("click");
			if ($.isFunction(sureCallBack)) {
				$("#alertModal").find("#sureBtn").on("click", function() {
					var r = sureCallBack();
					if (r || typeof (r) == 'undefined') {
						$("#alertModal").modal("hide");
						hideLoading();
					}
				});
			} else {
				$("#alertModal").find("#sureBtn").on("click", function() {
					$("#alertModal").modal("hide");
					hideLoading();
				});

			}
			$("#alertModal").modal();
		},
		showConfirm: function(msg, hiddenCallback, sureCallBack) {
			$("#confirmContent").html("");
			$("#confirmContent").html(msg);
			if ($.isFunction(hiddenCallback)) {
				$("#confirmModal").on("hidden.bs.modal", function(e) {
					hiddenCallback();
				});
			}
			;
			if ($.isFunction(sureCallBack)) {
				$("#confirmModal").find("#confirmSureBtn").unbind("click")
				$("#confirmModal").find("#confirmSureBtn").on("click", function() {
					sureCallBack();
					$("#confirmModal").modal("hide");
				});
			} else {
				$("#confirmModal").find("#confirmSureBtn").on("click", function() {
					$("#confirmModal").modal("hide");
				});

			}
			$("#confirmModal").modal();
		},
		showPrompt: function(title, hiddenCallback, sureCallBack, showCancelBtn) {
			$("#pro_modal_title").html("");
			$("#pro_modal_title").html(title);
			$("#pro_alert").hide();
			$("#bind_code_group").hide();
			$("#bind_code_modal").val('');
			$("#pro_modal_value").attr("placeholder", title);
			$("#pro_modal_value").val('');
			$("#value_repeat").hide();
			$("#pro_modal_value_repeat").val('');
			$("#pro_Modal").find("#pro_confirmSureBtn").button("reset");
			if (typeof (showCancelBtn) == 'undefined') {
				showCancelBtn = true;
			}
			if (!showCancelBtn) {
				$("#pro_Modal").find("#pro_cancelBtn").hide();
				;
			}
			if ($.isFunction(hiddenCallback)) {
				$("#pro_Modal").on("hidden.bs.modal", function(e) {
					hiddenCallback();
				});
			}
			$("#pro_Modal").find("#pro_confirmSureBtn").unbind("click");
			if ($.isFunction(sureCallBack)) {
				$("#pro_Modal").find("#pro_confirmSureBtn").on("click", function() {
					if ($.trim($("#pro_modal_value").val()) != "") {
						$(this).button("loading");
					}
					$("#pro_alert").hide();
					if (sureCallBack()) {
						$("#pro_Modal").modal("hide");
					} else {
						event.preventDefault();
						$(this).button("reset");
					}
				});
			} else {
				$("#pro_Modal").find("#pro_confirmSureBtn").on("click", function() {
					$("#pro_Modal").modal("hide");
				});

			}
			$("#pro_Modal").modal();
		},
		showTextAreaPrompt: function(title, hiddenCallback, sureCallBack) {
			$("#pro_textarea_modal_title").html("");
			$("#pro_textarea_modal_title").html(title);
			$("#pro_textarea_alert").hide();
			if ($.isFunction(hiddenCallback)) {
				$("#pro_textarea_Modal").on("hidden.bs.modal", function(e) {
					hiddenCallback();
				});
			}
			$("#pro_textarea_Modal").find("#pro_textarea_confirmSureBtn").unbind("click");
			if ($.isFunction(sureCallBack)) {
				$("#pro_textarea_Modal").find("#pro_textarea_confirmSureBtn").on("click", function() {
					if ($.trim($("#pro_textarea_modal_value").val()) != "") {
						$(this).button("loading");
					}
					$("#pro_textarea_alert").hide();
					if (sureCallBack()) {
						$("#pro_textarea_Modal").modal("hide");
					} else {
						$(this).button("reset");
					}
				});
			} else {
				$("#pro_textarea_Modal").find("#pro_textarea_confirmSureBtn").on("click", function() {
					$("#pro_textarea_Modal").modal("hide");
				});

			}
			$("#pro_textarea_Modal").modal();
		},
		showPromptError: function(text) {
			$("#pro_alert").show();
			$("#pro_alert").html("");
			$("#pro_alert").html(text);
		},
		setPromptInputType: function(type) {
			$("#pro_modal_value").attr("type", type);
		},
		getProModalValue: function() {
			return $.trim($("#pro_modal_value").val());
		},
		setProModalValue: function(value) {
			$("#pro_modal_value").val(value);
		},
		getProModalValueRepeat: function() {
			return $.trim($("#pro_modal_value_repeat").val());
		},
		showProValRepeat: function() {
			$("#value_repeat").show();
		},
		getProTextAreaModalValue: function() {
			return $.trim($("#pro_textarea_modal_value").val());
		},
		setProTextAreaModalValue: function(value) {
			$("#pro_textarea_modal_value").text(value);
		},
		clearProModalValue: function() {
			$("#pro_modal_value").val("");
			$("#pro_textarea_modal_value").text("");
		},
		closeProModal: function() {
			$("#pro_Modal").modal("hide");
			$("#pro_textarea_Modal").modal("hide");
		},
		closeModal: function() {
			$("#alertModal").modal("hide");
		},
		setQrCode: function(container, url) {
			var qr = qrcode(10, 'H');
			qr.addData(url);
			qr.make();
			$("#" + container).html(qr.createImgTag());
			$("#" + container).append(
					'<br>"长按二维码->识别图中二维码"或者"长按二维码->保存图片->返回微信扫一扫"');
		},
		getRegCode: function(cell) {
			var deferred = $q.defer();
			$.post("/clubs/" + $rootScope.club.club_eng + "/getcellcode?" + new Date().getTime(), {cell: cell, openid: $rootScope.openid}, function(data) {
				deferred.resolve(data);
			});
			return deferred.promise;
		},
		getCode: function(cell, btn) {
			var deferred = $q.defer();
			$.ajax({
				url: "/bind/getcellcode",
				data: {
					openid: $rootScope.openid,
					cell: cell
				},
				beforeSend: function() {
					$rootScope.btnLoading(btn);
				},
				success: function(data) {
					deferred.resolve(data);
				},
				error: function() {
					$rootScope.btnReset(btn);
				}
			});
			return deferred.promise;
		},
		bindCell: function(cell, cellCode) {
			var deferred = $q.defer();
			$.ajax({
				url: "/bind/bind",
				data: {
					openid: $rootScope.openid,
					cell: cell,
					cellCode: cellCode
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		checkCell: function(cell) {
			var deferred = $q.defer();
			$.ajax({
				url: "/bind/checkcell",
				data: {
					openid: $rootScope.openid,
					cell: cell,
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		checkEmail: function(email) {
			var deferred = $q.defer();
			$.ajax({
				url: "/bind/checkemail",
				data: {
					openid: $rootScope.openid,
					email: email,
				},
				success: function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		disableBtn: function(btn, text) {
			var sec = 30;
			$(btn).attr("disabled", "disabled");
			var timer = window.setInterval(function() {
				$(btn).attr("disabled", "disabled");
				$(btn).text(text + "(" + --sec + ")");

				if (sec == 0) {
					$(btn).text(text);
					$(btn).removeAttr("disabled");
					window.clearInterval(timer);
				}
			}, 1000);
		},
		scrollTo: function(top) {
			if (top == 0) {
				top = $("*[ng-view]").scrollTop() - 100;
			}
			if (top > 0 && top > $(window).height()) {
				top = $("*[ng-view]").scrollTop() + top - $(window).height() + $(window).height() / 2;
			}
			if (top < 0) {
				top = $("*[ng-view]").scrollTop() + top - 100;
			}
			$('*[ng-view]').animate({scrollTop: top + "px"}, 800);
		},
		setLocalClubMemberDate: function(val, club_eng) {
			store.set(this.LOCALSTORE_CLUB_MEMBER_START_DATE + "_" + club_eng, val);
		},
		getLocalClubMemberDate: function(club_eng) {
			return store.get(this.LOCALSTORE_CLUB_MEMBER_START_DATE + "_" + club_eng);
		},
		setLocalClubMemberIds: function(val, club_eng) {
			store.set(this.LOCALSTORE_CLUB_MEMBER_IDS + "_" + club_eng, val);
		},
		getLocalClubMemberIds: function(club_eng) {
			return store.get(this.LOCALSTORE_CLUB_MEMBER_IDS + "_" + club_eng);
		},
		setLocalActivityDate: function(val, club_eng) {
			store.set(this.LOCALSTORE_ACT_START_DATE + "_" + club_eng, val);
		},
		getLocalActivityDate: function(club_eng) {
			return store.get(this.LOCALSTORE_ACT_START_DATE + "_" + club_eng);
		},
		setLocalActivityIds: function(val, club_eng) {
			store.set(this.LOCALSTORE_ACT_IDS + "_" + club_eng, val);
		},
		getLocalActivityIds: function(club_eng) {
			return store.get(this.LOCALSTORE_ACT_IDS + "_" + club_eng);
		},
		createFileUploadScript: function(callBack) {
			var deferred = $q.defer();
			$.ajax({
				url: "http://" + document.domain + "/jq_fileupload/jquery.fileupload-all.js",
				dataType: "script",
				cache: true,
				success: function() {
					$.ajax({
						url: "http://" + document.domain + "/jq_fileupload/jquery.fileupload-image-all.js",
						dataType: "script",
						cache: true,
						success: function() {
							if ($.isFunction(callBack)) {
								callBack();
							}
						}
					});
				}
			})
			return deferred.promise;
		},
		createQQMapScript: function() {

			if (!$rootScope.initQQMap) {
				var deferred = $q.defer();
				$.ajax({
					url: "http://map.qq.com/api/js?v=2.exp&key=C36BZ-WNZRV-6EVPM-UD4MA-JDH45-GAB7P&callback=init",
					dataType: "script",
					cache: true,
					success: function() {
						$rootScope.initQQMap = true;
					}
				});
				return deferred.promise;
			}
		},
		loadChartScript: function(callBack) {
			var deferred = $q.defer();
			$.ajax({
				url: "http://echarts.baidu.com/build/dist/echarts.js",
				dataType: "script",
				cache: true,
				success: function() {
					require.config({
						paths: {
							echarts: 'http://echarts.baidu.com/build/dist'
						}
					});
					if ($.isFunction(callBack)) {
						callBack();
					}
				}
			});
			return deferred.promise;
		},
		inArray: function(v, array) {
			var r = -1;
			if ($.isArray(array)) {
				$.each(array, function(i, n) {
					if (v == n) {
						r = i;
						return false;
					}
				});
			}
			return r;
		},
		uploadFromSDK: function(serverid, type) {
			var deferred = $q.defer();
			$.ajax({
				url: "/upload/wxsdkupload",
				data: {server_ids: serverid, type: type},
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


wechatApp.controller('mainController', function($scope, $routeParams, ClubService, WxService, UtilService, MapService, MarketingService, $rootScope, $interval, $timeout) {
	$scope.share = function() {
		$("#shareModal").show();
		hideLoading();
	}

	$scope.chooseImage = function(count, isPre, callBack) {
		var id = event.target.id;
		WxService.chooseImg(count, function(localids) {
			showLoading();
			$("#" + id + "_value").val("");
			var wxSuccess = function(serverid) {
				if (localids.length > 0) {
					WxService.uploadImg(localids, wxSuccess, 0);
				} else {
					var serverids = WxService.wx_serverids.join(",");
					UtilService.uploadFromSDK(serverids, id).then(function(data) {
						if (data.status == 1) {
							$.each(data.images, function(x, y) {
								var obj = {pre: WxService.wx_localids[x], image: y};
								WxService.upload_images.push(obj);
							});
							if ($("#" + id).attr("check-type") != undefined) {
								$("#" + id).attr("check-type", " ");
								$("#" + id).trigger("change");
							}
							if (isPre) {
								var source = $("#image-pre-template").html();
								var template = Handlebars.compile(source);
								//$("#"+file).next().remove();
								if ($("#" + id).parent().children(".upload-pre").length == 0) {
									$("#" + id).parent().append(template({images: WxService.upload_images}));
								} else {
									$(template({images: WxService.upload_images})).replaceAll($("#" + id).parent().children(".upload-pre"));
								}
							}
							var values = "";
							$.each(WxService.upload_images, function(i, n) {
								values += "," + n.image;
							});
							if (values != "") {
								$("#" + id + "_value").val(values.substring(1));
							}
							hideLoading();
							if ($.isFunction(callBack)) {
								callBack(WxService.upload_images, id);
							}
						} else {
							UtilService.showMessage("文件上传错误，请稍后重试!", null, null);
						}
					});
				}
			}
			$timeout(WxService.uploadImg(localids, wxSuccess, 0), 100);
		});
	}

	$scope.refreshWx = function() {
		store.remove("openid");
		$rootScope.openid = null;
		window.location.reload();
	}

	$scope.selectClick = function(data) {
		$("#" + data.parent).val(data.text);
		$("#" + data.collapse).collapse('hide');
		$("#" + data.valueField).val(data.value);
	}

	$scope.actInfo = function(act) {
//			if($rootScope.club){
//				var act_start_time_ids = UtilService.getLocalActivityIds($rootScope.club.club_eng);
//				if(act.isnew){
//					var index = UtilService.inArray(act.act_id,$scope.act_start_time_ids);
//					if(index!=-1){
//						act_start_time_ids.splice(index,1);
//					}
//					UtilService.setLocalActivityIds(act_start_time_ids,$rootScope.club.club_eng);
//					UtilService.setLocalActivityDate(act.act_start_time,$rootScope.club.club_eng);
//					act.isnew=false;
//				}
//			}
		if ($routeParams.ticket) {
			location.href = "http://" + document.domain + "/wechat/#/activity/info?act_id=" + act.act_id + "&ticket=" + $.trim($routeParams.ticket);
		} else {
			location.href = "http://" + document.domain + "/wechat/#/activity/info?act_id=" + act.act_id;
		}

	}

	$scope.$on("initMapFinished", function(e, data) {
		$scope.map = data.map;
		$scope.marker = data.marker;
		$scope.info = data.info;
		$scope.label = data.label;
	});

	$scope.search = function() {
		MapService.search($scope.map, function(latLng) {
			lat = latLng.lat;
			lng = latLng.lng;
			MapService.setMapMarker($scope.marker, lat, lng, $scope.info);
			MapService.geocoder(function(address) {
				$("#locationLabel").text(address);
				$("#location").val(address);
				$("#lat").val(lat);
				$("#lng").val(lng);
				MapService.setLabel($scope.label, $scope.marker, address);
			}, latLng.lat, latLng.lng)
		});
	}

	$scope.getCode = function() {
		$("#pro_alert").hide();
		var cell = UtilService.getProModalValue();
		if (!$rootScope.REG_CELL.test(cell)) {
			UtilService.showPromptError("请输入正确的手机号");
			return false;
		}
		$("#bind_code_modal").val('');
		UtilService.getCode(cell, "code_modal_btn").then(function(data) {
			if (data.status == 0) {
				$rootScope.btnReset("code_modal_btn");
				UtilService.showPromptError(data.msg);
			} else {
				$rootScope.btnCountdown("code_modal_btn", 30);
			}
		});
	}

	$scope.proInputChange = function() {
		var cell = UtilService.getProModalValue();
		if ($rootScope.need_bind_code) {
			if ($rootScope.modalInputValue != UtilService.getProModalValue()) {
				$("#bind_code_group").show();
			}
		}
	}

	$rootScope.btnCountdown = function(btn, time) {
		var btn = $("#" + btn);
		btn.attr("disabled", true);
		var interval = $interval(function() {
			btn.text(time);
			time--;
			if (time == 0) {
				$interval.cancel(interval);
				btn.html($rootScope.stateBtnText);
				btn.removeAttr("disabled")
			}
		}, 1000);
	}

	$rootScope.btnReset = function(btn) {
		var btn = $("#" + btn);
		btn.html($rootScope.stateBtnText);
		btn.removeAttr("disabled")
	}

	$rootScope.btnLoading = function(btn) {
		var btn = $("#" + btn);
		btn.attr("disabled", true);
		$rootScope.stateBtnText = btn.html();
		btn.html("<i class='fa fa-spinner fa-spin'></i>");
		setTimeout(function() {
			btn.html($rootScope.stateBtnText);
			btn.removeAttr("disabled")
		}, 31000);
	}


	$scope.myLocation = function() {
		WxService.getLocation(function(lat, lng) {
			$scope.map.panTo(new qq.maps.LatLng(lat, lng));
			MapService.setMapMarker($scope.marker, lat, lng, $scope.info);
			MapService.geocoder(function(address) {
				$("#locationLabel").text(address);
				$("#location").val(address);
				$("#lat").val(lat);
				$("#lng").val(lng);
				MapService.setLabel($scope.label, $scope.marker, address);
			}, lat, lng);
		});
	}
});

wechatApp.config(function($routeProvider, $locationProvider) {

});
