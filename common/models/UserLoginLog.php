<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_login_log".
 *
 * @property integer $log_id
 * @property string $login_account
 * @property string $login_pwd
 * @property integer $login_status
 * @property integer $uid
 * @property string $login_refer
 * @property string $ip
 * @property string $login_agent
 * @property string $create_time
 */
class UserLoginLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_login_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['login_status', 'uid'], 'integer'],
            [['create_time'], 'required'],
            [['create_time'], 'safe'],
            [['login_account', 'login_pwd', 'login_refer', 'login_agent'], 'string', 'max' => 120],
            [['ip'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'log_id' => '主键',
            'login_account' => '登录用户名',
            'login_pwd' => '登录密码',
            'login_status' => '是否成功',
            'uid' => '登录成功用户UID',
            'login_refer' => '登录来源页面',
            'ip' => '登录客户端IP',
            'login_agent' => '登录客户端设备信息',
            'create_time' => '创建时间',
        ];
    }
}
