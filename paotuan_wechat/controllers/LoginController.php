<?php
namespace paotuan_wechat\controllers;
use Yii;
use common\component\WechatSDK\Wechat;
use common\models\UserOauth;
use paotuan_wechat\component\Util;
use paotuan_wechat\component\PaotuanController;
use common\models\User;
use common\models\AccessToken;

class LoginController extends PaotuanController {

    public function actionIndex(){
        //先获取用户openid判断是不是注册过
        $this->checkWechatOauth('/login/index');
        if( $this->user ){
            $uid=$this->user->uid;
            $user=User::findOne(['uid'=>$uid]);
            if(!empty($user)){
                $access_token=AccessToken::findOne(['uid'=>$uid]);
                if($access_token) {
                    $token = $access_token->access_token;
                }else{
                    $accessToken = new AccessToken();
                    $accessToken->uid = $uid;
                    $accessToken->login_type = 2;
                    $accessToken->save(false);
                    $token=$access_token->access_token;
                }
                if( empty($user->user_name) ){
                    Header("Location:http://paotuan.runningtogether.net/mobile/#/bindphone?type=login&access_token=$token");
                }else{
                    Header("Location:http://paotuan.runningtogether.net/mobile/#/bindphone?type=show&access_token=$token");
                }
            }else{
                Header("Location:http://paotuan.runningtogether.net/mobile/#/login");
            }
        }else{
            Header("Location:http://paotuan.runningtogether.net/mobile/#/login");
//        }
    }
    }

    public function checkOpenidOauth( $oAuthUrl ) {
        $code = \Yii::$app->request->get( "code", "" );
        $type = \Yii::$app->request->get( 'type' );
        if ( !$this->we_user || empty( $this->we_user->nick_name ) ) {
            if ( !empty( $code ) && !$type ) {
                $this->we_user = Util::getUserOpenid();
                if ( !$this->we_user ) {
                    throw new NotFoundHttpException( null, 000 );
                }
                return true;
            } else {
                $hostInfo = \Yii::$app->request->hostInfo;
                if ( $hostInfo == "http://wechat.runningtogether.net" ) {
                    $redirectUrl = "http://wechat.paobuqu.com/getCodeForTest.php?redirectUrl=" . $oAuthUrl;
                } else {
                    $redirectUrl = $hostInfo . $oAuthUrl;
                }
                \Yii::$app->response->redirect( $this->we_wechat->getOauthRedirect( $redirectUrl,'','snsapi_base' ) );
                \Yii::$app->end();
            }
        }
    }
    public function actionSharact(){
        $act_id=\yii::$app->request->get('act_id');
        if($act_id){
            \Yii::$app->session["act_id"]=$act_id;
        }
        $this->checkWechatOauth('/login/sharact');
        if($this->user){
            $uid=$this->user->uid;
            $access_token=AccessToken::findOne(['uid'=>$uid]);
            if($access_token){
                $access_token->login_type=1;
                $access_token->save(false);
            }else{
                $access_token=new AccessToken();
                $access_token->uid=$uid;
                $access_token->login_type=1;
                $access_token->save(false);
            }
            $access_token_value=$access_token->access_token;
            $hostInfo = \Yii::$app->request->hostInfo;
            if( \Yii::$app->session["act_id"] ){
                $act_id=\Yii::$app->session["act_id"];
            }
            return $this->redirect("$hostInfo/mobile/#/activity/detail?act_id=$act_id&access_token=$access_token_value");
        }
    }


}