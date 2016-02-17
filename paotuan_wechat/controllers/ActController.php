<?php

namespace paotuan_wechat\controllers;

use common\component\CustomHelper;
use common\models\Act;
use common\models\ActChannel;
use common\models\ActStay;
use common\models\Item;
use common\models\OrderDetail;
use common\models\OrderMaster;
use common\models\Register;
use common\models\UserBindLog;
use common\models\UserPaper;
use paotuan_wechat\component\PaotuanController;
use paotuan_wechat\component\Util;
use paotuan_wechat\models\Club;
use paotuan_wechat\models\ClubMember;
use Yii;
use yii\helpers\ArrayHelper;

class ActController extends PaotuanController {

	public function actionIndex( $id ) {
		$channelId = \Yii::$app->request->get( "channelid", "" );
		$this->checkWechatOauth( "/act/$id?channelid=$channelId" );
		return $this->processIndex( $id );
	}

	private function processIndex( $id ) {
		$act = null;
		$register = null;
		$channelId = \Yii::$app->request->get( "channelid", "" );
		if ( $id && !empty( $channelId ) ) {
			$act = Act::findOne( [
						"actid" => trim( $id ),
						"act_status" => Act::ACT_STATUS_NORMAL
					] );
			$register = Register::find()->where( [
						"uid" => $this->user->uid,
						"actid" => $id
					] )->orderBy( "update_time desc" )->all();
			if ( $register == null ) {
				$register = new Register ();
			} else {
				$register = $register[0];
			}
			$channel = ActChannel::findOne( $channelId );
			$member = null;
			$club = null;
			if ( $channel && $channel->limit_range ) {
				$club = $channel->clubInfo;
				if ( $club->clubid != 1 && $club->club_type == Club::CLUB_TYPE_CLUB ) {
					$member = ClubMember::findOne( ["club_id" => $club->clubid, "uid" => $this->user->uid, "member_status" => ClubMember::STATUS_NORMAL ] );
				}
			}
			$register = Util::copyUserInfoToRegister( $register, $this->user->userInfo );
			$items = Item::findItemList( $channelId );
			$order = null;
			if ( $register->registerid ) {
				$order = $register->order;
				if ( $order !== null ) {
					$trans = \Yii::$app->db->beginTransaction();
					if ( $order->order_status == OrderMaster::STATUS_WAIT_PAY && strtotime( $order->expire_time ) < time() ) {
						$order->order_status = OrderMaster::STATUS_CANCEL;
						$details = OrderDetail::findOrderDetailInfo( $order->orderid );
						$register->payment_status = Register::PAY_STATUS_CANCEL;
						$register->register_status = Register::STATUS_CANCEL;
						if ( $order->save() && $register->save() ) {
							foreach ( $details as $detail ) {
								$detail->item_status = OrderDetail::STATUS_CANCEL;
								$detail->itemInfoAll->item_buy_sum = $detail->itemInfoAll->item_buy_sum - $detail->item_num;
								if ( !$detail->itemInfoAll->save() || !$detail->save() ) {
									$trans->rollBack();
									\Yii::$app->end();
								}
							}
							ActStay::updateAll( [
								"orderid" => $order->orderid
									], "payment_status=3" );
							$trans->commit();
						} else {
							$trans->rollBack();
							\Yii::$app->end();
						}
					}
				}
				if ( $register->payment_status == Register::PAY_STATUS_NEED_PAY && $register->channelid != $channelId ) {
					$order = null;
				}
				$register->channelid = $channelId;
			}
			//$order = OrderMaster::getOrdersByActAndUser ( $act->actid, $this->user->uid );
			$certs = UserPaper::findAll( ["uid" => $this->user->uid, "paper_type" => UserPaper::PAPER_TYPE_COMPLETE, "paper_status" => 0 ] );
			return $this->renderPartial( 'index', [
						'user' => $this->user,
						'act' => $act,
						"signPackage" => Util::getJssdkParams(),
						'register' => $register,
						'items' => $items,
						'order' => $order,
						'channelId' => $channelId,
						"certs" => $certs,
						"club" => $club,
						"member" => $member,
						'channel' => $channel
					] );
		}
	}

	public function actionChannelcode( $id ) {
		if ( Yii::$app->request->isAjax ) {
			$code = \Yii::$app->request->post( "invite_code", "" );
			$channel = ActChannel::findOne( ["channelid" => $id, "invite_code" => $code ] );
			$result = ["status" => 0 ];
			if ( $channel ) {
				$result["status"] = 1;
			}
			CustomHelper::RetrunJson( $result );
		}
	}

	public function actionApply() {
		$id = Yii::$app->request->post( "registerid", "" );
		$cell = "";
		if ( empty( $id ) ) {
			$model = new Register ();
			$model->setIsNewRecord( true );
		} else {
			$model = Register::findOne( $id );
			$cell = $model->user_cell;
			$courseid = $model->courseid;
			$id_number = trim( $model->id_number );
		}
		$msg = array(
			"status" => 0
		);
		if ( $model->load( Yii::$app->request->post() ) ) {
			if ( !$model->isNewRecord ) {
				$model->courseid = $courseid;
			}
			$model->uid = $this->user->uid;
			if ( $model->validate() ) {
				$userInfo = $this->user->userInfo;
				if ( $userInfo->user_cell != $model->user_cell ) {
					// 验证手机验证码
					$code = \Yii::$app->request->post( "code" );
					$code1 = UserBindLog::findOne( [
								"uid" => $model->uid,
								"bind_type" => 1,
								"bind_info" => $model->user_cell,
								"bind_code" => $code,
								"bind_status" => 0
							] );
					if ( $code1 === null || strtotime( $code1->expiry_time ) < time() ) {
						$msg ["msg"] = "验证码错误";
						CustomHelper::RetrunJson( $msg );
					}
					$code1->bind_status = 1;
					$code1->save();
				}
				if ( Register::findOne( ["actid" => $model->actid, "id_number" => $model->id_number, "register_status" => Register::STATUS_REGISTER ] ) ) {
					$msg ["msg"] = "证件号已存在!";
					CustomHelper::RetrunJson( $msg );
				}
				$userInfo = Util::copyRegisterToUserInfo( $userInfo, $model );
				$msg ["status"] = 1;
				$msg ["result"] = "success";

				$trans = \Yii::$app->db->beginTransaction();
				if ( $model->save() && $userInfo->save() ) {
					$trans->commit();
					$msg ["id"] = $model->registerid;
				} else {
					$trans->rollBack();
					$msg ["status"] = 0;
					$msg ["msg"] = "报名失败信息失败";
				}
			} else {
				$msg ["status"] = 0;
				$msg ["msg"] = Util::getStringFromError( $model->errors );
			}
		}
		CustomHelper::RetrunJson( $msg );
	}

	public function actionItem( $id ) {
		$item = Item::findOne( $id );
		$item = ArrayHelper::toArray( $item );
		if ( $item ["item_status"] == Item::STATUS_END || strtotime( $item ["item_end"] ) < time() ) {
			$item ["end"] = 1;
		} else {
			$item ["end"] = 0;
		}
		if ( $item ["item_num_limit"] > 0 && $item ["item_num_limit"] - $item ["item_buy_sum"] <= 0 ) {
			$item ["out"] = 1;
		}
		CustomHelper::RetrunJson( ArrayHelper::toArray( $item ) );
	}

	public function actionCancelpay( $id ) {
		$result = array(
			"status" => 0
		);
		$register = Register::findOne( [
					"uid" => $this->user->uid,
					"registerid" => $id
				] );
		$order = $register->order;
		if ( $order !== null ) {
			$trans = \Yii::$app->db->beginTransaction();
			$order->order_status = OrderMaster::STATUS_CANCEL;
			$details = OrderDetail::findOrderDetailInfo( $order->orderid );
			$register->payment_status = Register::PAY_STATUS_CANCEL;
			$register->register_status = Register::STATUS_CANCEL;
			if ( $order->save() && $register->save() ) {
				foreach ( $details as $detail ) {
					$detail->item_status = OrderDetail::STATUS_CANCEL;
					$detail->itemInfo->item_buy_sum = $detail->itemInfo->item_buy_sum - $detail->item_num;
					if ( !$detail->itemInfo->save() || !$detail->save() ) {
						$trans->rollBack();
						$result["msg"] = "系统异常";
						CustomHelper::RetrunJson( $result );
					}
				}
				ActStay::updateAll( [
					"orderid" => $order->orderid
						], "payment_status=3" );
				$trans->commit();
				$result["status"] = 1;
			} else {
				$trans->rollBack();
				$result["msg"] = "系统异常";
				CustomHelper::RetrunJson( $result );
			}
		}
		CustomHelper::RetrunJson( $result );
	}

	public function actionOrdersubmit( $id ) {
		$items = \Yii::$app->request->post( "items", "" );
		$nums = \Yii::$app->request->post( "nums", "" );
		$orderid = \Yii::$app->request->post( "orderid", "" );
		$registerid = \Yii::$app->request->post( "registerid", "" );
		$channelid = \Yii::$app->request->post( "channelid", "" );
		$result = array(
			"status" => 0
		);
		if ( !empty( $items ) && !empty( $nums ) && $id && $registerid ) {
			$register = Register::findOne( $registerid );
			if ( $channelid != $register->channelid ) {
				$register->channelid = $channelid;
				$register->registerid = null;
				$register->register_status = Register::STATUS_NORMAL;
				$register->setIsNewRecord( true );
				$register->save();
			} else {
				if ( $register->register_status == Register::STATUS_CANCEL ) {
					$register->registerid = null;
					$register->register_status = Register::STATUS_NORMAL;
					$register->setIsNewRecord( true );
					$register->save();
				}
			}

			$om = new OrderMaster ();
			$act = Act::findOne( $id );
			$trans = \Yii::$app->db->beginTransaction();

			OrderDetail::deleteAll( [
				"orderid" => $orderid
			] );
			ActStay::deleteAll( [
				"orderid" => $orderid
			] );
			$itemStrs = explode( ",", $items );
			$nums = explode( ",", $nums );
			$map = array();
			$items = Item::findAll( $itemStrs );
			for ( $i = 0; $i != count( $itemStrs ); $i++ ) {
				$map[$itemStrs[$i]] = $nums[$i];
			}

			if ( count( $items ) > 0 ) {
				$om->uid = $this->user->uid;
				$om->order_title = $act->act_name;
				$om->order_status = 0;
				$om->order_type = OrderMaster::TYPE_ONE_REG;
				$om->payment_start = date( "Y-m-d H:i:s" );
				$om->expire_time = date( "Y-m-d H:i:s", strtotime( "1 hour" ) );
				$om->amount = 0;
				if ( $om->save() ) {
					$channelid = null;
					$total = 0;
					for ( $i = 0; $i != count( $items ); $i++ ) {
						$item = $items[$i];
						$num = $map[$item->itemid];
						if ( $item->item_status == Item::STATUS_END || strtotime( $item->item_end ) < time() ) {
							$result ["msg"] = "对不起，报名已结束！";
							CustomHelper::RetrunJson( $result );
						}
						if ( $item->item_num_limit > 0 && $item->item_num_limit - $item->item_buy_sum < $num ) {
							$result ["msg"] = "对不起,{$item->item_name}名额不足！";
							CustomHelper::RetrunJson( $result );
						}

						$total = $total + $item->item_price * $num;
						$od = new OrderDetail ();
						$od->orderid = $om->orderid;
						$od->itemid = $item->itemid;
						$od->item_title = $item->item_name;
						$od->item_num = $num;
						$od->item_price = $item->item_price;
						$od->item_status = 0;
						$item->item_buy_sum = $item->item_buy_sum + $num;

						if ( !$od->save() || !$item->save() ) {
							$trans->rollBack();
							$result ["msg"] = "系统错误！";
							CustomHelper::RetrunJson( $result );
						} else {
							if ( $item->item_type == Item::TYPE_HOTEL || $item->item_type == Item::TYPE_TRAFFIC ) {
								$as = new ActStay();
								$as->orderid = $om->orderid;
								$as->actid = $item->actid;
								$as->uid = $this->user->uid;
								$as->detailid = $od->detailid;
								$as->passport_name = $this->user->userInfo->passport_name;
								$as->cell = $this->user->userInfo->user_cell;
								if ( $item->item_type == Item::TYPE_HOTEL ) {
									$as->stay_type = ActStay::TYPE_HOTEL;
								} else {
									$as->stay_type = ActStay::TYPE_TRAFFIC;
								}
								$as->stay_status = ActStay::STATUS_NORMAL;
								$as->payment_status = 1;
								if ( !$as->save() ) {
									$trans->rollBack();
									$result ["msg"] = "系统错误！";
									CustomHelper::RetrunJson( $result );
								}
							}
							if ( $item->item_type == Item::TYPE_REGISTER ) {
								$register->courseid = $item->courseid;
								if ( !$register->save() ) {
									$trans->rollBack();
									$result ["msg"] = "系统错误！";
									CustomHelper::RetrunJson( $result );
								}
							}
						}
					}

					$register->orderid = $om->orderid;
					$register->payment_status = Register::PAY_STATUS_NEED_PAY;
					$om->amount = $total;
					$om->actual_payment = $total;
					if ( $total == 0 ) {
						$register->payment_status = Register::PAY_STATUS_FREE;
						$register->register_status = Register::STATUS_REGISTER;
						$om->order_status = OrderMaster::STATUS_NORMAL;
						$om->payment_type = "微信支付wechat";
						$om->trade_no = date( "YmdHis" ) . $om->orderid;
						Util::sendRegEmail( $register );
						$params = array();
						$params["openid"] = \Yii::$app->session["openid"];
						$params["actid"] = $register->actid;
						$params["channelid"] = $register->channelid;
						$params["first"] = "恭喜您,活动报名成功";
						$params["act_name"] = $register->act->act_name;
						$params["act_desc"] = $register->course->course_name;
						$params["act_day"] = $register->act->act_day;
						$params["address"] = $register->act->act_addr;
						$params["cell"] = "4008200124";
						$params["remark"] = "点击详情，查看报名信息";
						Util::http_post( "http://www.paobuqu.com/v3/wechat/register", $params );
					}
					if ( $om->save() && $register->save() ) {
						$trans->commit();
						$result["status"] = 1;
						$result["data"] = ArrayHelper::toArray( $om );
					} else {
						$trans->rollBack();
						$result["msg"] = "系统错误";
					}
				}
			}
		}
		CustomHelper::RetrunJson( $result );
	}

	public function actionUploadcerts() {
		$certs = \Yii::$app->request->post( "certs", "" );
		if ( $certs && $this->user ) {
			$certs = explode( ",", $certs );
			$trans = \Yii::$app->db->beginTransaction();
			UserPaper::deleteAll( ["uid" => $this->user->uid, "paper_type" => 3 ] );
			foreach ( $certs as $cert ) {
				$paper = UserPaper::addPaper( ["uid" => $this->user->uid, "url" => CustomHelper::CreateImageUrl( $cert ) ], "cert" );
				if ( !$paper || !$paper->paperid ) {
					$trans->rollBack();
					CustomHelper::RetrunJson( ["status" => 0 ] );
				}
			}
			$trans->commit();
			CustomHelper::RetrunJson( ["status" => 1 ] );
		}
	}

}
