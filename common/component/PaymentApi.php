<?php
namespace common\component;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use yii\base\Component;
use yii\base\Exception;
use common\component\payment\AlipayException;
use common\component\payment\WxpayException;
use common\models\OrderMaster;
use common\models\Register;
use common\models\Act;
use common\models\ActCourse;
use common\models\EmailTemplate;
use common\models\UserInfo;
use common\models\ActPost;
use common\models\OrderDetail;
use common\models\Item;
use common\models\UserMaster;

/**
 * 用于处理订单支付
 * 支持处理支付宝，微信支付处理
 *
 * @author wubaoxin
 */
class PaymentApi extends Component
{
    protected static $TypeArr = ['android' => 'mobile','ios'=>'mobile','wpf'=>'mobile','wap'=>'wap','web'=>'web','mobile'=>'mobile'];
        
    /*
     * $partner:alipay[支付宝],wxpay[微信支付]
     * $method:mobile[手机APP移动支付]，wap[手机网页支付],web[网站支付],
     * $app_type:android安卓应用,ios苹果,wpf,wap[手机网页支付],web[网站支付],
     */
    public static function PayOprate($partner,$app_type)
    {
        try
        {
            $Mothed = self::$TypeArr[$app_type];
            if(empty($Mothed)){
                throw new Exception("操作类型错误！");
            }
            \Yii::info('获取支付处理的方法！');
            $Class=ucfirst($partner).ucfirst($Mothed);
            $File = \Yii::getAlias("@common")."/component/payment/".$Class.".php";
            if(!file_exists($File))
            {
                throw new Exception("处理文件不存在！");
            }
            require_once $File;
            $Pay = new $Class();
            $Pay->OprateNotify($app_type);
            echo 'success';
        }
        catch (AlipayException $e)
        {
            $e->getMessage();
            echo 'fail';
        }
        catch (WxpayException $e)
        {
            \Yii::info('支付错误信息微信'.$e->getMessage());
            echo 'fail';
        }
        catch (Exception $e)
        {
            \Yii::info('支付错误信息全部'.$e->getMessage());
            echo 'fail';
        }
    }
    
    public static function ListenOrder($orderid)
    {
        $order = OrderMaster::findOne(['orderid'=>$orderid]);
        $response = ['status'=>1,'message' => '订单信息不存在！'];
//         if($order){
//             $response = ['status'=>0,'result' => [] ];
//             switch ($order->order_status)
//             {
//                 case 0:
//                     $response['result']['order_status'] = 0;//未支付
//                     break;
//                 case 1:
//                     $response['result']['order_status'] = 1;//未支付
//                     $response['result']['trade_no'] =  CustomHelper::CreateOrderID($orderid);//订单号
//                     $response['result']['payment_no'] = $order->payment_no;//第三方支付号
//                     break;
//                 case 2:
//                     $response['result']['order_status'] = 2;//支付已取消或过期
//                     break;
//             }
//         }
        if ($order) 
        {
            $response = ['status'=>0,'result' => ['orderid'=>$orderid,'order_status'=>$order->order_status] ];
        }
        return $response;
    }
    
    /*
     * 创建支付请求:$partner,$mothed,$app_type
     */
    public static function CreatePay($param)
    {
        $Mothed = self::$TypeArr[$param['mothed']];
        if(empty($Mothed)){
            throw new Exception("操作类型错误！");
        }
        
        $Class=ucfirst($param['partner']).ucfirst($Mothed);
        $File = \Yii::getAlias("@common")."/component/payment/".$Class.".php";
        if(!file_exists($File))
        {
            throw new Exception("处理文件不存在！");
        }
        require_once $File;
        $Pay = new $Class();
        return $Pay->CreatePay($param);
        
    }
    
    /**
     * 报名成功后发送邮件和短信
     * @param array $param
     */
    public static function RegisterSuccOprate($param)
    {
        $uid = $param["uid"];
        $order_id = $param["order_id"];
        $register_id = $param["register_id"];
        $type = $param['type'];
        if ($type)
        {
            $OrderMaster = OrderMaster::findOne(['orderid'=>$order_id]);
            switch ($type)
            {
                case 'channel':
                    if ($register_id)
                    {
                        $Register = Register::findOne(["registerid"=>$register_id]);
                    }
                    else
                    {
                        $Register = Register::findOne(["orderid"=>$order_id]);
                    }
                    // 免报名费的状态为0，需要支付的状态为1
                    $status = 0;
                    // 参赛人姓名
                    $entry_name = $Register->passport_name;
//                     // 参赛人性别
//                     $entry_sex = "男";
//                     if ($Register->user_gender == 2)
//                     {
//                         $entry_sex = "女";
//                     }
                    // 参赛活动名称
                    $Act = Act::findOne(["actid"=>$Register->actid]);
                    $act_name = $Act->act_name;
                    // 参赛科目名称
                    $ActCourse = ActCourse::findOne(["courseid"=>$Register->courseid]);
                    $act_course_name = $ActCourse->course_name;
//                     // 服务项目名
//                     $service_name = $act_name.$act_course_name;
                    // 费用
                    $act_fee = "";
                    $UserMaster = UserMaster::findOne(['uid'=>$Register->uid]);
                    // 参赛者邮箱
                    $entry_email = $UserMaster->user_email;
                    // 参赛者电话
                    $entry_cell = $UserMaster->user_cell;
                    if ($Register->payment_status)
                    {
                        $status = 1;
                        $act_fee = $OrderMaster->actual_payment;
                    }
                    
                    if ($entry_email)
                    {
                        // 发送邮件
                        $EmailBody = EmailTemplate::findOne(["email_code"=>"REGISTRATIONSUCCESS"]);
                        $body = $EmailBody->email_body;
                        $body = str_replace("{nick_name}",$entry_name,$body);
                        $body = str_replace("{act_name}",$act_name,$body);
                        $body = str_replace("{course_name}",$act_course_name,$body);
                        $body = str_replace("{registerid}", $register_id, $body);
                        $body = str_replace("{register_fee}",$act_fee,$body);
                        $body = str_replace("{order_time}",$OrderMaster->update_time,$body);
                        $param_email = [];
                        $param_email["email"] = $entry_email;
                        $param_email["body"] = $body;
                        $param_email["title"] = $EmailBody->email_title;
                        $Mail = new MailManage();
                        $Mail->SendRegisterSuccEmail($param_email);
                    }
                    
                    if ($entry_cell)
                    {
                        // 发送短信
                        $msg = $act_name."-".$act_course_name."，成功支付费用：".$act_fee;
                        if(CustomHelper::isCell($entry_cell))
                        {
                            MessageApi::send($entry_cell, $msg);
                        }
                    }
                    break;
                case 'act_post':
                    $UserInfo = UserInfo::findOne(['uid'=>$uid]);
                    // 免报名费的状态为0，需要支付的状态为1
                    $status = 0;
                    // 参赛人姓名
                    $entry_name = $UserInfo->passport_name;
//                     // 参赛人性别
//                     $entry_sex = "男";
//                     if ($UserInfo->user_gender == 2)
//                     {
//                         $entry_sex = "女";
//                     }
                    $ActPost = ActPost::findOne(['orderid'=>$order_id]);
                    // 参赛科目名称
                    $ActCourse = ActCourse::findOne(["courseid"=>$ActPost->courseid]);
                    $act_course_name = $ActCourse->course_name;
                    // 参赛活动名称
                    $Act = Act::findOne(["actid"=>$ActCourse->actid]);
                    $act_name = $Act->act_name;
                    $OrderDetail = OrderDetail::findOne(['orderid'=>$order_id]);
                    $Item = Item::findOne(['itemid'=>$OrderDetail->itemid]);
                    // 费用
                    $act_fee = "";
                    // 参赛者邮箱
                    $entry_email = $UserInfo->user_email;
//                     // 服务内容
//                     $service_name = $ActPost->course_name.$Item->item_name;
                    // 参赛者电话
                    $entry_cell = $UserInfo->user_cell;
                    $status = 1;
                    $act_fee = $OrderMaster->actual_payment;
                    
                    if ($entry_email)
                    {
                        // 发送邮件
                        $EmailBody = EmailTemplate::findOne(["email_code"=>"ACTPOSTSUCCESS"]);
                        $body = $EmailBody->email_body;
                        $body = str_replace("{nick_name}",$entry_name,$body);
                        $body = str_replace("{act_name}",$act_name,$body);
                        $body = str_replace("{course_name}",$act_course_name,$body);
                        $body = str_replace("{runner_no}",$ActPost->runner_no?$ActPost->runner_no:'',$body);
                        $body = str_replace("{post_fee}",$act_fee,$body);
                        $body = str_replace("{order_time}",$OrderMaster->update_time,$body);
                        $param_email = [];
                        $param_email["email"] = $entry_email;
                        $param_email["body"] = $body;
                        $param_email["title"] = $EmailBody->email_title;
                        $Mail = new MailManage();
                        $result = $Mail->SendRegisterSuccEmail($param_email);
                    }
                    
                    if ($entry_cell)
                    {
                        // 发送短信
                        $msg = "您已申请".$act_name."-".$act_course_name."赛包代领服务，成功支付费用：".$act_fee;
                        if(CustomHelper::isCell($entry_cell))
                        {
                            MessageApi::send($entry_cell, $msg);
                        }
                    }
                    break;
            }
        }
    }
}
