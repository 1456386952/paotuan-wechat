'use strict';

angular.module('myApp.team.users', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/team/:crewId/users', {
    templateUrl: 'team/users.html',
    controller: 'UsersCtrl'
    // css: 'css/team/team.css'
  });
}])

.controller('UsersCtrl', ['$scope', '$routeParams', 'CrewService', 'WxAPI', '$location', 'TimeService', '$rootScope', 'Config', '$timeout', function($scope, $routeParams, CrewService, WxAPI, $location, TimeService, $rootScope, Config, $timeout) {
  $scope.crewId = $routeParams.id;
  if ($routeParams.crewId) {
    $rootScope.domain = $routeParams.crewId;
  };

  $scope.page = 1;
  $scope.pageSize = 20;
  $scope.hasMore = true;
  $scope.users = [];
  $scope.isLoading = false;
  $scope.isCrewLoaded = false;
  $scope.pageLoading = true;

  $scope.init = function() {
    // url中指定的跑团
    if (!!$routeParams.id) {
      CrewService.getCrewByDomain({
        domain: $routeParams.id,
        successCallback: function(crew) {
          $scope.crew = crew;
          getCrewInfo(crew);
          $scope.isCrewLoaded = true;
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
            $scope.isCrewLoaded = true;
            //$scope.loadMore();
          },
          errorCallback: function() {

          }
        });
      };
    }
  };

  // 加载更多
  $scope.loadMore = function() {
    if (!$scope.isCrewLoaded) {
      if($scope.loadTimer) {$timeout.cancel($scope.loadTimer);}
      $scope.loadTimer = $timeout(function() {
        $scope.loadMore();
      }, 10);
      return;
    } else {
      $timeout.cancel($scope.loadTimer);
    }
    $timeout.cancel($scope.loadTimer);
    if (!$scope.isCrewLoaded || !$scope.hasMore) {return;};
    delete $scope.loadTimer;
    $scope.isLoading = true;
    CrewService.getUsers({
      crew: $scope.crew,
      page: $scope.page,
      per: $scope.pageSize,
      successCallback: function(res) {
        var users = res.data;
        if(users.length > 0) {
          $scope.page += 1;
          $scope.hasMore = true;
          $scope.isLoading = false;
          $scope.users = $scope.users.concat(users);
        } else {
          $scope.hasMore = false;
          $scope.isLoading = false;
        }
        $scope.pageLoading = false;
      },
      errorCallback: function(res) {
        $scope.isLoading = false;
      }
    });
  }

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