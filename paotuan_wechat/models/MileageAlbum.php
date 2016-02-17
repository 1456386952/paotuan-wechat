<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "user_maileage_album".
 *
 * @property integer $id
 * @property integer $uid
 * @property string $image_url
 * @property string $desc
 */
class MileageAlbum extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_maileage_album';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'image_url','mileage_id'], 'required'],
            [[ 'mileage_id'], 'integer'],
            [['desc','image_url'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mileage_id' => 'Mileage Id',
            'image_url' => 'Image Url',
            'desc' => 'Desc',
        ];
    }
}
