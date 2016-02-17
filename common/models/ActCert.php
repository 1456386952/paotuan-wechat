<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "act_cert".
 *
 * @property integer $certid
 * @property integer $uid
 * @property integer $act_name
 * @property integer $actid
 * @property string $race_score
 * @property string $cert_copy_url
 * @property integer $cert_status
 * @property string $update_time
 * @property string $create_time
 */
class ActCert extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_cert';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'cert_status', 'update_time', 'create_time'], 'required'],
            [['uid', 'act_name', 'actid', 'cert_status'], 'integer'],
            [['update_time', 'create_time'], 'safe'],
            [['race_score'], 'string', 'max' => 20],
            [['cert_copy_url'], 'string', 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'certid' => '证书ID',
            'uid' => '用户ID',
            'act_name' => '赛事名称',
            'actid' => '赛事ID',
            'race_score' => '赛事成绩',
            'cert_copy_url' => '证书存储路径',
            'cert_status' => '证书状态',
            'update_time' => '更新时间',
            'create_time' => '创建时间',
        ];
    }
}
