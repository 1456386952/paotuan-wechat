<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "user_mileage_target".
 *
 * @property integer $id
 * @property integer $uid
 * @property double $target
 * @property string $month
 * @property string $update_time
 */
class UserMileageTarget extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_mileage_target';
    }
    
    public static function getTarget($uid,$date){
    	return self::findOne(["uid"=>$uid,"month"=>$date]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'target', 'month', 'update_time'], 'required'],
            [['uid'], 'integer'],
            [['target'], 'number'],
            [['update_time'], 'safe'],
            [['month'], 'string', 'max' => 7]
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
            'target' => '目标跑量',
            'month' => '月份',
            'update_time' => 'Update Time',
        ];
    }
}
