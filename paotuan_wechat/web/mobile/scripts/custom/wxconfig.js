/*
 * 注意：
 * 1. 所有的JS接口只能在公众号绑定的域名下调用，公众号开发者需要先登录微信公众平台进入“公众号设置”的“功能设置”里填写“JS接口安全域名”。
 * 2. 如果发现在 Android 不能分享自定义内容，请到官网下载最新的包覆盖安装，Android 自定义分享接口需升级至 6.0.2.58 版本及以上。
 * 3. 常见问题及完整 JS-SDK 文档地址：http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
 *
 * 开发中遇到问题详见文档“附录5-常见错误及解决办法”解决，如仍未能解决可通过以下渠道反馈：
 * 邮箱地址：weixin-open@qq.com
 * 邮件主题：【微信JS-SDK反馈】具体问题
 * 邮件内容说明：用简明的语言描述问题所在，并交代清楚遇到该问题的场景，可附上截屏图片，微信团队会尽快处理你的反馈。
 */
/*wx.config({
    debug: false,
    appId: 'wx1b5e279f9b984e1a',
    timestamp: '1439177211',
    nonceStr: 'ioart2015',
    signature: 'a8207e3a337be4149d5ec241a06dbbdaa323406c',
    jsApiList: [
        'checkJsApi',
        'scanQRCode'
    ]
});*/

/**
 * 微信分享设置.
 * @param title(String) .
 * @param desc(String) .
 * @param link(String) .
 * @param imgUrl(String) .
 * @return (Boolean).
 */
function sns(title, desc, link, imgUrl) {
	wx.ready(function () {

		// 分享到朋友圈
		wx.onMenuShareTimeline({
			title: title, // 分享标题
			link: link, // 分享链接
			imgUrl: imgUrl, // 分享图标
			success: function () {
				// 用户确认分享后执行的回调函数
			},
			cancel: function () {
				// 用户取消分享后执行的回调函数
			}
		});

		// 分享给朋友
		wx.onMenuShareAppMessage({
			title: title, // 分享标题
			desc: desc, // 分享描述
			link: link, // 分享链接
			imgUrl: imgUrl, // 分享图标
			type: '', // 分享类型,music、video或link，不填默认为link
			dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
			success: function () {
				// 用户确认分享后执行的回调函数
			},
			cancel: function () {
				// 用户取消分享后执行的回调函数
			}
		});

		// 分享到QQ
		wx.onMenuShareQQ({
			title: title, // 分享标题
			desc: desc, // 分享描述
			link: link, // 分享链接
			imgUrl: imgUrl, // 分享图标
			success: function () {
			   // 用户确认分享后执行的回调函数
			},
			cancel: function () {
			   // 用户取消分享后执行的回调函数
			}
		});

		// 分享到腾讯微博
		wx.onMenuShareWeibo({
			title: title, // 分享标题
			desc: desc, // 分享描述
			link: link, // 分享链接
			imgUrl: imgUrl, // 分享图标
			success: function () {
			   // 用户确认分享后执行的回调函数
			},
			cancel: function () {
				// 用户取消分享后执行的回调函数
			}
		});

		// 分享到QQ空间
		wx.onMenuShareQZone({
		    title: title, // 分享标题
		    desc: desc, // 分享描述
		    link: link, // 分享链接
		    imgUrl: imgUrl, // 分享图标
		    success: function () { 
		       // 用户确认分享后执行的回调函数
		    },
		    cancel: function () { 
		        // 用户取消分享后执行的回调函数
		    }
		});

	});

	return true;
}


// 分享到朋友圈
function shareFriends(title, desc, link, imgUrl) {
	wx.ready(function () {
		wx.onMenuShareTimeline({
			title: title, // 分享标题
			link: link, // 分享链接
			imgUrl: imgUrl, // 分享图标
			success: function () {
				// 用户确认分享后执行的回调函数
			},
			cancel: function () {
				// 用户取消分享后执行的回调函数
			}
		});
	});

	return true;
}


// 分享给朋友
function shareWX(title, desc, link, imgUrl) {
	wx.ready(function () {
		wx.onMenuShareAppMessage({
			title: title, // 分享标题
			desc: desc, // 分享描述
			link: link, // 分享链接
			imgUrl: imgUrl, // 分享图标
			type: '', // 分享类型,music、video或link，不填默认为link
			dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
			success: function () {
				// 用户确认分享后执行的回调函数
			},
			cancel: function () {
				// 用户取消分享后执行的回调函数
			}
		});
	});

	return true;
}


// 分享到QQ
function shareQQ(title, desc, link, imgUrl) {
	wx.ready(function () {
		wx.onMenuShareQQ({
			title: title, // 分享标题
			desc: desc, // 分享描述
			link: link, // 分享链接
			imgUrl: imgUrl, // 分享图标
			success: function () {
			   // 用户确认分享后执行的回调函数
			},
			cancel: function () {
			   // 用户取消分享后执行的回调函数
			}
		});
	});

	return true;
}


// 分享到腾讯微博
function shareWeibo(title, desc, link, imgUrl) {
	wx.ready(function () {
		wx.onMenuShareWeibo({
			title: title, // 分享标题
			desc: desc, // 分享描述
			link: link, // 分享链接
			imgUrl: imgUrl, // 分享图标
			success: function () {
			   // 用户确认分享后执行的回调函数
			},
			cancel: function () {
				// 用户取消分享后执行的回调函数
			}
		});
	});

	return true;
}


// 分享到QQ空间
function shareQzone(title, desc, link, imgUrl) {
	wx.ready(function () {
		wx.onMenuShareQZone({
		    title: title, // 分享标题
		    desc: desc, // 分享描述
		    link: link, // 分享链接
		    imgUrl: imgUrl, // 分享图标
		    success: function () { 
		       // 用户确认分享后执行的回调函数
		    },
		    cancel: function () { 
		        // 用户取消分享后执行的回调函数
		    }
		});
	});

	return true;
}


// 判断是否微信
function isWeiXin(){
	var ua = window.navigator.userAgent.toLowerCase();
	if(ua.match(/MicroMessenger/i) == 'micromessenger'){
		return true;
	}else{
		return false;
	}
}

// 设置微信接口
;(function setConfig() {

	if( !isWeiXin() ) {
		return;
	};

	// 获取当前网址
	var url = location.href.split('#')[0];
	var jsInfo = {
		'timestamp': '',
		'nonceStr': '',
		'signature': ''
	}

	// 获取signature
	$.post(
		'http://wechat.runningtogether.net/wechat/jsapiparams', {
			url: url
		},
		function(data) {

			jsInfo.timestamp = data.timestamp;
			jsInfo.nonceStr  = data.nonceStr;
			jsInfo.signature = data.signature;

			wx.config({
				debug: false,
				appId: 'wxe380ca3504f26643',
				timestamp: jsInfo.timestamp,
				nonceStr: jsInfo.nonceStr,
				signature: jsInfo.signature,
				jsApiList: [
                	'checkJsApi',
                	'onMenuShareTimeline',
                	'onMenuShareAppMessage',
                	'onMenuShareQQ',
                	'onMenuShareWeibo',
                	'onMenuShareQZone',
                	'hideMenuItems',
                	'showMenuItems',
                	'hideAllNonBaseMenuItem',
                	'showAllNonBaseMenuItem',
                	'translateVoice',
                	'startRecord',
                	'stopRecord',
                	'onRecordEnd',
                	'playVoice',
                	'pauseVoice',
                	'stopVoice',
                	'uploadVoice',
                	'downloadVoice',
                	'chooseImage',
                	'previewImage',
                	'uploadImage',
                	'downloadImage',
                	'getNetworkType',
                	'openLocation',
                	'getLocation',
                	'hideOptionMenu',
                	'showOptionMenu',
                	'closeWindow',
                	'scanQRCode',
                	'chooseWXPay',
                	'openProductSpecificView',
                	'addCard',
                	'chooseCard',
                	'openCard'
				]
			});

		}
	);

	// 调用扫描方法
	// $(".menu-scan").show().off().on("click", function() {
	// 	alert('sss')
	// 	// wx.scanQRCode();
	// 	wx.scanQRCode({
	// 		needResult: 1,
	// 		desc: 'scanQRCode desc',
	// 		success: function (res) {
	// 		  location.href = res.resultStr;
	// 		}
	// 	});
	// });
})()