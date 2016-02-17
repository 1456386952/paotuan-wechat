<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "wx_token".
 *
 * @property string $data
 * @property integer $expire_time
 * @property integer $type
 */
class WxToken extends \yii\db\ActiveRecord
{
	
	const TYPE_ACCESS_TOKEN=1;
	const TYPE_JSAPI_TICKET=2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wx_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data'], 'string'],
            [['expire_time', 'type'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'data' => 'Data',
            'expire_time' => 'Expire Time',
            'type' => 'Type',
        ];
    }
}
