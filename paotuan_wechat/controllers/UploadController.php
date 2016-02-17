<?php

namespace paotuan_wechat\controllers;

use Yii;
use yii\web\Controller;
use common\component\CustomHelper;
use paotuan_wechat\component\Util;
use common\component\UpYun;
use common\component\ImageLibrary;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use paotuan_wechat\component\PaotuanController;
use yii\db\Connection;
use yii\base\Exception;
use paotuan_wechat\models\bisai\RaceRunnerImg;
use paotuan_wechat\models\bisai\ResultChipInfo;
use paotuan_wechat\models\bisai\ResultRacer;
use paotuan_wechat\component\WxUtil;


class UploadController extends PaotuanController {
	public $enableCsrfValidation = false;
	
	public function actionUploadimg() {
		if (Yii::$app->request->isPost) {
			$result = array (
					"status" => 0,
					 "images"=>[]
			);
			try {
				$type = \Yii::$app->request->post ( "img_type" );
				$file_id = \Yii::$app->request->post ( "file_id" );
				
				if(count($_FILES)!=0){
					$upyunInfo = \Yii::$app->params ['upyun'];
					$upyun = new UpYun ( $upyunInfo ['bucketname'], $upyunInfo ['username'], $upyunInfo ['password'] );
					foreach($_FILES as $file){
						if(is_array($file["tmp_name"])){
							for($i=0;$i!=count($file["tmp_name"]);$i++){
								$tmpName = $file["tmp_name"][$i];
								$fileName =$file["name"][$i];
								$ex = substr($fileName, stripos($fileName, "."));
								$tmpfile = CustomHelper::randomPassword ( 10 ) . $ex;
								$path = "/{$type}/".time()."/{$tmpfile}";
								$info = $upyun->writeFile ( $path, fopen ($tmpName, 'r+' ), true );
								if (! empty ( $info )) {
									$result ["status"] = 1;
									$result ["id"] = $file_id;
									array_push($result["images"], $path);
								}
							}
						}else{
							$tmpName = $file["tmp_name"];
							$fileName =$file["name"];
							$ex = substr($fileName, stripos($fileName, "."));
							$tmpfile = CustomHelper::randomPassword ( 10 ) . $ex;
							$path = "/{$type}/{$tmpfile}";
							$info = $upyun->writeFile ( $path, fopen ($tmpName, 'r+' ), true );
							if (! empty ( $info )) {
								$result ["status"] = 1;
								$result ["id"] = $file_id;
								array_push($result["images"], $path);
							}
						}
					}
				}
					
			} catch ( Exception $e ) {
			}
		}
		CustomHelper::RetrunJson($result);
	}
	
	public function actionUploadimgforrace() {
		if (Yii::$app->request->isPost) {
			$result = array (
					"status" => 0
			);
				$race_id = trim(\Yii::$app->request->post ( "race_id","" ));
				$mac = trim(\Yii::$app->request->post ( "chip_mac","" ));
				$cp_index = trim(\Yii::$app->request->post ( "cp_index","" ));
				if(!$race_id){
					$result["msg"]="required race_id";
					CustomHelper::RetrunJson($result);
				}
				if(!$mac){
					$result["msg"]="required chip_mac";
					CustomHelper::RetrunJson($result);
				}
				if(!$cp_index){
					$result["msg"]="required cp_index";
					CustomHelper::RetrunJson($result);
				}
				if(count($_FILES)!=0){
					$upyunInfo = \Yii::$app->params ['upyun'];
					$upyun = new UpYun ( $upyunInfo ['bucketname'], $upyunInfo ['username'], $upyunInfo ['password'] );
					foreach($_FILES as $file){
// 						if(is_array($file["tmp_name"])){
// 							for($i=0;$i!=count($file["tmp_name"]);$i++){
// 								$tmpName = $file["tmp_name"][$i];
// 								$fileName =$file["name"][$i];
// 								$ex = substr($fileName, stripos($fileName, "."));
// 								$tmpfile = CustomHelper::randomPassword ( 10 ) . $ex;
// 								$path = "/{$type}/".time()."/{$tmpfile}";
// 								$info = $upyun->writeFile ( $path, fopen ($tmpName, 'r+' ), true );
// 								if (! empty ( $info )) {
// 									$result ["status"] = 1;
// 								}
// 							}
// 						}else{
							$tmpName = $file["tmp_name"];
							$fileName =$file["name"];
							$ex = substr($fileName, stripos($fileName, "."));
							$tmpfile = CustomHelper::randomPassword ( 10 ) . $ex;
							$path = "/race/{$race_id}/{$mac}/{$cp_index}/".time()."/$tmpfile";
							$info = $upyun->writeFile ( $path, fopen ($tmpName, 'r+' ), true );
							if (! empty ( $info )) {
								$result ["status"] = 1;
								try{
								   $img = new RaceRunnerImg();
								  $rci = ResultChipInfo::findOne(["race_id"=>$race_id,"chip_mac"=>$mac]);
								  if($rci){
								  	$rr = ResultRacer::findOne(["chip_no"=>$rci->chip_no,"race_id"=>$race_id]);
								  	if($rr){
								  		$img->runner_id = $rr->runner_id;
								  	}
								  }
								  $img->race_id=$race_id;
								  $img->chip_mac=$mac;
								  $img->cp_id=$cp_index;
								  $img->img_url =$path;
								  if(!$img->save()){
								  	$result ["status"]=0;
								  	$result ["msg"]="db error";
								  }
								}catch (Exception $e)
								{
									$upyun->delete($path);
									$result["msg"]="upyun upload error";
								}
 							}
// 						}
					}
				}else{
					$result["msg"]="no file upload";
				}
				CustomHelper::RetrunJson($result);
		}
	}
	public function actionWxsdkupload() {
		$serverids = \Yii::$app->request->post("server_ids");
		$type = \Yii::$app->request->post("type");
		if($serverids){
			$result["status"]=0;
			$result["images"]=[];
			$serverids = explode(",", $serverids);
			foreach ($serverids as $id){
				if(!$id||!trim($id)){
					continue;
				}
				$file = WxUtil::downLoadFile($id);
				if($file){
					try{
					$upyunInfo = \Yii::$app->params ['upyun'];
					$upyun = new UpYun ( $upyunInfo ['bucketname'], $upyunInfo ['username'], $upyunInfo ['password'] );
					$path = "/{$type}/".time()."/".CustomHelper::randomPassword ( 10 ).".jpg";
					$info = $upyun->writeFile ( $path,fopen ($file, 'r+' ), true );
					if (! empty ( $info )) {
						$result ["status"] = 1;
						array_push($result["images"], $path);
					}
					}catch(\Exception $e){
						throw new Exception($e->getMessage());
					}finally {
						unlink($file);
					}
				}
			}
			CustomHelper::RetrunJson($result);
		}
	}
	
}
