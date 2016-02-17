var marketing_club = angular.module('marketing_club', [ 'ngRoute',
		'infinite-scroll' ]);
marketing_club.config(function($routeProvider, $locationProvider) {
	$routeProvider.when("/marketing/club/register", {
		templateUrl : 'marketing/club/intro.html?' + new Date().getTime(),
		controller : 'MarketingClubsRegisterContorller',
		resolve : {
			myClubs : function($route, RunnerService, $rootScope) {
				return RunnerService.getOwnClubs();
			},
			marketingInfo : function($route, MarketingService) {
				var mi = MarketingService
						.getMarketingInfo($route.current.params["id"],false,function(data){
							if(data.reg_end&&!data.vote_end){
								location.replace("http://"+document.domain+"/wechat/#/marketing/club/vote?id="+data.marketing.marketing_id);
							    return false;
							}
							
							if(data.reg_end&&data.vote_end){
								location.replace("http://"+document.domain+"/wechat/#/marketing/club/voteSuccess?id="+data.marketing.marketing_id);
							    return false;
							}
							return true;
						});
				return mi;
			}
		}
	   })
	   .when("/marketing/club/vote", {
		templateUrl : 'marketing/club/vote.html?' + new Date().getTime(),
		controller : 'MarketingClubsVoteContorller',
		resolve : {
			marketingInfo : function($route, MarketingService) {
				var mi = MarketingService
				.getMarketingInfo($route.current.params["id"],false,function(data){
					if(data.vote_end||!data.can_vote){
						location.replace("http://"+document.domain+"/wechat/#/marketing/club/voteSuccess?id="+data.marketing.marketing_id);
					    return false;
					}
					return true;
				});
		    return mi;
		  }
		}
	   })
	   .when("/marketing/club/voteSuccess", {
			templateUrl : 'marketing/club/vote_success.html?' + new Date().getTime(),
			controller : 'MarketingClubsVoteSuccessContorller',
			resolve : {
				rank : function($route, MarketingService) {
					return MarketingService
							.clubVoteSuccess($route.current.params["id"]);
				}
			}
	})
	   .when("/marketing/club/result", {
			templateUrl : 'marketing/club/result.html?' + new Date().getTime(),
			controller : 'MarketingClubsResultContorller',
			resolve : {
				rank : function($route, MarketingService) {
					return MarketingService
							.clubMileageRank($route.current.params["id"]);
				}
			}
	});
	;
});

marketing_club.run(function($rootScope, LoginService, WxService, ClubService,
		$location, UtilService) {

	$rootScope.$on('ngRenderFinished', function(ngRenderFinishedEvent, data) {
		$("#marketing_container").css("min-height",$(window).height());
	});
});

marketing_club.factory('MarketingService', function($http, $q, $rootScope) {
	var service = {
		getMarketingInfo : function(marketing_id,async,callBack) {
			var deferred = $q.defer();
			$.ajax({
				url : "/marketing/info",
				data : {
					openid : $rootScope.openid,
					marketing_id : marketing_id
				},
				async:true,
				success : function(data) {
					if($.isFunction(callBack)){
						callBack(data);
					}
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		getMarketingInfoVote : function(marketing_id) {
			var deferred = $q.defer();
			$.ajax({
				url : "/marketing/infovote",
				data : {
					openid : $rootScope.openid,
					marketing_id : marketing_id
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		clubReg : function(marketing_id, club_eng) {
			var deferred = $q.defer();
			$.ajax({
				url : "/marketing/clubreg",
				data : {
					openid : $rootScope.openid,
					marketing_id : marketing_id,
					club_eng : club_eng
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		clubs : function(marketing_id) {
			var deferred = $q.defer();
			$.ajax({
				url : "/marketing/clubs",
				data : {
					openid : $rootScope.openid,
					marketing_id : marketing_id,
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		clubVote : function(clubs,marketing_id) {
			var deferred = $q.defer();
			$.ajax({
				url : "/marketing/clubvote",
				data : {
					openid : $rootScope.openid,
					clubs : clubs,
					marketing_id : marketing_id
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		clubVoteSuccess : function(marketing_id) {
			var deferred = $q.defer();
			$.ajax({
				url : "/marketing/voterank",
				data : {
					openid : $rootScope.openid,
					marketing_id : marketing_id
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		},
		clubMileageRank : function(marketing_id) {
			var deferred = $q.defer();
			$.ajax({
				url : "/marketing/mileagerank",
				data : {
					openid : $rootScope.openid,
					marketing_id : marketing_id
				},
				success : function(data) {
					deferred.resolve(data);
				}
			});
			return deferred.promise;
		}
	}
	return service;
});


marketing_club
.controller(
		'MarketingClubsRegisterContorller',
		function($scope, $routeParams, WxService, UtilService,
				MarketingService, $rootScope, $compile, myClubs,
				marketingInfo,$sce) {
			$scope.myClubs_length = myClubs.length;
			$scope.myClubs = myClubs;
			$scope.marketing = marketingInfo.marketing;
			$("#rule_pre").html($scope.marketing.rule_desc);
			WxService.shareObj.title ="报名|"+$scope.marketing.marketing_name;
			WxService.shareObj.desc = $scope.marketing.marketing_desc;
			WxService.shareObj.link = location.href;
				WxService.shareObj.imgUrl = "http://" + document.domain+$scope.marketing.marketing_icon;
			$scope.can_check = false;
			$scope.check_count = 0;
			$scope.can_reg = marketingInfo.can_reg;
			$scope.can_vote = marketingInfo.can_vote;
		
			$.each($scope.myClubs,function(i, n) {
								n.can_check = false;
								n.is_check = false;
								if ($scope.marketing.audiences == 1
										&& $scope.myClubs[0].member_sum > $scope.marketing.club_member_min) {
									n.can_check = true;
									$scope.can_check = true;
								}
							});
			if ($scope.myClubs_length > 0
					&& $scope.myClubs[0].can_check) {
				$scope.myClubs[0].is_check = true;
				$scope.check_count = 1;
				$scope.can_reg=true;
			}
			
			$scope.regClubs = marketingInfo.clubs;
			$scope.reg_length = marketingInfo.clubs.length;
			$scope.can_reg = marketingInfo.can_reg;

			$scope.check = function(obj, index, objs) {
				if (!obj.is_check && obj.can_check) {
					obj.is_check = true;
					$scope.check_count = 1;
					$.each(objs, function(i, n) {
						if (i != index && n.is_check) {
							n.is_check = false;
						}
					});
				}
			}

			$scope.share = function() {
				$("#marketingShareModal").modal();
				hideLoading();
				$("#marketing_modal_msg").modal("hide");
			}

			$scope.marketing_club_reg = function() {
				$.each($scope.myClubs,function(i, n) {
							if (n.is_check) {
										$("#marketing_modal_msg").modal("hide");
										showLoading();
										MarketingService.clubReg($scope.marketing.marketing_id,n.club_eng)
												.then(function(data) {
															hideLoading();
															if (data.status == 1) {
																$scope.regClubs.push(n);
																$scope.can_reg = false;
																var share = '<div class="container-fluid text-center">'
																		+ '<div class="col-xs-5 no-padding-right" style="width: 50px;height:50px"><img src="/image/club_default.png" class="thumb-circle" style="border:1px #54382b solid"></div>'
																		+ '<div class="col-xs-7">'
																		+ '<div class="container-fluid" style="padding:6px 0 0 0;line-height:50px;height:50px">'
																		+ '<div class="col-xs-12 list-item-title text-truncate text-left" style="line-height:24px;padding-left:4px">'
																		+ n.club_name
																		+ '</div>'
																		+ '<div class="col-xs-12  text-left" style="line-height:14px;padding-left:4px">'
																		+ '<small class="text-truncate text-muted">'
																		+ (n.club_slogan == null ? "": n.club_slogan)
																		+ '</small>'
																		+ '</div>'
																		+ '</div>'
																		+ '</div></div>'
																		+ '<h3 class="text-center" style="margin-top:50px"><button class="btn btn-default no-padding no-border" ng-click="share();" style="margin-bottom:25px"><img src="/image/marketing/share.png" style="height:25px"></button></h3>'
																var c = $compile(share)($scope);
																$("#marketing_modal_content").html(c);
																$("#marketing_modal_title").find(".modal-title").html("您已报名成功");
																$("#marketing_modal_title").show();
															} else {
																$("#marketing_modal_title").find(".modal-title").html("抱歉!!");
																$("#marketing_modal_content").html(data.msg+ '<h3 class="text-center"><button onclick="$(\'#marketing_modal_msg\').modal(\'hide\');" class="modal-sure btn btn-default" style="border-radius:10px;">确定</button></h3>');
																$("#marketing_modal_title").show();
															}
															$("#marketing_modal_msg").modal("show");
														});
									}
								});
			}

			$scope.reg = function() {
				if ($scope.myClubs_length == 0) {
					$("#marketing_modal_content").html('您还不是团长，赶快通知您的团长或者创建自己的跑团吧.<h3 class="text-center"><a href="/clubs/new" class="btn btn-default no-padding no-border" style="border-radius:10px;background-color:transparent"><img src="/image/marketing/create_club.png" style="height:25px"></a></h3>');
					$("#marketing_modal_msg").modal("show");
					$("#marketing_modal_title").find(".modal-title")
							.html("抱歉!!");
					$("#marketing_modal_title").show();

				} else if ($scope.myClubs_length >= 1) {
					var tmp = '<button type="button" class="close" style="margin-top:20px;" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><div class="container-fluid no-padding" id="club_list" style="margin-top:40px;overflow-y:scroll;height:180px;">'
							+ '<div class="col-xs-4 text-truncate" ng-repeat="club in myClubs" style="padding:4px 8px 4px 8px">'
							+ '<button ng-disabled="!club.can_check"  ng-click="check(club,$index,myClubs)" class="btn btn-default  no-border no-padding text-truncate">'
							+ ' <img ng-if="club.club_logo" ng-src="{{STATIC_IMG_PRE}}/{{club.club_logo}}!m.pre" class="thumb-circle" style="border:1px #54382b solid">'
							+ ' <img ng-if="!club.club_logo" ng-src="/image/club_default.png" class="thumb-circle" style="border:1px #54382b solid">'
							+ ' <br>'
							+ ' <small style="width:54px" class="text-truncate">{{club.club_name}}</small>'
							+ ' <img ng-if="club.is_check" src="/image/marketing/check.png" style="height:20px;position:absolute;top:30px;left:45px">'
							+ ' </button>'
							+ '</div>'
							+ ' </div><h3 class="text-center no-margin">'
							+ ' <button ng-if="can_check&&check_count==1" class="btn btn-default no-padding no-border" ng-click="marketing_club_reg();"><img src="/image/marketing/reg_now.png" style="height:25px"></button>'
							+ ' <button ng-if="!can_check||check_count==0" disabled="disabled" class="btn btn-default no-padding no-border" ><img src="/image/marketing/reg_now.png" style="height:25px"></button>'
							+ '  </h3>';
					var c = $compile(tmp)($scope);
					$("#marketing_modal_content").html(c);
					$("#marketing_modal_title").hide();
					$("#marketing_modal_msg").modal("show");
				} 
			}

			var createModal = '<div class="modal fade marketing-modal" id="marketing_modal_msg" data-show="false" role="dialog">'
					+ '<div class="modal-dialog"><div class="modal-content"><div class="modal-header text-center" id="marketing_modal_title">'
					+ '<h4 class="modal-title"  style="margin-top:50px">抱歉!!</h4></div><div class="modal-body" style="padding:8px 25px 8px 25px" id="marketing_modal_content">'
					+ '</div></div></div></div>';
			
			var shareModal = '<div id="marketingShareModal" style="width: 100%;background-color: rgba(0,0,0,0.5);z-index:8888;height:100%;position: fixed;top: 0;left: 0;text-align: center;display:none" onclick="this.style.display=\'none\'">'+
			                  '<div style="width: 100%;text-align: right;">'+
			                  '<img src="/image/marketing/share_arrow.png">'+
			                   '</div>'+
			                  '<div>'+
			                 '<img src="/image/share/zi.png" style="width:300px">'+
			                 '</div>'+
	                         '</div>';
			
			if ($("#marketingShareModal").length == 0) {
				$('body').append(shareModal);
			}
	                         
			if ($("#marketing_modal_msg").length == 0) {
				$('body').append(createModal);
				$("#marketing_modal_msg").modal();
				$('#marketing_modal_msg').on('hidden.bs.modal', function (e) {
					$(".modal-backdrop").remove();
				});
				$('#marketing_modal_msg').on('shown.bs.modal', function (e) {
					if($(".modal-backdrop").length==0){
						$('body').append('<div class="modal-backdrop fade in"></div>');
					}
				});
				
			}

		});

marketing_club
		.controller(
				'MarketingClubsVoteContorller',
				function($scope, $routeParams, WxService, UtilService,
						MarketingService, $rootScope, $compile,marketingInfo) {
					$scope.marketing = marketingInfo.marketing;
					WxService.shareObj.title ="投票|"+$scope.marketing.marketing_name;
					WxService.shareObj.desc = $scope.marketing.marketing_desc;
					WxService.shareObj.link = location.href;
					WxService.shareObj.imgUrl = "http://" + document.domain+$scope.marketing.marketing_icon;
					$("#rule_pre").html($scope.marketing.rule_desc);
					$scope.regClubs = marketingInfo.clubs;
					$scope.reg_length = marketingInfo.clubs.length;
					$scope.can_vote=marketingInfo.can_vote;
					$scope.voted = marketingInfo.voted;
					$scope.vote_sum=0;
                    $scope.votes = [];
					$scope.share = function() {
						$("#marketingShareModal").modal();
					}
					
					$scope.select = function(regClub) {
						if(!$scope.voted&&$scope.can_vote){
							if(regClub.selected){
								regClub.selected=false;
								if($scope.vote_sum>0){
									$scope.vote_sum--;
								}
							}else{
								if($scope.vote_sum<3){
									regClub.selected=true;
									$scope.vote_sum++;
								}
							}
						}
					}
					
					$scope.vote=function(){
						if($scope.vote_sum>0&&$scope.vote_sum<=3){
							var clubs = "";
							$.each($scope.regClubs,function(i,n){
								if(n.selected){
									clubs+=","+n.clubid;
								}
							});
							if(clubs.length>0){
								clubs = clubs.substring(1);
								MarketingService.clubVote(clubs,$scope.marketing.marketing_id).then(function(data){
									if(data.status==1){
										location.href="/wechat/#/marketing/club/voteSuccess?id="+$scope.marketing.marketing_id;
									}else{
										$("#marketing_modal_title").find(".modal-title").html("抱歉!!");
										$("#marketing_modal_content").html(data.msg+ '<h3 class="text-center"><button onclick="$(\'#marketing_modal_msg\').modal(\'hide\');" class="modal-sure btn btn-default" style="border-radius:10px;">确定</button></h3>');
										$("#marketing_modal_title").show();
										$("#marketing_modal_msg").modal("show");
									}
								});
							}
						}
					}

					var shareModal = '<div id="marketingShareModal" style="width: 100%;background-color: rgba(0,0,0,0.5);z-index:8888;height:100%;position: fixed;top: 0;left: 0;text-align: center;display:none" onclick="this.style.display=\'none\'">'+
					                  '<div style="width: 100%;text-align: right;">'+
					                  '<img src="/image/marketing/share_arrow.png">'+
					                   '</div>'+
					                  '<div>'+
					                 '<img src="/image/share/zi.png" style="width:300px">'+
					                 '</div>'+
			                         '</div>';
					
					var createModal = '<div class="modal fade marketing-modal" id="marketing_modal_msg" data-show="false" role="dialog">'
						+ '<div class="modal-dialog"><div class="modal-content"><div class="modal-header text-center" id="marketing_modal_title">'
						+ '<h4 class="modal-title" style="margin-top:50px">抱歉!!</h4></div><div class="modal-body" style="padding:8px 25px 8px 25px" id="marketing_modal_content">'
						+ '</div></div></div></div>';
					
					if ($("#marketingShareModal").length == 0) {
						$('body').append(shareModal);
					}
					
					if ($("#marketing_modal_msg").length == 0) {
						$('body').append(createModal);
						$("#marketing_modal_msg").modal();
					}

				});

marketing_club.controller(
		'MarketingClubsVoteSuccessContorller',
		function($scope, $routeParams, WxService, UtilService,
				MarketingService, $rootScope, $compile,rank) {
			$scope.marketing = rank.marketing;
			WxService.shareObj.title ="人气榜 |"+$scope.marketing.marketing_name;
			WxService.shareObj.desc = $scope.marketing.marketing_desc;
			WxService.shareObj.link = location.href;
			WxService.shareObj.imgUrl = "http://" + document.domain+$scope.marketing.marketing_icon;
			$scope.voteClubs = rank.clubs;
			$scope.myVotes = rank.my_votes;
			$scope.myVotes_length =$scope.myVotes.length; 
			$scope.vote_length = $scope.voteClubs.length;
			$scope.can_vote = rank.can_vote;
			$scope.go_mileage=rank.go_mileage;
		});

marketing_club.controller(
		'MarketingClubsResultContorller',
		function($scope, $routeParams, WxService, UtilService,
				MarketingService, $rootScope, $compile,rank) {
			$scope.marketing = rank.marketing;
			WxService.shareObj.title ="跑量榜 |"+$scope.marketing.marketing_name;
			WxService.shareObj.desc = $scope.marketing.marketing_desc;
			WxService.shareObj.link = location.href;
			WxService.shareObj.imgUrl = "http://" + document.domain+$scope.marketing.marketing_icon;
			$scope.voteClubs = rank.clubs;
			$scope.myVotes = rank.my_votes;
			$scope.myVotes_length =$scope.myVotes.length; 
			$scope.vote_length = $scope.voteClubs.length;
		});