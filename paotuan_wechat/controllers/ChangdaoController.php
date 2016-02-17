<?php

namespace paotuan_wechat\controllers;

use Yii;
use yii\web\Controller;
use paotuan_wechat\models\ChangDao;

class ChangdaoController extends Controller {
	public $enableCsrfValidation = false;
	public function actionIndex(){
		$id_no = trim(\Yii::$app->request->post("id_no",""));
		$r=null;
		$query=false;
		if($id_no){
			$r = ChangDao::findOne(["id_no"=>$id_no]);
			$query=true;
		}
		return $this->renderPartial("index",["r"=>$r,"query"=>$query]);
	}
}
