<?php
namespace paotuan_wechat\controllers;
use common\component\WechatSDK\Wechat;
use common\models\UserOauth;
use Yii;
use yii\web\Controller;
use common\component\CustomHelper;
use paotuan_wechat\models\ClubMemberPayment;
use paotuan_wechat\models\ClubMember;
use common\component\Jssdk;
use common\component\BindComponent;
use common\component\MessageApi;
use common\models\OrderMaster;
use common\models\ActStay;
use common\models\Register;
use paotuan_wechat\component\Util;
use paotuan_wechat\models\Club;
use paotuan_wechat\component\WxUtil;
use paotuan_wechat\models\ActivityUser;
use common\models\UserMaster;
use paotuan_wechat\models\Activity;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
include_once (dirname ( dirname ( __FILE__ ) ) . "/component/WxPayPubHelper/log_.php");
include_once (dirname ( dirname ( __FILE__ ) ) . "/component/WxPayPubHelper/WxPayPubHelper.php");
class WxpayController extends Controller {
	public $enableCsrfValidation = false;
	public function beforeAction($action) {
		$session = \Yii::$app->session;
		$session->open ();
		$options = \Yii::$app->params ['weixin_config'] ['wechart'];
		return parent::beforeAction ( $action );
	}
	
	//比赛支付回调
	public function actionRegisternotify() {
		$notify = new \Notify_pub ();
		// 存储微信的回调
		$xml = $GLOBALS ['HTTP_RAW_POST_DATA'];
		$notify->saveData ( $xml );
		if ($notify->checkSign () == FALSE) {
			$notify->setReturnParameter ( "return_code", "FAIL" ); // 返回状态码
			$notify->setReturnParameter ( "return_msg", "签名失败" ); // 返回信息
		} else {
			$notify->setReturnParameter ( "return_code", "SUCCESS" ); // 设置返回码
		}
		$returnXml = $notify->returnXml ();
		echo $returnXml;
		if ($notify->checkSign () == TRUE) {
			if ($notify->data ["return_code"] == "FAIL") {
				// 此处应该更新一下订单状态，商户自行增删操作
				// $log_->log_result ( $log_name, "【通信出错】:\n" . $xml . "\n" );
			} elseif ($notify->data ["result_code"] == "FAIL") {
				// 此处应该更新一下订单状态，商户自行增删操作
				// $log_->log_result ( $log_name, "【业务出错】:\n" . $xml . "\n" );
			} else {
				$third_party_trade_no = $notify->data ["transaction_id"];
				$openid = $notify->data ["openid"];
				$out_trade_no = $notify->data ["out_trade_no"];
				$trade_time = date ( "Y-m-d H:i:s", strtotime ( $notify->data ["time_end"] ) );
				$fee = $notify->data ["total_fee"] / 100;
				
				$trans = \Yii::$app->db->beginTransaction ();
				$attach = $notify->data ["attach"];
				$om = OrderMaster::findOne ($attach);
				$register = Register::findOne ( [ 
						"orderid" => $om->orderid 
				] );
				if ($om !== null) {
					try {
						$om->order_status = OrderMaster::STATUS_NORMAL;
						$om->trade_no = $out_trade_no;
						if($register){
							$register->payment_status = Register::PAY_STATUS_NORMAL;
							$register->register_status = Register::STATUS_REGISTER;
							$register->payment_time = $trade_time;
							$register->update ();
						}
						$om->payment_no = $third_party_trade_no;
						$om->update_time = $trade_time;
						$om->payment_type = "微信支付wechat";
						if ($om->save ()) {
							ActStay::updateAll ( [ "payment_status"=>ActStay::PAY_STATUS_DONE
							],["orderid" => $om->orderid]);
							$trans->commit ();
							try {
								if($register){
									Util::sendRegEmail ( $register );
									$params = array();
									$params["openid"]=$openid;
									$params["actid"]=$register->actid;
									$params["channelid"]=$register->channelid;
									$params["first"]="恭喜您,活动报名成功";
									$params["act_name"]=$register->act->act_name;
									$params["act_desc"]=$register->course->course_name;
									$params["act_day"]=$register->act->act_day;
									$params["address"]=$register->act->act_addr;
									$params["cell"]="4008200124";
									$params["remark"]="点击详情，查看报名信息";
									Util::http_post("http://www.paobuqu.com/v3/wechat/register", $params);
									
								}
								MessageApi::send ($om->user->user_cell, "支付已成功，收到您的微信支付" . $fee . "元." );
							} catch (Exception $e) {
								
							}
						
						} else {
							throw new \Exception();
						}
					} catch (\Exception $e) {
						$trans->rollBack();
					}
				}
			}
		}
	}
	
	//活动支付回调
	public function actionActregisternotify() {
		$notify = new \Notify_pub ();
		// 存储微信的回调
		$xml = $GLOBALS ['HTTP_RAW_POST_DATA'];
		$notify->saveData ( $xml );

		// 检查签名
		if ($notify->checkSign () == FALSE) {
			$notify->setReturnParameter ( "return_code", "FAIL" ); // 返回状态码
			$notify->setReturnParameter ( "return_msg", "签名失败" ); // 返回信息
			// 签名失败，直接返回
			echo $notify->returnXml ();
			return;
		}

		if ($notify->data ["return_code"] == "FAIL") {
			// 此处应该更新一下订单状态，商户自行增删操作
			// $log_->log_result ( $log_name, "【通信出错】:\n" . $xml . "\n" );
			return;
		} elseif ($notify->data ["result_code"] == "FAIL") {
			// 此处应该更新一下订单状态，商户自行增删操作
			// $log_->log_result ( $log_name, "【业务出错】:\n" . $xml . "\n" );
			return;
		}

		// 微信支付成功，更新业务数据表
		$third_party_trade_no = $notify->data ["transaction_id"];
		$openid = $notify->data ["openid"];
		$out_trade_no = $notify->data ["out_trade_no"];
		$trade_time = date ( "Y-m-d H:i:s", strtotime ( $notify->data ["time_end"] ) );
		$fee = $notify->data ["total_fee"] / 100;
		$attach = $notify->data ["attach"];

		$trans = \Yii::$app->db->beginTransaction ();
		try {
			$developer_cell_phone = "18964096269";

			// 查找订单数据
			$om = OrderMaster::findOne ($attach);
			$act_user = ActivityUser::findOne(["order_id"=>$om->orderid]);
			if ($om == null || $act_user == null) {
				// 没有找到订单信息
				\Yii::error('[activity payment update fail]');
				\Yii::error($om);
				\Yii::error($act_user);
				MessageApi::send ($developer_cell_phone, "微信支付回调，没有找到订单信息" . $attach);
				return;
			}

			$act = Activity::findOne($act_user->act_id);
			if ($act == null) {
				// 没有找到活动信息
				\Yii::error('[activity update fail]');
				\Yii::error($act);
				MessageApi::send ($developer_cell_phone, "微信支付回调，没有找到活动信息" . $act_user->act_id);
				return;
			}

			$om->order_status = OrderMaster::STATUS_NORMAL;
			$om->trade_no = $out_trade_no;
			$act_user->status =ActivityUser::STATUS_NORMAL;
			$act_user->isreg = 1;
			$act_user->reg_time = $trade_time;
			$om->payment_no = $third_party_trade_no;
			$om->update_time = $trade_time;
			$om->actual_payment = $fee;
			$om->payment_type = "微信支付wechat";
			$act->reg_num++;

			if ($om->save() && $act_user->save() && $act->save()) {
				$member = ClubMember::getNormalMember($act->club->clubid, $act_user->uid);
				if($member){
					$r=Util::genCredit(Util::CREDIT_EVENT_ACT_REG, $act->club->clubid, $act->act_id, $act_user->uid);
				}
				$trans->commit ();
				// 数据更新成功
				$notify->setReturnParameter ( "return_code", "SUCCESS" ); // 设置返回码
				echo $notify->returnXml ();
				MessageApi::send ( $act_user->user->user_cell, "支付已成功，收到您的微信支付" . $fee . "元." );
			} else {
				\Yii::error('[payment info update fail]');
				\Yii::error($om);
				\Yii::error($act_user);
				\Yii::error($act);
				MessageApi::send ($developer_cell_phone, "微信支付回调，没有保存成功" . $act_user->act_id);
				return;
			}
		} catch (Exception $e) {
			// 更新数据提交失败
			\Yii::warning($e);
			MessageApi::send ($developer_cell_phone, "微信支付回调，更新数据提交失败" . $notify->data ["attach"]);
			$trans->rollBack ();
		}
	}

	public function actionCheckpay() {
		$orderid = \Yii::$app->request->post ( "orderid" );
		$result = array (
				"status" => 0 
		);
		$order = null;
		if (\Yii::$app->request->post ( "paytype" ) == "club_member_fee") {
			$order = ClubMemberPayment::findOne ( [ 
					"id" => $orderid,
					"trade_status" => ClubMemberPayment::PAY_STATUS_FINISHED 
			] );
		} else {
			$order = OrderMaster::findOne ( [ 
					"orderid" => $orderid,
					"order_status" => OrderMaster::STATUS_NORMAL 
			] );
		}
		if ($order) {
			$result ["status"] = 1;
		} else {
			$result ["status"] = 0;
		}
		CustomHelper::RetrunJson ( $result );
	}
	public function actionNotify() {
		$notify = new \Notify_pub ();
		// 存储微信的回调
		$xml = $GLOBALS ['HTTP_RAW_POST_DATA'];
		$notify->saveData ( $xml );
		/*
		 * $payment = new ClubMemberPayment();
		 * $payment->club_id = 1;
		 * $payment->trade_no= CustomHelper::CreateOrderID(time());
		 * $payment->third_party_trade_no=$notify->checkSign ()?"true":"false";
		 * $payment->uid=245;
		 * $payment->year=date("Y");
		 * $payment->payment_fee = 1;
		 * $payment->pay_partner="wxpay";
		 * $payment->save();
		 */
		// 验证签名，并回应微信。
		// 对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
		// 微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
		// 尽可能提高通知的成功率，但微信不保证通知最终能成功。
		if ($notify->checkSign () == FALSE) {
			$notify->setReturnParameter ( "return_code", "FAIL" ); // 返回状态码
			$notify->setReturnParameter ( "return_msg", "签名失败" ); // 返回信息
		} else {
			$notify->setReturnParameter ( "return_code", "SUCCESS" ); // 设置返回码
		}
		$returnXml = $notify->returnXml ();
		echo $returnXml;
		
		if ($notify->checkSign () == TRUE) {
			if ($notify->data ["return_code"] == "FAIL") {
				// 此处应该更新一下订单状态，商户自行增删操作
				// $log_->log_result ( $log_name, "【通信出错】:\n" . $xml . "\n" );
			} elseif ($notify->data ["result_code"] == "FAIL") {
				// 此处应该更新一下订单状态，商户自行增删操作
				// $log_->log_result ( $log_name, "【业务出错】:\n" . $xml . "\n" );
			} else {
				$openid = $notify->data ["openid"];
				$third_party_trade_no = $notify->data ["transaction_id"];
				$out_trade_no = $notify->data ["out_trade_no"];
				$trade_time = date ( "Y-m-d H:i:s", strtotime ( $notify->data ["time_end"] ) );
				$attach=explode("_", $notify->data ["attach"]);
				$payment = ClubMemberPayment::findOne ($attach[1]);
				if ($payment->trade_status == 1) {
					$payment->trade_status = 2;
					$payment->payment_fee = $notify->data ["total_fee"] / 100;
					$payment->trade_time = $trade_time;
					$payment->third_party_trade_no = $third_party_trade_no;
					$payment->year = $attach[0];
					$payment->trade_no =$out_trade_no;
					$payment->save ();
					$member = ClubMember::findOne ( [ 
							"uid" => $payment->uid,
							"club_id" => $payment->club_id 
					] );
					$member->member_status = ClubMember::STATUS_NORMAL;
					switch ($payment->year) {
						case '3' :
							$member->expiration_date = date ( "Y-m-d H:i:s", strtotime ( "1 month", strtotime ( $notify->data ["time_end"] ) ) );
							break;
						case '2' :
							$member->expiration_date = date ( "Y-m-d H:i:s", strtotime ( "3 month", strtotime ( $notify->data ["time_end"] ) ) );
							break;
						case '1' :
							$member->expiration_date = date ( "Y-m-d H:i:s", strtotime ( "1 year", strtotime ( $notify->data ["time_end"] ) ) );
							break;
						case '4' :
							$member->expiration_date = null;
							break;
					}
					if($member->save ()){
						Util::genCredit(Util::CREDIT_EVENT_CLUB_JOIN, $member->club_id, 0, $member->uid);
						WxUtil::sendTemplateClubMember(["openid"=>$openid,"id"=>$member->id,"club_name"=>$member->club->club_name,"club_eng"=>$member->club->club_eng,"nick_name"=>$member->user->nick_name]);
					}
					if (CustomHelper::isCell ( trim ( $member->cell ) )) {
						MessageApi::send ( $member->cell, "支付已成功，收到您的微信支付" . $payment->payment_fee . "元." );
					}
					
				}
			}
		}
	}
	public function actionOrder() {
		$orderid = \Yii::$app->request->post ( "orderid" );
		$openid = \Yii::$app->request->post ( "openid" );
		$trade_type = \Yii::$app->request->post ( "trade_type" );
		$body;
		$total_fee;
		$notify_url;
		$attach = "";
		$trade_no;
		$result = array (
				"status" => 0 
		);
		$time_expire = null;
		if (\Yii::$app->request->post ( "paytype" ) == "club_member_fee") {
			$payment = ClubMemberPayment::findOne ( [ 
					"id" => $orderid,
					"trade_status" => ClubMemberPayment::PAY_STATUS_TODO 
			] );
			
			if ($payment) {
				//$payment->trade_no = CustomHelper::CreateOrderID ( time () . $payment->id );
				//$payment->save ();
				$trade_no = CustomHelper::CreateOrderID ( time () . $payment->id );
				$club = Club::findOne ( $payment->club_id );
				$total_fee = $club->member_fee * 100;
				$body = $club->club_name . "\n会费 ￥" . $club->member_fee;
				$attach = $club->fee_circle.'_'.$payment->id;
				$notify_url = \Yii::$app->request->hostInfo . "/wxpay/notify";
			} else {
				$result = array (
						"status" => 1 
				);
				CustomHelper::RetrunJson ( $result );
			}
		} else {
			$om = OrderMaster::findOne ( [ 
					"orderid" => $orderid 
			] );
			if(!$om){
				CustomHelper::RetrunJson ( $result );
			}
			if ($om->order_status == OrderMaster::STATUS_WAIT_PAY) {
				//$om->trade_no = date ( "YmdHis" ).$orderid;
				$om->save ();
				$attach = $om->orderid;
				$trade_no =  date ( "YmdHis" ).$orderid;
				$time_expire = date ( "YmdHis", strtotime ( $om->expire_time ) );
				$total_fee = $om->actual_payment * 100;
				if(\Yii::$app->request->post ( "paytype" ) == "club_act_fee"){
					$body = $om->order_title;
					$notify_url = \Yii::$app->request->hostInfo . "/wxpay/actregisternotify";
				}else{
					$details = $om->detail;
					foreach ( $details as $detail ) {
						if($detail->item_price>0){
							$body = $body . "\n" . $detail->item_title . " ￥" . $detail->item_price;
						}
					}
					$body = $om->order_title . $body;
				    $notify_url = \Yii::$app->request->hostInfo . "/wxpay/registernotify";
				}
			} elseif ($om->order_status == OrderMaster::STATUS_NORMAL) {
				$result = array (
						"status" => 1 
				);
				CustomHelper::RetrunJson ( $result );
			} elseif ($om->order_status == OrderMaster::STATUS_CANCEL || $om->order_status == OrderMaster::STATUS_DELETE) {
				$result = array (
						"status" => 2 
				);
				CustomHelper::RetrunJson ( $result );
			}
		}
		$unifiedOrder = new \UnifiedOrder_pub ();
		$unifiedOrder->setParameter ( "openid", $openid );
		$body=mb_substr($body,0,57,"utf-8")."...";    //body允许string(127)
		$unifiedOrder->setParameter ("body", "$body" ); // 商品描述
		                                                 // 自定义订单号，此处仅作举例
		$timeStamp = time ();
		// $out_trade_no = \WxPayConf_pub::APPID."$timeStamp";
		$unifiedOrder->setParameter ( "out_trade_no", "$trade_no" ); // 商户订单号
		$unifiedOrder->setParameter ( "total_fee", "$total_fee" ); // 总金额
		$unifiedOrder->setParameter ( "notify_url", $notify_url ); // 通知地址
		$unifiedOrder->setParameter ( "trade_type", strtoupper ( $trade_type ) ); // 交易类型
		                                                                          // 非必填参数，商户可根据实际情况选填
		                                                                          // $unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
		if (! empty ( $attach )) { // $unifiedOrder->setParameter("device_info","XXXX");//设备号
			$unifiedOrder->setParameter ( "attach", "$attach" ); // 附加数据
		}
		// $unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
		if ($time_expire) {
			$unifiedOrder->setParameter ( "time_expire", $time_expire ); // 交易结束时间
		}
		
		// $unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
		// $unifiedOrder->setParameter("openid","XXXX");//用户标识
		// $unifiedOrder->setParameter("product_id","XXXX");//商品ID
		if (strtolower ( $trade_type ) == "native") {
			$unifiedOrderResult = $unifiedOrder->getResult ();
			
			// 商户根据实际情况设置相应的处理流程
			if ($unifiedOrderResult ["return_code"] == "FAIL") {
				// 商户自行增加处理流程
				echo "通信出错：" . $unifiedOrderResult ['return_msg'] . "<br>";
			} elseif ($unifiedOrderResult ["result_code"] == "FAIL") {
				// 商户自行增加处理流程
				echo "错误代码：" . $unifiedOrderResult ['err_code'] . "<br>";
				echo "错误代码描述：" . $unifiedOrderResult ['err_code_des'] . "<br>";
			} elseif ($unifiedOrderResult ["code_url"] != NULL) {
				// 从统一支付接口获取到code_url
				$code_url = $unifiedOrderResult ["code_url"];
				$result ["code_url"] = $code_url;
				// ......
			}
		} else {
			$prepay_id = $unifiedOrder->getPrepayId ();
			// =========步骤3：使用jsapi调起支付============
			$jsApi = new \JsApi_pub ();
			$jsApi->setPrepayId ( $prepay_id );
			$jsApiParameters = $jsApi->getParameters ();
			$result ["jsApiParameters"] = $jsApiParameters;
		}
		CustomHelper::RetrunJson ( $result );
	}
	public function actionIndex() {
		$openid = Yii::$app->request->post ( "openid" );
		$goodDesc = Yii::$app->request->post ( "goodDesc" );
		$trade_no = Yii::$app->request->post ( "trade_no" );
		$total_fee = Yii::$app->request->post ( "total_fee" );
		$attach = Yii::$app->request->post ( "attach" );
		$notify_url = Yii::$app->request->post ( "notify_url" );
		$orderid = \Yii::$app->request->post ( "orderid" );
		
		$options = \Yii::$app->params ['weixin_config'] ['wechart'];
		$jssdk = new Jssdk ( $options ["appid"], $options ["appsecret"] );
		$signPackage = $jssdk->getSignPackage ();
		// $jssdk = new Jssdk (\WxPayConf_pub::APPID, \WxPayConf_pub::APPSECRET );
		/**
		 * $jsApi = new \JsApi_pub ();
		 *
		 * // =========步骤1：网页授权获取用户openid============
		 * // 通过code获得openid
		 * // if (!isset($_GET['code']))
		 * // {
		 * // //触发微信返回code码
		 * // $url = $jsApi->createOauthUrlForCode(rawurlencode(\WxPayConf_pub::JS_API_CALL_URL));
		 *
		 * // \Yii::$app->response->redirect($url);
		 * // }else
		 * // {
		 * // //获取code码，以获取openid
		 * // $code = $_GET['code'];
		 * // $jsApi->setCode($code);
		 * // $openid = $jsApi->getOpenId();
		 * // }
		 *
		 * // =========步骤2：使用统一支付接口，获取prepay_id============
		 * // 使用统一支付接口
		 * $unifiedOrder = new \UnifiedOrder_pub ();
		 *
		 * // 设置统一支付接口参数
		 * // 设置必填参数
		 * // appid已填,商户无需重复填写
		 * // mch_id已填,商户无需重复填写
		 * // noncestr已填,商户无需重复填写
		 * // spbill_create_ip已填,商户无需重复填写
		 * // sign已填,商户无需重复填写
		 * $unifiedOrder->setParameter ( "openid", $openid ); // 商品描述
		 * $unifiedOrder->setParameter ( "body", $goodDesc . "" ); // 商品描述
		 * // 自定义订单号，此处仅作举例
		 * $timeStamp = time ();
		 * // $out_trade_no = \WxPayConf_pub::APPID."$timeStamp";
		 * $unifiedOrder->setParameter ( "out_trade_no", "$trade_no" ); // 商户订单号
		 * $unifiedOrder->setParameter ( "total_fee", "$total_fee" ); // 总金额
		 * $unifiedOrder->setParameter ( "notify_url", $notify_url ); // 通知地址
		 * $unifiedOrder->setParameter ( "trade_type", "JSAPI" ); // 交易类型
		 * // 非必填参数，商户可根据实际情况选填
		 * // $unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
		 * // $unifiedOrder->setParameter("device_info","XXXX");//设备号
		 * $unifiedOrder->setParameter ( "attach", "$attach" ); // 附加数据
		 * // $unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
		 * // $unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
		 * // $unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
		 * // $unifiedOrder->setParameter("openid","XXXX");//用户标识
		 * // $unifiedOrder->setParameter("product_id","XXXX");//商品ID
		 *
		 * $prepay_id = $unifiedOrder->getPrepayId ();
		 * // =========步骤3：使用jsapi调起支付============
		 * $jsApi->setPrepayId ( $prepay_id );
		 *
		 * $jsApiParameters = $jsApi->getParameters ();
		 *
		 * if(Yii::$app->request->post("paytype")=="club_member_fee"){
		 * $trade_no = "club_$trade_no";
		 * }else{
		 * $trade_no = "reg_$trade_no";
		 * }
		 * $nativeLink =new \NativeLink_pub();
		 * $nativeLink->setParameter("product_id","$trade_no");//商品id
		 * //获取链接
		 * $product_url = $nativeLink->getUrl();
		 * //使用短链接转换接口
		 * $shortUrl = new \ShortUrl_pub();
		 * //设置必填参数
		 * //appid已填,商户无需重复填写
		 * //mch_id已填,商户无需重复填写
		 * //noncestr已填,商户无需重复填写
		 * //sign已填,商户无需重复填写
		 * $shortUrl->setParameter("long_url","$product_url");//URL链接
		 * //获取短链接
		 * $codeUrl = $shortUrl->getShortUrl();
		 * // CustomHelper::RetrunJson ( $jsApiParameters );
		 */
		return $this->renderPartial ( "index", [ 
				"signPackage" => $jssdk->getSignPackage (),
				"params" => Yii::$app->request 
		] );
	}
	public function actionNativepay() {
		$orderid = \Yii::$app->request->post ( "orderid" );
		$result = array (
				"status" => 0 
		);
		if (\Yii::$app->request->post ( "paytype" ) == "club_member_fee") {
			$payment = ClubMemberPayment::findOne ( [ 
					"id" => $orderid,
					"trade_status" => ClubMemberPayment::PAY_STATUS_TODO 
			] );
			if (! $payment) {
				$result = array (
						"status" => 1 
				);
				CustomHelper::RetrunJson ( $result );
			}
		} else {
			$om = OrderMaster::findOne ( [ 
					"orderid" => $orderid,
					"order_status" => OrderMaster::STATUS_WAIT_PAY 
			] );
			if (! $om) {
				$result = array (
						"status" => 1 
				);
				CustomHelper::RetrunJson ( $result );
			}
		}
		$unifiedOrder = new \UnifiedOrder_pub ();
		
		// 设置统一支付接口参数
		// 设置必填参数
		// appid已填,商户无需重复填写
		// mch_id已填,商户无需重复填写
		// noncestr已填,商户无需重复填写
		// spbill_create_ip已填,商户无需重复填写
		// sign已填,商户无需重复填写
		$unifiedOrder->setParameter ( "body", "贡献一分钱" ); // 商品描述
		                                                 // 自定义订单号，此处仅作举例
		$timeStamp = time ();
		$out_trade_no = WxPayConf_pub::APPID . "$timeStamp";
		$unifiedOrder->setParameter ( "out_trade_no", "$out_trade_no" ); // 商户订单号
		$unifiedOrder->setParameter ( "total_fee", "1" ); // 总金额
		$unifiedOrder->setParameter ( "notify_url", WxPayConf_pub::NOTIFY_URL ); // 通知地址
		$unifiedOrder->setParameter ( "trade_type", "NATIVE" ); // 交易类型
		                                                        // 非必填参数，商户可根据实际情况选填
		                                                        // $unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
		                                                        // $unifiedOrder->setParameter("device_info","XXXX");//设备号
		                                                        // $unifiedOrder->setParameter("attach","XXXX");//附加数据
		                                                        // $unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
		                                                        // $unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
		                                                        // $unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
		                                                        // $unifiedOrder->setParameter("openid","XXXX");//用户标识
		                                                        // $unifiedOrder->setParameter("product_id","XXXX");//商品ID
		                                                        
		// 获取统一支付接口结果
		$unifiedOrderResult = $unifiedOrder->getResult ();
		
		// 商户根据实际情况设置相应的处理流程
		if ($unifiedOrderResult ["return_code"] == "FAIL") {
			// 商户自行增加处理流程
			echo "通信出错：" . $unifiedOrderResult ['return_msg'] . "<br>";
		} elseif ($unifiedOrderResult ["result_code"] == "FAIL") {
			// 商户自行增加处理流程
			echo "错误代码：" . $unifiedOrderResult ['err_code'] . "<br>";
			echo "错误代码描述：" . $unifiedOrderResult ['err_code_des'] . "<br>";
		} elseif ($unifiedOrderResult ["code_url"] != NULL) {
			// 从统一支付接口获取到code_url
			$code_url = $unifiedOrderResult ["code_url"];
			// 商户自行增加处理流程
			// ......
		}
		$result ["product_url"] = $codeUrl;
		CustomHelper::RetrunJson ( $result );
	}
	public function actionQuery() {
		$transaction_id = \Yii::$app->request->get ( "transaction_id", "" );
		if ($transaction_id) {
			// 使用订单查询接口
			$orderQuery = new \OrderQuery_pub ();
			// 设置必填参数
			// appid已填,商户无需重复填写
			// mch_id已填,商户无需重复填写
			// noncestr已填,商户无需重复填写
			// sign已填,商户无需重复填写
			//$orderQuery->setParameter ( "out_trade_no", time () ); // 商户订单号
			                                                  // 非必填参数，商户可根据实际情况选填
			                                                  // $orderQuery->setParameter("sub_mch_id","XXXX");//子商户号
			$orderQuery->setParameter ( "transaction_id", "$transaction_id" ); // 微信订单号
			                                                               
			// 获取订单查询结果
			$orderQueryResult = $orderQuery->getResult ();
			
			// 商户根据实际情况设置相应的处理流程,此处仅作举例
			if ($orderQueryResult ["return_code"] == "FAIL") {
				echo "通信出错：" . $orderQueryResult ['return_msg'] . "<br>";
			} elseif ($orderQueryResult ["result_code"] == "FAIL") {
				echo "错误代码：" . $orderQueryResult ['err_code'] . "<br>";
				echo "错误代码描述：" . $orderQueryResult ['err_code_des'] . "<br>";
			} else {
				echo "交易状态：" . $orderQueryResult ['trade_state'] . "<br>";
				echo "设备号：" . $orderQueryResult ['device_info'] . "<br>";
				echo "用户标识：" . $orderQueryResult ['openid'] . "<br>";
				echo "是否关注公众账号：" . $orderQueryResult ['is_subscribe'] . "<br>";
				echo "交易类型：" . $orderQueryResult ['trade_type'] . "<br>";
				echo "付款银行：" . $orderQueryResult ['bank_type'] . "<br>";
				echo "总金额：" . $orderQueryResult ['total_fee'] . "<br>";
				echo "现金券金额：" . $orderQueryResult ['coupon_fee'] . "<br>";
				echo "货币种类：" . $orderQueryResult ['fee_type'] . "<br>";
				echo "微信支付订单号：" . $orderQueryResult ['transaction_id'] . "<br>";
				echo "商户订单号：" . $orderQueryResult ['out_trade_no'] . "<br>";
				echo "商家数据包：" . $orderQueryResult ['attach'] . "<br>";
				echo "支付完成时间：" . $orderQueryResult ['time_end'] . "<br>";
			}
		}
	}
	public function actionNativecall() {
		// 使用native通知接口
		$nativeCall = new \NativeCall_pub ();
		
		// 接收微信请求
		$xml = $GLOBALS ['HTTP_RAW_POST_DATA'];
		$nativeCall->saveData ( $xml );
		
		if (false) {
			$nativeCall->setReturnParameter ( "return_code", "FAIL" ); // 返回状态码
			$nativeCall->setReturnParameter ( "return_msg", "签名失败" ); // 返回信息
		} else {
			// 提取product_id
			$product_id = $nativeCall->getProductId ();
			// 使用统一支付接口
			$unifiedOrder = new \UnifiedOrder_pub ();
			$type = explode ( "_", $product_id )[0];
			$orderid = explode ( "_", $product_id )[1];
			$order = null;
			$body;
			$total_fee;
			$notify_url;
			$attach = "";
			$trade_no;
			if ($type == "club") {
				$payment = ClubMemberPayment::findOne ( [ 
						"id" => $orderid,
						"trade_status" => ClubMemberPayment::PAY_STATUS_TODO 
				] );
				$order = $payment;
				if ($payment) {
					$payment->trade_no = CustomHelper::CreateOrderID ( time () . $payment->id );
					$payment->save ();
					$trade_no = $payment->trade_no;
					$club = Club::findOne ( $payment->club_id );
					$body = $club->club_name . "会费";
					$total_fee = $club->member_fee * 100;
					$attach = $club->fee_circle;
					$notify_url = \Yii::$app->request->hostInfo . "/wxpay/notify";
				}
			} elseif ($type == "reg") {
				$om = OrderMaster::findOne ( [ 
						"orderid" => $orderid,
						"order_status" => OrderMaster::STATUS_WAIT_PAY 
				] );
				$order = $om;
				if ($om) {
					$om->trade_no = date ( "YmdHis" ) . $order->orderid;
					$om->save ();
					$trade_no = $om->trade_no;
					$total_fee = $om->amount * 100;
					$register = Register::findOne ( [ 
							"orderid" => $om->orderid 
					] );
					$attach = $om->orderid . "_" . $register->user_cell . "_" . $register->registerid;
					$details = $om->detail;
					foreach ( $details as $detail ) {
						$body = $body . "," . $detail->item_title;
					}
					$body = substr ( $body, 1 );
					$notify_url = \Yii::$app->request->hostInfo . "/wxpay/registernotify";
				}
			}
			if ($order !== null) {
				
				$unifiedOrder->setParameter ( "body", "test" ); // 商品描述
				                                                // 自定义订单号，此处仅作举例
				$timeStamp = time ();
				$unifiedOrder->setParameter ( "out_trade_no", "$trade_no" ); // 商户订单号 $unifiedOrder->setParameter("product_id","$product_id");//商品ID
				$unifiedOrder->setParameter ( "total_fee", $total_fee ); // 总金额
				$unifiedOrder->setParameter ( "notify_url", $notify_url ); // 通知地址
				$unifiedOrder->setParameter ( "trade_type", "NATIVE" ); // 交易类型
				$unifiedOrder->setParameter ( "product_id", "$product_id" ); // 用户标识
				                                                             // 非必填参数，商户可根据实际情况选填
				                                                             // $unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
				                                                             // $unifiedOrder->setParameter("device_info","XXXX");//设备号
				                                                             // $unifiedOrder->setParameter("attach","XXXX");//附加数据
				                                                             // $unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
				                                                             // $unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
				                                                             // $unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
				                                                             // $unifiedOrder->setParameter("openid","XXXX");//用户标识
				                                                             
				// 获取prepay_id
				$prepay_id = $unifiedOrder->getPrepayId ();
				// 设置返回码
				// 设置必填参数
				// appid已填,商户无需重复填写
				// mch_id已填,商户无需重复填写
				// noncestr已填,商户无需重复填写
				// sign已填,商户无需重复填写
				$nativeCall->setReturnParameter ( "return_code", "SUCCESS" ); // 返回状态码
				$nativeCall->setReturnParameter ( "result_code", "SUCCESS" ); // 业务结果
				$nativeCall->setReturnParameter ( "prepay_id", "$prepay_id" ); // 预支付ID
			} else {
				$nativeCall->setReturnParameter ( "return_code", "SUCCESS" ); // 返回状态码
				$nativeCall->setReturnParameter ( "result_code", "FAIL" ); // 业务结果
				$nativeCall->setReturnParameter ( "err_code_des", "此商品无效" ); // 业务结果
			}
		}
		
		// 将结果返回微信
		$returnXml = $nativeCall->returnXml ();
		echo $returnXml;
		\Yii::info ( print_r ( \Yii::$app->response, true ) );
		exit ();
	}
	private function getJsApiParameters($params) {
		try {
			$jsApi = new \JsApi_pub ();
			
			// =========步骤1：网页授权获取用户openid============
			// 通过code获得openid
			// if (!isset($_GET['code']))
			// {
			// //触发微信返回code码
			// $url = $jsApi->createOauthUrlForCode(rawurlencode(\WxPayConf_pub::JS_API_CALL_URL));
			
			// \Yii::$app->response->redirect($url);
			// }else
			// {
			// //获取code码，以获取openid
			// $code = $_GET['code'];
			// $jsApi->setCode($code);
			// $openid = $jsApi->getOpenId();
			// }
			
			// =========步骤2：使用统一支付接口，获取prepay_id============
			// 使用统一支付接口
			$unifiedOrder = new \UnifiedOrder_pub ();
			
			// 设置统一支付接口参数
			// 设置必填参数
			// appid已填,商户无需重复填写
			// mch_id已填,商户无需重复填写
			// noncestr已填,商户无需重复填写
			// spbill_create_ip已填,商户无需重复填写
			// sign已填,商户无需重复填写
			$unifiedOrder->setParameter ( "openid", $params ["openid"] ); // 商品描述
			$unifiedOrder->setParameter ( "body", $params ["body"] ); // 商品描述
			                                                          // 自定义订单号，此处仅作举例
			$timeStamp = time ();
			// $out_trade_no = \WxPayConf_pub::APPID."$timeStamp";
			$unifiedOrder->setParameter ( "out_trade_no", $params ["trade_no"] ); // 商户订单号
			$unifiedOrder->setParameter ( "total_fee", $params ["total_fee"] ); // 总金额
			$unifiedOrder->setParameter ( "notify_url", $params ["notify_url"] ); // 通知地址
			$unifiedOrder->setParameter ( "trade_type", "JSAPI" ); // 交易类型
			                                                       // 非必填参数，商户可根据实际情况选填
			                                                       // $unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
			                                                       // $unifiedOrder->setParameter("device_info","XXXX");//设备号
			$unifiedOrder->setParameter ( "attach", $params ["attach"] ); // 附加数据
			                                                              // $unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
			                                                              // $unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
			                                                              // $unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
			                                                              // $unifiedOrder->setParameter("openid","XXXX");//用户标识
			                                                              // $unifiedOrder->setParameter("product_id","XXXX");//商品ID
			
			$prepay_id = $unifiedOrder->getPrepayId ();
			// =========步骤3：使用jsapi调起支付============
			$jsApi->setPrepayId ( $prepay_id );
			
			return $jsApi->getParameters ();
		} catch ( \Exception $ex ) {
			return false;
		}
	}
	
// 	public function actionSendgroupredpack(){
// 		$cell = trim(\Yii::$app->request->get("cell",""));
// 		if($cell=="13482295614"){
// 			$para["wishing"]="满满的爱";
// 			$para["act_name"]="测试";
// 			$para["remark"]="抢红包啦";
// 			$para["total_amount"]="100";
// 			$para["re_openid"] ="oyL64uI7WE6HUm2RJ60ahVuZpbOc";
// 			echo WxUtil::sendGroupRedpack($para);
// 		}
// 	}
	
// 	public function actionSendredpack(){
// 		$cell = trim(\Yii::$app->request->get("cell",""));
// 		if($cell=="13482295614"){
// 			$para["wishing"]="满满的爱";
// 			$para["act_name"]="测试";
// 			$para["remark"]="抢红包啦";
// 			$para["total_amount"]=100;
// 			$para["re_openid"] ="oyL64uI7WE6HUm2RJ60ahVuZpbOc";
// 			echo WxUtil::sendRedpack($para);
// 		}
// 	}

    //跑团活动下的支付
	//如果 用户没有用微信注册过则要获取用户openid去支付
	private  $wechat;
	private  $user;
	public function actionActpay(){
		$order_id=\yii::$app->request->post('order_id');
		if($order_id){
			\Yii::$app->session["order_id"]=$order_id;
		}
		if( !\Yii::$app->session["order_id"] ){
			return;
		}
		$this->wechat = Wechat::getDefaultInstance();
		$this->checkWechatOauth('/wxpay/actpay');
		$user=$this->user;
		if( \Yii::$app->session["order_id"] ){
			$order_id=\Yii::$app->session["order_id"];
		}else{
			return;
		}
		//使用jsapi接口
		$order=OrderMaster::findOne(['trade_no'=>$order_id]);
		if($order){
				$openid=$user['openid'];
			    $attach=$order->orderid;
				$jsApi = new \JsApi_pub();
				//=========步骤2：使用统一支付接口，获取prepay_id============
				//使用统一支付接口
				$unifiedOrder = new \UnifiedOrder_pub();
				$unifiedOrder->setParameter("openid","$openid");//商品描述
				$unifiedOrder->setParameter("body","小艾爱跑");//商品描述
				//自定义订单号，此处仅作举例
			    if( \Yii::$app->session["order_id"] ){
					$out_trade_no=\Yii::$app->session["order_id"];
				}else{
					return;
				}
				$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
				$unifiedOrder->setParameter("total_fee",$order->amount*100);//总金额
			    $notify_url = \Yii::$app->request->hostInfo."/wxpay/actregisternotify";
				$unifiedOrder->setParameter("notify_url",$notify_url);//通知地址
				$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
			    $unifiedOrder->setParameter ( "attach", "$attach" );
				$prepay_id = $unifiedOrder->getPrepayId();
				//=========步骤3：使用jsapi调起支付============
				$jsApi->setPrepayId($prepay_id);
				$jsApiParameters = $jsApi->getParameters();
				unset(\Yii::$app->session["order_id"]);
				$act_id=ActivityUser::findOne(['order_id'=>$order->orderid])->act_id;
				return $this->renderPartial ( "actpay", [
						"signPackage" => $jsApiParameters,
						"act_id"      =>$act_id,
						"order_id"    =>$order->orderid
				] );

		}else{
			echo '定单号不存在！';
			return;
		}
	}
	
	public function createNoncestr($length = 32) {
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = "";
		for($i = 0; $i < $length; $i ++) {
			$str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
		}
		return $str;
	}


	public function checkWechatOauth( $oAuthUrl ) {
		$code = \Yii::$app->request->get( "code", "" );
		$type = \Yii::$app->request->get( 'type' );
		if ( !$this->user || empty( $this->user->nick_name ) ) {
			if ( !empty( $code ) && !$type ) {
				$this->user = Util::getUserOpenid();
				if ( !$this->user ) {
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
				\Yii::$app->response->redirect( $this->wechat->getOauthRedirect( $redirectUrl,'','snsapi_base' ) );
				\Yii::$app->end();
			}
		}
	}
}