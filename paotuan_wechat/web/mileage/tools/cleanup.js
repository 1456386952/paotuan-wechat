'use strict';

angular.module('myApp.cleanup', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/cleanup', {
    templateUrl: 'tools/cleanup.html',
    controller: 'CleanupCtrl'
  });
}])

.controller('CleanupCtrl',  ['Constants', '$window', function(Constants, $window) {
  console.log('clean up');
  delete $window.localStorage[Constants.CURRENT_USER];
}]);