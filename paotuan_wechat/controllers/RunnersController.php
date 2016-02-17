<?php

namespace paotuan_wechat\controllers;

use common\component\CustomHelper;
use common\component\UpYun;
use common\models\UserMaster;
use common\models\UserPaper;
use paotuan_wechat\component\PaotuanController;
use paotuan_wechat\component\Util;
use paotuan_wechat\models\Activity;
use paotuan_wechat\models\Club;
use paotuan_wechat\models\ClubMember;
use paotuan_wechat\models\CreditLog;
use paotuan_wechat\models\Mileage;
use paotuan_wechat\models\MileageAlbum;
use paotuan_wechat\models\Partner;
use paotuan_wechat\models\PartnerCoupon;
use paotuan_wechat\models\UploadForm;
use paotuan_wechat\models\UserMileageTarget;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class RunnersController extends PaotuanController {

	public function actionMileages() {
		\Yii::$app->response->redirect( "/wechat/#/runner/sign" );
	}

	private function processMileages() {
		$target = UserMileageTarget::getTarget( $this->user->uid, date( "Y-m" ) );
		return $this->renderPartial( 'mileage', [
					'user' => $this->user,
					'target' => $target,
					"signPackage" => Util::getJssdkParams()
				] );
	}

	public function actionIndex() {
		// $this->user = UserMaster::findOne(277);
		if ( $this->user ) {
			$mileages = Mileage::find()->where( [
						"uid" => $this->user->uid
					] )->orderBy( "mileage_date desc" )->all();
			$totalMileages = 0;
			foreach ( $mileages as $mileage ) {
				$totalMileages = $totalMileages + $mileage->mileage;
			}
			$members = $this->user->clubMembers;
			$clubs = array();
			foreach ( $members as $member ) {
				array_push( $clubs, $member->club );
			}
			return $this->renderPartial( 'index', [
						'user' => $this->user,
						"signPackage" => Util::getJssdkParams(),
						"mileages" => $mileages,
						"chartData" => $this->convertToChart( $this->getChartData( $this->user->uid ) ),
						"total" => $totalMileages,
						"clubs" => $clubs
					] );
		} else {
			return $this->renderPartial( 'index', [
						'user' => $this->user
					] );
		}
	}

	public function actionDeleterecent() {
		$id = \Yii::$app->request->post( "id" );
		Mileage::deleteAll( [
			"uid" => $this->user->uid,
			"id" => $id
		] );
		CustomHelper::RetrunJson( [
			"status" => 1
		] );
	}

	public function actionMe() {
		\Yii::$app->response->redirect( "/wechat/#/runner/me" );
	}

	private function processMe() {
		if ( $this->user ) {
			$mileages = Mileage::find()
							->where( ["uid" => $this->user->uid ] )
							->orderBy( "mileage_date desc" )->all();
			$totalMileages = 0;
			foreach ( $mileages as $mileage ) {
				$totalMileages = $totalMileages + $mileage->mileage;
			}
			$members = $this->user->clubMembers;
			$clubs = array();
			foreach ( $members as $member ) {
				array_push( $clubs, $member->club );
			}
			return $this->renderPartial( 'me', [
						'user' => $this->user,
						"signPackage" => Util::getJssdkParams(),
						"mileages" => $mileages,
						"chartData" => $this->convertToChart( $this->getChartData( $this->user->uid ) ),
						"total" => $totalMileages,
						"clubs" => $clubs,
					] );
		} else {
			return $this->renderPartial( 'me', ['user' => $this->user ] );
		}
	}

	public function actionUserinfo( $params ) {
		if ( $params ) {
			$user = UserMaster::findOne( trim( $params ) );
			if ( !$user ) {
				throw new NotFoundHttpException ();
			}
			$this->checkWechatOauth( "/runners/userinfo/{trim($params)}" );
			return $this->processUserInfo( $user );
		}
	}

	public function actionCheckin() {
		$ticket = \Yii::$app->request->get( "ticket", null );
		if ( $ticket ) {
			\Yii::$app->response->redirect( "/wechat/#/activity/list?ticket=$ticket" );
		} else {
			\Yii::$app->response->redirect( "/wechat/#/activity/checkin" );
		}
	}

	public function processUserInfo( $user ) {
		$mileages = Mileage::find()->where( [
					"uid" => $user->uid
				] )->orderBy( "mileage_date desc" )->all();
		$totalMileages = 0;
		foreach ( $mileages as $mileage ) {
			$totalMileages = $totalMileages + $mileage->mileage;
		}
		$members = $user->clubMembers;
		$clubs = array();
		foreach ( $members as $member ) {
			array_push( $clubs, $member->club );
		}
		return $this->renderPartial( 'me', [
					'user' => $user,
					"infoUser" => $this->user,
					"signPackage" => Util::getJssdkParams(),
					"mileages" => $mileages,
					"chartData" => $this->convertToChart( $this->getChartData( $user->uid ) ),
					"total" => $totalMileages,
					"clubs" => $clubs
				] );
	}

	public function actionSign() {
		$mileage = new Mileage ();
		$result = array( "status" => 0 );
		if ( isset( \Yii::$app->session ["openid"] ) ) {
			// $user = Util::getUserFromSession ();
			// $user=UserMaster::findOne(245);
			if ( $mileage->load( \Yii::$app->request->post() ) ) {
				$mileage->uid = $this->user->uid;
				if ( $mileage->validate() ) {
					if ( $mileage->mileage_date == "today" ) {
						$mileage->mileage_date = date( "Y-m-d" );
						$mileage->mileage_year = date( "Y" );
						$mileage->mileage_month = date( "Y-m" );
						$date = date( "d" );
						$mileage->mileage_week = $mileage->mileage_year . "-" . date( "W" );
					} elseif ( $mileage->mileage_date == "yestoday" ) {
						$mileage->mileage_date = date( "Y-m-d", strtotime( "-1 day" ) );
						$mileage->mileage_year = date( "Y", strtotime( "-1 day" ) );
						$mileage->mileage_month = date( "Y-m", strtotime( "-1 day" ) );
						$date = date( "d", strtotime( "-1 day" ) );
						$mileage->mileage_week = $mileage->mileage_year . "-" . date( "W", strtotime( $mileage->mileage_date ) );
					} else {
						$mileage->mileage_date = date( "Y-m-d", strtotime( "-2 day" ) );
						$mileage->mileage_year = date( "Y", strtotime( "-2 day" ) );
						$mileage->mileage_month = date( "Y-m", strtotime( "-2 day" ) );
						$date = date( "d", strtotime( "-2 day" ) );
						$mileage->mileage_week = $mileage->mileage_year . "-" . date( "W", strtotime( $mileage->mileage_date ) );
					}
					$mileage->mileage = round( $mileage->mileage, 2 );
					if ( !empty( $mileage->duration ) && $mileage->duration != 0 ) {
						$r = ceil( $mileage->duration / $mileage->mileage );
						$mileage->pace = Util::getTimeFromSec( $r );
						$mileage->format_duration = Util::getTimeFromSec( $mileage->duration );
					}
					$mileage_image = \Yii::$app->request->post( "mileage_image" );
					if ( !empty( $mileage_image ) ) {
						$mileage_image = explode( ",", $mileage_image );
						$trans = \Yii::$app->db->beginTransaction();
						$flag = true;
						if ( $mileage->save() ) {
							foreach ( $mileage_image as $image ) {
								$img = new MileageAlbum ();
								$img->image_url = $image;
								$img->mileage_id = $mileage->id;
								if ( !$img->save() ) {
									$flag = false;
									break;
								}
							}
							if ( $flag ) {
								$trans->commit();
								$result ["status"] = 1;
								$result ["id"] = $mileage->id;
							} else {
								$result ["status"] = 0;
								$result ["msg"] = "系统错误！";
							}
						} else {
							$trans->rollBack();
							$result ["status"] = 0;
							$result ["msg"] = "系统错误！";
						}
					} else {
						if ( $mileage->save() ) {
							$result ["status"] = 1;
							$result ["id"] = $mileage->id;
						} else {
							$result ["status"] = 0;
							$result ["msg"] = "系统错误！";
						}
					}
				} else {
					$result ["msg"] = $this->getStringFromError( $mileage->errors );
				}
			}
		} else {
			$result ["msg"] = "登录超时";
		}
		CustomHelper::RetrunJson( $result );
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

	private function getChartData( $uid, $type = "seven" ) {
		switch ( $type ) {
			case "seven" : {
					return Mileage::getMileages( $uid, "mileage_date" );
					break;
				}
			case "week" : {
					return Mileage::getMileages( $uid, "mileage_week", 0, 4 );
					break;
				}
			case "month" : {
					// $startDate = date ( "Y-01-01" );
					// $endDate = date ( "Y-12-31" );
					return Mileage::getMileages( $uid, "mileage_month", 0, 4 );
					// return Mileage::getMileagesByDate ( $uid, $startDate, $endDate, "mileage_month" );
					break;
				}
			case "year" : {
					return Mileage::getMileages( $uid, "mileage_year", 0, 4 );
					break;
				}
		}
	}

	private function convertToChart( $mileages, $type = "seven" ) {
		$xAxis = array();
		$series = array();
		switch ( $type ) {
			case "seven" : {
					foreach ( $mileages as $mileage ) {
						$date = date( "m-d", strtotime( $mileage->mileage_date ) );
						array_unshift( $xAxis, $date );
						array_unshift( $series, $mileage->mileage );
					}
					break;
				}
			case "week" : {
					foreach ( $mileages as $mileage ) {
						$date = Util::getWeekFirstDay( strtotime( $mileage->mileage_date ) );
						$nextDate = Util::getWeekLastDay( strtotime( $mileage->mileage_date ) );
						// $month = explode ( "-", $mileage->mileage_week );
						// $startDate = Util::getMonthFirstDay (strtotime($month[0]."-".$month[1]."-01"));
						// if ($month [2] == 1) {
						// $date = date ( "m-d", strtotime ( $startDate ) );
						// $nextDate = date ( "m-d", strtotime ( "+6 days", strtotime ( $startDate ) ) );
						// } elseif ($month [2] == 2) {
						// $date = date ( "m-d", strtotime ( "+7 days", strtotime ( $startDate ) ) );
						// $nextDate = date ( "m-d", strtotime ( "+13 days", strtotime ( $startDate ) ) );
						// } elseif ($month [2] == 3) {
						// $date = date ( "m-d", strtotime ( "+14 days", strtotime ( $startDate ) ) );
						// $nextDate = date ( "m-d", strtotime ( "+20 days", strtotime ( $startDate ) ) );
						// } elseif ($month [2] == 4) {
						// $date = date ( "m-d", strtotime ( "+21 days", strtotime ( $startDate ) ) );
						// $nextDate = date ( "m-d", strtotime ( "+27 days", strtotime ( $startDate ) ) );
						// } elseif ($month [2] == 5) {
						// $date = date ( "m-d", strtotime ( "+28 days", strtotime ( $startDate ) ) );
						// $nextDate = date ( "m-d", strtotime ( strtotime($month[0]."-".$month[1]) ) );
						// }
						array_unshift( $xAxis, $date );
						array_unshift( $series, $mileage->mileage );
					}

					break;
				}
			case "month" : {
					foreach ( $mileages as $mileage ) {
						$date = date( "m", strtotime( $mileage->mileage_month ) );
						array_unshift( $xAxis, $date );
						array_unshift( $series, $mileage->mileage );
					}
					break;
				}
			case "year" : {
					foreach ( $mileages as $mileage ) {
						$date = date( "Y", strtotime( $mileage->mileage_year ) );
						array_unshift( $xAxis, $date );
						array_unshift( $series, $mileage->mileage );
					}
					break;
				}
		}

		return array(
			"xAxis" => json_encode( $xAxis ),
			"series" => json_encode( $series )
		);
	}

	public function actionMileageinfo( $params ) {
		if ( isset( $params ) ) {
			$mi = Mileage::findOne( $params );
			if ( $mi != null ) {
				$mileage_count = Mileage::findBySql( 'select count(1) from mileage where uid=' . $mi->uid . ' and mileage_date>="2015-06-05" and status=0' )->count();
				// $Partner = Partner::findOne(['uid'=>$this->user->uid]);
				return $this->renderPartial( 'mileageInfo', [
							'mi' => $mi,
							'mileage_count' => $mileage_count,
							// 'partner' => $Partner,
							"signPackage" => Util::getJssdkParams()
						] );
			} else {
				\Yii::$app->response->redirect( "/notfound.html" );
			}
		}
	}

	public function actionUploadimg() {
		$uploadForm = new UploadForm ();
		if ( Yii::$app->request->isPost ) {
			$result = array(
				"status" => 0
			);
			try {
				$type = \Yii::$app->request->post( "img_type" );
				$file_id = \Yii::$app->request->post( "file_id" );
				if ( count( $_FILES ) != 0 ) {
					$upyunInfo = \Yii::$app->params ['upyun'];
					$upyun = new UpYun( $upyunInfo ['bucketname'], $upyunInfo ['username'], $upyunInfo ['password'] );
					foreach ( $_FILES as $file ) {
						if ( count( $file ["tmp_name"] ) > 0 ) {
							for ( $i = 0; $i != count( $file ["tmp_name"] ); $i ++ ) {
								$tmpName = $file ["tmp_name"] [$i];
								$fileName = $file ["name"] [$i];
								$ex = substr( $fileName, stripos( $fileName, "." ) );
								$tmpfile = CustomHelper::randomPassword( 10 ) . $ex;
								$path = "/{$type}/" . time() . "/{$tmpfile}";
								$info = $upyun->writeFile( $path, fopen( $tmpName, 'r+' ), true );
								if ( !empty( $info ) ) {
									$result ["status"] = 1;
									$result ["image"] = $path;
									$result ["id"] = $file_id;
									echo "<div class='uploadResult'>" . json_encode( $result ) . "</div>";
								}
							}

							// $tmpfile = CustomHelper::randomPassword ( 10 ) . "." . $file["extension"];
							// print_r($file);
							// $path = "/{$type}/{$file}";
							// $info = $upyun->writeFile ( $path, fopen ( $uploadForm->file->tempName, 'r+' ), true );
							// if (! empty ( $info )) {
							// $result ["status"] = 1;
							// $result ["image"] = $path;
							// $result ["id"] = $file_id;
							// echo "<div id='uploadResult'>" . json_encode ( $result ) . "</div>";
							// }
						}
					}
				}

				// $uploadForm->file = UploadedFile::getInstance ( $uploadForm, 'file' );
				// if ($uploadForm->file) {
				// $upyunInfo = \Yii::$app->params ['upyun'];
				// $upyun = new UpYun ( $upyunInfo ['bucketname'], $upyunInfo ['username'], $upyunInfo ['password'] );
				// $file = CustomHelper::randomPassword ( 10 ) . "." . $uploadForm->file->extension;
				// $path = "/{$type}/{$file}";
				// $info = $upyun->writeFile ( $path, fopen ( $uploadForm->file->tempName, 'r+' ), true );
				// if (! empty ( $info )) {
				// $result ["status"] = 1;
				// $result ["image"] = $path;
				// $result ["id"] = $file_id;
				// echo "<div id='uploadResult'>" . json_encode ( $result ) . "</div>";
				// }
				// }
			} catch (Exception $e) {
				echo "<div id='uploadResult'>" . json_encode( $result ) . "</div>";
			}
		}
	}

	public function actionXpbind() {
		CustomHelper::RetrunJson( ["status" => 0 ] );
	}

	// 打卡次数
	public function actionCountmileage() {
		if ( Yii::$app->request->isAjax ) {
			$response = [
				'status' => 1,
				'message' => '操作错误'
			];
			try {
				if ( !isset( \Yii::$app->session ["openid"] ) ) {
					throw new Exception( '登录超时' );
				}
				if ( $this->user ) {
					$count = Mileage::findBySql( 'select count(1) from mileage where uid=' . $this->user->uid . ' and status=0' )->count();
					$response = [
						'status' => 0,
						'result' => $count
					];
				} else {
					return $this->renderPartial( 'me', [
								'user' => $this->user
							] );
				}
			} catch (Exception $e) {
				Yii::error( '获取打卡次数错误' . $e->getMessage() );
				$response = [
					'status' => 1,
					'message' => $e->getMessage()
				];
			}
			CustomHelper::RetrunJson( $response );
		}
	}

	// 星之健身免费体测活动
	public function actionXzjsaction() {
		if ( Yii::$app->request->isAjax ) {
			$response = [
				'status' => 1,
				'message' => '操作错误'
			];
			try {
				if ( !isset( \Yii::$app->session ["openid"] ) ) {
					throw new Exception( '登录超时' );
				}
				$param = Yii::$app->request->post( 'Partner' );
				$uid = $this->user->uid;
				$Partner = Partner::create( $uid, $param );
				$response = [
					'status' => 0,
					'result' => $Partner
				];
			} catch (Exception $e) {
				Yii::error( '新增星之健身体测活动错误' . $e->getMessage() );
				$response = [
					'status' => 1,
					'message' => $e->getMessage()
				];
			}
			CustomHelper::RetrunJson( $response );
		}
	}

	// 星之健身运营活动展示
	public function actionOperatactive() {
		if ( isset( \Yii::$app->session ["openid"] ) ) {
			// $Coupon = PartnerCoupon::findOne(['uid'=>$this->user->uid]);
			$mileage_count = Mileage::findBySql( 'select count(1) from mileage where uid=' . $this->user->uid . ' and mileage_date>="2015-06-05" and status=0' )->count();
			// $Partner = Partner::findOne(['uid'=>$this->user->uid]);
			return $this->renderPartial( 'xzjsaction', [
						"signPackage" => Util::getJssdkParams(),
						'mileage_count' => $mileage_count
					] );
		} else {
			return $this->renderPartial( 'me', [
						'user' => $this->user
					] );
		}
	}

	// 申请星之健身活动优惠码
	public function actionXzjscoupon() {
		if ( Yii::$app->request->isAjax ) {
			$response = [
				'status' => 1,
				'message' => '操作错误'
			];
			try {
				if ( !isset( \Yii::$app->session ["openid"] ) ) {
					throw new Exception( '登录超时' );
				}
				$Coupon = PartnerCoupon::findOne( [
							'uid' => $this->user->uid
						] );
				if ( !$Coupon ) {
					$Coupon = PartnerCoupon::find()->where( "uid is null or uid=''" )->one();
					if ( !$Coupon ) {
						throw new Exception( '已发放完' );
					}
					$Coupon->uid = $this->user->uid;
					if ( !$Coupon->save( false ) ) {
						throw new Exception( '操作错误' );
					}
				}
				$response = [
					'status' => 0,
					'result' => $Coupon->coupon_code
				];
			} catch (Exception $e) {
				Yii::error( '申请星之健身活动优惠码错误' . $e->getMessage() );
				$response = [
					'status' => 1,
					'message' => $e->getMessage()
				];
			}
			CustomHelper::RetrunJson( $response );
		}
	}

	// 跑量打卡运营活动展示
	public function actionMileageactive() {
		return $this->renderPartial( 'mileageaction', [
					"signPackage" => Util::getJssdkParams()
				] );
	}

	public function actionMemileage() {
		if ( $this->user ) {
			$mileage = Mileage::getBaseMileages( $this->user->uid );
			$mileage["mileage"] = round( $mileage["mileage"], 2 );
			$mileage["month_target"] = 0;
			$target = UserMileageTarget::getTarget( $this->user->uid, date( "Y-m" ) );
			$curren_month = Mileage::find()->select( "sum(mileage) as mileage" )->where( ["mileage_month" => date( "Y-m" ), "uid" => $this->user->uid ] )->one();
			$mileage["curren_month"] = $curren_month->mileage;
			if ( $target && $target->target > 0 ) {
				$mileage["month_target"] = $target->target;
				$mileage["finish_percent"] = round( $mileage["curren_month"] / $mileage["month_target"], 2 ) * 100;
			}
			CustomHelper::RetrunJson( ["mileage" => $mileage ] );
		}
	}

	public function actionMetarget() {
		if ( $this->user ) {
			$target = UserMileageTarget::getTarget( $this->user->uid, date( "Y-m" ) );
			$user = ArrayHelper::toArray( $this->user, ["common\models\UserMaster" => ["uid", "user_face", "user_cell", "user_email", "nick_name" ] ] );
			$user["user_face_orginal"] = $user["user_face"];
			$user["user_face"] = Util::getUserFace( $user["user_face"] );
			$curren_month = Mileage::find()->select( "sum(mileage) as mileage" )->where( ["mileage_month" => date( "Y-m" ), "uid" => $this->user->uid ] )->one();
			$isSet = false;
			if ( $target && $target->target > 0 ) {
				$isSet = true;
				$target = $target->target;
			}
			CustomHelper::RetrunJson( ["user" => $user, "isSet" => $isSet, "current_month" => ArrayHelper::toArray( $curren_month ), "target" => $target ] );
		}
	}

	public function actionSettarget() {
		$result = ["status" => 0 ];
		if ( $this->user ) {
			$month = date( "Y-m" );
			$target_num = trim( \Yii::$app->request->post( "target", "" ) );
			if ( $target_num != "" && is_numeric( $target_num ) ) {
				$target = UserMileageTarget::getTarget( $this->user->uid, date( "Y-m" ) );
				if ( !$target ) {
					$target = new UserMileageTarget();
				}
				$target_num = doubleval( $target_num );
				if ( $target_num > 0 ) {
					$target->uid = $this->user->uid;
					$target->month = $month;
					$target_num = round( $target_num, 2 );
					$target->target = $target_num;
					$target->update_time = date( "Y-m-d H:i:s" );
					if ( $target->save( false ) ) {
						$result["status"] = 1;
						$result["data"] = $target_num;
					} else {
						throw new Exception( "数据库异常!" );
					}
				} else {
					$result["msg"] = "跑量目标只能大于0!";
				}
			} else {
				$result["msg"] = "跑量目标只能为数字!";
			}
		}
		CustomHelper::RetrunJson( $result );
	}

	public function actionClubs() {
		if ( $this->user ) {
			$clubs = Club::getUserClubs( $this->user->uid );
			if ( count( $clubs ) > 0 ) {
				$default = $clubs[0];
				$default["can_set"] = false;
				if ( $default["is_default"] == 1 ) {
					$default["next_set_default_time"] = date( "Y-m-d H:i:s", strtotime( $default["set_default_interval"] . " days", strtotime( $default["set_default_time"] ) ) );
					if ( $default["next_set_default_time"] < date( "Y-m-d H:i:s" ) ) {
						$default["can_set"] = true;
					}
				} else {
					$default["can_set"] = true;
				}
				$clubs[0] = $default;
			}
			CustomHelper::RetrunJson( $clubs );
		}
	}

	public function actionActs() {
		$offset = \Yii::$app->request->post( "offset", 0 );
		$limit = \Yii::$app->request->post( "limit", 20 );
		if ( $this->user ) {
			$acts = ArrayHelper::toArray( Activity::getUserActs( $this->user->uid, $offset, $limit ) );
			CustomHelper::RetrunJson( Activity::makeActs( $acts ) );
		}
	}

	public function actionCredits() {
		if ( $this->user ) {
			$members = $this->user->clubMembers;
			$credits = array();
			foreach ( $members as $m ) {
				$club = $m->club;
				if($club->club_name!=""){
					array_push( $credits, ["club_name" => $club->club_name, "club_logo" => $club->club_logo, "credit" => $m->credits, "club_eng" => $club->club_eng ] );
				}
			} 
			CustomHelper::RetrunJson( $credits );
		}
	}
	public function actionCreditsinfo() {
		if ( $this->user && $this->club ) {
			$offset = \Yii::$app->request->post( "offset", 0 );
			$limit = \Yii::$app->request->post( "limit", 20 );
			$credits = CreditLog::getCreditsList( $this->club->clubid, $this->user->uid, $offset, $limit );
			foreach ( $credits as $c ) {
				$c->record_time = date( "Y-m-d H:i:s", $c['record_time'] );
			}
			CustomHelper::RetrunJson( ArrayHelper::toArray( $credits ) );
		}
	}

	public function actionOwnclubs() {
		if ( $this->user ) {
			CustomHelper::RetrunJson( Club::getOwnClubs( $this->user->uid ) );
		}
	}

	public function actionDefaultclub() {
		$cm_id = trim( \Yii::$app->request->post( "cm_id", "" ) );
		$result = [ ];
		if ( $this->user ) {
			$set_default_time = date( "Y-m-d H:i:s" );
			$trans = \Yii::$app->db->beginTransaction();
			$cm = ClubMember::findOne( ["id" => $cm_id ] );
			if ( $cm ) {
				$cm->is_default = 1;
				$cm->set_default_time = $set_default_time;
				if ( $cm->save() ) {
					ClubMember::updateAll( ["is_default" => 0, "set_default_time" => null ], "uid=:uid and id!=:cm_id", [":uid" => $this->user->uid, ":cm_id" => $cm_id ] );
					$result["status"] = 1;
					$result["set_default_time"] = $set_default_time;
					$result["set_default_interval"] = $cm->set_default_interval;
					$result["next_set_default_time"] = date( "Y-m-d H:i:s", strtotime( $cm->set_default_interval . " days" ) );
					$result["can_set"] = false;
					$trans->commit();
				} else {
					$result["status"] = 0;
					$trans->rollBack();
				}
			}
		}
		CustomHelper::RetrunJson( $result );
	}

	public function actionGetdefaultclub() {
		$result = ["set_default" => false ];
		if ( $this->user ) {
			$cm = ClubMember::find()->where( "uid =:uid and is_default=1 and (member_status=:normal or member_status=:simple)" )
					->addParams( [":uid" => $this->user->uid, ":normal" => ClubMember::STATUS_NORMAL, ":simple" => ClubMember::STATUS_SIMPLE ] )
					->with( "club" )
					->one();
			if ( $cm && $cm->club ) {
				$result["set_default"] = true;
				$result["club_eng"] = $cm->club->club_eng;
			}
		}
		CustomHelper::RetrunJson( $result );
	}

	public function actionUpdateuserinfo() {
		if ( $this->user && $this->user->userInfo ) {
			$result["status"] = 1;
			$email = $this->user->userInfo->user_email;
			if ( $this->user->userInfo->load( Yii::$app->request->post() ) ) {
				if ( !empty( $this->user->userInfo->id_copy ) ) {
					$userPaper = UserPaper::findOne( [
								"uid" => $this->user->uid,
								"paper_type" => UserPaper::PAPER_TYPE_ID_COPY
							] );
					if ( $userPaper !== null ) {
						$userPaper->paper_url = CustomHelper::CreateImageUrl( $this->user->userInfo->id_copy );
						$userPaper->save();
					} else {
						UserPaper::addPaper( array(
							"uid" => $this->user->uid,
							"url" => CustomHelper::CreateImageUrl( $this->user->userInfo->id_copy )
								), "idcopy" );
					}
				}
				if ( !empty( $this->user->userInfo->id_copy_back ) ) {
					$userPaper_back = UserPaper::findOne( [
								"uid" => $this->user->uid,
								"paper_type" => UserPaper::PAPER_TYPE_ID_COPY_BACK
							] );
					if ( $userPaper_back !== null ) {
						$userPaper_back->paper_url = CustomHelper::CreateImageUrl( $this->user->userInfo->id_copy_back );
						$userPaper_back->save();
					} else {

						UserPaper::addPaper( array(
							"uid" => $this->user->uid,
							"url" => CustomHelper::CreateImageUrl( $this->user->userInfo->id_copy_back )
								), "idcopyback" );
					}
				}

				if ( !empty( $this->user->userInfo->health ) ) {
					$userPaper_medical = UserPaper::findOne( [
								"uid" => $this->user->uid,
								"paper_type" => UserPaper::PAPER_TYPE_HEALTH,
								"paper_status" => 0
							] );
					if ( $userPaper_medical !== null ) {
						$userPaper_medical->paper_url = CustomHelper::CreateImageUrl( $this->user->userInfo->health );
						$userPaper_medical->save();
					} else {
						UserPaper::addPaper( array(
							"uid" => $this->user->uid,
							"url" => CustomHelper::CreateImageUrl( $this->user->userInfo->medical_report )
								), "report" );
					}
				}

				if ( !empty( $this->user->userInfo->certs ) ) {
					$certs = explode( ",", $this->user->userInfo->certs );
					UserPaper::deleteAll( ["uid" => $this->user->uid, "paper_type" => 3 ] );
					foreach ( $certs as $cert ) {
						UserPaper::addPaper( ["uid" => $this->user->uid, "url" => CustomHelper::CreateImageUrl( $cert ) ], "cert" );
					}
				}

				if ( !empty( $this->user->userInfo->user_email ) && $email != $this->user->userInfo->user_email ) {
					$ue = UserMaster::findOne( ["user_email" => $this->user->userInfo->user_email ] );
					if ( $ue ) {
						$this->user->userInfo->user_email = $email;
						$result["status"] = 2;
						$result["msg"] = "已存在的邮箱地址，请重新输入";
					}
				}
				$this->user->userInfo->save( false );
			}
			CustomHelper::RetrunJson( $result );
		}
	}

}
