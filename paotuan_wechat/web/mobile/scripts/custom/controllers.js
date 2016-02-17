// 删除数组中第一个匹配的元素，成功则返回位置索引，失败则返回 -1。
Array.prototype.deleteElementByValue = function(varElement) {
    var numDeleteIndex = -1;
    for (var i=0; i<this.length; i++)
    {
        // 严格比较，即类型与数值必须同时相等。
        if (this[i] === varElement)
        {
            // this.splice(i, 1);
            numDeleteIndex = i;
            break;
        }
    }
    return numDeleteIndex;
}

/**
 * [主控制器]
 * @param  {[type]} $rootScope    [description]
 * @param  {[type]} $scope        [description]
 * @param  {[type]} $http         [description]
 * @param  {[type]} cfpLoadingBar [loading进度条]
 * @param  {[type]} $timeout      [description]
 * @param  {[type]} ngDialog      [模态框]
 * @return {[type]}               [description]
 */
ActivityModule.controller('AppController', ['$rootScope', '$scope', '$http', '$state', 'cfpLoadingBar', '$timeout', 'ngDialog', 'httpService', function($rootScope, $scope, $http, $state, cfpLoadingBar, $timeout, ngDialog, httpService){

    console.log(store.getAll())

    // 获取缓存权限信息
    if (store.get('admin')) {
        $rootScope.is_admin = store.get('admin').is_admin;
        $rootScope.isActer = store.get('admin').isActer;
    }

    $rootScope.isTopNavFn = function() {
        $rootScope.isTopNav = true;
    }

    $rootScope.closeFn = function() {
        $rootScope.isTopNav = false;
    }

    console.log(store.get('admin'))

    /* loading */
    $scope.start = function() {
        cfpLoadingBar.start();
    };

    $scope.complete = function () {
        cfpLoadingBar.complete();
    };

    // fake the initial load so first time users can see the bar right away:
    $scope.start();
    // $scope.fakeIntro = true;
    // $timeout(function() {
    $scope.complete();
    // $scope.fakeIntro = false;
    // }, 1250);
    
    // 显示分享
    $rootScope.shareFn = function() {
        $scope.openShareAlert = true;
        ngDialog.close();
    };

    // 隐藏分享
    $rootScope.closeShareAlert = function() {
        $scope.openShareAlert = false;
    }

    /* 分享模态框 */
    $scope.openShare = function () {
        ngDialog.open({
            template: './tpl/share.dialog.html',
            className: 'ngdialog-theme-default ngdialog-share',
            scope: $scope
        });
    };

    /* 通用模态框 */
    $scope.openDialog = function () {
        ngDialog.open({
            template: '<p>test</p>',
            className: 'ngdialog-theme-default',
            plain: true
        });
    };

    /* 返回上一页 */
    $rootScope.back = function() {
        history.go(-1);
    };

    /* 获取窗口宽度 */
    $rootScope.winWidth = window.innerWidth;

    /* 取消活动 */
    $rootScope.cancelact = function() {

        // 判断是否分享的链接
        if ($state.params.type == 'share') {
            ngDialog.open({
                template: '<p class="alert-p">只有管理员或团长才有权限操作哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true
            });

            return false;
        }

        /* 获取用户登录标识 */
        var access_token = store.get('access_token');
        if (!access_token) return;
        
        httpService.postCancelAct(access_token, {'act_id': store.get('actInfo').act_id})
            .then(function(data) {

                // 判断是否有权限操作
                if (data.code==5) {
                    ngDialog.open({
                        template: '<p class="alert-p">只有管理员或团长才有权限操作哦</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true
                    });

                    return false;
                } else {
                    ngDialog.open({
                        template: '<p>取消成功哦</p>'+
                                    '<button type="button" class="btn btn-red" ui-sref="index.activity" ng-click="closeThisDialog()">返回活动列表</button>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true
                    });
                }

            console.log(data)
        }, function(data) {
            console.log('error')
        });

        // ngDialog.open({
        //     template: '<div class="addemail text-center">'+
        //                     '<h5>确定取消活动???</h5>'+
        //                     '<button type="button" class="btn btn-cancel" ng-click="closeThisDialog()">取消</button>'+
        //                     '<button type="submit" class="btn btn-add" ng-click="cancel(); closeThisDialog()">确定</button>'+
        //                 '</div>',
        //     className: 'ngdialog-theme-default ngdialog-alert',
        //     plain: true,
        //     scope: $scope
        // });

    }

}]);


/**
 * [活动列表 控制器]
 * @param  {[type]} $scope      [description]
 * @param  {[type]} $http       [description]
 * @param  {[type]} $state      [description]
 * @param  {[type]} httpService [自定义接口服务]
 * @param  {[type]} dateFormat  [自定义时间格式化服务]
 * @return {[type]}             [description]
 */
ActivityModule.controller('ActivityController', ['$location', '$rootScope', '$scope', '$http', '$state', 'ngDialog', 'httpService', 'dateFormat', function($location, $rootScope, $scope, $http, $state, ngDialog, httpService, dateFormat) {
    console.log($location.$$url)

    //判断是否从用户个人中心跳转过来的。获取参数判断做相应跳转。
    if ($location.$$url) {
        var url = $location.$$url.substr(16);
        var flag = (url.indexOf('uid')!=-1)&&(url.indexOf('act')!=-1);

        console.log(url)

        if (flag) {

            // $rootScope.hidelogin = true;

            $scope.hide = true;

            $http({
                method: 'GET',
                url: 'http://www.paobuqu.com/v4/common/linkactivity?' + url
            })
            .success(function(data, status, headers, config) {
                if (data.code==1) {
                    store.set('access_token', data.message.access_token);
                    store.set('clubInfo', data.message.club_info[0]);

                    /* 获取用户登录标识 */
                    var access_token = store.get('access_token');

                    if (!access_token) return;

                    /* 获取当前跑团信息 */
                    $scope.clubInfo = store.get('clubInfo');


                    /* 拼接POST数据格式 */
                    var params = {
                        clubid: $scope.clubInfo.clubid
                    };

                    /* 获取活动列表 */
                    httpService.postActList(access_token,params)
                    .then(function(data){
                        var data = $scope.data = data.message;
                        if (data.is_admin==0) {

                            var role = data.role.indexOf('activity');

                            console.log(role)

                            $rootScope.isActer = role==-1 ? false : true;

                            console.log($rootScope.isActer)
                        }

                        $rootScope.is_admin    = data.is_admin == 1 ? true : false; //判断是否为创建者 

                        // 如果权限信息存在，则删除之，再缓存
                        if (store.get('admin')) {
                            store.remove('admin')
                        }

                        store.set('admin',{'is_admin': $rootScope.is_admin, 'isActer': $rootScope.isActer});

                        $scope.incheck    = data.incheck;     //缓存正在签到的活动列表
                        $scope.inregister = data.inregister;  //缓存正在报名的活动列表
                        $scope.nostart    = data.nostart;     //缓存尚未开始的活动列表
                        $scope.inend      = data.inend;       //缓存已结束的活动列表
                        $scope.iscancel   = data.incancel;    //缓存已取消的活动列表

                        /* 计算活动个数，已取消活动仅管理员才有 */
                        var length = $scope.incheck.length + $scope.inregister.length + $scope.nostart.length + $scope.inend.length;
                        $scope.actlength = data.is_admin ? (length + $scope.iscancel.length) : length;
                        
                        /* 格式化时间 */
                        angular.forEach(data, function(value, index){
                            dateFormat.format(value)
                        });

                        console.log(data)
                    }, function(data) {
                        console.log('error')
                    });

                }
                console.log(data)
            })
            .error(function(data, status, headers, config) {  
                console.log(data)
            });

            // return false;
        }
        
    }


    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前跑团信息 */
    $scope.clubInfo = store.get('clubInfo');

    // console.log($scope.clubInfo)

    /* 配置tab列表 */
    $scope.tabs = [
        {
            "title": "主页",
            "src": "index",
            "badge": false
        },
        {
            "title": "活动",
            "src": "index.activity",
            "active": true,
            "badge": true
        }
    ];

    /* 拼接POST数据格式 */
    var params = {
        clubid: $scope.clubInfo.clubid
    };

    $rootScope.isTopNavFn = function() {
        $rootScope.isTopNav = true;
    }

    $rootScope.closeFn = function() {
        $rootScope.isTopNav = false;
    }

    // $rootScope.isActer = true;
    /* 获取活动列表 */
    httpService.postActList(access_token,params)
    .then(function(data){
        var data = $scope.data = data.message;

        if (data.is_admin==0) {

            var role = data.role.indexOf('activity');

            console.log(role)

            $rootScope.isActer = role==-1 ? false : true;

            console.log($rootScope.isActer)
        }

        $rootScope.is_admin    = data.is_admin == 1 ? true : false; //判断是否为创建者 

        // 如果权限信息存在，则删除之，再缓存
        if (store.get('admin')) {
            store.remove('admin')
        }

        store.set('admin',{'is_admin': $rootScope.is_admin, 'isActer': $rootScope.isActer});

        console.log(store.get('admin'))

        $scope.incheck    = data.incheck;     //缓存正在签到的活动列表
        $scope.inregister = data.inregister;  //缓存正在报名的活动列表
        $scope.nostart    = data.nostart;     //缓存尚未开始的活动列表
        $scope.inend      = data.inend;       //缓存已结束的活动列表
        $scope.iscancel   = data.incancel;    //缓存已取消的活动列表

        /* 计算活动个数，已取消活动仅管理员才有 */
        var length = $scope.incheck.length + $scope.inregister.length + $scope.nostart.length + $scope.inend.length;
        $scope.actlength = data.is_admin ? (length + $scope.iscancel.length) : length;

        /* 格式化时间 */
        angular.forEach(data, function(value, index){
            dateFormat.format(value)
        });

        console.log(data)
    }, function(data) {
        console.log('error')
    });

    /* 跳转路由至activityInfo */
    $scope.actInfo = function(item, actId, check) {
        console.log(item)
        console.log($scope.data['iscancel'])
        var index = $scope.data[check].deleteElementByValue(item);
        var actInfo = $scope.data[check][index];
        store.set('actInfo', actInfo); //缓存当前活动信息至localStorage

        $state.go('activityInfo', {act_id: item.act_id});
    };

    //点击签到助手
    $scope.signHelper = function() {
        //正在报名的活动的数量
        //暂时没有正在报名的活动用一结束的测试（暂时）
        var incheck = $scope.incheck.length;

        //没有正在报名的活动
        if (incheck == 0) {
            alert("当前没有正在签到的活动");

        } else if (incheck == 1) {

            store.set('new_act_id', $scope.incheck[0].act_id);

            $state.go('signinfo', {newact: 3});

        } else{
            //多个正在报名的活动的场合
            // 缓存正在签到活动信息
            store.set('inchecklist', $scope.incheck);
            $state.go('inchecklist');
        }
    };

}]);

/**
 * [活动信息 控制器]
 * @param  {[type]} $scope       [description]
 * @param  {[type]} $http        [description]
 * @param  {[type]} $state       [description]
 * @param  {[type]} httpService  [自定义接口服务]
 * @param  {[type]} getDateDiff  [自定义计算时间差服务]
 * @return {[type]}              [description]
 */
ActivityModule.controller('ActivityInfoController', ['$rootScope', '$scope', '$http', '$state', 'ngDialog', 'httpService', 'getDateDiff', function($rootScope, $scope, $http, $state, ngDialog, httpService, getDateDiff) {
    
    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前活动信息 */
    $scope.actInfo = store.get('actInfo');

    console.log($scope.actInfo)

    /* 获取评论、报名/签到列表 */
    httpService.postCommentList(access_token, {act_id: $state.params.act_id})
    .then(function(data){
        $scope.comment = data.message;
        $scope.regCheck = [];
        
        /* 遍历报名/签到数据取出6条数据 */
        var regCheck = data.acttivity_user;

        /* 如果报名/签到数据不为空， 则取出数据*/
        if (regCheck) {

            var isreg_T = regCheck.isreg && regCheck.isreg.length>0;
            var isreg_F = regCheck.isreg && regCheck.isreg.length==0;
            var ischeckin_T = regCheck.ischeckin && regCheck.ischeckin.length>0;
            var ischeckin_F = regCheck.ischeckin && regCheck.ischeckin.length==0;
            var isreg_len = regCheck.isreg.length;
            var ischeckin_len = regCheck.ischeckin.length;

            /* 报名有数据, 签到无数据 */
            if (isreg_T && ischeckin_F) {
                var len = isreg_len <= 6 ? isreg_len : 6;
                for (var i = 0; i < len; i++) {
                    $scope.regCheck.push(regCheck.isreg[i])
                }
            }

            /* 报名无数据, 签到有数据 */
            if (isreg_F && ischeckin_T) {
                var len = ischeckin_len <= 6 ? ischeckin_len : 6;
                for (var i = 0; i < len; i++) {
                    $scope.regCheck.push(regCheck.ischeckin[i])
                }
            }

            /* 报名, 签到均有数据 */
            if (isreg_T && ischeckin_T) {
                var len_1 = isreg_len <= 3 ? isreg_len : 3;
                for (var i = 0; i < len_1; i++) {
                    $scope.regCheck.push(regCheck.isreg[i])
                }

                var len_2 = ischeckin_len <= (6-len_1) ? ischeckin_len : (6-len_1);
                for (var i = 0; i < len_2; i++) {
                    $scope.regCheck.push(regCheck.ischeckin[i])
                }
            }


            /* 判断报名数据是否为空 */
            // if (regCheck.isreg && regCheck.isreg.length>0) {
            //     var len = regCheck.isreg.length <= 3 ? regCheck.isreg.length : 3;
            //     for (var i = 0; i < len; i++) {
            //         $scope.regCheck.push(regCheck.isreg[i])
            //     }
            // }

            // /* 判断签到数据是否为空 */
            // if (regCheck.ischeckin && regCheck.ischeckin.length>0) {
            //     var len = regCheck.ischeckin.length <= 3 ? regCheck.ischeckin.length : 3;
            //     for (var i = 0; i < len; i++) {
            //         $scope.regCheck.push(regCheck.ischeckin[i])
            //     }
            // }

            console.log($scope.regCheck)
        }
        
        if ($scope.comment && $scope.comment.length>0) {    
            /* 遍历评论的数据计算出时间差，并插入数据中 */
            angular.forEach($scope.comment, function(value, index){
                var start,now,diff;
                if (value.create_time) {
                    start = new Date(value.create_time);
                    now = new Date();
                    diff = getDateDiff.diff(start, now)
                    $scope.comment[index]['diff'] = diff;
                }
            });
        }

        console.log(data)
    }, function(data) {
        console.log('error')
    });

    /* 跳转路由至comment */
    $scope.goComment = function() {
        $state.go('comment');
    };

    /* 删除评论 */
    $scope.delete = function(item) {
        var index = $scope.comment.deleteElementByValue(item);
        $scope.comment.splice(index, 1);
        console.log(index)
    };

    //点击活动提醒，判断是否为认证跑团
    $scope.sendmessage = function() {
        if (store.get('clubInfo').club_status==1) {
            $state.go('sendmessage')
        } else {
            ngDialog.open({
                template: '<p>认证跑团才能使用活动提醒功能，非认证跑团不能使用活动提醒功能哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true
            })
        }
    };

    //点击签到助手
    $scope.signHelper = function() {
        //缓存活动id
        store.set('new_act_id', $state.params.act_id);
        //跳转签到信息页面
        $state.go('signinfo', {newact: 4});
    };

    /* 点赞功能 */
    $scope.praise = function() {
        httpService.postPraise(access_token, {'activity_id': $state.params.act_id})
        .then(function(data) {

            //取消点赞
            if (data.code==0) {
                $scope.isPraise = false;
                //更新点赞数目
                $scope.actInfo.act_praise--;
                //更新活动信息缓存
                if (store.get('actInfo')) {
                    store.remove('actInfo')
                }
                store.set('actInfo', $scope.actInfo)
                console.log(store.get('actInfo'))
            }

            //点赞
            if (data.code==1) {
                $scope.isPraise = true;
                //更新点赞数目
                $scope.actInfo.act_praise++;
                //更新活动信息缓存
                if (store.get('actInfo')) {
                    store.remove('actInfo')
                }
                store.set('actInfo', $scope.actInfo)
                console.log(store.get('actInfo'))
            }
            console.log(data)
        }, function(data) {
            console.log('error')
        });
    }

    // 自定义分享内容接口
    var title  = $scope.actInfo.act_title,
        desc   = $scope.actInfo.act_desc.substr(0,20),
        link   = 'http://wechat.paobuqu.com/mobile/#/activity/detail?type=share&act_id=' + $state.params.act_id,
        imgUrl = $scope.actInfo.act_image;

    sns(title, desc, link, imgUrl);

}]);


/**
 * [活动详情 控制器]
 * @param  {[type]} $scope         [description]
 * @param  {[type]} $http          [description]
 * @param  {[type]} $state         [description]
 * @param  {[type]} ngDialog       [模态框]
 * @param  {[type]} httpService    [自定义接口服务]
 * @param  {[type]}                [description]
 * @return {[type]}                [description]
 */
ActivityModule.controller('ActivityDetailController', ['$rootScope', '$scope', '$http', '$state', '$timeout', 'ngDialog', 'httpService', 'dateFormat', function($rootScope, $scope, $http, $state, $timeout, ngDialog, httpService, dateFormat) {
    
    // 判断参数中是否有access-token，并缓存起来
    if ($state.params.access_token) {

        // 返回上一页
        $scope.goActInfo = function() {
            history.go(-1);
        }

        /* 取消活动 */
        $rootScope.cancelact = function() {
            httpService.postCancelAct($state.params.access_token, {'act_id': $state.params.act_id})
                .then(function(data) {

                    // 判断是否有权限操作
                    if (data.code==5) {
                        ngDialog.open({
                            template: '<p class="alert-p">只有管理员或团长才有权限操作哦</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true
                        });

                        return false;
                    } else {
                        ngDialog.open({
                            template: '<p>取消成功哦</p>'+
                                        '<button type="button" class="btn btn-red" ui-sref="index.activity" ng-click="closeThisDialog()">返回活动列表</button>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true
                        });
                    }

                console.log(data)
            }, function(data) {
                console.log('error')
            });
        }

        console.log($state.params.access_token)
        // store.get('access_token', $state.params.access_token)
    } else {

        // 返回活动信息页
        $scope.goActInfo = function() {
            $state.go('activityInfo', {act_id: $state.params.act_id})
        }

        // 删除分享活动的clubId
        if (store.get('shareClubId')) {
            store.remove('shareClubId')
        }

        // 删除分享活动的信息
        if (store.get('shareActInfo')) {
            store.remove('shareActInfo')
        }
    }

    // 判断是否分享的链接
    if ($state.params.type == 'share') {
        httpService.postSharedAct('', {'act_id': $state.params.act_id})
        .then(function(data){

            $scope.actInfo = data.act[0];
            $scope.project = data.project;

            // 缓存分享活动的clubId
            store.set('shareClubId', data.club_id);

            // 缓存分享活动的信息
            store.set('shareActInfo', data.act[0]);

            // 格式化时间
            var check_time = '', T1, T2, T3;
            if ($scope.actInfo.act_start_time||$scope.actInfo.act_end_time) {
                T1 = moment($scope.actInfo.act_start_time).format('MM-DD');
                T2 = moment($scope.actInfo.act_start_time).format('HH:mm');
                T3 = moment($scope.actInfo.act_end_time).format('HH:mm');
                check_time = T1 + ' ' + T2 + '-' + T3;
                $scope.actInfo['check_time'] = check_time;
            }

            if ($scope.actInfo.act_start_time&&$scope.actInfo.recurring_type=='1') {
                var week = null;
                var day  = moment($scope.actInfo.act_start_time).format('e');
                switch (day) {
                    case '0':
                        week = "每周日举行";
                        break;
                    case '1':
                        week = "每周一举行";
                        break;
                    case '2':
                        week = "每周二举行";
                        break;
                    case '3':
                        week = "每周三举行";
                        break;
                    case '4':
                        week = "每周四举行";
                        break;
                    case '5':
                        week = "每周五举行";
                        break;
                    case '6':
                        week = "每周六举行";
                        break;
                }
            }

            $scope.actInfo['recurring_type_time'] = week;

            // 报名时间
            $scope.inreg   = moment() > moment($scope.actInfo.act_create_time) && moment() < moment($scope.actInfo.register_end_time);

            // 签到时间
            $scope.incheck = moment() > moment($scope.actInfo.act_start_time).subtract(30, 'minutes') && moment() < moment($scope.actInfo.act_end_time);    

            $scope.isChecked = false;
            $scope.default   = false;
            $scope.btnText   = $scope.isChecked ? '已报名' : '立即报名';
            
            // 点击立即签到按钮跳转至签到页面
            $scope.joinProject = function() {
                window.location.href = 'http://wechat.paobuqu.com/login/sharact?act_id=' + $state.params.act_id;
            }

            // 禁用按钮
            $scope.actAlert = function() {
                ngDialog.open({
                    template: '<p class="alert-p">只有管理员或团长才有权限操作哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true
                });
            }

            console.log(data)
        }, function(data) {
            console.log('error')
        });

        return true;
    }

    /* 获取用户登录标识 */
    var access_token = $state.params.access_token ? $state.params.access_token : store.get('access_token');

    if (!access_token) return;

    //  获取当前活动信息 
    $scope.actInfo = $state.params.access_token ? store.get('shareActInfo') : store.get('actInfo');
    $scope.actId   = $state.params.act_id;

    console.log(store.get('shareActInfo'))
    //图文混排显示
    // document.getElementById("act_desc").innerHTML = $scope.actInfo.act_desc;
    // console.log($scope.actInfo)

    /* 获取活动项目列表信息 */
    httpService.postActProject(access_token, {'act_id': $state.params.act_id})
    .then(function(data){

        $scope.project = data.message;

        console.log(data)
    }, function(data) {
        console.log('error')
    });

    // 报名时间
    $scope.inreg   = moment() > moment($scope.actInfo.act_create_time) && moment() < moment($scope.actInfo.register_end_time);

    // 签到时间
    $scope.incheck = moment() > moment($scope.actInfo.act_start_time).subtract(30, 'minutes') && moment() < moment($scope.actInfo.act_end_time);

    console.log($scope.incheck)

    $scope.isChecked = false;
    $scope.default   = false;
    $scope.btnText   = $scope.isChecked ? '已报名' : '立即报名';

    /* 判断是否报名活动项目 */
    httpService.postUserReg(access_token, {'act_id': $state.params.act_id})
    .then(function(data){

        console.log(data)

        /* 缓存code */
        $scope.type = data.type;
        $scope.code = data.code;
        $scope.dataMessage = data.message;

        if ($scope.type=='onlycheck') {
            // $scope.inreg = true;
            $scope.btnText   = '立即签到';
            return false;
        }

        /* code为0时, 已报名, 禁用按钮 */
        if ($scope.code==0) {

            // 判断是否可以取消报名
            if ($scope.type=='cancel') {

                $scope.cancel = true;
                $scope.btnText2   = '可取消';

                $scope.cancelFn = function() {
                    httpService.postCancelReg(access_token, {'act_id': $state.params.act_id})
                    .then(function(data) {

                        // 如果code为0，则提示用户不能取消报名
                        if (data.code==0) {
                            ngDialog.open({
                                template: '<p class="alert-p">不能取消报名哦</p>',
                                className: 'ngdialog-theme-default ngdialog-alert',
                                plain: true
                            });
                        }

                        // 如果取消成功则提示用户并刷新当前页面
                        if (data.code==1) {

                            ngDialog.open({
                                template: '<p class="alert-p">取消成功哦</p>',
                                className: 'ngdialog-theme-default ngdialog-alert',
                                plain: true
                            });

                            $timeout(function() {
                                location.reload()
                            }, 300)
                        }

                        console.log(data)
                    }, function(data) {
                        console.log('error')
                    });
                }

            } else {
                $scope.isChecked = true;
                $scope.btnText   = '已报名';
            }

        }

        /* code为1时, 默认项目 */
        if ($scope.code==1) {
            $scope.btnText   = '立即报名';
            console.log($scope.project)
        }

        /* code为2时, 已报名未支付, 跳转至支付页面 */
        if ($scope.code==2) {
            $scope.btnText   = '已报名，未支付';
        }

        /* code为3时, 用户尚未加入本跑团, 不可报名, 禁用按钮并呼出模态框提示 */
        if ($scope.code==3) {

            $scope.isChecked = true;
            $scope.btnText = '尚未加入本跑团';

            // 点击跳转至此跑团主页
            $scope.joinClub = function() {
                window.location.href = 'http://wechat.paobuqu.com/wechat/#/clubs/home?club_eng=' + data.club_eng
            }

            ngDialog.open({
                template: '<p>该活动仅对本跑团的跑友开放，参加活动需要先加入跑团哦</p>'+
                            '<button type="button" class="btn btn-cancel" ng-click="closeThisDialog()">知道了</button>'+
                            '<button type="button" class="btn btn-red" ng-click="joinClub(); closeThisDialog()">加入</button>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });

        }

    }, function(data) {
        console.log('error')
    });

    /* 报名活动项目模态框 */
    $scope.joinProject = function() {

        if ($scope.type=='onlycheck') {
            window.location.href = 'http://wechat.paobuqu.com/wechat/#/activity/checkin'
        }

        /* code为1时, 可以报名, 呼出模态框 */
        if ($scope.code==1) {
            ngDialog.open({
                template: './tpl/joinproject.dialog.html',
                className: 'ngdialog-theme-default join-project',
                scope: $scope
            });
        }

        /* code为2时, 已报名未支付, 跳转至支付页面 */
        if ($scope.code==2) {

            $scope.goPay = function() {
                /* 缓存订单信息 */
                store.set('payInfo', $scope.dataMessage);
                $state.go('pay', {act_id: $state.params.act_id});
            };

            ngDialog.open({
                template: '<p>您已报名过活动项目哦</p>'+
                            '<p>但是还未付款哦</p>'+
                            '<button type="buttom" class="btn btn-red" ng-click="goPay(); closeThisDialog()">立即付款</button>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });   
        }

    };

    var projectData = '';

    /* 获取选中的数据 */
    $scope.check = function() {  
        var projectName = document.getElementsByName("projectName");  
        for(i = 0; i < projectName.length; i++) {    
            if(projectName[i].checked) {
                angular.forEach($scope.project, function(value, index){
                    if (i==index) {
                        projectData = projectData + ',' + value.id;
                    }
                });
                console.log(projectData.substr(1))
            }       
        }    
    }  

    /* 提交表单 */
    $scope.submitForm = function(isValid) {

        if (!isValid) {
            
            ngDialog.open({
                template: '<p class="alert-p">验证失败哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        if (isValid) {

            /* 清空缓存数组，并拼接pro_id字符串 */
            projectData = [];
            $scope.check();

            if (projectData.length==0) {
                ngDialog.open({
                    template: '<p class="alert-p">请选择要报名的活动项目哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true,
                    scope: $scope
                });
            }

            if (projectData.length>0) {
                /* 拼接POST数据格式 */
                var params = {
                    'act_id': $state.params.act_id,
                    'pro_id': projectData.substr(1)
                };

                /* 报名活动项目 */
                httpService.postRegProject(access_token, params)
                .then(function(data){

                    /* 判断数据是否为空 */
                    if (data.message&&data.message.length>0) {
                        store.set('regInfo', data.message); //缓存报名活动项目信息
                        $state.go('regInfo',{act_id: $state.params.act_id, pro_id: projectData.substr(1)});
                        ngDialog.close();
                    }

                    console.log(data)
                }, function(data) {
                    console.log('error')
                });                               
            }          
        }

        return false;
    };

    /* 复制活动 */
    $scope.copyact = function() {
        // 获取复制活动的信息
        httpService.postCopyActivity(access_token, { 'club_id': store.get('shareClubId')?store.get('shareClubId'):store.get('clubInfo').clubid, 'act_id': $state.params.act_id, 'type': 'copy'})
        .then(function(data){
            // 判断是否有权限操作
            if (data.code==5) {
                ngDialog.open({
                    template: '<p class="alert-p">只有管理员或团长才有权限操作哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true
                });

                return false;
            } else {
                $state.go('copyActivity', {act_id: $state.params.act_id, type: 'copy'})
            }

        }, function(data) {
            console.log('error')
        })
    }

    /* 编辑活动 */
    $scope.editact = function() {
        // 获取复制活动的信息
        httpService.postCopyActivity(access_token, { 'club_id': store.get('shareClubId')?store.get('shareClubId'):store.get('clubInfo').clubid, 'act_id': $state.params.act_id, 'type': 'copy'})
        .then(function(data){
            // 判断是否有权限操作
            if (data.code==5) {
                ngDialog.open({
                    template: '<p class="alert-p">只有管理员或团长才有权限操作哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true
                });

                return false;
            } else {
                $state.go('editActivity', {act_id: $state.params.act_id, type: 'edit'})
            }

        }, function(data) {
            console.log('error')
        })  
    }

    /* 点赞功能 */
    $scope.praise = function() {
        httpService.postPraise(access_token, {'activity_id': $state.params.act_id})
        .then(function(data) {

            //取消点赞
            if (data.code==0) {
                $scope.isPraise = false;
                //更新点赞数目
                $scope.actInfo.act_praise--;
                //更新活动信息缓存
                if (store.get('actInfo')) {
                    store.remove('actInfo')
                }
                store.set('actInfo', $scope.actInfo)
                console.log(store.get('actInfo'))
            }

            //点赞
            if (data.code==1) {
                $scope.isPraise = true;
                //更新点赞数目
                $scope.actInfo.act_praise++;
                //更新活动信息缓存
                if (store.get('actInfo')) {
                    store.remove('actInfo')
                }
                store.set('actInfo', $scope.actInfo)
                console.log(store.get('actInfo'))
            }
            console.log(data)
        }, function(data) {
            console.log('error')
        });
    }

    // 自定义分享内容接口
    var title  = $scope.actInfo.act_title,
        desc   = $scope.actInfo.act_desc.substr(0,20),
        link   = 'http://wechat.paobuqu.com/mobile/#/activity/detail?type=share&act_id=' + $state.params.act_id,
        imgUrl = $scope.actInfo.act_image;

    sns(title, desc, link, imgUrl);
    
}]);


/**
 * [活动复制 控制器]
 * @param  {[type]} $rootScope  [description]
 * @param  {[type]} $scope      [description]
 * @param  {[type]} $http       [description]
 * @param  {[type]} $state      [description]
 * @param  {[type]} $timeout    [description]
 * @param  {[type]} ngDialog    [模态框]
 * @param  {[type]} httpService [自定义接口服务]
 * @param  {[type]} mapService  [自定义QQ地图服务]
 * @param  {[type]} fileReader  [自定义上传图片服务]
 * @return {[type]}             [description]
 */
ActivityModule.controller('CopyActivityController', ['$rootScope', '$scope', '$http', '$state', '$timeout', 'ngDialog', 'httpService', 'mapService', 'fileReader', function($rootScope, $scope, $http, $state, $timeout, ngDialog, httpService, mapService, fileReader) {

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    // 图文混排
    // var editor;
    // KindEditor.ready(function(K) {

    //     editor = K.create('#editor_id', {
    //         width : '100%',
    //         height : '150px',
    //         themeType : 'example1',
    //         items : [
    //             'fontsize', 'forecolor', 'bold'
    //         ]
    //     });
    // });

    /* 获取当前跑团信息 */
    var clubInfo = store.get('clubInfo');

    // 用以缓存表单信息，也是最终提交的信息
    $rootScope.copyAct = {
        activity: '',
        regfield: '',
        project: ''
    }

    // 用以缓存表单信息，渲染页面
    $scope.act = {
        addr_id: '',
        address: '',
        cycle: false,
        recurring_end: '',
        protocol: true,
        checkin_gain: false,
        checkin_num: 0
    };

    // 缓存报名信息
    $rootScope.regfield = [
        {
            'col_name': 'passport_name',
            'col_title': '姓名',
            'col_type': 1,
            'col_list_values': 0
        },
        {
            'col_name': 'cell',
            'col_title': '手机号',
            'col_type': 1,
            'col_list_values': 0
        }
    ];

    /* 获取活动项目信息 */
    httpService.postActivityJoinInfo(access_token, {})
    .then(function(data) {

        if (data) {
            $rootScope.field     = data.field;
            $rootScope.join_info = data.join_info;
            $rootScope.type      = data.type;

            console.log($rootScope.join_info)
        }
        
        console.log(data)

    }, function(data) {
        console.log('error')
    });

    // 默认显示周期性活动选项
    $scope.showcycle = true;

    // 如果是编辑状态则隐藏周期性活动选项
    if ($state.params.act_id&&$state.params.type=='edit') {
        $scope.showcycle = false;
    }

    // 周期性活动截至时间模态框
    $scope.cycle = function() {
        $scope.act.cycle = !$scope.act.cycle;

        // 取消周期性活动
        $scope.cancelCycle = function() {
            $scope.act.cycle = false;
        }

        // 如果勾选周期性活动，则显示模态框
        if ($scope.act.cycle ) {
            ngDialog.open({
                template: 
                            '<div class="addemail text-center">'+
                            '<div class="col-xs-offset-1 col-xs-10">'+
                                '<h5>请输入周期性活动截至时间</h5>'+
                                '<input type="datetime-local" class="form-control" name="recurring_end" ng-model="act.recurring_end" placeholder="截止时间">'+
                            '</div>'+
                            '<button type="button" class="btn btn-cancel" ng-click="cancelCycle(); closeThisDialog()">取消</button>'+
                            '<button type="submit" class="btn btn-add" ng-click="closeThisDialog()">确定</button>'+
                            '</div>',
                className: 'ngdialog-theme-default',
                plain: true,
                scope: $scope
            });
        }
    }

    // 判断是复制、编辑活动，则获取复制、编辑活动的信息；若是新建活动，则监听活动时间模型
    if ($state.params.act_id&&($state.params.type=='copy'||$state.params.type=='edit')) {

        // 获取复制活动的信息
        httpService.postCopyActivity(access_token, {'club_id': store.get('clubInfo').clubid, 'act_id': $state.params.act_id, 'type': 'copy'})
        .then(function(data){

            // 判断是否有权限操作
            if (data.code==5) {
                ngDialog.open({
                    template: '<p class="alert-p">只有管理员或团长才有权限操作哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true
                });

                return false;
            }

            var activity, regfield, project;

            $rootScope.copyAct.activity = activity = data.activity;
            $rootScope.copyAct.regfield = regfield = data.regfield;
            $rootScope.copyAct.project  = project  = data.project;

            $rootScope.copyAct.activity.act_start_time = moment(activity.act_start_time)._d;
            $rootScope.copyAct.activity.act_end_time = moment(activity.act_end_time)._d;
            $rootScope.copyAct.activity.register_end_time = moment(activity.register_end_time)._d;


            angular.forEach(data.regfield, function(value, index) {

                // 正则匹配
                var regexp = new RegExp('reserved_field');

                // 缓存非passport_name与cell字段
                if (value.col_name!='passport_name' && value.col_name!='cell') {
                    $rootScope.regfield.push({
                        'col_name': value.col_name,
                        'col_title': value.col_title,
                        'col_type': value.col_type,
                        'col_list_values': value.col_list_values?value.col_list_values:0
                    });
                }

                // 缓存自定义字段
                if (regexp.test(value.col_name)) {
                    $rootScope.join_info.push({
                        'field': value.col_name,
                        'display': value.col_title,
                        'fieldtype': value.col_type,
                        'listvalue': value.col_list_values?value.col_list_values:0
                    });
                    console.log($rootScope.join_info)
                }

            });

            console.log($rootScope.regfield);

            $rootScope.isNewImage = false;
            $scope.imageSrc       = activity.act_image;
            
            console.log(data);

            return data.activity;
            
        }, function(data) {
            console.log('error')
        })

        // 获取复制活动当前匹配地址
        .then(function(activity){

            /* 遍历地址数组，获取当前匹配act_location的地址 */
            httpService.postFindLocation(access_token, {'club_id': clubInfo.clubid})
            .then(function(data){

                $scope.address = data.message;
                $scope.ip = data.ip;

                angular.forEach(data.message, function(value, index) {
                    if (activity.act_location==value.id) {
                        $scope.act.addr_id = value.id;
                        $scope.act.address = value.location;
                        console.log(value)
                    }
                });

                console.log(data)
            }, function(data) {
                console.log('error')
            });

            console.log(activity)
        }, function(data) {
            console.log('error')
        });

    } else {

        // 初始化数据
        $rootScope.copyAct.activity = {};
        $rootScope.copyAct.regfield = [];
        $rootScope.copyAct.project  = [];

        // 默认仅限本跑团
        $rootScope.copyAct.activity.rang_limit = 1;

        // 获取活动集合地点
        httpService.postFindLocation(access_token, {'club_id': clubInfo.clubid})
        .then(function(data){
            $scope.address = data.message;
            $scope.ip = data.ip;
            console.log(data)
        }, function(data) {
            console.log('error')
        });

        // var nowDate = moment()._d;

        //开始时间的最小时间
        // $scope.minStartDate = moment().subtract(1, 'day').format('YYYY-MM-DD HH:mm:ss');

        /* 监听开始时间model */
        // $scope.$watch('copyAct.activity.act_start_time', function(newValue, oldValue) {
        //     if (newValue) {
        //         console.log(newValue)

        //         var thatTime = moment(newValue).format('YYYY-MM-DD');

        //         $scope.act.recurring_end = moment(thatTime).add(1, 'weeks').add(1, 'days')._d;

        //         //结束时间为开始时间同一天
        //         $rootScope.copyAct.activity.act_end_time = moment(thatTime).add(3, 'hours')._d;

        //         //报名截至时间
        //         if (!$rootScope.copyAct.activity.register_end_time) {
        //             $rootScope.copyAct.activity.register_end_time = moment(thatTime).subtract(30, 'minutes')._d;
        //         }

        //     }
        // });

        /* 监听结束时间model */
        // $scope.$watch('copyAct.activity.act_end_time', function(newValue, oldValue) {
        //     if (newValue) {
        //         // console.log(newValue<$rootScope.copyAct.activity.act_start_time)

        //         // 判断结束时间与开始时间是否为同一天
        //         var act_end_time   = newValue.getTime(),
        //             act_start_time = $rootScope.copyAct.activity.act_start_time.getTime(),
        //             same_date      = new Date(act_end_time).toDateString() === new Date(act_start_time).toDateString(),
        //             greater_than   = act_end_time > act_start_time,
        //             less_than      = act_end_time < act_start_time;


        //         // 如果不为同一天，则提示用户，并重置结束时间
        //         if (!same_date) {

        //             ngDialog.open({
        //                 template: '<p>开始时间与结束时间应当在同一天哦</p>',
        //                 className: 'ngdialog-theme-default ngdialog-alert',
        //                 plain: true
        //             });

        //             var thatTime = moment(act_start_time).format('YYYY-MM-DD');
        //             $rootScope.copyAct.activity.act_end_time = moment(thatTime).add(3, 'hours')._d;
        //         }

        //         // 如果为同一天
        //         // if (same_date&&less_than) {

        //         //     ngDialog.open({
        //         //         template: '<p>结束时间应当大于开始时间哦</p>',
        //         //         className: 'ngdialog-theme-default ngdialog-alert',
        //         //         plain: true
        //         //     });

        //         //     var thatTime = moment(act_start_time).format('YYYY-MM-DD');
        //         //     $rootScope.copyAct.activity.act_end_time = moment(thatTime).add(3, 'hours')._d;
        //         // }  

        //         // console.log(act_end_time)
        //         console.log(less_than)



        //         //结束时间小于开始时间时，更新结束时间
        //         // if (newValue<$rootScope.copyAct.activity.act_start_time) {

        //         //     ngDialog.open({
        //         //         template: '<p>开始时间与结束时间应当在同一天哦</p>',
        //         //         className: 'ngdialog-theme-default ngdialog-alert',
        //         //         plain: true
        //         //     });

        //         //     $rootScope.copyAct.activity.act_end_time = $rootScope.copyAct.activity.act_start_time;
        //         // }
        //     }
        // });

        /* 监听结束时间model */
        // $scope.$watch('copyAct.activity.register_end_time', function(newValue, oldValue) {
        //     if (newValue) {

        //         console.log(newValue.getTime())

        //         //报名截至时间大于开始时间时，更新报名截至时间
        //         if (newValue>$rootScope.copyAct.activity.act_start_time) {

        //             ngDialog.open({
        //                 template: '<p>报名截至时间应当小于开始时间哦</p>',
        //                 className: 'ngdialog-theme-default ngdialog-alert',
        //                 plain: true
        //             });


        //             var thatTime = moment($rootScope.copyAct.activity.act_start_time).format('YYYY-MM-DD');

        //             $rootScope.copyAct.activity.register_end_time = moment(thatTime).subtract(30, 'minutes')._d;
        //         }

        //         //报名截至时间小于当前时间时，更新报名截至时间
        //         // if (newValue<nowDate) {

        //         //     ngDialog.open({
        //         //         template: '<p>报名截至时间应当大于当前时间哦</p>',
        //         //         className: 'ngdialog-theme-default ngdialog-alert',
        //         //         plain: true
        //         //     });

        //         //     var thatTime = moment($rootScope.copyAct.activity.act_start_time).format('YYYY-MM-DD');

        //         //     $rootScope.copyAct.activity.register_end_time = moment(thatTime).subtract(30, 'minutes')._d;
        //         // }
        //     }
        // });

        console.log($state.params)
    }

    /* 提交表单 */
    $scope.submitForm = function(isValid) {
        
        // 验证失败，则提示用户
        if (!isValid) {

            if (!$rootScope.copyAct.activity.act_title&&!$scope.myFile&&!$rootScope.copyAct.activity.act_start_time&&
                !$rootScope.copyAct.activity.act_end_time&&!$rootScope.copyAct.activity.register_end_time&&!$scope.act.address&&
                !$rootScopecopyAct.activity.act_desc&&!$scope.act.protocol) {
                $scope.error = '请填写活动基本信息哦';
            } else if (!$rootScope.copyAct.activity.act_title) {
                $scope.error = '请填写活动标题哦';
            } else if(!$scope.myFile) {
                $scope.error = '请填写活动图片哦';
            } else if(!$rootScope.copyAct.activity.act_start_time) {
                $scope.error = '请填写活动开始时间哦';
            } else if(!$rootScope.copyAct.activity.act_end_time) {
                $scope.error = '请填写活动结束时间哦';
            } else if(!$rootScope.copyAct.activity.register_end_time) {
                $scope.error = '请填写活动报名截止时间哦';
            } else if(!$scope.act.address) {
                $scope.error = '请填写活动活动集合地点哦';
            } else if(!$rootScopecopyAct.activity.act_desc) {
                $scope.error = '请填写活动详情哦';
            } else if (!$scope.act.protocol) {
                $scope.error = '发布活动, 必须同意跑团助手平台发布协议哦';
            }

            ngDialog.open({
                template: '<p class="alert-p">{{error}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });

            return false;
        }

        // 验证通过，则发布活动
        if (isValid) {
            
            // 当前时间
            var nowDate = moment();

            // 判断活动时间与当前时间大小，小于为true，大于为false
            var isDate  = moment($rootScope.copyAct.activity.act_start_time) < nowDate ||
                          moment($rootScope.copyAct.activity.act_end_time) < nowDate ||
                          moment($rootScope.copyAct.activity.register_end_time) < nowDate; 

            // 判断活动开始时间、结束时间是否为同一天
            var same_date = new Date($rootScope.copyAct.activity.act_start_time).toDateString() !== new Date($rootScope.copyAct.activity.act_end_time).toDateString();
            
            // 判断报名截至时间是否小于开始时间
            var less_than = moment($rootScope.copyAct.activity.register_end_time) > moment($rootScope.copyAct.activity.act_start_time)

            console.log(less_than)

            if (isDate) {

                ngDialog.open({
                    template: '<p class="alert-p">活动开始时间、结束时间、报名截至时间不能小于当前时间哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true
                });

                return false;
            }

            if (!isDate) {
                if (same_date) {
                    ngDialog.open({
                        template: '<p class="alert-p">活动开始时间、结束时间应当为同一天哦</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true
                    });

                    return false;
                }

                if (less_than) {
                    ngDialog.open({
                        template: '<p class="alert-p">报名截至时间应当小于开始时间哦</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true
                    });

                    return false;
                }
            }

            // 如果活动时间正确，则提交表单
            if (!isDate&&!same_date&&!less_than) {

                //禁用按钮、显示loading
                $scope.hide = true;

                // 活动地址
                $rootScope.copyAct.activity.act_location = $scope.act.addr_id;

                // 签到积分
                $rootScope.copyAct.activity.credits = $scope.act.checkin_num;

                // 限制本跑团
                $rootScope.copyAct.activity.rang_limit = $rootScope.copyAct.activity.rang_limit == 1 ? 1 : 0;

                // 报名字段
                $rootScope.copyAct.regfield = $rootScope.regfield;


                // 格式化活动时间
                var act_start_time = moment($rootScope.copyAct.activity.act_start_time).format('YYYY-MM-DD HH:mm:ss');
                var act_end_time = moment($rootScope.copyAct.activity.act_end_time).format('YYYY-MM-DD HH:mm:ss');
                var register_end_time = moment($rootScope.copyAct.activity.register_end_time).format('YYYY-MM-DD HH:mm:ss');

                // 判断是否为复制活动, 拼接POST数据格式
                if ($state.params.act_id && $state.params.type == 'copy') {
                    var params = {
                        // 'act_image': $scope.myFile,
                        'act_start_time': act_start_time,
                        'act_end_time': act_end_time,
                        'register_end_time': register_end_time,
                        'recurring_type': $scope.act.cycle?1:0,
                        'recurring_end': $scope.act.cycle?moment($scope.act.recurring_end).format('YYYY-MM-DD HH:mm:ss'):'0000-00-00 00:00:00',
                        'club_id': clubInfo.clubid,
                        'activity': $rootScope.copyAct.activity, 
                        'project': $rootScope.copyAct.project,
                        'regfield': $rootScope.regfield,
                        'type': 'save'
                    }
                }
                
                // 判断是否为编辑活动, 拼接POST数据格式
                if ($state.params.act_id && $state.params.type == 'edit') {
                    var params = {
                        // 'act_image': $scope.myFile,
                        'act_start_time': act_start_time,
                        'act_end_time': act_end_time,
                        'register_end_time': register_end_time,
                        'club_id': clubInfo.clubid,
                        'activity': $rootScope.copyAct.activity, 
                        'project': $rootScope.copyAct.project,
                        'regfield': $rootScope.regfield,
                        'type': 'edit'
                    }
                }

                // 判断是否为新建活动，拼接POST数据格式
                if (!($state.params.act_id&&($state.params.type=='copy'||$state.params.type=='edit'))) {
                    var params = {
                        // 'act_image': $scope.myFile,
                        'act_start_time': act_start_time,
                        'act_end_time': act_end_time,
                        'register_end_time': register_end_time,
                        'recurring_type': $scope.act.cycle?1:0,
                        'recurring_end': $scope.act.cycle?moment($scope.act.recurring_end).format('YYYY-MM-DD HH:mm:ss'):'0000-00-00 00:00:00',
                        'club_id': clubInfo.clubid,
                        'activity': $rootScope.copyAct.activity, 
                        'project': $rootScope.copyAct.project,
                        'regfield': $rootScope.regfield,
                        'type': 'new'
                    }
                }

                //添加活动详情(html格式)
                // params.activity.act_desc = editor.html();
                console.log(params)

                // 保存活动信息
                httpService.postCopyActivity(access_token, params)
                .then(function(data){

                    // 显示按钮、隐藏loading
                    $scope.hide = false;

                    // 保存成功后，返回活动列表页面
                    if (data.code==1) {

                        // 上传图片，判断是否修改了图片，是则type为has，否则no
                        httpService.postUploadImage(access_token, {
                            'act_id': data.message,
                            'act_image': $scope.myFile,
                            'type': $rootScope.isNewImage ? 'has' : 'no',
                            'status': $state.params.type
                        })
                        .then(function(data){

                            if (data.code==1) {
                                ngDialog.open({
                                    template: '<p class="alert-p">保存成功哦</p>',
                                    className: 'ngdialog-theme-default ngdialog-alert',
                                    plain: true
                                });
                                $state.go('index.activity');
                            }

                            console.log(data)
                        }, function(data) {
                            console.log('error')
                        });

                    }

                    // 保存失败后，提示用户
                    if (data.code==0) {
                        ngDialog.open({
                            template: '<p class="alert-p">保存失败哦</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true
                        });
                    }

                    // 新建活动成功后，跳转至签到页面
                    if (data.code==2) {

                        if (data.message) {

                            var new_act_id = data.message;

                            // 上传图片
                            httpService.postUploadImage(access_token, {
                                'act_id': new_act_id,
                                'act_image': $scope.myFile,
                                'type': $rootScope.isNewImage ? 'has' : 'no',
                                'status': 'new'
                            })
                            .then(function(data2){

                                if (data2.code==1) {
                                    /* 如果新建活动标识存在, 则清除之 */
                                    if (store.get('new_act_id')) {
                                        store.remove('new_act_id')
                                    }

                                    /* 缓存新建活动标识至localStorage */
                                    store.set('new_act_id', new_act_id);

                                    /* 获取创建成功的活动信息 */
                                    httpService.postActInfo(access_token, {
                                        'club_id': clubInfo.clubid,
                                        'act_id': new_act_id
                                    })
                                    .then(function(data3){

                                        if (data3.code==1) {
                                            store.set('newactInfo',data3);
                                            $state.go('signinfo', {newact: 1});
                                        }

                                        console.log(data3)
                                    }, function(data3) {
                                        console.log('error')
                                    });
                                }

                                console.log(data2)
                            }, function(data2) {
                                console.log('error')
                            });

                            
                        }

                    }

                    console.log(data)
                }, function(data) {
                    console.log('error')
                });
            }


            console.log( $rootScope.regfield)
        }

        return false;
    }

    /* 获取上传图片的地址url，用以实现预览图片 */
    $scope.getFile = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {
            console.log($scope.file)

            //判断附件是否为图片
            if(!/image\/\w+/.test($scope.file.type)){

                ngDialog.open({
                    template: '<p class="alert-p">图片类型必须是.gif,jpeg,jpg,png中的一种哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true
                });
                
                return false;
            } else {
                $scope.imageSrc = result;
                $rootScope.isNewImage = true;
            }
            
        }, function(data) {
            console.log('error')
        });
    };

    /* 获取活动集合地点 */
    $scope.findlocation = function() {

        $scope.showaAddr = true;

        httpService.postFindLocation(access_token, {'club_id': clubInfo.clubid})
        .then(function(data){
            $scope.address = data.message;
            $scope.ip = data.ip;
            console.log(data)
        }, function(data) {
            console.log('error')
        });

        ngDialog.open({
            template: './tpl/newaddr.dialog.html',
            className: 'ngdialog-theme-default',
            scope: $scope
        });
    };

    /* 点击选中当前活动地址 */
    $scope.selectedAddr = function(item) {
        console.log(item)
        $scope.act.address = item.location;
        $scope.act.addr_id = item.id;
        $scope.showaAddr = false;
    };

    /* 截取活动集合地点数组 */
    $scope.delete = function(item, index, $event) {

        console.log(item)

        // 阻止冒泡
        $event.stopPropagation();

        // 删除活动地址
        httpService.postUpdateLocation(access_token, {'id': item.id, 'types': 'del'})
        .then(function(data) {

            // 判断活动地址是否已被使用，否则提示用户
            if (data.code==0) {
                ngDialog.open({
                    template: '<p class="alert-p">不能删除已有活动地址信息哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true
                });
            }

            // 判断活动地址是否已被使用，是则删除之
            if (data.code==1) {
                $scope.address.splice(index, 1);
            }

            console.log(data)
        }, function(data) {
            console.log('error')
        });
    };
    
    /* 获取地图、城市信息 */
    $scope.initMap = function(lat, lng) {

        // 如果默认纬、经度不存在，则设置默认
        if(!lat||!lng){
            lat = 31.231592;
            lng = 121.478577;
        }

        /* 创建地图 */
        var map = mapService.createMap("container", 39.916527, 116.397128);

        $scope.marker = mapService.getMarker(map);  //缓存地图标识
        $scope.info   = mapService.getInfo(map);    //缓存地图信息
        $scope.label  = mapService.getLabel(map);   //缓存地图标签

        /* 如果IP存在，则根据IP查询城市信息，否则设置默认城市信息 */
        if($scope.ip){
            var cityLocation = mapService.getCityLocation(map, function(latLng){

                /* 纬度、经度 */
                var coord = {
                    lat: latLng.lat,
                    lng: latLng.lng
                };
                
                /* 改变地图中心点位置 */
                mapService.setMapMarker($scope.marker, coord.lat, coord.lng, $scope.info);

                /* 获取城市信息 */
                mapService.geocoder(function(address){
                    $scope.$apply(function() {
                        $scope.mapAddress = address;
                        $scope.coord = {
                            lat: coord.lat,
                            lng: coord.lng
                        };
                    });
                    console.log(address)
                    mapService.setLabel($scope.label, $scope.marker, address);
                }, coord.lat, coord.lng);
            });

            /* 根据IP查询城市信息 */
            cityLocation.searchCityByIP($scope.ip);
        } else {

            /* 改变地图中心点位置 */ 
            mapService.setMapMarker($scope.marker, lat, lng, $scope.info);

            /* 获取城市信息 */
            mapService.geocoder(function(address){
                $scope.$apply(function() {
                    $scope.mapAddress = address;
                    $scope.coord = {
                        lat: lat,
                        lng: lng
                    };
                });
                console.log(address)
                mapService.setLabel($scope.label, $scope.marker, address);
            }, lat, lng);
        }

        /* 点击地图事件，改变地图中心点位置，获取城市信息 */
        qq.maps.event.addListener(map, 'click', function(event) {

            /* 纬度、经度 */
            var coord = {
                lat: event.latLng.getLat(),
                lng: event.latLng.getLng()
            };

            /* 改变地图中心点位置 */
            mapService.setMapMarker($scope.marker, coord.lat, coord.lng, $scope.info);

            /* 获取城市信息 */
            mapService.geocoder(function(address){
                $scope.$apply(function() {
                    $scope.mapAddress = address;
                    $scope.coord = {
                        lat: coord.lat,
                        lng: coord.lng
                    };
                });
                console.log(address)
                mapService.setLabel($scope.label, $scope.marker, address);
            }, coord.lat, coord.lng);
        }); 

    };

    /* 新建活动地址模态框 */
    $scope.newAddr = function() {

        // QQMap模态框
        ngDialog.open({
            template: './tpl/qqmap.dialog.html',
            className: 'ngdialog-theme-default map',
            scope: $scope
        });

        // 延迟250ms初始化地图
        $timeout(function() {
            $scope.initMap();
        }, 250);
    };

    /* 新建活动地址 */
    $scope.createLocation = function(act_name) {

        // 判断是否设置别名，否则提示用户
        if (!act_name) {
            ngDialog.open({
                template: '<p class="alert-p">请设置别名哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true
            });
        }

        // 判断是否设置别名，是则提交表单
        if (act_name) {

            // 更新地址
            $scope.act.address = act_name;

            /* 拼接POST数据格式 */
            var params = {
                club_id: clubInfo.clubid,
                name: $scope.act_name,
                location: $scope.act.address,
                lat: $scope.coord.lat,
                lng: $scope.coord.lng
            };

            // 保存数组，成功后关闭模态框
            httpService.postCreateLocation(access_token, params)
            .then(function(data){
                ngDialog.close();
                console.log(data)
            }, function(data) {
                console.log('error')
            });

        }
    };

    /* 高级设置 */
    $scope.settings = function(isValid) {

        //遍历数组，获取复制活动中已存在的报名字段信息，标记col_name_copy: true
        angular.forEach($rootScope.regfield, function(value, index) {
            angular.forEach($scope.join_info, function(value2, index2) {
                if (value.col_name==value2.field) {
                    value2['col_name_copy'] = true;
                }
            });
        });

        /* 高级设置页面 */
        ngDialog.open({
            template: './tpl/copy.activity.settings.html',
            className: 'ngdialog-theme-default ngdialog-addtypetext dialog-bg-f5f5f5',
            scope: $scope
        });

    }

    /* 增加活动项目 */
    $scope.addProject = function() {

        // 取消失约扣除
        $scope.cancelMeet = function() {
            $scope.project.meet_consume = 0
        };

        /* 失约扣除 */
        $scope.showMeetConsume = function() {
            ngDialog.open({
                template: 
                        '<div class="addemail text-center">'+
                            '<div class="col-xs-offset-1 col-xs-10">'+
                                '<h5>请输入失约扣除积分</h5>'+
                                '<input type="text" class="form-control" name="meetConsume" ng-model="project.meet_consume" placeholder="">'+
                            '</div>'+
                            '<button type="button" class="btn btn-cancel" ng-click="cancelMeet(); closeThisDialog()">取消</button>'+
                            '<button type="submit" class="btn btn-add" ng-click="closeThisDialog()">确定</button>'+
                        '</div>',
                className: 'ngdialog-theme-default',
                plain: true,
                scope: $scope
            });
        };

        /* 表单数据 */
        $scope.project = {
            name: null,
            money: null,
            number: null,
            reg_gain: 0,
            reg_num: 0,
            meet_consume: 0
        };

        /* 活动项目页面 */
        ngDialog.open({
            template: './tpl/copy.activity.project.html',
            className: 'ngdialog-theme-default ngdialog-addtypetext',
            scope: $scope
        });

        /* 表单提交 */
        $scope.saveProject = function(isValid) {

            // 验证失败，提示用户
            if (!isValid) {
                
                ngDialog.open({
                    template: '<p class="alert-p">验证失败哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true,
                    scope: $scope
                });

                return false;
            }

            // 验证通过，拼接数据缓存起来
            if (isValid) {

                /* 拼接POST数据格式 */
                var params = {
                    // 'activity_id': $state.params.act_id,
                    'project_name': $scope.project.name,
                    'price': $scope.project.money,
                    'project_num': $scope.project.number,
                    'reg_gain': $scope.project.reg_gain == 0 ? $scope.project.reg_num : 0,
                    'reg_consume': $scope.project.reg_gain == 1 ? $scope.project.reg_num : 0,
                    'meet_consume': $scope.project.meet_consume
                }

                //插入数组中
                $rootScope.copyAct.project.push(params);

                console.log(params);

            }

            return false;
        };
    };

    /* 编辑活动项目 */
    $scope.editProject = function(item, index) {

        console.log(item)

        /* 失约扣除 */
        $scope.showMeetConsume = function() {
            ngDialog.open({
                template: '<input type="text" name="meetConsume" ng-model="project.meet_consume">',
                className: 'ngdialog-theme-default',
                plain: true,
                scope: $scope
            });
        };

        /* 表单数据 */
        $scope.project = {
            name: item.project_name,
            money: item.price,
            number: item.project_num,
            reg_gain: item.reg_consume==0?0:1,
            reg_num: item.reg_consume==0?item.reg_gain:item.reg_consume,
            meet_consume: item.meet_consume
        };

        /* 活动项目页面 */
        ngDialog.open({
            template: './tpl/copy.activity.project.html',
            className: 'ngdialog-theme-default ngdialog-addtypetext',
            scope: $scope
        });

        /* 表单提交 */
        $scope.saveProject = function(isValid) {

            // 验证失败，提示用户
            if (!isValid) {
                
                ngDialog.open({
                    template: '<p class="alert-p">验证失败哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true,
                    scope: $scope
                });

                return false;
            }

            // 验证通过，截取数组
            if (isValid) {

                /* 拼接POST数据格式 */
                var params = {
                    // 'activity_id': $state.params.act_id,
                    'project_name': $scope.project.name,
                    'price': $scope.project.money,
                    'project_num': $scope.project.number,
                    'reg_gain': $scope.project.reg_gain == 0 ? $scope.project.reg_num : 0,
                    'reg_consume': $scope.project.reg_gain == 1 ? $scope.project.reg_num : 0,
                    'meet_consume': $scope.project.meet_consume
                }

                //截取数组
                $rootScope.copyAct.project.splice(index, 1, params);

                console.log(params);

            }

            return false;
        };
    };

    /* 新增报名填写的信息 */
    $scope.addFn = function(item) {

        //字段类型标识
        $scope.thisType = item.fieldtype;

        //判断字段类型：1为文本, 2为列表, 3为日期, 4为时间, 5为文件
        //列表类型
        if ($scope.thisType == 2) {

            //隐藏标题
            $scope.showTitle = false;

            //空数组用以缓存数据
            var list = [];
                
            //初始化列表选项
            $scope.texts = [
                {
                    text:''
                }
            ];
            
            //自定义字段模态框
            ngDialog.open({
                template: './tpl/copy.addtypetext.dialog.html',
                className: 'ngdialog-theme-default ngdialog-addtypetext',
                scope: $scope
            });

            //提交表单
            $scope.saveFieldtype = function(isValid) {

                /* 验证失败 */
                if (!isValid) {
                    ngDialog.open({
                        template: '<p class="alert-p">请填写字段哦</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true
                    });
                    return false;
                }

                /* 验证成功 */
                if (isValid) {

                    /* 拼接列表内容字符串 */
                    angular.forEach($scope.texts, function(value, index) {
                        list = list + ',' + value.text;
                    });

                    list = list.substr(1);
                    console.log(list);

                    /* 拼接报名字段数据格式 */
                    var params = {
                        'col_name': item.field,
                        'col_title': item.display,
                        'col_type': item.fieldtype,
                        'col_list_values': list?list:0
                    };

                    //插入数组中
                    $rootScope.regfield.push(params);

                    console.log($rootScope.regfield);

                }

                return false;
            }
        }

        //非列表类型
        if ($scope.thisType !== 2) {

            /* 拼接报名字段数据格式 */
            var params = {
                'col_name': item.field,
                'col_title': item.display,
                'col_type': item.fieldtype,
                'col_list_values': 0
            };

            //插入数组中
            $rootScope.regfield.push(params);

            console.log($rootScope.regfield);

        }

    };

    /* 删除报名填写的信息 */
    $scope.delFn = function(item) {

        console.log(item)

        /* 拼接报名字段数据格式 */
        var params = {
            'col_name': item.field,
            'col_title': item.display,
            'col_type': item.fieldtype,
            'col_list_values': item.listvalue?item.listvalue:0
        };

        //数组中删除数据
        angular.forEach($rootScope.regfield, function(value, index) { 
            //遍历数组，获取已移除报名字段信息，标记col_name_copy: false
            angular.forEach($scope.join_info, function(value2, index2) {
                if (value.col_name==value2.field&&value.col_name==params.col_name) {
                    value2['col_name_copy'] = value2['flag'] = false;
                    $rootScope.regfield.splice(index, 1);
                }
            });
        });

        console.log($rootScope.regfield);

    };

    /* 新建报名填写的类型 */
    $scope.addType = function() {
        ngDialog.open({
            template: './tpl/addtype.dialog.html',
            className: 'ngdialog-theme-default ngdialog-addtype',
            scope: $scope
        });
    };

    /* 新建报名填写的类型 */
    $scope.col_len = 0;
    $scope.newTitle = '';
    $scope.addTypeText = function(index) {

        //判断自定义字段个数不能超过15个
        if ($scope.col_len>=15) {
            ngDialog.open({
                template: '<p class="alert-p">自定义字段最多15个哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true
            });
            return false
        }

        //判断自定义字段个数不能超过8个
        if ($scope.col_len<15){
            //字段类型标识
            $scope.thisType = index + 1;

            //自定义字段模态框
            ngDialog.open({
                template: './tpl/copy.addtypetext.dialog.html',
                className: 'ngdialog-theme-default ngdialog-addtypetext',
                scope: $scope
            });

            //判断字段类型：1为文本, 2为列表, 3为日期, 4为时间, 5为文件
            //列表类型
            if ($scope.thisType == 2) {

                //显示标题
                $scope.showTitle = true;

                //空数组用以缓存数据
                var list = [];
                
                //初始化列表选项
                $scope.texts = [
                    {
                        text:'选项1'
                    },
                    {
                        text:'选项2'
                    },
                    {
                        text:'选项3'
                    }
                ];

                //提交表单
                $scope.saveFieldtype = function(isValid) {

                    /* 验证失败 */
                    if (!isValid) {
                        ngDialog.open({
                            template: '<p class="alert-p">请填写字段哦</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true
                        });
                        return false;
                    }

                    /* 验证成功 */
                    if (isValid) {

                        /* 拼接列表内容字符串 */
                        angular.forEach($scope.texts, function(value, index) {
                            list = list + ',' + value.text;
                        });

                        list = list.substr(1);   

                        /* 拼接POST数据格式 */
                        var params = {
                            'col_name': 'reserved_field_' + ($scope.col_len + 1),
                            'col_title': document.getElementById('newTitle').value,
                            // 'visible': 1,
                            // 'optional': 1,
                            // 'comment': '',
                            'col_type': $scope.thisType,
                            'col_list_values': list?list:0
                        };

                        var field = {
                            'desc': '',
                            'display': document.getElementById('newTitle').value,
                            'field': 'reserved_field_' + ($scope.col_len + 1),
                            'fieldtype': $scope.thisType,
                            'listvalue': list?list:0,
                            'sort': $rootScope.join_info.length,
                            'system': 1,
                            'flag': true
                        };

                        console.log(params)

                        //自定义类型个数自增1
                        $scope.col_len++;

                        //插入数组中
                        $rootScope.regfield.push(params);

                        //插入数组中渲染到页面
                        $rootScope.join_info.push(field)

                        console.log($rootScope.regfield);

                    }

                    return false;
                }

            } 

            //非列表类型
            if ($scope.thisType !== 2) {

                //提交表单
                $scope.saveFieldtype = function(isValid) {

                    /* 验证失败 */
                    if (!isValid) {
                        ngDialog.open({
                            template: '<p class="alert-p">请填写字段哦</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true
                        });
                        return false;
                    }

                    /* 验证成功 */
                    if (isValid) {

                        /* 拼接自定义字段数据格式 */
                        var params = {
                            'col_name': 'reserved_field_' + ($scope.col_len + 1),
                            'col_title': document.getElementById('newTitle').value,
                            // 'visible': 1,
                            // 'optional': 1,
                            // 'comment': '',
                            'col_type': $scope.thisType,
                            'col_list_values': 0
                            
                        };

                        var field = {
                            'desc': '',
                            'display': document.getElementById('newTitle').value,
                            'field': 'reserved_field_' + ($scope.col_len + 1),
                            'fieldtype': $scope.thisType,
                            'col_list_values': 0,
                            'sort': $rootScope.join_info.length,
                            'system': 1,
                            'flag': true
                        };

                        console.log(params);

                        //自定义类型个数自增1
                        $scope.col_len++;

                        //插入数组中
                        $rootScope.regfield.push(params);

                        //插入数组中渲染到页面
                        $rootScope.join_info.push(field);

                        console.log($rootScope.join_info);

                    }

                    return false;
                }
            }
        }
    };

    /* 协议预览 */
    $scope.protocol = function() {
        ngDialog.open({
            template: './tpl/protocol.html',
            className: 'ngdialog-theme-default ngdialog-addtypetext overflow-scroll',
        });
    };

}]);


/**
 * [报名信息 控制器]
 * @param  {[type]} $scope       [description]
 * @param  {[type]} $http        [description]
 * @param  {[type]} $state       [description]
 * @param  {[type]} ngDialog     [模态框]
 * @param  {[type]} httpService  [自定义接口服务]
 * @return {[type]}              [description]
 */
ActivityModule.controller('RegInfoController', ['$scope', '$http', '$state', 'ngDialog', 'httpService', 'fileReader', function($scope, $http, $state, ngDialog, httpService, fileReader) {

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前活动信息 */
    $scope.actInfo = store.get('actInfo');
    $scope.actId   = $state.params.act_id;

    var innerHtml = "";

    // 文件存储
    $scope.myFile = [];

    httpService.postRegProject(access_token, {
        'act_id': $state.params.act_id,
        'pro_id': $state.params.pro_id
    })
    .then(function(data){

        /* 判断数据是否为空 */
        if (data.message&&data.message.length>0) {

            $scope.regInfo  = data.message;
            $scope.reg_info = data.reg_info;
            $scope.total    = 0;

            angular.forEach(data.reg_info, function(value, index) {
                $scope.total+=parseFloat(value.price);
            });

            $scope.total = $scope.total.toFixed(2);

            var fileNo = 0;

            var oTest = document.getElementById("regInfo_form");

            angular.forEach($scope.regInfo, function(value, index) {

                // 新节点
                var newNode = document.createElement("div");
                var reforeNode = document.getElementById("file" + fileNo);

                //文本
                if (value.col_type == 1) {

                    newNode.innerHTML ="<label class='margin-top-10px'>" + value.col_title + "</label><input type='text' class='form-control margin-top-5px' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + ">";  
                    oTest.insertBefore(newNode,reforeNode);

                    // innerHtml += "<div class='margin-top-10px'><label>" + value.col_title + "</label><input type='text' class='form-control margin-top-5px' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + "></div>"
                } else if (value.col_type == 2) {

                    //1.0的场合(两版本分隔符不一样暂时这样处理)
                    var obj;
                    if (value.col_list_values.indexOf('`') != -1) {
                        obj = value.col_list_values.split('`');
                    } else {
                        obj = value.col_list_values.split(",");
                    }
                    //列表
                    var option = "<option value=''></option>";
                    angular.forEach(obj, function(objvalue, objindex) {
                        option += "<option value =" + objvalue + ">" + objvalue + "</option>";
                    })

                    newNode.innerHTML = "<label class='margin-top-10px'>" + value.col_title + "</label><select class='form-control margin-top-5px' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + ">" + option + "</select>"
                    oTest.insertBefore(newNode,reforeNode);

                    // innerHtml += "<div class='margin-top-10px'><label>" + value.col_title + "</label><select class='form-control margin-top-5px' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + ">" + option + "</select></div>"
                } else if (value.col_type == 3) {
                    // 日期
                    newNode.innerHTML = "<label class='margin-top-10px'>" + value.col_title + "</label><input type='date' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + " class='form-control margin-top-5px'>"
                    oTest.insertBefore(newNode,reforeNode);
                    // innerHtml += "<div class='margin-top-10px'><label>" + value.col_title + "</label><input type='date' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + " class='form-control margin-top-5px'></div>"
                } else if (value.col_type == 4) {
                    // 时间
                    newNode.innerHTML = "<label class='margin-top-10px'>" + value.col_title + "</label><input type='time' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + " class='form-control margin-top-5px'>"
                    oTest.insertBefore(newNode,reforeNode);
                    // innerHtml += "<div class='margin-top-10px'><label>" + value.col_title + "</label><input type='time' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + " class='form-control margin-top-5px'></div>"
                } else if (value.col_type == 6) {
                    // 电子邮件
                    newNode.innerHTML = "<label class='margin-top-10px'>" + value.col_title + "</label><div class='input-group'><input type='text' class='form-control margin-top-5px' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + " aria-describedby='basic-addon2' required<span class='input-group-addon' id='basic-addon2'>@example.com</span></div>"
                    oTest.insertBefore(newNode,reforeNode);
                    // innerHtml += "<div class='margin-top-10px'><label>" + value.col_title + "</label><div class='input-group'><input type='text' class='form-control margin-top-5px' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + " aria-describedby='basic-addon2' required<span class='input-group-addon' id='basic-addon2'>@example.com</span></div></div>"
                } else if (value.col_type == 7) {
                    // 数字
                    newNode.innerHTML = "<label class='margin-top-10px'>" + value.col_title + "</label><input type='number' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + " class='form-control margin-top-5px'>"
                    oTest.insertBefore(newNode,reforeNode);
                    // innerHtml += "<div class='margin-top-10px'><label>" + value.col_title + "</label><input type='number' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + " class='form-control margin-top-5px'></div>"
                } else if (value.col_type == 8) {
                    // 长文本
                    newNode.innerHTML = "<label class='margin-top-10px'>" + value.col_title + "</label><textarea id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + " class='form-control margin-top-5px' rows='5'></textarea>"
                    oTest.insertBefore(newNode,reforeNode);
                    innerHtml += "<div class='margin-top-10px'><label>" + value.col_title + "</label><textarea id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + " class='form-control margin-top-5px' rows='5'></textarea></div>"
                } else if (value.col_type == 5) {
                    // 文件
                    // innerHtml += "<div class='margin-top-10px'><label>" + value.col_title + "</label><input  type='file' id=" + $scope.regInfo[index].col_name + " name=" + $scope.regInfo[index].col_name + " class='form-control margin-top-5px' required></div>"
                    var filediv = document.getElementById('file' + fileNo);

                    document.getElementById('file' + fileNo).style.display = "block";
                    //设置显示内容
                    filediv.children[0].innerHTML = value.col_title;
                    //设置id
                    filediv.children[1].name = $scope.regInfo[index].col_name;
                     // 设置name
                    filediv.children[1].id = $scope.regInfo[index].col_name;
                    // 计数器增加
                    fileNo++;
                } 
                console.log(index);

            });
            //生成html
            // document.getElementById('regInfo').innerHTML = innerHtml; 
        }

        console.log($scope.regInfo)

        console.log(data)
    }, function(data) {
        console.log('error')
    });

    /* 提交表单 */
    $scope.submitForm = function(isValid) {

        // if (!isValid) {
            
        //     ngDialog.open({
        //         template: '<p class="alert-p">验证失败哦</p>',
        //         className: 'ngdialog-theme-default ngdialog-alert',
        //         plain: true,
        //         scope: $scope
        //     });
        //     return false;
        // }

        // 表单验证
        // 光标
         var focus = "";
        //循环报名所填的所有项目
        var size = $scope.regInfo.length;
        angular.forEach(document.getElementById("regInfo_form").children, function (value, index) {

            if (index >= size) {
                return;
            }

            //文件的场合
            if (value.id) {
                //没有上传文件的场合
                if (!$scope.myFile[parseInt(value.id.substr(4, 1))]) {
                    document.getElementById(value.id).children[1].style.border = "1px solid #fe4557";
                    //更新focus的值
                    if (focus == "") {
                        focus = "file" + i;
                    }
                } else {
                    //边框恢复为灰色
                    document.getElementById(value.id).children[1].style.border = "1px solid #ccc";
                }

            } else {
                //非文件的场合
                //控件值为空的场合
                if (value.children[1].value == "") {
                    // 变边框颜色
                    value.children[1].style.border = "1px solid #fe4557";
                    //光标为空的场合给光标赋值
                    if (focus == "") {
                        focus = value.children[1].id;
                    }
                    console.log(index);
                } else{
                    // 边框颜色恢复
                    value.children[1].style.border = "1px solid #ccc";
                    console.log(index);
                }
            }

        })

        // for (var i = 0; i < 10; i++) {
        //     //所有显示的文件控件
        //     if (document.getElementById("file" + i).style.display) {
        //         //没有上传文件的场合
        //         if (!$scope.myFile[i]) {
        //             document.getElementById("file" + i).children[1].style.border = "1px solid #fe4557";
        //             //更新focus的值
        //             if (focus == "") {
        //                 focus = "file" + i;
        //             }
        //         } else {
        //             //边框恢复为灰色
        //             document.getElementById("file" + i).children[1].style.border = "1px solid #ccc";
        //         }
        //     }
        // }
        // focus不为空的场合（有未填的项目）
        if (focus != "") {
            //设置光标
            document.getElementById(focus).focus();
            // 弹窗警告
            ngDialog.open({
                template: '<p class="alert-p">报名信息没有填写完整哟</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true
            });
            return;
        }

        if (isValid) {

            // 禁用按钮、显示loading
            $scope.hide = true;

            var params = { };

            /* 拼接POST数据格式 */
            params['act_id'] = $state.params.act_id,

            angular.forEach($scope.regInfo, function(value, index) {
                if (value.col_type != 5) {
                    params[value.col_name] = document.getElementById(value.col_name).value;
                } else {
                    console.log($scope.myFile[document.getElementById(value.col_name).parentElement.id.substr(4, 1)])
                    // params[value.col_name] = $scope.myFile;document.getElementById(value.col_name)
                    params[value.col_name] = $scope.myFile[document.getElementById(value.col_name).parentElement.id.substr(4, 1)];
                }           
            });
            
            console.log(params)

            /* 提交报名项目信息 */
            httpService.postUserRegInfo(access_token, params)
            .then(function(data){

                // 显示按钮、隐藏loading
                $scope.hide = false;

                // 判断是否已报名
                if (data.code==0) {

                    //返回活动详情
                    $scope.backDetail = function() {
                        $state.go('activityDetail', {act_id: $state.params.act_id})
                    };

                    ngDialog.open({
                        template: '<p class="alert-p">您已经报过名了哦</p>'+
                                    '<button type="buttom" class="btn btn-red" ng-click="backDetail(); closeThisDialog();">返回活动详情</button>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true,
                        scope: $scope
                    });
                }

                // 判断是否提交成功
                if (data.code==1) {

                    // 需支付，跳转页面至支付页
                    if (data.message.order_id) {

                        if (store.get('orderDetail')) {
                            store.remove('orderDetail')
                        }

                        // 缓存订单详情
                        store.set('orderDetail', data.message);

                        // 跳转页面至支付页
                        $state.go('pay', {act_id: $state.params.act_id});
                    }   

                    // 无需支付，提示用户报名成功并跳转页面至详情页
                    if (!data.message.order_id) {

                        //返回活动详情
                        $scope.backDetail = function() {
                            $state.go('activityDetail', {act_id: $state.params.act_id})
                        };

                        ngDialog.open({
                            template: '<p class="alert-p">恭喜您, 报名成功哦</p>'+
                                        '<button type="buttom" class="btn btn-red" ng-click="backDetail(); closeThisDialog();">返回活动详情</button>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true,
                            scope: $scope
                        });
                    }
                }

                // 判断是否上传图片
                if (data.code==4) {
                    ngDialog.open({
                        template: '<p class="alert-p">请上传图片哦</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true
                    });
                }

                console.log(data)
            }, function(data) {
                console.log('error')
            });
        }

        return false;
    };
    /* 获取上传文件 */
    $scope.getFile0 = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {

            console.log($scope.file)

            $scope.myFile[0] = $scope.file;

        });
    };
    $scope.getFile1 = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {

            console.log($scope.file)

            $scope.myFile[1] = $scope.file;

        });
    };
    $scope.getFile2 = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {

            console.log($scope.file)

            $scope.myFile[2] = $scope.file;

        });
    };
    $scope.getFile3 = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {

            console.log($scope.file)

            $scope.myFile[3] = $scope.file;

        });
    };
    $scope.getFile4 = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {

            console.log($scope.file)

            $scope.myFile[4] = $scope.file;

        });
    };
    $scope.getFile5 = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {

            console.log($scope.file)

            $scope.myFile[5] = $scope.file;

        });
    };
    $scope.getFile6 = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {

            console.log($scope.file)

            $scope.myFile[6] = $scope.file;

        });
    };
    $scope.getFile7 = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {

            console.log($scope.file)

            $scope.myFile[7] = $scope.file;

        });
    };
    $scope.getFile8 = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {

            console.log($scope.file)

            $scope.myFile[8] = $scope.file;

        });
    };
    $scope.getFile9 = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {

            console.log($scope.file)

            $scope.myFile[9] = $scope.file;

        });
    };

    /* 点赞功能 */
    // $scope.praise = function() {
    //     httpService.postPraise(access_token, {'activity_id': $scope.actInfo.act_id})
    //     .then(function(data) {

    //         //取消点赞
    //         if (data.code==0) {
    //             $scope.isPraise = false;
    //             //更新点赞数目
    //             $scope.actInfo.act_praise--;
    //             //更新活动信息缓存
    //             if (store.get('actInfo')) {
    //                 store.remove('actInfo')
    //             }
    //             store.set('actInfo', $scope.actInfo)
    //             console.log(store.get('actInfo'))
    //         }

    //         //点赞
    //         if (data.code==1) {
    //             $scope.isPraise = true;
    //             //更新点赞数目
    //             $scope.actInfo.act_praise++;
    //             //更新活动信息缓存
    //             if (store.get('actInfo')) {
    //                 store.remove('actInfo')
    //             }
    //             store.set('actInfo', $scope.actInfo)
    //             console.log(store.get('actInfo'))
    //         }
    //         console.log(data)
    //     }, function(data) {
    //         console.log('error')
    //     });
    // }

}]);


/**
 * [报名付款 控制器]
 * @param  {[type]} $scope       [description]
 * @param  {[type]} $http        [description]
 * @param  {[type]} $state       [description]
 * @param  {[type]} httpService  [自定义接口服务]
 * @param  {[type]} getDateDiff  [自定义计算时间差服务]
 * @return {[type]}              [description]
 */
ActivityModule.controller('PayController', ['$rootScope', '$scope', '$http', '$state', 'ngDialog', 'httpService', 'getDateDiff', function($rootScope, $scope, $http, $state, ngDialog, httpService, getDateDiff) {

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前活动信息 */
    $scope.actInfo = store.get('actInfo');
    $scope.actId   = $state.params.act_id;

    /* 获取当前支付信息 */
    httpService.postUserReg(access_token, {'act_id': $state.params.act_id})
    .then(function(data){

        // code为2时, 已报名未支付
        if (data.code==2) {

            $scope.user_info = [];
            $scope.payInfo   = data.message;

            // 变量取出用户基本信息: 姓名、手机号
            angular.forEach(data.message.user_info, function(value, index) {
                console.log(index)
                if (index=='passport_name'||index=='cell') {
                    $scope.user_info.push(value)
                }
            })
            
        }

        console.log(data)
    }, function(data) {
        console.log('error')
    });

    /* 判断是否微信, 设置默认支付状态 */
    function isWeiXin(){
        var ua = window.navigator.userAgent.toLowerCase();
        if(ua.match(/MicroMessenger/i) == 'micromessenger'){
            return true;
        }else{
            return false;
        }
    }

    $scope.pay = isWeiXin() ? 0 : 1;

    /* 提交表单 */
    $scope.submitForm = function(isValid) {

        if (!isValid) {
            ngDialog.open({
                template: '<p class="alert-p">验证失败哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        if (isValid) {

            // 禁用按钮、显示loading
            $scope.hide = false;

            console.log($scope.pay)

            /* 微信支付 */
            if ($scope.pay==0) {
                var order_id = $scope.payInfo.order_id;
                var div = document.createElement('div');
                var form = '<form action="http://wechat.paobuqu.com/wxpay/actpay" method="post" name="wxsubmit">'+
                '<div class="form-group"><label for=""></label><input type="text" class="form-control" name="order_id" value="'+order_id+'"></div>'+
                '<button type="submit" class="btn btn-default">Submit</button>'+
                '</form>';
                console.log(div.style)
                div.style.display = 'none';
                div.innerHTML = form;
                document.body.appendChild(div);
                console.log(document.forms['wxsubmit']['order_id'].value)
                document.forms['wxsubmit'].submit();

                // 显示按钮、隐藏loading
                $scope.hide = false;
            }

            /* 支付宝支付 */
            if ($scope.pay==1) {

                /* 拼接POST数据格式 */
                var params = {
                    'out_trade_no': $scope.payInfo.order_id
                };

                /* 支付宝支付 */
                httpService.postAlipay('', params)
                .then(function(data){

                    // 显示按钮、隐藏loading
                    $scope.hide = false;

                    /* 已支付或其他 */
                    if (data.code==0) {
                        $scope.error = data.message;
                        ngDialog.open({
                            template: '<p class="alert-p">{{error}}</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true,
                            scope: $scope
                        });
                    }

                    /* 插入表单并立即执行，跳转至支付宝登录 */
                    if (data.code==1) {
                        var div = document.createElement('div');
                        div.style.display = 'none';
                        div.innerHTML = data.message;
                        document.body.appendChild(div);
                        document.forms['alipaysubmit'].submit();
                    }

                    console.log(data)
                }, function(data) {
                    console.log('error')
                });
            }
        }

        return false;
    };

}]);


/**
 * [订单详情 控制器]
 * @param  {[type]} $scope       [description]
 * @param  {[type]} $http        [description]
 * @param  {[type]} $state       [description]
 * @param  {[type]} httpService  [自定义接口服务]
 * @param  {[type]} getDateDiff  [自定义计算时间差服务]
 * @return {[type]}              [description]
 */
ActivityModule.controller('OrderDetailController', ['$scope', '$http', '$state', 'ngDialog', 'httpService', 'getDateDiff', function($scope, $http, $state, ngDialog, httpService, getDateDiff) {

    // 缓存活动ID
    $scope.actId = $state.params.act_id;

    /* 获取订单详情 */
    httpService.postOrderInfo('', {'order_id': $state.params.order_id})
    .then(function(data){

        // 缓存活动标题
        $scope.act_title = data.message.act_title;

        // 缓存项目信息
        $scope.reg_info  = data.message.reg_info;

        // 缓存项目总价
        $scope.total     = data.message.price;

        console.log(data)
    }, function(data) {
        console.log('error')
    });

}]);


/**
 * [活动评论 控制器]
 * @param  {[type]} $scope       [description]
 * @param  {[type]} $http        [description]
 * @param  {[type]} $state       [description]
 * @param  {[type]} httpService  [自定义接口服务]
 * @param  {[type]} getDateDiff  [自定义计算时间差服务]
 * @return {[type]}              [description]
 */
ActivityModule.controller('ActivityCommentController', ['$scope', '$http', '$state', 'ngDialog', 'httpService', 'getDateDiff', function($scope, $http, $state, ngDialog, httpService, getDateDiff) {
    
    $scope.showReply = false;   

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前活动信息 */
    $scope.actInfo = store.get('actInfo');

    console.log($scope.actInfo)

    /* 拼接POST数据格式 */
    var params = {
        act_id: $scope.actInfo.act_id
    };

    /* 获取评论、报名/签到列表 */
    httpService.postCommentList(access_token, params)
    .then(function(data){
        $scope.comment = data.message;

        /* 遍历评论的数据计算出时间差，并插入数据中 */
        angular.forEach($scope.comment, function(value, index){
            var start,now,diff;
            if (value.create_time) {
                start = new Date(value.create_time);
                now = new Date();
                diff = getDateDiff.diff(start, now);
                $scope.comment[index]['diff'] = diff;
            }

            var item = $scope.comment[index]['user_review'];

            /* 遍历回复评论的数据计算出时间差，并插入数据中 */
            angular.forEach(item, function(value2, index2){
                var start2,now2,diff2;
                if (value2.create_time) {
                    start2 = new Date(value2.create_time);
                    now2 = new Date();
                    diff2 = getDateDiff.diff(start2, now2);
                    item[index2]['diff'] = diff2;
                }
            })
        });

        console.log(data)
    }, function(data) {
        console.log('error')
    });

    /* 删除评论 */
    $scope.delete = function(item) {
        var index = $scope.comment.deleteElementByValue(item);
        $scope.comment.splice(index, 1);
        console.log(index)
    };

    $scope.delete2 = function(item, reply) {
        var index = $scope.comment.deleteElementByValue(item);
        var that = $scope.comment[index]['user_review']
        var deleteIndex = that.deleteElementByValue(reply);

        that.splice(deleteIndex, 1)
        console.log(deleteIndex)
    };

}]);


/**
 * [报名签到列表 控制器]
 * @param  {[type]} $scope       [description]
 * @param  {[type]} $http        [description]
 * @param  {[type]} $state       [description]
 * @param  {[type]} httpService  [自定义接口服务]
 * @param  {[type]} getDateDiff  [自定义计算时间差服务]
 * @param  {[type]}              [description]
 * @return {[type]}              [description]
 */
ActivityModule.controller('RunnerlistController', ['$rootScope', '$scope', '$http', '$state', 'ngDialog', 'httpService', 'getDateDiff', function($rootScope, $scope, $http, $state, ngDialog, httpService, getDateDiff) {
    
    // 判断是否分享的链接
    if ($state.params.type == 'share') {
        httpService.postSharedAct('', {'act_id': $state.params.act_id})
        .then(function(data){

            $scope.runners = data.user.isreg;
            $scope.sign = data.user.ischeckin;

            /* 遍历报名的数据计算出时间差，并插入数据中 */
            if ($scope.runners && $scope.runners.length>0) {
                angular.forEach($scope.runners, function(value, index){
                    var start,now,diff;
                    if (value.reg_time) {
                        start = new Date(value.reg_time);
                        now = new Date();
                        diff = getDateDiff.diff(start, now)
                        $scope.runners[index]['diff'] = diff;
                    }

                    /* 遍历数据取出既签到又报名数据，并标记为icon: true */
                    if ($scope.sign && $scope.sign.length>0) {
                        var that   = $scope.sign;
                        var length = that.length;

                        for (var i=0; i<length; i++){
                            if (value.uid === $scope.sign[i].uid) {
                                $scope.runners[index]['icon'] = $scope.sign[i]['icon'] = true;
                                break;;
                            }
                        }
                    }
                });
            }

            /* 遍历签到的数据计算出时间差，并插入数据中 */
            if ($scope.sign && $scope.sign.length>0) {
                angular.forEach($scope.sign, function(value, index){
                    var start,now,diff;
                    if (value.checkin_time) {
                        start = new Date(value.checkin_time);
                        now = new Date();
                        diff = getDateDiff.diff(start, now)
                        $scope.sign[index]['diff'] = diff;
                    }
                });
            }

        
            console.log(data)
        }, function(data) {
            console.log('error')
        });

        return true;
    }

    $scope.tabs = [
        {
            "title": "报名",
            "src": "runnerlist.reg",
            "active": true
        },
        {
            "title": "签到",
            "src": "runnerlist.sign"
        }
    ];

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前活动信息 */
    $scope.actInfo = store.get('actInfo');

    console.log($scope.actInfo )

    /* 拼接POST数据格式 */
    var params = {
        act_id: $scope.actInfo.act_id
    };

    /* 获取评论、报名/签到列表 */
    httpService.postCommentList(access_token, params)
    .then(function(data) {
        $scope.runners = data.acttivity_user.isreg;
        $scope.sign = data.acttivity_user.ischeckin;

        /* 遍历报名的数据计算出时间差，并插入数据中 */
        if ($scope.runners && $scope.runners.length>0) {
            angular.forEach($scope.runners, function(value, index){
                var start,now,diff;
                if (value.reg_time) {
                    start = new Date(value.reg_time);
                    now = new Date();
                    diff = getDateDiff.diff(start, now)
                    $scope.runners[index]['diff'] = diff;
                }

                /* 遍历数据取出既签到又报名数据，并标记为icon: true */
                if ($scope.sign && $scope.sign.length>0) {
                    var that   = $scope.sign;
                    var length = that.length;

                    for (var i=0; i<length; i++){
                        if (value.uid === $scope.sign[i].uid) {
                            $scope.runners[index]['icon'] = $scope.sign[i]['icon'] = true;
                            break;;
                        }
                    }
                }
            });
        }

        /* 遍历签到的数据计算出时间差，并插入数据中 */
        if ($scope.sign && $scope.sign.length>0) {
            angular.forEach($scope.sign, function(value, index){
                var start,now,diff;
                if (value.checkin_time) {
                    start = new Date(value.checkin_time);
                    now = new Date();
                    diff = getDateDiff.diff(start, now)
                    $scope.sign[index]['diff'] = diff;
                }
            });
        }

        console.log(data)
    }, function(data) {
        console.log('error')
    });
    
    /* 删除报名数据 */
    $scope.cancel = function(runner, $event) {
        $event.stopPropagation();
        console.log(runner)

        if ($rootScope.is_admin||$rootScope.isActer) {
            httpService.postCancelReg(access_token, params)
            .then(function(data) {

                // 如果code为0，则提示用户不能取消报名
                if (data.code==0) {
                    ngDialog.open({
                        template: '<p class="alert-p">不能取消报名哦</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true
                    });
                }

                // 如果取消成功则提示用户并刷新当前页面
                if (data.code==1) {
                    var index = $scope.runners.deleteElementByValue(runner);
                    $scope.runners.splice(index, 1);
                }

                console.log(data)
            }, function(data) {
                console.log('error')
            });
        } else {
            ngDialog.open({
                template: '<p class="alert-p">只有管理员或团长才可以取消报名哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true
            });
        }

    };

    /* 跳转路由至跑友资料 */
    $scope.runnerinfo = function(uid) {

        if ($rootScope.is_admin||$rootScope.isActer) {
            $state.go('runnerinfo',{act_id: $scope.actInfo.act_id, uid: uid});
        } else {
            ngDialog.open({
                template: '<p class="alert-p">只有管理员或团长才可以查看跑友资料哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
        }

        console.log(uid)
    };

}]);


/**
 * [活动提醒 控制器]
 * @param  {[type]} $scope         [description]
 * @param  {[type]} $http          [description]
 * @param  {[type]} $state         [description]
 * @param  {[type]} httpService    [自定义接口服务]
 * @param  {[type]}                [description]
 * @return {[type]}                [description]
 */
ActivityModule.controller('SendMessageController', ['$scope', '$http', '$state', 'ngDialog', 'httpService', function($scope, $http, $state, ngDialog, httpService) {
    $scope.hide = false; 

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前活动信息 */
    var actInfo = $scope.actInfo = store.get('actInfo');

    /* 拼接POST数据格式 */
    var params = {
        act_id: $scope.actInfo.act_id
    };

    /* 获取评论、报名/签到列表 */
    httpService.postCommentList(access_token, params)
    .then(function(data){
        $scope.isreg = data.acttivity_user.isreg;
        $scope.ischeckin = data.acttivity_user.ischeckin;
        console.log(data)
    }, function(data) {
        console.log('error')
    });

    /* 发送短信表单数据 */
    $scope.msg = {
        title: actInfo.act_title,
        txt: '',
        radio: 0,
    };

    /* 设置输入框字数上限50个 */
    $scope.checkText = function () {
        if (!$scope.msg.txt) return;
        if ($scope.msg.txt.length > 50) {
            $scope.msg.txt = $scope.msg.txt.substr(0, 50);
        }
    };

    /* 提交表单 */
    $scope.submitForm = function(isValid) {
        if (!isValid||$scope.hide) {
            
            ngDialog.open({
                template: '<p class="alert-p">验证失败哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        if (isValid) {

            $scope.hide = true; //禁用提交按钮

            /* 拼接POST数据格式 */
            var params = {
                'act_id': actInfo.act_id,
                'is_reg': $scope.msg.radio == 0 ? 1 : 0,
                'is_checkin': $scope.msg.radio == 1 ? 1 : 0,
                'message': $scope.msg.txt
            }    

            /* 短信推送API */
            httpService.postMSG(access_token, params)
            .then(function(data){
                console.log(data)
            }, function(data) {
                console.log('error')
            });

            console.log(params)
            
        }

        return false;
    };
}]);


/**
 * [导出邮箱 控制器]
 * @param  {[type]} $rootScope   [description]
 * @param  {[type]} $scope       [description]
 * @param  {[type]} $http        [description]
 * @param  {[type]} $state       [description]
 * @param  {[type]} ngDialog     [模态框]
 * @param  {[type]} httpService  [自定义接口服务]
 * @return {[type]}              [description]
 */
ActivityModule.controller('SendEmailController', ['$rootScope', '$scope', '$http', '$state', 'ngDialog', 'httpService', function($rootScope, $scope, $http, $state, ngDialog, httpService) {
    
    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前活动信息 */
    $scope.actInfo = store.get('actInfo');
    console.log($scope.actInfo)

    httpService.postEmail(access_token, {})
    .then(function(data) {

        /* 缓存新建邮箱 */
        $rootScope.common_email  = [];

        if (data.message.other&&data.message.other.length>0) {
            $rootScope.common_email  = data.message.other;
        }
        
        /* 如果已绑定默认邮箱 */    
        if (data.message.self) {
            /* 缓存默认邮箱 */
            $rootScope.default_email = data.message.self;

            /* 缓存手机号码 */
            $rootScope.user_cell = data.message.self.user_cell;
        }

        /* 如果没有绑定邮箱,则提示绑定 */
        if (!data.message.self) {

            $scope.closeDialog = function() {
                ngDialog.close();
                $rootScope.back();
            };

            ngDialog.open({
                template:  '<div class="addemail text-center">'+
                                '<h5>你还没有绑定的邮箱，请先去绑定邮箱</h5>'+
                                '<button type="button" class="btn btn-cancel" ng-click="closeDialog()">取消</button>'+
                                '<button type="submit" class="btn btn-add" ui-sref="bindemail" ng-click="closeThisDialog()">好的</button>'+
                            '</div>',
                className: 'ngdialog-theme-default',
                plain: true,
                scope: $scope
            });
        }

        console.log(data)
    }, function(data) {
        console.log('error')
    });
    
    /* 动态绑定ngModel */
    $scope.chanage = function(t) {
        console.log(t)
        var node = t.email;
        node.common = t.$id;
    }

    /* 获取手机验证码 */
    var realCode = null;
    $scope.getCode = function() {

        /* 拼接POST数据格式 */
        var params = {
            'phone': $rootScope.user_cell
        };

        httpService.postPhoneCode(params)
        .then(function(data){
            realCode = data.message; //缓存验证码
            console.log(data)
        }, function(data) {
            console.log('error')
        });
    }

    /* 表单数据 */
    $scope.user = {
        email: true,
        code: ''
    };

    var emailData; //用以缓存以选中的数据

    /* 获取选中的数据 */
    $scope.check = function() {  
        var commonEmail = document.getElementsByName("commonEmail");  
        for(i = 0; i < commonEmail.length; i++) {    
            if(commonEmail[i].checked) {
                angular.forEach($rootScope.common_email, function(value, index){
                    if (i==index) {
                        emailData.push({
                            'user_name': value.user_name,
                            'user_email': value.user_email
                        })
                    }
                });
                console.log(emailData)
            }       
        }    
    }  

    /* 提交表单 */
    $scope.submitForm = function(isValid) {
        
        //匹配验证码
        var isCode = $scope.user.code == realCode ? true : false;

        //验证失败
        if (!isValid||!isCode) {
            
            ngDialog.open({
                template: '<p class="alert-p">验证失败哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        //验证通过
        if (isValid&&isCode) {
            
            // 禁用按钮
            $scope.hide = true;

            //清空数组
            emailData = []; 

            //插入默认邮箱
            emailData.push({
                'user_name': $rootScope.default_email.user_name, 
                'user_email': $rootScope.default_email.user_email
            });

            //获取选中的数据
            $scope.check();

            console.log(emailData)

            /* 拼接POST数据格式 */
            var params = {
                'user_email': angular.toJson({'email': emailData}),
                'act_id': $scope.actInfo.act_id
            };

            console.log(params)
            /* 导出跑友资料 */
            httpService.postRunnerInfo(access_token, params)
            .then(function(data){

                // 显示按钮
                $scope.hide = false;

                // 如果code为0，则提示用户发送失败
                if (data.code==0) {
                    ngDialog.open({
                        template: '<p class="alert-p">邮件发送失败，请重新发送哦</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true
                    });
                }

                // 如果code为1，则提示用户邮件已发送
                if (data.code==1) {
                    ngDialog.open({
                        template: '<p class="alert-p">邮件已发送，请查收哦</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true
                    });
                }

                console.log(data)
            }, function(data) {
                console.log('error')
            });

            
        }

        return false;
    };

    /* 新建邮箱 */
    $scope.addEmail = function() {
        ngDialog.open({
            template: './tpl/addemail.dialog.html',
            className: 'ngdialog-theme-default',
            controller: 'AddEmailController'
        });
    };

    /* 删除邮箱 */
    $scope.delete = function(email, index) {
        console.log(email)
        /* 拼接POST数据格式 */
        var params = {
            'type': 'del',
            'user_name': email.user_name,
            'user_email': email.user_email
        };

        /* 删除邮箱 */
        httpService.postAddUserEmail(access_token, params)
        .then(function(data){
            console.log(data)
        }, function(data) {
            console.log('error')
        });

        $rootScope.common_email.splice(index, 1);
    };

}]);


/**
 * [绑定邮箱 控制器]
 * @param  {[type]} $rootScope   [description]
 * @param  {[type]} $scope       [description]
 * @param  {[type]} $http        [description]
 * @param  {[type]} $state       [description]
 * @param  {[type]} ngDialog     [模态框]
 * @param  {[type]} httpService  [自定义接口服务]
 * @return {[type]}              [description]
 */
ActivityModule.controller('BindEmailController', ['$rootScope', '$scope', '$http', '$state', 'ngDialog', 'httpService', function($rootScope, $scope, $http, $state, ngDialog, httpService) {
    
    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;
    
    /* 表单数据 */
    $scope.user = {
        email: '',
        code: ''
    };

    $rootScope.isloading = true;

    /* 获取邮箱验证码 */
    var realCode = null;
    $scope.getCode = function(isValid) {

        if (!isValid) return false;

        if (isValid&&$rootScope.isloading) {
            /* 拼接POST数据格式 */
            var params = {
                'email': $scope.user.email
            };

            httpService.postEmailCode(params)
            .then(function(data){
                realCode = data.message; //缓存验证码
                console.log(data)
            }, function(data) {
                console.log('error')
            });
        }
    };

    /* 提交表单 */
    $scope.submitForm = function(isValid) {

        //匹配验证码
        var isCode = $scope.user.code == realCode ? true : false;

        if (!isValid||$scope.hide||!isCode) {
            ngDialog.open({
                template: '<p class="alert-p">验证失败哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        if (isValid&&isCode) {

            $scope.hide = true; //禁用提交按钮

            /* 拼接POST数据格式 */
            var params = {
                'email': $scope.user.email,
                'code': $scope.user.code
            };

            /* 存储默认邮箱 */
            httpService.postAddEmail(access_token, params)
            .then(function(data){
                //成功的场合
                if (data.code == 1) {
                    //从exportemail页面过来的场合
                    if ($state.params.back == 'exportemail') {
                        $state.go('exportemail');
                    } else {
                        $state.go('sendemail');
                    }

                    
                } else {
                    // 失败的场合
                    //弹出err信息
                    $scope.errmessage = data.message;
                    ngDialog.open({
                        template: '<p class="alert-p">{{errmessage}}</p>',
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
            
            console.log($rootScope.common_email)
        }

        return false;
    };

}]);


/**
 * [新增邮箱 控制器]
 * @param  {[type]} $rootScope   [description]
 * @param  {[type]} $scope       [description]
 * @param  {[type]} $state       [description]
 * @param  {[type]} ngDialog     [模态框]
 * @param  {[type]} httpService  [自定义接口服务]
 * @return {[type]}              [description]
 */
ActivityModule.controller('AddEmailController', ['$rootScope', '$scope', '$state', 'ngDialog', 'httpService', function($rootScope, $scope, $state, ngDialog, httpService) {

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    $scope.hide = false; 

    /* 表单数据 */
    $scope.user = {
        email: '',
        name: ''
    };

    /* 提交表单 */
    $scope.submitForm = function(isValid) {

        if (!isValid||$scope.hide) {
            ngDialog.open({
                template: '<p class="alert-p">验证失败哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        if (isValid) {

            /* 禁用提交按钮 */
            $scope.hide = true;

            /* 拼接数据格式 */
            var userdata = {
                'user_name': $scope.user.name,
                'user_email': $scope.user.email
            };

            /* 缓存至数组中 */
            $rootScope.common_email.push(userdata);
            console.log($rootScope.common_email)

            /* 拼接POST数据格式 */
            var params = {
                'type': 'add',
                'user_name': $scope.user.name,
                'user_email': $scope.user.email
            };

            /* 新增邮箱 */
            httpService.postAddUserEmail(access_token, params)
            .then(function(data){
                ngDialog.close();
                console.log(data)
            }, function(data) {
                console.log('error')
            });
        
        }

        return false;
    };

}]);


/**
 * [导出跑友资料 控制器]
 * @param  {[type]} $scope         [description]
 * @param  {[type]} $http          [description]
 * @param  {[type]} $state         [description]
 * @param  {Object} httpService)   [自定义接口服务]
 * @param  {[type]}                [description]
 * @return {[type]}                [description]
 */
ActivityModule.controller('RunnerInfoController', ['$scope', '$http', '$state', 'ngDialog', 'httpService', function($scope, $http, $state, ngDialog, httpService) {
    
    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 拼接POST数据格式 */
    var params = {
        'act_id': $state.params.act_id,
        'uid': $state.params.uid
    }

    console.log(params)

    /* 跑友资料 */
    httpService.postUserInfo(access_token, params)
    .then(function(data) {
        $scope.runnerInfo = data.message;
        var AllImgExt = ".jpg|.jpeg|.gif|.bmp|.png|";
        angular.forEach($scope.runnerInfo, function(value, index) {
            var FileExt = value.value.substr(value.value.lastIndexOf(".")).toLowerCase(); 
            if(AllImgExt.indexOf(FileExt+"|")!=-1) {
                value.image = value.value
            }
        });

        console.log($scope.runnerInfo)

        $scope.userInfo = data.user_info;
        console.log(data)
    }, function(data) {
        console.log('error')
    });

}]);


/**
 * [发起活动 控制器]
 * @param  {[type]} $scope      [description]
 * @param  {[type]} $http       [description]
 * @param  {[type]} $state      [description]
 * @param  {[type]} $timeout    [description]
 * @param  {[type]} httpService [自定义接口服务]
 * @param  {[type]} mapService  [自定义QQ地图服务]
 * @param  {[type]} ngDialog    [模态框]
 * @param  {[type]} fileReader  [自定义上传图片服务]
 * @return {[type]}             [description]
 */
ActivityModule.controller('LaunchActivityController', ['$location', '$scope', '$http', '$state', '$timeout', 'httpService', 'mapService', 'ngDialog', 'fileReader', function($location, $scope, $http, $state, $timeout, httpService, mapService, ngDialog, fileReader) {

    //判断是否从用户个人中心跳转过来的。获取参数判断做相应跳转。
    if ($location.$$url) {
        var url = $location.$$url.substr(16);
        var flag = (url.indexOf('uid')!=-1)&&(url.indexOf('newact')!=-1);

        console.log(url)

        if (flag) {

            // $rootScope.hidelogin = true;

            $http({
                method: 'GET',
                url: 'http://www.paobuqu.com/v4/common/linkactivity?' + url
            })
            .success(function(data, status, headers, config) {
                if (data.code==1) {
                    store.set('access_token', data.message.access_token);
                    store.set('clubInfo', data.message.club_info[0]);

                     /* 获取用户登录标识 */
                    var access_token = store.get('access_token');

                    if (!access_token) return;

                    /* 获取当前跑团信息 */
                    var clubInfo = store.get('clubInfo');
                }

                console.log(data)
            })
            .error(function(data, status, headers, config) {  
                console.log(data)
            });

            // return false;
        }
        
    }

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取当前跑团信息 */
    var clubInfo = store.get('clubInfo');

    /* 发布活动的表单数据 */
    var now = new Date();
    $scope.act = {
        title: '',
        startTime: '',
        endTime: '',
        regEndTime: '',
        addr_id: '',
        address: '',
        text: '',
        cycle: false,
        type: true,
        protocol: true
    };

    //开始时间的最小时间为：当前时间的前一天
    $scope.minStartDate = moment().subtract(1, 'day')._d;

    /* 监听开始时间model */
    $scope.$watch('act.startTime', function(newValue, oldValue) {
        if (newValue) {
            console.log(newValue)

            //结束时间为开始时间同一天
            $scope.act.endTime = moment(newValue).add(1, 'hours')._d;

            //报名截至时间
            $scope.act.regEndTime = moment(newValue).subtract(30, 'minutes')._d

            //结束时间的最小时间
            $scope.minEndDate = moment(newValue)._d;

            //结束时间的最大时间
            // $scope.maxEndDate = moment(newValue)._d;

            //报名截至时间的最大时间
            $scope.maxdate = moment(newValue)._d;

            //报名截至时间大于开始时间时，更新报名截至时间
            // if($scope.act.regEndTime!==''&&$scope.act.regEndTime>newValue) {
            //     $scope.act.regEndTime = moment()._d;
            // }
        }
    });

    /* 监听结束时间model */
    $scope.$watch('act.endTime', function(newValue, oldValue) {
        if (newValue) {
            console.log(newValue)
            
            //报名截至时间的最大时间为：结束时间
            // $scope.maxdate = moment(newValue)._d;

            if (newValue<$scope.act.startTime) {
                $scope.act.endTime = moment($scope.act.startTime).add(1, 'hours')._d;
                // alert('结束时间不能小于开始时间')
            }
        }
    });

    /* 监听报名截止时间model */
    // $scope.$watch('act.regEndTime', function(newValue, oldValue) {
    //     if (newValue) {
    //         console.log(newValue)

    //         if (newValue<$scope.act.startTime||newValue>$scope.act.endTime) {
    //             $scope.act.regEndTime = moment($scope.act.startTime).add(1, 'day')._d;
    //             alert('报名截止时间不能小于开始时间，不能大于结束时间')
    //         }
    //     }
    // });
    
    $scope.isSettings = false;

    /* 提交表单 */
    $scope.submitForm = function(isValid) {
        console.log($scope.act)
        if (!isValid) {

            if (!$scope.act.title&&!$scope.myFile&&!$scope.act.startTime&&!$scope.act.endTime&&!$scope.act.regEndTime&&!$scope.act.address&&!$scope.act.text&&!$scope.act.protocol) {
                $scope.error = '请填写活动基本信息哦';
            } else if (!$scope.act.title) {
                $scope.error = '请填写活动标题哦';
            } else if(!$scope.myFile) {
                $scope.error = '请填写活动图片哦';
            } else if(!$scope.act.startTime) {
                $scope.error = '请填写活动开始时间哦';
            } else if(!$scope.act.endTime) {
                $scope.error = '请填写活动结束时间哦';
            } else if(!$scope.act.regEndTime) {
                $scope.error = '请填写活动报名截止时间哦';
            } else if(!$scope.act.address) {
                $scope.error = '请填写活动活动集合地点哦';
            } else if(!$scope.act.text) {
                $scope.error = '请填写活动详情哦';
            } else if (!$scope.act.protocol) {
                $scope.error = '发布活动, 必须同意跑团助手平台发布协议哦';
            }

            ngDialog.open({
                template: '<p class="alert-p">{{error}}</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        if (isValid) {

            //禁用按钮
            $scope.hide = true;

            /* 时间格式化 */
            var start_time   = moment($scope.act.startTime).format('YYYY-MM-DD HH:mm:ss');
            var end_time     = moment($scope.act.endTime).format('YYYY-MM-DD HH:mm:ss');
            var reg_end_time = moment($scope.act.regEndTime).format('YYYY-MM-DD HH:mm:ss');

            /* 拼接POST数据格式 */
            var settings = {
                act_id: $scope.isSettings ? store.get('new_act_id') : 0,
                club_id: clubInfo.clubid,
                title: $scope.act.title,
                start_time: start_time,  
                end_time: end_time,  
                reg_end_time: reg_end_time,   
                act_desc: $scope.act.text, 
                act_location: $scope.act.addr_id,   
                act_image: $scope.myFile,
                rang_limit: $scope.act.type ? 1 : 0
            };

            console.log(settings)

            /* 创建活动 */
            httpService.postCreateActivity(access_token, settings)
            .then(function(data){

                if (data.message) {
                    /* 如果新建活动标识存在, 则清除之 */
                    if (store.get('new_act_id')) {
                        store.remove('new_act_id')
                    }

                    /* 缓存新建活动标识至localStorage */
                    store.set('new_act_id', data.message);

                    /* 获取新建活动标识 */
                    // var new_act_id = store.get('new_act_id');

                    /* 拼接POST数据格式 */
                    var params = {
                        'club_id': clubInfo.clubid,
                        'act_id': data.message
                    };

                    /* 获取创建成功的活动信息 */
                    httpService.postActInfo(access_token, params)
                    .then(function(data){

                        if (data.code==1) {
                            store.set('newactInfo',data);
                            $state.go('signinfo', {newact: 1});
                        }

                        console.log(data)
                    }, function(data) {
                        console.log('error')
                    });
                }

                console.log(data)
            }, function(data) {
                console.log('error')
            });

            
        }
        return false;
    };

    /* 获取上传图片的地址url，用以实现预览图片 */
    $scope.getFile = function () {
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {
            console.log($scope.file)

            //判断附件是否为图片
            if(!/image\/\w+/.test($scope.file.type)){

                ngDialog.open({
                    template: '<p class="alert-p">图片类型必须是.gif,jpeg,jpg,png中的一种哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true
                });
                
                return false;
            } else {
                $scope.imageSrc = result;
            }
            
        }, function(data) {
            console.log('error')
        });
    };

    /* 获取活动集合地点 */
    $scope.findlocation = function() {

        $scope.showaAddr = true;

        /* 拼接POST数据格式 */
        var params = {
            club_id: clubInfo.clubid
        };

        httpService.postFindLocation(access_token, params)
        .then(function(data){
            $scope.address = data.message;
            $scope.ip = data.ip;
            console.log(data)
        }, function(data) {
            console.log('error')
        });

        ngDialog.open({
            template: './tpl/newaddr.dialog.html',
            className: 'ngdialog-theme-default',
            scope: $scope
        });
    };

    /* 点击选中当前活动地址 */
    $scope.selectedAddr = function(item) {
        console.log(item)
        $scope.act.address = item.location;
        $scope.act.addr_id = item.id;
        $scope.showaAddr = false;
    };

    /* 截取活动集合地点数组 */
    $scope.delete = function(item, index, $event) {
        $event.stopPropagation();
        console.log(item)

        httpService.postUpdateLocation(access_token, {'id': item.id, 'types': 'del'})
        .then(function(data) {

            if (data.code==0) {
                ngDialog.open({
                    template: '<p class="alert-p">不能删除已有活动地址信息哦</p>',
                    className: 'ngdialog-theme-default ngdialog-alert',
                    plain: true
                });
            }

            if (data.code==1) {
                $scope.address.splice(index, 1);
            }

            console.log(data)
        }, function(data) {
            console.log('error')
        });
    };
    
    /* 获取地图、城市信息 */
    $scope.initMap = function(lat, lng) {

        if(!lat||!lng){
            lat = 31.231592;
            lng = 121.478577;
        }

        /* 创建地图 */
        var map = mapService.createMap("container", 39.916527, 116.397128);

        $scope.marker = mapService.getMarker(map);  //缓存地图标识
        $scope.info   = mapService.getInfo(map);    //缓存地图信息
        $scope.label  = mapService.getLabel(map);   //缓存地图标签

        // $scope.ip = '180.168.36.174';

        /* 如果IP存在，则根据IP查询城市信息，否则设置默认城市信息 */
        if($scope.ip){
            var cityLocation = mapService.getCityLocation(map, function(latLng){

                /* 纬度、经度 */
                var coord = {
                    lat: latLng.lat,
                    lng: latLng.lng
                };
                
                /* 改变地图中心点位置 */
                mapService.setMapMarker($scope.marker, coord.lat, coord.lng, $scope.info);

                /* 获取城市信息 */
                mapService.geocoder(function(address){
                    $scope.$apply(function() {
                        $scope.mapAddress = address;
                        $scope.coord = {
                            lat: coord.lat,
                            lng: coord.lng
                        };
                    });
                    console.log(address)
                    mapService.setLabel($scope.label, $scope.marker, address);
                }, coord.lat, coord.lng);
            });

            /* 根据IP查询城市信息 */
            cityLocation.searchCityByIP($scope.ip);
        } else {

            /* 改变地图中心点位置 */ 
            mapService.setMapMarker($scope.marker, lat, lng, $scope.info);

            /* 获取城市信息 */
            mapService.geocoder(function(address){
                $scope.$apply(function() {
                    $scope.mapAddress = address;
                    $scope.coord = {
                        lat: lat,
                        lng: lng
                    };
                });
                console.log(address)
                mapService.setLabel($scope.label, $scope.marker, address);
            }, lat, lng);
        }

        /* 点击地图事件，改变地图中心点位置，获取城市信息 */
        qq.maps.event.addListener(map, 'click', function(event) {

            /* 纬度、经度 */
            var coord = {
                lat: event.latLng.getLat(),
                lng: event.latLng.getLng()
            };

            /* 改变地图中心点位置 */
            mapService.setMapMarker($scope.marker, coord.lat, coord.lng, $scope.info);

            /* 获取城市信息 */
            mapService.geocoder(function(address){
                $scope.$apply(function() {
                    $scope.mapAddress = address;
                    $scope.coord = {
                        lat: coord.lat,
                        lng: coord.lng
                    };
                });
                console.log(address)
                mapService.setLabel($scope.label, $scope.marker, address);
            }, coord.lat, coord.lng);
        }); 

    };

    /* 新建活动地址模态框 */
    $scope.newAddr = function() {
        $scope.showaAddr = true;

        ngDialog.open({
            template: '<div class="header"><h4>创建约跑地点</h4></div>'+
                      '<div class="body"><p>设置别名：<input type="text" ng-model="act_name"></p>'+  
                      '<p>当前位置：<span>{{mapAddress}}</span></p>'+
                      '<p><button type="button" class="btn btn-blue" ng-click="createLocation(act_name)">保存</button></div>'+
                      '<div id="container" style="width:100%; height:460px;"></div>',
            className: 'ngdialog-theme-default map',
            plain: true,
            scope: $scope
        });

        $timeout(function() {
            $scope.initMap();
        }, 250);
    };

    /* 新建活动地址 */
    $scope.createLocation = function(act_name) {

        if (!act_name) {
            ngDialog.open({
                template: '<p class="alert-p">请设置别名哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true
            });
        }

        if (act_name) {
            $scope.act.address = act_name;

            /* 拼接POST数据格式 */
            var params = {
                club_id: clubInfo.clubid,
                name: $scope.act_name,
                location: $scope.act.address,
                lat: $scope.coord.lat,
                lng: $scope.coord.lng
            };

            httpService.postCreateLocation(access_token, params)
            .then(function(data){
                ngDialog.close();
                console.log(data)
            }, function(data) {
                console.log('error')
            });

        }
    };

    /* 高级设置 */
    $scope.settings = function(isValid) {

        if (!isValid) {
            ngDialog.open({
                template: '<p class="alert-p">请填写基本信息哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });

            return false;
        }

        if (isValid) {

            /* 时间格式化 */
            var start_time   = moment($scope.act.startTime).format('YYYY-MM-DD HH:mm:ss');
            var end_time     = moment($scope.act.endTime).format('YYYY-MM-DD HH:mm:ss');
            var reg_end_time = moment($scope.act.regEndTime).format('YYYY-MM-DD HH:mm:ss');

            /* 拼接POST数据格式 */
            var params = {
                club_id: clubInfo.clubid,
                title: $scope.act.title,
                start_time: start_time,  
                end_time: end_time,  
                reg_end_time: reg_end_time,   
                act_desc: $scope.act.text, 
                act_location: $scope.act.addr_id,   
                act_image: $scope.myFile,
                rang_limit: $scope.act.type ? 1 : 0
            };

            console.log(params)

            /* 创建活动 */
            httpService.postCreateActivity(access_token, params)
            .then(function(data){
                if (data.message) {

                    $scope.isSettings = true;

                    /* 如果新建活动标识存在, 则清除之 */
                    if (store.get('new_act_id')) {
                        store.remove('new_act_id')
                    }

                    /* 缓存新建活动标识至localStorage */
                    store.set('new_act_id', data.message);

                    /* 高级设置页面 */
                    ngDialog.open({
                        template: './tpl/advancedsettings.html',
                        className: 'ngdialog-theme-default ngdialog-addtypetext dialog-bg-f5f5f5',
                        controller: 'AdvancedSettingsController'
                    });
                }
                console.log(data)
            }, function(data) {
                console.log('error')
            });
        }
    };

    // 协议
    $scope.protocol = function() {
        // alert("1232423");
        /* 高级设置页面 */
        ngDialog.open({
            template: './tpl/protocol.html',
            className: 'ngdialog-theme-default ngdialog-addtypetext overflow-scroll',
        });
    };

}]);


/**
 * [发起活动-高级设置 控制器]
 * @param  {[type]} $scope         [description]
 * @param  {[type]} $http          [description]
 * @param  {[type]} $state         [description]
 * @param  {[type]} ngDialog       [模态框]
 * @param  {[type]} httpService    [自定义接口服务]
 * @param  {[type]}                [description]
 * @return {[type]}                [description]
 */
ActivityModule.controller('AdvancedSettingsController', ['$rootScope', '$scope', '$http', '$state', 'ngDialog', 'httpService', function($rootScope, $scope, $http, $state, ngDialog, httpService) {
    
    var new_act_id = store.get('new_act_id'); //获取新建活动标识
    console.log(new_act_id)

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    /* 获取活动项目信息 */
    httpService.postActivityJoinInfo(access_token, {})
    .then(function(data) {

        if (data) {
            $scope.field     = data.field;
            $scope.join_info = data.join_info;
            $scope.type      = data.type; 
        }
        
        console.log(data)

    }, function(data) {
        console.log('error')
    });

    /* 增加活动项目 */
    $scope.addProject = function() {

        /* 活动项目页面 */
        ngDialog.open({
            template: './tpl/activityproject.html',
            className: 'ngdialog-theme-default ngdialog-addtypetext',
            controller: 'AddProjectController'
        });

    };

    //用以缓存活动项目
    $rootScope.projectArray = [];

    /* 编辑活动项目 */
    $scope.editProject = function(item, index) {

        //设置索引值
        item.index = index;

        /* 缓存当前编辑活动项目信息 */
        store.set('editProject', item);

        /* 活动项目页面 */
        ngDialog.open({
            template: './tpl/activityproject.html',
            className: 'ngdialog-theme-default ngdialog-addtypetext',
            controller: 'EditProjectController'
        });

    };

    /* 新建报名填写的类型 */
    $scope.addType = function() {
        ngDialog.open({
            template: './tpl/addtype.dialog.html',
            className: 'ngdialog-theme-default ngdialog-addtype',
            scope: $scope
        });
    };

    /* 新建报名填写的类型 */
    $scope.col_len = 0;
    $scope.newTitle = '';
    $scope.addTypeText = function(index) {

        //判断自定义字段个数不能超过8个
        if ($scope.col_len>=8) {
            ngDialog.open({
                template: '<p class="alert-p">自定义字段最多8个哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true
            });
            return false
        }

        //判断自定义字段个数不能超过8个
        if ($scope.col_len<8){
            //字段类型标识
            $scope.thisType = index + 1;

            //自定义字段模态框
            ngDialog.open({
                template: './tpl/addtypetext.dialog.html',
                className: 'ngdialog-theme-default ngdialog-addtypetext',
                scope: $scope
            });

            //判断字段类型：1为文本, 2为列表, 3为日期, 4为时间, 5为文件
            //列表类型
            if ($scope.thisType == 2) {

                //显示标题
                $scope.showTitle = true;

                //空数组用以缓存数据
                var list = [];
                
                //初始化列表选项
                $scope.texts = [
                    {
                        text:'选项1'
                    },
                    {
                        text:'选项2'
                    },
                    {
                        text:'选项3'
                    }
                ];

                //提交表单
                $scope.submitForm = function(isValid) {

                    /* 验证失败 */
                    if (!isValid) {
                        ngDialog.open({
                            template: '<p class="alert-p">请填写字段哦</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true
                        });
                        return false;
                    }

                    /* 验证成功 */
                    if (isValid) {

                        /* 拼接列表内容字符串 */
                        angular.forEach($scope.texts, function(value, index) {
                            list = list + ',' + value.text;
                        });

                        list = list.substr(1);   

                        /* 拼接POST数据格式 */
                        var params = {
                            'act_id': new_act_id,
                            'col_name': 'reserved_field_' + ($scope.col_len + 1),
                            'col_title': document.getElementById('newTitle').value,
                            'visible': 1,
                            'optional': 1,
                            'comment': '',
                            'col_type': $scope.thisType,
                            'col_list_values': list
                        };

                        console.log(params)

                        /* 新增报名填写的信息 */
                        httpService.postCheckinInfo(access_token, params)
                        .then(function(data) {

                            //自定义类型个数自增1
                            $scope.col_len++;

                            var item = {
                                desc: "",
                                display: params.col_title,
                                field: params.col_name,
                                fieldtype: params.col_type,
                                sort: 0,
                                system: 1,
                                flag: true
                            };

                            $scope.join_info.push(item) 
                            
                            console.log(data)
                        }, function(data) {
                            console.log('error')
                        });
                    }

                    return false;
                }

            } 

            //非列表类型
            if ($scope.thisType !== 2) {

                //提交表单
                $scope.submitForm = function(isValid) {

                    /* 验证失败 */
                    if (!isValid) {
                        ngDialog.open({
                            template: '<p class="alert-p">请填写字段哦</p>',
                            className: 'ngdialog-theme-default ngdialog-alert',
                            plain: true
                        });
                        return false;
                    }

                    /* 验证成功 */
                    if (isValid) {
                        /* 拼接POST数据格式 */
                        var params = {
                            'act_id': new_act_id,
                            'col_name': 'reserved_field_' + ($scope.col_len + 1),
                            'col_title': document.getElementById('newTitle').value,
                            'visible': 1,
                            'optional': 1,
                            'comment': '',
                            'col_type': $scope.thisType,
                            'col_list_values': ''
                        };

                        console.log(params);

                        /* 新增报名填写的信息 */
                        httpService.postCheckinInfo(access_token, params)
                        .then(function(data) {

                            //自定义类型个数自增1
                            $scope.col_len++;

                            var item = {
                                desc: "",
                                display: params.col_title,
                                field: params.col_name,
                                fieldtype: params.col_type,
                                sort: 0,
                                system: 1,
                                flag: true
                            };

                            $scope.join_info.push(item)

                            console.log(data)
                        }, function(data) {
                            console.log('error')
                        });
                    }

                    return false;
                }
            }
        }
    };

    /* 新增报名填写的信息 */
    $scope.addFn = function(item) {

        //字段类型标识
        $scope.thisType = item.fieldtype;

        //判断字段类型：1为文本, 2为列表, 3为日期, 4为时间, 5为文件
        //列表类型
        if ($scope.thisType == 2) {

            //隐藏标题
            $scope.showTitle = false;

            //空数组用以缓存数据
            var list = [];
                
            //初始化列表选项
            $scope.texts = [
                {
                    text:''
                }
            ];
            
            //自定义字段模态框
            ngDialog.open({
                template: './tpl/addtypetext.dialog.html',
                className: 'ngdialog-theme-default ngdialog-addtypetext',
                scope: $scope
            });

            //提交表单
            $scope.submitForm = function(isValid) {

                /* 验证失败 */
                if (!isValid) {
                    ngDialog.open({
                        template: '<p class="alert-p">请填写字段哦</p>',
                        className: 'ngdialog-theme-default ngdialog-alert',
                        plain: true
                    });
                    return false;
                }

                /* 验证成功 */
                if (isValid) {

                    /* 拼接列表内容字符串 */
                    angular.forEach($scope.texts, function(value, index) {
                        list = list + ',' + value.text;
                    });

                    list = list.substr(1);
                    console.log(list);

                    /* 拼接POST数据格式 */
                    var params = {
                        'act_id': new_act_id,
                        'col_name': item.field,
                        'col_title': item.display,
                        'col_type': item.fieldtype,
                        'col_list_values': list
                    };

                    //保存字段
                    httpService.postCheckinInfo(access_token, params)
                    .then(function(data) {
                        console.log(data)
                    }, function(data) {
                        console.log('error')
                    });
                }

                return false;
            }
        }

        //非列表类型
        if ($scope.thisType !== 2) {

            /* 拼接POST数据格式 */
            var params = {
                'act_id': new_act_id,
                'col_name': item.field,
                'col_title': item.display,
                'col_type': item.fieldtype,
                'col_list_values': ''
            };

            //保存字段
            httpService.postCheckinInfo(access_token, params)
            .then(function(data) {
                console.log(data)
            }, function(data) {
                console.log('error')
            });
        }

    };

    /* 删除报名填写的信息 */
    $scope.delFn = function(item) {

        /* 拼接POST数据格式 */
        var params = {
            'act_id': new_act_id,
            'col_name': item.field
        };

        httpService.postDelInfo(access_token, params)
        .then(function(data) {
            console.log(data)
        }, function(data) {
            console.log('error')
        });
    };

}]);


/**
 * [发起活动-高级设置-新增项目 控制器]
 * @param  {[type]} $scope       [description]
 * @param  {[type]} $http        [description]
 * @param  {[type]} $state       [description]
 * @param  {[type]} ngDialog     [模态框]
 * @param  {[type]} httpService  [自定义接口服务]
 * @return {[type]}              [description]
 */
ActivityModule.controller('AddProjectController', ['$rootScope', '$scope', '$http', '$state', 'ngDialog', 'httpService', function($rootScope, $scope, $http, $state, ngDialog, httpService) {

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    var new_act_id = store.get('new_act_id'); //获取新建活动标识
    console.log(new_act_id)

    /* 失约扣除 */
    $scope.showMeetConsume = function() {
        ngDialog.open({
            template: '<input type="text" name="meetConsume" ng-model="project.meet_consume">',
            className: 'ngdialog-theme-default',
            plain: true,
            scope: $scope
        });
    };

    /* 表单数据 */
    $scope.project = {
        name: null,
        money: null,
        number: null,
        reg_gain: 0,
        reg_num: 0,
        checkin_gain: 0,
        checkin_num: 0,
        meet_consume: 0
    };

    /* 表单提交 */
    $scope.submitForm = function(isValid) {

        if (!isValid) {
            
            ngDialog.open({
                template: '<p class="alert-p">验证失败哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        if (isValid) {

            /* 拼接POST数据格式 */
            var params = {
                'type': 'add',
                'activity_id': new_act_id,
                'project_name': $scope.project.name,
                'price': $scope.project.money,
                'project_num': $scope.project.number,
                'reg_gain': $scope.project.reg_gain == 0 ? $scope.project.reg_num : 0,
                'reg_consume': $scope.project.reg_gain == 1 ? $scope.project.reg_num : 0,
                'checkin_gain': $scope.project.checkin_gain == 0 ? $scope.project.checkin_num : 0,
                'checkin_consume': $scope.project.checkin_gain == 1 ? $scope.project.checkin_num : 0,
                'meet_consume': $scope.project.meet_consume
            }

            console.log(params);

            /* 新增活动项目 */
            httpService.postCreateProject(access_token, params)
            .then(function(data){

                $rootScope.projectArray.push(data.message);

                console.log($rootScope.projectArray)

                console.log(data)
            }, function(data) {
                console.log('error')
            });

        }

        return false;
    };

}]);


/**
 * [发起活动-高级设置-编辑项目 控制器]
 * @param  {[type]} $rootScope   [description]
 * @param  {[type]} $scope       [description]
 * @param  {[type]} $http        [description]
 * @param  {[type]} $state       [description]
 * @param  {[type]} ngDialog     [模态框]
 * @param  {[type]} httpService  [自定义接口服务]
 * @return {[type]}              [description]
 */
ActivityModule.controller('EditProjectController', ['$rootScope', '$scope', '$http', '$state', 'ngDialog', 'httpService', function($rootScope, $scope, $http, $state, ngDialog, httpService) {

    /* 获取用户登录标识 */
    var access_token = store.get('access_token');

    if (!access_token) return;

    var new_act_id = store.get('new_act_id'); //获取新建活动标识
    console.log(new_act_id)

    var editProject = store.get('editProject'); //获取当前编辑活动项目信息
    console.log(editProject)

    /* 失约扣除 */
    $scope.showMeetConsume = function() {
        ngDialog.open({
            template: '<input type="text" name="meetConsume" ng-model="project.meet_consume">',
            className: 'ngdialog-theme-default',
            plain: true,
            scope: $scope
        });
    };

    /* 表单数据 */
    $scope.project = {
        name: editProject.project_name,
        money: editProject.price,
        number: editProject.project_num,
        reg_gain: editProject.reg_consume==0?0:1,
        reg_num: editProject.reg_consume==0?editProject.reg_gain:editProject.reg_consume,
        checkin_gain: editProject.checkin_consume==0?0:1,
        checkin_num: editProject.checkin_consume==0?editProject.checkin_gain:editProject.checkin_consume,
        meet_consume: editProject.meet_consume
    };

    /* 表单提交 */
    $scope.submitForm = function(isValid) {

        if (!isValid) {
            
            ngDialog.open({
                template: '<p class="alert-p">验证失败哦</p>',
                className: 'ngdialog-theme-default ngdialog-alert',
                plain: true,
                scope: $scope
            });
            return false;
        }

        if (isValid) {

            /* 拼接POST数据格式 */
            var params = {
                'type': 'update',
                'id': editProject.id,
                'activity_id': new_act_id,
                'project_name': $scope.project.name,
                'price': $scope.project.money,
                'project_num': $scope.project.number,
                'reg_gain': $scope.project.reg_gain == 0 ? $scope.project.reg_num : 0,
                'reg_consume': $scope.project.reg_gain == 1 ? $scope.project.reg_num : 0,
                'checkin_gain': $scope.project.checkin_gain == 0 ? $scope.project.checkin_num : 0,
                'checkin_consume': $scope.project.checkin_gain == 1 ? $scope.project.checkin_num : 0,
                'meet_consume': $scope.project.meet_consume
            }

            console.log(params);

            /* 新增活动项目 */
            httpService.postCreateProject(access_token, params)
            .then(function(data){

                $rootScope.projectArray.splice(editProject.index, 1, data.message);

                console.log($rootScope.projectArray)

                console.log(data)
            }, function(data) {
                console.log('error')
            });

        }

        return false;
    };

}]);