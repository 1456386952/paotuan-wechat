<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use common\models\OrderDetail;
use common\models\UserInfo;
use yii\db\Query;

/**
 * This is the model class for table "order_master".
 *
 * @property integer $orderid
 * @property integer $trade_no
 * @property integer $uid
 * @property string $order_title
 * @property string $amount
 * @property string $actual_payment
 * @property string $ip
 * @property string $payment_start
 * @property string $expire_time
 * @property integer $order_type
 * @property string $order_remark
 * @property integer $order_status
 * @property string $payment_type
 * @property string $payment_no
 * @property string $update_time
 * @property string $create_time
 */
class OrderMaster extends \yii\db\ActiveRecord
{
	
	const TYPE_ONE_REG=5;
	const STATUS_WAIT_PAY=0;
	const STATUS_NORMAL=1;
	const STATUS_CANCEL=2;
	const STATUS_DELETE=3;
	const TYPE_CLUB_ACT=6;
	const TYPE_HOTEL_TRAFFIC=3;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_master';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'amount','payment_start'], 'required'],
            [['uid', 'order_type', 'order_status'], 'integer'],
            [['amount', 'actual_payment'], 'number'],
            [['payment_start', 'expire_time', 'update_time', 'create_time'], 'safe'],
            [['order_title','trade_no', 'ip', 'payment_type'], 'string', 'max' => 120],
            [['order_remark'], 'string', 'max' => 640],
            [['payment_no'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'orderid' => '订单号',
            'trade_no' => '拼接上日期的订单号',
            'uid' => '用户ID',
            'order_title' => '订单标题',
            'amount' => '订单费用',
            'actual_payment' => '实际支付金额',
            'ip' => '订单发起IP',
            'payment_start' => '订单可以支付的时间	预报名不能立即支付,所以需要设定开始支付时间',
            'expire_time' => '订单过期时间',
            'order_type' => '订单类型（1：通道，2：赛包直送）',
            'order_remark' => '备注说明',
            'order_status' => '订单状态	0未支付，1已支付，2取消支付，3删除',
            'payment_type' => '支付渠道',
            'payment_no' => '第三方支付号',
            'update_time' => '更新时间',
            'create_time' => '生成时间',
        ];
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert))
        {
            $this->update_time = new Expression('NOW()');
            if ($this->isNewRecord)
            {
                $this->create_time = new Expression('NOW()');
            }
            return true;
        }
        return false;
    }
    
    /*获取我的订单列表*/
    public static function findOrderInfo($uid)
    {
        return static::find()->where("uid=".$uid." and (order_status=0 or order_status=1)")
                      ->orderBy(['order_type'=>SORT_DESC,'create_time'=>SORT_DESC])
                      ->all();
    }

    public function getUser()
    {
        return $this->hasOne(UserInfo::className(),['uid'=>'uid']);
    }

    public function getDetail()
    {
        return $this->hasMany(OrderDetail::className(),['orderid'=>'orderid']);
    }
    
    public static function needPayOrder($uid,$act_id){
    	$query = new Query();
    	return $query->select("om.*")
    	             ->from(OrderMaster::tableName()." as om")
    	             ->innerJoin("".OrderDetail::tableName()." as od"," od.orderid=om.orderid" )
    	             ->innerJoin("(select item.itemid from ".Item::tableName()." where item.actid=:actid) i","i.itemid=od.itemid")
    	             ->where("om.order_status = :status and om.uid=$uid and expire_time>:ex_time")->addParams([":actid"=>$act_id,":status"=>self::STATUS_WAIT_PAY,":ex_time"=>date("Y-m-d H:i:s")])
    				 ->one();
    }
    
    public static function getOrders($uid,$act_id){
    	$query = new Query();
    	return $query->select("om.*")
    	->from(OrderMaster::tableName()." as om")
    	->innerJoin("".OrderDetail::tableName()." as od"," od.orderid=om.orderid" )
    	->innerJoin("(select item.itemid from ".Item::tableName()." where item.actid=:actid) i","i.itemid=od.itemid")
    	->where("((om.order_status = :need_pay_status and expire_time>:ex_time) or om.order_status=:normal) and om.uid=$uid")->addParams([":actid"=>$act_id,":need_pay_status"=>self::STATUS_WAIT_PAY,":normal"=>self::STATUS_NORMAL,":ex_time"=>date("Y-m-d H:i:s")])
    	->orderBy("om.order_status asc,create_time desc")
    	->distinct(true)
    	->all();
    }
    
    /*
     * 获取通道已支付订单信息
     */
    public static function ChannelOrderInfo($channelid,$offset=0)
    {
        return static::find()
            ->where("orderid in ( select orderid from register where channelid={$channelid} and register_status>0) AND order_status=1")
            ->with('detail','user')
            ->orderBy(['create_time'=>SORT_DESC])
            ->all();
    }

    /*下载报名通道的订单信息*/
    public static function DownLoadOrder($channelid)
    {
        $OrdersObj =  OrderMaster::ChannelOrderInfo($channelid);
        $Str = chr(0xEF).chr(0xBB).chr(0xBF)."订单号,订单标题,订单金额,订单详情,客户名称,联系电话\r\n";
        foreach($OrdersObj as $orderObj){
            $Str .= $orderObj->trade_no.",".$orderObj->order_title.",".$orderObj->amount.",";
            foreach($orderObj->detail as $detail){
                $Str .=$detail->item_title.':'.$detail->item_num."*".$detail->item_price."|";
            }
            $Str .=",".$orderObj->user->passport_name.",".$orderObj->user->user_cell."\r\n";
        }
        $road = Yii::getAlias('@runtime/logs');
        $file = $road."/{$channelid}.csv";
        $fp = fopen($file,"w+"); // 打开文件
        fwrite($fp,$Str);
        fclose($fp);
        return $file;
    }
}
