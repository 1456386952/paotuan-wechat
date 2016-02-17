<?php   header("Content-type: text/html; charset=utf-8");?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport"
	content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<link rel="stylesheet" href="/css/amazeui.min.css" />
<script type="text/javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" src="/jq_fileupload/jquery.fileupload-all.js	"></script>
<script type="text/javascript" src="/jq_fileupload/load-image.all.min.js"></script>
<script type="text/javascript" src="/jq_fileupload/jquery.fileupload-image.js"></script>
<script type="text/javascript" src="/js/wechat.js"></script>
</head>

<style>
   .text{
    background-color: transparent;
   }
   input{
    width:100%
   }
</style>
<body>
<?php 
// $expiration = time()+30;
// $saveKey ="/test/test_{year}{mon}{day}{hour}{min}{sec}{.suffix}"; 
// $tmp = '{"bucket":"xiaoi","expiration":'.$expiration.',"save-key":"'.$saveKey.'"}';
// $policy = base64_encode($tmp);
// $formAPI="+MJqrva8kj5+PQYIuDLovBCL3Wc=";
// $signature=md5($policy."&".$formAPI);
?>
<form id="form" action="http://v0.api.upyun.com/xiaoi" method="post" enctype="multipart/form-data">
   <input type="text" name="signature" value="<?=$signature ?>">
      <input type="text" name="policy" value="<?=$policy ?>">
      <input type="file" name="file" multiple="multiple" id="test" accept="image/gif,image/png,image/jpg,image/jpeg,image/bmp">
      <input type="button" value="submit" id="upload" >
</form>

<form id="form" action="http://wechat.paobuqu.com/upload/uploadimgforrace" method="post" enctype="multipart/form-data">
   <input type="text" name="race_id" value="1">
      <input type="text" name="chip_mac" value="123">
       <input type="text" name="cp_index" value="1">
      <input type="file" name="file" id="test" accept="image/gif,image/png,image/jpg,image/jpeg,image/bmp">
      <input type="submit" value="submit" >
</form>
<?php echo time();?> 
<script type="text/javascript">
var file = "test";

$("#"+file)
.bind('fileuploadadd', function (e, data) {
	  file_validate_error=false;
 data.context=$("#upload").click(function(){
	 file_validate_error=false;
	  if(localStorage.upload_ok){
		  var oks =JSON.parse(localStorage.upload_ok);
		  if(oks&&oks.length>0&&$.inArray(data.files[0].name,oks)!=-1){
			  return false;
			  }
		}
	 data.process().done(function(){
             data.submit();
		 });
   });
	
})
.bind('fileuploadprocessfail', function (e, data) {
	if(data.files[data.index].error=="File type not allowed"&&!file_validate_error){
    	file_validate_error=true;   
    	alert("只能上传图片(gif,jpg,png)!");
	    }else if(data.files[data.index].error=="Maximum number of files exceeded"&&!file_validate_error){
	    	file_validate_error=true;   
	    }
   });
	
$("#"+file).fileupload({
	  dataType: 'json',
	  url:"upload/uploadimgforrace",//"http://v0.api.upyun.com/xiaoi",
	  sequentialUploads:true,
	  disableImageResize:false,
	  imageCrop: false,
	  replaceFileInput:false,
	  formData:{file_id:file,img_type:file},
	  singleFileUploads:true,
	  forceIframeTransport:false,
	  acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
	  autoUpload:false,
	  change:function(e,data){
			  file_validate_error=false;
			  localStorage.removeItem("upload_ok");
		  },
		progressall: function (e, data) {
			   progress_percent = parseInt(data.loaded / data.total * 100, 10);
			   setProgressPercent(progress_percent);
		    },
	  send:function(e,data){
		  showFileLoading();
		  },
    done:function(e,data){
	        if(data.result.status==1){
	        	 var oks=[];
	        	if(localStorage.upload_ok){
	               oks =JSON.parse(localStorage.upload_ok);
	                }
                if($.inArray(data.files[0].name,oks)==-1){
                	 oks.push(data.files[0].name);
                    }
                localStorage.upload_ok = JSON.stringify(oks);
         	   var oks =JSON.parse(localStorage.upload_ok);
			   $("#upload").val(oks.length+"/"+$("#test").prop("files").length);
		      }
	      },
    fail:function(e,data){
         
    },
	  always:function(e,data){
		  hideLoading();
		}
});
</script>
<?php 

// $oCurl = curl_init();
// curl_setopt($oCurl, CURLOPT_URL,"http://worker.paobuqu.com/?r=main/register/index");
// curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($oCurl, CURLOPT_POST,true);
// $params =array();
// $params["openid"]="oyL64uI7WE6HUm2RJ60ahVuZpbOc";
// $params["nick_name"]="么有";
// $params["act_name"]="么有";
// $params["club_eng"]=11;
// $params["act_time"]="2015-01-01~1015-02-02";
// $params["act_address"]="123";
//  $params["club_name"]="123";
// // openid：string(120) 微信openid

// // nick_name：string(64) 活动发起人昵称

// // act_name：string(64) 活动名称

// // club_eng：int(11) 活动短名

// // act_time：string(64) 活动时间

// // act_address：string(120) 活动地址

// // club_name：string(64) 跑团名称
// $objects[] = [
// 			'action' => 'PaotuanApi',
// 			'params' => [
// 				'url' => "http://www.paobuqu.com/v3/wechat/eventcancel",
// 				'params' =>$params
// 			]
// 		];

// curl_setopt($oCurl, CURLOPT_POSTFIELDS,json_encode(["objects"=>$objects]));
// $sContent = curl_exec($oCurl);
// $aStatus = curl_getinfo($oCurl);
// print_r($aStatus);

// echo  $sContent;
// curl_close($oCurl);

function curl_post_ssl($url, $vars, $second=30,$aHeader=array())
{
	$ch = curl_init();
	//超时时间
	curl_setopt($ch,CURLOPT_TIMEOUT,$second);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
	//这里设置代理，如果有的话
	//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
	//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);

	//以下两种方式需选择一种

	//第一种方法，cert 与 key 分别属于两个.pem文件
	//默认格式为PEM，可以注释
	curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
	curl_setopt($ch,CURLOPT_SSLCERT,dirname(getcwd()).'/config/apiclient_cert.pem');
	//默认格式为PEM，可以注释
	curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
	curl_setopt($ch,CURLOPT_SSLKEY,dirname(getcwd()).'/config/apiclient_key.pem');
	//第二种方式，两个文件合成一个.pem文件
	//curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');

	if( count($aHeader) >= 1 ){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
	}

	curl_setopt($ch,CURLOPT_POST, 1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
	$data = curl_exec($ch);
	if($data){
		curl_close($ch);
		return $data;
	}
	else {
		$error = curl_errno($ch);
		echo "call faild, errorCode:$error\n";
		curl_close($ch);
		return false;
	}
}

function createNoncestr( $length = 32 )
{
	$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
	$str ="";
	for ( $i = 0; $i < $length; $i++ )  {
		$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
	}
	return $str;
}


 function getSign($Obj)
{
	foreach ($Obj as $k => $v)
	{
		$Parameters[$k] = $v;
	}
	//签名步骤一：按字典序排序参数
	ksort($Parameters);
	$String = formatBizQueryParaMap($Parameters, false);
	//echo '【string1】'.$String.'</br>';
	//签名步骤二：在string后加入KEY
	$String = $String."&key=d19a4f7fc95036d7f5855e2c9d998e49";
	//echo "【string2】".$String."</br>";
	//签名步骤三：MD5加密
	$String = md5($String);
	//echo "【string3】 ".$String."</br>";
	//签名步骤四：所有字符转为大写
	$result_ = strtoupper($String);
	//echo "【result】 ".$result_."</br>";
	return $result_;
}

function formatBizQueryParaMap($paraMap, $urlencode)
{
	$buff = "";
	ksort($paraMap);
	foreach ($paraMap as $k => $v)
	{
		if($urlencode)
		{
			$v = urlencode($v);
		}
		//$buff .= strtolower($k) . "=" . $v . "&";
		$buff .= $k . "=" . $v . "&";
	}
	$reqPar;
	if (strlen($buff) > 0)
	{
		$reqPar = substr($buff, 0, strlen($buff)-1);
	}
	return $reqPar;
}

function arrayToXml($arr)
{
	$xml = "<xml>";
	foreach ($arr as $key=>$val)
	{
		if (is_numeric($val))
		{
			$xml=$xml."<".$key.">".$val."</".$key.">";
		}
		else{
			$xml=$xml."<".$key."><![CDATA[".$val."]]></".$key.">";
		}
	}
	$xml.="</xml>";
	return $xml;
}
$nonce_str =createNoncestr();
//print_r($nonce_str."<br>");
// $para["mch_appid"]="wxe380ca3504f26643";
// $para["mchid"] = "1220390101";

// $para["nonce_str"]=$nonce_str;

// $para["partner_trade_no"] =time();
// $para["openid"] ="oyL64uI7WE6HUm2RJ60ahVuZpbOc";
// $para["check_name"] ="NO_CHECK";
// $para["amount"] =1;
// $para["desc"] ="test";
// $para["spbill_create_ip"] ="10.2.3.10";
// $para["sign"] = getSign($para);
//$url="https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers"

// $para["wxappid"]="wxe380ca3504f26643";
// $para["mch_id"] = "1220390101";
// $para["nonce_str"]=$nonce_str;
// $para["mch_billno"] =time();
// $para["re_openid"] ="oyL64uI7WE6HUm2RJ60ahVuZpbOc";
// $para["send_name"] ="小i爱跑";
// $para["total_amount"] =300;
// $para["total_num"] ="3";
// $para["amt_type"]="ALL_RAND";
// $para["wishing"]="满满的爱";
// $para["act_name"]="测试";
// $para["remark"]="抢红包啦";
// $para["sign"] = getSign($para);

// $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack";
// $postData =arrayToXml($para);
// $data = curl_post_ssl($url,$postData,30);
// print_r($data);

?>

</body>
</html>


