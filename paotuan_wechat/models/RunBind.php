<?php

namespace paotuan_wechat\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "run_bind".
 *
 * @property integer $bindid
 * @property string $user_id
 * @property integer $uid
 * @property string $access_token
 * @property string $nick_name
 * @property integer $bind_type
 * @property string $mac_key
 * @property integer $bind_status
 * @property string $update_time
 * @property string $create_time
 */
class RunBind extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'run_bind';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'update_time', 'create_time'], 'required'],
            [['uid','bind_type','bindid','bind_status'], 'integer'],
            [['update_time', 'create_time'], 'safe'],
            [['user_id', 'nick_name'], 'string', 'max' => 64],
            [['access_token'], 'string', 'max' => 1024],
            [['mac_key'], 'string', 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'bindid' => 'Bindid',
            'user_id' => 'User ID',
            'uid' => 'Uid',
            'access_token' => 'Access Token',
            'nick_name' => 'Nick Name',
            'bind_type' => '绑定类型（1：咕咚，2：虎扑，3：益动，4：小米）',
            'mac_key' => 'mac密钥',
            'bind_status' => '绑定状态（1：正常，0：删除）',
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
