<?php
$redirectUrl = rawurldecode($_GET["redirectUrl"]);
if(stripos($redirectUrl,"?")===false){
	header("Location:http://wechat.runningtogether.net$redirectUrl?code=".$_GET["code"]);
}else{
   header("Location:http://wechat.runningtogether.net$redirectUrl&code=".$_GET["code"]);
}

?>