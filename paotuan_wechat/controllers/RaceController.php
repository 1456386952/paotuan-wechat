<?php

namespace paotuan_wechat\controllers;

use Yii;
use paotuan_wechat\component\PaotuanController;
use paotuan_wechat\models\bisai\RaceMaster;
use common\component\CustomHelper;
use yii\helpers\ArrayHelper;
use paotuan_wechat\models\bisai\RaceRunnerImg;
use common\component\MailManage;
use common\models\Act;
use yii\web\NotFoundHttpException;

class RaceController extends PaotuanController {
	private $channel;
	private $race;
	public function beforeAction($action) {
	   if(\Yii::$app->request->isAjax){
	   	    $act_id = $_REQUEST["act_id"];
	   	    if($act_id){
	   	    	$act = Act::find()->where("actid =:act_id and act_status <> :status",[":act_id"=>$act_id,":status"=>Act::ACT_STATUS_CANCEL])->one();
	   	        if($act){
	   	        	$this->race =$act; 
	   	        }else{
	   	        	throw new NotFoundHttpException();
	   	        }
	   	    }
		}else{
			throw new NotFoundHttpException();
		}
		return parent::beforeAction($action);
	}
	
	public function actionList(){
		$offset = \Yii::$app->request->post("offset",0);
		$limit= \Yii::$app->request->post("limit",20);
		$search_key =  trim(\Yii::$app->request->post("search_key",""));
		if($search_key!==""){
			$rm= RaceMaster::find()->where("display=1 and (disp_name like :search_key or official_name like :search_key) ",[":search_key"=>"%$search_key%"])->orderBy("race_time desc")->offset($offset)->limit($limit)->all();
		}else{
		 	$rm= RaceMaster::find()->where(["display"=>1])->orderBy("race_time desc")->offset($offset)->limit($limit)->all();
		}
		 CustomHelper::RetrunJson(ArrayHelper::toArray($rm,["paotuan_wechat\models\bisai\RaceMaster"
		 													=>["race_id","short_name","disp_name","race_time","city","place","race_icon"]]));
	}
	
	public function actionRacerimages(){
		$race_id = trim(\Yii::$app->request->post("race_id",""));
		$runner_id= trim(\Yii::$app->request->post("runner_id",""));
		if($runner_id){
			$images = RaceRunnerImg::findAll(["runner_id"=>$runner_id]);
			CustomHelper::RetrunJson(ArrayHelper::toArray($images));
		}
	}
	
	public function actionRequestimages(){
		$email = trim(\Yii::$app->request->post("email",""));
		$race_id = trim(\Yii::$app->request->post("race_id",""));
		$runner_id = trim(\Yii::$app->request->post("runner_id",""));
		$result = ["status"=>0];
		if($email&&CustomHelper::isEmail($email)){
			if($race_id&&$runner_id){
				$mail = new MailManage();
				$param=["title"=>"获取比赛照片",
						"email"=>"hello@runningtogether.net",
						"body"=>"跑友请求下载比赛照片:<br><b>race_id:</b>$race_id<br><b>runner_id:</b>$runner_id<br><b>接收邮箱:</b>$email"
				];
				$mail->SendRegisterSuccEmail($param);
			}
			$result["status"]="1";
		}else{
			$result["msg"]="请输入正确的邮箱!";
		}
		CustomHelper::RetrunJson($result);
	}
}

