'use strict';

angular.module('myApp.user', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/user/:crewId/:id', {
    templateUrl: 'user/user.html',
    controller: 'UserCtrl'
    // css: 'css/user/user.css'
  });
}])

.controller('UserCtrl',  ['$scope', '$routeParams', '$http', '$q','Config', 'UserService', 'TrackerService', 'CrewService', 'TimeService', 'FeedService', 'WxAPI', '$rootScope', '$location', 'AUTH_EVENTS', function($scope, $routeParams, $http, $q, Config, UserService, TrackerService, CrewService, TimeService, FeedService, WxAPI, $rootScope, $location, AUTH_EVENTS) {

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

  var shared_to_feed = function(site) {
    FeedService.share({
      site: site,
      target_id: $scope.user.id,
      target_type: 'users',
      session_token: $rootScope.loggedInUser ? $rootScope.loggedInUser.session_token : '',
      successCallback: function(res) {
      },
      errorCallback: function(res) {}
    });
  }

  var m = true;
  $scope.showMore = function(id){
    $scope.hideMore();
    m = false;
    setTimeout(function(){m=true;},500);

    if(angular.element('#more_content_'+id).is(':hidden')){
      angular.element('#more_content_'+id).animate({
        width : "show",
        paddingLeft : "show",
        paddingRight : "show",
        marginLeft : "show",
        marginRight : "show"
      }, 300);
    } else {
      angular.element('#more_content_'+id).animate({
        width : "hide",
        paddingLeft : "hide",
        paddingRight : "hide",
        marginLeft : "hide",
        marginRight : "hide"
      }, 300);
    }
  }
  $scope.hideMore = function(){
    if(m){
      angular.element('.more .more-content').animate({
        width : "hide",
        paddingLeft : "hide",
        paddingRight : "hide",
        marginLeft : "hide",
        marginRight : "hide"
      }, 300);
    }
  }

  $scope.deleteTracker = function(id, $event) {
    TrackerService.deleteTracker({
      id: id,
      successCallback: function() {
        console.log('delete success');
        angular.element($event.currentTarget).parents('article').fadeOut();
        //重新获得summary
        UserService.getSummary({
          userid: $scope.user.id,
          successCallback: function(res) {
            $scope.summary = res.data;
          },
          errorCallback: function() {

          }
        });
      },
      errorCallback: function() {
        alert('删除失败');
      }
    });
  };

  UserService.getUser({
    userid: $routeParams.id,
    successCallback: function(res) {
      var user = res.data;
      $scope.user = user;

      // 获取打卡记录
      TrackerService.getTracksByUser({
        userid: user.id,
        page: 1,
        per: 5,
        successCallback: function(res) {
          var logs = res.data;
          for(var i = 0, max = logs.length; i < max; i++){
            var average = logs[i].duration / logs[i].distance;
            logs[i].duration = TimeService.formatSeconds({seconds: logs[i].duration, isZh: false});
            logs[i].average = TimeService.formatSeconds({seconds: average, isZh: false});
          }

          $scope.trackers = logs;
          if (logs.logs == 0) {
            $scope.hasNoTracker = true;
          }

          WxAPI.config({
            debug: false,
            successCallback: function() {
              wx.ready(function() {
                // 图片预览
                $scope.previewImage = function(index, current) {
                  wx.previewImage({
                    current: current,
                    urls: $scope.trackers[index].photos
                  });
                };

                $scope.previewAvatar = function() {
                  wx.previewImage({
                    current: user.avatar,
                    urls: [user.avatar]
                  });
                };
              });
            }
          });
        },
        errorCallback: function() {
        }
      });
      // 获取摘要
      UserService.getSummary({
        userid: user.id,
        successCallback: function(res) {
          $scope.summary = res.data;
          WxAPI.config({
            debug: false,
            successCallback: function() {
              wx.ready(function() {
                var shareTitle = user.name + '的跑步记录';
                var shareDesc = '打卡'+ $scope.summary.times + '次，共计' + $scope.summary.kms + '公里。\n\r来自 ' + ($rootScope.currentCrew ? $rootScope.currentCrew.name : Config.SITE_NAME);
                var imageUrl = user.avatar;
                // 分享到朋友圈
                wx.onMenuShareTimeline({
                  title: shareTitle, // 分享标题
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
                  },
                  cancel: function () {
                    // 用户取消分享后执行的回调函数
                    shared_to_feed('qq');
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
        },
        errorCallback: function() {

        }
      });
      // 获取所在跑团
      CrewService.getCrewsByUser({
        userid: user.id,
        successCallback: function(res) {
          $scope.crews = res.data;
        },
        errorCallback: function() {

        }
      });
    },
    errorCallback: function() {
      $rootScope.$broadcast(AUTH_EVENTS.needLogin);
    }
  });
}]);