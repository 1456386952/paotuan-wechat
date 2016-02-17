<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>解绑成功</title>
<style>
.bind_success{
	margin-top:30px;
	width:72%;
	margin-left:auto;
	margin-right:auto;
}
.bind_write{
	margin-top:20px;
	color:#A1A1A1;
	font-size:15px;
	width:80%;
	margin-left:auto;
	margin-right:auto;
}
.bind_back{
	text-decoration:none;
	background-color:#009ADA;
	color:#000000;
	padding-left:25%;
	padding-right:25%;
	padding-top:6px;
	padding-bottom:6px;
	border-radius:6px;
	font-size:18px;
}
</style>
</head>
<body style="text-align: center;">
<div style="margin-top:50px;">
    <?php if ($bind_type == 1):?>
    <img src="/image/bind/codoonbig.png"/>
    <?php elseif ($bind_type == 2) :?>
    <img src="/image/bind/hupubig.png"/>
    <?php elseif ($bind_type == 3) :?>
    <img src="/image/bind/edoonbig.png"/>
    <?php elseif ($bind_type == 4):?>
    <img src="/image/bind/xiaomibig.png"/>
    <?php endif;?>
</div>
<div class="bind_success">
    <div style="float:left;">
        <img src="/image/bind/success.png"/>
    </div>
    <div style="font-size:18px;">
        恭喜您，<?php if ($bind_type == 1):?>咕咚<?php elseif ($bind_type == 2):?>虎扑<?php elseif ($bind_type == 3):?>益动<?php elseif ($bind_type == 4):?>小米<?php endif;?>解绑成功！
    </div>
</div>
<div class="bind_write">
解除绑定后，新增的<?php if ($bind_type == 1):?>咕咚<?php elseif ($bind_type == 2):?>虎扑<?php elseif ($bind_type == 3):?>益动<?php elseif ($bind_type == 4):?>小米<?php endif;?>打卡记录将不会被导入到跑团助手中。
</div>
<div style="margin-top:30px;">
    <a href="/runners/me" class="bind_back" style="color:white;">返回个人中心</a>
</div>
</body>
</html>