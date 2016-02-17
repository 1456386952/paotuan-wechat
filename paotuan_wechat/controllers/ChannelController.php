<?php

namespace paotuan_wechat\controllers;

use Yii;
use common\models\ActChannel;
use paotuan_wechat\models\ClubMember;
use common\component\CustomHelper;
use yii\helpers\ArrayHelper;
use paotuan_wechat\models\Club;
use paotuan_wechat\component\PaotuanController;
use common\models\Item;
use yii\web\NotFoundHttpException;
use common\models\Act;
use common\models\Register;
use paotuan_wechat\component\Util;
use common\models\RegisterConfig;
use common\models\common\models;
use common\models\OrderMaster;
use common\models\OrderDetail;
use yii\base\Exception;
use common\models\ActStay;
use common\models\UserBindLog;
use yii\base\Action;
use paotuan_wechat\models\bisai\ResultChipInfo;
use common\models\ActCourse;
use common\models\UserPaper;

class ChannelController extends PaotuanController {
	private $channel;
	private $act;
	public function beforeAction($action) {
	   if(\Yii::$app->request->isAjax){
			$channel_id = $_REQUEST["channel_id"];
		    if($channel_id){
		    	$this->channel = ActChannel::findAvailableChannel($channel_id);
		    	$this->act =$this->channel->actInfo;
			if (!$this->channel||!$this->act||$this->act->act_status==Act::ACT_STATUS_CANCEL) {
				throw new NotFoundHttpException();
			}
		   }
		}else{
			throw new NotFoundHttpException();
		}
		return parent::beforeAction($action);
	}
	
	public function actionChannelinfo(){
		$start=false;
		$end =false;
		$isMember = false;
		$limit_club=false;
		$needBind=true;
		$act_register_end=false;
		$can_register=true;
		$club=null;
		$isSubmit=false;
		$isReg=false;
		$needPay=false;
		$need_code=false;
		$order=[];
		if($this->channel->channel_start<=date("Y-m-d H:i:s")){
			$start=true;
		}
		if($this->channel->channel_end<=date("Y-m-d H:i:s")){
			$end=true;
		}
		if($this->channel->limit_range==1){
			$limit_club=true;
			$club = $this->channel->clubInfo;
			if($club&&$club->club_type==Club::CLUB_TYPE_CLUB){
				$club = ArrayHelper::toArray($club,["paotuan_wechat\models\Club"=>["clubid","club_name","club_eng","club_logo","club_status"]]);
			}
		}
		if($this->channel->invite_code&&trim($this->channel->invite_code)!=''){
			$need_code=true;
		}
		if($this->act->register_end<=date("Y-m-d H:i:s")){
			$act_register_end=true;
		}
		if($this->user){
			$member = ClubMember::getNormalMember($this->channel->clubid, $this->user->uid);
			if($member){
				$isMember=true;
			}
			if($this->user->user_cell){
				$needBind=false;
			}
		}
		
		$act = $this->act;
		$country = $act->country->chn_name;
		$city = $act->city->chn_name;
		$act =ArrayHelper::toArray($this->act);
		$act["country"] = $country;
		$act["city"] = $city;
		CustomHelper::RetrunJson(["channel"=>ArrayHelper::toArray($this->channel,["common\models\ActChannel"=>["channelid"]]),
				                   "act"=>$act,
				                   "order"=>ArrayHelper::toArray($order),
				                   "status"=>[
				                   		"start"=>$start,
				                   		"end"=>$end,
				                   		"isMember"=>$isMember,
				                   		"limit_club"=>$limit_club,
				                   		"needBind"=>$needBind,
				                   		"need_code"=>$need_code,
				                   		"act_register_end"=>$act_register_end,
				                   		"can_register"=>$can_register,
				                   ],
				                    "club"=>$club
		]);
	}
	
	public function actionItems(){
		$items = Item::findAll(["channelid"=>$this->channel->channelid,"actid"=>$this->channel->actid]);
		$r["regs"]  = [];
		$r["hotels"]  = [];
		$r["traffic"]=[];
		$r["other"]=[];
		foreach ($items as $item){
			if($item){
				$item = ArrayHelper::toArray($item);
				if(strtotime($item["item_end"])<time()||$item["item_status"]==Item::STATUS_END){
					$item["is_end"]=true;
				}
				switch ($item["item_type"]){
					case Item::TYPE_REGISTER:
						array_push($r["regs"], $item);
						break;
					case Item::TYPE_HOTEL:
						array_push($r["hotels"], $item);
						break;
					case Item::TYPE_TRAFFIC:
						array_push($r["traffic"], $item);
						break;
					case Item::TYPE_OTHER:
						array_push($r["other"], $item);
						break;
				}
			}
		}
		CustomHelper::RetrunJson($r);
	}
	
	public function actionGetregister(){
		$isSubmit=false;
		$isReg=false;
		$needPay=false;
		$order=null;
		$reg=array();
		if($this->user){
			if($this->user->userInfo){
				if(!$this->user->user_cell){
					$need_bind=true;
				}
				if(!$this->user->userInfo->id_number&&trim($this->user->userInfo->id_number)==""){
					$reg = new Register();
				}else{
					$reg=Register::find()->where("id_number=:id_number and actid=:actid and (register_status = :normal or register_status = :done)",
											[":id_number"=>trim($this->user->userInfo->id_number),":actid"=>$this->act->actid,":normal"=>Register::STATUS_NORMAL,":done"=>Register::STATUS_REGISTER])
					                         ->one();
					if($reg){
						if($reg->register_status==Register::STATUS_REGISTER){
							$isReg=true;
						}
						if($reg->register_status==Register::STATUS_NORMAL){
							$isSubmit=true;
						}
					}else{
						$reg = new Register();
					}
				}
				$orders = OrderMaster::getOrders($this->user->uid, $this->act->actid);
				$need_pay_count=0;
				$channels = [];
				if($orders){
				     $hasOrder=count($orders);
					foreach ($orders as $o){
						if($o["order_status"]==OrderMaster::STATUS_WAIT_PAY){
							$needPay=true;
							$need_pay_count++;
							if($o["order_type"]==OrderMaster::TYPE_ONE_REG){
								$reg_order = $o["orderid"];	
							}
						}
						
						$items =OrderDetail::findAll(["orderid"=>$o["orderid"]]);
						foreach ($items as $i){
							$item = $i->itemInfoAll;
							if($item){
								if($item->channelid==$this->channel->channelid){
									$channelOrder=true;
								}
							}
						}
					}
				}
				$regObj=Util::copyUserInfoToRegister($reg, $this->user->userInfo);
				$reg = ArrayHelper::toArray($regObj);
				$reg["id_copy"] = "http://xiaoi.b0.upaiyun.com/".$regObj->id_copy;
				$reg["id_copy_back"] = "http://xiaoi.b0.upaiyun.com/".$regObj->id_copy_back;
				$reg["medical_report"] ="http://xiaoi.b0.upaiyun.com/".$regObj->medical_report;
				$reg["certs"] = ArrayHelper::toArray($regObj->certs,["common\models\UserPaper"=>["paper_url"]]);
				if(count($reg["certs"])==0){
					$reg["certs"]=null;
				}
				CustomHelper::RetrunJson([
						"is_reg"=>$isReg,
						"is_submit"=>$isSubmit,
						"need_pay"=>$needPay,
						"register" =>$reg,
						"hasOrder"=>$hasOrder,
						"pay_count"=>$need_pay_count,
						"reg_order"=>$reg_order,
						"channelOrder"=>$channelOrder,
						"need_bind"=>$need_bind,
						"act_name"=>$this->act->act_name
 				]);
			}
		}
	}
	
	public function actionCancel(){
		$result = array (
				"status" => 0
		);
		$order_id = trim(\Yii::$app->request->post("order_id",""));
		if($order_id&&$this->user){
			$order = OrderMaster::find()->where("orderid=:order_id and uid=:uid and order_status=:status",[":order_id"=>$order_id,":uid"=>$this->user->uid,":status"=>OrderMaster::STATUS_WAIT_PAY])->one();
		    if($order){
		    	$this->trans = \Yii::$app->db->beginTransaction ();
			    $order->order_status = OrderMaster::STATUS_CANCEL;
			    if($order->save()){
			    	$register = Register::findOne(["orderid"=>$order_id]);
			    	if($register){
			    		$register->payment_status=Register::PAY_STATUS_CANCEL;
			    		$register->register_status=Register::STATUS_CANCEL;
			    		if(!$register->save()){
			    			throw new Exception();
			    		}
			    		$result["need_reg"]=true;
			    	}
			    	$details = OrderDetail::findOrderDetailInfo ( $order->orderid );
			    	foreach ( $details as $detail ) {
			    		$detail->item_status = OrderDetail::STATUS_CANCEL;
			    		$detail->itemInfo->item_buy_sum = $detail->itemInfo->item_buy_sum - $detail->item_num;
			    		if (! $detail->itemInfo->save () || ! $detail->save ()) {
			    		    throw new Exception();
			    		}
			    	}
			    	ActStay::updateAll ( [
			    			"payment_status" =>ActStay::PAY_STATUS_CANCEL,
			    			"stay_status" =>ActStay::STATUS_CANCEL,
			    	], "orderid=:orderid",[":orderid"=>$order_id] );
			    }else{
			    	throw new Exception();
			    }
			    $this->trans->commit();
		    }
		    	$result["status"]=1;
		}else{
			$result["msg"]="不存在的订单！";
		}
		CustomHelper::RetrunJson($result);
	}
	
	public function actionOrdersubmit() {
		$items = \Yii::$app->request->post ( "items", "" );
		$nums= \Yii::$app->request->post ( "nums", "" );
		$contact_info_name=trim(\Yii::$app->request->post ( "contact_info_name", "" ));
		$contact_info_cell=trim(\Yii::$app->request->post ( "contact_info_cell", "" ));
		$submitData = \Yii::$app->request->post ( "formData", "" );
		
		$result = array (
				"status" => 0
		);
		if($this->user&&$submitData){
			$submitData = json_decode($submitData);
			if(!$submitData||!is_array($submitData)||count($submitData)<=0){
				$result["msg"] = "请选择需要购买的项目!";
				CustomHelper::RetrunJson ($result);
				return;
			}else{
				$ods = array();
				$itemsMap = array();
				$actStays = array();
				$om = new OrderMaster();
				$om->uid = $this->user->uid;
				$om->order_title = $this->act->act_name;
				$om->order_status = 0;
				$om->payment_start = date ( "Y-m-d H:i:s" );
				$om->expire_time = date ( "Y-m-d H:i:s", strtotime ( "1 hour" ) );
				$om->amount = 0;
				$om->actual_payment=0;
				$total = 0;
				$register=null;
				foreach($submitData as $data){
					$item = Item::findOne($data->itemid);
					if($item){
							$num = intval($data->num);
							if ($item->item_status == Item::STATUS_END || strtotime ( $item->item_end ) < time ()) {
								$result ["msg"] = "{$item->item_name}已结束！";
								CustomHelper::RetrunJson ( $result );
							}
							if ($item->item_num_limit > 0 && $item->item_num_limit - $item->item_buy_sum < $num) {
								$result ["msg"] = "对不起,{$item->item_name}名额不足！";
								CustomHelper::RetrunJson ( $result );
							}
							$od = new OrderDetail ();
							$od->itemid = $item->itemid;
							$od->item_title = $item->item_name;
							$od->item_num = $num;
							$od->item_price = $item->item_price;
							$od->item_status = 0;
							array_push($ods, $od);
							$item->item_buy_sum = $item->item_buy_sum + $num;
							$itemsMap[$item->itemid] = $item;
							$total = $total + $item->item_price*$num;
							if($item->item_type==Item::TYPE_HOTEL||$item->item_type==Item::TYPE_TRAFFIC||$item->item_type==Item::TYPE_OTHER){
								if(!$data->passport_name||!$data->user_cell){
									$result["msg"] = "请完善个人信息";
									CustomHelper::RetrunJson ( $result );
									return;
								}
								$as = new ActStay();
								$as->actid =$item->actid;
								$as->uid =$this->user->uid;
								$as->passport_name =$data->passport_name;
								$as->cell =$data->user_cell;
								if($item->item_type==Item::TYPE_HOTEL){
									$as->stay_type = ActStay::TYPE_HOTEL;
								}else{
									$as->stay_type = ActStay::TYPE_TRAFFIC;
								}
								$as->stay_status=ActStay::STATUS_LOCKED;
								$as->payment_status = ActStay::PAY_STATUS_WAIT;
								$actStays[$item->itemid]=$as;
								if($om->order_type != OrderMaster::TYPE_ONE_REG){
									$om->order_type = OrderMaster::TYPE_HOTEL_TRAFFIC;
								}
							}
							if($item->item_type==Item::TYPE_REGISTER){
								$om->order_type = OrderMaster::TYPE_ONE_REG;
								$register = $this->getRegister($item->courseid);
							}
					}
				}
						$this->trans = \Yii::$app->db->beginTransaction();
						$om->amount = $total;
						$om->actual_payment=$total;
						if($om->save(false)){
							foreach ($ods as $od){
								$od->orderid = $om->orderid;
								if($od->save()&&$itemsMap[$od->itemid]->save()){
									$stay=$actStays[$od->itemid];
									if($stay){
										if($total==0){
											$stay->payment_status=ActStay::PAY_STATUS_FREE;
											$stay->stay_status = ActStay::STATUS_NORMAL;
										}
										$stay->orderid = $om->orderid;
										$stay->detailid = $od->detailid;
										if(!$stay->save()){
											throw new Exception();
										}
									}
								}else{
									throw new Exception();
								}
							}
						}else{
							throw new Exception();
						}
						if($register){
							$register->orderid = $om->orderid;
							if($total==0){
								$register->payment_status = Register::PAY_STATUS_FREE;
								$register->register_status=Register::STATUS_REGISTER;
								$om->order_status=OrderMaster::STATUS_NORMAL;
								$om->payment_type="微信支付wechat";
								$om->trade_no = date ( "YmdHis" ).$om->orderid;
								$om->update();
								Util::sendRegEmail($register);
								$params = array();
								$params["openid"]=\Yii::$app->session["openid"];
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
							}else{
								$register->payment_status = Register::PAY_STATUS_NEED_PAY;
								$register->register_status=Register::STATUS_NORMAL;
							}
							if(!$register->save()&&!$this->user->userinfo->save()){
								throw new Exception();
							}
						}
						$result["order"] =$om->orderid;
						$this->trans->commit();
						$result["status"]=1;
						CustomHelper::RetrunJson ( $result );
					}
		}
	}
	
	public function actionRunnerorders(){
		if($this->user){
			$orders = OrderMaster::getOrders($this->user->uid, $this->act->actid);
			for($i=0;$i!=count($orders);$i++){
				$o = $orders[$i];
				$items =OrderDetail::findAll(["orderid"=>$o["orderid"]]);
				foreach ($items as $item){
					if(!isset($o["items_desc"])){
						$o["items_desc"]=array();
					}
					array_push($o["items_desc"], $item->item_title.($item->item_num==1?'':"X".$item->item_num));
				}
				$o["items_desc"] = join("+", $o["items_desc"]);
				if($o["order_status"]==OrderMaster::STATUS_WAIT_PAY){
					$o["need_pay"] = true;
				}else{
					$o["need_pay"] = false;
				}
				$orders[$i]=$o;
			}
			CustomHelper::RetrunJson($orders);
		}
	}
	
	private function getRegister($courseid){
		$model = new Register ();
		if ($model->load ( Yii::$app->request->post () )) {
			$model->uid = $this->user->uid;
			$model->channelid = $this->channel->channelid;
			$model->actid = $this->act->actid;
			$model->courseid=$courseid;
			if ($model->validate ()) {
				$userInfo = $this->user->userInfo;
// 				if ($userInfo->user_cell != $model->user_cell) {
// 					// 验证手机验证码
// 					$code = \Yii::$app->request->post ( "code" );
// 					$code1 = UserBindLog::findOne ( [
// 							"uid" => $model->uid,
// 							"bind_type" => 1,
// 							"bind_info" => $model->user_cell,
// 							"bind_code" => $code,
// 							"bind_status" => 0
// 					] );
// 					if ($code1 === null || strtotime ( $code1->expiry_time ) < time ()) {
// 						$msg ["msg"] = "验证码错误";
// 						CustomHelper::RetrunJson ( $msg );
// 					}
// 					$code1->bind_status = 1;
// 					$code1->save ();
// 				}
				if(Register::findOne(["actid"=>$this->act->actid,"id_number"=>$model->id_number,"register_status"=>Register::STATUS_REGISTER])){
					$msg ["msg"] = "证件号已存在!";
					CustomHelper::RetrunJson ( $msg );
				}
				$userInfo = Util::copyRegisterToUserInfo ( $userInfo, $model );
				$userInfo->save();
					return $model; 
			} else {
				$msg ["status"] = 0;
				$msg ["msg"] = Util::getStringFromError ( $model->errors );
				CustomHelper::RetrunJson ( $msg );
			}
		}
	}
	
	public function actionOrderinfo(){
		if($this->user){
			$orderid = trim(\Yii::$app->request->post("order_id",""));
			if($orderid){
				$order = OrderMaster::findOne($orderid);
				if($order){
					$order = ArrayHelper::toArray($order);
					$details =OrderDetail::findOrderDetailInfoAll($order["orderid"]);
					switch ($order["order_status"]){
						case OrderMaster::STATUS_CANCEL:
							$order["cancel"]=true;
							break;
						case OrderMaster::STATUS_NORMAL:
							$order["done"]=true;
							break;
						case OrderMaster::STATUS_WAIT_PAY:
							$order["need_pay"]=true;
							break;
						case OrderMaster::STATUS_DELETE:
								$order["delete"]=true;
								break;
					}
					foreach ($details as $d){
						$item = $d->itemInfoAll;
						$item = ArrayHelper::toArray($item);
						if(!isset($order["items"])){
							$order["items"]=array();
						}
						if($item){
							$stay = ActStay::findOne(["orderid"=>$order["orderid"],"detailid"=>$d->detailid]);
							$item["item_num"] = $d->item_num;
							if($stay){
								$item["passport_name"]=$stay->passport_name;
								$item["user_cell"]=$stay->cell;
							}
							if($item["item_type"]==Item::TYPE_REGISTER){
								$course = ActCourse::findOne($item["courseid"]);
								if($course){
									$item["address"] =$course->course_addr; //ArrayHelper::toArray($course,["common\models\ActCourse"=>["course_intro","course_pack_time","course_start","course_close","course_mileage","course_addr"]]);
									$item["mileage"] = $course->course_mileage;
									$item["start_time"] = $course->course_start;
									$item["close_time"] = $course->course_close;
								}
								$item["is_reg"]=true;
							  array_unshift($order["items"], $item);
							}else{
							  array_push($order["items"], $item);
							}
						}
					}
					CustomHelper::RetrunJson($order);
				}
			}
		}
	}
	
	public function actionRacedisclaimer(){
		$result = ArrayHelper::toArray($this->act,["common\models\Act"=>["disclaimer","actid"]]);
		$result["channel_id"] = $this->channel->channelid;
		CustomHelper::RetrunJson($result);
	}
	
	public function actionChannelcode(){
			$code=\Yii::$app->request->post("invite_code","");
			$result = ["status"=>0];
			if($this->channel->invite_code==$code){
				$result["status"]=1;
			}
			CustomHelper::RetrunJson($result);
	}
	
	public function actionUpdateregister(){
		$registerid = trim(\Yii::$app->request->post("registerid",""));
		$model = Register::findOne($registerid);
		$result = ["status"=>0];
		if($this->user&&$model){
			if($model->load ( Yii::$app->request->post ())){
				if (! empty ( $model->id_copy )) {
					$userPaper = UserPaper::findOne ( [
							"uid" => $this->user->uid,
							"paper_type" => UserPaper::PAPER_TYPE_ID_COPY
					] );
					if ($userPaper !== null) {
						$userPaper->paper_url = CustomHelper::CreateImageUrl ( $model->id_copy );
						$userPaper->save ();
					} else {
						UserPaper::addPaper ( array (
								"uid" => $this->user->uid,
								"url" => CustomHelper::CreateImageUrl ( $model->id_copy )
						), "idcopy" );
					}
				}
				if (! empty (  $model->id_copy_back )) {
					$userPaper_back = UserPaper::findOne ( [
							"uid" => $this->user->uid,
							"paper_type" => UserPaper::PAPER_TYPE_ID_COPY_BACK
					] );
					if ($userPaper_back !== null) {
						$userPaper_back->paper_url = CustomHelper::CreateImageUrl ( $model->id_copy_back );
						$userPaper_back->save ();
					} else {
						 
						UserPaper::addPaper ( array (
								"uid" => $this->user->uid,
								"url" => CustomHelper::CreateImageUrl ($model->id_copy_back )
						), "idcopyback" );
					}
				}
				 
				if (! empty (  $model->medical_report)) {
					$userPaper_medical = UserPaper::findOne ( [
							"uid" => $this->user->uid,
							"paper_type" => UserPaper::PAPER_TYPE_HEALTH,
							"paper_status" => 0
					] );
					if ($userPaper_medical !== null) {
						$userPaper_medical->paper_url = CustomHelper::CreateImageUrl ($model->medical_report );
						$userPaper_medical->save ();
					} else {
						UserPaper::addPaper ( array (
								"uid" => $this->user->uid,
								"url" => CustomHelper::CreateImageUrl ( $model->medical_report )
						), "report" );
					}
				}
				 
				if(!empty($model->certs)){
					$certs = explode(",", $model->certs);
					UserPaper::deleteAll(["uid"=>$this->user->uid,"paper_type"=>3]);
					foreach ($certs as $cert){
						UserPaper::addPaper(["uid"=>$this->user->uid,"url"=>CustomHelper::CreateImageUrl($cert)], "cert");
					}
				}
				if($model->user_cell&&!CustomHelper::isCell($model->user_cell)){
					$result["status"]=2;
					$result["msg"]="请输入正确的手机号";
					CustomHelper::RetrunJson($result);
					return;
				}
				if($model->user_email&&!CustomHelper::isEmail($model->user_email)){
					$result["status"]=2;
					$result["msg"]="请输入正确的邮箱";
					CustomHelper::RetrunJson($result);
					return;
				}
				if($model->save()){
					$result["status"]=1;
				}else{
					throw new Exception();
				}
				 
			}
			CustomHelper::RetrunJson($result);
		}
	}
	
	private function isReg(){
		$reg=null;
		if($this->user){
			if($this->user->userInfo){
				if($this->user->userInfo->id_number&&trim($this->user->userInfo->id_number)!=""){
				  return $reg;
				}else{
					$reg=Register::find()->where("id_number=:id_number and act_id=:actid and (register_status = :normal or register_status = :done)",
											[":id_number"=>trim($this->user->userInfo->id_number),":actid"=>$this->act->actid,":normal"=>Register::STATUS_NORMAL,":done"=>Register::STATUS_REGISTER])
					                         ->one();
				}
			}
		}
		return $isReg;
	}

	
}