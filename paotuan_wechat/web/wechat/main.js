var wechat_app_version = 9.4;
$script('http://libs.baidu.com/jquery/2.1.1/jquery.min.js', function() {
	$script(["http://apps.bdimg.com/libs/bootstrap/3.3.4/js/bootstrap.min.js",
		"/slidebars/slidebars.min.js",
		"/js/wechat.min.js?v=" + wechat_app_version,
		'http://apps.bdimg.com/libs/angular.js/1.3.9/angular.min.js',
		"/js/store.min.js",
		"http://apps.bdimg.com/libs/handlebars.js/2.0.0-alpha.4/handlebars.min.js",
		"http://res.wx.qq.com/open/js/jweixin-1.0.0.js"
	], function() {
		var mySlidebars = new $.slidebars();
		$(document).on("click", "a[data-type='slidebar']", function() {
			mySlidebars.close();
		});
		$script([
			'http://apps.bdimg.com/libs/angular.js/1.3.9/angular-route.min.js',
			'/angular/ng-infinite-scroll.min.js',
			'clubs/clubs.js?v=' + wechat_app_version,
			"activity/activity.js?v=" + wechat_app_version,
			"runner/runner.js?v=" + wechat_app_version,
			"marketing/club/club.min.js?v=" + wechat_app_version,
			"analysis/analysis.min.js?v=" + wechat_app_version,
			"race/race.min.js?v=" + wechat_app_version,
			"index.min.js?v=" + wechat_app_version
		], function() {
			$script("directive.min.js?v=" + wechat_app_version, function() {
				angular.bootstrap(document, ["wechatApp"]);
			});
		});
	});

});
