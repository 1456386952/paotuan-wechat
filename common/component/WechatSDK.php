<?php
namespace common\component;

use common\component\WechatSDK\Wechat;

final class WechatSDK
{
	static public $instance;

	static public $config = array(
            'id' 	=> 'gh_7a167312e413',
            'appid' 	=> 'wxe380ca3504f26643',
            'appsecret' => '7916f82922f82693c88a9dbe50d78ba7',
            'token'	=> 'iRunner20130913'
	);

	static public $extinstance;

	static public $weixinToken;

	static public function getInstance($options=null)
	{
		if( ! self::$instance instanceof Wechat ){
			if( empty($options) ){
				if( !empty(\Yii::$app->params['weixin_config']['wechart']) ){
					$options = \Yii::$app->params['weixin_config']['wechart'];
				}else{
					$options = self::$config;
				}
			}else{
                            $options = array_merge(self::$config, $options);
			}
			self::$instance = new Wechat($options);
			// self::$instance->valid();
		}
		return self::$instance;
	}
}
