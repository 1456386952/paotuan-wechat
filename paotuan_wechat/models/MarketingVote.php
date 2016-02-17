<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "marketing_vote".
 *
 * @property integer $id
 * @property integer $uid
 * @property integer $club_id
 * @property integer $marketing_id
 * @property string $create_time
 */
class MarketingVote extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'marketing_vote';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'club_id', 'marketing_id', 'create_time'], 'required'],
            [['uid', 'club_id', 'marketing_id'], 'integer'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => '投票人',
            'club_id' => '跑团id',
            'marketing_id' => '营销活动id',
            'create_time' => 'Create Time',
        ];
    }
    
    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert)) {
    		if ($this->isNewRecord) {
    			$this->create_time =  date("Y-m-d H:i:s");
    		}
    		return true;
    	}
    	return false;
    }
    
}
