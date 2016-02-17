<?php
namespace common\component;
/*
 * 自定义函数类
 */

/**
 * Description of CustomHelper
 *
 * @author wubaoxin
 */
class CustomHelper {
    //put your code here
    
    public static function hex2rgb( $colour ) {
        if ( $colour[0] == '#' ) {
            $colour = substr( $colour, 1 );
        }
        if ( strlen( $colour ) == 6 ) {
            list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
        } elseif ( strlen( $colour ) == 3 ) {
            list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
        } else {
            return false;
        }
        $r = hexdec( $r );
        $g = hexdec( $g );
        $b = hexdec( $b );
        return array( 'red' => $r, 'green' => $g, 'blue' => $b );
    }
    /*是否手机号码*/
    public static function isCell($cell)
    {
        if (strlen ( $cell ) != 11 || ! preg_match ( '/^1[3|4|5|7|8][0-9]\d{4,8}$/', $cell )) {
                return false;
        } else {
                return true;
        }
    }
    /*是否邮箱*/
    public static function isEmail($email)
    {
        if (filter_var ($email, FILTER_VALIDATE_EMAIL )) {
                return true;
        } else {
                return false;
        }
    }
    /*获取随机码*/
    public static function RandCode($length=6)
    {
        $str = '0123456789'; 
        $randString = ''; 
        $len = strlen($str)-1; 
        for($i = 0;$i < $length;$i ++){ 
            $num = mt_rand(0, $len); 
            $randString .= $str[$num];         
        } 
        return $randString ; 
    }
    
    /*检验时间是否给定类型*/
    public static function isDate($str,$format="Y-m-d"){
        $unixTime_1 = strtotime($str);
        if ( !is_numeric($unixTime_1) ) return 0;
        $checkDate = date($format, $unixTime_1);
        $unixTime_2 = strtotime($checkDate);;
        if($unixTime_1 == $unixTime_2){
            return 1;
        }else{
            return 0;
        }
    }
    //生成订单号
    public static function CreateOrderID($orderid)
    {
        $Day = date("Ymd");
        $PadID = str_pad($orderid,6,'0',STR_PAD_LEFT);
        return $Day.$PadID;
    }
    //还原订单号
    public static function RestoreOrderID($tradeID)
    {
        return (int)substr($tradeID,8);
    }
    //生成图片路径
    public static function CreateImageUrl($img,$target=null)
    {
        $param=\Yii::$app->params['upyun']['image_size'][$target];
        if(stripos($img,"/")===0){
        	$img = substr($img, 1);
        }
        if(!empty($param)){
            if(preg_match('#upaiyun.com#', $img, $match) ){   
                return $img.=$param;
            }
            elseif(preg_match('#http#', $img, $match)){
                return $img;
            }
            else
            {
                return 'http://xiaoi.b0.upaiyun.com/'.$img.$param;
            }
        }else{
        	if(preg_match('#upaiyun.com#', $img, $match) ){
        		return $img;
        	}else{
        		return 'http://xiaoi.b0.upaiyun.com/'.$img;
        	}
        }
    }
    //返回接送数据
    public static function RetrunJson($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        \Yii::$app->end();
    }
    // 用户终端IP
    public static function getIPaddress()
    {
        $IPaddress='';
        if (isset($_SERVER)){
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
                $IPaddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $IPaddress = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $IPaddress = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")){
                $IPaddress = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $IPaddress = getenv("HTTP_CLIENT_IP");
            } else {
                $IPaddress = getenv("REMOTE_ADDR");
            }
        }
        return $IPaddress;
    }
    
    /*密码输入是否符合要求*/
    public static function verifyPassword($password)
    {
        if (! preg_match ( '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/', $password )) {
            return false;
        } else {
            return true;
        }
    }
    
    /*随机生成$length位包含字母与数字的密码*/
    public static function randomPassword($length)
    {
        $password = '';
        // 生成字母
        $letter_chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z');
        // 字母长度
        $letter_length = round($length/2);
        $letter_keys = array_rand($letter_chars, $letter_length);
        for($i = 0; $i < $letter_length; $i++)
        {
            // 将 $length 个数组元素连接成字符串
            $password .= $letter_chars[$letter_keys[$i]];
        }
        
        // 生成数字
        $num_chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        // 数字长度
        $num_length = $length - $letter_length;
        $num_keys = array_rand($num_chars, $num_length);
        for($i = 0; $i < $num_length; $i++)
        {
            // 将 $length 个数组元素连接成字符串
            $password .= $num_chars[$num_keys[$i]];
        }
        // 打乱
        $password = str_shuffle($password);
        return $password;
    }
    
    

    public static function Itemtype($item_type)
    {
        switch($item_type)
        {
            case 0:
                $item = '名额';
                break;
            case 51:
                $item = '住宿';
                break;
            case 52:
                $item = '交通';
                break;
            case 99:
                $item = '其它';
                break;
        }
        return $item;
    }

    public static function Flag($var1, $var2, $flag, $flag2 = '') {
        $var2 = (array) $var2;
        if(in_array($var1, $var2)){
            echo $flag;
        }else{
            echo $flag2;
        }
        return;
    }
    
    /*随机生成$length位包含字母与数字的密码*/
    public static function createCoupon($length)
    {
        $coupon_code = '';
        // 生成字母
        $letter_chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y','z');
        // 字母长度
        $letter_length = round($length/2);
        $letter_keys = array_rand($letter_chars, $letter_length);
        for($i = 0; $i < $letter_length; $i++)
        {
        // 将 $length 个数组元素连接成字符串
            $coupon_code .= $letter_chars[$letter_keys[$i]];
        }
    
        // 生成数字
        $num_chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        // 数字长度
        $num_length = $length - $letter_length;
        $num_keys = array_rand($num_chars, $num_length);
        for($i = 0; $i < $num_length; $i++)
        {
        // 将 $length 个数组元素连接成字符串
        $coupon_code .= $num_chars[$num_keys[$i]];
        }
        // 打乱
        $coupon_code = str_shuffle($coupon_code);
        return $coupon_code;
    }
    
    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @return string content
     */
    public static function http_post($url,$param){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        return json_decode($sContent,true);
    }
}
