<?php
return [
  	"queue_url"=>"http://worker.paobuqu.com/?r=main/register/index",
    /*又拍云账号信息*/
    'upyun'=>[
        'username'=>'paobuqu',
        'password'=>'paobuqu2015',
        'bucketname'=>'xiaoi',
        'image_size'=>[
            'small80'=>'!80X80',
            'big640'=>'!640X320',
	    'img_logo'=>'!mid',
            'auto640'=>'!640X640',
        ],
    ],
    /*小i微信参数信息*/
    'weixin_config' => [
        'wechart'=>[
            'id' 	 => 'gh_7a167312e413',
            'appid' 	 => 'wxe380ca3504f26643',
            'appsecret'  => '7916f82922f82693c88a9dbe50d78ba7',
            'token'	 => 'iRunner20130913',
        ],
        'android' =>[
            'appid'      => 'wx23d6ab254d4ef28c',
            'appsecret'  => '65c6a8df31ffc1fecc5b002712704988',            
        ],
        'tenpay'=>[
            'partnerid'  => '1226215101',
            'partnerkey' => 'fc0c4a339e807b6138f1a4c7f089584d',
            'paysignkey' => 'oIwHqgPREHspKcIxlcIWy50WIg2lCtoC1MDzbSnv2HPDlk4XSXGE29XBiaJceX2gyklUkhB2PM3PwGrA2OzJRupE7vNI31EXwPycWC4DToLx7AXM9wcpXDLF98xh4eio',
        ],
    ],
    /*跑盟支付宝参数信息*/
    'ali_config' => [
        'alipay'=>[
            'partnerid' => '2088711677278709',
            'partnerkey' => 'criftqtx9r9di1g9ck6v5w1vil4svnbv',
        ],
    ],
    'short_domain' => 'http://www.irunner.asia',
    'baseURL' => 'http://www.irunner.asia',
    'token_expire_time'=>259200,
    /*咕咚应用授权参数*/
    'codoon_config' => [
        'appid' => '9ef7cf0814a511e599a000163e000233',
        'appsecret' => '9ef7d19214a511e599a000163e000233',
    ],
    /*虎扑应用授权参数*/
    'hupu_config' => [
        'appid' => 'fab3573722e4522a',
        'appsecret' => '727f2bf5b639e607b143c8d9e3b4dd9f',
    ],
    /*益动应用授权参数*/
    'edoon_config' => [
        'appid' => '00c6d893c8f13190',
        'appsecret' => '6128752FF85BB9AA1027301BB3E8A8D6',
    ],
    /*小米应用授权参数*/
    'xiaomi_config' => [
        'appid' => 2882303761517378948,
        'appsecret' => 'IZyMxPNvLphZqDIPsoxTlA==',
    ],
    /*华米应用授权参数*/
    'huami_config' => [
        'third_appid' => '1440987103',
        'third_appsecret' => 'ZTIyODcxMWI4ZDFjOTI0MDNjY2JkODdkYjFmNmNkOTI',
    ],
];
