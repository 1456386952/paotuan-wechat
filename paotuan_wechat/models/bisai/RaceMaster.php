<?php

namespace paotuan_wechat\models\bisai;

use Yii;

/**
 * This is the model class for table "race_master".
 *
 * @property integer $race_id
 * @property string $short_name
 * @property string $disp_name
 * @property string $official_name
 * @property string $race_time
 * @property integer $race_type
 * @property string $city
 * @property string $place
 * @property string $race_icon
 * @property string $entry_item
 * @property string $entry_start_time
 * @property string $entry_close_time
 * @property integer $entry_nums
 * @property integer $entry_totals
 * @property integer $status
 * @property string $create_time
 * @property integer $weight
 * @property string $access_code
 */
class RaceMaster extends \yii\db\ActiveRecord
{
	
	const  STATUS_INVALID=0;
	const  STATUS_TODO=1;
	const  STATUS_NORMAL=2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'race_master';
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
            [['race_time', 'entry_start_time', 'status', 'weight'], 'required'],
            [['race_time', 'entry_start_time', 'entry_close_time', 'create_time'], 'safe'],
            [['race_type', 'entry_nums', 'entry_totals', 'status', 'weight','display'], 'integer'],
            [['short_name'], 'string', 'max' => 20],
            [['disp_name', 'official_name'], 'string', 'max' => 80],
            [['city'], 'string', 'max' => 12],
            [['place'], 'string', 'max' => 60],
            [['race_icon'], 'string', 'max' => 160],
            [['entry_item'], 'string', 'max' => 100],
            [['access_code'], 'string', 'max' => 10],
            [['short_name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'race_id' => '赛事ID',
            'short_name' => '赛事唯一的英文或数字短名，仅用于URL',
            'disp_name' => '显示名称',
            'official_name' => '官方名称',
            'race_time' => '开赛时间',
            'race_type' => '赛事类型：1定距赛，2定时赛',
            'city' => '赛事举办城市',
            'place' => '赛事举办地点',
            'race_icon' => '赛事标题图片',
            'entry_item' => '可以报名的项目，逗号分隔符',
            'entry_start_time' => '报名开始时间',
            'entry_close_time' => '报名结束时间',
            'entry_nums' => '报名名额',
            'entry_totals' => '已报名名额',
            'status' => '0未通过1待审核2通过审核',
            'create_time' => '创建时间',
            'weight' => '赛事权重，用于展示排名',
            'access_code' => 'Access Code',
        ];
    }
}
