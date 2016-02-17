'use strict';

angular.module('myApp.user.trackerlogs', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/user/:crewId/:id/trackerlogs', {
    templateUrl: 'user/trackerlogs.html',
    controller: 'UserTrackerLogsCtrl'
    // css: 'css/user/user.css'
  });
}])

.controller('UserTrackerLogsCtrl',  ['$scope', '$routeParams', '$http', '$q','Config', 'UserService', 'TrackerService', 'CrewService', 'TimeService', 'WxAPI', '$timeout',
 function($scope, $routeParams, $http, $q, Config, UserService, TrackerService, CrewService, TimeService, WxAPI, $timeout) {

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
        angular.element($event.currentTarget).parents('article').addClass('animated flipOutX');
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


  $scope.page = 1;
  $scope.pageSize = 2;
  $scope.hasMore = true;
  $scope.trackers = [];
  $scope.userLoaded = false;
  $scope.pageLoading = true;

  $scope.loadMore = function() {
    if (!$scope.userLoaded) {
      if($scope.loadTimer) {$timeout.cancel($scope.loadTimer);}
      $scope.loadTimer = $timeout(function() {
        $scope.loadMore();
      }, 100);
    };
    if (!$scope.user || !$scope.hasMore) {return;};
    $timeout.cancel($scope.loadTimer);
    $scope.isLoading = true;
    TrackerService.getTracksByUser({
      userid: $routeParams.id,
      page: $scope.page,
      per: $scope.pageSize,
      successCallback: function(res) {
        var logs = res.data;
        if (logs.length > 0) {
          $scope.hasMore = true;
          $scope.page += 1;
          for(var i = 0, max = logs.length; i < max; i++){
            var average = logs[i].duration / logs[i].distance;
            logs[i].duration = TimeService.formatSeconds({seconds: logs[i].duration, isZh: true});
            logs[i].average = TimeService.formatSeconds({seconds: average, isZh: true});
          }
          $scope.trackers = $scope.trackers.concat(logs);
        } else {
          $scope.hasMore = false;
        }
        $scope.pageLoading = false;
        $scope.isLoading = false;
      },
      errorCallback: function() {
        $scope.isLoading = false;
      }
    });
  };

  $scope.init = function() {
    UserService.getUser({
      userid: $routeParams.id,
      successCallback: function(res) {
        var user = res.data;
        $scope.user = user;
        $scope.userLoaded = true;
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
            });
          }
        });

        // 获取摘要
        UserService.getSummary({
          userid: user.id,
          successCallback: function(res) {
            $scope.summary = res.data;
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
        console.log('error');
      }
    });
  };


}]);