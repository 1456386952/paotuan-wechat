
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <title>微信安全支付</title>

    <script type="text/javascript">

        //调用微信JS api 支付
        function jsApiCall()
        {
            WeixinJSBridge.invoke(
                'getBrandWCPayRequest',
                <?php echo $signPackage; ?>,
                function(res){
                    WeixinJSBridge.log(res.err_msg);
                    if (res.err_msg == "get_brand_wcpay_request:ok") {
                        location.href = 'http://wechat.paobuqu.com/mobile/#/paysuccess?act_id='+<?php echo $act_id; ?>+'&order_id=' + <?php echo $order_id; ?>;
                    } else {
                        location.href = 'http://wechat.paobuqu.com/mobile/#/payerr';
                    }
                    // if (res.err_msg == "get_brand_wcpay_request:ok") {
                    //     alert("支付成功");
                    // } else {
                    //     alert("支付ss");
                    // }
                    // alert(res.err_msg)
                    
                    //alert(res.err_code+res.err_desc+res.err_msg);
                }
            );
        }

        function callpay()
        {
            if (typeof WeixinJSBridge == "undefined"){
                if( document.addEventListener ){
                    document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                }else if (document.attachEvent){
                    document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                    document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                }
            }else{
                jsApiCall();
            }
        }
        callpay();
    </script>
</head>
<body>
</body>
</html>