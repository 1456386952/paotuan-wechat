<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "club_member_payment".
 *
 * @property integer $id
 * @property integer $uid
 * @property integer $club_id
 * @property string $year
 * @property string $payment_fee
 * @property integer $trade_status
 * @property string $pay_partner
 * @property string $trade_no
 * @property string $third_party_trade_no
 * @property string $trade_time
 * @property string $create_time
 */
class ClubMemberPayment extends \yii\db\ActiveRecord
{
	
	const PAY_STATUS_TODO =1;
	const PAY_STATUS_FINISHED =2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'club_member_payment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'required'],
            [['uid', 'club_id', 'trade_status'], 'integer'],
            [['payment_fee'], 'number'],
            [['trade_time', 'create_time'], 'safe'],
            [['year'], 'string', 'max' => 10],
            [['pay_partner'], 'string', 'max' => 20],
            [['trade_no'], 'string', 'max' => 50],
            [['third_party_trade_no'], 'string', 'max' => 255]
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
            'year' => 'Year',
            'payment_fee' => 'Payment Fee',
            'trade_status' => 'Trade Status',
            'pay_partner' => 'Pay Partner',
            'trade_no' => 'Trade No',
            'third_party_trade_no' => 'Third Party Trade No',
            'trade_time' => 'Trade Time',
            'create_time' => 'Create Time',
        ];
    }
    
    public static function getNewlyPay($uid,$club_id){
    	$recs = ClubMemberPayment::find()->where(["uid"=>$uid,"club_id"=>$club_id,"trade_status"=>ClubMemberPayment::PAY_STATUS_FINISHED])->orderBy("trade_time desc")->all();
        if(count($recs)>0){
        	return $recs[0];
        }else{
        	return null;
        }
    }
    
    
    
    public function beforeSave($insert)
    {
    	
    	if (parent::beforeSave($insert)) {
    		if($insert){
    			$this->create_time = date("Y-m-d H:i:s");
    		}
    		return true;
    	}
    	return false;
    }
}
