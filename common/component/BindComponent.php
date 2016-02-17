<?php
namespace common\component;
/*
 * 用于处理用户信息的绑定
 * 及验证码的发送
 */
use yii\base\Exception;
use yii\base\Component;
use common\component\MessageApi;
use common\component\CustomHelper;
use common\models\UserMaster;
use common\models\UserBindLog;
use common\component\MailManage;
/**
 * Description of BindComponent
 *
 * @author wubaoxin
 */
class BindComponent extends Component {
    //put your code here
    
    public function SendCode()
    {
        try{
            $bind_info=\Yii::$app->request->post("bind_info");
            $uid=  \Yii::$app->user->id;
            $RandCode=  CustomHelper::RandCode(6);
            if(CustomHelper::isCell($bind_info))
            {
                $bind_type=1;
                $param=['cell'=>$bind_info,'rand_code'=>$RandCode];
                $this->isBindCheck($bind_info);
                $this->SendCellCode($param);
            }
            elseif(CustomHelper::isEmail($bind_info))
            {
                $bind_type=2;
                $param=['email'=>$bind_info,'rand_code'=>$RandCode];
                $this->isBindCheck($bind_info,'user_email','邮箱');
                $this->SendEmailCode($param);
            }else
            {
                throw new Exception('操作类型错误！',1);
            }
            /*写入数据库*/
            $UserBind=new UserBindLog();
            $UserBind->uid=$uid;
            $UserBind->bind_type=$bind_type;
            $UserBind->bind_info=$bind_info;
            $UserBind->bind_code=$RandCode;
            $UserBind->save();
            $response=['status'=>0,'message'=>'验证码已成功发送！'];
        }
        catch (Exception $e)
        {
            $response=['status'=>1,'message'=>$e->getMessage()];
        }
        return $response;
    }
    
    public static function SendCellCode($param=[], $message='您的手机绑定验证码',$addMsg = " ，")
    {
        if(!CustomHelper::isCell($param['cell']))
        {
            throw new Exception('手机号码不正确！');
        }
        $Msg=$message.'：'.$param['rand_code'].$addMsg;
        MessageApi::send($param['cell'], $Msg);
        return TRUE;
    }
    
    protected function SendEmailCode($param=[])
    {
        $Mail=new MailManage();
        $Mail->SendBindEmail($param['uid'],$param['email'],$param['bind_code']);
        return TRUE;
    }
    
    protected function isBindCheck($bind,$type='user_cell',$Msg='手机号')
    {
        if(UserMaster::findOne([$type=>$bind]))
        {
            throw new Exception('此'.$Msg.'已经被绑定了！',1);
        }
        return TRUE;
    }

    public function BindOprate()
    {
        $uid=  \Yii::$app->user->id;
        $bind_info =  \Yii::$app->request->post("bind_info");
        $bind_code = \Yii::$app->request->post("bind_code");
        try{
            if(CustomHelper::isCell($bind_info))
            {
                 $bindType="user_cell";
                 $isBind="is_bind_cell";
                 /*根据手机号获取用户旧信息，更新进入user_info*/
                 $BindInfo=UserBindLog::findOne(['uid'=>$uid,'bind_info'=>$bind_info,'bind_code'=>$bind_code]);
                 if(!$BindInfo)
                 {
                     throw new Exception('验证信息错误，请确认再试！',1);
                 }
                 $BindInfo->bind_status=1;
                 if(!$BindInfo->save())
                 {
                     throw new Exception('验证信息失败！',1);
                 }
                 $bind_status=1;
            }
            elseif(CustomHelper::isEmail($bind_info))
            {
                 $this->isBindCheck($bind_info,'user_email','邮箱');
                 $bindType="user_email";
                 $isBind="is_bind_email";
                 $bindCode = CustomHelper::RandCode(6);
                 $param = [];
                 $param["email"] = $bind_info;
                 $param["uid"] = $uid;
                 $param["bind_code"] = $bindCode;
                 self::SendEmailCode($param);
                 $UserBind=new UserBindLog();
                 $UserBind->uid=$uid;
                 $UserBind->bind_type=2;
                 $UserBind->bind_info=$bind_info;
                 $UserBind->bind_code=$bindCode;
                 if(!$UserBind->save())
                 {
                     throw new Exception('验证信息失败！',1);
                 }
                 $bind_status=0;
            }else{
                throw new Exception('操作类型错误！',1);
            }
            $User =  UserMaster::findOne(['uid'=>$uid]);
            $User->$bindType = $bind_info;
            $User->$isBind = $bind_status;
            if (CustomHelper::isCell($bind_info))
            {
                $User->user_name = $bind_info;
            }
            if(CustomHelper::isEmail($bind_info))
            {
                $User->is_bind_email = 1;
            }
            $User->save();
            //更新个人报名信息中的信息
            $UserInfo = \common\models\UserInfo::findOne(['uid'=>$uid]);
            //$UserUpType =  str_replace('user_', '', $bindType);
            $UserInfo->$bindType = $bind_info;
            $UserInfo->save();
            $response=['status'=>0,'message'=>'绑定成功！'];
        }
        catch (Exception $e)
        {
            $response=['status'=>1,'message'=>$e->getMessage()];
        }
        return $response;
    }
}
