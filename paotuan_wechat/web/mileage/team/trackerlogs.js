'use strict';

angular.module('myApp.team.trackerlogs', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/team/:crewId/trackerlogs', {
    templateUrl: 'team/trackerlogs.html',
    controller: 'TrackerLogsCtrl'
    // css: 'css/team/team.css'
  });
}])

.controller('TrackerLogsCtrl', ['$scope', '$routeParams', '$window', 'CrewService', 'WxAPI', '$location', 'TimeService', '$rootScope', 'Config', '$timeout', function($scope, $routeParams, $window, CrewService, WxAPI, $location, TimeService, $rootScope, Config, $timeout) {
  $scope.crewId = $routeParams.id;
  if ($routeParams.crewId) {
    $rootScope.domain = $routeParams.crewId;
  };

  $scope.page = 1;
  $scope.pageSize = 5;
  $scope.hasMore = true;
  $scope.logs = [];
  $scope.crewLoaded = false;
  $scope.pageLoading = true;

  $scope.init = function() {
    // url中指定的跑团
    if (!!$routeParams.id) {
      CrewService.getCrewByDomain({
        domain: $routeParams.id,
        successCallback: function(crew) {
          $scope.crew = crew;
          //$scope.crewLoaded = true;
          getCrewInfo(crew);
          //$scope.loadMore();
        },
        errorCallback: function() {

        }
      });
    // 当前跑团
    } else {
      if (!!$routeParams.crewId) {
        CrewService.getCrewByDomain({
          domain: $routeParams.crewId,
          successCallback: function(crew) {
            $rootScope.currentCrew = crew;
            $scope.crew = crew;
            getCrewInfo(crew);
            $scope.loadMore();
          },
          errorCallback: function() {

          }
        });
      };
    }
  };

  // 加载更多
  $scope.loadMore = function() {
    //if (!$scope.crewLoaded) {
    //  $timeout(function() {
    //    $scope.loadMore();
    //  }, 100);
    //};
    if (!$scope.crew || !$scope.hasMore) {return ;};
    $scope.isLoading = true;
    CrewService.getTracks({
      crew: $scope.crew,
      page: $scope.page,
      per: $scope.pageSize,
      successCallback: function(res) {
        var logs = res.data;
        if(logs.length > 0) {
          $scope.page += 1;
          $scope.hasMore = true;
          for(var index in logs) {
            var log = logs[index];
            var average = log.duration / log.distance;
            log.duration = TimeService.formatSeconds({seconds:log.duration, isZh: false});
            log.average = TimeService.formatSeconds({seconds:average, isZh: false});
          }
          $scope.logs = $scope.logs.concat(logs);
          $scope.isLoading = false;
        } else {
          $scope.hasMore = false;
          $scope.isLoading = false;
        }
        $scope.pageLoading = false;
      },
      errorCallback: function(res) {}
    });
  };

  var getCrewInfo = function(crew) {
    var shareTitle = crew.desc + '\r\n来自 ' + ($rootScope.currentCrew ? $rootScope.currentCrew.name : Config.SITE_NAME);
    WxAPI.config({
      debug: false,
      successCallback: function() {
        wx.ready(function() {
          // 分享到朋友圈
          wx.onMenuShareTimeline({
            title: crew.name,
            link: $location.href, // 分享链接
            imgUrl: crew.logo, // 分享图标
            success: function () { 
              //用户确认分享后执行的回调函数
            },
            cancel: function () { 
              //用户取消分享后执行的回调函数
            }
          });

          // 分享给好友
          wx.onMenuShareAppMessage({
            title: crew.name, // 分享标题
            desc: shareTitle, // 分享描述
            link: $location.href, // 分享链接
            imgUrl: crew.logo, // 分享图标
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () { 
              // 用户确认分享后执行的回调函数
            },
            cancel: function () { 
              // 用户取消分享后执行的回调函数
            }
          });

          // 分享到qq
          wx.onMenuShareQQ({
            title: crew.name, // 分享标题
            desc: shareTitle, // 分享描述
            link: $location.href, // 分享链接
            imgUrl: crew.logo, // 分享图标
            success: function () { 
              // 用户确认分享后执行的回调函数
            },
            cancel: function () { 
              // 用户取消分享后执行的回调函数
            }
          });

          // 分享到腾讯微博
          wx.onMenuShareWeibo({
            title: crew.name, // 分享标题
            desc: shareTitle, // 分享描述
            link: $location.href, // 分享链接
            imgUrl: crew.logo, // 分享图标
            success: function () { 
              // 用户确认分享后执行的回调函数
            },
            cancel: function () { 
              // 用户取消分享后执行的回调函数
            }
          });
        });
      }
    });
  };




}]);