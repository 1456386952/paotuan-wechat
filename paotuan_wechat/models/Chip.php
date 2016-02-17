<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "chip".
 *
 * @property integer $chipid
 * @property string $number
 * @property string $mac
 * @property string $maker
 * @property string $clubid
 * @property string $uid
 * @property integer $createtime
 * @property integer $type
 * @property integer $status
 */
class Chip extends \yii\db\ActiveRecord
{
	const TYPE_PER_CHIP=0;
	const STATUS_BIND=0;
	const STATUS_UNBIND=1;
	const STATUS_DISABLED=2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'chip';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clubid', 'uid', 'createtime', 'type', 'status'], 'integer'],
            [['number'], 'string', 'max' => 20],
            [['mac'], 'string', 'max' => 30],
            [['maker'], 'string', 'max' => 50],
        	[['bind_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'chipid' => '芯片流水ID',
            'number' => '芯片编号',
            'mac' => '芯片mac地址',
            'maker' => '芯片制造厂商',
            'clubid' => '跑团ID',
            'uid' => '用户ID',
            'createtime' => '创建时间戳',
            'type' => '芯片类型',
            'status' => '芯片状态(0为绑定，1为解除绑定，2为作废)',
        ];
    }
}
