<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "marketing".
 *
 * @property integer $marketing_id
 * @property string $marketing_name
 * @property string $marketing_desc
 * @property string $rule_desc
 * @property integer $audiences
 * @property integer $club_member_min
 * @property string $start_time
 * @property string $end_time
 * @property string $reg_start_time
 * @property string $reg_end_time
 * @property integer $vote_limit
 * @property string $vote_start_time
 * @property string $vote_end_time
 * @property string $valid_data_start_time
 * @property string $valid_data_end_time
 */
class Marketing extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'marketing';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['marketing_name', 'reg_start_time', 'reg_end_time', 'vote_start_time', 'vote_end_time', 'valid_data_start_time', 'valid_data_end_time'], 'required'],
            [['rule_desc'], 'string'],
            [['audiences', 'club_member_min', 'vote_limit'], 'integer'],
            [['start_time', 'end_time', 'reg_start_time', 'reg_end_time', 'vote_start_time', 'vote_end_time', 'valid_data_start_time', 'valid_data_end_time'], 'safe'],
            [['marketing_name', 'marketing_desc','marketing_icon'], 'string', 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'marketing_id' => 'Marketing ID',
            'marketing_name' => 'Marketing Name',
            'marketing_desc' => '活动描述',
            'rule_desc' => '规则说明',
            'audiences' => '营销对象:1跑团,2跑者',
            'club_member_min' => '参加的跑团会员必须大于',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'reg_start_time' => '报名开始时间',
            'reg_end_time' => '报名结束时间',
            'vote_limit' => '一次投票的数量,0不限制',
            'vote_start_time' => '投票开始时间',
            'vote_end_time' => '投票结束时间',
            'valid_data_start_time' => '有效数据开始时间',
            'valid_data_end_time' => '有效数据结束时间',
        ];
    }
}
