<?php
defined('URL_ASSETS') or define('URL_ASSETS', '/assets/');
defined('URL_UPLOADS') or define('URL_UPLOADS', '/uploads/');
defined('URL_IMAGE') or define('URL_IMAGE', 'http://xiaoi.b0.upaiyun.com/');
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
error_reporting(E_ALL^E_NOTICE);
require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../config/bootstrap.php');
header("Access-Control-Allow-Origin: *");
$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php'),
    require(__DIR__ . '/../config/main.php'),
    require(__DIR__ . '/../config/main-local.php')
);
$application = new yii\web\Application($config);
$application->run();

