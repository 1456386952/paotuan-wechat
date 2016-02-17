<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "act_stay".
 *
 * @property integer $stayid
 * @property integer $uid
 * @property integer $actid
 * @property integer $orderid
 * @property integer $detailid
 * @property string $passport_name
 * @property string $cell
 * @property string $stay_remark
 * @property integer $stay_type
 * @property integer $stay_status
 * @property integer $payment_status
 * @property string $update_time
 * @property string $create_time
 */
class ActStay extends \yii\db\ActiveRecord
{
	const  STATUS_CANCEL=0;
	const  STATUS_NORMAL=1;
	const  STATUS_LOCKED=2;
	
	const  TYPE_HOTEL=1;
	const  TYPE_TRAFFIC=2;
	
	const PAY_STATUS_FREE=0;//免费
	const PAY_STATUS_WAIT=1;//待支付
	const PAY_STATUS_DONE=2;//已支付
	const PAY_STATUS_CANCEL=3;//取消支付
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_stay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'actid', 'orderid', 'detailid', 'passport_name', 'cell'], 'required'],
            [['uid', 'actid', 'orderid', 'detailid', 'stay_type', 'stay_status', 'payment_status'], 'integer'],
            [['update_time', 'create_time'], 'safe'],
            [['passport_name', 'cell', 'stay_remark'], 'string', 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'stayid' => 'Stayid',
            'uid' => 'Uid',
            'actid' => 'Actid',
            'orderid' => 'Orderid',
            'detailid' => 'Detailid',
            'passport_name' => 'Passport Name',
            'cell' => 'Cell',
            'stay_remark' => 'Stay Remark',
            'stay_type' => 'Stay Type',
            'stay_status' => 'Stay Status',
            'payment_status' => 'Payment Status',
            'update_time' => 'Update Time',
            'create_time' => 'Create Time',
        ];
    }
    
    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert)) {
    		$this->update_time = new Expression('NOW()');
    		if ($this->isNewRecord) {
    			$this->create_time = new Expression('NOW()');
    		}
    		return true;
    	}
    	return false;
    }
}
