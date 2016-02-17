<?php

namespace paotuan_wechat\models;

use Yii;
use common\component\CustomHelper;
use common\models\UserInfo;
use common\models\User;
use common\models\UserMaster;

/**
 * This is the model class for table "club_member".
 *
 * @property integer $id
 * @property integer $uid
 * @property integer $club_id
 * @property string $openid
 * @property string $passport_name
 * @property integer $gender
 * @property string $cell
 * @property string $net_name
 * @property string $education
 * @property string $id_type
 * @property string $id_number
 * @property string $nationality
 * @property string $birthday
 * @property string $city
 * @property string $email
 * @property string $shirt_size
 * @property string $address
 * @property string $company_info
 * @property string $job_title
 * @property string $emerge_name
 * @property string $emerge_ship
 * @property string $id_image
 * @property string $emerge_cell
 * @property string $run_age
 * @property string $run_line
 * @property string $join_day
 * @property string $sub_group
 * @property string $plan_year
 * @property string $plan_month
 * @property string $blood_type
 * @property integer $shoe_size
 * @property string $health_check_certificate
 * @property string $marathon_score_certificate
 * @property integer $cross_race_score_certificate
 * @property integer $height
 * @property integer $weight
 * @property string $medical_history
 * @property integer $plan_marathon
 * @property integer $plan_halfmarathon
 * @property string $recommander
 * @property string $recommande_info
 * @property string $apply_intro
 * @property string $run_plan
 * @property string $runner_intro
 * @property string $expiration_date
 * @property integer $member_status
 * @property integer $runner_power
 * @property string $update_time
 * @property string $create_time
 * @property string $reserved_field_1
 * @property string $reserved_field_2
 * @property string $reserved_field_3
 * @property string $reserved_field_4
 * @property string $reserved_field_5
 * @property string $reserved_field_6
 * @property string $reserved_field_7
 * @property string $reserved_field_8
 * @property string $marathon_max_score
 * @property string $half_marathon_max_score
 * @property string $ten_km_max_score
 */
class ClubMember extends \yii\db\ActiveRecord
{
	public $id_copy_back;
	const STATUS_RE = 1;
	const STATUS_WAIT = 2;
	const STATUS_NORMAL = 4;
	const STATUS_PAY = 5;
	const STATUS_SIMPLE = 3;
	const STATUS_PAY_WAIT_FOR_DONE = 6;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'club_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'required'],
            [['uid', 'club_id', 'gender', 'shoe_size', 'plan_marathon', 'plan_halfmarathon', 'member_status', 'runner_power', 'id_type','credits','is_default','set_default_interval'], 'integer'],
            [['birthday', 'join_day', 'expiration_date', 'update_time', 'create_time','set_default_time'], 'safe'],
            [['plan_year', 'plan_month','height', 'weight'], 'number'],
            [['runner_intro',"id_copy_back"], 'string'],
            [['openid', 'passport_name', 'nationality'], 'string', 'max' => 40],
            [['cell'], 'string', 'max' => 15],
            [['net_name', 'sub_group'], 'string', 'max' => 64],
            [['education', 'id_number', 'city', 'emerge_name'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 60],
            [['shirt_size'], 'string', 'max' => 30],
            [['address', 'id_image', 'run_age', 'run_line','cross_race_score_certificate','health_check_certificate', 'marathon_score_certificate', 'marathon_max_score', 'half_marathon_max_score', 'ten_km_max_score','morning_pulse','member_wechat'], 'string', 'max' => 120],
            [['company_info'], 'string', 'max' => 400],
            [['job_title'], 'string', 'max' => 100],
            [['emerge_ship'], 'string', 'max' => 14],
            [['emerge_cell'], 'string', 'max' => 16],
            [['blood_type'], 'string', 'max' => 10],
            [['medical_history'], 'string', 'max' => 2000],
            [['recommander'], 'string', 'max' => 30],
            [['recommande_info', 'run_plan'], 'string', 'max' => 300],
            [['apply_intro', 'reserved_field_1', 'reserved_field_2', 'reserved_field_3', 'reserved_field_4', 'reserved_field_5', 'reserved_field_6', 'reserved_field_7', 'reserved_field_8','reserved_field_9','reserved_field_10','reserved_field_11','reserved_field_12','reserved_field_13','reserved_field_14','reserved_field_15'], 'string', 'max' => 600]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'club_id' => 'Club ID',
            'openid' => 'Openid',
            'passport_name' => 'Passport Name',
            'gender' => 'Gender',
            'cell' => 'Cell',
            'net_name' => 'Net Name',
            'education' => 'Education',
            'id_type' => 'Id Type',
            'id_number' => 'Id Number',
            'nationality' => 'Nationality',
            'birthday' => 'Birthday',
            'city' => 'City',
            'email' => 'Email',
            'shirt_size' => 'Shirt Size',
            'address' => 'Address',
            'company_info' => 'Company Info',
            'job_title' => 'Job Title',
            'emerge_name' => 'Emerge Name',
            'emerge_ship' => 'Emerge Ship',
            'id_image' => 'Id Image',
            'emerge_cell' => 'Emerge Cell',
            'run_age' => 'Run Age',
            'run_line' => 'Run Line',
            'join_day' => 'Join Day',
            'sub_group' => 'Sub Group',
            'plan_year' => 'Plan Year',
            'plan_month' => 'Plan Month',
            'blood_type' => 'Blood Type',
            'shoe_size' => 'Shoe Size',
            'health_check_certificate' => 'Health Check Certificate',
            'marathon_score_certificate' => 'Marathon Score Certificate',
            'cross_race_score_certificate' => 'Cross Race Score Certificate',
            'height' => 'Height',
            'weight' => 'Weight',
            'medical_history' => 'Medical History',
            'plan_marathon' => 'Plan Marathon',
            'plan_halfmarathon' => 'Plan Halfmarathon',
            'recommander' => 'Recommander',
            'recommande_info' => 'Recommande Info',
            'apply_intro' => 'Apply Intro',
            'run_plan' => 'Run Plan',
            'runner_intro' => 'Runner Intro',
            'expiration_date' => 'Expiration Date',
            'member_status' => 'Member Status',
            'runner_power' => 'Runner Power',
            'update_time' => 'Update Time',
            'create_time' => 'Create Time',
            'reserved_field_1' => 'Reserved Field 1',
            'reserved_field_2' => 'Reserved Field 2',
            'reserved_field_3' => 'Reserved Field 3',
            'reserved_field_4' => 'Reserved Field 4',
            'reserved_field_5' => 'Reserved Field 5',
            'reserved_field_6' => 'Reserved Field 6',
            'reserved_field_7' => 'Reserved Field 7',
            'reserved_field_8' => 'Reserved Field 8',
            'marathon_max_score' => 'Marathon Max Score',
            'half_marathon_max_score' => 'Half Marathon Max Score',
            'ten_km_max_score' => 'Ten Km Max Score',
        ];
    }
    
    public function getNeedpay(){
    	return ClubMemberPayment::findOne(["club_id"=>$this->club_id,"uid"=>$this->uid,"trade_status"=>1]);
    }
    
    public function setStatusPay(){
    	$trans = \Yii::$app->db->beginTransaction();
    	$this->member_status=self::STATUS_PAY;
    	$this->expiration_date=null;
    	$payment = ClubMemberPayment::findOne(["club_id"=>$this->club_id,"uid"=>$this->uid,"trade_status"=>'1']);
    	if(is_null($payment)){
    		$payment = new ClubMemberPayment();
    		$payment->setIsNewRecord(true);
    		$payment->club_id = $this->club_id;
    		$payment->trade_no= CustomHelper::CreateOrderID(time().$this->uid);
    		$payment->uid=$this->uid;
    		$payment->pay_partner="wxpay";
    		$payment->trade_status=1;
    	}else{
    		$payment->trade_no= CustomHelper::CreateOrderID(time().$this->uid);
    	}
    
    
    	if($this->update()&&$payment->save()){
    		$trans->commit();
    	}else{
    		$trans->rollBack();
    		return false;
    	}
    	return true;
    }
    
    public static function getMemberByCell($cell){
    	return ClubMember::findOne(["cell"=>$cell]);
    }
    
    public static function getClubMembers($clubid,$offset=0,$limit=25){
       return ClubMember::find()->where("club_id =:clubid and (member_status=".self::STATUS_SIMPLE." or member_status = ".self::STATUS_NORMAL.")",[":clubid"=>$clubid])->with("user")->offset($offset)->limit($limit)->orderBy("member_status,create_time desc")->all();
    }
       
    public function getMileages(){
    	return $this->hasMany(Mileage::className(), ["uid"=>uid]);
    }
    
  public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['uid' => 'uid']);
    }
    
    public function getUser()
    {
    	return $this->hasOne(UserMaster::className(), ['uid' => 'uid'])->inverseOf('clubMembers');
    }
    
    public function getClub(){
    	return $this->hasOne(Club::className(), ["clubid"=>"club_id"])->where("(club_status=".Club::CLUB_STATUS_NORMAL." or club_status=".Club::CLUB_STATUS_PROCESS.") and club_type=1")->with("members");
    }
    
    public static function getNormalMember($clubid,$uid){
    	return self::find()->where("club_id =:club_id and uid=:uid and (member_status=".self::STATUS_SIMPLE." or member_status = ".self::STATUS_NORMAL.")",[":club_id"=>$clubid,":uid"=>$uid])->one();
    }
    
    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert)) {
    		$this->update_time = date("Y-m-d H:i:s");
    		if($insert){
    			$this->create_time = date("Y-m-d H:i:s");
    		}
    		return true;
    	}
    	return false;
    }
}
