<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>跳转中</title>
<script src="/js/store.min.js"></script>
</head>
<body style="text-align: center;">
   <div style="padding-top:100px">跳转中...</div>
   <script type="text/javascript">
        store.set("openid",'<?=$openid?>');
       // location.href ='<?=$redirectUrl?>';
        location.replace('<?=$redirectUrl?>');
   </script>
</body>
</html>