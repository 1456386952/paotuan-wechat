<?php

namespace paotuan_wechat\controllers;

use Yii;
use yii\web\Controller;
use common\component\Jssdk;
use common\component\WechatSDK\Wechat;
use paotuan_wechat\models\Club;
use paotuan_wechat\models\ClubForm;
use paotuan_wechat\models\ClubMember;
use common\component\CustomHelper;
use common\component\WechatSDK;
use paotuan_wechat\models\ClubMemberPayment;
use common\component\BindComponent;
use yii\filters\AccessControl;
use common\models\UserBindLog;
use common\models\User;
use yii\web\UploadedFile;
use common\models\UserInfo;
use common\models\UserOauth;
use common\component\UpYun;
use paotuan_wechat\models\UploadForm;
use common\component\ImageLibrary;
use paotuan_wechat\component\Util;
use common\models\Country;
use common\models\UserInfoOld;
use common\models\UserMaster;
use paotuan_wechat\models\Mileage;
use paotuan_wechat\component\PaotuanController;
use paotuan_wechat\models\ActivityUser;
use paotuan_wechat\models\Activity;
use paotuan_wechat\models\RunBind;
use yii\helpers\ArrayHelper;
use paotuan_wechat\models\MarketingVote;
use paotuan_wechat\models\UserMileageTarget;
use paotuan_wechat\models\Chip;
use yii\web\NotFoundHttpException;

class BindController extends PaotuanController {
	public function actionIndex() {
		//$this->user = UserMaster::findOne(2649);
		$this->checkWechatOauth("/bind");
		
		if ($this->user) {
			return $this->renderPartial ( "bindCell", [ 
					"uid"=>$this->user->uid,
					"signPackage" => Util::getJssdkParams () 
			] );
		} 
	}
	
	public function actionCheckcell(){
		$uid = \Yii::$app->request->post ( "uid" );
		$cell = \Yii::$app->request->post ( "cell" );
		$result=["status"=>0];
		if($this->user){
			$uid = $this->user->uid;
		}
		if (! empty ( $uid ) && ! empty ( $cell )) {
			$user = UserMaster::findOne ( [
					"user_cell" => $cell
			] );
			if($user){
				$result["status"]=1;
			}
		}
		CustomHelper::RetrunJson($result);
	}
	

	public function actionCheckemail(){
		$uid = \Yii::$app->request->post ( "uid" );
		$email = \Yii::$app->request->post ( "email" );
		$result=["status"=>0];
		if($this->user){
			$uid = $this->user->uid;
		}
		if (! empty ( $uid ) && ! empty ( $email )) {
			$user = UserMaster::findOne ( [
					"user_email" => $email
			] );
			if($user){
				$result["status"]=1;
			}
		}
		CustomHelper::RetrunJson($result);
	}
	
	public function actionBind() {
		$uid = \Yii::$app->request->post ( "uid" );
		$cell = \Yii::$app->request->post ( "cell" );
		$cc = \Yii::$app->request->post ( "cellCode" );
		if($this->user){
			$uid = $this->user->uid;
		}
		$result = array (
				"status" => 0, 
				"msg" => "系统异常"
		);
		if (! empty ( $uid ) && ! empty ( $cell ) && ! empty ( $cc )) {
			if (! CustomHelper::isCell ( $cell )) {
				$result ["msg"] = "手机号错误";
				CustomHelper::RetrunJson ( $result );
			}
			if (! Util::checkCellCode ( array (
					"uid" => $uid,
					"cell" => trim($cell),
					"cellCode" => trim($cc) 
			) )) {
				$result ["msg"] = "验证码错误";
				CustomHelper::RetrunJson ( $result );
			}
			
			$result ["status"] = 1;
			$uo = UserOauth::findAll ( [ 
					"uid" => $uid 
			] );
			if (count($uo)>0) {
				$wechatUser = $uo[0]->user;
				$user = UserMaster::findOne ( [ 
						"user_cell" => $cell 
				] );
				if ($user !== null) {
					UserOauth::updateAll(["uid"=>$user->uid],"uid=".$wechatUser->uid);
					$user->nick_name = $uo[0]->oauth_nick;
					$user->user_face = $wechatUser->user_face;
					ClubMember::updateAll ( [ 
							"uid" => $user->uid 
					], "uid=" . $wechatUser->uid );
					Mileage::updateAll ( [ 
							"uid" => $user->uid 
					], "uid=" . $wechatUser->uid );
					Club::updateAll ( [
							"uid" => $user->uid
					], "uid=" . $wechatUser->uid );
					ActivityUser::updateAll ( [
							"uid" => $user->uid
					], "uid=" . $wechatUser->uid );
					Activity::updateAll ( [
							"uid" => $user->uid
					], "uid=" . $wechatUser->uid );
					RunBind::updateAll([
							"uid" => $user->uid
					], "uid=" . $wechatUser->uid );
					
					MarketingVote::updateAll([
							"uid" => $user->uid
					], "uid=" . $wechatUser->uid);
					
					UserMileageTarget::updateAll([
							"uid" => $user->uid
					], "uid=" . $wechatUser->uid);
					
					Chip::updateAll([
							"uid" => $user->uid
					], "uid=" . $wechatUser->uid);
					
					$userInfo = $user->userInfo;
					if($userInfo){
						$userInfo->user_cell = $cell;
						if ($user->save ()&& $userInfo->update ()) {
							$result ["status"] = 1;
							$result ["msg"] = "";
						}
					}else{
						if ($user->save ()) {
							$result ["status"] = 1;
							$result ["msg"] = "";
						}
					}
					
				} else {
					$user = UserMaster::findOne ( $uid );
					if($user){
						$user->user_cell = $cell;
						$user->is_bind_cell = 1;
						$userInfo = $user->userInfo;
						if($userInfo){
							$userInfo->user_cell = $cell;
							if ($user->save () && $userInfo->update ()) {
								$result ["status"] = 1;
								$result ["msg"] = "";
							}
						}else{
							if ($user->save ()) {
								$result ["status"] = 1;
								$result ["msg"] = "";
							}
						}
					}
				}
				if($user){
				  try {
				  	$this->copyOldUserToUser ( array (
				  			"openid" => $uo[0]->oauth_openid,
				  			"cell" => $cell
				  	), $user );
				     } catch (Exception $e) {
				  }
				}
			}
		}
		CustomHelper::RetrunJson ( $result );
	}
	public function copyOldUserToUser($params, $user) {
		$openid = $params ["openid"];
		$cell = $params ["cell"];
		$old = UserInfoOld::findOne ( [ 
				"open_id" => $openid 
		] );
		if ($old === null) {
			$old = UserInfoOld::findOne ( [ 
					"cell" => $cell 
			] );
		}
		if ($old !== null) {
			$userInfo = $user->userInfo;
			if(!$userInfo){
				return;
			}
			if (empty ( $userInfo->passport_name ) && ! empty ( $old->passport_name ))
				$userInfo->passport_name = $old->passport_name;
			if (empty ( $userInfo->user_gender ) && ! empty ( $old->gender ))
				$userInfo->user_gender = $old->gender;
			if (empty ( $userInfo->nationality ) && ! empty ( $old->nationality ))
				$userInfo->nationality = $old->nationality;
			if (empty ( $userInfo->id_type ) && ! empty ( $old->id_type ))
				$userInfo->id_type = $old->id_type;
			if (empty ( $userInfo->id_number ) && ! empty ( $old->id_number ))
				$userInfo->id_number = $old->id_number;
			if (empty ( $userInfo->birthday ) && ! empty ( $old->birthday ))
				$userInfo->birthday = $old->birthday;
			if (empty ( $userInfo->tshirt_size ) && ! empty ( $old->tshirt_size ))
				$userInfo->tshirt_size = $old->tshirt_size;
			if (empty ( $userInfo->shoes_size ) && ! empty ( $old->shoes_size ))
				$userInfo->shoes_size = $old->shoes_size;
			if (empty ( $userInfo->address ) && ! empty ( $old->address ))
				$userInfo->address = $old->address;
			if (empty ( $userInfo->blood_type ) && ! empty ( $old->blood_type ))
				$userInfo->blood_type = $old->blood_type;
			if (empty ( $userInfo->height ) && ! empty ( $old->height ))
				$userInfo->height = $old->height;
			if (empty ( $userInfo->weight ) && ! empty ( $old->weight ))
				$userInfo->weight = $old->weight;
			if (empty ( $userInfo->emerge_name ) && ! empty ( $old->emerge_name )) {
				$userInfo->emerge_name = $old->emerge_name;
				$userInfo->emerge_cell = $old->emerge_cell;
				$userInfo->emerge_ship = $old->emerge_ship;
				$userInfo->emerge_addr = $old->emerge_addr;
			}
			if (empty ( $userInfo->user_email ) && ! empty ( $old->email )) {
				$userInfo->user_email = $old->email;
				$user->user_email = $old->email;
				$user->is_bind_email = 1;
			}
			if (empty ( $userInfo->user_cell ) && ! empty ( $old->cell ) && CustomHelper::isCell ( $old->cell ))
				$userInfo->user_cell = $old->cell;
			
			if ($userInfo->save () && $user->save ( false )) {
				$old->status = 0;
				$old->save ();
			}
		}
	}
	public function actionGetcellcode() {
		$cell = trim(\Yii::$app->request->post ( "cell","" ));
		$uid = \Yii::$app->request->post ( "uid" );
		if($this->user){
			$uid = $this->user->uid;
		}
		$result = array (
				"status" => 0,
				"msg" => "手机号码有误!" 
		);
		if (! empty ( $cell ) && CustomHelper::isCell ( $cell )) {
			$param ['rand_code'] = CustomHelper::RandCode ();
			$param ['cell'] = $cell;
			BindComponent::SendCellCode ( $param );
			$UserBind = new UserBindLog ();
			$UserBind->uid =$uid;
			$UserBind->bind_type = 1;   
			$UserBind->bind_info = $cell;
			$UserBind->bind_code = $param ['rand_code'];
			$UserBind->insert ();
			$result ["status"] = 1;
			$result ["msg"] = "验证码发送成功";
		}
		CustomHelper::RetrunJson ( $result );
	}
	
	public function actionCheckcode(){
		$cell = trim(\Yii::$app->request->post ( "cell",""));
		$code = trim(\Yii::$app->request->post ( "code","" ));
		$result ["status"]=0;
		if (! Util::checkCellCode ( array (
				"cell" => $cell,
				"cellCode" => $code
		) )) {
			$result ["msg"] = "验证码错误";
		}else{
			$result ["status"]=1;
		}
		CustomHelper::RetrunJson ( $result );
	}
	
	//第三方绑定页面
	public function actionRunbind()
	{
 	    //$this->user = UserMaster::findOne(['uid'=>10]);
	    //$this->checkWechatOauth("/bind/runbind");
        //咕咚绑定数据
        if($this->user){
        	$CodoonBind = RunBind::findOne(['uid' => $this->user->uid,'bind_type'=>1,'bind_status'=>1]);
        	//虎扑绑定数据
        	$HupuBind = RunBind::findOne(['uid' => $this->user->uid,'bind_type'=>2,'bind_status'=>1]);
        	//益动绑定数据
        	$EdoonBind = RunBind::findOne(['uid' => $this->user->uid,'bind_type'=>3,'bind_status'=>1]);
        	//小米绑定数据
        	$XiaomiBind = RunBind::findOne(['uid' => $this->user->uid,'bind_type'=>4,'bind_status'=>1]);
        	//个人芯片绑定
        	$chipBind = Chip::findAll(["uid"=>$this->user->uid,"status"=>Chip::STATUS_BIND]);
        	
        	if(\Yii::$app->request->isAjax){
        		CustomHelper::RetrunJson(["codoonbind"=>$CodoonBind==null?null:ArrayHelper::toArray($CodoonBind),
        				                   "hupubind"=>$HupuBind==null?null:ArrayHelper::toArray($HupuBind),
        				                   "edoonbind"=>$EdoonBind==null?null:ArrayHelper::toArray($EdoonBind),
        				                   "xiaomibind"=>$XiaomiBind==null?null:ArrayHelper::toArray($XiaomiBind),
        				                    "chipbind"=>ArrayHelper::toArray($chipBind)
        		                           ]);
        	}
//         	return $this->renderPartial ( "runbind", [
//         			"codoonbind" => $CodoonBind,
//         			'hupubind' => $HupuBind
//         	] );

        }
      
	}
	
	
	//解绑操作
	public function actionUnbind()
	{
	    $bind_type = \Yii::$app->request->get('bind_type');
// 	    $this->user = UserMaster::findOne(['uid'=>10]);
	    $this->checkWechatOauth("/bind/unbind");
	    //解绑账户
	    $RunBind = RunBind::findOne(['uid' => $this->user->uid,'bind_type'=>$bind_type,'bind_status'=>1]);
	    if ($RunBind)
	    {
	        $RunBind->bind_status = 0;
	        $RunBind->save(false);
	    }
	    return $this->renderPartial ( "unbind", ['bind_type'=>$bind_type] );
	}
	
	public function actionBindchip(){
		$chipNo = trim(\Yii::$app->request->post("chipNo",""));
		if($chipNo&&$this->user){
			$result = ["status"=>1];
			$chip = Chip::findOne(["number"=>$chipNo]);
			if($chip){
				if($chip->status==Chip::STATUS_BIND){
					$result["status"]=-1;
				}elseif($chip->status==Chip::STATUS_DISABLED){
					$result["status"]=-2;
				}elseif ($chip->status==Chip::STATUS_UNBIND){
						$chip->uid=$this->user->uid;
						$chip->status=Chip::STATUS_BIND;
						$chip->bind_time=date("Y-m-d H:i:s");
						if($chip->save(false)){
							$result["status"]=1;
							$result["data"] = ArrayHelper::toArray($chip);
						}else{
							$result["status"]=0;
							$result["msg"] = "系统错误!";
						}
				}
			}else{
				$result["status"]=-2;
			}
			if(\Yii::$app->request->isAjax){
			   CustomHelper::RetrunJson($result);
			}else{
			   throw  new NotFoundHttpException();
			}
		}
	}
}