<?php
namespace common\component\payment;
/*
 * 定义支付接口处理的接口
 */
use yii\base\Object;
use common\models\OrderMaster;
use common\models\Register;
use yii\db\Expression;
use yii\base\Exception;
use common\models\ActWantgo;
use common\component\PaymentApi;
use common\models\OrderDetail;
use common\models\ActPost;
use common\models\ActCourse;
/**
 * Description of PayInteface
 *
 * @author wubaoxin
 */
abstract class PayInteface
{
    protected $OrderID;//订单号
    protected $TradeNo;
    protected $PaymentType;//支付方式
    protected $PaymentNo;//第三方交易号
    protected $PaymentStatus;//交易状态
    protected $TotalFee;//交易金额
    protected $AppType;//app类型
    
    public function __construct() {
        
    }

    abstract protected function PayVerify();
    
    //订单处理
    public function OprateNotify($app_type)
    {
        $this->AppType=$app_type;
        $this->PayVerify();
        $transaction = \Yii::$app->db->beginTransaction();
            //只要订单状态不是等于1，就允许处理
            $sql = "SELECT orderid,order_status,order_type FROM order_master WHERE orderid = {$this->OrderID} AND order_status!=1 FOR UPDATE";
            $OrderModel = \Yii::$app->db->createCommand($sql)->queryOne();
            //$OrderModel = OrderMaster::findOne(['orderid'=>$this->OrderID]);
            if(!$OrderModel)
            {
                $transaction->rollback();
                throw new Exception('订单信息不存在！');
            }
            $uid = "";
            $actid = "";
            $courseid = '';
            //根据订单类型处理报名状态
            switch ($OrderModel['order_type'])
            {
             case 1://通道报名
                 $RegModel = Register::findOne(['orderid'=>$this->OrderID]);
                 if(!$RegModel)
                 {
                     throw new Exception('报名信息不存在！');
                 }
                 $RegModel->payment_status = 2;
                 $RegModel->payment_time = new Expression('NOW()');
                 $RegModel->register_status = 2;
                 $uid=$RegModel->uid;
                 $actid=$RegModel->actid;
                 $courseid = $RegModel->courseid;
                 if(!$RegModel->save())
                 {
                     $transaction->rollback();
                     throw new Exception("更新报名信息状态失败！");
                 }
                 //增加我会去
                 ActWantgo::addWantgo($uid, $actid,$courseid);
                 //发送报名成功的短信和邮件
                 $param = [];
                 $param["uid"] = $uid;
                 $param["order_id"] = $this->OrderID;
                 $param["register_id"] = $RegModel->registerid;
                 $param['type'] = 'channel';
                 PaymentApi::RegisterSuccOprate($param);
                 break;
             case 2:
                 $ActPost = ActPost::findOne(['orderid'=>$this->OrderID]);
                 if(!$ActPost)
                 {
                     throw new Exception('配送信息不存在！');
                 }
                 $ActPost->payment_status = 2;
                 $ActPost->post_status = 2;
                 $uid=$ActPost->uid;
                 $actid='';
                 $courseid = $ActPost->courseid;
                 $ActCourse = ActCourse::findOne(['courseid'=>$courseid]);
                 if ($ActCourse)
                 {
                     $actid = $ActCourse->actid;
                 }
                 if(!$ActPost->save())
                 {
                     $transaction->rollback();
                     throw new Exception("更新配送信息状态失败！");
                 }
                 //增加我会去
                 ActWantgo::addWantgo($uid, $actid, $courseid);
                 //发送赛包成功的短信和邮件
                 $param = [];
                 $param["uid"] = $uid;
                 $param["order_id"] = $this->OrderID;
                 $param["register_id"] = '';
                 $param['type'] = 'act_post';
                 PaymentApi::RegisterSuccOprate($param);
                 break;
            }
            //如果订单已经被取消了，则要重新处理名额、商品数量问题
            if($OrderModel['order_status'] == 2){
                if ($OrderModel['order_type'] == 1)
                {
                    $OrderDetail = OrderDetail::findOrderDetailInfo($this->OrderID);
                    if ($OrderDetail)
                    {
                        foreach ($OrderDetail as $detail)
                        {
                            $Item = $detail->itemInfo;
                            if ($Item)
                            {
                                //更新商品数量
                                $sql4 = "update item set item_buy_sum=item_buy_sum+{$detail->item_num} where itemid={$Item->itemid}";
                                \Yii::$app->db->createCommand($sql4)->execute();
                            }
                        }
                    }
                }
            }
            //更新订单信息
            $update = \Yii::$app->db->createCommand()
                 ->update(
                    'order_master', 
                    [
                        'trade_no' => $this->TradeNo,
                        'payment_type' => $this->PaymentType,
                        'order_status' => $this->PaymentStatus,
                        'payment_no' =>$this->PaymentNo
                    ], 
                    "orderid = {$this->OrderID}"
                )->execute();
            if(!$update)
            {
              $transaction->rollback();
              throw new Exception("更新订单状态信息失败！");
            }
        $transaction->commit();
    }
    //创建支付请求信息
    abstract public function CreatePay($param);
}
