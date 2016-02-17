<?php 
use paotuan_wechat\component\WxUtil;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
	<link rel="stylesheet" href="/css/amazeui.min.css" />
</head>
<body>

<div class="view">
<div class="panel">
<?php 
echo str_ireplace("@", "&", "@1@2@3");
?>
<form action="/wxpay?<?=time()?>#1234" method="post">
<input type="text" name="orderid" value="78">
<input type="text" name="openid" value="oyL64uI7WE6HUm2RJ60ahVuZpbOc">
<input type="text" name="paytype" value="club_member_fee">
<input type="text" name="notify_url" value="http://wechat.runningtogether.net/wxpay/notify">
<input type="submit" value="submit">

</form>

<form action="http://wechat.paobuqu.com/wechat/getticket">
  <input type="submit" value="get">
</form>

<form action="/wxpay?<?=time()?>" method="post">
<input type="text" name="orderid" value="485">
<input type="text" name="openid" value="oyL64uI7WE6HUm2RJ60ahVuZpbOc">
<input type="text" name="notify_url" value="http://wechat.runningtogether.net/wxpay/notify">
<input type="submit" value="submit">

</form>

<form action="/wxpay/query">
  <input type="text" name="transaction_id" id="transaction_id">
  <input type="submit" value="queryorder">
</form>


<form action="/wxpay/query">
         测试文件上传
         <img id="test_pre">
  <input id="test" type="file" name="file[]" multiple="multiple"/>
</form>

<form action="http://www.paobuqu.com/v3/wechat/wxmember" method="post">
   <input type="text"  name="openid" value='oyL64uI7WE6HUm2RJ60ahVuZpbOc'>
   <input type="text"  name="id" value='1781'>
    <input type="text"  name="club_name" value='小i爱跑'>
    <input type="text"  name="club_eng" value='szrunners'>
    <input type="text"  name="nick_name" value='测试'>
    <input type="submit" value='submit'>
</form>
<form action="http://www.paobuqu.com/v3/wechat/smsinfo" method="post">
   <input type="text"  name="cell" value='13482295614'>
    <input type="text"  name="club_name" value='club_name'>
    <input type="text"  name="club_eng" value='club_eng'>
    <input type="submit" value='发短信'>
</form>


</div>
</div>
<script type="text/javascript" src="/js/jquery.min.js"></script>
<script src="/jq_fileupload/jquery.ui.widget.js"></script>
<script src="/jq_fileupload/load-image.all.min.js"></script>
<script src="/jq_fileupload/canvas-to-blob.min.js"></script>
<script src="/jq_fileupload/jquery.iframe-transport.js"></script>
<script src="/jq_fileupload/jquery.fileupload.js"></script>
<script src="/jq_fileupload/jquery.fileupload-process.js"></script>
<script src="/jq_fileupload/jquery.fileupload-image.js"></script>
<script src="/jq_fileupload/jquery.fileupload-validate.js"></script>
<script src="/js/amazeui.min.js"></script>
<script type="text/javascript">
function fileupload(file){
	$("#"+file).fileupload({
		  dataType: 'json',
		  url:"/upload/uploadimg",
		  sequentialUploads:true,
		  disableImageResize:false,
		  imageCrop: false,
		  formData:{file_id:file,img_type:file},
		  singleFileUploads:false,
		  acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
		  processfail:function(e,data){
			    if(data.files[data.index].error=="File type not allowed"){
                        alert("只能上传图片(gif,jpg,png)!");
				    }
			  },
		  send:function(e,data){
			  $.afui.blockUI(0.01);
			    $.afui.showMask();
			  },
	      done:function(e,data){
		      alert(data.files[0].name);
		        if(data.result.status==1){
			        $.each(data.result.images);
			        if(data.result.images.length>=1){
			        	$("#"+file+"_pre").attr("src","http://xiaoi.b0.upaiyun.com"+data.result.images[0]);
				      }
			      }
		      },
	      fail:function(e,data){
                 alert("fail");
		      },
		  always:function(e,data){
			  $.afui.unblockUI(0.01);
			 $.afui.hideMask();
			}
	  });
}

$.each($("input[type='file']"),function(i,n){
	fileupload(n.id);
});
</script>
<?php 
//$params=["openid"=>"oyL64uI7WE6HUm2RJ60ahVuZpbOc","id"=>1781,"club_name"=>"小I",club_eng=>"szrunners","nick_name"=>"test"];
//WxUtil::sendTemplateClubMember($params);
// $oCurl = curl_init();
// curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
// curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
// curl_setopt($oCurl, CURLOPT_URL,"https://api.weixin.qq.com/shakearound/user/getshakeinfo?access_token=JJVPoq4s74cxos8yVTW4Erz52cn3_KMeVbU1zmOgNr9i31vj2rfz88yXvhUlJ4lKSWWQvNoFdNFx7HN2akdjUfgu22OuEy8tZWmPs6LfVGY");
// curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($oCurl, CURLOPT_POST,true);
// $params =new stdClass(); 
// $params->ticket = "fcaac84bde1f5e723f62a472bed41d09";
// curl_setopt($oCurl, CURLOPT_POSTFIELDS,json_encode($params));
// curl_setopt($oCurl, CURLOPT_HTTPHEADER, array(
// 		'Content-Type: application/json',
// 		'Content-Length: ' . strlen(json_encode($params)))
// );
// $sContent = curl_exec($oCurl);
// $aStatus = curl_getinfo($oCurl);
// print_r($aStatus);
// echo curl_error($oCurl);
// echo  $sContent;
// curl_close($oCurl);

?>

</body>
</html>


