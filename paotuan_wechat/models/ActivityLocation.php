<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "activity_location".
 *
 * @property integer $id
 * @property integer $club_id
 * @property string $name
 * @property string $location
 * @property double $lat
 * @property double $lng
 * @property string $create_time
 */
class ActivityLocation extends \yii\db\ActiveRecord
{
	
	const  STATUS_NORMAL=1;
	const  STATUS_DEL=0;
	const  TYPE_PER=2;
	const  TYPE_CLUB=1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_location';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['club_id','uid','type','status'], 'integer'],
            [['lat', 'lng'], 'number'],
            [['create_time'], 'safe'],
            [['name'], 'string', 'max' => 20],
            [['location'], 'string', 'max' => 200]
        ];
    }
    
    
    public  function getActs(){
    	return $this->hasMany(Activity::className(), ["act_location"=>"id"])->orderby("act_create_time desc");
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'club_id' => 'Club ID',
            'name' => 'Name',
            'location' => 'Location',
            'lat' => 'Lat',
            'lng' => 'Lng',
            'create_time' => 'Create Time',
        ];
    }
    
    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert)) {
    		if ($this->isNewRecord) {
    			$this->create_time = date("Y-m-d H:i:s");
    		}
    		return true;
    	}
    	return false;
    }
}
