<?php

namespace paotuan_wechat\controllers;

use Yii;
use paotuan_wechat\component\PaotuanController;
use paotuan_wechat\models\Activity;
use common\component\CustomHelper;
use paotuan_wechat\models\Club;
use paotuan_wechat\models\ClubMember;
use paotuan_wechat\models\ActivityLocation;
use yii\helpers\ArrayHelper;
use paotuan_wechat\models\paotuan_wechat\models;
use paotuan_wechat\models\ActivityClub;
use paotuan_wechat\component\Util;
use paotuan_wechat\models\Ibeacon;
use paotuan_wechat\component\WxUtil;
use paotuan_wechat\models\ActivityUser;
use paotuan_wechat\models\ActivityConfig;
use yii\web\NotFoundHttpException;
use common\models\OrderMaster;
use common\models\User;
use paotuan_wechat\models\CreditRule;

class ActivityController extends PaotuanController {
	private $act;
	public function beforeAction($action) {
		if(\Yii::$app->request->isAjax){
			$act_id = $_REQUEST["activity_id"];
		    if($act_id){
			$this->act = Activity::findOne(["act_id"=>$act_id]);
			if (!$this->act) {
				throw new NotFoundHttpException();
			}
		   }
		}else{
			throw new NotFoundHttpException();
		}
		return parent::beforeAction($action);
	}
	public function actionCancel() {
		$result = [ 
				"status" => 0 
		];
		if ($this->user) {
			if ($this->act) {
				$act = $this->act;
				if ($this->canCancel ( $act )) {
					if ($act->uid != $this->user->uid) {
						$result ["msg"] = "只有创建人才能取消活动";
					} else {
						$act->act_status = Activity::STATUS_DEL;
						if ($act->save ( false )) {
							$result ["status"] = 1;
							$regs = ActivityUser::find ()->where ( [ 
									"act_id" => $act->act_id,
									"isreg" => 1 
							] )->orderBy ( "reg_time desc" )->all ();
							$club = $act->club;
							$location = $act->location;
							$objects = array ();
							$params = array ();
							foreach ( $regs as $reg ) {
								$params ["openid"] = $this->openid;
								$params ["nick_name"] = $this->user->nick_name;
								$params ["act_name"] = $act->act_title;
								$params ["club_eng"] = $club->club_eng;
								$params ["act_time"] = date ( "m-d H:i", strtotime ( $act->act_start_time ) ) . "~" . date ( "H:i", strtotime ( $act->act_end_time ) );
								$params ["act_address"] = $location->name;
								$params ["club_name"] = $club->club_name;
								array_push ( $objects, WxUtil::getObject ( $params, WxUtil::API_ACTIVITY_CANCEL ) );
							}
							if (count ( $objects ) > 0) {
								WxUtil::sendWXTemplateMsg ( $objects );
							}
						} else {
							$result ["msg"] = "系统错误,请稍后重试!";
						}
					}
				} else {
					$result ["status"] = - 2;
					if ($act->act_status == Activity::STATUS_DEL) {
						$result ["msg"] = "活动已经取消";
					} else {
						$result ["msg"] = "签到已开始不能取消活动";
					}
				}
			}
		} else {
			$result ["status"] = - 1;
			$result ["msg"] = "您还没有登录";
		}
		CustomHelper::RetrunJson ( $result );
	}
	
	public function actionCannew(){
		$result=["status"=>0];
		if($this->user){
			$wxUser = WxUtil::getWxUserInfo($this->openid);
			if($wxUser){
				if($wxUser->subscribe==0){
					$result["status"]=-1;
				}else{
					if($this->club){
						$member = ClubMember::getNormalMember($this->club->clubid, $this->user->uid);
						if(!$member){
							$result["status"]=-2;
						}else{
							if($this->user->uid!=$this->club->uid&&$member->roleid!=1){
								$result["status"]=-3;
							}else{
								if(!$this->user->user_cell){
									$result["status"]=-4;
								}else{
									$result["status"]=1;
								}
							}
						}
					}
				}
			}else{
				$result["msg"]= "获取微信用户信息失败!";
			}
		}else{
			$result["msg"]= "获取用户信息失败!";
		}
		CustomHelper::RetrunJson($result);
	}
	
	public function actionPublish() {
		$result = [ 
				"status" => 0 
		];
		if ($this->user) {
			$club_id = \Yii::$app->request->post ( "club_id", null );
			$id = \Yii::$app->request->post ( "id", null );
			$act = new Activity ();
			if ($id) {
				$act = Activity::findOne ( $id );
			}
			if ($act->load ( Yii::$app->request->post () )) {
				$act->uid = $this->user->uid;
				if ($act->validate ()) {
					$act->act_check_start_time = date ( "Y-m-d H:i:s", strtotime ( "-15 minutes", strtotime ( $act->act_start_time ) ) );
					$act->act_check_end_time = $act->act_end_time;
					$act->register_end_time = date ( "Y-m-d H:i:s", strtotime ( "-1 seconds", strtotime ( $act->act_check_start_time ) ) );
					$this->trans = \Yii::$app->db->beginTransaction ();
					if ($act->save ()) {
						$act->check_code = CustomHelper::RandCode ( 3 ) . $act->act_id;
						$act->qrcode = CustomHelper::randomPassword ( 4 ) . $act->act_id;
						$ac = new ActivityClub ();
						$ac->act_id = $act->act_id;
						$ac->club_id = $club_id;
						if ($act->save () && $ac->save ()) {
							$result ["status"] = 1;
							$result ["act"] = ArrayHelper::toArray ( $act );
							$this->trans->commit ();
						} else {
							$this->trans->rollBack ();
						}
					} else {
						$this->trans->rollBack ();
					}
				}
			}
			if ($result ["status"] == 1) {
				$location = ActivityLocation::findOne ( $act->act_location );
				WxUtil::sendTemplateActPublish ( [ 
						"openid" => \Yii::$app->session ["openid"],
						"act_id" => $act->act_id,
						"name" => $act->act_title,
						"time" => date ( "m-d H:i", strtotime ( $act->act_start_time ) ) . "~" . date ( "H:i", strtotime ( $act->act_end_time ) ),
						"address" => $location->name 
				] );
			}
		}
		
		CustomHelper::RetrunJson ( $result );
	}
	
	// 生成活动签到码
	public function actionGencheckcode($id) {
		$result = [ 
				"status" => 0 
		];
		if ($this->user) {
			$act = Activity::findOne ( $id );
			if ($act) {
				if ($this->user->uid != $act->uid) {
					$result ["msg"] = "只有发起人才能生成！";
				} else {
					$result ["code"] = CustomHelper::RandCode ( 5 )  ;
					if ($act->check_code && $act->check_code == $result ["code"]) {
						while ( true ) {
							$result ["code"] = CustomHelper::RandCode ( 5) ;
							if ($act->check_code != $result ["code"]) {
								break;
							}
						}
					}
					$act->check_code = $result ["code"];
					if ($act->save ( false )) {
						$result ["status"] = 1;
					}
				}
			}
		} else {
			$result ["msg"] = "登陆超时,请返回微信重新进入!";
		}
		CustomHelper::RetrunJson ( $result );
	}
	public function actionGenqrcode() {
		$result = [ 
				"status" => 0 
		];
		if ($this->user) {
			$act_id = \Yii::$app->request->post ( "activity_id", null );
			$act = Activity::findOne ( $act_id );
			if ($act) {
				if ($this->user->uid != $act->uid) {
					$result ["msg"] = "只有发起人才能生成！";
				} else {
					$result ["code"] = CustomHelper::randomPassword ( 4 ) . $act->act_id;
					if ($act->qrcode && $act->qrcode == $result ["code"]) {
						while ( true ) {
							$result ["code"] = CustomHelper::randomPassword ( 4 ) . $act->act_id;
							if ($act->qrcode != $result ["code"]) {
								break;
							}
						}
					}
					$act->qrcode = $result ["code"];
					if ($act->save ( false )) {
						$result ["status"] = 1;
					}
				}
			}
		}
		CustomHelper::RetrunJson ( $result );
	}
	public function actionInfo() {  
		$act_id = \Yii::$app->request->post ( "activity_id", null );
		if ($act_id) {
			$act = Activity::findOne ( $act_id );//获取到活动的信息
                        $act_count = ActivityUser::findBySql("select count(1)  from activity_user where act_id={$act_id} and status=1")->count();
                        $act['reg_num'] = $act_count;
			//判断活动状态，0为删除 ，1为正常
			if ($act->act_status == Activity::STATUS_DEL) {
				//当为删除状态时的操作
				CustomHelper::RetrunJson ( ArrayHelper::toArray ( [ 
						"act" => $act ,
						"club"=>ArrayHelper::toArray($act->club,["paotuan_wechat\models\Club"=>["club_eng","club_logo","club_name","club_status"]]),
                         "location"=>ArrayHelper::toArray($act->location)			
				] ) );
				return;
			}
			$canReg = false;
			$reg_end=false;
			$check_start=false;
			$canCheck = false;
			$ismember = false;
			$needJoin = false;
			$canRegCancel=false;
			$needContact=false;
			$configs=[];
			$reg=null;
			$order=null;
			$act_user_cell=null;//活动发起人的信息
			$info = User::findBySql("select user_cell from user where uid=(select uid from activity where act_id={$act_id} )")->one();
			$act_user_cell = $info["user_cell"];
			unset($info);
			//报名时间register_end_time
			if (strtotime ( $act->register_end_time ) > time ()) {
				$canReg=true;
				//活动参加人数上限 	 0：无限制
				if ($act->total_limit > 0) {
					if ($act->reg_num >= $act->total_limit||$act->reg_submit_num>= $act->total_limit) {
						$canReg = false;
					}
				}
			}else{
				$reg_end=true;
			}
			//签到开始时间 签到结束时间
			if (strtotime ( $act->act_check_start_time ) <= time () && strtotime ( $act->act_check_end_time ) >= time ()) {
				$canCheck = true;
			}
			if(strtotime ( $act->act_check_start_time ) <= time ()){
				$check_start=true;
			}else{
				$check_start=false;
			}
			$location = $act->location;//活动地点信息
			$club = $act->club;//发布活动的跑团的信息
			$member = ClubMember::findOne ( [ 
					"club_id" => $club->clubid,
					"uid" => $this->user->uid 
			] );//跑团会员信息
			//判断跑者的状态是否为正常或者一般会员（3,4）
			if ($member && (($member->member_status == ClubMember::STATUS_NORMAL || $member->member_status == ClubMember::STATUS_SIMPLE))) {
				$ismember = true;
			}else{
				$ismember=false;
			}
			//是否限定范围 1 限定 0 不限定
			if ($canReg && $act->rang_limit == Activity::LIMIT_RANGE_CLUB) {
				if ($ismember) {
					//积分限制于个人积分
					if($act->credit_limit>0&&$member->credits<$act->credit_limit){
						$canReg=false;
					}
				}else{
					$needJoin = true;
				}
			}
			$canCancel = $this->canCancel ( $act );//签到时间
			$actObj = $act;
			$act = ArrayHelper::toArray ( $act );
			$act ["canCancel"] = $canCancel;
			$needBind=false;
			if ($this->user) {
				if($canReg){
					if($this->user->user_cell){
						$needBind=false;
					}else{
						$needBind=true;
					}
				}
				$reg = ActivityUser::find()
				          ->where("act_id=:act_id and uid = :uid and (isreg=1 or status=:status)")
				          ->addParams([":act_id"=>$act_id,":uid"=>$this->user->uid,":status"=>ActivityUser::STATUS_NEED_PAY])->one();
				$check = ActivityUser::findOne ( [ 
						"act_id" => $act_id,
						"uid" => $this->user->uid,
						"ischeckin" => 1 
				] );
				if ($this->user->uid == $act ["uid"]) {
					$act ["owner"] = true;
				} else {
					$act ["owner"] = false;
				}
				if($reg&&$reg->status==ActivityUser::STATUS_NEED_PAY&&$canReg){
					$act ["needPay"]=true;
					$order = $reg->order;
					if($order&&strtotime($order->expire_time)<=time()){
						$act ["needPay"]=false;
						$order->order_status =OrderMaster::STATUS_CANCEL;
						$reg->status =ActivityUser::STATUS_CANCEL;
						$reg->isreg=null;
		    			$reg->reg_time=null;
						if($this->act->register_fee>0||$this->act->register_fee_other>0){
							$this->act->reg_submit_num--;
						}else{
							$this->act->reg_num--;
						}
						$this->trans = \Yii::$app->db->beginTransaction();
						if($reg->save(false)&&$this->act->save(false)&&$order->save(false)){
							$this->trans->commit();
						}else{
							throw new Exception();
							$this->trans->rollBack();
						}
						$reg=null;
					}
				}else{
					$act ["needPay"]=false;
				}
				$act ["isReg"] = $reg ? true : false;
				$act ["isCheck"] = $check ? true : false;
			}
			
			$act["owner_name"]=$actObj->owner->nick_name;
			
			if (! $act ["isReg"] && $canReg) {
				$act ["canReg"] = true;
			}
			if (! $act ["isCheck"] && $canCheck) {
				$act ["canCheck"] = true;
			} else {
				$act ["canCheck"] = false;
			}
			if($act ["canReg"]){
				$user_id = $this->user->uid;
				$club_id = $club->clubid;
				$configs = $actObj->getConfigs();
			    $configs =$this->copyValue2Config($configs,$reg); 
			   $configs =$this->copyValue2Member($configs,$reg,$user_id,$club_id); 
			}
			$act ["isEnd"] = $this->isEnd ( $act ["act_end_time"] );
			$act["date"] = date("Y-m-d",strtotime($act["act_start_time"]));
			$act["act_start_time"] = date("H:i",strtotime($act["act_start_time"]));
			$act["act_end_time"] = date("H:i",strtotime($act["act_end_time"]));
			if(!$act ["isEnd"]&&$act ["isReg"]&&!$canCheck){
			    if($reg->status==ActivityUser::STATUS_NEED_PAY){
			    	$canRegCancel=true;
				}
				if($reg->status==ActivityUser::STATUS_NORMAL){
					if($reg->order&&$reg->order->amount>0){
						$canRegCancel=true;
						$needContact=true;
					}else{
						$canRegCancel=true;
					}
				}
				
			}
			// $act["regs"] = ActivityUser::find()->where(["act_id" => $act_id,"isreg"=>1])->with("user")->count("id");
			// $act["checkins"] = ActivityUser::find()->where(["act_id" => $act_id,"ischeckin"=>1])->with("user")->count("id");
			$act ["needJoin"] = $needJoin;
			$act ["reg_end"] = $reg_end;
			$act ["check_start"] = $check_start;
			$act["canRegCancel"] = $canRegCancel;
			$act["needContact"] = $needContact;
			$act["act_user_cell"] = $act_user_cell;
// 			$act["fee"]=$act["register_fee"];
// 			if(!$ismember&&$act["register_fee_other"]>0){
// 				$act["fee"]=$act["register_fee_other"];
// 			}
            $creditRules=[];
            foreach ($actObj->creditRules as $c){
            	if($c->action==Util::CREDIT_EVENT_ACT_REG){
            		$creditRules["reg"] = ArrayHelper::toArray($c,["paotuan_wechat\models\CreditRule",["action","credits"]]);
            	}
            	if($c->action==Util::CREDIT_EVENT_ACT_SIGN){
            		$creditRules["sign"] = ArrayHelper::toArray($c,["paotuan_wechat\models\CreditRule",["action","credits"]]);
            	}
            }
			CustomHelper::RetrunJson ( ArrayHelper::toArray ( [ 
					"act" => $act,
					"reg_end"=>$reg_end,
					"location" => $location,
					"club" => $club,
					"ismember" => $ismember,
					"configs"=>$configs,
					"needBind"=>$needBind,
					"canRegCancel"=>$canRegCancel,
					"needContact"=>$needContact,
					"order"=>ArrayHelper::toArray($order==null?[]:$order) ,
					'credits'=>count($creditRules)>0?$creditRules:null
			] ) );
		}
	}
	
	
	public function actionCancelreg(){ 
		if($this->user){
			$result["status"]=0;
			    $au = ActivityUser::findOne(["act_id"=>$this->act->act_id,"uid"=>$this->user->uid]);
		    		if($this->act->register_fee>0||$this->act->register_fee_other>0){
		    				$this->act->reg_submit_num--;
		    			if($au->status==ActivityUser::STATUS_NORMAL){
		    				$this->act->reg_num--;
		    			}
		    	}else{
		    		$this->act->reg_num--;
		    	}
		    	$au->isreg=null;
		    	$au->reg_time=null;
		    	$au->status=ActivityUser::STATUS_CANCEL;
		    	$this->trans = \Yii::$app->db->beginTransaction();
		    	$order  = $au->order;
		    	if($au->save()&&$this->act->save()){
		    		if($order){
		    			$order->order_status=OrderMaster::STATUS_CANCEL;
		    			if(!$order->save()){
		    				throw new Exception();
		    			}
		    		}
		    		$member = ClubMember::getNormalMember($this->act->club->clubid,$this->user->uid);
		    		if($member){
		    			$r=Util::genCredit(Util::CREDIT_EVENT_ACT_REG, $this->act->club->clubid, $this->act->act_id, $this->user->uid,1);
		    			if($r){
		    				$this->trans->commit ();
		    				$result["status"]=1;
		    				$result["uid"] = $this->user->uid;
		    			}else{
		    				$this->trans->rollBack ();
		    				$result ["msg"] = "系统错误!Credit";
		    			}
		    		}else{
		    			$result["status"]=1;
		    			$result["uid"] = $this->user->uid;
		    			$this->trans->commit ();
		    		}
		    	}else{
		    		$this->trans->rollBack();
		    		$result["msg"]="系统错误";
		    	}
		    	CustomHelper::RetrunJson($result);
		}
	}
	
	private function copyValue2Config($configs,$reg){
		$configs = ArrayHelper::toArray($configs);
		$reg= ArrayHelper::toArray($reg);
		for($i=0;$i!=count($configs);$i++){
			$config = $configs[$i];
			if($config["col_type"]==ActivityConfig::TYPE_LIST){
				$list_values =explode("`", $config["col_list_values"]);
				$config["col_list_values"]=$this->converArray2Obj($list_values, $list_values,$v); 
			}
			if(count($reg)>0){
				$config["value"]=$reg[$config["col_name"]];
			}
			$configs[$i]=$config;
		}
		return $configs;
	}
	private function copyValue2Member($configs,$reg,$user_id,$club_id){
		$configs = ArrayHelper::toArray($configs);
		$reg= ArrayHelper::toArray($reg);
		for($i=0;$i!=count($configs);$i++){
			$config = $configs[$i];
			if($config["map"]!=""){
				list($table,$field) =explode(".", $config["map"]);   
				if($table=='user'){ 
					$que = User::find()->select($field)->where("uid={$user_id}")->one(); 
				}else if($table=='club_member'){ 
					$que = ClubMember::find()->select($field)->where("uid={$user_id} and club_id={$club_id}")->one(); 
				}
				$config["member_info"] = $que[$field];
			} 
			$configs[$i]=$config;
		}
		return $configs;
	}
	private function converArray2Obj($name,$value,$select){
		$array = [];
		for($i=0;$i!=count($name);$i++){
			$tmp = new \stdClass();
			$tmp->value=$value[$i];
			$tmp->text=$name[$i];
			if($select==$tmp->value){
				$tmp->selected="1";
			}
			array_push($array, $tmp);
		}
		return $array;
	}
	
	public function actionClublocation() {
		$result = [ 
				"status" => 0 
		];
		if ($this->club && $this->user) {
			$locations = ActivityLocation::find ()->where ( "(club_id=:clubid  or uid =" . $this->user->uid . ") and status=" . ActivityLocation::STATUS_NORMAL )->addParams ( [ 
					"clubid" => $this->club->clubid 
			] )->orderBy ( "type desc,create_time desc" )->all ();
			$locations = ArrayHelper::toArray ( $locations ); 
			for ($i=0;$i!=count($locations);$i++){
				$l = $locations[$i];
			if($l["type"]==ActivityLocation::TYPE_PER){
					$l["can_delete"]=true;
					$locations[$i]=$l;
				}
			}
			$result ["status"] = 1;
			$result ["ip"] = \Yii::$app->request->userIP;
			$result ["data"] = $locations;
		}
		CustomHelper::RetrunJson ( $result );
	}
	public function actionListbyclub() {
		$club_eng = \Yii::$app->request->post ( "club_eng", "" );
		$offset = \Yii::$app->request->post ( "offset", 0 );
		$limit = \Yii::$app->request->post ( "limit", 20 );
		if ($this->club && $this->user) {
			$acts = Activity::getClubActs ( $this->club->clubid, $this->user->uid, $offset, $limit );
			CustomHelper::RetrunJson ( Activity::makeActs ( $acts ) );
		}
	}
	private function canReg($act) {
		if ($act->act_status == Activity::STATUS_NORMAL && strtotime ( $act->register_end_time ) > time ()) {
			return true;
		}else {
			return false;
		}
	}
	private function canCancel($act) {
		$cancelDate = strtotime ( $act->act_check_start_time ); 
		if ($act->act_status == Activity::STATUS_NORMAL) {
			return true;
		} else {
			return false;
		}
	}
	private function canCheck($act) {
		if ($act->act_status == Activity::STATUS_NORMAL && strtotime ( $act->act_check_start_time ) <= time () && strtotime ( $act->act_check_end_time ) >= time ()) {
			return true;
		}
		return false;
	}
	private function isCheck($act_id) {
		$check = ActivityUser::findOne ( [ 
				"act_id" => $act_id,
				"uid" => $this->user->uid,
				"ischeckin" => 1 
		] );
		return $check ? true : false;
	}
	private function isEnd($end_time) {
		if (strtotime ( $end_time ) < time ()) {
			return true;
		}
		return false;
	}
	
	/**
	 * 活动报名
	 */
	public function actionReg() { 
		if ($this->user) {
			$act_id = \Yii::$app->request->post ( "activity_id", "" ); 
			if($act_id) {
				$result = [ 
						"status" => 0,
						"msg" => "系统错误" 
				];
				$act_user = ActivityUser::findOne ( [ 
						"act_id" => $act_id,
						"uid" => $this->user->uid
						
				] ); 
				//当前用户的参加活动的详细信息
				if ($act_user && $act_user->isreg == 1) {
					$result ["status"] = 1;
					$result ["msg"] = "已经报名";
				} else {
					//活动状态， 0 删除  1 正常
					$act = Activity::findOne ( [ 
							"act_id" => $act_id,
							"act_status" => Activity::STATUS_NORMAL 
					] );
					if ($act && $this->canReg ( $act )) {
							if ($act->total_limit > 0) {
								if ($act->reg_num >=$act->total_limit||$act->reg_submit_num>$act->total_limit) {
										$result ["msg"] = "名额已抢完!";
									CustomHelper::RetrunJson ( $result );
								} 
							}
							$club=$act->club; //跑团信息 
							//当前用户是否为本跑团成员
							$member = ClubMember::findOne ( [
									"club_id" => $club->clubid,
									"uid" => $this->user->uid
							] );
							$isMember=false; //保存跑者是否为本跑团正式会员的状态
							//如果有记录 则查看跑者的状态   1 未通过2 待审核 5待支付  6支付待确认 4正常3 一般会员
							if ($member && (($member->member_status == ClubMember::STATUS_NORMAL || $member->member_status == ClubMember::STATUS_SIMPLE))) {
								$isMember=true;
							}
							//跑团活动是否限定范围 0 不限制 1限制
							if ($act->rang_limit == Activity::LIMIT_RANGE_CLUB) {
								if ($isMember) {
									//积分限制
									if($act->credit_limit>0&&$member->credits<$act->credit_limit){
										$result ["msg"] = "您的积分没达到要求!";
										CustomHelper::RetrunJson ( $result );
									}
								}else{
									$result ["msg"] = "加入跑团才能报名!";
									CustomHelper::RetrunJson ( $result );
								}     
							}
							//没有报名过活动 
							if (! $act_user) {
								$act_user = new ActivityUser ();
							}
							$act_user->uid =$this->user->uid; //当前跑者的id
					if ($act_user->load ( Yii::$app->request->post () )) {
						$order=null;
					   $this->trans = \Yii::$app->db->beginTransaction ();
					   //活动费用
						if($act->register_fee>0||$act->register_fee_other>0){
							$act->reg_submit_num++;//名额所定人数+1
							$order = new OrderMaster();//支付订单
							$order->order_type = OrderMaster::TYPE_CLUB_ACT;
							$order->uid = $this->user->uid;
							$order->order_title =$act->act_title;
							$order->amount = $act->register_fee;
							$order->actual_payment =$act->register_fee;
							if(!$isMember&&$act->register_fee_other>0){
								$order->amount = $act->register_fee_other;
								$order->actual_payment =$act->register_fee_other;
							}
							$order->payment_start = date ( "Y-m-d H:i:s" );
							$order->expire_time = date ( "Y-m-d H:i:s", strtotime ( "1 hour" ) );
							if($order->actual_payment==0){
								$order->order_status = OrderMaster::STATUS_NORMAL;
								$act_user->status=ActivityUser::STATUS_NORMAL;
								$act_user->isreg = 1;
								$act_user->reg_time = date ( "Y-m-d H:i:s" );
								$act->reg_num = $act->reg_num + 1;
							}else{
								$order->order_status = OrderMaster::STATUS_WAIT_PAY;
								$act_user->status=ActivityUser::STATUS_NEED_PAY;
							}
							if($order->save()){
								$act_user->order_id = $order->orderid;
							}else{
								$this->trans->rollBack ();
								$result ["msg"] = "系统错误!";
								CustomHelper::RetrunJson ( $result );
								return;
							}
						}else{
							$act_user->status=ActivityUser::STATUS_NORMAL;
							if (! $act_user->isreg) {
								$act->reg_num = $act->reg_num + 1;
							}
							$act_user->isreg = 1;
							$act_user->reg_time = date ( "Y-m-d H:i:s" );
						}   
						if($act_user->save ( false ) && $act->save ()) {  
							if($act_user->status==ActivityUser::STATUS_NEED_PAY){
								$result ["status"] = 2;
								$result ["msg"]="";
								$result["order"] = ArrayHelper::toArray($order==null?[]:$order) ;
								$this->trans->commit ();
							}else{
								if($member){
									$r=Util::genCredit(Util::CREDIT_EVENT_ACT_REG,  $club->clubid, $act->act_id, $this->user->uid);
								    if($r){
 								    	$this->trans->commit ();
 								    	$result ["status"] = 1;
 								    	$result ["msg"]="";
								    }else{
								    	$this->trans->rollBack ();
								    	$result ["msg"] = "系统错误!Credit";
								    }
								}else{
									$this->trans->commit ();
									$result ["status"] = 1;
									$result ["msg"]="";
								}
							}
						} else {
							$this->trans->rollBack ();
							$result ["msg"] = "系统错误!";
						}
					 }
					} else {
						$result ["msg"] = "活动不存在或报名已结束!";
					}
				}
				CustomHelper::RetrunJson ( $result );
			}
		}
	}
	
	public function actionCheckinvitecode() {
		$code = trim(\Yii::$app->request->post("code",""));
		$result["status"]=0;
		if($this->act&&$this->act->invite_code==$code){
			$result["status"]=1;
		}
		CustomHelper::RetrunJson($result);
	}
	private function checkReg($uid, $act) {
		$reg = ActivityUser::findOne ( [ 
				"act_id" => $act->act_id,
				"uid" => $this->user->uid,
				"isreg" => 1 
		] );
		if ($reg) {
			return true;
		}
		return false;
	}
	public function actionCodecheckin() {
		$code = \Yii::$app->request->get ( "code", null );
		$result = [ 
				"status" => 0 
		];
		if ($code) {
			$qrcode = false;
			if ($this->user) {
				$acts = Activity::findAll ( [ 
						"check_code" => $code,
						"act_status" => Activity::STATUS_NORMAL ,
				] );
				$type = ActivityUser::CHECK_TYPE_CODE;
				if (count($acts)==0) {
					$acts = Activity::findAll ( [ 
							"qrcode" => $code,
							"act_status" => Activity::STATUS_NORMAL 
					] );
					$qrcode = true;
					$type = ActivityUser::CHECK_TYPE_QRCODE;
				}
				if(count($acts)==0){
					$result ["msg"] = "活动不存在!";
				}else{
					$canCheck = false;
					foreach ($acts as $act){
						if ($act && strtotime ( $act->act_check_start_time ) <= time () && strtotime ( $act->act_check_end_time ) >= time ()) {
							$result = $this->checkin ( $act, $this->user->uid, $type );
							$canCheck=true;
							break;
						}
					}
					if(!$canCheck){
						$result ["msg"] ="活动不在签到时间!";
					}
				}
			}
			CustomHelper::RetrunJson ( $result );
		}
	}
	private function checkin($act, $uid, $type = 1) {
		$result = [ 
				"status" => 0 
		];
		if ($act && strtotime ( $act->act_check_start_time ) <= time () && strtotime ( $act->act_check_end_time ) >= time ()) {
			$act_user = $ac = ActivityUser::findOne ( [ 
					"act_id" => $act->act_id,
					"uid" => $this->user->uid 
			] );
			$result ["act_id"] = $act->act_id;
			$isNew=true;
			if (! $act_user) {
				$act_user = new ActivityUser ();
				$act_user->act_id = $act->act_id;
				$act_user->uid = $this->user->uid;
				$act_user->ischeckin = 1;
				$act_user->checkin_type = $type;
				$act_user->checkin_time = date ( "Y-m-d H:i:s" );
				$act->sign_num = $act->sign_num + 1;
			} else {
				if (! $act_user->ischeckin) {
					$act->sign_num = $act->sign_num + 1;
				}else{
					$isNew=false;
				}
				$act_user->checkin_type = $type;
				$act_user->ischeckin = 1;
				$act_user->checkin_time = date ( "Y-m-d H:i:s" );
			}
			$this->trans = \Yii::$app->db->beginTransaction ();
			if ($act_user->save ( false ) && $act->save ()) {
				if(	$isNew){
					$club=$act->club;
					$member = ClubMember::getNormalMember( $club->clubid,$this->user->uid);
					if($member){
						$r=Util::genCredit(Util::CREDIT_EVENT_ACT_SIGN,  $club->clubid, $act->act_id, $this->user->uid);
						if($r){
							$this->trans->commit ();
							$result ["status"] = 1;
							$result ["msg"]="";
						}else{
							$this->trans->rollBack ();
							$result ["msg"] = "系统错误!Credit";
						}
					}else{
					$this->trans->commit ();
					$result ["status"] = 1;
					$result ["msg"]="";
				   }
				}else{
					$this->trans->commit ();
					$result ["status"] = 1;
					$result ["msg"]="";
				}
			} else {
				$this->trans->rollBack ();
				$result ["msg"] = "系统错误!";
			}
		} else {
			$result ["msg"] = "活动不存在或签到已结束!";
		}
		return $result;
	}
	public function actionShakeactlist() {
		$ticket = \Yii::$app->request->post ( "ticket", null );
		if (! $ticket) {
			$ticket = \Yii::$app->request->get ( "ticket", null );
		}
		$result = [ 
				"status" => 0 
		];
		if ($ticket) {
			$r = WxUtil::getDeviceInfo ( $ticket );
			// $r=true;
			if ($r) {
				// $r = new \stdClass();
				// $r->data = new \stdClass();
				// $r->data->openid="oyL64uI7WE6HUm2RJ60ahVuZpbOc";
				if ($r->data->openid != \Yii::$app->request->post ( "openid" )) {
					$result ["msg"] = "用户信息不匹配，请重新使用摇一摇签到";
				} else {
					$beacon_info = $r->data->beacon_info;
					$beacon = Ibeacon::findOne ( [ 
							"major" => $beacon_info->major,
							"minor" => $beacon_info->minor,
							"uuid" => $beacon_info->uuid 
					] );
					// $beacon = Ibeacon::findOne(1);
					if ($beacon) {
						$acts =Activity::getIbeaconActs ( $beacon->id, $this->user->uid );
						$acts = Activity::makeActs ( $acts );
						$tmp = [];
						foreach ( $acts as $act ) {
							if ($act["can_check"]) {
								array_push ( $tmp, $act );
							}
						}
						$result ["data"] = $tmp;
						$result ["status"] = 1;
					} else {
						$result ["msg"] = "获取系统设备信息失败";
					}
				}
			} else {
				$result ["msg"] = "摇一摇授权失效，请重新使用摇一摇签到";
			}
		}
		
		CustomHelper::RetrunJson ( $result );
	}
	public function actionShakcheckin() {
		$ticket = \Yii::$app->request->post ( "ticket", null );
		$act_id = \Yii::$app->request->post ( "activity_id", null );
		$result = [ 
				"status" => 0 
		];
		if ($ticket) {
			$r = WxUtil::getDeviceInfo ( $ticket );
			if ($r) {
				if ($r->data->openid != \Yii::$app->request->post ( "openid" )) {
					$result ["msg"] = "用户信息不匹配，请重新使用摇一摇签到";
				} else {
					$beacon_info = $r->data->beacon_info;
					$beacon = Ibeacon::findOne ( [ 
							"major" => $beacon_info->major,
							"minor" => $beacon_info->minor,
							"uuid" => $beacon_info->uuid 
					] );
					if ($beacon) {
						$act = Activity::findOne ( $act_id );
						if ($act) {
							$result = $this->checkin ( $act, $this->user->uid, ActivityUser::CHECK_TYPE_SHAKE );
						}
					} else {
						$result ["msg"] = "获取系统设备信息失败";
					}
				}
			} else {
				$result ["msg"] = "获取设备信息失败";
			}
		} else {
			$result ["msg"] = "摇一摇授权失效，请重新使用摇一摇签到";
		}
		CustomHelper::RetrunJson ( $result );
	}
	public function actionRegusers() {
		$act_id = \Yii::$app->request->post ( "activity_id", null );
		$regs = ActivityUser::find ()->where ( [ 
				"act_id" => $act_id,
				"isreg" => 1 
		] )->orderBy ( "reg_time desc" )->all ();
		$regs_array = array ();
		foreach ( $regs as $reg ) {
			$user = ArrayHelper::toArray ( $reg->user, [ 
					"common\models\UserMaster" => [ 
							"uid",
							"nick_name",
							"user_face" 
					] 
			] );
			$user ["create_time"] = $reg->reg_time;
			$user ["create_short_time"] = date ( "H:i:s", strtotime ( $reg->reg_time ) );
			array_push ( $regs_array, $user );
		}
		CustomHelper::RetrunJson ( $regs_array );
	}
	public function actionCheckusers() {
		$act_id = \Yii::$app->request->post ( "activity_id", null );
		$checkins = ActivityUser::find ()->where ( [ 
				"act_id" => $act_id,
				"ischeckin" => 1 
		] )->orderBy ( "checkin_time desc" )->all ();
		$checkins_array = array ();
		foreach ( $checkins as $checkin ) {
			$user = ArrayHelper::toArray ( $checkin->user, [ 
					"common\models\UserMaster" => [ 
							"uid",
							"nick_name",
							"user_face" 
					] 
			] );
			$user ["create_time"] = $checkin->checkin_time;
			$user ["create_short_time"] = date ( "H:i:s", strtotime ( $checkin->checkin_time ) );
			array_push ( $checkins_array, $user );
		}
		CustomHelper::RetrunJson ( $checkins_array );
	}
	public function actionActusers() {
		$act_id = \Yii::$app->request->post ( "activity_id", null );
		$act =$this->act;
		if ($act) {
			$checkins = ActivityUser::find ()->where ("act_id=:act_id and (isreg=1 or ischeckin=1)" )->addParams([":act_id"=>$act->act_id])->orderBy ( "checkin_time desc" )->all ();
			$checkins_array = array ();
			$checkins_array ["users"] = array ();
			foreach ( $checkins as $checkin ) {
				$user = ArrayHelper::toArray ( $checkin );
				if ($checkin->reg_time) {
					$user ["reg_short_time"] = date ( "H:i:s", strtotime ( $checkin->reg_time ) );
				}
				if ($checkin->checkin_time) {
					$user ["checkin_short_time"] = date ( "H:i:s", strtotime ( $checkin->checkin_time ) );
				}
				$user ["userInfo"] = ArrayHelper::toArray ( $checkin->user, [ 
						"common\models\UserMaster" => [ 
								"uid",
								"nick_name",
								"user_face" 
						] 
				] );
				$user ["userInfo"] ["user_face"] = Util::getUserFace ( $user ["userInfo"] ["user_face"] );
				array_push ( $checkins_array ["users"], $user );
			}
			CustomHelper::RetrunJson ( $checkins_array );
		}
	}
	public function actionSavelocation() {
		$result = [ 
				"status" => 0 
		];
		if ($this->user) {
			$location = new ActivityLocation ();
			$location->uid = $this->user->uid;
			if ($location->load ( Yii::$app->request->post () )) {
				if ($location->name == null || ! trim ( $location->name )) {
					$location->name = $location->location;
				}
				$location->type=ActivityLocation::TYPE_PER;
				$tmp = ActivityLocation::findOne ( [ 
						"name" => $location->name,
						"uid" => $this->user->uid 
				] );
				if (! $tmp) {
					if ($location->save ()) {
						$result ["status"] = 1;
						$result ["data"] = ArrayHelper::toArray ( $location );
					}
				} else {
					$result ["msg"] = "名字已存在";
				}
			}
		}
		
		CustomHelper::RetrunJson ( $result );
	}
	
	public function actionDeletelocation() {
		$result = [
				"status" => 0
		];
		if ($this->user) {
		$location_id = trim(\Yii::$app->request->post("location_id",""));
		   $location = ActivityLocation::findOne($location_id);
		   if($location){
                   if($location->type==ActivityLocation::TYPE_CLUB){
                   	$result["msg"] = "只能删除个人约跑地点";
                   }else{
                   	$location->status=ActivityLocation::STATUS_DEL;
                   	 if($location->save()){
                   	 	$result["status"]=1;
                   	 }
                   }		   	
		   }else{
		   	 throw new NotFoundHttpException();
		   }
		}
	
		CustomHelper::RetrunJson ( $result );
	}
}
