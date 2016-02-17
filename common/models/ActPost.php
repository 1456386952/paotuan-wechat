<?php

namespace common\models;

use Yii;
use yii\db\Expression;
/**
 * This is the model class for table "act_post".
 *
 * @property integer $postid
 * @property integer $uid
 * @property integer $orderid
 * @property integer $detailid
 * @property string $detail_title
 * @property integer $detail_num
 * @property integer $courseid
 * @property string $course_name
 * @property integer $runner_gender
 * @property string $runner_no
 * @property string $receiver
 * @property string $receiver_date
 * @property string $receiver_addr
 * @property string $receiver_cell
 * @property string $post_remark
 * @property integer $post_status
 * @property integer $payment_status
 * @property string $update_time
 * @property string $create_time
 */
class ActPost extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['postid', 'orderid', 'uid'], 'required'],
            [['postid', 'orderid', 'uid', 'detailid', 'detail_num', 'courseid', 'runner_gender', 'post_status', 'payment_status'], 'integer'],
            [['detail_title', 'course_name', 'receiver_addr'], 'string', 'max' => 120],
            [['runner_no'], 'string', 'max' => 20],
            [['receiver'], 'string', 'max' => 64],
            [['receiver_cell'], 'string', 'max' => 16],
            [['post_remark'], 'string', 'max' => 600],
            [['update_time', 'create_time', 'receiver_date'], 'safe'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'postid' => '递送ID',
            'uid' => '用户ID',
            'orderid' => '订单号',
            'detailid' => '订单细项id',
            'detail_title' => '订单详情项目',
            'detail_num' => '数量',
            'courseid' => '参赛科目id',
            'course_name' => '参赛科目名称',
            'runner_gender' => '选手性别（1男，2女）',
            'runner_no' => '选手比赛号',
            'receiver' => '收货人名称',
            'receiver_date' => '希望收货时间',
            'receiver_addr' => '收货地址',
            'receiver_cell' => '收货人手机',
            'post_remark' => '备注说明',
            'post_status' => '递送状态（0取消 1正常 2待分拣 3快递途中 4已送达 ）',
            'payment_status' => '支付状态	0免费，1未支付，2已支付，3取消支付',
            'update_time' => '更新时间',
            'create_time' => '创建时间',
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
