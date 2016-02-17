<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "register_invite_config".
 *
 * @property integer $inviteid
 * @property integer $courseid
 * @property string $invite_code
 * @property integer $invite_limit
 * @property integer $invite_type
 * @property integer $reg_num
 */
class RegisterInviteConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'register_invite_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['courseid'], 'required'],
            [['courseid', 'invite_limit', 'invite_type', 'reg_num'], 'integer'],
            [['invite_code'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'inviteid' => '邀请码ID',
            'courseid' => '活动科目ID',
            'invite_code' => '邀请码',
            'invite_limit' => '邀请现在人数',
            'invite_type' => '邀请人类型：0无性别限制，1男子，2女子',
            'reg_num' => '已成功报名的要求人数，报名成功才占用名额',
        ];
    }
}
