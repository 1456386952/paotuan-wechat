<?php

namespace paotuan_wechat\component;

use common\component\Jssdk;
use common\models\UserBindLog;
use common\models\UserPaper;
use common\component\WechatSDK;
use common\models\UserOauth;
use common\models\UserMaster;
use paotuan_wechat\models\Mileage;
use common\component\CustomHelper;
use common\models\EmailTemplate;
use common\component\MailManage;
use yii\base\Exception;
use common\models\AccessToken;

include_once dirname ( __FILE__ ) . "/WxPayPubHelper/WxPayPubHelper.php";
class WxUtil {
	const API_ACTIVITY_CANCEL = "v3/wechat/eventcancel";
	const API_CLUB_AUDIT_RESULT = "v3/wechat/auditresult";
	public static function getDeviceInfo($ticket) {
		$accessToken = Util::http_get ( "http://wechat.paobuqu.com/wechat/getaccesstoken" );
		$r = Util::http_post ( "https://api.weixin.qq.com/shakearound/user/getshakeinfo?access_token=" . $accessToken, "{\"ticket\":\"$ticket\"}", "json" );
		if ($r) {
			$r = json_decode ( $r );
			if ($r->errcode == 0) {
				return $r;
			}
		}
		return false;
	}
	// 裂变红包
	public static function sendGroupRedpack($para) {
		$url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack";
		\WxPayConf_pub::initialize ();
		$util = new \Common_util_pub ();
		if (isset ( $para ["total_num"] ) && $para ["total_num"] < 3) {
			$para ["total_num"] = 3;
		}
		$para ["wxappid"] = \WxPayConf_pub::APPID;
		$para ["mch_id"] = \WxPayConf_pub::MCHID;
		$para ["nonce_str"] = $util->createNoncestr ();
		$para ["mch_billno"] = date ( "YmdHis" ) . time ();
		$para ["send_name"] = "小i爱跑";
		$para ["amt_type"] = "ALL_RAND";
		$para ["sign"] = $util->getSign ( $para );
		return $util->postXmlSSLCurl ( $util->arrayToXml ( $para ), $url );
	}
	// 现金红包
	public static function sendRedpack($para) {
		$url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
		\WxPayConf_pub::initialize ();
		$util = new \Common_util_pub ();
		$para ["nick_name"] = "小i爱跑";
		$para ["send_name"] = "小i爱跑";
		$para ["wxappid"] = \WxPayConf_pub::APPID;
		$para ["mch_id"] = \WxPayConf_pub::MCHID;
		$para ["nonce_str"] = $util->createNoncestr ();
		$para ["mch_billno"] = date ( "YmdHis" ) . time ();
		$para ["min_value"] = $para ["total_amount"];
		$para ["max_value"] = $para ["total_amount"];
		$para ["total_num"] = 1;
		$para ["client_ip"] = "192.168.0.1";
		$para ["wishing"] = "满满的爱";
		$para ["act_name"] = "测试";
		$para ["remark"] = "抢红包啦";
		$para ["total_amount"] = 100;
		$para ["re_openid"] = "oyL64uI7WE6HUm2RJ60ahVuZpbOc";
		$para ["sign"] = $util->getSign ( $para );
		return $util->postXmlSSLCurl ( $util->arrayToXml ( $para ), $url );
	}
	public function createNoncestr($length = 32) {
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = "";
		for($i = 0; $i < $length; $i ++) {
			$str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
		}
		return $str;
	}
	public function getSign($Obj) {
		foreach ( $Obj as $k => $v ) {
			$Parameters [$k] = $v;
		}
		// 签名步骤一：按字典序排序参数
		ksort ( $Parameters );
		$String = formatBizQueryParaMap ( $Parameters, false );
		// echo '【string1】'.$String.'</br>';
		// 签名步骤二：在string后加入KEY
		$String = $String . "&key=d19a4f7fc95036d7f5855e2c9d998e49";
		// echo "【string2】".$String."</br>";
		// 签名步骤三：MD5加密
		$String = md5 ( $String );
		// echo "【string3】 ".$String."</br>";
		// 签名步骤四：所有字符转为大写
		$result_ = strtoupper ( $String );
		// echo "【result】 ".$result_."</br>";
		return $result_;
	}
	public function formatBizQueryParaMap($paraMap, $urlencode) {
		$buff = "";
		ksort ( $paraMap );
		foreach ( $paraMap as $k => $v ) {
			if ($urlencode) {
				$v = urlencode ( $v );
			}
			// $buff .= strtolower($k) . "=" . $v . "&";
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar;
		if (strlen ( $buff ) > 0) {
			$reqPar = substr ( $buff, 0, strlen ( $buff ) - 1 );
		}
		return $reqPar;
	}
	public function arrayToXml($arr) {
		$xml = "<xml>";
		foreach ( $arr as $key => $val ) {
			if (is_numeric ( $val )) {
				$xml = $xml . "<" . $key . ">" . $val . "</" . $key . ">";
			} else {
				$xml = $xml . "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}
		}
		$xml .= "</xml>";
		return $xml;
	}
	public static function sendWXTemplateMsg($objects) {
		$url = \Yii::$app->params ['queue_url'];
		$r = Util::http_post ( $url, json_encode ( [ 
				"objects" => $objects 
		] ) );
		if ($r) {
			$r = json_decode ( $r );
			if ($r->code == 0) {
				return true;
			} else {
				return false;
			}
		}
	}
	public static function getObject($params, $api_url) {
		return [ 
				'action' => 'PaotuanApi',
				'params' => [ 
						'url' => Util::getHost () . $api_url,
						'params' => $params 
				] 
		];
	}
	public static function sendTemplateClubMember($params) {
		$url = Util::getHost () . "v3/wechat/wxmember";
		$r = Util::http_post ( $url, $params );
		if ($r) {
			$r = json_decode ( $r );
			if ($r == 0) {
				return true;
			} else {
				return false;
			}
		}
	}
	public static function sendTemplateActPublish($params) {
		$url = Util::getHost () . "v3/wechat/wxactbegin";
		$r = Util::http_post ( $url, $params );
		if ($r) {
			$r = json_decode ( $r );
			if ($r == 0) {
				return true;
			} else {
				return false;
			}
		}
	}
	public static function getAccessToken() {
		return Util::http_get ( "http://wechat.paobuqu.com/wechat/getaccesstoken" );
	}
	public static function getWxUserInfo($openid) {
		$options = \Yii::$app->params ['weixin_config'] ['wechart'];
		$r = Util::http_get ( "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . self::getAccessToken () . "&openid=$openid&lang=zh_CN" );
		if ($r) {
			return json_decode ( $r );
		} else {
			return false;
		}
	}
	public static function downLoadFile($media_id) {
		$token = self::getAccessToken ();
		if ($token) {
			$url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=$token&media_id=$media_id";
			$oCurl = curl_init ();
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
			curl_setopt ( $oCurl, CURLOPT_URL, $url );
			curl_setopt($oCurl,CURLOPT_RETURNTRANSFER,1);
			$fileName = \Yii::$app->basePath . "/upload/".time().CustomHelper::randomPassword ( 10 ).".jpg";
			$fp = fopen ( $fileName, "wb" );
			curl_setopt($oCurl,CURLOPT_FILE,$fp);
			$sContent = curl_exec ( $oCurl );
			$aStatus = curl_getinfo ( $oCurl );
			curl_close ( $oCurl );
			fclose($fp);
			if (intval ( $aStatus ["http_code"] ) == 200&&$aStatus["content_type"]=='image/jpeg') {
				return $fileName;
			} else {
				unlink($fileName);
				return false;
			}
		}
	}
}
