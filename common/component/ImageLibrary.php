<?php
namespace common\component;

use yii;
use yii\helpers\FileHelper;
use yii\base\Exception;
use common\component\UpYun;
use common\component\CustomHelper;

final class ImageLibrary
{
    const NAME_DIR_LOGO = 'face';//头像
    const NAME_DIR_CERT = 'cert';//证书
    const NAME_DIR_REPORT = 'report';//体检报告
    const NAME_DIR_COPY = 'idcopy';//证件复印件

    static public $defaultImages = array(
        self::NAME_DIR_LOGO => array(
            'source'=> "default.png"
        ),
        self::NAME_DIR_HEAD => array(
            'source'=> "default.png",
        ),
    );

    static public $handle;

    static public function setUpload($uploaded, $namespace)
    {
        if( ! empty($uploaded) ){
            try{
                    if( is_string($uploaded) && is_file($uploaded) ){
                        $filename = $uploaded;
                        $size = filesize($filename);
                        $type = FileHelper::getMimeType($filename);
                        if( preg_match('#image\/(.+)#', $type, $match) ){
                            $extname = $match[1];
                        }else{
                            throw new Exception("无法获取图片类型，请确保它是jpg,png,gif类型");
                        }
                    }else{
                        $size = $uploaded->getSize();
                        $extname = $uploaded->getExtensionName();
                        $type = $uploaded->getType();
                        $filename = $uploaded->getTempName();
                        if( !preg_match('#image\/.+#', $type) ){
                            throw new Exception("上传的文件必须是图片类型");
                        }
                    }

                    if( $size > 1024*1024 ){
                        throw new Exception("图片大小不能超过1MB");
                    }
                    
                    $hashname  = md5_file($filename).'.'.$extname;
                    $filename = "{$hashname[0]}/{$hashname[1]}/".substr($hashname, 2);
                    $uploaddir = DIR_UPLOADS.$namespace;
                    $realfile = "{$uploaddir}/{$filename}";
                    $dirname  = dirname($realfile);
                    
                    if( !is_dir($dirname) ){
                        if( !@mkdir($dirname, 0777, true) ){
                            throw new Exception("系统错误：上传文件目录无法写");
                        }
                    }

                    if( is_string($uploaded) && is_file($uploaded) ){
                        rename($uploaded, $realfile);
                    }else{
                        $uploaded->saveAs($realfile);
                    }
                    
                    return $filename;
                    
                }catch(Exception $e){
                    throw new Exception("保存上传图片时出错：". $e->getMessage());
                }
            }
	}

	static public function setHandle($realfile=null)
	{
		$ext2functions = array(
                    'jpg'  => 'imagecreatefromjpeg',
                    'jpeg' => 'imagecreatefromjpeg',
                    'png'  => 'imagecreatefrompng',
                    'gif'  => 'imagecreatefromgif'
                );

                $fileext = FileHelper::getExtension($realfile);
                if (!isset($ext2functions[$fileext])){
                        throw new Exception("不支持所选图片格式:{$fileext}");
                }

                self::$handle = call_user_func($ext2functions[$fileext], $realfile);
        }
        /*仅压缩处理图片*/
        static public function resize($file, $method)
        {
            $filename = "{$method}/{$file}";
            $realfile = DIR_UPLOADS. $filename;

            self::setHandle($realfile);
            $width = $height = 1024;
            $handle = self::crop($width, $height);
            $newfile = ImageLibrary::addThumbFlag($realfile);
            self::saveAs($handle, $newfile);
            $files = ImageLibrary::addThumbFlag($file);
            return $files;
	}

	/**
        * 在保持图像长宽比的情况下将图像裁减到指定大小
        *
        * crop() 在缩放图像时，可以保持图像的长宽比，从而保证图像不会拉高或压扁。
        *
        * crop() 默认情况下会按照 $width 和 $height 参数计算出最大缩放比例，
        * 保持裁减后的图像能够最大程度的充满图片。
        *
        * 例如源图的大小是 800 x 600，而指定的 $width 和 $height 是 200 和 100。
        * 那么源图会被首先缩小为 200 x 150 尺寸，然后裁减掉多余的 50 像素高度。
        *
        * 用法：
        * @code php
        * $image->crop($width, $height);
        * @endcode
        *
        * 如果希望最终生成图片始终包含完整图像内容，那么应该指定 $options 参数。
        * 该参数可用值有：
        *
        * -   fullimage: 是否保持完整图像
        * -   pos: 缩放时的对齐方式
        * -   bgcolor: 缩放时多余部分的背景色
        * -   enlarge: 是否允许放大
        * -   reduce: 是否允许缩小
        *
        * 其中 $options['pos'] 参数的可用值有：
        *
        * -   left: 左对齐
        * -   right: 右对齐
        * -   center: 中心对齐
        * -   top: 顶部对齐
        * -   bottom: 底部对齐
        * -   top-left, left-top: 左上角对齐
        * -   top-right, right-top: 右上角对齐
        * -   bottom-left, left-bottom: 左下角对齐
        * -   bottom-right, right-bottom: 右下角对齐
        *
        * 如果指定了无效的 $pos 参数，则等同于指定 center。
        *
        * $options 中的每一个选项都可以单独指定，例如在允许裁减的情况下将图像放到新图片的右下角。
        *
        * @code php
        * $image->crop($width, $height, array('pos' => 'right-bottom'));
        * @endcode
        *
        * @param int $width 新的宽度
        * @param int $height 新的高度
        * @param array $options 裁减选项
        *
        * @return Helper_ImageGD 返回 Helper_ImageGD 对象本身，实现连贯接口
        */
    static function crop($width, $height, $options = array())
    {
        if (is_null(self::$handle)) return false;

        $default_options = array(
            'fullimage' => false,
            'pos'       => 'center',
            'bgcolor'   => false,
            'enlarge'   => false,
            'reduce'    => true,
        );
        $options = array_merge($default_options, $options);

        // 创建目标图像
        $dest = imagecreatetruecolor($width, $height);

        if( $options['bgcolor'] ){
            list ($r, $g, $b) = CustomHelper::hex2rgb($options['bgcolor']);
            $bgcolor = imagecolorallocate($dest, $r, $g, $b);
        }else{
            $bgcolor = imagecolorallocatealpha($dest, 255, 255, 255, 0);
        }
        imagefilledrectangle($dest, 0, 0, $width, $height, $bgcolor);
        imagecolordeallocate($dest, $bgcolor);

        // 根据源图计算长宽比
        $full_w = imagesx(self::$handle);
        $full_h = imagesy(self::$handle);
        $ratio_w = doubleval($width) / doubleval($full_w);
        $ratio_h = doubleval($height) / doubleval($full_h);

        if ($options['fullimage'])
        {
            // 如果要保持完整图像，则选择最小的比率
            $ratio = $ratio_w < $ratio_h ? $ratio_w : $ratio_h;
        }
        else
        {
            // 否则选择最大的比率
            $ratio = $ratio_w > $ratio_h ? $ratio_w : $ratio_h;
        }

        if (!$options['enlarge'] && $ratio > 1) $ratio = 1;
        if (!$options['reduce'] && $ratio < 1) $ratio = 1;

        // 计算目标区域的宽高、位置
        $dst_w = $full_w * $ratio;
        $dst_h = $full_h * $ratio;

        // 根据 pos 属性来决定如何定位
        switch (strtolower($options['pos']))
        {
            case 'left':
                $dst_x = 0;
                $dst_y = ($height - $dst_h) / 2;
                break;
            case 'right':
                $dst_x = $width - $dst_w;
                $dst_y = ($height - $dst_h) / 2;
                break;
            case 'top':
                $dst_x = ($width - $dst_w) / 2;
                $dst_y = 0;
                break;
            case 'bottom':
                $dst_x = ($width - $dst_w) / 2;
                $dst_y = $height - $dst_h;
                break;
            case 'top-left':
            case 'left-top':
                $dst_x = $dst_y = 0;
                break;
            case 'top-right':
            case 'right-top':
                $dst_x = $width - $dst_w;
                $dst_y = 0;
                break;
            case 'bottom-left':
            case 'left-bottom':
                $dst_x = 0;
                $dst_y = $height - $dst_h;
                break;
            case 'bottom-right':
            case 'right-bottom':
                $dst_x = $width - $dst_w;
                $dst_y = $height - $dst_h;
                break;
            case 'center':
            default:
                $dst_x = ($width - $dst_w) / 2;
                $dst_y = ($height - $dst_h) / 2;
        }

        imagecopyresampled($dest, self::$handle, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $full_w, $full_h);

        return $dest;
    }


    static public function saveAs($handle=null, $file=null)
    {
        if( is_null($handle) ){
            $handle = self::$handle;
        }

        $fileext = FileHelper::getExtension($file);
        $savefuncs = array(
            'jpg'  => 'imagejpeg',
            'jpeg' => 'imagejpeg',
            'png'  => 'imagepng',
            'gif'  => 'imagegif',
        );
        $savefuncs[$fileext]($handle, $file);
        imagedestroy($handle);
    }

    static public function getURL($file, $method, $size=null, $host=URL_IMAGE)
    {
        $path = $method;
        if( empty($file) ){
            if( isset(self::$defaultImages[$method][$size]) ){
                $file = self::$defaultImages[$method][$size];
            }else{
                $file = self::$defaultImages[$method]['source'];
            }
        }
        if( strpos($file, "http://")===false ){
            if( strpos($file, "/")!==1 ){
                $host = URL_UPLOADS;
            }
            $url = "{$host}{$path}/{$file}";
            if( strpos($url, "http://")===false ){
                $url = \Yii::$app->params['baseURL']. $url;
            }
        }else{
            $url = $file;
            if( !is_null($size) ){
                $url = self::addThumbFlag($url, $size);
            }
        }
        
        return $url;
    }


    public static function addThumbFlag($file, $flag=null)
    {
        $infos = pathinfo($file);
        if( empty($infos['extension']) ){
            return false;
        }
        $flag  = empty($flag) ? '' : "-{$flag}";
        $file  = "{$infos['filename']}{$flag}.{$infos['extension']}";
        if( $infos['dirname'] !== '.' ){
            $file = $infos['dirname'].'/'.$file;
        }
        return $file;
    }
    /*上传*/
    public static function upyun($file, $method)
    {
        $upyunInfo=  \Yii::$app->params['upyun'];
        $upyun = new UpYun($upyunInfo['bucketname'], $upyunInfo['username'], $upyunInfo['password']);
        $files = !is_array($file) ? array($file) : $file;

        $success = 0;
        foreach($files as $file){
            $uploadfile  = DIR_UPLOADS . "{$method}/{$file}";
            $info = $upyun->writeFile("/{$method}/{$file}", fopen($uploadfile, 'r+'), true);
            if( !empty($info) ){
                $success++;
            }
        }
        return count($files)===$success;
    }
    /*完整处理图片上传至又拍云*/
    public static function Upfile($method,$type='')
    {
        //获取上传的图片编码数据流
        $logoStream = \Yii::$app->request->post($method);
        try{
            if( !empty($logoStream) ){
                if ($type && $type=='ios')
                {
                    $logoStream = str_replace(' ', '', $logoStream);
                    $logoStream = str_ireplace("<", "", $logoStream);
                    $logoStream = str_ireplace(">", "", $logoStream);
                    $logoStream = pack("H*",$logoStream);
                    //设置图片的临时文件名
                    $temp = tempnam(sys_get_temp_dir(), 'running_');
                    //存储图片临时文件
                    file_put_contents($temp, $logoStream);
                }
                else
                {
                    //对图片编码进行解码
                    $logoStream = mb_convert_encoding($logoStream, "UTF-8", "BASE64");
                    if( empty($logoStream) ){
                        throw new Exception("图片解码失败");
                    }
                    //设置图片的临时文件名
                    $temp = tempnam(sys_get_temp_dir(), 'running_');
                    //存储图片临时文件
                    file_put_contents($temp, $logoStream);
                }
                //处理图片上传
                $filename = ImageLibrary::setUpload($temp, $method);
                //重新设置图片大小,目前无法处理
                //$files 	  = ImageLibrary::resize($filename, $method);
                //上传图片至又拍云
                $success  = ImageLibrary::upyun($filename, $method);
                if( ! $success ){
                        throw new Exception("无法上传到云端");
                }
                //获取图片的存储路径
                return ImageLibrary::getURL($filename, $method);
            }
        }catch(Exception $e){
                //yii::log($logoStream, 'info', 'debug');
                throw new Exception("暂时无法处理您上传的图片，请稍后再手动编辑一下图片：". $e->getMessage());
        }
        return FALSE;
    }
    /*删除图片*/
    public static function delImage($ImageFile)
    {
        $upyunInfo=  \Yii::$app->params['upyun'];
        $upyun = new UpYun($upyunInfo['bucketname'], $upyunInfo['username'], $upyunInfo['password']);
        $files = !is_array($ImageFile) ? array($ImageFile) : $ImageFile;
        foreach($files as $file){
            $upyun->deleteFile($ImageFile);
        }
    }
}
