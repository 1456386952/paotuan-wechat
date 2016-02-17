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
use paotuan_wechat\models\Activity;

class Util {
	Const CREDIT_EVENT_ACT_REG="act_reg";
	Const CREDIT_EVENT_ACT_SIGN="act_sign";
	Const CREDIT_EVENT_CLUB_JOIN="join_club";
	
	public static function isUrl($str) {
		return preg_match ( "/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $str );
	}
	public static function postData($url, $data) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		// 设置header
		curl_setopt ( $ch, CURLOPT_HEADER, FALSE );
		// 要求结果为字符串且输出到屏幕上
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		// post提交方式
		curl_setopt ( $ch, CURLOPT_POST, TRUE );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		// 运行curl
		$data = curl_exec ( $ch );
		curl_close ( $$ch );
		return $data;
		// 返回结果
	}
	public static function getJssdkParams($url=null) {
		$options = \Yii::$app->params ['weixin_config'] ['wechart'];
		$jssdk = new Jssdk ( $options ["appid"], $options ["appsecret"] );
		return $jssdk->getSignPackage ($url);
	}
	public static function checkCellCode($params) {
		$code1 = UserBindLog::findOne ( [ 
				"bind_type" => 1,
				"bind_info" => $params ["cell"],
				"bind_code" => $params ["cellCode"],
				"bind_status" => 0 
		] );
		if ($code1 === null || strtotime ( $code1->expiry_time ) < time ()) {
			return false;
		}
		$code1->bind_status = 1;
		$code1->save ();
		return true;
	}
	
	public static function copyMemberToUserInfo($userInfo, $member) {
		$userInfo->passport_name = $member->passport_name;
		$userInfo->user_gender = $member->gender;
		if (! empty ( $member->nationality ))
			$userInfo->nationality = $member->nationality;
		if (empty ( $userInfo->id_type ) || ! empty ( $member->id_type ))
			$userInfo->id_type = $member->id_type;
		if (empty ( $userInfo->id_number ) || ! empty ( $member->id_number ))
			$userInfo->id_number = $member->id_number;
		if (! empty ( $member->birthday ))
			$userInfo->birthday = $member->birthday;
		if (! empty ( $member->shirt_size ))
			$userInfo->tshirt_size = $member->shirt_size;
		if (! empty ( $member->shoe_size ))
			$userInfo->shoes_size = $member->shoe_size;
		if (! empty ( $member->address ))
			$userInfo->address = $member->address;
		if (! empty ( $member->blood_type ))
			$userInfo->blood_type = $member->blood_type;
		if (! empty ( $member->height ))
			$userInfo->height = $member->height;
		if (! empty ( $member->weight ))
			$userInfo->weight = $member->weight;
		if ( ! empty ( $member->medical_history ))
			$userInfo->medical_history = $member->medical_history;
		if (empty ( $userInfo->emerge_name ) || ! empty ( $member->emerge_name )) {
			$userInfo->emerge_name = $member->emerge_name;
		}
		if (empty ( $userInfo->emerge_cell ) || ! empty ( $member->emerge_cell )) {
			$userInfo->emerge_cell = $member->emerge_cell;
		}
		if (empty ( $userInfo->emerge_ship ) || ! empty ( $member->emerge_ship )) {
			$userInfo->emerge_ship = $member->emerge_ship;
		}
		if (empty ( $userInfo->user_email ) || ! empty ( $member->user_email ))
			$userInfo->user_email = $member->email;
		if (empty ( $userInfo->user_cell ) || ! empty ( $member->user_cell ))
			$userInfo->user_cell = $member->cell;
		if (! empty ( $member->run_age ))
			$userInfo->run_age = $member->run_age;
		$userInfo->update_time = date ( "Y-m-d H:i:s" );
		if (! empty ( $member->id_image )) {
			$userPaper = UserPaper::findOne ( [
					"uid" => $userInfo->uid,
					"paper_type" => UserPaper::PAPER_TYPE_ID_COPY
			] );
			if ($userPaper !== null) {
				$userPaper->paper_url = CustomHelper::CreateImageUrl ( $member->id_image );
				$userPaper->save ();
			} else {
				UserPaper::addPaper ( array (
						"uid" => $member->uid,
						"url" => CustomHelper::CreateImageUrl ( $member->id_image )
				), "idcopy" );
			}
		}
		if (! empty ( $member->id_copy_back )) {
			$userPaper_back = UserPaper::findOne ( [
					"uid" => $userInfo->uid,
					"paper_type" => UserPaper::PAPER_TYPE_ID_COPY_BACK
			] );
			if ($userPaper_back !== null) {
				$userPaper_back->paper_url = CustomHelper::CreateImageUrl ( $member->id_copy_back );
				$userPaper_back->save ();
			} else {
	
				UserPaper::addPaper ( array (
						"uid" => $member->uid,
						"url" => CustomHelper::CreateImageUrl ( $member->id_copy_back )
				), "idcopyback" );
			}
		}
		return $userInfo;
	}
	
	
	public static function copyUserInfoToMember($member, $userInfo) {
		$member->passport_name = $userInfo->passport_name;
		$member->gender = $userInfo->user_gender;
		$member->nationality = $userInfo->nationality;
		if(empty($member->id_type)){
			$member->id_type = $userInfo->id_type;
		}
		if(empty($member->id_number)){
				$member->id_number = $userInfo->id_number;
		}
		$member->birthday = $userInfo->birthday;
		$member->shirt_size = $userInfo->tshirt_size;
		$member->shoe_size = $userInfo->shoes_size;
		$member->address = $userInfo->address;
		$member->blood_type = $userInfo->blood_type;
		$member->height = $userInfo->height;
		$member->weight = $userInfo->weight;
		$member->weight = $userInfo->weight;
		$member->morning_pulse = $userInfo->morning_pulse;
		if (empty ( $member->emerge_name ))
			$member->emerge_name = $userInfo->emerge_name;
		if (empty ( $member->emerge_cell ))
			$member->emerge_cell = $userInfo->emerge_cell;
		if (empty ( $member->emerge_ship ))
			$member->emerge_ship = $userInfo->emerge_ship;
		if (empty ( $member->email )) {
			$member->email = $userInfo->user_email;
		}
		if (empty ( $member->cell )) {
			$member->cell = $userInfo->user_cell;
		}
		$member->run_age = $userInfo->run_age;
		$userPaper = UserPaper::findOne ( [ 
				"uid" => $userInfo->uid,
				"paper_type" => UserPaper::PAPER_TYPE_ID_COPY 
		] );
		if ($userPaper !== null) {
			$member->id_image = substr ( $userPaper->paper_url, strlen ( "http://xiaoi.b0.upaiyun.com/" ) );
		}
		$userPaper = UserPaper::findOne ( [ 
				"uid" => $userInfo->uid,
				"paper_type" => UserPaper::PAPER_TYPE_ID_COPY_BACK 
		] );
		if ($userPaper !== null) {
			$member->id_copy_back = substr ( $userPaper->paper_url, strlen ( "http://xiaoi.b0.upaiyun.com/" ) );
		}
		$userPaper = UserPaper::findOne ( [ 
				"uid" => $userInfo->uid,
				"paper_type" => UserPaper::PAPER_TYPE_HEALTH,
				"paper_status" => 0 
		] );
		if ($userPaper !== null) {
			$member->health_check_certificate = substr ( $userPaper->paper_url, strlen ( "http://xiaoi.b0.upaiyun.com/" ) );
		}
		return $member;
	}
	public static function copyUserInfoToRegister($register, $userInfo) {
		if (empty ( $register->passport_name ))
		$register->passport_name = $userInfo->passport_name;
		if (empty ( $register->user_gender ))
		$register->user_gender = $userInfo->user_gender;
		if (empty ( $register->nationality ))
		$register->nationality = $userInfo->nationality;
		if (empty ( $register->id_type ))
		$register->id_type = $userInfo->id_type;
		if (empty ( $register->id_number ))
		$register->id_number = $userInfo->id_number;
		if (empty ( $register->birthday ))
		$register->birthday = $userInfo->birthday;
		if (empty ( $register->tshirt_size ))
		$register->tshirt_size = $userInfo->tshirt_size;
		if (empty ( $register->shoes_size ))
		$register->shoes_size = $userInfo->shoes_size;
		if (empty ( $register->address ))
		$register->address = $userInfo->address;
		if (empty ( $register->blood_type ))
		$register->blood_type = $userInfo->blood_type;
		if (empty ( $register->height ))
		$register->height = $userInfo->height;
		if (empty ( $register->weight ))
		$register->weight = $userInfo->weight;
		if (empty ( $register->medical_history ))
		$register->medical_history = $userInfo->medical_history;
		if (empty ( $register->emerge_name ))
			$register->emerge_name = $userInfo->emerge_name;
		if (empty ( $register->emerge_cell ))
			$register->emerge_cell = $userInfo->emerge_cell;
		if (empty ( $register->emerge_ship ))
			$register->emerge_ship = $userInfo->emerge_ship;
		if (empty ( $register->user_email ))
			$register->user_email = $userInfo->user_email;
		if (empty ( $register->user_cell ))
			$register->user_cell = $userInfo->user_cell;
		
		$userPaper = UserPaper::findOne ( [ 
				"uid" => $userInfo->uid,
				"paper_type" => UserPaper::PAPER_TYPE_ID_COPY 
		] );
		if ($userPaper !== null) {
			$register->id_copy = substr ( $userPaper->paper_url, strlen ( "http://xiaoi.b0.upaiyun.com/" ) );
		}
		$userPaper = UserPaper::findOne ( [ 
				"uid" => $userInfo->uid,
				"paper_type" => UserPaper::PAPER_TYPE_ID_COPY_BACK 
		] );
		if ($userPaper !== null) {
			$register->id_copy_back = substr ( $userPaper->paper_url, strlen ( "http://xiaoi.b0.upaiyun.com/" ) );
		}
		$userPaper = UserPaper::findOne ( [ 
				"uid" => $userInfo->uid,
				"paper_type" => UserPaper::PAPER_TYPE_HEALTH,
				"paper_status" => 0 
		] );
		if ($userPaper !== null) {
			$register->medical_report = substr ( $userPaper->paper_url, strlen ( "http://xiaoi.b0.upaiyun.com/" ) );
		}
		
		$register->certs= UserPaper::findAll(["uid"=>$userInfo->uid,"paper_type"=>UserPaper::PAPER_TYPE_COMPLETE,"paper_status"=>0]);
		return $register;
	}
	public static function copyRegisterToUserInfo($userInfo, $register) {
		if (! empty ( $register->passport_name ))
		$userInfo->passport_name = $register->passport_name;
		if (! empty ( $register->user_gender ))
		$userInfo->user_gender = $register->user_gender;
		if (! empty ( $register->nationality ))
			$userInfo->nationality = $register->nationality;
		if (! empty ( $register->id_type ))
			$userInfo->id_type = $register->id_type;
		if (! empty ( $register->id_number ))
			$userInfo->id_number = $register->id_number;
		if (! empty ( $register->birthday ))
			$userInfo->birthday = $register->birthday;
		if (! empty ( $register->tshirt_size ))
			$userInfo->tshirt_size = $register->tshirt_size;
		if (! empty ( $register->shoes_size ))
			$userInfo->shoes_size = $register->shoes_size;
		if (! empty ( $register->address ))
			$userInfo->address = $register->address;
		if (! empty ( $register->blood_type ))
			$userInfo->blood_type = $register->blood_type;
		if (! empty ( $register->height ))
			$userInfo->height = $register->height;
		if (! empty ( $register->weight ))
			$userInfo->weight = $register->weight;
		if (! empty ( $register->medical_history ))
			$userInfo->medical_history = $register->medical_history;
		if (empty ( $userInfo->emerge_name ) || ! empty ( $register->emerge_name )) {
			$userInfo->emerge_name = $register->emerge_name;
		}
		if (empty ( $userInfo->emerge_cell ) || ! empty ( $register->emerge_cell )) {
			$userInfo->emerge_cell = $register->emerge_cell;
		}
		if (empty ( $userInfo->emerge_ship ) || ! empty ( $register->emerge_ship )) {
			$userInfo->emerge_ship = $register->emerge_ship;
		}
		if (empty ( $userInfo->user_email ) && ! empty ( $register->user_email )) {
			$userInfo->user_email = $register->user_email;
		}
		$userInfo->update_time = date ( "Y-m-d H:i:s" );
		if (! empty ( $register->id_copy )) {
			$userPaper = UserPaper::findOne ( [ 
					"uid" => $userInfo->uid,
					"paper_type" => UserPaper::PAPER_TYPE_ID_COPY 
			] );
			if ($userPaper !== null) {
				$userPaper->paper_url = CustomHelper::CreateImageUrl ( $register->id_copy );
				$userPaper->save ();
			} else {
				UserPaper::addPaper ( array (
						"uid" => $register->uid,
						"url" => CustomHelper::CreateImageUrl ( $register->id_copy ) 
				), "idcopy" );
			}
		}
		if (! empty ( $register->id_copy_back )) {
			$userPaper_back = UserPaper::findOne ( [ 
					"uid" => $userInfo->uid,
					"paper_type" => UserPaper::PAPER_TYPE_ID_COPY_BACK 
			] );
			if ($userPaper_back !== null) {
				$userPaper_back->paper_url = CustomHelper::CreateImageUrl ( $register->id_copy_back );
				$userPaper_back->save ();
			} else {
				
				UserPaper::addPaper ( array (
						"uid" => $register->uid,
						"url" => CustomHelper::CreateImageUrl ( $register->id_copy_back ) 
				), "idcopyback" );
			}
		}
		if (! empty ( $register->medical_report )) {
			$userPaper_medical = UserPaper::findOne ( [ 
					"uid" => $userInfo->uid,
					"paper_type" => UserPaper::PAPER_TYPE_HEALTH,
					"paper_status" => 0 
			] );
			if ($userPaper_medical !== null) {
				$userPaper_medical->paper_url = CustomHelper::CreateImageUrl ( $register->medical_report );
				$userPaper_medical->save ();
			} else {
				
				UserPaper::addPaper ( array (
						"uid" => $register->uid,
						"url" => CustomHelper::CreateImageUrl ( $register->medical_report ) 
				), "report" );
			}
		}
		
		if(!empty($register->certs)){
			$certs = explode(",", $register->certs);
			UserPaper::deleteAll(["uid"=>$register->uid,"paper_type"=>3]);
			foreach ($certs as $cert){
				UserPaper::addPaper(["uid"=>$register->uid,"url"=>CustomHelper::CreateImageUrl($cert)], "cert");
			}
		}
		return $userInfo;
	}
	public static function getWeekFirstDay($time = null) {
		if (empty ( $time )) {
			$time = time ();
		}
		return date ( 'Y-m-d', $time - 86400 * date ( 'w', $time ) + (date ( 'w', $time ) > 0 ? 86400 : - 6 * 86400) );
	}
	public static function getWeekLastDay($time = null) {
		if (empty ( $time )) {
			$time = time ();
		}
		return date ( "Y-m-d", strtotime ( "+6 days", $time - 86400 * date ( 'w', $time ) + (date ( 'w', $time ) > 0 ? 86400 : - 6 * 86400) ) );
	}
	public static function getMonthFirstDay($time = null) {
		if (empty ( $time )) {
			$time = time ();
		}
		return date ( "Y-m-01", $time );
	}
	public static function getMonthLastDay($time = null) {
		if (empty ( $time )) {
			$time = time ();
		}
		$firstDay = date ( "Y-m-01", $time );
		return date ( "Y-m-d", strtotime ( "$firstDay+1 month -1 day" ) );
	}
	public static function getWechatUser() {
		$user = null;
		$data = self::checkLogin ();
		if ($data && $data->status == 0) {
			$user = UserMaster::findOne ( $data->result->uid );
		}
		return $user;
	}
	//只获取用户openid不去注册
	public static function getUserOpenid() {
		$user = null;
		$data = self::checkLoginOpenid();
		return $data;
	}

	public static function getUserFromSession() {
		$user = null;
		if (isset ( \Yii::$app->session ["openid"] )) {
			$openid = \Yii::$app->session ["openid"];
			$uo = UserOauth::findAll ( [ 
					"oauth_openid" => $openid 
			] )[0];
			$user = $uo->user;
		}
		return $user;
	}
	
public static function getdistance($lat1,$lng1,$lat2,$lng2){
	//将角度转为狐度
	$radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
	$radLat2=deg2rad($lat2);
	$radLng1=deg2rad($lng1);
	$radLng2=deg2rad($lng2);
	$a=$radLat1-$radLat2;
	$b=$radLng1-$radLng2;
	$s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
	return $s;
}
	public static function checkLogin() {
		$code = \Yii::$app->request->get ( "code" );
		if ($code) {
			$wechat = WechatSDK::getInstance ();
			$result = $wechat->getOauthAccessToken ( $code );
			if ($result) {
				if (\Yii::$app->request->hostInfo == "http://wechat.runningtogether.net") {
					$url = "http://www.runningtogether.net/v3/site/login/wechat";
				} else {
					$url = "http://www.paobuqu.com/v3/site/login/wechat";
				}
				\Yii::$app->session ["openid"] = $result ["openid"];
				$result = $wechat->http_post ( $url, array (
						"access_token" => $result ["access_token"],
						"openid" => $result ["openid"],
						"oauth_type" => 2 
				) );
				
				if ($result) {
					$user = json_decode ( $result );
					return $user;
				}
			}
		}
		return false;
	}
	//不登录只要openid
	public static function checkLoginOpenid() {
		$code = \Yii::$app->request->get ( "code" );
		if ($code) {
			$wechat = WechatSDK::getInstance ();
			$result = $wechat->getOauthAccessToken ( $code );
			if ($result) {
				return $result;
			}
		}
		return false;
	}
	
	public static function getTimeFromSec($sec) {
		if ($sec == 0) {
			return "";
		}
		$msec = $sec % 3600;
		$h = floor ( $sec / 3600 );
		$result = "";
		if ($msec == 0) {
			if ($h != 0) {
				return "$h:0'0\"";
			} else {
				$result = $h;
			}
		} else {
			if ($h != 0) {
				$result = $h;
			}
			$ssec = $msec % 60;
			$m = floor ( $msec / 60 );
			if ($m != 0) {
				if ($result == "") {
					$result = $m . "'";
				} else {
					$result = $result . ":" . $m . "'";
				}
			}
			if ($ssec != 0) {
				$result = $result . $ssec . "\"";
			}
		}
		
		return $result;
	}
	public static function getChartData($uid, $type = "seven") {
		switch ($type) {
			case "seven" :
				{
					return Mileage::getMileages ( $uid, "mileage_date" );
					break;
				}
			case "week" :
				{
					return Mileage::getMileages ( $uid, "mileage_week", 0, 5 );
					break;
				}
			case "month" :
				{
					$startDate = date ( "Y-01-01" );
					$endDate = date ( "Y-12-31" );
					return Mileage::getMileagesByDate ( $uid, $startDate, $endDate, "mileage_month" );
					break;
				}
			case "year" :
				{
					return Mileage::getMileages ( $uid, "mileage_year", 0, 4 );
					break;
				}
		}
	}
	public static function convertToChart($mileages, $type = "seven") {
		$xAxis = array ();
		$series = array ();
		switch ($type) {
			case "seven" :
				{
					foreach ( $mileages as $mileage ) {
						$date = date ( "m-d", strtotime ( $mileage->mileage_date ) );
						array_unshift ( $xAxis, $date );
						array_unshift ( $series, $mileage->mileage );
					}
					break;
				}
			case "week" :
				{
					foreach ( $mileages as $mileage ) {
						$month = explode ( "-", $mileage->mileage_week );
						$startDate = Util::getMonthFirstDay ( strtotime ( $month [0] . "-" . $month [1] . "-01" ) );
						if ($month [2] == 1) {
							$date = date ( "m-d", strtotime ( $startDate ) );
							$nextDate = date ( "m-d", strtotime ( "+6 days", strtotime ( $startDate ) ) );
						} elseif ($month [2] == 2) {
							$date = date ( "m-d", strtotime ( "+7 days", strtotime ( $startDate ) ) );
							$nextDate = date ( "m-d", strtotime ( "+13 days", strtotime ( $startDate ) ) );
						} elseif ($month [2] == 3) {
							$date = date ( "m-d", strtotime ( "+14 days", strtotime ( $startDate ) ) );
							$nextDate = date ( "m-d", strtotime ( "+20 days", strtotime ( $startDate ) ) );
						} elseif ($month [2] == 4) {
							$date = date ( "m-d", strtotime ( "+21 days", strtotime ( $startDate ) ) );
							$nextDate = date ( "m-d", strtotime ( "+27 days", strtotime ( $startDate ) ) );
						} elseif ($month [2] == 5) {
							$date = date ( "m-d", strtotime ( "+28 days", strtotime ( $startDate ) ) );
							$nextDate = date ( "m-d", strtotime ( strtotime ( $month [0] . "-" . $month [1] ) ) );
						}
						array_unshift ( $xAxis, $date );
						array_unshift ( $series, $mileage->mileage );
					}
					
					break;
				}
			case "month" :
				{
					foreach ( $mileages as $mileage ) {
						$date = date ( "m", strtotime ( $mileage->mileage_month ) );
						array_unshift ( $xAxis, $date );
						array_unshift ( $series, $mileage->mileage );
					}
					break;
				}
			case "year" :
				{
					foreach ( $mileages as $mileage ) {
						$date = date ( "Y", strtotime ( $mileage->mileage_year ) );
						array_unshift ( $xAxis, $date );
						array_unshift ( $series, $mileage->mileage );
					}
					break;
				}
		}
		
		return array (
				"xAxis" => json_encode ( $xAxis ),
				"series" => json_encode ( $series ) 
		);
	}
	public static function http_get($url) {
		$oCurl = curl_init ();
		if (stripos ( $url, "https://" ) !== FALSE) {
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
		}
		curl_setopt ( $oCurl, CURLOPT_URL, $url );
		curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec ( $oCurl );
		$aStatus = curl_getinfo ( $oCurl );
		curl_close ( $oCurl );
		if (intval ( $aStatus ["http_code"] ) == 200) {
			return $sContent;
		} else {
			return false;
		}
	}
	
	public static function http_post($url,$params,$type="text") {
		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		if($type=="json"){
			if(is_array($params)){
				$params = json_encode($params);
			}
			curl_setopt($oCurl, CURLOPT_POSTFIELDS,$params);
			curl_setopt($oCurl, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json;charset=utf-8',
					'Content-Length: ' . strlen($params))
			);
		}else{
			curl_setopt($oCurl, CURLOPT_POSTFIELDS,$params);
		}
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}
	
	public static function sendRegEmail($register) {
		// 发送邮件
		$EmailBody = EmailTemplate::findOne ( [ 
				"email_code" => "REGISTRATIONSUCCESS" 
		] );
		$body = $EmailBody->email_body;
		$body = str_replace ( "{nick_name}", $register->passport_name, $body );
		$body = str_replace ( "{act_name}", $register->act->act_name, $body );
		$body = str_replace ( "{course_name}", $register->course->course_name, $body );
		$body = str_replace ( "{registerid}", $register->registerid, $body );
		$body = str_replace ( "{register_fee}", $register->order->actual_payment, $body );
		$body = str_replace ( "{order_time}", $register->order->update_time, $body );
		$param_email = [ ];
		$param_email ["email"] = $register->user_email;
		$param_email ["body"] = $body;
		$param_email ["title"] = $EmailBody->email_title;
		$Mail = new MailManage ();
		try {
			$Mail->SendRegisterSuccEmail ( $param_email );
		} catch ( Exception $e ) {
		}
	}
	public static function getStringFromError($errors) {
		$str = "";
		foreach ( array_keys ( $errors ) as $error ) {
			$str = $str . "," . $error . ":" . $errors [$error];
		}
		if (strlen ( $str ) != 0) {
			$str = substr ( $str, 1 );
		}
		return $str;
	}
	public static function getFileUploadJs() {
		return '<script src="/jq_fileupload/jquery.ui.widget.js"></script>
				<script src="/jq_fileupload/load-image.all.min.js"></script>
				<script src="/jq_fileupload/canvas-to-blob.min.js"></script>
				<script src="/jq_fileupload/jquery.iframe-transport.js"></script>
				<script src="/jq_fileupload/jquery.fileupload.js"></script>
				<script src="/jq_fileupload/jquery.fileupload-process.js"></script>
				<script src="/jq_fileupload/jquery.fileupload-image.js"></script>
				<script src="/jq_fileupload/jquery.fileupload-validate.js"></script>
				<script id="image-pre-template" type="text/x-handlebars-template"> 
					<ul data-am-widget="gallery" class="am-gallery am-avg-sm-4
  				am-avg-md-6 am-avg-lg-8 am-gallery-default" data-am-gallery="{ pureview: 1}">
					{{#each images}} 
						<li>
    						<div class="am-gallery-item">
        							<img src="{{pre}}" data-rel="http://xiaoi.b0.upaiyun.com/{{image}}" style="width:50px;height:50px"/>
   							 </div>
  						</li>
  					{{/each}}
					</ul>
				</script>
				';
	}
	public static function getDateTimePickerJsCss() {
		return ' <script src="/datetime/mobiscroll_002.js" type="text/javascript"></script>
 				<script src="/datetime/mobiscroll_004.js" type="text/javascript"></script>
				<link href="/datetime/mobiscroll_002.css" rel="stylesheet" type="text/css">
				<link href="/datetime/mobiscroll.css" rel="stylesheet" type="text/css">
				<script src="/datetime/mobiscroll.js" type="text/javascript"></script>
				<script src="/datetime/mobiscroll_003.js" type="text/javascript"></script>
				<script src="/datetime/mobiscroll_005.js" type="text/javascript"></script>
				<link href="/datetime/mobiscroll_003.css" rel="stylesheet" type="text/css">';
	}
	public static function getMainJsCss() {
		return ' <link href="/css/paotuanzhuce/app.css" type="text/css" rel="stylesheet"/>
 				<link rel="stylesheet" href="/css/amazeui.min.css"/>
 				<link href="/css/wechat.css?' . time () . '" type="text/css" rel="stylesheet"/>
 				<link href="/css/ionicons/css/ionicons.min.css" type="text/css"	rel="stylesheet" />
				<script type="text/javascript" src="/js/jquery.min.js"></script>
 				<script src="/js/amazeui.min.js"></script>
				<script src="/js/handlebars.min.js"></script>
				<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
				<script src="/js/qrcode.js"></script>';
	}
	public static function getMainJsCssWithAppFramework() {
		return '<link rel="stylesheet" type="text/css" href="/appframe/af.ui.css"> 
				<link href="/css/paotuanzhuce/app.css" type="text/css" rel="stylesheet"/>
 				<link rel="stylesheet" href="/css/amazeui.min.css"/>
 				<link href="/css/wechat.css?' . time () . '" type="text/css" rel="stylesheet"/>
 				<link href="/css/ionicons/css/ionicons.min.css" type="text/css"	rel="stylesheet" />
				<script type="text/javascript" src="/js/jquery.min.js"></script>
				<script type="text/javascript" charset="utf-8"	src="/appframe/fastclick.js"></script>
				<script type="text/javascript" charset="utf-8"	src="/appframe/appframework.ui.min.js"></script>
 				<script src="/js/amazeui.min.js"></script>
				<script src="/js/handlebars.min.js"></script>
				<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
				<script src="/js/qrcode.js"></script>';
	}
	public static function getWechatJs() {
		return '<script src="/js/wechat.js?' . time () . '"></script>';
	}
	
	
	public static function getHost(){
		$hostInfo = \Yii::$app->request->hostInfo;
		if($hostInfo=="http://wechat.paobuqu.com"){
			return "http://www.paobuqu.com/";
		}else{
			return "http://www.runningtogether.net/";
		}
	}
	
	public static function genCredit($rule,$club_id,$related_id,$uid,$rollback=null){
		if($rollback!=null){
			$url =self::getHost()."v3/credit/execrule?rule=$rule&club_id=$club_id&related_id=$related_id&uid=$uid&rollback=$rollback";
		}else{
			$url =self::getHost()."v3/credit/execrule?rule=$rule&club_id=$club_id&related_id=$related_id&uid=$uid";
		}
		return self::http_get($url);
	}
	public static function sendClubCreateMsg($params){
		$url = Util::getHost()."v3/wechat/smsinfo";
		$r=Util::http_post($url, $params);
		if($r){
			$r = json_decode($r);
			if($r==0){
				return true;
			}else{
				return false;
			}
		}
	}
	
	   public static function getUserFace($img){
	   	  if($img){
	   	  	if(stristr($img, "http://wx.qlogo.cn")!==false){
	   	  	   return substr($img, 0,strripos($img,"/"))."/96";
	   	  	}else{
	   	  		  return $img."!mid";
	   	  	}
	   	  }
	   }
	   
	   public static  function isEnd($end_time){
	   	if (strtotime ($end_time ) < time ()) {
	   		return true;
	   	}
	   	return false;
	   }
    
    }
?>