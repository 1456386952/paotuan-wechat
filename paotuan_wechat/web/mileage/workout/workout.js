'use strict';

angular.module('myApp.workout', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/workout/:crewId', {
    templateUrl: 'workout/workout.html',
    controller: 'WorkoutCtrl'
    // css: 'css/workout/workout.css'
  });
}])

.controller('WorkoutCtrl',  ['$scope', '$http', '$q', 'Config', '$window', '$routeParams', '$location', 'WxAPI', 'TimeService', 'CrewService', '$rootScope', '$timeout', function($scope, $http, $q, Config, $window, $routeParams, $location, WxAPI, TimeService, CrewService, $rootScope, $timeout) {
  
  $scope.isSubmiting = false;

  $scope.user = $rootScope.loggedInUser;
  $scope.sign = {}
  $scope.sign.distance = "";
  $scope.sign.date = 0;
  $scope.sign.duration = {};
  $scope.uploadImages = {}  ;
  $scope.serverIdArray = [];

  $scope.minDuration = 4 * 60; // 2m55s
  $scope.maxDuration = 9 * 60; // 15m00s

  $scope.dates = [
    {date: 2, text: "自定义"},
    {date: 1, text: "昨天"},
    {date: 0, text: "今天"}
  ]

  $scope.seleteDate = function(date){
    $scope.sign.date = date;
  }

  $scope.changeDuration =  function(t){
    if($scope.duration[t] != null){
      if($scope.duration[t].length >= 2){
        switch(t){
          case 'h':
            angular.element('#durantion_m').focus();
            break;
          case 'm':
            angular.element('#durantion_s').focus();
            break;
        }
      }

      if($scope.duration[t].length == 0){
        switch(t){
          case 's':
            angular.element('#durantion_m').focus();
            break;
          case 'm':
            angular.element('#durantion_h').focus();
            break;
        }
      }
    } 
  }; 

  $scope.cancel = function(){
    $window.history.back();
  }
  $scope.doSign = function(signForm){
    $scope.isSubmiting = true;

    var data = $scope.sign;
    if(data.distance == null || data.distance == ""){
      alert("请输入有效的跑步距离");
      $scope.isSubmiting = false;
      angular.element('#distance').focus();
      return;
    }
    if(data.date == null || data.date < 0){
      alert("请选择跑步日期");
      $scope.isSubmiting = false;
      return;
    }
    
    if(data.duration.h == undefined && data.duration.m == undefined && data.duration.s == undefined){
      alert("请选择您跑了多久");
      $scope.isSubmiting = false;
      angular.element('#durantion_h').focus();
      return;
    }

    data.duration.h = data.duration.h != undefined ? data.duration.h : 0;
    data.duration.m = data.duration.m != undefined ? data.duration.m : 0;
    data.duration.s = data.duration.s != undefined ? data.duration.s : 0;

    var duration = parseInt(data.duration.h * 60 * 60) + parseInt(data.duration.m * 60) + parseInt(data.duration.s);
    if(duration <= 0 || data.duration.h > 99 || data.duration.m > 59 || data.duration.s > 59){
      alert("请正确输入跑步时间");
      $scope.isSubmiting = false;
      angular.element('#durantion_h').focus();
      return; 
    }
    if ($scope.serverIdArray.length == 0) {
      alert('请至少上传一张图片');
      $scope.isSubmiting = false;
      return false;
    }
    console.info(data.duration.h + 'h ' + data.duration.m + 'm  ' + data.duration.s + 's  ');
    submitWorkout({
      distance: data.distance,
      dateIndex: data.date,
      duration: duration,
      images: $scope.serverIdArray.join('^')
    }).then(function(res) {
      if (res.success) {
        $location.path('/detail/' + $scope.currentCrew.domain + '/' + res.data.id);
      } else {
        alert('打卡失败，请稍候再试');
        $scope.isSubmiting = false;
      }
    });
  }

  var submitWorkout = function(workout) {
    var deferred = $q.defer();
    $http.post(Config.API_ROOT + '/trackerlogs.json', {
      distance: workout.distance,
      executed_at: workout.dateIndex,
      duration: workout.duration,
      images: workout.images,
      session_token: $scope.user.session_token
    })
    .success(function(data, status, headers, config) {
      if (!!data.data ) {
        deferred.resolve({success: true, data: data.data});
      } else {
        deferred.resolve({success: false, message: data.message});
      }
    })
    .error(function(data, status, headers, config) {
      deferred.resolve({success: false, message: '操作失败，请稍候再试'});
    });
    return deferred.promise;
  };

  $scope.cleanImage = function() {
    $scope.serverIdArray = [];

    angular.element('#image-cleaner').hide();
    angular.element('#image-picker').show();
    angular.element('#upload-tip').show();
    angular.element('#images-container .uploadedImage').remove();
  };

  //测试时先注释
  
  // WxAPI.config({
  //   debug: false,
  //   successCallback: function() {
  //     var uploadImages = {};
  //     wx.ready(function() {
  //       var localIds = [];
  //       // 绑定删除图片事件
  //       angular.element('#images-container').delegate('.photo .del', 'click', function(){
  //         var _this = angular.element(this);
  //         var localId = _this.data('localid');
  //         localIds = angular.element.grep(localIds, function(value) {
  //           return value != localId;
  //         });
  //         _this.parent().remove();
  //         delete $scope.uploadImages[localId];
  //       });

  //       // 串行上传多张图片
  //       var syncUpload = function(localIds) {
  //         var localId = localIds.shift();
  //         wx.uploadImage({
  //           localId: localId, // 需要上传的图片的本地ID，由chooseImage接口获得
  //           isShowProgressTips: 1, // 默认为1，显示进度提示
  //           success: function (res) {
  //             var serverId = res.serverId; // 返回图片的服务器端ID
  //             $scope.serverIdArray.push(serverId);
  //             //angular.element('#images-container').append('<li class="photo uploadedImage"><i data-localid="'+localId+'" class="del"></i><div class="img" data-serverId="'+serverId+'"><img src="'+localId+'" /></div></li>');
  //             angular.element('#images-container').append('<li class="photo uploadedImage"><div class="img" data-serverId="'+serverId+'"><img src="'+localId+'" /></div></li>');
  //             angular.element('#upload-tip').hide();
  //             angular.element('#image-picker').hide();
  //             angular.element('#image-cleaner').show();
              
  //             $scope.uploadImages[localId] = {localId: localId, serverId: serverId};
  //             $timeout(function() {
  //               if (localIds.length > 0) {
  //                   syncUpload(localIds);
  //               };
  //             }, 10);
  //           }
  //         });
  //       };

  //       $scope.chooseImage = function() {
  //         wx.chooseImage({
  //           success: function (res) {
  //             $timeout(function() {
  //               localIds = res.localIds; 
  //               angular.element('#images-container .uploadedImage').remove();
  //               $scope.uploadImages = {};
  //               $scope.serverIdArray = [];
  //               angular.element('#image-picker').show();
  //               angular.element('#upload-tip').show();
  //               syncUpload(localIds);
  //             }, 10);
  //           }
  //         });
  //       };
  //       var shareTitle = '跑步打卡';
  //       var shareDesc = '记录你的每一次跑步。\r\n来自 ' + ($rootScope.currentCrew ? $rootScope.currentCrew.name : Config.SITE_NAME);
  //       var imageUrl = ($rootScope.currentCrew ? $rootScope.currentCrew.logo : '');
  //       // 分享到朋友圈
  //       wx.onMenuShareTimeline({
  //         title: shareTitle, // 分享标题
  //         link: $location.href, // 分享链接
  //         imgUrl: imageUrl, // 分享图标
  //         success: function () { 
  //           //用户确认分享后执行的回调函数
  //         },
  //         cancel: function () { 
  //           //用户取消分享后执行的回调函数
  //         }
  //       });

  //       // 分享给好友
  //       wx.onMenuShareAppMessage({
  //         title: shareTitle, // 分享标题
  //         desc: shareDesc, // 分享描述
  //         link: $location.href, // 分享链接
  //         imgUrl: imageUrl, // 分享图标
  //         dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
  //         success: function () { 
  //           // 用户确认分享后执行的回调函数
  //         },
  //         cancel: function () { 
  //           // 用户取消分享后执行的回调函数
  //         }
  //       });

  //       // 分享到qq
  //       wx.onMenuShareQQ({
  //         title: shareTitle, // 分享标题
  //         desc: shareDesc, // 分享描述
  //         link: $location.href, // 分享链接
  //         imgUrl: imageUrl, // 分享图标
  //         success: function () { 
  //           // 用户确认分享后执行的回调函数
  //         },
  //         cancel: function () { 
  //           // 用户取消分享后执行的回调函数
  //         }
  //       });

  //       // 分享到腾讯微博
  //       wx.onMenuShareWeibo({
  //         title: shareTitle, // 分享标题
  //         desc: shareDesc, // 分享描述
  //         link: $location.href, // 分享链接
  //         imgUrl: imageUrl, // 分享图标
  //         success: function () { 
  //           // 用户确认分享后执行的回调函数
  //         },
  //         cancel: function () { 
  //           // 用户取消分享后执行的回调函数
  //         }
  //       });
  //     });
  //   },
  //   errorCallback: function() {
  //     alert('微信环境初始化失败，请刷新页面或稍候再试');
  //   }
  // });

}]);