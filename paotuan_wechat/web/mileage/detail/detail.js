'use strict';

angular.module('myApp.detail', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/detail/:crewId/:trackerId', {
    templateUrl: 'detail/detail.html',
    controller: 'DetailCtrl'
    // css: 'css/detail/detail.css'
  });
}])

.controller('DetailCtrl',  ['$scope', '$routeParams', 'Config', 'TimeService', 'WxAPI', '$location', 'TrackerService', 'CrewService', 'FeedService', '$rootScope', '$filter', '$compile', function($scope, $routeParams, Config, TimeService, WxAPI, $location, TrackerService, CrewService, FeedService, $rootScope, $filter, $compile) {

  var trackerId = $routeParams.trackerId;
  var domain = $routeParams.crewId;

  CrewService.getCrewByDomain({
    domain: domain,
    successCallback: function(crew) {
      $rootScope.currentCrew = crew;
      $rootScope.domain = crew.domain;
    },
    errorCallback: function() {
      alert('获取跑团信息出错，请稍候再试');
    }
  });

  // 获取打卡信息
  TrackerService.getTracks({
    trackerId: trackerId,
    successCallback: function(res){
      // 分享提示
      setTimeout(function(){
        angular.element('.share').removeClass('fadeInDown').addClass('fadeOutUp')
      }, 5000);

      var average = res.data.duration / res.data.distance;
      $scope.detail = res.data;
      $scope.detail.duration = TimeService.formatSeconds({seconds: res.data.duration, isZh: false});
      $scope.detail.average = TimeService.formatSeconds({seconds: average, isZh: false});

      $scope.favors = res.data.favors;
      $scope.favorsIsMe = false;
      for(var i = 0; i < $scope.favors.length; i++){
        if($scope.favors[i].user.id == $rootScope.loggedInUser.id){
          $scope.favorsIsMe = true;
        }
      }
      wxShare();
    },
    errorCallback: function(){}
  });

  $scope.clickFavors = function(){
    if(!$scope.favorsIsMe){
      TrackerService.addFavors({
        trackerId: trackerId,
        session_token: $rootScope.loggedInUser ? $rootScope.loggedInUser.session_token : '',
        successCallback: function(res){
          if(res.success){
            var el = $compile('<li favors-id="'+$rootScope.loggedInUser.id+'" class="animated bounceIn"><a href="#/user/'+$rootScope.currentCrew.domain+'/'+$rootScope.loggedInUser.id+'"><pt-avatar avatar="'+$rootScope.loggedInUser.avatar+'" thumb="60"></pt-avatar></a></li>')($scope);
            angular.element('#favors-list').append(el);
            angular.element('#favors-number').html(parseInt(angular.element('#favors-number').html())+1);
            $scope.favorsIsMe = true;
          }
        },
        errorCallback: function(){}
      });
    } else {
      TrackerService.deleteFavors({
        trackerId: trackerId,
        successCallback: function(res){
          if(res.success){
            angular.element('#favors-list li[favors-id='+ $rootScope.loggedInUser.id +']').remove();
            angular.element('#favors-number').html(parseInt(angular.element('#favors-number').html())-1);
            $scope.favorsIsMe = false;
          }
          $scope.favorsIsMe = false;
        },
        errorCallback: function(){}
      });
    }
  }

  var shared_to_feed = function(site) {
    FeedService.share({
      site: site,
      target_id: $scope.detail.id,
      target_type: 'trackerlogs',
      session_token: $rootScope.loggedInUser ? $rootScope.loggedInUser.session_token : '',
      successCallback: function(res) {
      },
      errorCallback: function(res) {}
    });
  }

  var wxShare = function(){
    var shareDesc = '距离: ' + $scope.detail.distance +'KM\r\n用时: ' + $scope.detail.duration + '\r\n配速: ' + $scope.detail.average + '/KM\r\n' + ($rootScope.currentCrew ? $rootScope.currentCrew.name : Config.SITE_NAME);
    var shareTitle = $scope.detail.user.name + '的跑步打卡';
    var link = Config.ROOT + '/#/detail/' + $rootScope.domain + '/' + $scope.detail.id;

    var imageUrl = $scope.detail.user.avatar;
    if ($scope.detail.photos.length > 0) {
      if($scope.detail.photos[0].indexOf('upaiyun') > 0) {
        imageUrl = $scope.detail.photos[0] + '!thumb120';
      } else {
        imageUrl = $scope.detail.photos[0];
      }
    };

    WxAPI.config({
      debug: false,
      successCallback: function() {
        wx.ready(function() {
          // 图片预览
          $scope.previewImage = function(current) {
            wx.previewImage({
              current: current,
              urls: $scope.detail.photos
            });
          };
          // 分享到朋友圈
          wx.onMenuShareTimeline({
            title: '跑步打卡：' + $scope.detail.distance + 'KM，用时' + $scope.detail.duration + '，配速' + $scope.detail.average + '/KM。来自' + ($rootScope.currentCrew ? $rootScope.currentCrew.name : Config.SITE_NAME), // 分享标题
            link: $location.href, // 分享链接
            imgUrl: imageUrl, // 分享图标
            success: function () {
              //用户确认分享后执行的回调函数
              shared_to_feed('timeline');
            },
            cancel: function () {
              //用户取消分享后执行的回调函数
            }
          });

          // 分享给好友
          wx.onMenuShareAppMessage({
            title: shareTitle, // 分享标题
            desc: shareDesc, // 分享描述
            link: $location.href, // 分享链接
            imgUrl: imageUrl, // 分享图标
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
              // 用户确认分享后执行的回调函数
              shared_to_feed('friend');
            },
            cancel: function () {
              // 用户取消分享后执行的回调函数
            }
          });

          // 分享到qq
          wx.onMenuShareQQ({
            title: shareTitle, // 分享标题
            desc: shareDesc, // 分享描述
            link: $location.href, // 分享链接
            imgUrl: imageUrl, // 分享图标
            success: function () {
              // 用户确认分享后执行的回调函数
              shared_to_feed('qq');
            },
            cancel: function () {
              // 用户取消分享后执行的回调函数
            }
          });

          // 分享到腾讯微博
          wx.onMenuShareWeibo({
            title: shareTitle, // 分享标题
            desc: shareDesc, // 分享描述
            link: $location.href, // 分享链接
            imgUrl: imageUrl, // 分享图标
            success: function () {
              // 用户确认分享后执行的回调函数
              shared_to_feed('weibo');
            },
            cancel: function () {
              // 用户取消分享后执行的回调函数
            }
          });
        });
      }
    });
  }

}]);