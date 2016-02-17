<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_account_log".
 *
 * @property integer $logid
 * @property integer $uid
 * @property integer $inout_type
 * @property string $amount
 * @property string $befor_balance
 * @property string $payment_type
 * @property string $payment_no
 * @property string $remark
 * @property string $ip
 * @property integer $log_status
 * @property string $update_time
 * @property string $create_time
 */
class UserAccountLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_account_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'inout_type', 'amount', 'befor_balance', 'log_status', 'update_time', 'create_time'], 'required'],
            [['uid', 'inout_type', 'log_status'], 'integer'],
            [['amount', 'befor_balance'], 'number'],
            [['update_time', 'create_time'], 'safe'],
            [['payment_type', 'ip'], 'string', 'max' => 20],
            [['payment_no'], 'string', 'max' => 32],
            [['remark'], 'string', 'max' => 640]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'logid' => '流水号',
            'uid' => '用户ID',
            'inout_type' => '收支类型，1收入，2支出',
            'amount' => '发生金额',
            'befor_balance' => '账户余额',
            'payment_type' => '收支渠道',
            'payment_no' => '第三方支付号',
            'remark' => '备注说明',
            'ip' => 'IP地址',
            'log_status' => '流水状态：0已取消，1处理中，2已完成',
            'update_time' => '更新时间',
            'create_time' => '创建时间',
        ];
    }
}
