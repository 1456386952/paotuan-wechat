/* 封装一个$http服务 */
App.factory('httpService', ['$rootScope', '$http', '$q', function($rootScope, $http, $q) {

    /* 声明对象缓存接口 */
    var URL = 'http://www.paobuqu.com/v4';

    var act = {
        loginUrl: URL + '/common/login',                                      //登录验证
        regUrl: URL + '/common/userregister',                                 //用户注册
        resetPWDUrl: URL + '/common/resetpassword',                           //重置密码
        clubListUrl: URL + '/club/clublist?access-token=',                    //获取跑团列表
        activityUrl: URL + '/activity/activitylist?access-token=',            //获取活动列表
        commentUrl: URL + '/activity/reviewlist?access-token=',               //获取评论、报名/签到列表
        MSGUrl: URL + '/activity/sendactuser?access-token=',                  //短信推送 
        runnerInfoUrl: URL + '/activity/senduseremail?access-token=',         //导出跑友资料 
        phoneCodeUrl: URL + '/common/verifyphone',                            //获取手机验证码
        emailCodeUrl: URL + '/common/verifyemail',                            //获取邮箱验证码
        getEmailUrl: URL + '/usercommon/returnuser?access-token=',            //获取默认邮箱 
        addEmailUrl: URL + '/usercommon/bindemail?access-token=',             //存储默认邮箱
        addUserEmailUrl: URL + '/usercommon/adduseremail?access-token=',      //新增、删除邮箱
        findLocationUrl: URL + '/club/findlocation?access-token=',            //获取活动集合地点
        createLocationUrl: URL + '/club/createlocation?access-token=',        //创建活动地址
        createActivityUrl: URL + '/activity/createactivity?access-token=',    //创建活动
        findcityclubUrL: URL + '/club/cityclub?access-token=',                //发现跑团
        activitysearchUrL: URL + '/activity/activitysearch?access-token=',    //按照地址或名称查询活动
        avtivityJoinInfoUrl: URL + '/activity/joininfo?access-token=',        //获取活动项目信息
        checkinInfoUrl: URL + '/activity/checkininfo?access-token=',          //新增报名填写的信息
        delInfoUrl: URL + '/activity/delinfo?access-token=',                  //删除报名填写的信息
        createProjectUrl: URL + '/activity/createproject?access-token=',      //新增活动项目
        createclubUrl: URL + '/club/createclub?access-token=',                //创建跑团
        activitysearchUrL: URL + '/activity/activitysearch?access-token=',    //按照地址或名称查询活动
        actlistemailUrL: URL + '/activity/actlistemail?access-token=',        //搜索活动后导向邮件
        actInfoUrL: URL + '/activity/actinfo?access-token=',                  //获取创建成功的活动信息
        actProjectUrL: URL + '/activityreg/actproject?access-token=',         //获取活动项目列表信息
        regProjectUrL: URL + '/activityreg/regproject?access-token=',         //报名活动项目
        cancelRegUrL: URL + '/activityreg/cancelreg?access-token=',           //取消报名活动项目
        userRegInfoUrL: URL + '/activityreg/userreginfo?access-token=',       //提交报名项目信息
        alipayUrL: URL + '/alipay/createpay',                                 //支付宝支付
        praiseUrL: URL + '/activity/praise?access-token=',                    //点赞功能
        cancelActUrL: URL + '/activity/cancelact?access-token=',              //取消活动
        userInfoUrL: URL + '/club/checkuserinfo?access-token=',               //用户信息
        userRegUrL: URL + '/activityreg/userreg?access-token=',               //判断用户报名是否支付
        updateLocationUrL: URL + '/club/updatelocation?access-token=',        //新增、删除活动地址
        copyActivityUrL: URL + '/activity/copyactivity?access-token=',        //复制活动
        uploadImageUrL: URL + '/activity/image?access-token=',                //上传图片
        sharedActUrL: URL + '/common/sharedact',                              //获取活动详情
        orderInfoUrL: URL + '/common/orderinfo'                               //获取订单详情

    }

    /* GET */
    // var getRequest = function(baseUrl) {
    //     return $http({
    //         method: 'GET',
    //         url: baseUrl
    //     });
    // }

    /* POST */
    // var postRequest = function(baseUrl, params) {
    //     return $http({
    //         method: 'POST',
    //         url: baseUrl,
    //         data: params
    //     });
    // }
    
    /* GET */
    var getRequest = function(baseUrl) {
        var deferred = $q.defer();

        $http({
            method: 'GET',
            url: baseUrl
        })
        .success(function(data, status, headers, config) {  
            deferred.resolve(data); 
        })
        .error(function(data, status, headers, config) {  
            deferred.reject(data);
        });

        return deferred.promise;
    }

    /* POST */
    var postRequest = function(baseUrl, params) {
        var deferred = $q.defer();
        
        $http({
            method: 'POST',
            url: baseUrl,
            data: params
        })
        .success(function(data, status, headers, config) {  
            deferred.resolve(data); 
        })
        .error(function(data, status, headers, config) {  
            deferred.reject(data);
        });

        return deferred.promise;
    }

    /* upload file */
    var postMultipart = function(baseUrl, params) {
        var deferred = $q.defer();

        var fd = new FormData();
        angular.forEach(params, function(val, key) {
            fd.append(key, val);
        });

        $http({
            method: 'POST',
            url: baseUrl,
            data: fd,
            headers: {'Content-Type': undefined},
            transformRequest: angular.identity
        })
        .success(function(data, status, headers, config) {  
            deferred.resolve(data); 
        })
        .error(function(data, status, headers, config) {  
            deferred.reject(data);
        });

        return deferred.promise;
    }

    return {
        /* 登录验证 */
        postLogin: function(params) {
            return postRequest(act.loginUrl, params);
        },
        /* 用户注册 */
        postRegister: function(params) {
            return postRequest(act.regUrl, params);
        },
        /* 重置密码 */
        postResetPWD: function(params) {
            return postRequest(act.resetPWDUrl, params);
        },
        /* 获取跑团列表 */
        postClubList: function(access_token, params) {
            return postRequest(act.clubListUrl + access_token, params);
        },
        /* 获取活动列表 */
        postActList: function(access_token, params) {
            return postRequest(act.activityUrl + access_token, params);
        },
        /* 获取评论、报名/签到列表 */
        postCommentList: function(access_token, params) {
            return postRequest(act.commentUrl + access_token, params);
        },
        /* 短信推送 */
        postMSG: function(access_token, params) {
            return postRequest(act.MSGUrl + access_token, params);
        },
        /* 导出跑友资料 */
        postRunnerInfo: function(access_token, params) {
            return postRequest(act.runnerInfoUrl + access_token, params);
        },
        /* 获取手机验证码 */
        postPhoneCode: function(params) {
            return postRequest(act.phoneCodeUrl, params);
        },
        /* 获取邮箱验证码 */
        postEmailCode: function(params) {
            return postRequest(act.emailCodeUrl, params);
        },
        /* 获取默认邮箱 */
        postEmail: function(access_token, params) {
            return postRequest(act.getEmailUrl + access_token, params);
        },
        /* 存储默认邮箱  */
        postAddEmail: function(access_token, params) {
            return postRequest(act.addEmailUrl + access_token, params);
        },
        /* 新增、删除邮箱  */
        postAddUserEmail: function(access_token, params) {
            return postRequest(act.addUserEmailUrl + access_token, params);
        },
        /* 获取活动集合地点  */
        postFindLocation: function(access_token, params) {
            return postRequest(act.findLocationUrl + access_token, params);
        },
        /* 创建活动地址  */
        postCreateLocation: function(access_token, params) {
            return postRequest(act.createLocationUrl + access_token, params);
        },
        /* 创建活动  */
        postCreateActivity: function(access_token, params) {
            return postMultipart(act.createActivityUrl + access_token, params);
        },
        /* 发现跑团 */
        postfindcityclub: function(access_token, params) {
            return postRequest(act.findcityclubUrL + access_token, params);
        },
        /* 创建跑团 */
        postBulidPaotuan: function(access_token, params) {
            return postMultipart(act.createclubUrl + access_token, params);
        },
        /* 按照地址或名称查询活动 */
        activitysearchUrL: function(access_token, params) {
            return postRequest(act.activitysearchUrL + access_token, params);
        },
        /* 获取活动项目信息 */
        postActivityJoinInfo: function(access_token, params) {
            return postRequest(act.avtivityJoinInfoUrl + access_token, params);
        },
        /* 新增报名填写的信息 */
        postCheckinInfo: function(access_token, params) {
            return postRequest(act.checkinInfoUrl + access_token, params);
        },
        /* 删除报名填写的信息 */
        postDelInfo: function(access_token, params) {
            return postRequest(act.delInfoUrl + access_token, params);
        },
        /* 新增活动项目 */
        postCreateProject: function(access_token, params) {
            return postRequest(act.createProjectUrl + access_token, params);
        },
        /* 搜索活动后导出邮箱 */
        actlistemailUrL: function(access_token, params) {
            return postRequest(act.actlistemailUrL + access_token, params);
        },
        /* 获取创建成功的活动信息 */
        postActInfo: function(access_token, params) {
            return postRequest(act.actInfoUrL + access_token, params);
        },
        /* 获取活动项目列表信息 */
        postActProject: function(access_token, params) {
            return postRequest(act.actProjectUrL + access_token, params);
        },
        /* 报名活动项目 */
        postRegProject: function(access_token, params) {
            return postRequest(act.regProjectUrL + access_token, params);
        },
        /* 取消报名活动项目 */
        postCancelReg: function(access_token, params) {
            return postRequest(act.cancelRegUrL + access_token, params);
        },
        /* 提交报名项目信息 */
        postUserRegInfo: function(access_token, params) {
            return postMultipart(act.userRegInfoUrL + access_token, params);
        },
        /* 支付宝支付 */
        postAlipay: function(access_token, params) {
            return postRequest(act.alipayUrL + access_token, params);
        },
        /* 点赞功能 */
        postPraise: function(access_token, params) {
            return postRequest(act.praiseUrL + access_token, params);
        },
        /* 取消活动 */
        postCancelAct: function(access_token, params) {
            return postRequest(act.cancelActUrL + access_token, params);
        },
        /* 用户信息 */
        postUserInfo: function(access_token, params) {
            return postRequest(act.userInfoUrL + access_token, params);
        },
        /* 判断用户报名是否支付 */
        postUserReg: function(access_token, params) {
            return postRequest(act.userRegUrL + access_token, params);
        },
        /* 新增、删除活动地址 */
        postUpdateLocation: function(access_token, params) {
            return postRequest(act.updateLocationUrL + access_token, params);
        },
        /* 复制活动 */
        postCopyActivity: function(access_token, params) {
            return postRequest(act.copyActivityUrL + access_token, params);
        },
        /* 上传图片 */
        postUploadImage: function(access_token, params) {
            return postMultipart(act.uploadImageUrL + access_token, params);
        },
        /* 获取活动详情 */
        postSharedAct: function(access_token, params) {
            return postRequest(act.sharedActUrL + access_token, params);
        },
        /* 获取订单详情 */
        postOrderInfo: function(access_token, params) {
            return postRequest(act.orderInfoUrL + access_token, params);
        }
    }
}]);


/* 封装一个格式化时间服务 */
App.factory('dateFormat', [function() {
    var format = function(data) {
        angular.forEach(data, function(value, index){
            var check_time = '', T1, T2, T3;
            if (value.act_start_time||value.act_end_time) {
                T1 = moment(value.act_start_time).format('MM-DD');
                T2 = moment(value.act_start_time).format('HH:mm');
                T3 = moment(value.act_end_time).format('HH:mm');
                check_time = T1 + ' ' + T2 + '-' + T3;
                data[index]['check_time'] = check_time;
            }

            if (value.act_start_time&&value.recurring_type=='1') {
                var week = null;
                var day = moment(value.act_start_time).format('e');
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

                data[index]['recurring_type_time'] = week;
            }

        });
    }

    return {
        format: function(data) {
            return format(data);
        }
    }
}]);


/* 封装一个计算时间差服务 */
App.factory('getDateDiff', [function(){
    var getDateDiff = function(start, now) {
        var s1 = start.getTime(),
            s2 = now.getTime(),
            total = (s2 - s1)/1000;
         
        var day       = parseInt(total / (24*60*60));       //计算整数天数
        var afterDay  = total - day*24*60*60;               //取得算出天数后剩余的秒数
        var hour      = parseInt(afterDay/(60*60));         //计算整数小时数
        var afterHour = total - day*24*60*60 - hour*60*60;  //取得算出小时数后剩余的秒数
        var min       = parseInt(afterHour/60);             //计算整数分
        var afterMin  = total - day*24*60*60 - hour*60*60 - min*60;  //取得算出分后剩余的秒数
        
        if (day>0) {
            var year = Math.floor(day/365); //计算年数
            var month = Math.floor(day/30); //计算月数
            if (year>0) {
                return year + '年之前';
            }
            if (month>0) {
                return month + '月之前';
            }
            return day + '天之前';
        } else if (hour>0) {
            return hour + '小时之前'
        } else if (min>0) {
            return min + '分钟之前'
        } else {
            return '刚刚'
        }
    }

    return {
        diff: function(start, now) {
            return getDateDiff(start, now);
        }
    }
}])


/* 封装一个QQ MAP服务 */
App.factory('mapService', ['$http', '$q', '$compile', function($http, $q, $compile) {
    var service = {
        /* 创建地图 */
        createMap: function(container, lat, lng) {
            var myLatlng = new qq.maps.LatLng(lat, lng);
            var myOptions = {
                zoom: 14,
                center: myLatlng
            };
            if (document.getElementById(container)) {
                document.getElementById(container).style.display = "block";
                var map = new qq.maps.Map(document.getElementById(container), myOptions);
                return map;
            }
        },
        /* 获取地图标识 */
        getMarker: function(map) {
            return new qq.maps.Marker({
                map: map,
                animation: qq.maps.MarkerAnimation.DOWN,
            });
        },
        /* 设置地图中心点位置及标识 */
        setMapMarker: function(marker, lat, lng, info) {
            marker.map.panTo(new qq.maps.LatLng(lat, lng));
            marker.setPosition(new qq.maps.LatLng(lat, lng));
        },
        /* 获取城市纬度、经度 */
        getCityLocation: function(map, callback) {
            return new qq.maps.CityService({
                map: map,
                complete: function(results) {
                    map.setCenter(results.detail.latLng);
                    if (angular.isFunction(callback)) {
                        callback(results.detail.latLng);
                    }
                }
            });
        },
        /* 获取地图信息 */
        getInfo: function(map) {
            return new qq.maps.InfoWindow({
                map: map
            });
        },
        /* 设置地图信息 */
        setInfo: function(info, marker, infotext) {
            info.open();
            info.setContent('<div style="text-align:center;white-space:nowrap;'
                    + 'margin:0;">' + infotext + '</div>');
            info.setPosition(marker.getPosition());
        },
        /* 获取地图标签 */
        getLabel: function(map) {
            return new qq.maps.Label({
                map: map,
            });
        },
        /* 设置地图标签 */
        setLabel: function(label, marker, infotext) {
            label.setPosition(marker.getPosition());
            label.setContent(infotext);
        },
        setCircle: function(map, lat, lng, radius) {
            var circle = new qq.maps.Circle({
                map: map,
                center: new qq.maps.LatLng(lat, lng),
                radius: radius,
                strokeWeight: 1
            });
        },
        computeDistance: function(lat, lng, lat1, lng1) {
            var start = new qq.maps.LatLng(lat, lng);
            var end = new qq.maps.LatLng(lat1, lng1);
            return Math.round(qq.maps.geometry.spherical
                    .computeDistanceBetween(start, end) * 10) / 10;
        },
        /* 获取城市信息 */
        geocoder: function(callBack, lat, lng) {
            var geocoder = new qq.maps.Geocoder({
                complete: function(result) {
                    if (angular.isFunction(callBack)) {
                        callBack(result.detail.address);
                    }
                }
            });
            var coord = new qq.maps.LatLng(lat, lng);
            geocoder.getAddress(coord);
        }
    };

    return service;
}]);


/* 封装一个upload file服务 */
App.factory('fileReader', ["$q", "$log", function($q, $log){
    var onLoad = function(reader, deferred, scope) {
        return function () {
            scope.$apply(function () {
                deferred.resolve(reader.result);
            });
        };
    };

    var onError = function (reader, deferred, scope) {
        return function () {
            scope.$apply(function () {
                deferred.reject(reader.result);
            });
        };
    };

    var getReader = function(deferred, scope) {
        var reader = new FileReader();
        reader.onload = onLoad(reader, deferred, scope);
        reader.onerror = onError(reader, deferred, scope);
        return reader;
    };

    var readAsDataURL = function (file, scope) {
        var deferred = $q.defer();
        var reader = getReader(deferred, scope);         
        reader.readAsDataURL(file);
        return deferred.promise;
    };

    return {
        readAsDataUrl: readAsDataURL  
    };
}])


/* 封装一个服务获取当前选中checkbox的索引值 */
App.factory('getChecked', [function(){

    var checked = function(name) {
        var thisName = document.getElementsByName(name);
        var length   = thisName.length;

        for(var i = 0; i < length; i++) {    
            if(thisName[i].checked) {
                return i;
            }       
        } 
    }

    return {
        getIndex: function(name) {
            return checked(name);
        }
    }

}]);


/* 封装一个拦截器服务，用以实现loading效果 */
App.factory('timestampMarker', ['$rootScope', '$timeout', function ($rootScope, $timeout) {
    var timestampMarker = {
        request: function (config) {
            $rootScope.loading = true;
            config.requestTimestamp = new Date().getTime();
            return config;
        },
        response: function (response) {
            // $timeout(function(){
                $rootScope.loading = false;
            // }, 3000);
            response.config.responseTimestamp = new Date().getTime();
            return response;
        }
    };
    return timestampMarker;
}]);