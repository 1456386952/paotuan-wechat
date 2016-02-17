<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "wx_log".
 *
 * @property integer $id
 * @property integer $uid
 * @property string $nick_name
 * @property string $type
 * @property integer $result
 * @property string $result_detail
 * @property string $send_time
 * @property string $create_time
 * @property string $update_time
 */
class WXLog extends \yii\db\ActiveRecord
{
	const  RESULT_FAIL=1;
	const  RESULT_SUCCESS=0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wx_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'required'],
            [['uid', 'result'], 'integer'],
            [['result_detail'], 'string'],
            [['send_time', 'create_time', 'update_time'], 'safe'],
            [['nick_name', 'type'], 'string', 'max' => 255]
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
            'nick_name' => '昵称',
            'type' => '类型',
            'result' => '结果（0：成功，1：失败）',
            'result_detail' => '执行详情',
            'send_time' => '发送日期',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
