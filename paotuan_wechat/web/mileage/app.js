'use strict';

// Declare app level module which depends on views, and components
angular.module('myApp', [
  'ngRoute',
  'myApp.config',
  'myApp.team',
  'myApp.workout',   
  'myApp.detail',
  'myApp.user',
  'myApp.wxauth',
  'myApp.team.trackerlogs',
  'myApp.team.users',
  'myApp.team.top',
  'myApp.user.trackerlogs',
  'myApp.cleanup',
  'infinite-scroll'
])
.directive('ptTabBar', ['UserService', 'CrewService', 'WxAPI', '$rootScope', function(UserService, CrewService, WxAPI, $rootScope) {
  return {
    restrict: 'E',
    templateUrl: 'templates/include/tabbar.html',
    controller: function($scope) {
      var user = $rootScope.loggedInUser;
      $scope.user = user;
      $scope.isInCrew = false;


      //加入跑团方法
      $scope.joinCrew = function(crewId) {
        CrewService.joinCrew({
          crewId: crewId,
          session_token: $rootScope.loggedInUser ? $rootScope.loggedInUser.session_token : '',
          successCallback: function(res) {
            //alert('成功加入跑团，快快打卡吧');
            $scope.isInCrew = true;
          },
          errorCallback: function() {
            //alert('操作失败，请稍候再试');
          }
        })
      };

      // 登录
      $scope.login = function(domain) {
        WxAPI.auth({
          domain: $rootScope.currentCrew.domain
          //fwd: window.location.href
        });
        return ;
      };

      if (user != null) {
        CrewService.getCrewsByUser({
          userid: user.id,
          successCallback: function(res) {
            // 获取用户所在的跑团
            var crews = res.data;
            //判断用户是否在跑团里
            for(var index in crews) {
              if (crews[index].domain == $rootScope.currentCrew.domain) {
                $scope.isInCrew = true;
                break;
              };
            }
          },
          errorCallback: function() {
            console.log('无法获取用户所在的跑团');
          }
        });
      }
    }
  }
}]).
directive('ptDurationSelect', function(){
  return {
    restrict: 'E',
    templateUrl: 'templates/include/duration-select.html',
    require: 'ngModel',
    replace: true,
    scope: {
      value: '=type'
    },
    link: function(scope, element, attrs , ngModelCtrl) {
      var min = attrs.min, max = attrs.max;
      scope.placeholder = attrs.placeholder;
      scope.durationArr = [];

      for(var i = min; i <= max; i++){
        scope.durationArr.push({value: i, key: i < 10 ? '0'+i : i});
      }

      ngModelCtrl.$parsers.push(function(viewValue) {
        return viewValue;
      });

      scope.$watch('result', function() {
        scope.viewResult = scope.result < 10 ? '0'+scope.result :scope.result;
        ngModelCtrl.$setViewValue(scope.result);
      });

    }
  }
})
.directive('ptAvatar', ['Config', function(Config){
return {
  restrict: 'E',
  template: '<img ng-src="{{avatar}}" />',
  link: function($scope, $element, $attrs){
    var avatar = $attrs.avatar.indexOf('upaiyun') > 0 ? $attrs.avatar + '!thumb'+ $attrs.thumb : $attrs.avatar;
    $scope.avatar = avatar ? avatar : Config.ROOT + '/img/avatar.jpg';
  }
}
}])
.directive('head', ['$rootScope', '$compile', function($rootScope, $compile){
  return {
    restrict: 'E',
    link: function(scope, element){
      var html = '<link rel="stylesheet" ng-repeat="(routeCtrl, cssUrl) in routeStyles" ng-href="{{cssUrl}}" />';
      element.append($compile(html)(scope));
      scope.routeStyles = {};
      $rootScope.$on('$routeChangeStart', function (e, next, current) {
        if(current && current.$$route && current.$$route.css){
          if(!angular.isArray(current.$$route.css)){
            current.$$route.css = [current.$$route.css];
          }
          angular.forEach(current.$$route.css, function(sheet){
            delete scope.routeStyles[sheet];
          });
        }
        if(next && next.$$route && next.$$route.css){
          if(!angular.isArray(next.$$route.css)){
            next.$$route.css = [next.$$route.css];
          }
          angular.forEach(next.$$route.css, function(sheet){
            scope.routeStyles[sheet] = sheet;
          });
        }
      })
    }
  }
}])
.factory('AuthService', ['Config', function(Config) {
  return {
    wxauth: function(params) {
      var auth_url = Config.WX_AUTH_URL + '?source_url=' + encodeURIComponent(Config.ROOT + '/#/wxauth/' + (!!params.crew ? params.crew.domain : ''));
      window.location.href = auth_url;
    }
  }
}])
.factory('UserService', ['$http', '$q', 'Config', '$window', 'Constants', function($http, $q, Config, $window, Constants) {
  return {
    // 获取本地用户
    getLocalUser: function(params) {
      var userStr = $window.localStorage[Constants.Current_user];
      if (!!userStr) {
        return JSON.parse(userStr);
      } else {
        return null;
      }
    },
    getUser: function(params) {
      var deferred = $q.defer();
      $http({global: false, url: Config.API_ROOT + '/users/' + params.userid + '.json'}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      }).error(function() {
        deferred.resolve({success: false});
      });
      
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    getSummary: function(params) {
      var deferred = $q.defer();
      $http({global: false, url: Config.API_ROOT + '/users/' + params.userid + '/summary.json'}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    }
  };
}])
.factory('TrackerService', ['$http', '$q', 'Config', function($http, $q, Config) {
  return {
    getTracks: function(params){
      var deferred = $q.defer();
      $http({global: false, method: 'GET', url: Config.API_ROOT + '/trackerlogs/' + params.trackerId + '.json'}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    getTracksByUser: function(params) {
      var deferred = $q.defer();
      var pageStr = '?page=' + (params.page ? params.page : '0') + '&per=' + (params.per ? params.per : '0');
      $http({global: false, url: Config.API_ROOT + '/users/' + params.userid + '/trackerlogs' + '.json' + pageStr}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    deleteTracker: function(params) {
      var deferred = $q.defer();
      $http.delete(Config.API_ROOT + '/trackerlogs/' + params.id + '.json').success(function(response){
        deferred.resolve({success: true});
      })
      .error(function(){
        deferred.resolve({success: false});
      });

      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    getFavors: function(params){
      var deferred = $q.defer();
      $http({global: false, url: Config.API_ROOT + '/trackerlogs/' + params.trackerId + '/favors'}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    addFavors: function(params){
      var deferred = $q.defer();
      $http.post(Config.API_ROOT + '/trackerlogs/' + params.trackerId + '/favors', {
        session_token: params.session_token ? params.session_token : ''
      }).success(function(data, status, headers, config) {
        if (!!data.data ) {
          deferred.resolve({success: true, data: data.data});
        } else {
          deferred.resolve({success: false});
        }
      })
      .error(function(data, status, headers, config) {
        deferred.resolve({success: false, message: '操作失败，请稍候再试'});
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    deleteFavors: function(params) {
      var deferred = $q.defer();
      $http.delete(Config.API_ROOT + '/trackerlogs/' + params.trackerId + '/favors').success(function(response){
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      })
      .error(function(){
        deferred.resolve({success: false});
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    }
  }
}])
.factory('CrewService', ['Config', '$http', '$q', '$routeParams', '$window', 'Constants', function(Config, $http, $q, $routeParams, $window, Constants) {
  var getCrew = function() {
    var crewId = $routeParams.crewId;
    var deferred = $q.defer();
    $http({global: false, url: Config.API_ROOT + '/crews/' + crewId + '.json'}).success(function(response) {
      if (!!response.data ) {
        deferred.resolve({success: true, data: response.data});
      } else{
        deferred.resolve({success: false});
      }
    });
    return deferred.promise;
  };

  var getCrewFromUrl = function(successCallback, errorCallback) {
    var crewId = $routeParams.crewId;
    if (!crewId) { return null ;}
    getCrew(crewId).then(function(res) {
      if (res.success) {
        var crew = res.data;
        crew.expireAt = (new Date()).getTime() +  24 * 60 * 60;
        $window.localStorage[Constants.CREW] = JSON.stringify(crew);
        successCallback(crew);
      } else {
        console.log('获取跑团信息出错');
        errorCallback();
      }
    });
  };
  return {
    getCrew: function(successCallback, errorCallback) {
      var crewStr = $window.localStorage[Constants.CREW];
      var crew = {};
      if (!!crewStr) {
        crew = JSON.parse(crewStr);
        var crewIdInUrl = $routeParams.crewId;

        var now = (new Date()).getTime();
        if (crewIdInUrl != crew.domain || now - crew.expireAt > 24 * 60 * 60) {
          getCrewFromUrl(function(newcrew){
            successCallback(newcrew)
          }, function() {});
        } else {
          successCallback(crew);
        }
      } else if( !!$routeParams.crewId ) {
        getCrewFromUrl(function(newcrew){
          successCallback(newcrew);
        }, function() {});
      }
    },
    getCrewByDomain: function(params) {
      var deferred = $q.defer();
      $http({global: false, url: Config.API_ROOT + '/crews/' + params.domain + '.json'}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res.data);
        } else {
          console.log('获取跑团信息出错');
        }
      });
    },
    // 获取用户所属跑团
    getCrewsByUser: function(params) {
      var deferred = $q.defer();
      $http({global: false, url: Config.API_ROOT + '/users/' + params.userid + '/crews' + '.json'}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    joinCrew: function(params) {
      var deferred = $q.defer();

      $http.post(Config.API_ROOT + '/crews/' + params.crewId + '/join.json', {
        session_token: params.session_token
      })
      .success(function(data, status, headers, config) {
        if (!!data.data ) {
          deferred.resolve({success: true});
        } else {
          deferred.resolve({success: false});
        }
      })
      .error(function(data, status, headers, config) {
        deferred.resolve({success: false, message: '操作失败，请稍候再试'});
      });

      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    getSummary: function(params) {
      var deferred = $q.defer();
      $http({global: false, url: Config.API_ROOT + '/crews/' + params.crew.domain + '/summary.json'}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    getTracks: function(params) {
      var deferred = $q.defer();
      var pageStr = '?page=' + (params.page ? params.page : '0') + '&per=' + (params.per ? params.per : '0');
      $http({global: false, url: Config.API_ROOT + '/crews/' + params.crew.domain + '/trackerlogs.json' + pageStr}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    getUsers: function(params){
      var deferred = $q.defer();
      var pageStr = '?page=' + (params.page ? params.page : '0') + '&per=' + (params.per ? params.per : '0');
      $http({global: false, url: Config.API_ROOT + '/crews/' + params.crew.domain + '/members.json' + pageStr}).success(function(response) {
        // $http({global: false, url: 'users.json' + pageStr}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    getStats: function(params){
      var deferred = $q.defer();
      var pageStr = (params.at ? '?at='+params.at : '') + (params.from ? '?from='+params.from : '') + (params.to ? '&to='+params.to : '');
      $http({global: false, url: Config.API_ROOT + '/stats/crew/' + params.crew.domain + '/' + params.spec + '.json' + pageStr}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    },
    getTop: function(params){
      var deferred = $q.defer();
      var pageStr = (params.topn ? '?topn='+params.topn : 'topn=10') + (params.at ? '&at='+params.at : '') + (params.from ? '&from='+params.from : '') + (params.to ? '&to='+params.to : '');
      $http({global: false, url: Config.API_ROOT + '/stats/crew/' + params.crew.domain + '/' + params.spec + '/top.json' + pageStr}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    }
  };
}])
.factory('DateService', [function(){
  return {
    getDateBydays: function(params){
      var date = new Date();
      date = date.valueOf();
      date = date - (params.days - 1) * 24 * 60 * 60 * 1000;
      date = new Date(date);
      return date;
    }
  }
}])
.factory('TimeService', [function() {
  return {
    formatSeconds: function(params) {
      var isZh = params.isZh;
      var theTime = parseInt(params.seconds);// 秒
      var theTime1 = 0;// 分
      var theTime2 = 0;// 小时
      if(theTime > 60) {
        theTime1 = parseInt(theTime/60);
        theTime = parseInt(theTime%60);
        if(theTime1 > 60) {
          theTime2 = parseInt(theTime1/60);
          theTime1 = parseInt(theTime1%60);
        }
      }
      var result = parseInt(theTime) + (isZh ? '秒' : '"');
      // var result = "";
      if(theTime1 > 0) {
        result = parseInt(theTime1) + (isZh ? '分' : '\'') + result;
      }
      if(theTime2 > 0) {
        result = parseInt(theTime2)+ (isZh ? '小时' : ':') + result;
      }
      return result;
    }
  };
}])
.factory('FeedService', ['Config', '$q', '$http', function(Config, $q, $http) {
  return {
    share: function(params){
      var deferred = $q.defer();

      $http.post(Config.API_ROOT + '/feeds/share.json', {
        session_token: params.session_token,
        site: params.site,
        target_id: params.target_id,
        target_type: params.target_type
      })
      .success(function(data, status, headers, config) {
        if (!!data.data ) {
          deferred.resolve({success: true});
        } else {
          deferred.resolve({success: false});
        }
      })
      .error(function(data, status, headers, config) {
        deferred.resolve({success: false, message: '操作失败，请稍候再试'});
      });

      deferred.promise.then(function(res) {
        if (res.success) {
          params.successCallback(res);
        } else {
          params.errorCallback();
        }
      });
    }
  };
}])
.factory('WxAPI', ['Config', '$q', '$http', function(Config, $q, $http) {
  var getWxTicket = function() {
    var deferred = $q.defer();
    $http({global: false, url: Config.API_ROOT + '/weixin/signature.json?url=' + encodeURIComponent(window.location.href.split('#')[0])}).success(function(response) {
      if (!!response.data ) {
        deferred.resolve({success: true, data: response.data});
      } else{
        deferred.resolve({success: false});
      }
    });
    return deferred.promise;
  };
  return {
    config: function(params) {
      getWxTicket().then(function(res) {
        if (res.success) {
          wx.config({
            debug: false || params.debug, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: res.data.appid, // 必填，公众号的唯一标识
            timestamp: res.data.timestamp, // 必填，生成签名的时间戳
            nonceStr: res.data.noncestr, // 必填，生成签名的随机串
            signature: res.data.signature,// 必填，签名，见附录1
            jsApiList: [
              'chooseImage',
              'uploadImage',
              'onMenuShareTimeline',
              'onMenuShareAppMessage',
              'onMenuShareQQ',
              'onMenuShareWeibo'
            ] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
          });
          wx.ready(function() {
            params.successCallback();
          });
        } else {
          params.errorCallback();
        }
      });
    },
    auth: function(params) {
      var auth_url = Config.WX_AUTH_URL + '?source_url=' + Config.ROOT + encodeURIComponent('/#/wxauth/' + params.domain);
      //if (params.fwd) {
      //  auth_url += encodeURIComponent('?fwd=' + params.fwd);
      //};
      auth_url = auth_url;
      window.location.href = auth_url;
    },
    shareStats: function(params) {
      var deferred = $q.defer();
      $http({global: false, url: Config.API_ROOT + '/weixin/signature.json?url=' + encodeURIComponent(window.location.href.split('#')[0])}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
    }
  };
}])
.constant('Constants', {
  CURRENT_USER: 'currentUser',
  CURRENT_CREW: 'crew',
  SESSION_TOKEN_HEADER: 'x-session-token'
})
.constant('AUTH_EVENTS', {
  loginSuccess: 'auth-login-success',
  loginFailed: 'auth-login-failed',
  needLogin: 'auth-need-login',
  logoutSuccess: 'auth-logout-success',
  userRegisterSuccess: 'auth-register-success',
  userRegisterFailed: 'auth-register-failed',
  sessionTimeout: 'auth-session-timeout',
  notAuthenticated: 'auth-not-authenticated',
  notAuthorized: 'auth-not-authorized',
  udpateNameSuccess: 'update-name-success',
  udpateEmailSuccess: 'update-email-success'
})
.constant('AJAX_EVENTS', {
  AJAX_SUCCESS_CODE: 0,
  ajaxStart: 'ajax-start',
  ajaxStop: 'ajax-stop',
  ajaxError: 'ajax-error'
})
// httpInterceptor
.factory('ptHttpInterceptor', ['$q', '$rootScope', 'AJAX_EVENTS', 'Config', 'AUTH_EVENTS','Constants', function($q, $rootScope, AJAX_EVENTS, Config, AUTH_EVENTS, Constants) {

  return {
    request: function(config) {
      if ($rootScope.session_token) {
        config.headers[Constants.SESSION_TOKEN_HEADER] = $rootScope.session_token;
      } else {
        var localUserStr = window.localStorage[Constants.CURRENT_USER];
        if (localUserStr) {
          try {
            var localUser = JSON.parse(localUserStr);
            $rootScope.loggedInUser = localUser;
            config.headers[Constants.SESSION_TOKEN_HEADER] = localUser.session_token;
          } catch(e) {

          }
        };
      }
      
      //header中加入domain?
      if (!!config.global) {
        // 显示加载中
        //$rootScope.$broadcast(AJAX_EVENTS.ajaxStart);
      };
      return config || $q.when(config);
    },
    requestError: function(rejection) {
      //console.log('request error');
      rejection.config.global && $rootScope.$broadcast(AJAX_EVENTS.ajaxStop);
      return $q.reject(rejection);
    },
    response: function(response) {
      //console.log('response!');
      response.config.global && $rootScope.$broadcast(AJAX_EVENTS.ajaxStop);
      return response || $q.when(response);
    },
    responseError: function(rejection) {
      //rejection.config.global && $rootScope.$broadcast(AJAX_EVENTS.ajaxStop);
      //需要用户权限
      if(rejection.status === 401) {
        $rootScope.$broadcast(AUTH_EVENTS.needLogin);
      }
      if (rejection.status == 422) {
        alert(rejection.data.message);
      }
      return $q.reject(rejection);
    }
  };
}])
.config(['$httpProvider', function($httpProvider){
  $httpProvider.interceptors.push('ptHttpInterceptor');
}])
.config(['$routeProvider', function($routeProvider) {
  $routeProvider.otherwise({redirectTo: '/team/fanscrew'});
}])
.run(['$rootScope', '$location', 'Config', 'AUTH_EVENTS', 'WxAPI', 'CrewService', 'Constants', function($rootScope, $location, Config, AUTH_EVENTS, WxAPI, CrewService, Constants) {
  // 设置全局Title
  $rootScope.pageTitle = Config.SITE_NAME ? Config.SITE_NAME : '跑团小秘';
  // 获取当前跑团
  $rootScope.domain = $rootScope.domain || Config.DEFAULT_DOMAIN;
  //初始化跑团
  if (!$rootScope.currentCrew) {
    CrewService.getCrewByDomain({
      domain: $rootScope.domain,
      successCallback: function(crew) {
        $rootScope.currentCrew = crew;
        $rootScope.domain = crew.domain;
      },
      errorCallback: function() {
        alert('获取跑团信息出错，请稍候再试');
      }
    });
  };

  //初始化本地用户
  var localUserStr = window.localStorage[Constants.CURRENT_USER];
  if (localUserStr) {
    try {
      var localUser = JSON.parse(localUserStr);
      $rootScope.loggedInUser = localUser;
    } catch(e) {

    }
  };

  if (window.location.href.indexOf('8000') > 0) {
    $rootScope.loggedInUser = {
        id: 'rRa7lD5v',
        name: "小伟",
        avatar: "http://dev-img-mi-paotuan.b0.upaiyun.com/uploads/user/78/f6/78f6ddf4f31f14ddebf833aa3cc11a29.jpeg",
        session_token: '83a801ed-ae3f-4700-8abe-12bb664ce4fa'
      }
  };
  // 定义需要用户权限的页面列表
  //测试时注释
  var need_auth_paths = ['workout'];
  $rootScope.$on( "$routeChangeStart", function(event, next, current) {
    if (!$rootScope.loggedInUser) {
      for(var i = 0, iMax = need_auth_paths.length; i < iMax; i++) {
        var path = need_auth_paths[i];
        var reg = new RegExp(path);
        if (reg.test(window.location.href)) {
         // WxAPI.auth({params: $rootScope.domain});
          break;
        };
      }
    }
  });

  $rootScope.$on(AUTH_EVENTS.needLogin, function(event, next, current){
    WxAPI.auth({domain: $rootScope.domain});
  });
}])
;
