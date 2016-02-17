<?php

namespace paotuan_wechat\controllers;

use common\component\BindComponent;
use common\component\CustomHelper;
use common\component\Jssdk;
use common\component\UpYun;
use common\models\Country;
use common\models\User;
use common\models\UserBindLog;
use common\models\UserInfo;
use common\models\UserMaster;
use common\models\UserPaper;
use paotuan_wechat\component\PaotuanController;
use paotuan_wechat\component\Util;
use paotuan_wechat\component\WxUtil;
use paotuan_wechat\models\Announcement;
use paotuan_wechat\models\Club;
use paotuan_wechat\models\ClubConfig;
use paotuan_wechat\models\ClubMember;
use paotuan_wechat\models\ClubMemberPayment;
use paotuan_wechat\models\ClubSub;
use paotuan_wechat\models\Mileage;
use paotuan_wechat\models\UploadForm;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ClubsController extends PaotuanController {

	const SPECIAL_CLUB_ID = 968;

	public function actionHome( $club ) {
		\Yii::$app->response->redirect( "/wechat/#/clubs/home?club_eng=$club" );
	}

	public function actionIndex( $club ) {
		$members = ClubMember::getClubMembers( $this->club->clubid, 0, 3 );
		$tmp = Mileage::getSumAndCountByClub( $this->club->clubid );
		\Yii::$app->response->redirect( "/wechat/#/clubs/home?club_eng=$club" );
	}

	public function actionFortest( $club ) {
		$openid = "";
		$user = User::findOne( 245 );
		$club = Club::findOne( [
					"club_eng" => $club
				] );
		$options = \Yii::$app->params ['weixin_config'] ['wechart'];
		$jssdk = new Jssdk( $options ["appid"], $options ["appsecret"] );
		$member = null;
		$userInfo = User::find()->where( [
					"uid" => 245
				] )->with( "city", "userInfo" )->one();
		if ( $user && $club !== null ) {
			$member = ClubMember::findOne( [
						"uid" => $user->uid,
						"club_id" => $club->clubid
					] );
			$newly_pay = ClubMemberPayment::getNewlyPay( $user->uid, $club->clubid );
			if ( !is_null( $member ) ) {
				if ( $member->member_status == ClubMember::STATUS_PAY || ($member->member_status == ClubMember::STATUS_NORMAL && $club->is_collect_member_fee && !empty( $member->expiration_date ) && strtotime( $member->expiration_date ) < time()) ) {
					$member->setStatusPay();
				}
			}
		}
		return $this->renderPartial( 'apply', [
					'user' => $user,
					"club" => $club,
					"signPackage" => $jssdk->getSignPackage(),
					"member" => $member,
					"openid" => "oyL64uI7WE6HUm2RJ60ahVuZpbOc",
					"userInfo" => $userInfo,
					"newlypay" => $newly_pay
				] );
	}

	public function actionMemberpaydone() {
		$uid = \Yii::$app->request->post( "uid" );
		$club_id = \Yii::$app->request->post( "club_id" );
		$result = array(
			"status" => 0
		);
		$member = ClubMember::findOne( [
					"member_status" => 5,
					"club_id" => $club_id,
					"uid" => $uid
				] );
		if ( $member != null ) {
			$member->member_status = ClubMember::STATUS_PAY_WAIT_FOR_DONE;
			$member->save();
			$result ["status"] = 1;
		}
		CustomHelper::RetrunJson( $result );
	}

	public function actionMembers( $club ) {
		// $this->checkWechatOauth ( "/clubs/$club/members" );
		//return $this->processMembers ();
		\Yii::$app->response->redirect( "/wechat/#/clubs/members?club_eng=$club" );
	}

	private function processMembers() {
		$openid = "";
		$createUser = UserMaster::findOne( $this->club->uid );
		return $this->renderPartial( 'members', [
					"club" => $this->club,
					"clubOwner" => $createUser,
					"signPackage" => Util::getJssdkParams()
				] );
	}

	public function actionFind() {
		\Yii::$app->response->redirect( "/wechat/#/clubs/search" );
	}

	public function actionNew() {
		$this->checkWechatOauth( "/clubs/new" );
		if ( $this->user ) {
			if ( !$this->user->user_cell ) {
				\Yii::$app->response->redirect( "/bind?uid=" . $this->user->uid . "&redirect=" . rawurlencode( \Yii::$app->request->hostInfo . "/clubs/new" ) );
			}
			$wxUser = WxUtil::getWxUserInfo( \Yii::$app->session ["openid"] );
			$subscribe = false;
			if ( $wxUser && !$wxUser->errcode && $wxUser->subscribe ) {
				$subscribe = true;
			}
			return $this->renderPartial( "new", [
						"signPackage" => Util::getJssdkParams(),
						"user" => $this->user,
						"subscribe" => $subscribe
					] );
		}
	}

	public function actionCreateclub() {
		$club_name = trim( \Yii::$app->request->post( "club_name", "" ) );
		$club_eng = trim( \Yii::$app->request->post( "club_eng", "" ) );
		$club_logo = trim( \Yii::$app->request->post( "club_logo", "" ) );
		$club_slogan = trim( \Yii::$app->request->post( "club_slogan", "" ) );
		$result = array(
			"status" => 0
		);
		if ( !empty( $club_name ) && !empty( $club_eng ) ) {
			if ( preg_match( '/^[_0-9a-zA-Z]+$/', $club_eng ) ) {
				$club = Club::find()->where( "club_name='$club_name' and club_type=" . Club::CLUB_TYPE_CLUB . " and (club_status=" . Club::CLUB_STATUS_NORMAL . " or club_status=" . Club::CLUB_STATUS_PROCESS . ")" )->one();
				if ( $club ) {
					$result ["status"] = - 1;
					$result ["msg"] = "跑团名称已经存在";
				} else {
					$club = Club::find()->where( "club_eng='$club_eng' and club_type=" . Club::CLUB_TYPE_CLUB . " and (club_status=" . Club::CLUB_STATUS_NORMAL . " or club_status=" . Club::CLUB_STATUS_PROCESS . ")" )->one();
					if ( $club ) {
						$result ["status"] = - 2;
						$result ["msg"] = "跑团英文短名已经存在";
					} else {
						$newClub = new Club ();
						$newClub->club_name = $club_name;
						$newClub->club_eng = $club_eng;
						$newClub->club_slogan = $club_slogan;
						$newClub->club_logo = $club_logo;
						$newClub->club_type = Club::CLUB_TYPE_CLUB;
						$newClub->club_status = Club::CLUB_STATUS_PROCESS;
						$newClub->uid = $this->user->uid;
						$newClub->start_date = date( "Y-m-d" );

						if ( $newClub->save() ) {
							$cc1 = new ClubConfig();
							$cc2 = new ClubConfig();
							$cc3 = new ClubConfig();
							$cc4 = new ClubConfig();
							$cc1->club_id = $newClub->clubid;
							$cc1->col_name = "passport_name";
							$cc1->col_title = "真实姓名";
							$cc1->visible = 1;
							$cc1->optional = 1;
							$cc1->col_type = ClubConfig::TYPE_TEXT;
							$cc1->sort = 0;
							$cc1->system = 1;

							$cc2->club_id = $newClub->clubid;
							$cc2->col_name = "gender";
							$cc2->col_title = "性别";
							$cc2->visible = 1;
							$cc2->optional = 1;
							$cc2->col_type = ClubConfig::TYPE_LIST;
							$cc2->col_list_values = "其他`男`女";
							$cc2->sort = 1;
							$cc2->system = 1;

							$cc3->club_id = $newClub->clubid;
							$cc3->col_name = "cell";
							$cc3->col_title = "手机号";
							$cc3->visible = 1;
							$cc3->optional = 1;
							$cc3->col_type = ClubConfig::TYPE_NUMBER;
							$cc3->sort = 2;
							$cc3->system = 1;

							$cc4->club_id = $newClub->clubid;
							$cc4->col_name = "nick_name";
							$cc4->col_title = "昵称";
							$cc4->visible = 1;
							$cc4->optional = 1;
							$cc4->col_type = ClubConfig::TYPE_TEXT;
							$cc4->sort = 3;
							$cc4->system = 1;

							$clubMember = new ClubMember ();
							$clubMember->club_id = $newClub->clubid;
							$clubMember->uid = $this->user->uid;
							//$clubMember->passport_name = $this->user->nick_name;
							$clubMember->gender = $this->user->user_gender;
							$clubMember->member_status = ClubMember::STATUS_NORMAL;
							$clubMember->openid = \Yii::$app->session ["openid"];
							$clubMember->cell = $this->user->user_cell;
							if ( $clubMember->save() && $cc1->save() && $cc2->save() && $cc3->save() && $cc4->save() ) {
								Util::sendClubCreateMsg( ["cell" => $this->user->user_cell, "club_name" => $club_name, "club_eng" => $club_eng ] );
								WxUtil::sendTemplateClubMember( ["openid" => $clubMember->openid, "id" => $clubMember->id, "club_name" => $club_name, "club_eng" => $club_eng, "nick_name" => $this->user->nick_name ] );
								$result ["status"] = 1;
								$result ["club"] = ArrayHelper::toArray( $newClub );
								$result ["url"] = \Yii::$app->request->hostInfo . "/clubs/$club_eng/home";
							}
						} else {
							$result ["msg"] = "系统错误";
						}
					}
				}
			} else {
				$result ["msg"] = "英文短名只能为数字字母下划线";
			}
			CustomHelper::RetrunJson( $result );
		}
	}

	public function actionFindclub() {
		$name = trim( \Yii::$app->request->post( "name", "" ) );
		$result = array();
		$result ["clubs"] = array();
		if ( !empty( $name ) ) {
			$clubs = Club::find()->where( "club_name like '%$name%' and (club_status = " . Club::CLUB_STATUS_NORMAL . " or club_status=" . Club::CLUB_STATUS_PROCESS . ") and club_type = " . Club::CLUB_TYPE_CLUB )->with( "members" )->all();
			// $club = Club::findOne(["club_name"=>$name,"club_status"=>Club::CLUB_STATUS_NORMAL,"club_type"=>Club::CLUB_TYPE_CLUB]);
			foreach ( $clubs as $club ) {
				if ( $club ) {
					$members = count( $club->members );
					$club = ArrayHelper::toArray( $club );
					$club ["members"] = $members;
					array_push( $result ["clubs"], $club );
				}
			}
		}
		CustomHelper::RetrunJson( ArrayHelper::toArray( $result ) );
	}

	public function actionFindclub1() {
		$name = trim( \Yii::$app->request->post( "name", "" ) );
		$offset = \Yii::$app->request->post( "offset", 0 );
		$limit = \Yii::$app->request->post( "limit", 20 );
		if ( !empty( $name ) ) {
			$clubs = Club::findClubs( $name, $offset, $limit );
			CustomHelper::RetrunJson( ArrayHelper::toArray( $clubs ) );
		}
	}

	public function actionMileages( $club ) {
		$this->redirect( "/wechat/#/clubs/mileages?club_eng=$club" );
		return;
	}

	private function processMileages() {
		$members = $this->club->members;
		$mis = array();
		$isMember = false;
		if ( $this->user ) {
			$uids = array();
			foreach ( $members as $member ) {
				if ( $member->uid == $this->user->uid ) {
					$isMember = true;
				}
				if ( array_search( $member->uid, $uids ) === false ) {
					array_push( $uids, $member->uid );
				}
			}
			$mis = Mileage::getRecent( $uids, 0, 6 );
		}
		$tmp = Mileage::getSumAndCountByClub( $this->club->clubid );
		return $this->renderPartial( 'clubMileages', [
					'user' => $this->user,
					"club" => $this->club,
					"signPackage" => Util::getJssdkParams(),
					"members" => $members,
					"count" => $tmp->id,
					"total" => round( $tmp->mileage, 2 ),
					"mileages" => $mis,
					"isMember" => $isMember
				] );
	}

	public function actionBasemileages() {
		if ( $this->club ) {
			$type = \Yii::$app->request->post( "type", 'run' );
			$tmp = Mileage::getSumAndCountByClub( $this->club->clubid, $type );
			$club = ArrayHelper::toArray( $this->club );
			$club["m_count"] = $tmp->id;
			$club["m_total"] = round( $tmp->mileage, 2 );
			CustomHelper::RetrunJson( $club );
		}
	}

	public function actionGetmembers() {
		$offset = \Yii::$app->request->post( "offset", 0 );
		$limit = \Yii::$app->request->post( "limit", 25 );
		// $club_id = \Yii::$app->request->post ( "clubid", "" );
		$result = new \stdClass ();
		$result->members = array();
		$members = ClubMember::getClubMembers( $this->club->clubid, $offset, $limit );
		foreach ( $members as $member ) {
			if ( $member->uid == $this->club->uid ) {
				continue;
			}
			$tmpUser["uid"] = $member->user->uid;
			$tmpUser["nick_name"] = $member->user->nick_name;
			$tmpUser["user_face"] = Util::getUserFace( $member->user->user_face );
			$tmpUser ["gender"] = $member->gender;
			$tmpUser ["status"] = $member->member_status;
			array_push( $result->members, $tmpUser );
		}
		CustomHelper::RetrunJson( $result );
	}

	public function actionRegister( $club ) {
		\Yii::$app->response->redirect( "/wechat/#/clubs/register?club_eng=$club" );
	}

	private function processRegister() {
		$openid = \Yii::$app->session ["openid"];
		$options = \Yii::$app->params ['weixin_config'] ['wechart'];
		$jssdk = new Jssdk( $options ["appid"], $options ["appsecret"] );
		$member = null;
		$newly_pay = null;
		if ( $this->user ) {
			$member = ClubMember::findOne( [
						"uid" => $this->user->uid,
						"club_id" => $this->club->clubid
					] );
			$newly_pay = ClubMemberPayment::getNewlyPay( $this->user->uid, $this->club->clubid );
			if ( !is_null( $member ) ) {
				if ( $member->member_status == ClubMember::STATUS_PAY || ($member->member_status == ClubMember::STATUS_NORMAL && $this->club->is_collect_member_fee && !empty( $member->expiration_date ) && strtotime( $member->expiration_date ) < time()) ) {
					$member->setStatusPay();
				}
			}
		}
		return $this->renderPartial( 'apply', [
					'user' => $this->user,
					"club" => $this->club,
					"signPackage" => $jssdk->getSignPackage(),
					"member" => $member,
					"openid" => $openid,
					"newlypay" => $newly_pay,
					"contry" => Country::find()->orderBy( "sort desc" )->all()
				] );
	}

	public function actionApply() {
		$id = Yii::$app->request->post( "id", "" );
		$cell = "";
		if ( empty( $id ) ) {
			$model = new ClubMember ();
			$model->setIsNewRecord( true );
		} else {
			$model = ClubMember::findOne( $id );
			$cell = $model->cell;
		}
		$msg = array(
			"status" => 0
		);
		if ( $model->load( Yii::$app->request->post() ) ) {
			$club = Club::findOne( $model->club_id );
			if ( $model->validate() ) {
				$userInfo = UserInfo::findOne( $model->uid );
				if ( !empty( $model->cell ) && $userInfo->user_cell != $model->cell ) {
					// 验证手机验证码
					$code = \Yii::$app->request->post( "code" );
					$code1 = UserBindLog::findOne( [
								"uid" => $model->uid,
								"bind_type" => 1,
								"bind_info" => $model->cell,
								"bind_code" => $code,
								"bind_status" => 0
							] );
					if ( $code1 === null || strtotime( $code1->expiry_time ) < time() ) {
						$msg ["msg"] = "验证码错误";
						CustomHelper::RetrunJson( $msg );
					}
					$code1->bind_status = 1;
					$code1->save();

					if ( ClubMember::findOne( [
								"club_id" => $model->club_id,
								"cell" => $model->cell
							] ) != null ) {
						$msg ["msg"] = "手机号已注册";
						CustomHelper::RetrunJson( $msg );
					}
				}
				$userInfo = $this->copyMemberToUserInfo( $userInfo, $model );
				$msg ["status"] = 1;
				$msg ["result"] = "success";
				$payment = new ClubMemberPayment();
				if ( $model->getIsNewRecord() ) {
					if ( (empty( $club->need_approve ) || $club->need_approve == 0 ) ) {
						if ( $club->is_collect_member_fee ) {
							if ( !empty( $club->fee_start_date ) ) {
								$feeStartDate = strtotime( $club->fee_start_date );
								$now = strtotime( date( "Y-m-d" ) );
								if ( $feeStartDate < $now ) {
									$model->member_status = ClubMember::STATUS_PAY;
									$msg ["result"] = "needpay";
								} else {
									$model->member_status = ClubMember::STATUS_NORMAL;
									$model->expiration_date = $club->fee_start_date;
									$msg ["result"] = "success";
								}
							} else {
								$model->member_status = ClubMember::STATUS_PAY;
								$payment->club_id = $this->club_id;
								$payment->trade_no = CustomHelper::CreateOrderID( time() . $this->uid );
								$payment->uid = $this->uid;
								$payment->pay_partner = "wxpay";
								$payment->trade_status = 1;
								$msg ["result"] = "needpay";
							}
						} else {
							$model->member_status = ClubMember::STATUS_NORMAL;
							$msg ["result"] = "success";
						}
					} else {
						$model->member_status = ClubMember::STATUS_WAIT;
						$msg ["result"] = "needconfirm";
					}
				}
				$trans = \Yii::$app->db->beginTransaction();
				if ( $model->save( false ) && $userInfo->save( false ) ) {
					if ( $model->getIsNewRecord() && $model->member_status == ClubMember::STATUS_PAY ) {
						if ( $payment->save() ) {
							$trans->commit();
						} else {
							$trans->rollBack();
							$msg ["status"] = 0;
							$msg ["msg"] = "创建会员信息失败";
						}
					} else {
						$trans->commit();
					}
					$msg ["id"] = $model->id;
				} else {
					$trans->rollBack();
					$msg ["status"] = 0;
					$msg ["msg"] = "创建会员信息失败";
				}
			} else {
				$msg ["status"] = 0;
				$msg ["msg"] = $this->getStringFromError( $model->errors );
			}
		}
		if ( $msg ["status"] == 1 && $model->member_status == ClubMember::STATUS_PAY ) {
			$model->setStatusPay();
		}
		if ( $msg ["status"] == 1 && $model->member_status == ClubMember::STATUS_NORMAL && $model->getIsNewRecord() ) {
			WxUtil::sendTemplateClubMember( ["openid" => $model->openid, "id" => $model->id, "club_name" => $this->club->club_name, "club_eng" => $this->club->club_eng, "nick_name" => $this->user->nick_name ] );
		}
		CustomHelper::RetrunJson( $msg );
	}

	public function actionNgapply() {
		$msg = array(
			"status" => 0
		);
		if ( !$this->user ) {
			$msg ["msg"] = "获取用户信息失败!";
			CustomHelper::RetrunJson( $msg );
		}

		$id = Yii::$app->request->post( "id", "" );
		$cell = "";
		if ( empty( $id ) ) {
			$model = new ClubMember ();
			$model->setIsNewRecord( true );
		} else {
			$model = ClubMember::findOne( $id );
			$cell = $model->cell;
		}


		if ( $model->load( Yii::$app->request->post() ) ) {
			$club = Club::findOne( $model->club_id );
			$model->uid = $this->user->uid;
			$model->net_name = $this->user->nick_name;
			if ( $model->validate() ) {
				$model->openid = $this->openid;
				$userInfo = UserInfo::findOne( $model->uid );
				if ( !empty( $model->cell ) && $userInfo->user_cell != $model->cell ) {
					// 验证手机验证码
					$code = \Yii::$app->request->post( "code" );
					$code1 = UserBindLog::findOne( [
								"uid" => $model->uid,
								"bind_type" => 1,
								"bind_info" => $model->cell,
								"bind_code" => $code,
								"bind_status" => 0
							] );
					if ( $code1 === null || strtotime( $code1->expiry_time ) < time() ) {
						$msg ["msg"] = "验证码错误";
						CustomHelper::RetrunJson( $msg );
					}
					$code1->bind_status = 1;
					$code1->save();

					if ( ClubMember::findOne( [
								"club_id" => $model->club_id,
								"cell" => $model->cell
							] ) != null ) {
						$msg ["msg"] = "手机号已注册";
						CustomHelper::RetrunJson( $msg );
					}
				}
				$userInfo = $this->copyMemberToUserInfo( $userInfo, $model );
				if ( $model->cell == null && trim( $model->cell == "" ) ) {
					$model->cell = $this->user->user_cell;
				}
				$msg ["status"] = 1;
				$msg ["result"] = "success";
				if ( $model->getIsNewRecord() ) {
					if ( (empty( $club->need_approve ) || $club->need_approve == 0 ) ) {
						if ( $club->is_collect_member_fee ) {
							if ( !empty( $club->fee_start_date ) ) {
								$feeStartDate = strtotime( $club->fee_start_date );
								$now = strtotime( date( "Y-m-d" ) );
								if ( $feeStartDate < $now ) {
									$model->member_status = ClubMember::STATUS_PAY;
									$msg ["result"] = "needpay";
								} else {
									$model->member_status = ClubMember::STATUS_NORMAL;
									$model->expiration_date = $club->fee_start_date;
									$msg ["result"] = "success";
								}
							} else {
								$model->member_status = ClubMember::STATUS_PAY;
								$msg ["result"] = "needpay";
							}
						} else {
							$model->member_status = ClubMember::STATUS_NORMAL;
							$msg ["result"] = "success";
						}
					} else {
						$model->member_status = ClubMember::STATUS_WAIT;
						$msg ["result"] = "needconfirm";
					}
				}
				$trans = \Yii::$app->db->beginTransaction();
				if ( $model->getIsNewRecord() && $model->member_status == ClubMember::STATUS_PAY ) {
					$payment = new ClubMemberPayment();
					$payment->setIsNewRecord( true );
					$payment->club_id = $this->club->clubid;
					$payment->uid = $this->user->uid;
					$payment->pay_partner = "wxpay";
					$payment->trade_status = 1;
					if ( $model->save( false ) && $userInfo->save( false ) && $payment->save( false ) ) {
						$trans->commit();
					} else {
						$trans->rollBack();
						$msg ["status"] = 0;
						$msg ["msg"] = "创建会员信息失败";
					}
				} else {
					if ( $model->member_status == ClubMember::STATUS_RE ) {
						$model->member_status = ClubMember::STATUS_WAIT;
					}
					if ( $model->save( false ) && $userInfo->save( false ) ) {
						$trans->commit();
						if ( empty( $id ) && $model->member_status == ClubMember::STATUS_NORMAL ) {
							$r = Util::genCredit( Util::CREDIT_EVENT_CLUB_JOIN, $model->club_id, 0, $model->uid );
							if ( $r ) {
								$msg ["status"] = 1;
								$msg ["msg"] = "";
							} else {
								$model->delete();
								$msg ["status"] = 0;
								$msg ["msg"] = "系统错误!Credit";
							}
						} else {
							$msg ["status"] = 1;
							$msg ["msg"] = "";
						}
					} else {
						$trans->rollBack();
						$msg ["status"] = 0;
						$msg ["msg"] = "创建会员信息失败";
					}
				}
				$msg["data"] = $this->copyMember2Config( $model );
			} else {
				$msg ["status"] = 0;
				$msg ["msg"] = $this->getStringFromError( $model->errors );
			}
		}

		if ( $msg ["status"] == 1 && empty( $id ) ) {
			if ( $model->member_status == ClubMember::STATUS_NORMAL ) {
				WxUtil::sendTemplateClubMember( ["openid" => $model->openid, "id" => $model->id, "club_name" => $this->club->club_name, "club_eng" => $this->club->club_eng, "nick_name" => $this->user->nick_name ] );
			}
			if ( $model->member_status == ClubMember::STATUS_WAIT ) {
				WxUtil::sendWXTemplateMsg( [
					WxUtil::getObject( ["openid" => $model->openid, "club_name" => $this->club->club_name, "club_eng" => $this->club->club_eng, "nick_name" => $this->user->nick_name, "status" => 1 ], WxUtil::API_CLUB_AUDIT_RESULT )
				] );
			}
			if ( $model->member_status == ClubMember::STATUS_PAY ) {
				WxUtil::sendWXTemplateMsg( [
					WxUtil::getObject( ["openid" => $model->openid, "club_name" => $this->club->club_name, "club_eng" => $this->club->club_eng, "nick_name" => $this->user->nick_name, "status" => 2 ], WxUtil::API_CLUB_AUDIT_RESULT )
				] );
			}
		}
		CustomHelper::RetrunJson( $msg );
	}

	public function actionUploadimg() {
		$uploadForm = new UploadForm ();
		if ( Yii::$app->request->isPost ) {
			$result = array(
				"status" => 0
			);
			try {
				$type = \Yii::$app->request->post( "img_type" );
				$uploadForm->file = UploadedFile::getInstance( $uploadForm, 'file' );
				if ( $uploadForm->file && $uploadForm->validate() ) {
					$upyunInfo = \Yii::$app->params ['upyun'];
					$upyun = new UpYun( $upyunInfo ['bucketname'], $upyunInfo ['username'], $upyunInfo ['password'] );
					$file = CustomHelper::randomPassword( 10 ) . "." . $uploadForm->file->extension;
					$path = "/{$type}/" . time() . "/{$file}";
					$info = $upyun->writeFile( $path, fopen( $uploadForm->file->tempName, 'r+' ), true );
					if ( !empty( $info ) ) {
						$result ["status"] = 1;
						$result ["image"] = $path;
						$result ["id"] = $type;
						echo "<div id='uploadResult'>" . json_encode( $result ) . "</div>";
					}
				}
			} catch (Exception $e) {
				echo "<div id='uploadResult'>" . json_encode( $result ) . "</div>";
			}
		}
	}

	private function getStringFromError( $errors ) {
		$str = "";
		foreach ( array_keys( $errors ) as $error ) {
			$str = $str . "," . $error . ":" . $errors [$error];
		}
		if ( strlen( $str ) != 0 ) {
			$str = substr( $str, 1 );
		}
		return $str;
	}

	public function actionGetcellcode() {
		$cell = \Yii::$app->request->post( "cell" );
		$uid = \Yii::$app->request->post( "uid", "" );
		if ( !$uid ) {
			$uid = $this->user->uid;
		}
		$result = array(
			"status" => 0,
			"msg" => "手机号码有误!"
		);
		if ( !empty( $uid ) && !empty( $cell ) && CustomHelper::isCell( $cell ) ) {
			$param ['rand_code'] = CustomHelper::RandCode();
			$param ['cell'] = $cell;
			BindComponent::SendCellCode( $param );
			$UserBind = new UserBindLog ();
			$UserBind->uid = $uid;
			$UserBind->bind_type = 1;
			$UserBind->bind_info = $cell;
			$UserBind->bind_code = $param ['rand_code'];
			$UserBind->insert();
			$result ["status"] = 1;
			$result ["msg"] = "验证码发送成功";
		}
		CustomHelper::RetrunJson( $result );
	}

	public function actionActivity( $id, $club ) {
		switch ( $id ) {
			case "new": {
					\Yii::$app->response->redirect( "/wechat/#/activity/new?club_eng=$club" );
					break;
				}
			case "list": {
					\Yii::$app->response->redirect( "/wechat/#/activity/list?club_eng=$club" );
					break;
				}
		}
	}

	private function copyMemberToUserInfo( $userInfo, $member ) {
		$userInfo->passport_name = $member->passport_name;
		$userInfo->user_gender = $member->gender;
		$userInfo->morning_pulse = $member->morning_pulse;
		if ( !empty( $member->nationality ) )
			$userInfo->nationality = $member->nationality;
		if ( empty( $userInfo->id_type ) || !empty( $member->id_type ) )
			$userInfo->id_type = $member->id_type;
		if ( empty( $userInfo->id_number ) || !empty( $member->id_number ) )
			$userInfo->id_number = $member->id_number;
		if ( !empty( $member->birthday ) )
			$userInfo->birthday = $member->birthday;
		if ( !empty( $member->shirt_size ) )
			$userInfo->tshirt_size = $member->shirt_size;
		if ( !empty( $member->shoe_size ) )
			$userInfo->shoes_size = $member->shoe_size;
		if ( empty( $userInfo->address ) || !empty( $member->address ) )
			$userInfo->address = $member->address;
		if ( !empty( $member->blood_type ) )
			$userInfo->blood_type = $member->blood_type;
		if ( !empty( $member->height ) )
			$userInfo->height = $member->height;
		if ( !empty( $member->weight ) )
			$userInfo->weight = $member->weight;
		if ( !empty( $member->medical_history ) )
			$userInfo->medical_history = $member->medical_history;
		if ( empty( $userInfo->emerge_name ) || !empty( $member->emerge_name ) ) {
			$userInfo->emerge_name = $member->emerge_name;
		}
		if ( empty( $userInfo->emerge_cell ) || !empty( $member->emerge_cell ) ) {
			$userInfo->emerge_cell = $member->emerge_cell;
		}
		if ( empty( $userInfo->emerge_ship ) || !empty( $member->emerge_ship ) ) {
			$userInfo->emerge_ship = $member->emerge_ship;
		}
		if ( empty( $userInfo->user_email ) || !empty( $member->user_email ) )
			$userInfo->user_email = $member->email;
		if ( empty( $userInfo->user_cell ) || !empty( $member->user_cell ) )
			$userInfo->user_cell = $member->cell;
		if ( !empty( $member->run_age ) )
			$userInfo->run_age = $member->run_age;
		$userInfo->update_time = date( "Y-m-d H:i:s" );
		if ( !empty( $member->id_image ) ) {
			$userPaper = UserPaper::findOne( [
						"uid" => $userInfo->uid,
						"paper_type" => UserPaper::PAPER_TYPE_ID_COPY
					] );
			if ( $userPaper !== null ) {
				$userPaper->paper_url = CustomHelper::CreateImageUrl( $member->id_image );
				$userPaper->save();
			} else {
				UserPaper::addPaper( array(
					"uid" => $member->uid,
					"url" => CustomHelper::CreateImageUrl( $member->id_image )
						), "idcopy" );
			}
		}
		if ( !empty( $member->id_copy_back ) ) {
			$userPaper_back = UserPaper::findOne( [
						"uid" => $userInfo->uid,
						"paper_type" => UserPaper::PAPER_TYPE_ID_COPY_BACK
					] );
			if ( $userPaper_back !== null ) {
				$userPaper_back->paper_url = CustomHelper::CreateImageUrl( $member->id_copy_back );
				$userPaper_back->save();
			} else {

				UserPaper::addPaper( array(
					"uid" => $member->uid,
					"url" => CustomHelper::CreateImageUrl( $member->id_copy_back )
						), "idcopyback" );
			}
		}

		if ( !empty( $member->health_check_certificate ) ) {
			$userPaper_medical = UserPaper::findOne( [
						"uid" => $member->uid,
						"paper_type" => UserPaper::PAPER_TYPE_HEALTH,
						"paper_status" => 0
					] );
			if ( $userPaper_medical !== null ) {
				$userPaper_medical->paper_url = CustomHelper::CreateImageUrl( $member->health_check_certificate );
				$userPaper_medical->save();
			} else {
				UserPaper::addPaper( array(
					"uid" => $member->uid,
					"url" => CustomHelper::CreateImageUrl( $member->health_check_certificate )
						), "report" );
			}
		}
		return $userInfo;
	}

	public function actionClubinfo( $club ) {
		$member_start_time = \Yii::$app->request->post( "member_start_time", null );
		$members = array();
		if ( $member_start_time ) {
			$members = ClubMember::find()->select( "id,create_time" )->where( "club_id = " . $this->club->clubid . " and (member_status=" . ClubMember::STATUS_SIMPLE . " or member_status = " . ClubMember::STATUS_NORMAL . ") and create_time >'" . $member_start_time . "'" )->orderBy( "create_time desc" )->all();
		} else {
			$members = ClubMember::find()->select( "id,create_time" )->where( "club_id = " . $this->club->clubid . " and (member_status=" . ClubMember::STATUS_SIMPLE . " or member_status = " . ClubMember::STATUS_NORMAL . ")" )->orderBy( "create_time desc" )->all();
			if ( $members ) {
				$members = [array_shift( $members ) ];
			}
		}
		if ( $members == null ) {
			$members = [ ];
		}

		$act_start_time = \Yii::$app->request->post( "act_start_time", null );
		$acts = $this->club->acts;
		$array_acts = array();
		if ( $act_start_time ) {
			foreach ( $acts as $act ) {
				if ( $act_start_time < $act->act_create_time ) {
					array_push( $array_acts, ArrayHelper::toArray( $act, ["paotuan_wechat\models\Activity" => ["act_id", "act_create_time" ] ] ) );
				}
			}
		} else {
			if ( count( $acts ) > 0 ) {
				array_push( $array_acts, ArrayHelper::toArray( $acts[0] ) );
			}
		}

		$createUser = UserMaster::findOne( $this->club->uid );
		$member = false;
		if ( $this->user ) {
			$member = ClubMember::getNormalMember( $this->club->clubid, $this->user->uid );
			if ( $member ) {
				$member = true;
			} else {
				$member = false;
			}
		}
		$anns = [ ];
		foreach ( $this->club->announs as $ann ) {
//			$user = $ann->user;
			$ann = ArrayHelper::toArray( $ann );
			$ann["create_time"] = date( "Y-m-d", $ann["create_time"] );
			array_push( $anns, $ann );
		}
		$result = [
			"club" => ArrayHelper::toArray( $this->club ),
			"acts" => $array_acts,
			"members" => ArrayHelper::toArray( $members ),
			"owner" => ["uid" => $createUser->uid, "nick_name" => $createUser->nick_name, "user_face" => Util::getUserFace( $createUser->user_face ), "gender" => $createUser->user_gender ],
			"total" => count( $this->club->members ),
			"anns" => $anns,
			"member" => $member,
			'showwalk' => $this->club->clubid == self::SPECIAL_CLUB_ID
		];
		CustomHelper::RetrunJson( $result );
	}

	public function actionCheckmember() {
		if ( $this->user ) {
			$result = array();
			$club_eng = trim( \Yii::$app->request->post( "club_eng", "" ) );
			if ( $club_eng ) {
				$club = Club::findOne( ["club_eng" => $club_eng ] );
				if ( $club ) {
					$member = ClubMember::getNormalMember( $club->clubid, $this->user->uid );
					if ( $member ) {
						$result ["member"] = true;
						$result["uid"] = $this->user->uid;
						if ( $member->user->user_cell ) {
							$result ["bindCell"] = true;
						} else {
							$result ["bindCell"] = false;
						}
						if ( $this->user->uid != $club->uid && $member->roleid != 1 ) {
							$result["role_error"] = true;
						} else {
							$result["role_error"] = false;
						}
						CustomHelper::RetrunJson( $result );
					}
				} else {
					throw new NotFoundHttpException();
				}
			} else {
				throw new NotFoundHttpException();
			}
		}
	}

	public function actionClubmember() {
		if ( $this->user && $this->club ) {
			$member = ClubMember::findOne( [
						"club_id" => $this->club->clubid,
						"uid" => $this->user->uid
					] );
			if ( !$member ) {
				$member = new ClubMember();
			} else {
				if ( $member->member_status == ClubMember::STATUS_PAY || ($member->member_status == ClubMember::STATUS_NORMAL && $this->club->is_collect_member_fee && !empty( $member->expiration_date ) && strtotime( $member->expiration_date ) < time()) ) {
					$member->setStatusPay();
				}
			}
			if ( $this->user->userInfo ) {
				$member = Util::copyUserInfoToMember( $member, $this->user->userInfo );
				//$member->cell = $this->user->user_cell;
				$member->net_name = $this->user->nick_name;
			}
			CustomHelper::RetrunJson( $this->copyMember2Config( $member ) );
		}
	}

	private function copyMember2Config( $member ) {
		if ( $member ) {
			$result = array();
			$id_copy_back = $member->id_copy_back;
			$memberArray = ArrayHelper::toArray( $member );
			$memberArray["id_copy_back"] = $member->id_copy_back;
			switch ( $member->member_status ) {
				case ClubMember::STATUS_NORMAL:$memberArray["statusText"] = "正式会员";
					break;
				case ClubMember::STATUS_RE:$memberArray["statusText"] = "驳回";
					break;
				case ClubMember::STATUS_WAIT:$memberArray["statusText"] = "待审核";
					break;
				case ClubMember::STATUS_PAY:$memberArray["statusText"] = "待支付";
					break;
				case ClubMember::STATUS_SIMPLE:$memberArray["statusText"] = "一般会员";
					break;
				case ClubMember::STATUS_PAY_WAIT_FOR_DONE:$memberArray["statusText"] = "支付确认中";
					break;
				default:$memberArray["statusText"] = "未加入";
					break;
			}
			$config = $this->club->configs;
			if ( $config ) {
				$configValue = ArrayHelper::toArray( $config );
				for ( $i = 0; $i != count( $configValue ); $i++ ) {
					$c = $configValue[$i];
					$has = false;
					$v = null;
					foreach ( $memberArray as $key => $value ) {
						if ( $c["col_name"] == $key ) {
							$v = $value;
							$has = true;
							break;
						}
					}
					if ( $c["col_name"] == "nick_name" ) {
						$v = $memberArray["net_name"];
					}
					if ( $c["col_name"] == "gender" ) {
						$c["col_type"] = ClubConfig::TYPE_LIST;
						if ( $list_values ) {
							
						}
						//$list_values =explode(",", $list_values);

						if ( $v == null ) {
							$v = 1;
						} else {
							if ( $v != 1 && $v != 2 ) {
								$v = 0;
							}
						}
						$c["col_list_values"] = $this->converArray2Obj( ["男", "女", "其他" ], [1, 2, 0 ], $v );
					}

					if ( $c["col_name"] == "id_type" ) {
						$c["col_type"] = ClubConfig::TYPE_LIST;
						$c["col_list_values"] = $this->converArray2Obj( ["身份证", "护照", "台胞证", "港澳通行证", "其他" ], [1, 2, 3, 4, 0 ], $v );
					}

					if ( $c["col_name"] == "shirt_size" ) {
						$c["col_type"] = ClubConfig::TYPE_LIST;
						$c["col_list_values"] = $this->converArray2Obj( ["XXS", "XS", "S", "M", "L", "XL", "XXL", "XXXL" ], ["XXS", "XS", "S", "M", "L", "XL", "XXL", "XXXL" ], $v );
					}

					if ( $c["col_name"] == "blood_type" ) {
						$c["col_type"] = ClubConfig::TYPE_LIST;
						$c["col_list_values"] = $this->converArray2Obj( ["A", "B", "AB", "O", "Other" ], ["A", "B", "AB", "O", "Other" ], $v );
					}

					if ( $c["col_name"] == "run_age" ) {
						$c["col_type"] = ClubConfig::TYPE_LIST;
						$c["col_list_values"] = $this->converArray2Obj( ["一年以内", "1", "2", "3", "4", "5年或以上" ], ["一年以内", "1", "2", "3", "4", "5" ], $v );
					}

					if ( $c["col_name"] == "birthday" ) {
						$c["col_type"] = ClubConfig::TYPE_DATE;
					}

					if ( $c["col_name"] == "shoe_size" ) {
						$c["col_type"] = ClubConfig::TYPE_LIST;
						$names = [ ];
						$values = [ ];
						for ( $j = 35; $j <= 48; $j++ ) {
							array_push( $names, $j );
							array_push( $values, $j );
						}
						$c["col_list_values"] = $this->converArray2Obj( $names, $values, $v );
					}

					/* if ( $c["col_name"] == "sub_group" ) {
					  $c["col_type"] = ClubConfig::TYPE_LIST;
					  $subs = $this->club->subs;
					  $names = [ ];
					  $values = [ ];
					  foreach ( $subs as $sub ) {
					  array_push( $names, $sub->sub_name );
					  array_push( $values, $sub->id );
					  }
					  $c["col_list_values"] = $this->converArray2Obj( $names, $values, $v );
					  } */

					if ( $c["col_name"] == "height" || $c["col_name"] == "weight" || $c["col_name"] == "morning_pulse" || $c["col_name"] == "emerge_cell" ) {
						$c["col_type"] = ClubConfig::TYPE_NUMBER;
					}

					if ( $c["col_name"] == "cell" ) {
						$c["col_type"] = ClubConfig::TYPE_CELLPHONE;
					}

					if ( $c["col_name"] == "email" ) {
						$c["col_type"] = ClubConfig::TYPE_EMAIL;
					}

					$c["value"] = $v;


					if ( $c["system"] === 0 && $c["col_type"] == ClubConfig::TYPE_LIST && $c["col_name"] != "sub_group" ) {
						if ( is_array( $c["col_list_values"] ) ) {
							$c["col_list_values"] = $c["col_list_values"];
						} else {
							$list_values = str_replace( "，", ",", $c["col_list_values"] );
							$list_values = str_replace( "`", ",", $list_values );
							$list_values = explode( ",", $list_values );
							$c["col_list_values"] = $this->converArray2Obj( $list_values, $list_values, $v );
						}
					}
					array_push( $result, $c );
					if ( $c["col_name"] == "id_image" ) {
						array_push( $result, ["col_name" => "id_copy_back", "col_title" => $c["col_title"] . "反面", "visible" => $c["visible"], "optional" => $c["optional"], "col_type" => $c["col_type"],
							"value" => $memberArray["id_copy_back"]
						] );
					}
				}
			}
			$pay = $member->getNeedpay();
			if ( $this->club->is_collect_member_fee && $member->member_status == ClubMember::STATUS_NORMAL ) {
				$newly_pay = ClubMemberPayment::getNewlyPay( $this->user->uid, $this->club->clubid );
			}
			return ["member" => $memberArray, "configs" => $result, "pay" => ArrayHelper::toArray( $pay ), "newPay" => ArrayHelper::toArray( $newly_pay ) ];
		}
	}

	private function converArray2Obj( $name, $value, $select ) {
		$array = [ ];
		for ( $i = 0; $i != count( $name ); $i++ ) {
			$tmp = new \stdClass();
			$tmp->value = $value[$i];
			$tmp->text = $name[$i];
			if ( $select == $tmp->value ) {
				$tmp->selected = "1";
			}
			array_push( $array, $tmp );
		}
		return $array;
	}

	public function actionGetclubwithinfo() {
		$createUser = UserMaster::findOne( $this->club->uid );
		$member = false;
		if ( $this->user ) {
			$member = ClubMember::getNormalMember( $this->club->clubid, $this->user->uid );
			if ( $member ) {
				$member = true;
			} else {
				$member = false;
			}
		}
		$result = [
			"club" => ArrayHelper::toArray( $this->club ),
			"owner" => ["uid" => $createUser->uid, "nick_name" => $createUser->nick_name, "user_face" => Util::getUserFace( $createUser->user_face ), "gender" => $createUser->user_gender ],
			"total" => count( $this->club->members ),
			"member" => $member
		];
		CustomHelper::RetrunJson( $result );
	}

	public function actionGetclub() {
		$club = ArrayHelper::toArray( $this->club );
		$club['sub'] = ClubSub::fetchAllByClubIdFormatToOption( $this->club->clubid );
		CustomHelper::RetrunJson( $club );
	}

	public function actionAnnouncements() {
		$anns = Announcement::find()->where( ["clubid" => $this->club->clubid, "status" => Announcement::STATUS_NORMAL ] )->with( "user" )->orderBy( "create_time desc" )->all();
		$result = [ ];
		foreach ( $anns as $ann ) {
			$user = $ann->user;
			$ann = ArrayHelper::toArray( $ann );
			$ann["create_time"] = date( "Y-m-d", $ann["create_time"] );
			$ann["user"] = ["nick_name" => $user->nick_name, "face" => $user->user_face ];
			array_push( $result, $ann );
		}
		CustomHelper::RetrunJson( $result );
	}

	public function actionAnnouncementinfo() {
		$id = \Yii::$app->request->post( "id", "" );
		$ann = Announcement::findOne( ["id" => $id, "status" => Announcement::STATUS_NORMAL ] );
		if ( $ann ) {
			$ann->views = $ann->views + 1;
			$ann->save();
			$user = $ann->user;
			$club = $ann->club;
			$ann = ArrayHelper::toArray( $ann );
			$ann["create_time"] = date( "Y-m-d", $ann["create_time"] );
			$ann["user"] = ["nick_name" => $user->nick_name, "face" => $user->user_face ];
			$ann["club"] = ArrayHelper::toArray( $club );
		}
		CustomHelper::RetrunJson( $ann );
	}

}
