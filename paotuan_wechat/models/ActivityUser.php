<?php

namespace paotuan_wechat\models;

use Yii;
use common\models\UserMaster;
use common\models\OrderMaster;

/**
 * This is the model class for table "activity_user".
 *
 * @property integer $id
 * @property integer $act_id
 * @property integer $uid
 * @property integer $isreg
 * @property integer $ischeckin
 * @property integer $status
 * @property string $checkin_time
 * @property string $reg_time
 * @property string $reserved_field_1
 * @property string $reserved_field_2
 * @property string $reserved_field_3
 * @property string $reserved_field_4
 * @property string $reserved_field_5
 * @property string $reserved_field_6
 * @property string $reserved_field_7
 * @property string $reserved_field_8
 */
class ActivityUser extends \yii\db\ActiveRecord
{
	const CHECK_TYPE_CODE=1;
	const CHECK_TYPE_QRCODE=2;
	const CHECK_TYPE_SHAKE=3;
	const STATUS_NEED_PAY=0;
	const STATUS_NORMAL=1;
	const STATUS_CANCEL=2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['act_id', 'uid','status'], 'required'],
            [['act_id', 'uid', 'isreg','ischeckin',"checkin_type",'order_id',"status"], 'integer'],
            [['checkin_time', 'reg_time'], 'safe'],
            [['reserved_field_1', 'reserved_field_2', 'reserved_field_3', 'reserved_field_4', 'reserved_field_5', 'reserved_field_6', 'reserved_field_7', 'reserved_field_8', 'reserved_field_9', 'reserved_field_10', 'reserved_field_11', 'reserved_field_12', 'reserved_field_13', 'reserved_field_14', 'reserved_field_15','m_reserved_field_1', 'm_reserved_field_2', 'm_reserved_field_3', 'm_reserved_field_4', 'm_reserved_field_5', 'm_reserved_field_6', 'm_reserved_field_7', 'm_reserved_field_8', 'm_reserved_field_9', 'm_reserved_field_10', 'm_reserved_field_11', 'm_reserved_field_12', 'm_reserved_field_13', 'm_reserved_field_14', 'm_reserved_field_15'], 'safe'],
            [['passport_name', 'nick_name','gender','cell','email','nationality','city','id_type','id_number','id_image','shirt_size','emerge_name','emerge_ship','emerge_cell','marathon_score_certificate','health_check_certificate','address','cross_race_score_certificate','company_info','job_title','marathon_max_score','half_marathon_max_score','ten_km_max_score','blood_type','run_age'], 'safe'],
            [['birthday'],'date'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'act_id' => 'Act ID',
            'uid' => 'Uid',
            'isreg' => 'Isreg',
            'ischeckin' => 'Ischeckin',
            'checkin_time' => 'Checkin Time',
            'reg_time' => 'Reg Time',
        ];
    }
    
    public function getUser()
    {
    	return $this->hasOne(UserMaster::className(), ['uid' => 'uid']);
    }
    
    public function getOrder(){
    	return $this->hasOne(OrderMaster::className(), ['orderid' => 'order_id']);
    }
}
