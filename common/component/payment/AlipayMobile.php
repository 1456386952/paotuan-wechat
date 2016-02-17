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
use common\component\payment\AlipayException;

/**
 * 用于处理支付宝APP支付处理方式
 *
 * @author wubaoxin
 */
class AlipayMobile extends PayInteface
{
    protected $Mothed;
    protected $PartnerId;
    protected $PartnerKey;
    
    protected function PayVerify()
    {
//         require \Yii::getAlias("@vendor")."/payment/alipay/mobile/alipay.config.php";
//         require \Yii::getAlias("@vendor")."/payment/alipay/mobile/lib/alipay_notify.class.php";
        
//         $alipayNotify = new \AlipayNotify($alipay_config);
        
//         $alipay_public_key = \Yii::getAlias("@vendor")."/payment/alipay/mobile/key/alipay_public_key.pem";
//         if(!$alipayNotify->verifyNotify($alipay_public_key))
//         {
//             \Yii::info('验证签名失败.');
//             throw new AlipayException('fail');
//         }
//         \Yii::info('验证签名成功...');
        
        $out_trade_no = \Yii::$app->request->post("out_trade_no");
        if ($out_trade_no)
        {
            $this->TradeNo = \Yii::$app->request->post("out_trade_no");
            /*OrderID由年月日+ordermaster的orderid[需要补前导0]组合而成，如201412241*/
            $this->OrderID = CustomHelper::RestoreOrderID($this->TradeNo);
            \Yii::info('订单号：'.$this->OrderID);
            //第三方交易号
            $this->PaymentNo = \Yii::$app->request->post("trade_no");
            //第三方交易方式
            $this->PaymentType = '支付宝无线支付'.$this->AppType;
            $trade_status =  \Yii::$app->request->post("trade_status");
            if($trade_status=='TRADE_FINISHED' || $trade_status=='TRADE_SUCCESS')
            {
                $this->PaymentStatus=1;
                $this->TotalFee = \Yii::$app->request->post("total_fee");
            }else{
                throw new AlipayException('支付状态错误！');
            }
        }
    }
    
    protected function setParam()
    {
        $this->PartnerId = \Yii::$app->params['ali_config']['alipay']['partnerid'];
        $this->PartnerKey = \Yii::$app->params['ali_config']['alipay']['partnerkey'];
    }
    
    //创建支付请求信息
    public function CreatePay($param) {
        require \Yii::getAlias("@vendor")."/payment/alipay/mobile/lib/alipay_rsa.function.php";
        require \Yii::getAlias("@vendor")."/payment/alipay/mobile/lib/alipay_core.function.php";

        $this->Mothed = $param['mothed'];
        $this->setParam();
        $signParams['partner'] = $this->PartnerId;
        $signParams['seller_id'] = 'hello@runningtogether.net';//卖家支付宝账号
        $signParams['out_trade_no'] = $param['out_trade_no'];//商户订单号
        $signParams['subject'] = '马拉松报名支付';//订单名称
        $signParams['body'] = $param['order_title'];//订单描述
        $signParams['total_fee'] = $param['total_fee'];//商品总金额
        $signParams['payment_type'] = '1';//支付类型
        $signParams['notify_url'] = $param['notify_url'];//通知地址
        $signParams['service'] = 'mobile.securitypay.pay';
        $signParams['_input_charset'] = strtolower('utf-8');
        // 设置未付款交易的超时时间
        // 默认30分钟，一旦超时，该笔交易就会自动被关闭。
        // 取值范围：1m～15d。
        // m-分钟，h-小时，d-天，1c-当天（无论交易何时创建，都在0点关闭）。
        // 该参数数值不接受小数点，如1.5h，可转换为90m。
        $signParams['it_b_pay'] = '45m';
        $private_key_path = \Yii::getAlias("@vendor")."/payment/alipay/mobile/key/rsa_private_key_pkcs8.pem";//支付宝密钥路径
//         \Yii::info('----------------------密钥：'.openssl_pkey_get_private(file_get_contents($private_key_path)));
//         return openssl_pkey_get_private(file_get_contents($private_key_path));
        
        //对数组排序
//         $signParams = argSort($signParams);
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $signParams = createLinkstring($signParams);
        
//         //生成支付签名
//         $sign = utf8_encode(rsaSign($signParams, $private_key_path));
        
//         //签名方式
//         $signType = 'RSA';
        
//         $outparams['payInfo']=$signParams."&sign='".$sign."'&sign_type='".$signType."'";
//         \Yii::info('++++++++++++++++++++签名参数'.$signParams."&sign='".$sign."'&sign_type='".$signType."'");
        $outparams['payInfo']=$signParams;
        //返回支付信息
        return $outparams;
    }
}
