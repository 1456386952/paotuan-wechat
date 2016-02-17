<?php

namespace paotuan_wechat\controllers;

use Yii;
use yii\web\Controller;
use common\component\CustomHelper;
use yii\helpers\ArrayHelper;
use common\models\WxToken;
use paotuan_wechat\component\Util;
use common\component\WechatSDK\Wechat;

class WechatController extends Controller {
	public $enableCsrfValidation = false;
	
	public function actionGetticket() {
		$ip = \Yii::$app->request->userIP;
		//if ($ip == "10.171.192.14" || $ip =="121.40.68.188") {
			echo $this->getJsApiTicket();
			\Yii::$app->end();
		//}
	}
	
	private function getJsApiTicket() {
		$data = WxToken::findOne(["type"=>WxToken::TYPE_JSAPI_TICKET]);
		if($data==null){
			$data = new WxToken();
		}
		if ($data->expire_time < time()) {
			$accessToken = $this->getAccessToken();
			// 如果是企业号用以下 URL 获取 ticket
			// $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
			$result = Util::http_get($url);
			$res = json_decode($result);
			$ticket = $res->ticket;
			if ($ticket) {
				$data->expire_time = time() + 7000;
				$data->data = $ticket;
				$data->type=WxToken::TYPE_JSAPI_TICKET;
				$data->save();
			}
		} else {
			$ticket = $data->data;
		}
	
		return $ticket;
	}
	
	private function getAccessToken() {
		// access_token 应该全局存储与更新，以下代码以写入到文件中做示例
		$data = WxToken::findOne(["type"=>WxToken::TYPE_ACCESS_TOKEN]);
		if($data==null){
			$data = new WxToken();
		}
		if ($data->expire_time < time()) {
			$options = \Yii::$app->params ['weixin_config'] ['wechart'];
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$options ["appid"]."&secret=".$options ["appsecret"];
			$res = json_decode(Util::http_get($url));
			$access_token = $res->access_token;
			if ($access_token) {
				$data->expire_time = time() + 7000;
				$data->data = $access_token;
				$data->type=WxToken::TYPE_ACCESS_TOKEN;
				$data->save();
			}
		} else {
			$access_token = $data->data;
		}
		return $access_token;
	}
	
	
	public function actionGetaccesstoken() {
		$ip = \Yii::$app->request->userIP;
		yii::info(\Yii::$app->request->getReferrer());
		//if ($ip == "10.171.192.14" || $ip =="121.40.68.188") {
			echo $this->getAccessToken();
			\Yii::$app->end();
		//}
	}
	
	public function actionJsapiparams(){
		$url = \Yii::$app->request->post("url",null);
		CustomHelper::RetrunJson(Util::getJssdkParams($url));
	}
	
	public function actionGetuserinfo(){
		$openid=\Yii::$app->request->post("openid");
		$options = \Yii::$app->params ['weixin_config'] ['wechart'];
		$accessToken = Util::http_get("http://wechat.paobuqu.com/wechat/getaccesstoken");
		echo Util::http_get("https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$accessToken."&openid=$openid&lang=zh_CN");
	}
	
	public function actionGetdeviceinfo(){
		$ticket=\Yii::$app->request->post("ticket");
		$accessToken = Util::http_get("http://wechat.paobuqu.com/wechat/getaccesstoken");
		echo Util::http_post("https://api.weixin.qq.com/shakearound/user/getshakeinfo?access_token=".$accessToken,json_encode(["ticket"=>$ticket]));
	}
}
