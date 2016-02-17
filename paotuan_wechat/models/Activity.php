<?php

namespace paotuan_wechat\models;

use Yii;
use yii\db\Query;
use paotuan_wechat\component\Util;
use common\models\UserMaster;

/**
 * This is the model class for table "activity".
 *
 * @property integer $act_id
 * @property integer $uid
 * @property string $act_title
 * @property string $act_image
 * @property string $act_location
 * @property string $lat_lon
 * @property string $act_desc
 * @property integer $rang_limit
 * @property integer $total_limit
 * @property integer $act_type
 * @property integer $need_register
 * @property string $register_end_time
 * @property double $register_fee
 * @property string $act_start_time
 * @property string $act_end_time
 * @property integer $gps_check_range
 * @property integer $master_id
 * @property string $check_code
 * @property string $act_check_start_time
 * @property string $act_check_end_time
 * @property string $act_create_time
 */
class Activity extends \yii\db\ActiveRecord
{
	const  STATUS_DEL=0;
	const  STATUS_NORMAL=1;
	const  STATUS_END=2;
	const  LIMIT_RANGE_CLUB=1;
	const  LIMIT_RANGE_NO=0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'act_start_time', 'act_end_time'], 'required'],
            [['uid', 'rang_limit', 'total_limit', 'act_type', 'need_register', 'gps_check_range',"reg_num","sign_num",'act_location','device_id','master_id'], 'integer'],
            [['act_desc','qrcode'], 'string'],
            [['register_end_time', 'act_start_time', 'act_end_time', 'act_check_start_time', 'act_check_end_time', 'act_create_time'], 'safe'],
            [['register_fee','act_status'], 'number'],
            [['act_title', 'act_image'], 'string', 'max' => 120],
            [['check_code'], 'string', 'max' => 10],
        ];
    }

    
    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert)) {
    		if ($this->isNewRecord) {
    			$this->act_create_time = date("Y-m-d H:i:s");
    		}
    		return true;
    	}
    	return false;
    }
    
    public  function getLocation(){
    	return $this->hasOne(ActivityLocation::className(), ["id"=>"act_location"])->inverseOf('acts');
    }
    
    public  function getOwner(){
    	return $this->hasOne(UserMaster::className(), ["uid"=>"uid"]);
    }
    
    public  function getClub(){
    	return  $this->hasOne(Club::className(), ["clubid"=>"club_id"])
    	         ->viaTable('activity_club', ['act_id' => 'act_id']);
    }
    
    public static function findByClub($clubid,$limit=5,$offset=0){
    	return self::find()->where("act_id in (select act_id from activity_club where club_id = '".$clubid."')")->limit($limit)->offset($offset)->orderBy("act_create_time desc")->all();
    }
    
    public function getCreditRules(){
    	return $this->hasMany(CreditRule::className(),["relatedid"=>'act_id'])->where("action ='act_reg' or action ='act_sign'");
    }
    
    public static function getUserActs($uid,$offset=0,$limit=20){
    	$query = new Query();
    	return $query->select("act.*,act.uid as act_create_user,au.*,au.uid as act_opt_user,l.name as location_name,act.act_id as act_id")->from(Activity::tableName()." as act")
    	     ->leftJoin(ActivityUser::tableName()." as au","act.act_id=au.act_id")
    	     ->leftJoin(ActivityLocation::tableName()." as l","act.act_location=l.id")
    	     ->where("(au.uid=$uid or act.uid=$uid) and act_status=".self::STATUS_NORMAL)->distinct(true)->offset($offset)->limit($limit)->orderBy("au.checkin_time desc, act.act_start_time desc")->all();
    }
    
    public static function getClubActs($clubid,$uid,$offset=0,$limit=20){
    	$query = new Query();
    	return $query->select("act.*,act.uid as act_create_user,au.*,{intVal($uid)} as act_opt_user,l.name as location_name,act.act_id as act_id")->from(Activity::tableName()." as act")
    	->leftJoin("(select * from  `activity_user` where uid=$uid) as au","act.act_id=au.act_id")
    	->leftJoin(ActivityLocation::tableName()." as l","act.act_location=l.id")
    	->leftJoin(ActivityClub::tableName()." as ac","act.act_id=ac.act_id")
    	->where("ac.club_id=$clubid and act_status=".self::STATUS_NORMAL)->distinct(true)->offset($offset)->limit($limit)->orderBy("act.act_start_time desc,au.checkin_time desc")->all();
    }
	

	public static function getIbeaconActs($ibeacon_id,$uid){
    	$query = new Query();
    	return $query->select("act.*,act.uid as act_create_user,au.*,{intVal($uid)} as act_opt_user,l.name as location_name,act.act_id as act_id")->from(Activity::tableName()." as act")
    	->leftJoin("(select * from  `activity_user` where uid=$uid) as au","act.act_id=au.act_id")
    	->leftJoin(ActivityLocation::tableName()." as l","act.act_location=l.id")
    	->where("act.device_id=$ibeacon_id and act_status=".self::STATUS_NORMAL)->distinct(true)->orderBy("act.act_start_time desc,au.checkin_time desc")->all();
    }
    
    public static function canReg($act){
    	if ($act->act_status==Activity::STATUS_NORMAL&&strtotime ( $act->register_end_time ) > time ()) {
    		return true;
    	}else{
    		return false;
    	}
    }
    
    public static function canCheck($act){
    	if ($act->act_status==Activity::STATUS_NORMAL&&strtotime ( $act->act_check_start_time ) <= time () && strtotime ( $act->act_check_end_time ) >= time ()) {
    		return true;
    	}
    	return false;
    }
    
    public  function getConfigs(){
    	$configs=ActivityConfig::find()->where(["act_id"=>$this->act_id])->orderBy("sort asc")->all();
    	if(count($configs)==0&&$this->master_id>0){
    		$configs=ActivityConfig::find()->where(["act_id"=>$this->master_id])->orderBy("sort asc")->all();
    	}
    	return $configs;
    }
    
    public static function makeActs($array){
    	if(!$array){
    		return [];
    	}
    	$tmp = new \stdClass();
    	for($i=0;$i!=count($array);$i++){
    		$act = $array[$i];
    		$act["date"] = date("Y-m-d",strtotime($act["act_start_time"]));
    		$act["start_time"] = date("H:i",strtotime($act["act_start_time"]));
    		$act["end_time"] = date("H:i",strtotime($act["act_end_time"]));
    		$act["is_end"] = Util::isEnd($act["act_end_time"]);
    		$act["image"] = explode(",",$act["act_image"])[0];
    		$tmp->act_status=$act["act_status"];
    		$tmp->register_end_time=$act["register_end_time"];
    		$tmp->need_register=$act["need_register"];
    		$tmp->act_check_start_time =$act["act_check_start_time"];
    		$tmp->act_check_end_time =$act["act_check_end_time"];
    		$act["can_reg"] = Activity::canReg($tmp);
    		$act["can_check"] = Activity::canCheck($tmp);
    		$array[$i]=$act;
    	}
    	return $array;
    }
}
