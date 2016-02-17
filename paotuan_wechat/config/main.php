<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'paotuan_wechat',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'paotuan_wechat\controllers',
    'components' => [
        'request'=>[
            'class'=>'yii\web\Request'
        ],
       'authManager' => [
    				'class' => 'yii\rbac\DbManager'
    		],
        'user' => [
            'identityClass' => 'common\models\AccessToken',
            'enableAutoLogin' => true,
            'enableSession' => true,
        ],
    	'session' => [
    				'timeout' => 1800,
    		],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['trace','error', 'warning','info']
                ]
            ]
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                 '<controller:(clubs)>/<club:\w+>/<action:\w+>'=>'<controller>/<action>',
            	 '<controller:(clubs)>/<club:\w+>/<action:\w+>/<id:\w+>'=>'<controller>/<action>',
                 '<controller:(runners)>/<action:\w+>/<params:\w+>'=>'<controller>/<action>',
                 '<controller:(act)>/<id:\w+>'=>'<controller>/index',
            	 '<controller:(act)>/<action:\w+>/<id:\w+>'=>'<controller>/<action>',
            	  '<controller:(activity)>/<action:\w+>/<id:\w+>'=>'<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];