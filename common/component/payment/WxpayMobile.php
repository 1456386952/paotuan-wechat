<?php
//namespace common\component\payment;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use common\component\payment\PayInteface;
use common\component\CustomHelper;
use yii\helpers\ArrayHelper;
use yii\base\Exception;
use common\component\payment\WxpayException;
use common\models\AppToken;

require \Yii::getAlias("@vendor")."/payment/wxpay/mobile/RequestHandler.class.php";
require \Yii::getAlias("@vendor")."/payment/wxpay/mobile/ResponseHandler.class.php";
require \Yii::getAlias("@vendor")."/payment/wxpay/mobile/client/ClientResponseHandler.class.php";
require \Yii::getAlias("@vendor")."/payment/wxpay/mobile/client/TenpayHttpClient.class.php";
/**
 * Description of WxpayMobile
 *
 * @author wubaoxin
 */
class WxpayMobile extends PayInteface 
{
    protected $Mothed;
    protected $AppID;
    protected $PartnerId;
    protected $AppSecret;
    protected $PartnerKey;
    protected $AppKey;
    protected $PaySignKey;

    protected function PayVerify() 
    {
        $this->setParam();
        /* 创建支付应答对象 */
        $resHandler = new \ResponseHandler();
        $resHandler->setKey($this->PartnerKey);
        //初始化页面提交过来的参数
        //$resHandler->Init();
        \Yii::info('微信手机支付回调处理开始！');
        //判断签名
        if(!$resHandler->isTenpaySign() && $resHandler->getParameter("trade_state") == "0" && $resHandler->getParameter("trade_mode") == "1")
        {
                $this->TradeNo = str_replace('-','', $resHandler->getParameter("out_trade_no")) ;
                //商户交易单号
                $this->OrderID = CustomHelper::RestoreOrderID($this->TradeNo);
                //财付通订单号
                $this->PaymentNo= $resHandler->getParameter("transaction_id");
                //商品金额,以分为单位,转换成元
                $this->TotalFee = $resHandler->getParameter("total_fee")/100;
                //第三方交易方式
                $this->PaymentType = '微信支付'.$this->AppType;
                $this->PaymentStatus = 1;
            \Yii::info('支付微信返回参数'.$resHandler->getDebugInfo());
        } else {
            \Yii::info('支付错误信息微信'.$resHandler->getDebugInfo());
            throw new WxpayException('验证签名失败');
        }
        \Yii::info('微信手机支付回调处理结束！');
    }
    
    protected function setParam()
    {
        if(!empty($this->Mothed)){
            $this->AppID = \Yii::$app->params['weixin_config'][$this->Mothed]['appid'];
            $this->AppSecret = \Yii::$app->params['weixin_config'][$this->Mothed]['appsecret'];
        }
        \Yii::info('----参数获取：'.json_encode(\Yii::$app->params['weixin_config']));
        $this->PartnerId = \Yii::$app->params['weixin_config']['tenpay']['partnerid'];
        $this->PartnerKey = \Yii::$app->params['weixin_config']['tenpay']['partnerkey'];
        $this->PaySignKey = \Yii::$app->params['weixin_config']['tenpay']['paysignkey'];
    }

    //创建支付请求信息
    public function CreatePay($param) 
    {
        $this->Mothed = $param['mothed'];
        $this->setParam();
        //获取token值
        $reqHandler = new \RequestHandler();
        $reqHandler->init($this->AppID, $this->AppSecret, $this->PartnerKey, $this->PaySignKey);
        //获取存储的Token
        $TokenInfo = AppToken::find()
                ->where("partner='wxpay' AND app_type='".$param['mothed']."' AND exprie_time > UNIX_TIMESTAMP()")
                ->orderBy(['create_time'=>SORT_DESC])->one();
        if(!empty($TokenInfo)){
            $Token = $reqHandler->GetToken($TokenInfo->access_token);
            \Yii::info('数据库提取的'.$Token);
        }else{
            //重新获取Token
            $Token = $reqHandler->GetToken();
            \Yii::info('微信获取的'.$Token);
            //保存获取的Token
            $TokenInfo = new AppToken();
            $TokenInfo->partner = 'wxpay';
            $TokenInfo->app_type = $param['mothed'];
            $TokenInfo->access_token = $Token;
            $TokenInfo->exprie_time = time()+7200;
            $TokenInfo->save();
        }
        if ( $Token !='' ){
                //=========================
                //生成预支付单
                //=========================
                //设置packet支付参数
                $packageParams =array();		

                $packageParams['bank_type']		= 'WX';	            //支付类型
                $packageParams['body']			= $param['order_title'];					//商品描述
                $packageParams['fee_type']		= '1';		    //银行币种
                $packageParams['input_charset']	= 'UTF-8';		    //字符集
                $packageParams['notify_url']	= $param['notify_url'];	    //通知地址
                $packageParams['out_trade_no']	= $param['out_trade_no'];   //商户订单号
                $packageParams['partner']		= $this->PartnerId;	    //设置商户号
                $packageParams['total_fee']		= $param['total_fee']*100;//商品总金额,以分为单位
                $packageParams['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];  //支付机器IP
                $packageParams['time_start'] = date('YmdHis',$param['payment_start']);//支付开始时间
                $packageParams['time_expire'] = date('YmdHis',$param['expire_time']-900);//支付结束时间，扣除15分钟
                $packageParams['attach'] = $param['attach'];
                
                //获取package包
                $package= $reqHandler->genPackage($packageParams);
                $time_stamp = time();
                $nonce_str = md5(rand());
                //设置支付参数
                $signParams =array();
                $signParams['appid']	=$this->AppID;
                $signParams['appkey']	=$this->PaySignKey;
                $signParams['noncestr']	=$nonce_str;
                $signParams['package']	=$package;
                $signParams['timestamp']=$time_stamp;
                $signParams['traceid']	= $param['attach'];
                //生成支付签名
                $sign = $reqHandler->createSHA1Sign($signParams);
                //增加非参与签名的额外参数
                $signParams['sign_method']	='sha1';
                $signParams['app_signature']	=$sign;
                //剔除appkey
                unset($signParams['appkey']);
                \Yii::info('-------微信支付参数：'.json_encode($signParams));
                //获取prepayid
                $prepayid=$reqHandler->sendPrepay($signParams);
                \Yii::info('+++++++预支付prepayid：'.$prepayid);
                
                if ($prepayid != null) {
                        $pack	= 'Sign=WXPay';
                        //输出参数列表
                        $prePayParams =array();
                        $prePayParams['appid']		=$this->AppID;
                        $prePayParams['appkey']		=$this->PaySignKey;
                        $prePayParams['noncestr']	=$nonce_str;
                        $prePayParams['package']	=$pack;
                        $prePayParams['partnerid']	=$this->PartnerId;
                        $prePayParams['prepayid']	=$prepayid;
                        $prePayParams['timestamp']	=$time_stamp;
                        //生成签名
                        $sign=$reqHandler->createSHA1Sign($prePayParams);

                        $outparams['retcode']=0;
                        $outparams['retmsg']='ok';
                        $outparams['appid']=$this->AppID;
                        $outparams['noncestr']=$nonce_str;
                        $outparams['package_value']=$pack;
                        $outparams['prepayid']=$prepayid;
                        $outparams['timestamp']=$time_stamp;
                        $outparams['sign']=$sign;
                        //返回支付信息
                        return $outparams;

                }else{
                        throw new Exception('错误：获取prepayId失败');
                }
        }else{
                throw new Exception('错误：获取不到Token');
        }
    }
}
