<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "app_token".
 *
 * @property integer $tokenid
 * @property integer $partner
 * @property string $app_type
 * @property string $access_token
 * @property integer $exprie_time
 * @property string $create_time
 */
class AppToken extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'app_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner'], 'required'],
            [['exprie_time'], 'integer'],
            [['create_time'], 'safe'],
            [['partner','app_type'], 'string', 'max' => 11],
            [['access_token'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tokenid' => 'Tokenid',
            'partner' => '第三方类型',
            'app_type' => 'app类型：andirod，ios,wpf',
            'access_token' => '微信token',
            'exprie_time' => 'token过期时间',
            'create_time' => '创建时间',
        ];
    }
    
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->create_time = new Expression('NOW()');
            }
            return true;
        }
        return false;
    }
}
