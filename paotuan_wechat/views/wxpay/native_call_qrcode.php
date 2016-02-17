<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
	<title>微信安全支付</title>
</head>
<body>
<a href="<?php echo $product_url;?>"><?php echo $product_url;?></a>
	<div align="center" id="qrcode">
	
	</div>
</body>
	<script src="/js/qrcode.js"></script>
	<script>
		var url = "<?php echo $product_url;?>";
		//参数1表示图像大小，取值范围1-10；参数2表示质量，取值范围'L','M','Q','H'
		var qr = qrcode(10, 'M');
		qr.addData(url);
		qr.make();
		var dom=document.createElement('DIV');
		dom.innerHTML = qr.createImgTag();
		var element=document.getElementById("qrcode");
		element.appendChild(dom);
	</script>
</html>