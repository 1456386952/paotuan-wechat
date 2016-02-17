<?php

namespace paotuan_wechat\component;

use common\component\BindComponent;
use common\component\CustomHelper;
use common\component\UpYun;
use common\component\WechatSDK\Wechat;
use common\models\UserBindLog;
use paotuan_wechat\component\Util;
use paotuan_wechat\models\Club;
use paotuan_wechat\models\ClubSub;
use paotuan_wechat\models\Mileage;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class PaotuanController extends Controller {

	public $enableCsrfValidation = false;
	protected $user = false;
	protected $wechat;
	protected $club;
	protected $openid;
	protected $trans = null;

	public function beforeAction( $action ) {
		$exAction = ["memberpaydone", "findclub", "findclub1", "find", "new", "createclub", "uploadimg", "checkmember", "announcementinfo" ];
		$controller = $action->controller->id;
		if ( $controller == "clubs" && in_array( $action->id, $exAction ) === false ) {
			$club = \Yii::$app->request->get( "club" );
			$this->club = Club::findClubWithMemberSum( $club );
			if ( !$this->club ) {
				throw new NotFoundHttpException();
			}
		}
		if ( $action->id != "createclub" && isset( $_REQUEST["club_eng"] ) ) {
			$club = $_REQUEST["club_eng"];
			if ( $club ) {
				$this->club = Club::find()->where( "club_eng=:club_eng and club_type=" . Club::CLUB_TYPE_CLUB . " and (club_status=" . Club::CLUB_STATUS_NORMAL . " or club_status=" . Club::CLUB_STATUS_PROCESS . ")", [":club_eng" => $club ] )->one();
				if ( !$this->club ) {
					throw new NotFoundHttpException();
				}
			}
		}

		$session = \Yii::$app->session;
		if ( !$session->isActive ) {
			$session->open();
		}
		$openid = \Yii::$app->request->post( "openid", null );
		if ( $openid ) {
			$session["openid"] = $openid;
		}
		if ( isset( \Yii::$app->session ["openid"] ) ) {
			$this->openid = \Yii::$app->session ["openid"];
			$this->user = Util::getUserFromSession();
		}
		$this->wechat = Wechat::getDefaultInstance();
		return parent::beforeAction( $action );
	}

	public function runAction( $id, $params = [ ] ) {
		try {
			return parent::runAction( $id, $params );
		} catch (NotFoundHttpException $nfe) {
			if ( $this->trans != null ) {
				$this->trans->rollBack();
			}
			throw $nfe;
		} catch (Exception $e) {
			\Yii::error( $e->getMessage() );
			if ( $this->trans != null ) {
				$this->trans->rollBack();
			}
			throw new HttpException( 500 );
		}
	}

	public function checkWechatOauth( $oAuthUrl ) {
		$code = \Yii::$app->request->get( "code", "" );
		$type = \Yii::$app->request->get( 'type' );
		if ( !$this->user || empty( $this->user->nick_name ) ) {
			if ( !empty( $code ) && !$type ) {
				$this->user = Util::getWechatUser();
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
				\Yii::$app->response->redirect( $this->wechat->getOauthRedirect( $redirectUrl ) );
				\Yii::$app->end();
			}
		}
	}

	public function actionGetrank() {
		$type = \Yii::$app->request->post( "type", "seven" );
		$offset = \Yii::$app->request->post( "offset", 0 );
		$limit = \Yii::$app->request->post( "limit", 8 );
		$uids = array();
		foreach ( $this->club->members as $member ) {
			if ( array_search( $member->uid, $uids ) === false ) {
				array_push( $uids, $member->uid );
			}
		} 
		CustomHelper::RetrunJson( $this->convertToRankChart( $this->getRankChartData(  $this->club->clubid,$uids, $offset, $type,$club_id ) ) );
	}

	private function getRankChartData($club_id,$uids, $offset, $type = "seven" ) {
		switch ( $type ) {
			case "seven" :
				return Mileage::getRankData( $club_id,$uids, "mileage_date", 5, date( "Y-m-d" ), $offset );
				break;
			case "week" :
				return Mileage::getRankData($club_id, $uids, "mileage_week", 5, date( "Y" ) . "-" . date( "W" ), $offset );
				break;
			case "month" :
				return Mileage::getRankData( $club_id,$uids, "mileage_month", 5, date( "Y-m" ), $offset );
				break;
			case "year" :
				return Mileage::getRankData( $club_id,$uids, "mileage_year", 5, date( "Y" ), $offset);
				break;
		}
	}

	/**
	 * 获取跑量排行列表
	 */
	public function actionGetranklist() {
		$type = \Yii::$app->request->post( "type", "seven" );
		$offset = \Yii::$app->request->post( "offset", 0 );
		$limit = \Yii::$app->request->post( "limit", 5 );
		$view = \Yii::$app->request->post( "view", 'runner' );
		$mileageType = \Yii::$app->request->post( "mileage_type", 'run' );
		if ( empty( $view ) or $view == 'runner' ) {
			$uids = array();
			foreach ( $this->club->members as $member ) {
				if ( array_search( $member->uid, $uids ) === false ) {
					array_push( $uids, $member->uid );
				}
			}
			CustomHelper::RetrunJson( $this->convertToRankList( $this->getRankListData( $this->club->clubid,$uids, $offset, $limit, $type, $mileageType ), $type, $offset ) );
		} elseif ( $view == 'sub' ) {
			CustomHelper::RetrunJson( $this->convertToSubRankList( $this->getRankListDataBySub( $this->club->clubid, $offset, $limit, $type ), $type, $offset ) );
		}
	}

	private function getRankListDataBySub( $clubId, $offset, $limit, $type = "seven", $mileageType = 'run' ) {
		switch ( $type ) {
			case "seven" :
				return Mileage::getRankDataBySub( $clubId, "mileage_date", $limit, date( "Y-m-d" ), $offset, $mileageType );
				break;
			case 'week':
				return Mileage::getRankDataBySub( $clubId, "mileage_week", $limit, date( "Y" ) . "-" . date( "W" ), $offset, $mileageType );
				break;
			case "month" :
				return Mileage::getRankDataBySub( $clubId, "mileage_month", $limit, date( "Y-m" ), $offset, $mileageType );
				break;
			case "year" :
				return Mileage::getRankDataBySub( $clubId, "mileage_year", $limit, date( "Y" ), $offset, $mileageType );
				break;
		}
	}

	private function getRankListData( $club_id,$uids, $offset, $limit, $type = "seven", $mileageType = 'run' ) {
		switch ( $type ) {
			case "seven" :
				return Mileage::getRankData( $club_id,$uids, "mileage_date", $limit, date( "Y-m-d" ), $offset, $mileageType );
				break;
			case "week" :
				return Mileage::getRankData( $club_id,$uids, "mileage_week", $limit, date( "Y" ) . "-" . date( "W" ), $offset, $mileageType );
				break;
			case "month" :
				return Mileage::getRankData( $club_id,$uids, "mileage_month", $limit, date( "Y-m" ), $offset, $mileageType );
				break;
			case "year" :
				return Mileage::getRankData( $club_id,$uids, "mileage_year", $limit, date( "Y" ), $offset, $mileageType );
				break;
		}
	}

	private function convertToSubRankList( $mileages, $type, $offset ) {
		$result = array();
		if ( count( $mileages ) > 0 ) {
			if ( $offset == 0 ) {
				$no1 = $mileages[0];
				$_SESSION[$type . "_mileage_rank"] = $no1;
			} else {
				$no1 = $_SESSION[$type . "_mileage_rank"];
			}
			for ( $i = 0; $i != count( $mileages ); $i++ ) {
				$mileage = $mileages[$i];
				if ( $mileage['mileage'] and ! empty( $mileage['sub_group'] ) ) {
					$tmp = [
						'subname' => ClubSub::fetchSubNameById( $mileage['sub_group'] ),
						'rank' => $i + $offset + 1,
						'mileage' => round( $mileage['mileage'], 2 ),
						'percent' => $mileage['mileage'] / $no1['mileage'] * 100
					];
					array_push( $result, $tmp );
				}
			}
		}
		return $result;
	}

	private function convertToRankList( $mileages, $type, $offset ) {
		$result = array();
		if ( count( $mileages ) > 0 ) {
			if ( $offset == 0 ) {
				$no1 = $mileages[0];
				$_SESSION[$type . "_mileage_rank"] = $no1;
			} else {
				$no1 = $_SESSION[$type . "_mileage_rank"];
			}
			for ( $i = 0; $i != count( $mileages ); $i++ ) {
				$mileage = $mileages[$i];
				if ( $mileage->mileage ) {
					$tmp = ArrayHelper::toArray( $mileage );
					$tmp["user"] = ArrayHelper::toArray( $mileage->user, ["common\models\UserMaster" => ["uid", "nick_name", "user_face" ] ] );
					$tmp["user"]["user_face"] = Util::getUserFace( $tmp["user"]["user_face"] );
					$tmp["rank"] = $i + $offset + 1;
					$tmp["mileage"] = round( $mileage->mileage, 2 );
					$tmp["percent"] = $mileage->mileage / $no1->mileage * 100;
					array_push( $result, $tmp );
				}
			}
		}
		return $result;
	}

	public function actionGetcellcode() {
		$cell = \Yii::$app->request->post( "cell" );
		$result = array(
			"status" => 0,
			"msg" => "手机号码有误!"
		);
		if ( !empty( $cell ) && CustomHelper::isCell( $cell ) ) {
			$param ['rand_code'] = CustomHelper::RandCode();
			$param ['cell'] = $cell;
			BindComponent::SendCellCode( $param );
			$UserBind = new UserBindLog ();
			$UserBind->uid = $this->user->uid;
			$UserBind->bind_type = 1;
			$UserBind->bind_info = $cell;
			$UserBind->bind_code = $param ['rand_code'];
			$UserBind->insert();
			$result ["status"] = 1;
			$result ["msg"] = "验证码发送成功";
		}
		CustomHelper::RetrunJson( $result );
	}

	private function convertToRankChart( $mileages ) {
		$yAxis = array();
		$series = array();
		for ( $i = 0; $i != count( $mileages ); $i++ ) {
			$mileage = $mileages[$i];
			if ( $mileage->mileage ) {
				array_unshift( $yAxis, $mileage->user->nick_name );
				array_unshift( $series, $mileage->mileage );
			}
		} 
		return array(
			"yAxis" => json_encode( $yAxis ),
			"series" => json_encode( $series )
		);
	}

	public function actionRecentmileages() {
		//$user = Util::getUserFromSession ();
		$offset = \Yii::$app->request->post( "offset", 0 );
		$limit = \Yii::$app->request->post( "limit", 8 );
		$clubid = \Yii::$app->request->post( "clubid" );
		$type = \Yii::$app->request->post( "mileage_type", 'run' );
		$club = Club::findOne( [
					"clubid" => $clubid
				] );
		//$user = trim(\Yii::$app->request->post("uid",""));
		$uids = array();
		if ( $club ) {
			foreach ( $club->members as $member ) {
				if ( array_search( $member->uid, $uids ) === false ) {
					array_push( $uids, $member->uid );
				}
			}
		} else {
			if ( $this->user ) {
				array_push( $uids, $this->user->uid );
			}
		}

		$mis = Mileage::getRecent($clubid, $uids, $offset, $limit, $type );
		$result = array();
		foreach ( $mis as $mi ) {
			$r = new \stdClass ();
			$r->mileage = ArrayHelper::toArray( $mi );
			//if(strtotime($mi->create_date)<=time()&&strtotime($mi->create_date)>=strtotime("-2 days")&&$this->user->uid==$mi->uid){
			$r->mileage["canDelete"] = true;
			//}
			if ( count( $mi->albums ) > 0 ) {
				$r->mileage ["albums"] = $mi->albums[0]->image_url;
				if ( stripos( $r->mileage ["albums"], "http://" ) === false ) {
					$r->mileage ["albums"] = "http://xiaoi.b0.upaiyun.com" . $r->mileage ["albums"] . "!mid";
				} elseif ( stripos( $r->mileage ["albums"], "http://xiaoi.b0.upaiyun.com" ) !== false ) {
					$r->mileage ["albums"] = $r->mileage ["albums"] . "!mid";
				}
			} else {
				$r->mileage ["albums"] = null;
			}
			if ( $club ) {
				$r->mileage ["user"] = ArrayHelper::toArray( $mi->user, ["common\models\UserMaster" => ["uid", "nick_name", "user_face" ] ] );
				$r->mileage ["user"]["user_face"] = Util::getUserFace( $r->mileage ["user"]["user_face"] );
			}
			switch ( $r->mileage ["from"] ) {
				case 2:
					$r->mileage ["from_text"] = "咕咚";
					break;
				case 3:
					$r->mileage ["from_text"] = "虎扑";
					break;
				case 4:
					$r->mileage ["from_text"] = "益动";
					break;
			}
			$r->mileage["mileage"] = round( $r->mileage["mileage"], 2 );
			array_push( $result, $r );
		}

		CustomHelper::RetrunJson( $result );
	}

	public function actionChartdata() {
		$type = \Yii::$app->request->post( "type", "seven" );
		$clubid = \Yii::$app->request->post( "clubid" );
		$mileage_type = \Yii::$app->request->post( "mileage_type", 'run' );
		$club = Club::findOne( ["clubid" => $clubid ] );
		$user = trim( \Yii::$app->request->post( "uid", "" ) );
		$uids = array();
		if ( $club ) {
			foreach ( $club->members as $member ) {
				if ( array_search( $member->uid, $uids ) === false ) {
					array_push( $uids, $member->uid );
				}
			}
		} else {
			if ( $user ) {
				array_push( $uids, $user );
			} else {
				if ( $this->user ) {
					array_push( $uids, $this->user->uid );
				}
			}
		}
		CustomHelper::RetrunJson( $this->convertToChart( $this->getChartData( $clubid,$uids, $type, $mileage_type ), $type ) );
	}

	private function getChartData($clubid, $uid, $type = "seven", $mileage_type = 'run' ) {
		switch ( $type ) {
			case "seven" :
				return Mileage::getMileages( $clubid,$uid, "mileage_date", 0, 7, $mileage_type );
				break;
			case "week" :
				return Mileage::getMileages( $clubid,$uid, "mileage_week", 0, 5, $mileage_type );
				break;
			case "month" :
				return Mileage::getMileages( $clubid,$uid, "mileage_month", 0, 5, $mileage_type );
				break;
			case "year" :
				return Mileage::getMileages( $clubid,$uid, "mileage_year", 0, 4, $mileage_type );
				break;
		}
	}

	private function convertToChart( $mileages, $type = "seven" ) {
		$xAxis = array();
		$series = array();
		$currentYear = date( "Y" );
		switch ( $type ) {
			case "seven" : {
					foreach ( $mileages as $mileage ) {
						$year = date( "Y", strtotime( $mileage->mileage_date ) );
						if ( $currentYear != $year ) {
							$date = $mileage->mileage_date;
						} else {
							$date = date( "m-d", strtotime( $mileage->mileage_date ) );
						}
						array_unshift( $xAxis, $date );
						array_unshift( $series, round( $mileage->mileage, 2 ) );
					}
					break;
				}
			case "week" : {
					foreach ( $mileages as $mileage ) {
						$date = Util::getWeekFirstDay( strtotime( $mileage->mileage_date ) );
						$dataYear = date( "Y", strtotime( $date ) );
						if ( $currentYear == $dataYear ) {
							$date = date( "m-d", strtotime( $date ) );
						}
						array_unshift( $xAxis, $date );
						array_unshift( $series, round( $mileage->mileage, 2 ) );
					}
					break;
				}
			case "month" : {
					foreach ( $mileages as $mileage ) {
						$year = date( "Y", strtotime( $mileage->mileage_month . "-01" ) );
						if ( $currentYear == $year ) {
							$date = date( "m", strtotime( $mileage->mileage_month ) );
						} else {
							$date = date( "Y-m", strtotime( $mileage->mileage_month ) );
						}
						array_unshift( $xAxis, $date );
						array_unshift( $series, round( $mileage->mileage, 2 ) );
					}
					break;
				}
			case "year" : {
					foreach ( $mileages as $mileage ) {
						$date = date( "Y", strtotime( $mileage->mileage_year . "-01-01" ) );
						array_unshift( $xAxis, $date );
						array_unshift( $series, round( $mileage->mileage, 2 ) );
					}
					break;
				}
		}

		return array(
			"xAxis" => json_encode( $xAxis ),
			"series" => json_encode( $series )
		);
	}

	public function actionUploadimg() {
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
						if ( is_array( $file["tmp_name"] ) ) {
							for ( $i = 0; $i != count( $file["tmp_name"] ); $i++ ) {
								$tmpName = $file["tmp_name"][$i];
								$fileName = $file["name"][$i];
								$ex = substr( $fileName, stripos( $fileName, "." ) );
								$tmpfile = CustomHelper::randomPassword( 10 ) . $ex;
								$path = "/{$type}/{$tmpfile}";
								$info = $upyun->writeFile( $path, fopen( $tmpName, 'r+' ), true );
								if ( !empty( $info ) ) {
									$result ["status"] = 1;
									$result ["image"] = $path;
									$result ["id"] = $file_id;
									echo "<div class='uploadResult'>" . json_encode( $result ) . "</div>";
								}
							}
						} else {
							$tmpName = $file["tmp_name"];
							$fileName = $file["name"];
							$ex = substr( $fileName, stripos( $fileName, "." ) );
							$tmpfile = CustomHelper::randomPassword( 10 ) . $ex;
							$path = "/{$type}/{$tmpfile}";
							$info = $upyun->writeFile( $path, fopen( $tmpName, 'r+' ), true );
							if ( !empty( $info ) ) {
								$result ["status"] = 1;
								$result ["image"] = $path;
								$result ["id"] = $file_id;
								echo "<div class='uploadResult'>" . json_encode( $result ) . "</div>";
							}
						}
					}
				}
			} catch (Exception $e) {
				echo "<div id='uploadResult'>" . json_encode( $result ) . "</div>";
			}
		}
	}

}
