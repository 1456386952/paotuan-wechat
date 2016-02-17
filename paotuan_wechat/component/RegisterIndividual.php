<?php
namespace paotuan_wechat\component;
/*
 * 个人报名处理类
 * 1:个人报名成功后根据需要更新个人资料，扣除占用名额
 * 2:生成支付订单
 */
use yii\base\Component;
use yii\base\Exception;
use common\models\UserInfo;
use common\models\ActCourse;
use common\models\Register;
use common\models\RegisterConfig;
use common\models\OrderMaster;
use common\models\OrderDetail;
use yii\db\Expression;
use common\component\CustomHelper;
use common\component\PaymentApi;
use common\models\ActWantgo;
use common\models\Item;
use common\models\ActPost;
use common\models\ActChannel;
use running_api\models\Club;
use common\models\ActFollow;
use common\models\Act;
use running_api\models\Coupon;

/**
 * Description of RegisterIndividual
 *
 * @author wubaoxin
 */
class RegisterIndividual extends Component
{
    /**
     * 1.一键报名（a：已经报名返回报名、支付状态 b：未报名返回报名状态）
     * 2.一键配送（a：已经下单返回配送和订单状态 b：未下单返回配送状态）
     */
    public function Filling()
    {
        $uid =  \Yii::$app->user->id;
        $courseid =  \Yii::$app->request->getQueryParam('courseid');
        $type = \Yii::$app->request->getQueryParam('type');
        $Course=  ActCourse::findOne(['courseid'=>$courseid]);
        if(!$Course)
        {
            throw new Exception('此科目不存在！',1);
        }
        $UserInfo = UserInfo::findOne(['uid'=>$uid]);
        if (!$UserInfo)
        {
            throw new Exception('无此用户',1);
        }
        if (!$UserInfo->id_number)
        {
            throw new Exception('个人信息不完整，请到个人中心完善',1);
        }
//         if (!$UserInfo->user_cell)
//         {
//             throw new Exception('请绑定手机号',1);
//         }
//         if (!$UserInfo->user_email)
//         {
//             throw new Exception('请绑定邮箱',1);
//         }
        if ($type)
        {
            switch ($type)
            {
                case 'channel':
                    $id_number = (string)$UserInfo->id_number;
                    $Register = Register::find()->where("actid=".$Course->actInfo->actid." and id_number='".$id_number."' and register_status>0")->one();
                    if($Register)
                    {
                        /*已经报名了*/
                        $response = ['status'=>0,'result'=>['register_status' => 2,'orderid' => $Register->orderid,'payment_status'=>$Register->payment_status]];
                    }
                    else
                    {
                        /*未报名*/
                        $response = ['status'=>0,'result'=>['register_status' => 1, 'orderid' => '', 'payment_status'=>'']];
                    }
                    break;
                case 'act_post':
                    $ActPost = ActPost::find()->where("courseid=".$courseid." and uid=".$uid." and post_status>0")->one();
                    if ($ActPost)
                    {
                        /*已经配送了*/
                        $response = ['status'=>0,'result'=>['post_status' => 2,'orderid' => $ActPost->orderid,'payment_status'=>$ActPost->payment_status]];
                    }
                    else
                    {
                        /*未配送*/
                        $response = ['status'=>0,'result'=>['post_status' => 1,'orderid' => '', 'payment_status'=>'']];
                    }
                    break;
            }
        }
        return $response;
    }
    /*
     * 确认报名,返回支付数据或返回报名成功提示[免费报名]
     * 1处理提交数据,生成报名号，
     * 2免费报名，返回报名成功
     * 3预报名，返回预报名成功，生成预支付订单
     * 4需要支付，生成支付订单，返回支付数据
     * 5更新个人资料
     */
    public function Confirm()
    {
        $uid =  \Yii::$app->user->id;
        $courseid =  \Yii::$app->request->post('courseid');
        //获取报名信息
        $RegisterData = \Yii::$app->request->post('user_info');
        $RegisterData = json_decode($RegisterData);
        //获取服务商品信息
        $item_arr = \Yii::$app->request->post('item_arr');
        $item_arr = json_decode($item_arr);
        $Course=  ActCourse::findOne(['courseid'=>$courseid]);
        if(!$Course)
        {
            throw new Exception('此科目不存在！',1);
        }
        $UserInfo = UserInfo::findOne(['uid'=>$uid]);
        $Register = Register::find()->where("actid=".$Course->actInfo->actid." and id_number='".$RegisterData->id_number."' and register_status>0")->one();
        if($Register)
        {
            /*已经报名了*/
            throw new Exception($Register->registerid,2);
        }
        
        //服务总价格
        $item_total_price = 0;
        $channelid = '';
        // 验证服务商品
        if ($item_arr)
        {
            foreach ($item_arr as $item_data)
            {
                if ($item_data->item_buy_sum)
                {
                    $Item = Item::findOne(['itemid'=>$item_data->itemid]);
                    if (!$channelid)
                    {
                        $channelid = $Item->channelid;
                    }
                    if ($Item->item_num_limit && ($Item->item_num_limit-$Item->item_buy_sum) < $item_data->item_buy_sum)
                    {
                        throw new Exception($Item->item_name.'已满！',1);
                    }
                    else
                    {
                        $item_total_price += $Item->item_price * $item_data->item_buy_sum;;
                    }
                }
            }
        }
        
        $RegisterData->actid=$Course->actid;
        $RegisterData->courseid=$courseid;
        $RegisterData->uid=$uid;
        $RegisterData->register_type=1;
        $RegisterData->register_fee=$item_total_price;
        $RegisterData->payment_status=0;
        $RegisterData->register_status=2;
        $RegisterData->birthday = date("Y-m-d h:i:s",strtotime($RegisterData->birthday));
        
        //获取用户的报名信息
        $UnFull = [];
//         //目前需要被过滤掉的字段
//         $UnWriteParam=[
//             'morning_pulse','waistline','emerge_addr','nationality',
//             'has_medical_report','has_cert','has_id_copy','medical_history',
//             'best_score','allergen','update_time','create_time'
//         ];
        //报名时必填字段
        $NeedParam=[
          'user_cell','passport_name','nationality','address','id_number',
          'emerge_name','emerge_ship','user_email','emerge_cell','tshirt_size',
          'user_gender'
        ];
        //根据报名规则需要填写信息
        foreach ($RegisterData as $key=>$val)
        {
            if (in_array($key, $NeedParam))
            {
                if (empty($val))
                {
                    $UnFull[] = $key;//没有填写的信息字段
                }
            }
        }
        
        
//         //根据报名规则需要填写信息
//         foreach($UserInfo as $key=>$val)
//         {
//             $NeedKey='need_'.$key;//当报名需要的信息为1时，必填
//             if(isset($RegConfig->$NeedKey) && $RegConfig->$NeedKey==1){
//                 if(!in_array($key, $UnWriteParam)){
//                     if(empty($val))
//                     {
//                         $UnFull[] = $key;//没有填写的信息字段
//                     }else{
//                         $RegisterData->$key = $val;
//                     }
//                 }
//             }
//         }
        
        // 个人信息不完整
        if(!empty($UnFull))
        {
            $UnStr = $this->registerParam($UnFull);
            //个人信息不完善的提示
            throw new Exception('请完善'.$UnStr.'信息',1);
        }
        /*处理报名*/
        $RegModel=new Register();
        $RegModel->channelid = $channelid;
        $RegModel->setAttributes((array)$RegisterData);
        if(!$RegModel->save()){
            throw new Exception('未能完成报名操作，请稍候重试！',1);
        }
        
        /*生成支付订单*/
        $OrderTitle = $Course->actInfo->act_name.'-'.$Course->course_name;
        $OrderModel = new OrderMaster();
        $OrderModel->uid = $uid;
        $OrderModel->amount = $item_total_price;
        $OrderModel->actual_payment = $item_total_price;//后期有优惠劵再做修改
        $OrderModel->payment_start = new Expression('NOW()');//起始支付时间
        $OrderModel->expire_time = new Expression('DATE_ADD(NOW(),  INTERVAL 60 MINUTE)');//支付过期时间
        $OrderModel->order_type = 1;//订单类型，1通道报名
        $OrderModel->order_title = $OrderTitle;
        
        if(!$OrderModel->save()){
            throw new Exception("未能正确生成订单！请稍候重试！",1);
        }
        
        /*添加服务商品订单详情*/
        if ($item_arr)
        {
            foreach ($item_arr as $item_data)
            {
                if ($item_data && $item_data->item_buy_sum)
                {
                    $Item = Item::findOne(['itemid'=>$item_data->itemid]);
                    if (!$Item)
                    {
                        throw new Exception('商品信息错误',1);
                    }
                    $OrderDetailModel = new OrderDetail();
                    $OrderDetailModel->itemid = $item_data->itemid;
                    $OrderDetailModel->orderid = $OrderModel->orderid;
                    $OrderDetailModel->item_title = $Item->item_name;
                    $OrderDetailModel->item_num = $item_data->item_buy_sum;
                    $OrderDetailModel->item_price = $Item->item_price * $item_data->item_buy_sum;
                    if(!$OrderDetailModel->save())
                    {
                        throw new Exception("未能生成订单详情！请稍候重试！",1);
                    }
                }
            }
        }
        
        //更新报名表的订单ID,设置支付状态
        $RegModel->register_fee =$item_total_price;
        $RegModel->payment_status = 1;
        $RegModel->register_status = 1;
        $RegModel->orderid = $OrderModel->orderid;
        /*扣除订单名额*/
        if(!$RegModel->save())
        {
            throw new Exception("操作错误！请稍候重试！",1);
        }
        
        /*收费报名*/
        if($item_total_price > 0)
        {
            $response = ['status'=>0,'result'=>['register_status'=>3,'orderid' => $OrderModel->orderid,],];
        }  else {
            //增加我会去
            ActWantgo::addWantgo($uid, $Course->actid, $courseid);
            //发送报名成功的短信和邮件
            $param = [];
            $param["uid"] = $uid;
            $param["order_id"] = "";
            $param["register_id"] = $RegModel->registerid;
            $param['type'] = 'channel';
            PaymentApi::RegisterSuccOprate($param);
            
            /*报名成功*/
            $response = ['status'=>0,'result'=>['register_status'=>4,'orderid' => $OrderModel->orderid,],];
        }
        
        // 修改服务商品购买数量
        if ($item_arr)
        {
            foreach ($item_arr as $item_data)
            {
                $Item = Item::findOne(['itemid'=>$item_data->itemid]);
                $Item->item_buy_sum += $item_data->item_buy_sum;
                if (!$Item->save())
                {
                    throw new Exception('修改商品库存失败',1);
                }
            }
        }
        
        return $response;
    }
    /*
     * 报名通道订单详情
     */
    public function Channelorder()
    {
        $uid =  \Yii::$app->user->id;
        $orderid =  \Yii::$app->request->getQueryParam('orderid');
        $OrderMaster = OrderMaster::findOne(['orderid'=>$orderid]);
        if (!$OrderMaster)
        {
            throw new Exception('此订单不存在！',1);
        }
        $Register =  Register::find()->where("orderid=".$orderid." and register_status=1 AND DATE_ADD(create_time,INTERVAL 1 HOUR)<NOW()")->one();
        if ($Register)
        {
            throw new Exception('订单已过期',1);
        }
        if (!$OrderMaster->order_type)
        {
            throw new Exception('订单信息错误！',1);
        }
        if ($OrderMaster->order_type != 1)
        {
            throw new Exception('订单类型错误！',1);
        }
        $Register =  Register::findOne(['orderid'=>$orderid,'uid'=>$uid]);
        if(!$Register)
        {
            throw new Exception('报名信息不存在！',1);
        }
        //通道创建人名称
        $club_name = '';
        /*科目信息*/ 
        $result_course['courseid'] = '';
        //科目名
        $result_course['course_name'] = '';
        //科目里程数
        $result_course['course_mileage'] = '';
        //名额数 
        $result_course['course_place'] = '';
        $ActCourse = ActCourse::findOne(['courseid'=>$Register->courseid]);
        //赛事ID
        $actid = '';
        if ($ActCourse)
        {
            $actid = $ActCourse->actid;
            $result_course['courseid'] = $ActCourse->courseid;
            $result_course['course_name'] = $ActCourse->course_name;
            $result_course['course_mileage'] = round($ActCourse->course_mileage);
            $result_course['course_place'] = 1;
        }
        //关注信息
        $result_follow = [];
        //订单完成页的分享功能
        $result_share = [];
        //是否有赛包代领服务
        $response['result']['has_post'] = '';
        if ($actid && $OrderMaster->payment_no)
        {
            $Act = Act::findOne(['actid'=>$actid]);
            $is_follow = ActFollow::isFollow($uid, $actid);
            $result_follow['actid'] = $actid;
            $result_follow['is_follow'] = $is_follow;
            $result_follow['follow'] = $Act->follow_sum;
            
            //分享URL
            $result_share['share_url'] = \Yii::$app->params['site_url'].'act/'.$Act->actid;
            //分享图片
            $result_share['share_img'] = $Act->act_logo?CustomHelper::CreateImageUrl($Act->act_logo, 'small80'):'';
            //分享标题
            $result_share['share_title'] = '我已报名'.$Act->act_name;
            //分享简介
            $result_share['share_info'] = $Act->act_intro?$Act->act_intro:'';

            //是否有赛包代领服务
            $response['result']['has_post'] = $Act->has_post;
        }
        $response['result']['result_follow'] = $result_follow;
        $response['result']['result_share'] = $result_share;
        
        /*住宿服务信息*/
        $result_item = array();
        $OrderDetail = OrderDetail::findOrderDetailInfo($orderid);
        if ($OrderDetail)
        {
            foreach ($OrderDetail as $detail)
            {
                $Item = $detail->itemInfo;
                $Club = Club::findOne(['clubid'=>$Item->clubid]);
                if ($Club)
                {
                    $club_name = $Club->club_name;
                }
                //过滤名额商品信息
                if ($Item->item_type)
                {
                    $item_data['itemid'] = $Item->itemid;
                    $item_data['item_name'] = $Item->item_name;
                    $item_data['item_num'] = $detail->item_num;
                    $item_data['item_type'] = $Item->item_type;
                    array_push($result_item, $item_data);
                }
            }
        }
        $response['result']['result_course'] = $result_course;
        $response['result']['result_item'] = $result_item;
        
        /*支付信息*/
        $payment_data = [];
        if ($OrderMaster->payment_no)
        {
            //支付方式
            $payment_data['payment_type'] = $OrderMaster->payment_type;
            //订单总额
            $payment_data['total_order'] = $OrderMaster->amount;
            //订单完成时间
            $payment_data['completion_time'] = $OrderMaster->update_time;
        }
        $response['result']['payment_data'] = $payment_data;
        
        //是否已支付
        if ($OrderMaster->payment_no)
        {
            $response['result']['payment_status'] = 1;
        }
        else
        {
            $response['result']['payment_status'] = $OrderMaster->order_status;
        }
        
        //报名号
        $response['result']['registerid'] = $Register->registerid;
        //报名人姓名
        $response['result']['passport_name'] = $Register->passport_name;
        //实际支付总额
        $response['result']['actual_payment'] = $OrderMaster->actual_payment;
        //通道创建方名称
        $response['result']['club_name'] = $club_name;
        //订单号
        $response['result']['orderid'] = $orderid;
        $response['status'] = 0;
        return $response;
    }
    /*
     * 赛包带领订单详情
     */
    public function Postorder()
    {
        $uid =  \Yii::$app->user->id;
        $orderid =  \Yii::$app->request->getQueryParam('orderid');
        $OrderMaster = OrderMaster::findOne(['orderid'=>$orderid]);
        if (!$OrderMaster)
        {
            throw new Exception('此订单不存在！',1);
        }
        $ActPost = ActPost::find()->where("orderid=".$orderid." and post_status=1 AND DATE_ADD(create_time,INTERVAL 1 HOUR)<NOW()")->one();
        if ($ActPost)
        {
            throw new Exception('订单已过期',1);
        }
        if (!$OrderMaster->order_type)
        {
            throw new Exception('订单信息错误！',1);
        }
        if ($OrderMaster->order_type != 2)
        {
            throw new Exception('订单信息错误！',1);
        }
        $ActPost = ActPost::findOne(['orderid'=>$orderid,'uid'=>$uid]);
        if (!$ActPost)
        {
            throw new Exception('无赛包直送信息！',1);
        }
        /*科目信息*/
        $result_course['courseid'] = '';
        //科目名
        $result_course['course_name'] = '';
        //科目里程数
        $result_course['course_mileage'] = '';
        //名额数
        $result_course['course_place'] = '';
        $ActCourse = ActCourse::findOne(['courseid'=>$ActPost->courseid]);
        //赛事ID
        $actid = '';
        if ($ActCourse)
        {
            $actid = $ActCourse->actid;
            $result_course['courseid'] = $ActCourse->courseid;
            $result_course['course_name'] = $ActCourse->course_name;
            $result_course['course_mileage'] = round($ActCourse->course_mileage);
            $result_course['register_fee'] = $ActCourse->register_fee;
        }
        
        //关注信息
        $result_follow = [];
        //订单完成页的分享功能
        $result_share = [];
        //是否赛包代领
        $response['result']['has_post'] = '';
        if ($actid && $OrderMaster->payment_no)
        {
            $Act = Act::findOne(['actid'=>$actid]);
            $is_follow = ActFollow::isFollow($uid, $actid);
            $result_follow['actid'] = $actid;
            $result_follow['is_follow'] = $is_follow;
            $result_follow['follow'] = $Act->follow_sum;
            
            //分享URL
            $result_share['share_url'] = \Yii::$app->params['site_url'].'act/'.$Act->actid;
            //分享图片
            $result_share['share_img'] = $Act->act_logo?CustomHelper::CreateImageUrl($Act->act_logo, 'small80'):'';
            //分享标题
            $result_share['share_title'] = '我已报名'.$Act->act_name;
            //分享简介
            $result_share['share_info'] = $Act->act_intro?$Act->act_intro:'';
            
            //是否赛包代领
            $response['result']['has_post'] = $Act->has_post;
        }
        $response['result']['result_follow'] = $result_follow;
        $response['result']['result_share'] = $result_share;
        
        //点赞信息
        $result_point = [];
        if ($actid && $OrderMaster->payment_no)
        {
            $is_point = ActFollow::isFollow($uid, $actid ,1);
            $result_point['is_point'] = $is_point;
            $result_point['point'] = ActFollow::getPointNum($actid);
        }
        $response['result']['result_point'] = $result_point;

        /*收货信息*/
        $result_post['receiver_addr'] = $ActPost->receiver_addr;
        $result_post['receiver'] = $ActPost->receiver;
        $result_post['receiver_cell'] = $ActPost->receiver_cell;
        $result_post['receiver_date'] = $ActPost->receiver_date?$ActPost->receiver_date:'';
        $result_post['runner_no'] = $ActPost->runner_no;

        $response['result']['result_course'] = $result_course;
        $response['result']['result_post'] = $result_post;

        //支付方式
        $payment_data['payment_type'] = $OrderMaster->payment_type;
        //订单总额
        $payment_data['total_order'] = $OrderMaster->amount;
        //订单完成时间
        $payment_data['completion_time'] = $OrderMaster->update_time;
        $response['result']['payment_data'] = $payment_data;

        //是否已支付
        if ($OrderMaster->payment_no)
        {
            $response['result']['payment_status'] = 1;
        }
        else
        {
            $response['result']['payment_status'] = $OrderMaster->order_status;
        }
        //实际支付总额
        $response['result']['actual_payment'] = $OrderMaster->actual_payment;
        //优惠码使用信息
        $Coupon = Coupon::findOne(['orderid'=>$orderid]);
        $response['result']['coupon_amount'] = 0;
        if ($Coupon)
        {
            $response['result']['coupon_amount'] = $Coupon->amount;
        }
        //订单号
        $response['result']['orderid'] = $orderid;
        //服务受理单号
        $response['result']['postid'] = $ActPost->postid;
        $response['status'] = 0;
        return $response;
    }
    //发起支付，生成支付信息
    public function Payment()
    {
        $orderid =  \Yii::$app->request->post('orderid');
        $param['partner'] =  \Yii::$app->request->post('partner');
        $param['mothed'] = \Yii::$app->request->post('mothed');//mobile
        try{
//             // 根据$RegisterID获取报名信息
//             $register = Register::findOne(["registerid"=>$RegisterID]);
//             if (!$register)
//             {
//                 return ['status'=>1,'message'=>'订单错误'];
//             }
            // 根据订单号获取订单信息
            $order_master = OrderMaster::findOne(["orderid"=>$orderid]);
            if (!$order_master)
            {
                return ['status'=>1,'message'=>'订单错误'];
            }
            // 订单号
            $param['out_trade_no'] = CustomHelper::CreateOrderID($orderid);
            // 商品描述
            $param['order_title'] = $order_master->order_title;
            // 订单总金额
            $param['total_fee'] = $order_master->actual_payment;
            // 交易起始时间
            $param['payment_start'] = strtotime($order_master->payment_start);
            // 交易结束时间
            $param['expire_time'] = strtotime($order_master->expire_time);
            //判断订单的状态
            if($param['expire_time']-900 > time() && $order_master->order_status == 0){
                // 附件信息
                $param['attach'] = $orderid;
                // 支付完成通知回调接口
                $param['notify_url'] = \Yii::$app->params['site_url']."api/payment/notify/{$param['partner']}/{$param['mothed']}";
                // 用户终端IP
                $param['spbill_create_ip'] = CustomHelper::getIPaddress();

                $outparams = PaymentApi::CreatePay($param);
                $response =['status' => 0 ,'result'=>['payment_status'=>0,'outparams'=>$outparams]];
            }  else {
                $response =['status' => 0 ,'result'=>['payment_status'=>1],'message'=>'订单已过期或已取消'];
            }
        }  
        catch (Exception $e)
        {
            $response =['status' => 1 ,'message'=>$e->getMessage()];
        }
        return $response;
    }
    /*
     * 取消报名信息[在事务中处理]:
     * 1更新报名信息表中状态
     * 2处理订单支付状态
     */
    public function Cancel()
    {
        $uid =  \Yii::$app->user->id;
        $orderid =  \Yii::$app->request->getQueryParam('orderid');
        $OrderMaster = OrderMaster::findOne(['orderid'=>$orderid]);
        if (!$OrderMaster)
        {
            throw new Exception('无此订单！',1);
        }
        if (!$OrderMaster->order_type)
        {
            throw new Exception('订单信息错误！',1);
        }
        switch ($OrderMaster->order_type)
        {
            case 1:
                /*通道报名订单取消*/
                $Register =  Register::findOne(['orderid'=>$orderid,'uid'=>$uid]);
                if(!$Register)
                {
                    throw new Exception('报名信息不存在！',1);
                }
                if (!$Register->register_status)
                {
                    throw new Exception('报名已取消！',1);
                }
                if ($Register->register_status == 2)
                {
                    throw new Exception('报名已支付！',1);
                }
                if ($Register->payment_status == 2)
                {
                    throw new Exception('报名已支付！',1);
                }
                // 判断订单有没有过期
                if(strtotime($OrderMaster->expire_time)-900 > time() && $OrderMaster->order_status == 0)
                {
                    $Register->register_status = 0;
                    $Register->payment_status = 3;
                    if(!$Register->save())
                    {
                        throw new Exception('取消报名订单失败！',1);
                    }
                    $OrderMaster->order_status = 2;
                    if (!$OrderMaster->save())
                    {
                        throw new Exception('取消订单失败！',1);
                    }
                    $OrderDetail = OrderDetail::findAll(['orderid'=>$Register->orderid,'item_status'=>0]);
                    if ($OrderDetail)
                    {
                        foreach ($OrderDetail as $detail)
                        {
                            $Item = Item::findOne(['itemid'=>$detail->itemid,'item_status'=>0]);
                            if ($Item)
                            {
                                $Item->item_buy_sum -= $detail->item_num;
                                if (!$Item->save())
                                {
                                    throw new Exception('取消订单操作失败！',1);
                                }
                            }
                        }
                    }
                    $response = ['status' =>0,'message' => '报名取消成功！'];
                }
                else
                {
//                     $response =['status' => 0 ,'result'=>['payment_status'=>1],'message'=>'订单已过期或已取消'];
                    throw new Exception('订单已过期或已取消',1);
                }
                break;
            case 2:
                /*赛包直送订单取消*/
                $ActPost = ActPost::findOne(['orderid'=>$orderid,'uid'=>$uid]);
                if (!$ActPost)
                {
                    throw new Exception('赛包直送信息不存在！',1);
                }
                if (!$ActPost->post_status)
                {
                    throw new Exception('赛包直送订单已取消！',1);
                }
                if ($ActPost->payment_status == 2)
                {
                    throw new Exception('订单已支付',1);
                }
                if ($ActPost->payment_status == 3)
                {
                    throw new Exception('订单已取消',1);
                }
                // 判断订单有没有过期
                if(strtotime($OrderMaster->expire_time)-900 > time() && $OrderMaster->order_status == 0)
                {
                    $ActPost->post_status = 0;
                    $ActPost->payment_status = 3;
                    if (!$ActPost->save())
                    {
                        throw new Exception('取消赛包直送订单失败！',1);
                    }
                    $OrderMaster->order_status = 2;
                    if (!$OrderMaster->save())
                    {
                        throw new Exception('取消订单失败！',1);
                    }
                    $response = ['status' =>0,'message' => '赛包直送取消成功！'];
                }
                else 
                {
//                     $response =['status' => 0 ,'result'=>['payment_status'=>1],'message'=>'订单已过期或已取消'];
                    throw new Exception('订单已过期或已取消',1);
                }                
                break;
        }
        return $response;
    }
    
    //验证报名参数
    protected function registerParam($UnFull)
    {
        $UnStr = '';
        if ($UnFull)
        {
            foreach($UnFull as $val)
            {
                switch ($val)
                {
                    case 'user_cell':
                        $UnStr .= ',手机号';
                        break;
                    case 'passport_name':
                        $UnStr .= ',真实姓名';
                        break;
                    case 'user_gender':
                        $UnStr .= ',性别';
                        break;
                    case 'nationality':
                        $UnStr .= ',国籍';
                        break;
                    case 'address':
                        $UnStr .= ',地址';
                        break;
                    case 'id_number':
                        $UnStr .= ',身份证号码';
                        break;
                    case 'emerge_name':
                        $UnStr .= ',紧急联系人';
                        break;
                    case 'emerge_ship':
                        $UnStr .= ',紧急联系人关系';
                        break;
                    case 'emerge_cell':
                        $UnStr .= ',紧急联系人号码';
                        break;
                    case 'user_email':
                        $UnStr .= ',邮箱';
                        break;
                    case 'tshirt_size':
                        $UnStr .= ',寸衣尺码';
                        break;
                }
            }
        }
        //去掉第一个&字符
        $UnStr = substr($UnStr,1,strlen($UnStr)-1);
        return $UnStr;
    }
}
