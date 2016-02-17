var LoginModule = angular.module('login', ['ngAnimate', 'ui.router', 'ngDialog']);

LoginModule.config(['$stateProvider', '$urlRouterProvider', '$httpProvider', function($stateProvider, $urlRouterProvider, $httpProvider) {
    //set router
    // $urlRouterProvider.otherwise("/login");
    $stateProvider
        .state('login', {
            url: '/login',
            templateUrl: 'tpl/login.html',
            controller: 'LoginController'
        })
        .state('register', {
            url: '/register',
            templateUrl: 'tpl/register.html',
            controller: 'registerController'
        })
        .state('lostpw', {
            url: '/lostpw',
            templateUrl: 'tpl/lostpw.html',
            controller: 'lostpwController'
        })
        .state('paotuanlist', {
            url: '/paotuanlist',
            templateUrl: 'tpl/paotuanlist.html',
            controller: 'paotuanListController'
        })
        .state('bulid', {
            url: '/bulid',
            templateUrl: 'tpl/bulid.html',
            controller: 'bulidController'
        })
        .state('discovery', {
            url: '/discovery',
            templateUrl: 'tpl/discovery.html',
            controller: 'discoveryController'
        })
        .state('inchecklist', {
            url: '/inchecklist',
            templateUrl: 'tpl/inchecklist.html',
            controller: 'inchecklistController'
        })
        .state('bindphone', {
            url: '/bindphone',
            templateUrl: 'tpl/bindphone.html',
            controller: 'registerController'
        });
}]);

//登录控制器
LoginModule.controller('LoginController', ['$rootScope', '$scope', '$http', '$state', '$location', 'httpService', 'ngDialog', function($rootScope, $scope, $http, $state, $location, httpService, ngDialog) {
     
    /* 获取用户登录标识 */
    var access_token = store.get('access_token');
    // 如果已经登录跳转到跑团列表页面
    // if (access_token) {
    //     $state.go('paotuanlist');
    // }

    /* 表单数据 */
    $scope.user = {
        account: '',
        password: ''
    };

    var ua = window.navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i)=="micromessenger") {
        $scope.isWechat = true;
    } else {
        $scope.isWechat = false;
    }

    /* 提交表单 */
    $scope.submitForm = function(isValid) {
        if (!isValid) {

            //弹出err信息
            $scope.errmessage = '验证失败';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        if (isValid) {

            /* 拼接POST数据格式 */
            var params = {
                'phone': $scope.user.account,
                'password': $scope.user.password
            }

            /* 登录验证 */
            httpService.postLogin(params)
            .then(function(data){

                /* access-token存在，则缓存起来，并进入跑团列表 */
                if (data.message.access_token) {
                    store.set('access_token', data.message.access_token);
                    $state.go('paotuanlist');
                } else {

                    //弹出err信息
                    $scope.errmessage = data.message;
                    ngDialog.open({
                        template: '<p>{{errmessage}}</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true,
                        scope: $scope
                    });
                }

                console.log(data);
            }, function(data) {
                console.log('error');
            });
        }
    };

}]);

//注册控制器   
LoginModule.controller('registerController', ['$rootScope', '$scope', '$http', '$state', 'httpService', 'ngDialog', function($rootScope, $scope, $http, $state, httpService, ngDialog) {
    // alert(location.hash.indexOf('type='))
    // alert(location.hash.substr(location.hash.indexOf('type=') + 5, 5));
    // alert(location.hash.substr(location.hash.indexOf('&') + 14));

    //微信登录场合
    if ((location.hash.indexOf('type=') != -1) && (location.hash.substr(location.hash.indexOf('type=') + 5, 5) != 'login')) {
        //type=login 的场合 显示绑定手机页面
        //跳转到跑团列表页面
        /* access-token，缓存起来，并进入跑团列表 */
        store.set('access_token', location.hash.substr(location.hash.indexOf('&') + 14));
        $state.go('paotuanlist');
    }

    /* 获取手机验证码 */
    var realCode = null;
    // 获取验证码的手或邮箱
    var realname = null;

    $rootScope.isloading = true;

    $scope.getCode = function() {

        //获取验证码之前判断用户名是否输入
        if ($scope.user.name == '') {
            //弹出err信息
            $scope.errmessage = '请输入用户名';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return;
        }
        //验证手机号码
        if (!!$scope.user.name.match(/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/)) {

            /* 拼接POST数据格式 */
            var params = {
                'phone': $scope.user.name
            };
            // 手机验证码验证
            httpService.postPhoneCode(params)
            .then(function(data){
                if (data.code == '1') {
                    //获取成功的场合
                    realCode = data.message; //缓存验证码
                    realname = $scope.user.name; //缓存获取验证码的手机或邮箱
                } else {
                    // 错误的场合
                    //弹出err信息
                    $scope.errmessage = data.message;
                    ngDialog.open({
                        template: '<p>{{errmessage}}</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true,
                        scope: $scope
                    });
                }

                console.log(data);
            }, function(data) {
                //弹出err信息
                $scope.errmessage = data.message;
                ngDialog.open({
                    template: '<p>{{errmessage}}</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true,
                    scope: $scope
                });
                console.log('error');
            });

        } else if (!!$scope.user.name.match(/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/)) {
            //验证邮箱
            /* 拼接POST数据格式 */
            var params = {
                'email': $scope.user.name
            };
            // 邮箱验证码验证
            httpService.postEmailCode(params)
            .then(function(data){
                realCode = data.message; //缓存验证码
                realname = $scope.user.name; //缓存获取验证码的手机或邮箱
                console.log(data);
            }, function(data) {
                //弹出err信息
                $scope.errmessage = data.message;
                ngDialog.open({
                    template: '<p>{{errmessage}}</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true,
                    scope: $scope
                });
                console.log('error');
            });
        } else {
            //弹出err信息
            $scope.errmessage = '请输入正确的手机';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
        }

        return false;
    };

    /* 表单数据 */
    $scope.user = {
        name: '',
        pinCode: '',
        passworld: '',
        passworld_again: ''
    };

    /* 提交表单 */
    $scope.submitForm = function(isValid, bindphoneflag) {
        if (realCode == null) {
            //弹出err信息
            $scope.errmessage = '请获取验证码!';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }
        // 与获取的验证码的手机或邮箱的不同的场合
        if (realname != $scope.user.name) {
            //弹出err信息
            $scope.errmessage = '修改后请重新验证手机号码！';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        //匹配验证码
        var isCode = $scope.user.pinCode == realCode ? true : false;

        //验证失败
        if (!isValid||!isCode) {
            //弹出err信息
            $scope.errmessage = '验证码错误';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }
        var objReg = /^[0-9|A-Z|a-z]*$/;
        if (!objReg.test($scope.user.passworld)) {
            $scope.errmessage = '请输入符合格式的密码';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        //比较两次密码是否相同
        if ($scope.user.passworld != $scope.user.passworld_again) {
            //弹出err信息
            $scope.errmessage = '请输入相同的密码';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        if (isValid) {
            /* 拼接POST数据格式 */
            var params = {
                'user_name': $scope.user.name,
                'code': $scope.user.pinCode,
                'password': $scope.user.passworld
            };
            if (bindphoneflag) {
                alert("调用绑定手机接口");

            } else {
                /* 注册验证 */
                httpService.postRegister(params)
                .then(function(data){

                    if (data.message.access_token) {
                        /* access-token，缓存起来，并进入跑团列表 */
                        store.set('access_token', data.message.access_token);
                        //暂定跑团列表，以后会改到跑团主页
                        $state.go('paotuanlist');

                        ngDialog.open({
                            template: '<p>注册成功</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true,
                            scope: $scope
                        });

                    } else {
                        //弹出err信息
                        $scope.errmessage = data.message;
                        ngDialog.open({
                            template: '<p>{{errmessage}}</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true,
                            scope: $scope
                        });
                    }

                    console.log(data)
                }, function(data) {
                   //弹出err信息
                    $scope.errmessage = data.message;
                    ngDialog.open({
                        template: '<p>{{errmessage}}</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true,
                        scope: $scope
                    });
                    console.log('error')
                }); 

            }

        }
    };
}]);

//忘记密码控制器
LoginModule.controller('lostpwController', ['$rootScope', '$scope', '$http', '$state', 'httpService','ngDialog', function($rootScope, $scope, $http, $state, httpService, ngDialog) {
    /* 表单数据 */
    $scope.user = {
        name: '',
        pinCode: '',
        passworld: '',
        passworld_again: ''
    };
    
    /* 获取手机验证码 */
    var realCode = null;
    // 获取验证码的手或邮箱
    var realname = null;

    $rootScope.isloading = true;

    $scope.getCode = function() {

        //获取验证码之前判断用户名是否输入
        if ($scope.user.name == '') {
           //弹出err信息
            $scope.errmessage = '请输入用户名';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return;
        }

        if (!!$scope.user.name.match(/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/)) {

            /* 拼接POST数据格式 */
            var params = {
                'phone': $scope.user.name
            };
            // 手机验证码验证
            httpService.postPhoneCode(params)
            .then(function(data){
                realCode = data.message; //缓存验证码
                realname = $scope.user.name; //缓存获取验证码的手机或邮箱
                console.log(data);
            }, function(data) {
                //弹出错误信息
                $scope.errmessage = data.message;
                ngDialog.open({
                    template: '<p>{{errmessage}}</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true,
                    scope: $scope
                });
                console.log('error');
            });

        } else if (!!$scope.user.name.match(/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/)) {
            
            /* 拼接POST数据格式 */
            var params = {
                'email': $scope.user.name
            };
            // 邮箱验证码验证
            httpService.postEmailCode(params)
            .then(function(data){
                realCode = data.message; //缓存验证码
                realname = $scope.user.name; //缓存获取验证码的手机或邮箱
                console.log(data);
            }, function(data) {
               //弹出err信息
                $scope.errmessage = data.message;
                ngDialog.open({
                    template: '<p>{{errmessage}}</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true,
                    scope: $scope
                });
                console.log('error');
            });
        } else {
           //弹出err信息
            $scope.errmessage = '修改后请输入正确的手机或';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
        }
        return false;
    }

   /* 提交表单 */
    $scope.submitForm = function(isValid) {
        //没有获取验证码的时候message
        if (realCode == null) {
           //弹出err信息
            $scope.errmessage = '请获取验证码';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        // 与获取的验证码的手机或邮箱的不同的场合
        if (realname != $scope.user.name) {
           //弹出err信息
            $scope.errmessage = '请重新验证手机号码！';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        //匹配验证码
        var isCode = $scope.user.pinCode == realCode ? true : false;

        //验证失败
        if (!isValid||!isCode) {
           //弹出err信息
            $scope.errmessage = '验证码错误';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }
        var objReg = /^[0-9|A-Z|a-z]*$/;
        if (!objReg.test($scope.user.passworld)) {
            $scope.errmessage = '请输入符合格式的密码';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        //比较两次密码是否相同
        if ($scope.user.passworld != $scope.user.passworld_again) {
           //弹出err信息
            $scope.errmessage = '请输入相同的密码';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });

            return false;
        }

        if (isValid) {

            /* 拼接POST数据格式 */
            var params = {

                'user_name': $scope.user.name,
                'code': $scope.user.pinCode,
                'new_password': $scope.user.passworld
            }

            /* 修改密码验证 */
            /* 注册验证 */
            httpService.postResetPWD(params)
            .then(function(data){
                //成功的场合
                if (data.code == '1') {
                    //弹出err信息
                    ngDialog.open({
                        template: '<p>修改密码成功</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true,
                        scope: $scope
                    });
                    //回登陆界面
                    $state.go('login');
                } else {
                    //失败的场合
                    //弹出err信息
                    $scope.errmessage = data.message;
                    ngDialog.open({
                        template: '<p>{{errmessage}}</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true,
                        scope: $scope
                    });
                }
                console.log(data)
            }, function(data) {
                console.log('error')
            }); 
        }
    };
}]);

//跑团列表控制器
LoginModule.controller('paotuanListController', ['$scope', '$http', '$state', 'httpService','ngDialog', function($scope, $http, $state, httpService, ngDialog) {
    
    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    $scope.pageClass = true;    //pageClass
    $scope.busy      = false;   //loading隐藏
    $scope.offset    = 0;       //offset
    $scope.limit     = 10;      //limit
    $scope.pageslen  = 1;       //数据长度

    /* 首次加载跑团列表 */
    $scope.load = function() {
        if ($scope.busy) {
            return false;
        }

        $scope.busy = true;  //loading显示

        /* 拼接POST数据格式 */
        var params = {
            'offset': $scope.offset,
            'limit': $scope.limit
        };

        httpService.postClubList(access_token, params)
        .then(function(data){

            if (angular.isArray(data.message)) {
                $scope.paotuanListshow = true; // 跑团列表显示
                $scope.busy = false; //loading隐藏
                $scope.paotuanListInfo = data.message;
                $scope.pageslen = data.message.length;

                console.log(data)
                console.log($scope.paotuanListInfo)
            } else {
                $scope.paotuanListshow = false; // 跑团列表隐藏
            }
            
        }, function(data) {
            console.log('error')
        }); 

    };

    $scope.load();

    /* 滚动加载跑团列表 */
    $scope.loadMore = function() {
        if ($scope.pageslen<$scope.limit) {
            $scope.last = true;
        }
        if ($scope.pageslen==$scope.limit) {
            $scope.offset++;
            if ($scope.busy) {
                return false;
            }

            $scope.busy = true; //loading显示

            /* 拼接POST数据格式 */
            var params = {
                'offset': $scope.offset,
                'limit': $scope.limit
            };

            httpService.postClubList(access_token, params)
            .then(function(data){

                if (angular.isArray(data.message)) {

                    $scope.busy = false; //loading隐藏

                    angular.forEach(data.message, function(value, index) {
                        $scope.paotuanListInfo.push(value)
                    });

                    $scope.pageslen = data.message.length;

                    console.log(data)
                    console.log($scope.paotuanListInfo)
                }

            }, function(data) {
                console.log('error')
            }); 

        }
    };

    /* 跳转路由至活动列表，并缓存当前跑团信息 */
    $scope.activity = function(item) {

        store.remove('actInfo');

        store.set('clubInfo', item);

        console.log(store.getAll());

        $state.go('index.activity');
    }; 
}]);

//发现跑团控制器
LoginModule.controller('discoveryController', ['$scope', '$http', '$state', '$timeout', 'httpService', 'mapService', 'ngDialog', 'fileReader', function($scope, $http, $state, $timeout, httpService, mapService, ngDialog, fileReader) {

    // 当前城市名
    var cityname = remote_ip_info["city"];

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');
    if (!access_token) return;

    /* 表单数据 */
    $scope.search = {
        searchdata: '',
        searched: false,
        //真实地址
        cityname: cityname
    };

    /* 拼接POST数据格式 */
    var params = {
        'city': $scope.search.cityname,
        'name': $scope.search.searchdata
        }

    /* 执行搜索 */
    httpService.postfindcityclub(access_token, params)
    .then(function(data){

        $scope.paotuanListInfo = data.message;
        console.log(data)

       }, function(data) {
        console.log('error')
     }); 


    /* 提交表单 */
    $scope.submitForm = function(isValid) {

        if (isValid) {

            /* 拼接POST数据格式 */
            var params = {
                'city': $scope.search.cityname,
                'name': $scope.search.searchdata
            }

            /* 执行搜索 */
            httpService.postfindcityclub(access_token, params)
            .then(function(data_searched){

                //没有搜索到跑团的场合
                if (!angular.isArray(data_searched.message)) {
                    $scope.errmessage = data_searched.message;
                    ngDialog.open({
                        template: '<p>{{errmessage}}</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true,
                        scope: $scope
                    });

                } else {
                    //显示搜索到的跑团信息
                    //隐藏附近跑团信息
                    $scope.search.searched = true;

                     $scope.search_paotuanListInfo = data_searched.message;
                }
                
                console.log(data_searched)

               }, function(data_searched) {
                console.log('error')
             }); 

        }
    };

    /* 跳转路由至活动列表，并缓存当前跑团信息 */
    $scope.activity = function(item) {
        store.set('clubInfo', item);
        $state.go('index.activity');
    }; 

    $scope.onfocus = function() {
        //更多搜索条件显示flag(true：显示；false：不显示，初始值：true)
        $scope.search.searched = false;
    };

}]);

//创建跑团控制器
LoginModule.controller('bulidController', ['$rootScope', '$scope', '$http', '$state', '$timeout', 'httpService', 'mapService', 'ngDialog', 'fileReader', function($rootScope, $scope, $http, $state, $timeout, httpService, mapService, ngDialog, fileReader) {
    
   /* 获取用户登录标识 */
    var access_token = store.get('access_token');
    if (!access_token) return;

    /* 表单数据 */
    $scope.paotuanInfo = {
        name: '',
        logo: '',
        bg_logo: '',
        add: '',
        info: ''
    };
    /* 提交表单 */
    $scope.submitForm = function(isValid) {

        if ($scope.myFile == undefined) {

            $scope.errmessage ='请上传跑团logo';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return;

        } else if ($scope.myFilebg == undefined) {

            $scope.errmessage ='请上传跑团背景';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return;
        }

        if (isValid) {
            /* 拼接POST数据格式 */
            var params = {
                club_name: $scope.paotuanInfo.name,
                club_logo: $scope.myFile,
                club_bgimage: $scope.myFilebg,
                city_name: $scope.paotuanInfo.add,
                club_desc: $scope.paotuanInfo.info
            }
            
            /* 创建跑团 */
            httpService.postBulidPaotuan(access_token, params)
            .then(function(data){

                if (data.code == '1') {
                    //弹出err信息
                    $rootScope.errmessage = '创建成功';
                    ngDialog.open({
                        template: '<p>{{errmessage}}</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true,
                        scope: $rootScope
                    });
                    //回到跑团列表界面
                    $state.go('paotuanlist');
                } else if (data.code == '0') {
                    //弹出err信息
                    $rootScope.errmessage = data.message;
                    ngDialog.open({
                        template: '<p>{{errmessage}}</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true,
                        scope: $rootScope
                    });
                }
                console.log(data);

               }, function(data) {
                console.log('error');
             });
        }
    };


    /* 获取上传图片的地址url，用以实现预览图片 */
    $scope.getFile = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {

            //判断附件是否为图片
            if(!/image\/\w+/.test($scope.file.type)){

                ngDialog.open({
                    template: '<p>图片类型必须是.gif,jpeg,jpg,png中的一种!!!</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true
                });
                
                return false;
            } else {
                $scope.imageSrc = result;
            } 
        });
    };

    /* 获取上传跑团背景图片的地址url，用以实现预览图片 */
    $scope.getfile2 = function () {
            fileReader.readAsDataUrl($scope.file, $scope)
            .then(function(result) {

                //判断附件是否为图片
                if(!/image\/\w+/.test($scope.file.type)){

                    ngDialog.open({
                        template: '<p>图片类型必须是.gif,jpeg,jpg,png中的一种!!!</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true
                    });
                    
                    return false;
                } else {
                    $scope.imageSrcbg = result;
                } 
            });
        };

}]);

//搜索活动控制器
LoginModule.controller('searchController', ['$scope', '$http', '$state', 'httpService', 'dateFormat','ngDialog', function($scope, $http, $state, httpService, dateFormat, ngDialog) {
    
    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前跑团信息 */
    $scope.clubInfo = store.get('clubInfo');

    /* 获取当前跑团ID */
    $scope.clubid = store.get('clubInfo').clubid;

    //更多搜索条件显示flag(true：显示；false：不显示，初始值：true)
    $scope.moredisplayflag = true;
    //按地址搜索显示flag(true：显示；false：不显示，初始值：true)
    $scope.searchbyadddisplayflag = true;
    // 按名称搜索的活动列表(true：显示；false：不显示，初始值：false)
    $scope.searchByNameflag = false;
    //导出邮箱按钮初始化隐藏
    $scope.exportemailbtnflag = false;


    var params = {
       'club_id': $scope.clubid
       //测试用临时
       // 'club_id': '19'
    }

    // 初期化的场合
    /* 搜索该跑团活动 */
    httpService.postFindLocation(access_token, params)
    .then(function(data){

        $scope.addInfoList = data.message;
        console.log(data)
    }, function(data) {
        console.log('error')
    }); 



    // 点击初期化时的跑团列表的场合
    /* 按地址搜索活动 */
    $scope.searchByAdd = function(item) {

        /* 拼接POST数据格式 */
        var params = {
            'club_id': $scope.clubid,
            'name': '',
            'location': item
        };

        /* 搜索该跑团活动 */
        httpService.activitysearchUrL(access_token, params)
        .then(function(data){

            /* 格式化时间 */
            dateFormat.format(data.message);

            if (data.message[0].act_title != undefined) {
                //更多搜索条件不显示
                $scope.moredisplayflag = false;
                //按地址搜索不显示
                $scope.searchbyadddisplayflag = false;
                // 按名称搜索的活动列表显示
                $scope.searchByNameflag = true;
                //导出邮箱按钮显示
                $scope.exportemailbtnflag = true;
                $scope.activityList = data.message;
            } else {

                //弹出err信息
                $scope.errmessage = data.message;
                ngDialog.open({
                    template: '<p>{{errmessage}}</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true,
                    scope: $scope
                });
            }

        }, function(data) {
            console.log('error')
        });
    };

    // 点击搜索按钮的场合
    //按名字查询活动
    $scope.searchByName = function() {

        /* 拼接POST数据格式 */
        var params = {
            'club_id': $scope.clubid,
            'name': $scope.name,
            'location': ''
        };

        /* 搜索该跑团活动 */
        httpService.activitysearchUrL(access_token, params)
        .then(function(data){

            /* 格式化时间 */
            dateFormat.format(data.message);

            if (data.message[0].act_title != undefined) {
                //更多搜索条件显示flag(true：显示；false：不显示，初始值：true)
                $scope.moredisplayflag = false;
                //按地址搜索显示flag(true：显示；false：不显示，初始值：true)
                $scope.searchbyadddisplayflag = false;
                // 按名称搜索的活动列表(true：显示；false：不显示，初始值：false)
                $scope.searchByNameflag = true;
                //导出邮箱按钮显示
                $scope.exportemailbtnflag = true;

                $scope.activityList = data.message;
            } else {

                //弹出err信息
                $scope.errmessage = data.message;
                ngDialog.open({
                    template: '<p>{{errmessage}}</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true,
                    scope: $scope
                });
            }
            

            console.log(data)
        }, function(data) {
            console.log('error')
        });
    };

    //点击条件搜索活动
    $scope.searchAllActivity = function(item) {

        /* 拼接POST数据格式 */
        var params = {
            clubid: $scope.clubid
        }

        /* 搜索该跑团活动 */
        httpService.postActList(access_token, params)
        .then(function(data){

            /* 格式化时间 */
            angular.forEach(data.message, function(value, index){
                dateFormat.format(value)
            });

            //更多搜索条件显示flag(true：显示；false：不显示，初始值：true)
            $scope.moredisplayflag = false;
            //按地址搜索显示flag(true：显示；false：不显示，初始值：true)
            $scope.searchbyadddisplayflag = false;
            // 按名称搜索的活动列表(true：显示；false：不显示，初始值：false)
            $scope.searchByNameflag = true;
            //导出邮箱按钮显示
            $scope.exportemailbtnflag = true;

            if (item == 'all') {
                //添加活动状态
                angular.forEach(data.message.incheck, function(value, index){
                    value['type'] = 'incheck';
                });
                angular.forEach(data.message.incheck, function(value, index){
                    value['type'] = 'inregister';
                });
                angular.forEach(data.message.incheck, function(value, index){
                    value['type'] = 'nostart';
                });
                angular.forEach(data.message.incheck, function(value, index){
                    value['type'] = 'inend';
                });
                angular.forEach(data.message.incheck, function(value, index){
                    value['type'] = 'iscancel';
                });

               $scope.activityList = data.message.incancel.concat(data.message.incheck).concat(data.message.inend).concat(data.message.inregister).concat(data.message.nostart);

                if ($scope.activityList.length == 0) {
                    //弹出err信息
                    $scope.errmessage = '没有要查找的活动';
                    ngDialog.open({
                        template: '<p>{{errmessage}}</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true,
                        scope: $scope
                    });

                    //恢复初期显示状态
                    initialStage();
                    return;
                }
                console.log(data)

            } else {

                if (item == 'incheck') {
                     if (data.message.incheck[0] != undefined) {
                        //添加活动状态
                        angular.forEach(data.message.incheck, function(value, index){
                            value['type'] = 'incheck';
                        });

                        $scope.activityList = data.message.incheck;
                     } else {

                        //弹出err信息
                        $scope.errmessage = '没有要查找的活动';
                        ngDialog.open({
                            template: '<p>{{errmessage}}</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true,
                            scope: $scope
                        });

                        //恢复初期显示状态
                        initialStage();
                        return;
                    }
                } else if (item == 'inregister') {
                    //正在报名
                     if (data.message.inregister[0] != undefined) {
                        //添加活动状态
                        angular.forEach(data.message.incheck, function(value, index){
                            value['type'] = 'inregister';
                        });

                        $scope.activityList = data.message.inregister;
                     } else {

                        //弹出err信息
                        $scope.errmessage = '没有要查找的活动';
                        ngDialog.open({
                            template: '<p>{{errmessage}}</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true,
                            scope: $scope
                        });

                        //恢复初期显示状态
                        initialStage();
                        return;
                    }
                } else if (item == 'nostart') {
                    //未开始
                     if (data.message.nostart[0] != undefined) {
                        //添加活动状态
                        angular.forEach(data.message.incheck, function(value, index){
                            value['type'] = 'nostart';
                        });

                        $scope.activityList = data.message.nostart;
                     } else {

                        //弹出err信息
                        $scope.errmessage = '没有要查找的活动';
                        ngDialog.open({
                            template: '<p>{{errmessage}}</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true,
                            scope: $scope
                        });

                        //恢复初期显示状态
                        initialStage();
                        return;
                    }
                } else if (item == 'inend') {
                    //已经结束
                     if (data.message.inend[0] != undefined) {
                        //添加活动状态
                        angular.forEach(data.message.incheck, function(value, index){
                            value['type'] = 'inend';
                        });

                        $scope.activityList = data.message.inend;
                     } else {

                        //弹出err信息
                        $scope.errmessage = '没有要查找的活动';
                        ngDialog.open({
                            template: '<p>{{errmessage}}</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true,
                            scope: $scope
                        });
                       //恢复初期显示状态
                        initialStage();
                        return;
                    }
                } else if (item == 'iscancel') {
                    //已取消
                     if (data.message.iscancel[0] != undefined) {
                        //添加活动状态
                        angular.forEach(data.message.incheck, function(value, index){
                            value['type'] = 'iscancel';
                        });

                        $scope.activityList = data.message.iscancel;
                     } else {

                        //弹出err信息
                        $scope.errmessage = '没有要查找的活动';
                        ngDialog.open({
                            template: '<p>{{errmessage}}</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true,
                            scope: $scope
                        });
                        //恢复初期显示状态
                        initialStage();
                        return;
                    }
                }

            }
            console.log(data)
        }, function(data) {
            console.log('error')
        });
    };

    //恢复初期显示
    function initialStage() {
        $scope.searchbyadddisplayflag = true;
        $scope.moredisplayflag = true;
        $scope.searchByNameflag = false;
        $scope.exportemailbtnflag = false;
    }

    /* 跳转路由至activityInfo */
    $scope.actInfo = function(item, actId, type) {
        store.set('actInfo', item); //缓存当前活动信息至localStorage
        $state.go('activityInfo');
    };

    $scope.onfocus = function() {
        //更多搜索条件显示flag(true：显示；false：不显示，初始值：true)
        $scope.moredisplayflag = true;
        //按地址搜索显示flag(true：显示；false：不显示，初始值：true)
        $scope.searchbyadddisplayflag = true;
        // 按名称搜索的活动列表(true：显示；false：不显示，初始值：false)
        $scope.searchByNameflag = false;
        //导出邮箱按钮初始化隐藏
        $scope.exportemailbtnflag = false;

    };

    //导出邮箱
    $scope.exportemail = function() {
        $state.go('exportemail');

        var activityList = Array();
        //生成活动信息
        angular.forEach($scope.activityList, function(value, index){
            activityList.push(value.act_id);
        });
        // 缓存活动信息
        store.set('activityList', activityList);
    };

    //点击返回按钮
    $scope.back = function() {
        history.back();
    };
}]);

// 导出邮箱控制器
LoginModule.controller('exportemailController', ['$rootScope', '$scope', '$http', '$state', 'ngDialog', 'httpService', function($rootScope, $scope, $http, $state, ngDialog, httpService) {

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;
    // 获取活动信息
    var activityList = store.get('activityList');


    /* 拼接POST数据格式 */
    var params = {

    };

    //缓存新建邮箱
    $rootScope.common_email  = [];

    // 调用接口，获取默认邮箱
    httpService.postEmail(access_token, params)
    .then(function(data) {

        if (data.code == 1) {
            //存在默认邮箱的场合
            if (data.message.self.user_email) {
                /* 缓存默认邮箱 */
                $rootScope.default_email = {
                    'default': true,
                    'user_name': data.message.self.user_name,
                    'user_email': data.message.self.user_email
                };
            } else{
                //没有默认邮箱的场合
                ngDialog.open({
                    template:  '<div class="addemail text-center">'+
                                    '<h5>您还没有绑定的邮箱，请先去绑定邮箱</h5>'+
                                    '<button type="button" class="btn btn-cancel" ng-click="closeThisDialog()">取消</button>'+
                                    '<button type="submit" class="btn btn-add" ng-click="closeThisDialog();binddefultEmail()">好的</button>'+
                                '</div>',
                    className: 'ngdialog-theme-default',
                    plain: true,
                    scope: $scope
                });
            }


        } else {
            //出错的情况
            //弹出err信息
            $scope.errmessage = data.message;
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return;            
        }

        //添加默认邮箱
        $rootScope.common_email.push($rootScope.default_email);
        //添加非默认邮箱
        $rootScope.common_email = $rootScope.common_email.concat(data.message.other);

        console.log(data)
    }, function(data) {
        console.log('error')
    });

    // 绑定默认邮箱
    $scope.binddefultEmail = function() {
        //跳转绑定邮箱页面
        $state.go('bindemail', {back: 'exportemail'});
    };


    /* 新建邮箱 */
    $scope.addEmail = function() {
        ngDialog.open({
            template: './tpl/addemail.dailog_2.html',
            className: 'ngdialog-theme-default',
            controller: 'AddEmailController_2'
        });
    };

    /* 删除邮箱 */
    $scope.delete = function(index) {
        // 数据库中移除邮箱
        var params = {
            'user_name': $rootScope.common_email[index].user_name,
            'user_email': $rootScope.common_email[index].user_email,
            'type': 'del'
        }

        httpService.addUserEmailUrl(access_token, params)
            .then(function(data) {
                if (data.code == 0) {
                    //弹出err信息
                    $scope.errmessage = data.message;
                    ngDialog.open({
                        template: '<p>{{errmessage}}</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true,
                        scope: $scope
                    });
                    return;
                }

            console.log(data)
        }, function(data) {
            console.log('error')
        });
        // 列表中移除邮箱
        $rootScope.common_email.splice(index, 1);

    };

    var emailData; //用以缓存以选中的邮箱

    /* 提交表单 */
    $scope.submitForm = function(isValid) {

        emailData = []; //清空数组
        $scope.check(); //获取选中的数据


        var act_id = $scope.getact_id(act_id);

        if (emailData.length < 1) {

            //弹出err信息
            $scope.errmessage = '请选择邮箱！';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return;
        }

        /* 拼接POST数据格式 */
        var params = {
            'user_email': angular.toJson({'email': emailData}),
            //拼接活动id字符串
            'act_id': act_id
        };
 
        console.log(params)
        /* 导出跑友资料 */
        httpService.actlistemailUrL(access_token, params)
        .then(function(data){
            alert(data.message);
            console.log(data)
        }, function(data) {

            //弹出err信息
            $scope.errmessage = data.message;
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            console.log('error')
        });
    };


    $scope.getact_id = function(act_id) {

        angular.forEach(store.get('activityList'), function(value, index){
            if (index == 0) {
                act_id = value;
            } else {
                act_id = act_id + "," + value;
            }
        });

        return act_id;
    }

    /* 获取选中的数据 */
    $scope.check = function() {  
        var commonEmail = document.getElementsByName("commonEmail");  
        for(i = 0; i < commonEmail.length; i++) {    
            if(commonEmail[i].checked) {
                angular.forEach($rootScope.common_email, function(value, index){
                    if (i == index) {
                        emailData.push(value)
                    }
                });
                console.log(emailData)
            }       
        }    
    }  

}]);

//增加邮箱控制器
LoginModule.controller('AddEmailController_2', ['$rootScope', '$scope', '$state', 'ngDialog', 'httpService', function($rootScope, $scope, $state, ngDialog, httpService) {


   /* 提交表单 */
    $scope.submitForm = function(isValid) {

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');
    if (!access_token) return;

        if (isValid) {

            /* 拼接数据格式 */
            var userdata = {
                'user_name': $scope.user.name,
                'user_email': $scope.user.email
            };

            /* 缓存至数组中 */
            $rootScope.common_email.push(userdata);

            var params = {
                'user_name': $scope.user.name,
                'user_email': $scope.user.email,
                'type': 'add'
            }

            httpService.addUserEmailUrl(access_token, params)
                .then(function(data) {
                    if (data.code == 0) {
                        //错误的场合弹出err信息
                        $scope.errmessage = data.message;
                        ngDialog.open({
                            template: '<p>{{errmessage}}</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true,
                            scope: $scope
                        });
                    }
                console.log(data)
            }, function(data) {
                console.log('error')
            });

            
            ngDialog.close();
        }
    };
}]);

LoginModule.controller('advancedsettingsController', ['$scope', '$http', '$state', 'ngDialog', function($scope, $http, $state, ngDialog) {
    
    $http.get('./api/advancedsettingsdata.json').
      success(function(data, status) {
            $scope.activityProjectList = data.message.activityProjectList;
            $scope.registratioInfoList = data.message.registrationInfoList;
            console.log(data)
        }).
      error(function(data, status) {
            console.log('error')
        });

    /* 新建项目 */
    $scope.addProject = function() {
        ngDialog.open({
            template: './tpl/addProject.html',
            //className: 'ngdialog-theme-default'
            // controller: 'AddEmailController_2'
        });
    };
}]);

LoginModule.controller('signequipmentController', ['$scope', '$http', '$state', 'httpService','ngDialog', function($scope, $http, $state, httpService, ngDialog) {

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前跑团绑定设备信息 */
    $scope.bindInfo = store.get('bindInfo');

    //获取当前活动ID
    $scope.act_id =  store.get('act_id');

    // 绑定设备ID device_id
    $scope.device_id = "";
    //设备绑定跑团ID
    $scope.act_id_l = "";

    $scope.bindsignequipment = function(device_id, act_id_l) {
        $scope.device_id = device_id;
        $scope.act_id_l = act_id_l;
    }

    $scope.bind = function() {
        if ($scope.device_id == "") {

            //弹出err信息
            $scope.errmessage = '请选择绑定设备';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });

            return;
        }

        if ($scope.act_id_l == "" || $scope.act_id_l == null) {
            $scope.act_id_l = $scope.act_id;
        }

        /* 拼接POST数据格式 */
        var params = {
            'act_id': $scope.act_id,
            'device_id': $scope.device_id,
            'act_id_l': $scope.act_id_l
        };

        /* 绑定设备提交 */
        httpService.postActInfo(access_token, params)
        .then(function(data){

        //跳转至签到信息页面
        $state.go('signinfo');

            console.log(data)
        }, function(data) {
            console.log('error')
        });    
    }
}]);

//签到信息控制器
LoginModule.controller('signinfoController', ['$rootScope', '$scope', '$http', '$state', 'httpService', 'ngDialog', function($rootScope, $scope, $http, $state, httpService, ngDialog) {

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前跑团信息 */
    var clubInfo = store.get('clubInfo');

    // 获取缓存活动信息
    var new_act_id = store.get('new_act_id');
    
    /* 获取当前跑团ID */
    $scope.clubid = store.get('clubInfo').clubid;

    var params = {
        act_id: new_act_id,
        club_id: $scope.clubid
    }
    
    /* 获取创建成功的活动信息 */
    httpService.postActInfo(access_token, params)
    .then(function(data){
        //显示活动信息
        $scope.inregister = data.message;
        /* 获取用户登录标识 */
        $scope.access_token = access_token;

        $scope.ibeacon = data.ibeacon;

        console.log(data)
    }, function(data) {
        console.log('error')
    });

    // 发起活动跳转过来的场合
    if ($state.params.newact == 1) {
        //返回路由
        $scope.back_ui_sref = 'index.activity';

        ngDialog.open({
            template:  '<div class="activity_bulid_Dialog container-fluid">'+
                            '<div class="row">'+
                                '<div class="col-xs-4 text-align-right">'+
                                    '<img class="dialg-img" ng-src="{{inregister[0].act_image}}" alt="" class="img-responsive">'+
                                '</div>'+
                                '<div class="col-xs-8">'+
                                    '<lable class="congratulate color-xiaoai">恭喜</lable><br>'+
                                    '<lable>您已成功发起活动</lable>'+
                                '</div>'+
                                '<button type="submit" class="col-xs-4 ok_btn" ng-click="closeThisDialog()">好的</button>'+
                            '</div>'+

                        '</div>',
            className: 'ngdialog-theme-default',
            plain: true,
            scope: $scope
        });
    } else if ($state.params.newact == 2) {
        //返回路由签到活动列表
        $scope.back_ui_sref = 'inchecklist';
    } else if ($state.params.newact == 3) {
        //返回活动初始列表
        $scope.back_ui_sref = 'index.activity';
    } else if ($state.params.newact == 4) {
        //返回活动信息(activity/info)
        $scope.back_ui_sref = 'activityInfo';
    } else{
        //返回活动初始列表
        $scope.back_ui_sref = 'index.activity';
    }

    $scope.bind = function() {
        if ($scope.ibeacon.length == 0) {

            //弹出err信息
            $scope.errmessage = '无可绑定摇一摇设备';
            ngDialog.open({
                template: '<p>{{errmessage}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });

        } else {
            //缓存绑定设备信息
            store.set('bindInfo',$scope.ibeacon);
            //缓存当前活动ID
            store.set('act_id',store.get('new_act_id'));
            //跳转签到设备页面
            $state.go('signequipment');
        }
    }

    /* 点赞功能 */
    $scope.praise = function() {
        httpService.postPraise(access_token, {'activity_id': new_act_id})
        .then(function(data) {

            //当前活动信息缓存
            var actInfo = store.get('actInfo');
            //取消点赞
            if (data.code==0) {
                $scope.isPraise = false;
                //更新点赞数目
                $scope.inregister[0].act_praise--;
                //更新活动信息缓存
                if (actInfo) {
                    actInfo.act_praise--;
                    store.set('actInfo', actInfo);
                }
            }

            //点赞
            if (data.code==1) {
                $scope.isPraise = true;
                //更新点赞数目
                $scope.inregister[0].act_praise++;
                //更新活动信息缓存
                if (actInfo) {
                    actInfo.act_praise++;
                    store.set('actInfo', actInfo);
                }
            }
        }, function(data) {
            console.log('error')
        });
    }
    //刷新
    $scope.f5 = function() {
        /* 获取用户登录标识 */
        var access_token = store.get('access_token');

        if (!access_token) return;

        /* 获取当前跑团信息 */
        var clubInfo = store.get('clubInfo');

        // 获取缓存活动信息
        var new_act_id = store.get('new_act_id');
        
        /* 获取当前跑团ID */
        $scope.clubid = store.get('clubInfo').clubid;

        var params = {
            act_id: new_act_id,
            club_id: $scope.clubid
        }
        
        /* 获取创建成功的活动信息 */
        httpService.postActInfo(access_token, params)
        .then(function(data){
            //显示活动信息
            $scope.inregister = data.message;
            /* 获取用户登录标识 */
            $scope.access_token = access_token;

            $scope.ibeacon = data.ibeacon;

            console.log(data)
        }, function(data) {
            console.log('error')
        });
     }
    
}]);

//签到列表控制器
LoginModule.controller('inchecklistController', ['$rootScope', '$scope', '$http', '$state', 'ngDialog', 'httpService', function($rootScope, $scope, $http, $state, ngDialog, httpService) {
    
    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    /* 获取当前跑团ID */
    $scope.clubid = store.get('clubInfo').clubid;

    if (!access_token) return;

    var inchecklist = store.get('inchecklist');

    //显示正在签到活动列表
    $scope.incheck = inchecklist;

    $scope.gosigninfo = function(item) {

        var data = item

        store.set('new_act_id',data);
        $state.go('signinfo', {newact: 2});
    };

}]);