var App = angular.module('App', ['login', 'activity']);

App.config(['$httpProvider', '$datepickerProvider', function($httpProvider, $datepickerProvider) {
    
    // Use x-www-form-urlencoded Content-Type
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function(data) {
        /**
        * The workhorse; converts an object to x-www-form-urlencoded serialization.
        * @param {Object} obj
        * @return {String}
        */
        var param = function(obj) {
            var query = '';
            var name, value, fullSubName, subName, subValue, innerObj, i;

            for(name in obj) {
                value = obj[name];

                if(value instanceof Array) {
                    for(i=0; i<value.length; ++i) {
                        subValue = value[i];
                        fullSubName = name + '[' + i + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                }
                else if (value instanceof Object) {
                    for(subName in value) {
                        subValue = value[subName];
                        fullSubName = name + '[' + subName + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                }
                else if(value !== undefined && value !== null) {
                    query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
                }
            }

            return query.length ? query.substr(0, query.length - 1) : query;
        };

        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];

    angular.extend($datepickerProvider.defaults, {
        startWeek: 1,
        startDate: new Date(),
        disabledDates: { 
            end: new Date()
        },
        iconLeft: 'fa fa-chevron-left',
        iconRight: 'fa fa-chevron-right'
    });

    //注入拦截器
    $httpProvider.interceptors.push('timestampMarker');

}]);


App.run(['$rootScope', '$location', function($rootScope, $location) {
    /* 获取用户登录标识 */
    // var access_token = store.get('access_token');

    // $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams){
    //     if (!access_token) {
    //         $location.path('/login')
    //     }
    //     console.log(toState)
    // });
    // $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams){
    //     console.log(toParams)

    //     var URL = 'http://wechat.runningtogether.net';
    //     var title, desc, link, imgUrl;

    //     // 判断路由，设置分享内容
    //     if ($location.$$path == '/activity/detail') {
    //         title  = '活动详情',
    //         desc   = '活动详情内容',
    //         link   = URL + '/mobile/#/activity/detail?type=share&act_id=' + toParams.act_id,
    //         imgUrl = URL + '/mobile/images/logo.png';
    //     } else {
    //         title  = '我的个人中心',
    //         desc   = '和未来的自己一起跑步去',
    //         link   = URL + '/wechat/#/runner/me',
    //         imgUrl = URL + '/mobile/images/logo.png';
    //     }

    //     // 自定义分享接口    
    //     sns(title, desc, link, imgUrl);
    // });

}]);