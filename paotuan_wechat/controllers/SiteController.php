<?php
namespace paotuan_wechat\controllers;

use Yii;
use yii\web\Controller;
use common\component\CustomHelper;
use yii\base\Exception;
use paotuan_wechat\component\Util;
use common\component\WechatSDK\Wechat;
use common\models\UserOauth;
use yii\helpers\ArrayHelper;
use common\models\UserPaper;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public $enableCsrfValidation = false;
    public function actionError(){
    	switch (Yii::$app->getErrorHandler()->exception->statusCode){
    		case 404:\Yii::$app->response->redirect("/notfound.html",404);break;
    		case 000:\Yii::$app->response->redirect("/oauthError.html",404);break;
    		case 500:\Yii::$app->response->redirect("/500.html",500);break;
    		default:\Yii::$app->response->redirect("/notfound.html",404);break;
    	}
    }
    
    public function actionLogin(){
    	$result = ["status"=>0];
    	$user = Util::checkLogin();
    	if($user){
    		$result["user"]=$user;
    	}
    }
    
    public function actionOauthurl(){
    	$oAuthUrl = \Yii::$app->request->post("redirectUrl");
    	$hostInfo = \Yii::$app->request->hostInfo;
     	if($hostInfo=="http://wechat.runningtogether.net"){
     		$redirectUrl="http://wechat.paobuqu.com/getCodeForTest.php?redirectUrl="."/site/redirect?redirectUrl=".$oAuthUrl;
     	}else{
    		$redirectUrl = $hostInfo."/site/redirect?redirectUrl=".$oAuthUrl;
    	}
    	echo Wechat::getDefaultInstance()->getOauthRedirect ( $redirectUrl);
    }
    
    public function actionRedirect(){
    	$redirectUrl = str_replace("$", "#",\Yii::$app->request->get("redirectUrl"));
    	$redirectUrl = str_replace("@", "&",$redirectUrl);
    	$code = \Yii::$app->request->get ( "code" );
    	$redirectUrl = $redirectUrl."&code=$code";
    	Util::checkLogin();
    	return $this->renderPartial("redirect",["redirectUrl"=>$redirectUrl,"openid"=>Yii::$app->session ["openid"]]);
    }
    
    public function actionForward(){
    	$code = trim(\Yii::$app->request->get ( "code","" ));
    	$redirectUrl =  \Yii::$app->request->get('redirectUrl');
    	if(!empty($code)){
    		$redirectUrl = rawurldecode($redirectUrl);
    		if(stripos($redirectUrl, "?")===false){
    			$redirectUrl =$redirectUrl.'?code='.$code; 
    		}else{
    			$redirectUrl =$redirectUrl.'&code='.$code;
    		}
    		$this->redirect($redirectUrl);
    		return ;
    	}else{
    		$redirectUrl ="http://wechat.paobuqu.com/site/forward?redirectUrl=$redirectUrl";
    		$this->redirect(Wechat::getDefaultInstance()->getOauthRedirect($redirectUrl));
    	}
    }
    
    public function actionUserinfo(){
    	$openid = \Yii::$app->request->post("openid",null);
    	$result = ["status"=>0];
    	if($openid){
    		$uo = UserOauth::findAll ( [
    				"oauth_openid" => $openid
    		] )[0];
    		$user = $uo->user;
    		if($user){
    			$result["status"]=1;
    			$user = ArrayHelper::toArray($user,["common\models\UserMaster"=>["uid","user_face","user_cell","user_email","nick_name"]]);
    			$user["user_face_orginal"] =$user["user_face"];
    			$user["user_face"] = Util::getUserFace($user["user_face"]);
    			$result["user"] = $user;
    		}
    	}
    	if($result["status"]==0){
    		\Yii::error("openid获取用户信息失败->$openid");
    	}
    	CustomHelper::RetrunJson($result);
    }
    
    public function actionUserinfoall(){
    	$openid = \Yii::$app->request->post("openid",null);
    	if($openid){
    		$uo = UserOauth::findAll ( [
    				"oauth_openid" => $openid
    		] )[0];
    		$user = $uo->user;
    		if($user){
    			$userInfo =$user->userInfo; 
    			$user = ArrayHelper::toArray($user,["common\models\UserMaster"=>["uid","user_face","user_cell","user_email","nick_name"]]);
    			$user["user_face_orginal"] =$user["user_face"];
    			$user["user_face"] = Util::getUserFace($user["user_face"]);
    			if($userInfo){
    				$user["info"] = ArrayHelper::toArray($userInfo);
    				$userPaper = UserPaper::findAll(["uid"=>$userInfo->uid,"paper_status"=>0]);
    				$user["info"]["papers"] = [];
    				$user["info"]["papers"]["length"] = count($userPaper);
    				foreach ($userPaper as $p){
    					switch($p->paper_type){
    						case UserPaper::PAPER_TYPE_ID_COPY:
    							$user["info"]["papers"]["id_copy"] =$p->paper_url;
    							break;
    					    case UserPaper::PAPER_TYPE_ID_COPY_BACK:
    								$user["info"]["papers"]["id_copy_back"] = $p->paper_url;
    								break;
    						case UserPaper::PAPER_TYPE_HEALTH:
    									$user["info"]["papers"]["health"] = $p->paper_url;
    									break;
    						case UserPaper::PAPER_TYPE_COMPLETE:
    							if(!isset($user["info"]["papers"]["certs"] )){
    								$user["info"]["papers"]["certs"]=[];
    							}
    							$tmp = new \stdClass();
    							$tmp->url =$p->paper_url; 
    							array_push($user["info"]["papers"]["certs"],$tmp);
    										break;
    					}
    				}
    			}
    			$result["user"] = $user;
    		}
    		CustomHelper::RetrunJson($result);
    	}
    }
    
    public function actionTest(){
    	return $this->renderPartial("test");
    }
}