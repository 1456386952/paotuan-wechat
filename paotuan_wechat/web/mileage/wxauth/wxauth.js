'use strict';

angular.module('myApp.wxauth', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/wxauth/:crewId', {
    templateUrl: 'wxauth/wxauth.html',
    controller: 'wxauthCtrl'
  });
}])
.controller('wxauthCtrl', ['Config','$routeParams', '$http', '$location', '$q', '$rootScope','AUTH_EVENTS', '$window', 'CrewService', 'Constants',
  function(Config, $routeParams, $http, $location, $q, $rootScope, AUTH_EVENTS, $window, CrewService, Constants) {
  delete window.localStorage[Constants.CURRENT_USER];
  var session_token = $routeParams.session_token;
  $rootScope.session_token = session_token;
  $rootScope.domain = $routeParams.crewId;
  //登录
  var getUser = function(session_token) {
    var deferred = $q.defer();
    $http({global: false, url: Config.API_ROOT + '/users/current.json?session_token=' + session_token}).success(function(response) {
        if (!!response.data ) {
          deferred.resolve({success: true, data: response.data});
        } else{
          deferred.resolve({success: false});
        }
      });
      return deferred.promise;
  };

  getUser(session_token).then(function(res) {
    if (res.success) {
      $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
      $rootScope.loggedInUser = res.data;
      $location.path('/team/' + $routeParams.crewId);
      // 本地保存用户信息
      window.localStorage[Constants.CURRENT_USER] = JSON.stringify(res.data);
    } else {
      alert('登录失败，请稍候再试');
    }
  });
}])
;