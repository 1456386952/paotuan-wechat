<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "ibeacon".
 *
 * @property integer $id
 * @property integer $club_id
 * @property string $device_id
 * @property string $major
 * @property string $minor
 * @property string $uuid
 * @property integer $status
 */
class Ibeacon extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ibeacon';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status',"club_id"], 'integer'],
            [['device_id', 'major', 'minor'], 'string', 'max' => 10],
            [['uuid'], 'string', 'max' => 100]
        ];
    }
    
    public function getLocation(){
    	return $this->hasOne(ActivityLocation::className(), ["id"=>"location_id"]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'club_id' => 'Club ID',
            'device_id' => 'Device ID',
            'major' => 'Major',
            'minor' => 'Minor',
            'uuid' => 'Uuid',
            'status' => 'Status',
        ];
    }
}
