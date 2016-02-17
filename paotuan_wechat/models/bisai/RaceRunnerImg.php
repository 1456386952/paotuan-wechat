<?php

namespace paotuan_wechat\models\bisai;

use Yii;

/**
 * This is the model class for table "race_runner_img".
 *
 * @property integer $id
 * @property integer $race_id
 * @property integer $runner_id
 * @property integer $chip_no
 * @property string $chip_max
 * @property integer $cp_id
 * @property string $img_url
 * @property string $create_time
 */
class RaceRunnerImg extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'result_racer_img';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_bisai');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['race_id', 'runner_id', 'cp_id'], 'required'],
            [['race_id', 'runner_id', 'chip_no', 'cp_id'], 'integer'],
            [['create_time'], 'safe'],
            [['chip_mac'], 'string', 'max' => 30],
            [['img_url'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'race_id' => '比赛id',
            'runner_id' => '选手id',
            'chip_no' => 'Chip No',
            'chip_max' => '芯片mac',
            'cp_id' => 'cpid',
            'img_url' => 'Img Url',
            'create_time' => 'Create Time',
        ];
    }
    
    public function beforeSave($insert){
    	if (parent::beforeSave($insert)) {
    		if($insert){
    			$this->create_time = date("Y-m-d H:i:s");
    		}
    		return true;
    	}
    	return false;
    }
}
