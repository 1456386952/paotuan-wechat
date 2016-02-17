<?php
namespace paotuan_wechat\component;

use yii\rest\Controller;
use yii\helpers\ArrayHelper;
use yii\filters\auth\CompositeAuth;
//use yii\filters\auth\HttpBearerAuth;
//use yii\filters\auth\HttpBasicAuth;
use common\component\SubQueryParamAuth;
use common\models\User;

/*用于验证认证的控制器*/
class AuthController extends Controller
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                #这个地方使用`ComopositeAuth` 混合认证
                'class' => CompositeAuth::className(),
                #`authMethods` 中的每一个元素都应该是 一种 认证方式的类或者一个 配置数组
                'authMethods' => [
                    //HttpBasicAuth::className(),
                    //HttpBearerAuth::className(),
                    SubQueryParamAuth::className()
                ]
            ]
        ]);
    }

    public function actionIndex()
    {
        $response=['status'=>1,'message'=>'非法操作！'];
        return $response;
    }
}
