var analysisApp = angular.module('analysisApp', [ 'ngRoute', 'infinite-scroll' ]);

analysisApp
		.config(function($routeProvider, $locationProvider) {
			$routeProvider
					.when(
							'/analysis/clubs',
							{
								templateUrl : 'analysis/clubs.html?'
										+ new Date().getTime(),
								controller : 'AnalysisContorller',
								resolve : {
									
								}
							});
		});

analysisApp.factory('AnalysisService', function($http, $q, $rootScope) {
	var service = {
		getClubs : function(cell,code) {
			var deferred = $q.defer();
			$.ajax({
				url : "/analysis/clubs",
				data : {
					openid : $rootScope.openid,
					cell:cell,
					code:code
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getCode:function(cell){
			var deferred = $q.defer();
			$.ajax({
				url : "/analysis/getcellcode",
				data : {
					openid : $rootScope.openid,
			        cell:cell
				},
				beforeSend:function(){
					$rootScope.btnLoading("code_btn");
				},
				success : function(data) {
					deferred.resolve(data);
				},
				error:function(){
					$rootScope.btnReset("code_btn");
				}
			});
			return deferred.promise;
		}
		
	}
	return service;
});


analysisApp.controller('AnalysisContorller', function($scope, $routeParams, $rootScope,UtilService,AnalysisService) {
  $scope.getCode=function(){
	  var cell = $.trim($("#cell").val());
	  if(cell!=""){
		  AnalysisService.getCode(cell).then(function(data){
			  if(data.status==0){
				  $rootScope.btnReset("code_btn");
				  UtilService.showMessage(data.msg,null,null);
			  }else{
				  $rootScope.btnCountdown("code_btn",30);
			  }
		  });
	  }
	
  }
  $scope.islogin=false;
  $scope.login=function(){
	  var cell = $.trim($("#cell").val());
	  var code = $.trim($("#code").val());
	  if(cell!=""&&code!=""){
		  $scope.cell=cell;
		  $scope.code = code;
		  $("#loginBtn").button("loading");
		  AnalysisService.getClubs(cell,code).then(function(data){
			  if(data.status==1){
				  $scope.islogin=true;
				  $scope.clubs = data.data;
				  $scope.total =$scope.clubs.shift();   
			  }else{
				  UtilService.showMessage(data.msg,null,null);
			  }
		  });
	  }
  }

});


