'use strict';
angular.module('myApp.config', [])
.constant('Config', {
  ROOT: 'http://mi.mixpay.cn/app',
  API_ROOT: 'http://mi.mixpay.cn/api/v1',
  WX_AUTH_URL: 'http://mi.mixpay.cn/auth/weixin',
  AJAX_SUCCESS_CODE: 0,
  SITE_NAME: '跑团小秘',
  DEFAULT_DOMAIN: 'fanscrew'
});
