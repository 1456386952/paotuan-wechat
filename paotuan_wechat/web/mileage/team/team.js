'use strict';

angular.module('myApp.team', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/team/:crewId', {
    templateUrl: 'team/team.html',
    controller: 'TeamCtrl'
    // css: 'css/team/team.css'
  });
}])

.controller('TeamCtrl', ['$scope', '$routeParams', '$window', 'CrewService', 'WxAPI', '$location', 'TimeService','DateService', 'FeedService', '$rootScope', 'Config', '$filter', function($scope, $routeParams, $window, CrewService, WxAPI, $location, TimeService, DateService, FeedService, $rootScope, Config, $filter) {
  $scope.crewId = $routeParams.id;
  if ($routeParams.crewId) {
    $rootScope.domain = $routeParams.crewId;
  };

  var getCrewInfo = function(crew) {
    CrewService.getSummary({
      crew: crew,
      successCallback: function(res) {
        $scope.summary = res.data;
      },
      errorCallback: function(res) {}
    });

    var shared_to_feed = function(site) {
      FeedService.share({
        site: site,
        target_id: crew.domain,
        target_type: 'crews',
        session_token: $rootScope.loggedInUser ? $rootScope.loggedInUser.session_token : '',
        successCallback: function(res) {
        },
        errorCallback: function(res) {}
      });
    }

    CrewService.getTracks({
      crew: crew,
      page: 1,
      per: 3,
      successCallback: function(res) {
        var logs = res.data;
        for(var index in logs) {
          var log = logs[index];
          var average = log.duration / log.distance;
          log.duration = TimeService.formatSeconds({seconds:log.duration, isZh: false});
          log.average = TimeService.formatSeconds({seconds:average, isZh: false});
        }
        $scope.logs = logs;
      },
      errorCallback: function(res) {}
    });

    // 图表配置
    require.config({
      paths: {
        echarts: 'http://echarts.baidu.com/build/dist'
      }
    });
    // 初始化
    require(
      [
        'echarts',
        'echarts/chart/bar'
      ],
      function(ec){
        // 周统计
        CrewService.getStats({
          crew: crew,
          spec: 'daily',
          from: $filter('date')(DateService.getDateBydays({days: 7}),'yyyy-MM-dd'),
          successCallback: function(res){
            var stats = {date: [], kms: []};
            for(var i = 0, data = res.data; i < data.length; i++){
              stats.date.push($filter('date')(new Date(data[i].checkpoint),'MM-dd'));
              stats.kms.push(data[i].kms);
            }
            // 初始化打卡周比例
            var statsChart = ec.init(document.getElementById('statsChart'));
            var statsOption = {
              tooltip: {trigger: 'axis', formatter: '{a} <br/>{b} : {c} KM'},
              xAxis: [{type: 'category', data: stats.date}],
              yAxis: [{type : 'value'}],
              grid: {x: 30, x2: 10, y: 20, y2: 30},
              series: [
                {
                  name:'跑量',
                  type:'bar',
                  data: stats.kms
                }
              ]
            };
            statsChart.setOption(statsOption);
          },
          errorCallback: function(res){}
        });


        // 排行
        $scope.showTop = function(spec){
          $scope.spec = spec;
          CrewService.getTop({
            crew: crew,
            spec: spec,
            topn: 5,
            successCallback: function(res){
              if(res.data.length > 0){
                var rankingChart = ec.init(document.getElementById('rankingChart'));
                var rankingUser = [], rankingData = [];
                for(var i = res.data.length; i > 0; i--){
                  rankingUser.push(res.data[i-1].user.name);
                  rankingData.push(res.data[i-1].kms);
                }
                var rankingOption = {
                  // tooltip: {trigger: 'axis', formatter: '{a} <br/>{b} : {c} KM'},
                  xAxis: [{type: 'value', boundaryGap: [0, 0.01]}],
                  yAxis: [{type: 'category', data: rankingUser}],
                  grid: {x: 50, x2:20, y:10, y2: 30},
                  series: [
                    {
                      name: '一周排行',
                      type: 'bar',
                      itemStyle: {normal: {label: {show: true}}},
                      data: rankingData
                    }
                  ]
                };
                rankingChart.setOption(rankingOption)
              } else {
                angular.element('#rankingChart').html('暂无数据');
              }
            },
            errorCallback: function(res){}
          });
        }
        $scope.showTop('weekly');
        // $scope.showTop =  function(){
        //   $window.location.href = '/app/#/team/' + $scope.crew.domain + '/top/' + $scope.spec;
        // };
      }
    );

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
              shared_to_feed('timeline');
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
              shared_to_feed('friend');
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
              shared_to_feed('qq');
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
              shared_to_feed('weibo');
            },
            cancel: function () {
              // 用户取消分享后执行的回调函数
            }
          });
        });
      }
    });
  };

  // url中指定的跑团
  if (!!$routeParams.id) {
    CrewService.getCrewByDomain({
      domain: $routeParams.id,
      successCallback: function(crew) {
        $scope.crew = crew;
        getCrewInfo(crew);
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
        },
        errorCallback: function() {

        }
      });
    };
  }
}]);