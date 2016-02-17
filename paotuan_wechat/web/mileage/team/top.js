'use strict';

angular.module('myApp.team.top', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/team/:crewId/top/:specId', {
    templateUrl: 'team/top.html',
    controller: 'TopCtrl'
    // css: 'css/team/team.css'
  });
}])

.controller('TopCtrl', ['$scope', '$routeParams', '$rootScope', 'CrewService', 'WxAPI', '$location', function($scope, $routeParams, $rootScope, CrewService, WxAPI, $location) {
  $scope.specId = $routeParams.specId;
  $scope.crewId = $routeParams.id;
  if ($routeParams.crewId) {
    $rootScope.domain = $routeParams.crewId;
  };

  $scope.pageLoading = true;
  $scope.init = function() {
    // url中指定的跑团
    if (!!$routeParams.id) {
      CrewService.getCrewByDomain({
        domain: $routeParams.id,
        successCallback: function(crew) {
          $scope.crew = crew;
          getTop(crew);
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
            getTop(crew);
          },
          errorCallback: function() {

          }
        });
      };
    }
  };

  var getTop = function(crew){
    CrewService.getTop({
      crew: crew,
      spec: $routeParams.specId,
      topn: 10,
      successCallback: function(res){
        $scope.pageLoading = false;
        $scope.tops = res.data;
        if(res.data.length > 0){
          var w = res.data[0].kms;
          for(var i = 0; i < res.data.length; i++){
            $scope.tops[i].pw = res.data[i].kms / w * 100;
          }
          wxShare(crew, true);
        } else {
          wxShare(crew, false);
        }
      },
      errorCallback: function(res){}
    });
  }

  var wxShare = function(crew, top) {
    var shareTitle = '', shareDesc = '', imgUrl = crew.logo;;
    if(top){
      var arr = {'daily': '本日', 'weekly': '本周', 'monthly': '本月'};
      var genderArr = ['TA','他','她'];
      shareTitle = arr[$scope.specId] +  '跑量排行榜 - ' + crew.name;
      shareDesc = '【' + arr[$scope.specId] +  '跑量TOP10】' + $scope.tops[0].user.name + '正在以' + $scope.tops[0].kms + '公里霸占榜首。快来挑战' + genderArr[$scope.tops[0].user.gender] + '！';
      if($rootScope.loggedInUser != null){
        for(var i =0; i < $scope.tops.length; i++){
          if($rootScope.loggedInUser.id == $scope.tops[i].user.id){
            if($scope.specId == 'daily'){
              shareDesc = '我今天打卡' + $scope.tops[i].times + '次，总里程' + $scope.tops[i].kms + '公里，冲入TOP10榜单第' + (i+1) + '名。快来挑战我！';
            } else {
              shareDesc = '我' + arr[$scope.specId] +  '跑步' + $scope.tops[i].days + '天，打卡' + $scope.tops[i].times + '次，总里程' + $scope.tops[i].kms + '公里，冲入TOP10榜单第' + (i+1) + '名。快来挑战我！';
            }
            imgUrl = $scope.tops[i].user.avatar;
          }
        }
      }
    }
    WxAPI.config({
      debug: false,
      successCallback: function() {
        wx.ready(function() {
          // 分享到朋友圈
          wx.onMenuShareTimeline({
            title: shareDesc + crew.name,
            link: $location.href, // 分享链接
            imgUrl: imgUrl, // 分享图标
            success: function () {
              //用户确认分享后执行的回调函数
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
            imgUrl: imgUrl, // 分享图标
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
            title: shareTitle, // 分享标题
            desc: shareDesc, // 分享描述
            link: $location.href, // 分享链接
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
            title: shareTitle, // 分享标题
            desc: shareDesc, // 分享描述
            link: $location.href, // 分享链接
            imgUrl: imgUrl, // 分享图标
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
