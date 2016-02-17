<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "credit_rule".
 *
 * @property string $rid
 * @property string $club_id
 * @property integer $relatedid
 * @property string $desc
 * @property string $action
 * @property integer $cycle_type
 * @property integer $cycle_time
 * @property integer $reward_num
 * @property integer $credits
 */
class CreditRule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'credit_rule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['club_id', 'relatedid', 'cycle_type', 'cycle_time', 'reward_num', 'credits'], 'integer'],
            [['desc'], 'string', 'max' => 50],
            [['action'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rid' => '规则ID',
            'club_id' => '自定义策略的跑团ID',
            'relatedid' => '规则关联对象ID',
            'desc' => '规则描述',
            'action' => '规则action唯一KEY',
            'cycle_type' => '奖励周期0:一次;1:每天;2:整点;3:间隔分钟;4:不限;',
            'cycle_time' => '间隔时间',
            'reward_num' => '奖励次数',
            'credits' => '积分操作值',
        ];
    }
}
