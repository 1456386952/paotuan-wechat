<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "user_bind_log".
 *
 * @property integer $bind_id
 * @property integer $uid
 * @property integer $bind_type
 * @property string $bind_info
 * @property string $bind_code
 * @property integer $bind_status
 * @property string $create_time
 * @property string $expiry_time
 */
class UserBindLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_bind_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'bind_type'], 'required'],
            [['uid', 'bind_type', 'bind_status'], 'integer'],
            [['create_time', 'expiry_time'], 'safe'],
            [['bind_info'], 'string', 'max' => 120],
            [['bind_code'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'bind_id' => '绑定ID',
            'uid' => '用户UID',
            'bind_type' => '绑定类型：1手机，2邮箱',
            'bind_info' => '要绑定的内容',
            'bind_code' => '验证码',
            'bind_status' => '1绑定手机，2绑定邮箱，3手机注册，4导入历史数据手机验证',
            'create_time' => '验证码创建时间',
            'expiry_time' => '验证码失效时间',
        ];
    }
    
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($this->isNewRecord)
                $this->expiry_time=date("Y-m-d H:i:s",strtotime("5 minute"));
                $this->create_time=date("Y-m-d H:i:s");
            return true;
        }
        else
            return false;
    }
}
