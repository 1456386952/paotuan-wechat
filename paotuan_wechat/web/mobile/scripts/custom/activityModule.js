var ActivityModule = angular.module('activity', ['ngAnimate', 'ngSanitize', 'ui.router', 'chieffancypants.loadingBar', 'ngDialog', 'ngTouch', 'mgcrea.ngStrap']);

ActivityModule.config(['$stateProvider', '$urlRouterProvider', '$httpProvider', '$locationProvider', 'cfpLoadingBarProvider', function($stateProvider, $urlRouterProvider, $httpProvider, $locationProvider, cfpLoadingBarProvider) {
    //set router
    // $urlRouterProvider.otherwise("/index");
    $stateProvider
        // 跑团主页
        .state('index', {
            url: '/index',
            templateUrl: 'tpl/index.html',
            controller: 'ActivityController'
        })

        // 活动列表页
        .state('index.activity', {
            url: '/activity?club_eng&uid&type',
            templateUrl: 'tpl/index.activity.html',
            controller: 'ActivityController'
        })

        // 活动信息页
        .state('activityInfo', {
            url: '/activity/info?act_id',
            templateUrl: 'tpl/activity.info.html',
            controller: 'ActivityInfoController'
        })

        // 活动详情页
        .state('activityDetail', {
            url: '/activity/detail?type&act_id&access_token',
            templateUrl: 'tpl/activity.detail.html',
            controller: 'ActivityDetailController'
        })

        // 报名信息页面
        .state('regInfo', {
            url: '/activity/reginfo?act_id&pro_id',
            templateUrl: 'tpl/reg.info.html',
            controller: 'RegInfoController'
        })

        // 支付页
        .state('pay', {
            url: '/pay?act_id',
            templateUrl: 'tpl/pay.html',
            controller: 'PayController'
        })

        // 支付成功页
        .state('paysuccess', {
            url: '/paysuccess?order_id',
            templateUrl: 'tpl/paysuccess.html',
            controller: 'OrderDetailController'
        })

        // 支付失败页
        .state('payerr', {
            url: '/payerr',
            templateUrl: 'tpl/payerr.html'
        })

        // 订单详情页
        // .state('orderDetail', {
        //     url: '/order/detail',
        //     templateUrl: 'tpl/order.detail.html',
        //     controller: 'OrderDetailController'
        // })

        // 新建活动页
        .state('newActivity', {
            url: '/new/activity',
            templateUrl: 'tpl/copy.activity.html',
            controller: 'CopyActivityController'
        })

        // 复制活动页
        .state('copyActivity', {
            url: '/copy/activity?act_id&type',
            templateUrl: 'tpl/copy.activity.html',
            controller: 'CopyActivityController'
        })

        // 编辑活动页
        .state('editActivity', {
            url: '/edit/activity?act_id&type',
            templateUrl: 'tpl/copy.activity.html',
            controller: 'CopyActivityController'
        })

        // 评论列表页
        .state('comment', {
            url: '/activity/comment',
            templateUrl: 'tpl/activity.comment.html',
            controller: 'ActivityCommentController'
        })

        // 签到、报名列表页
        .state('runnerlist', {
            abstract: true,
            url: '/activity/runnerlist',
            templateUrl: 'tpl/activity.runnerlist.html',
            controller: 'RunnerlistController'
        })
        .state('runnerlist.reg', {
            url: '/reg',
            templateUrl: 'tpl/activity.runnerlist.reg.html',
            controller: 'RunnerlistController'
        })
        .state('runnerlist.sign', {
            url: '/sign',
            templateUrl: 'tpl/activity.runnerlist.sign.html',
            controller: 'RunnerlistController'
        })

        // 活动提醒页
        .state('sendmessage', {
            url: '/send/message',
            templateUrl: 'tpl/send.message.html',
            controller: 'SendMessageController'
        })

        // 导出邮箱
        .state('sendemail', {
            url: '/send/email',
            templateUrl: 'tpl/send.email.html',
            controller: 'SendEmailController'
        })

        // 绑定邮箱
        .state('bindemail', {
            url: '/bind/email?back',
            templateUrl: 'tpl/bind.email.html',
            controller: 'BindEmailController'
        })

        // 跑友资料
        .state('runnerinfo', {
            url: '/runner/info?act_id&uid',
            templateUrl: 'tpl/runner.info.html',
            controller: 'RunnerInfoController'
        })

        // 导出邮箱
        .state('exportemail', {
            url: '/exportemail',
            templateUrl: 'tpl/exportemail.html',
            controller: 'exportemailController'
        })

        // 发起活动
        .state('launchactivity', {
            url: '/launchactivity?club_eng&uid&type',
            templateUrl: 'tpl/launchactivity.html',
            controller: 'LaunchActivityController'
        })

        // 发起活动-高级设置
        .state('advancedsettings', {
            url: '/advancedsettings',
            templateUrl: 'tpl/advancedsettings.html',
            controller: 'AdvancedSettingsController'
        })

        // 发起活动-高级设置-新建项目
        .state('activityproject', {
            url: '/activityproject',
            templateUrl: 'tpl/activityproject.html',
            controller: 'AddProjectController'
        })

        // 签到设备
        .state('signequipment', {
            url: '/signequipment',
            templateUrl: 'tpl/signequipment.html',
            controller: 'signequipmentController'
        })

        // 搜索页
        .state('search', {
            url: '/search',
            templateUrl: 'tpl/search.html',
            controller: 'searchController'
        })

        // 签到信息
        .state('signinfo', {
            url: '/signinfo?newact',
            templateUrl: 'tpl/signinfo.html',
            controller: 'signinfoController'
        });

    // $locationProvider.html5Mode(true);
    
    // true is the default, but I left this here as an example:
    cfpLoadingBarProvider.includeSpinner = true;
    // cfpLoadingBarProvider.includeBar = false;
    // cfpLoadingBarProvider.spinnerTemplate = '<div class="test"><div id="loading-bar-spinner"><div class="spinner-icon"></div></div></div>';
    
}]);