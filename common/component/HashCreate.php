<?php
namespace common\component;

final class HashCreate
{
	const TOKEN_DEFAULT_LENGTH = 64;

	const codeset = "23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";

    public static function validateHash($hash)
    {
        $token      = \Yii::$app()->request->get('token');
        $timestamp  = \Yii::$app()->request->get('timestamp');
        $secret     = \Yii::$app()->params['secret'];
        if( empty($timestamp) ){
            throw new Exception("缺少APP本地时间戳");
        }
        if( empty($token) ){
            throw new Exception("缺少APP token");
        }
        $trueHash   = md5("{$secret}-{$timestamp}-{$token}");
        return $hash === $trueHash;
    }

    public static function create($len=self::TOKEN_DEFAULT_LENGTH){
        $strs = str_split(self::codeset);
        $code = date("Ymd");

        for($i=0; $i<$len; $i++){
            $code .= $strs[array_rand($strs)];
        }

        return $code ;
    }

    public  static function encode($n){
        $base = strlen(self::codeset);
        $converted = '';

        while ($n > 0) {
            $converted = substr(self::codeset, bcmod($n,$base), 1) . $converted;
            $n = self::bcFloor(bcdiv($n, $base));
        }

        return $converted ;
    }

    public static function decode($code){
        $base = strlen(self::codeset);
        $c = '0';
        for ($i = strlen($code); $i; $i--) {
            $c = bcadd($c,bcmul(strpos(self::codeset, substr($code, (-1 * ( $i - strlen($code) )),1))
                    ,bcpow($base,$i-1)));
        }

        return bcmul($c, 1, 0);
    }

    static private function bcFloor($x)
    {
        return bcmul($x, '1', 0);
    }

    static private function bcCeil($x)
    {
        $floor = bcFloor($x);
        return bcadd($floor, ceil(bcsub($x, $floor)));
    }

    static private function bcRound($x)
    {
        $floor = bcFloor($x);
        return bcadd($floor, round(bcsub($x, $floor)));
    }
}