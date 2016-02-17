<?php

namespace paotuan_wechat\controllers;

use Yii;
use yii\web\Controller;
use common\component\CustomHelper;
use paotuan_wechat\component\Util;
use paotuan_wechat\models\Club;
use yii\helpers\ArrayHelper;
use common\component\BindComponent;
use common\models\UserBindLog;
use paotuan_wechat\models\Analysis;

class AnalysisController extends Controller {
	public $enableCsrfValidation = false;
	private $allowCell = ["13818181974","15900696904","13482295614"];
	private $cellProvince = ["13818181974"=>"all","15900696904"=>"all","13482295614"=>"all"];
	
	public function actionClubs() {
		$cell = trim(\Yii::$app->request->post ( "cell", ""));
		$code = trim(\Yii::$app->request->post ( "code", ""));
		$result =[
				"status" => 0,
				"msg" => "手机号码有误!"
		];
		$returnList=[];
		$total = ["member_sum"=>0,"member_sum1"=>0,"create_date"=>"","create_date1"=>"","member_percent"=>0];
		if (! empty ( $cell ) && CustomHelper::isCell ( $cell )&&in_array($cell, $this->allowCell)===true) {
			if (! Util::checkCellCode ( array (
					"cell" => $cell,
					"cellCode" => trim($code)
			) )) {
				$result ["msg"] = "验证码错误";
				CustomHelper::RetrunJson ( $result );
				return;
			}
			$result["status"]=1;
			$result["msg"]="";
			$result["data"]=[];
			$provinces = $this->cellProvince[$cell];
			$r = Analysis::getRecentClubsData($provinces);
			if(count($r)==0){
				CustomHelper::RetrunJson ($result);
			}
				$data = $r["data"];
				$data1 =[];
				if(isset($r["data1"])){
					$data1 =$r["data1"];
				} ;
				foreach($data as $d){
					$club = $d->club;
					$tmp=ArrayHelper::toArray($d);
					$tmp["club_name"]=$club->club_name;
					$tmp["member_percent"]=0;
					$total["member_sum"]+= $tmp["member_sum"];
					$total["create_date"] =$tmp["create_date"];
					foreach ($data1 as $d1){
						$tmp1 = ArrayHelper::toArray($d1);
						$total["create_date1"] =$tmp1["create_date"];
						$tmp["create_date1"] =$tmp1["create_date"];
						if($tmp["club_id"]==$tmp1["club_id"]){
							$member_sum=$tmp["member_sum"];
							$member_sum1 =$tmp1["member_sum"];
							$total["member_sum1"] += $tmp1["member_sum"];
							$tmp["member_sum1"]=$member_sum1;
							$tmp["member_percent"]=0;
							if($member_sum1>0){
								$tmp["member_percent"] = round(($member_sum-$member_sum1)/$member_sum1*100,2);
							}
			
							$mileage_count = $tmp["mileage_count"];
							$mileage_count1 = $tmp1["mileage_count"];
							$tmp["mileage_count1"] = $mileage_count1;
							$tmp["mileage_count_new"] = $mileage_count-$mileage_count1;
			
							$mileage_sum = $tmp["mileage_sum"];
							$mileage_sum1 = $tmp1["mileage_sum"];
							$tmp["mileage_sum1"] = $mileage_sum1;
			
							$activity_sum =  $tmp["activity_sum"];
							$activity_sum1 = $tmp1["activity_sum"];
							$tmp["activity_sum1"] = $activity_sum1;
							$tmp["activity_sum_new"] = $activity_sum-$activity_sum1;
			
							$activity_checkins =  $tmp["activity_checkins"];
							$activity_checkins1 = $tmp1["activity_checkins"];
							$tmp["activity_checkins1"] = $activity_checkins1;
							$tmp["activity_checkins_new"] = $activity_checkins-$activity_checkins1;
							
							$activity_regs =  $tmp["activity_regs"];
							$activity_regs1 = $tmp1["activity_regs"];
							$tmp["activity_regs1"] = $activity_regs1;
							$tmp["activity_regs_new"] = $activity_regs-$activity_regs1;
							break;
						}
					}
					array_push($returnList, $tmp);
				}
				if($provinces!="all"){
					if($total["member_sum1"]>0){
						$total["member_percent"] = round(($total["member_sum"]-$total["member_sum1"])/$total["member_sum1"]*100,2);
					}
					array_unshift($returnList,$total);
				}
				$result["data"] = $returnList;
		}
	
		CustomHelper::RetrunJson($result);
	}
	
	public function actionGetcellcode() {
		$cell = trim(\Yii::$app->request->post ( "cell",""));
		$result = array (
				"status" => 0,
				"msg" => "手机号码有误!"
		);
		if (! empty ( $cell ) && CustomHelper::isCell ( $cell )&&in_array($cell, $this->allowCell)===true) {
			$param ['rand_code'] = CustomHelper::RandCode ();
			$param ['cell'] = $cell;
			BindComponent::SendCellCode ( $param,"跑步去手机登录验证码" );
			$UserBind = new UserBindLog ();
			$UserBind->uid=0;
			$UserBind->bind_type = 1;
			$UserBind->bind_info = $cell;
			$UserBind->bind_code = $param ['rand_code'];
			$UserBind->insert ();
			$result ["status"] = 1;
			$result ["msg"] = "验证码发送成功";
		}
		CustomHelper::RetrunJson ( $result );
	}
}
