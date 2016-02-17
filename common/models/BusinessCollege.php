<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "business_college".
 *
 * @property integer $collegeid
 * @property integer $countryid
 * @property integer $areaid
 * @property integer $provinceid
 * @property integer $cityid
 * @property string $chn_name
 * @property string $eng_name
 * @property integer $sort
 */
class BusinessCollege extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'business_college';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['countryid', 'areaid', 'provinceid', 'cityid'], 'required'],
            [['countryid', 'areaid', 'provinceid', 'cityid', 'sort'], 'integer'],
            [['chn_name', 'eng_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'collegeid' => '商学院id',
            'countryid' => '国家ID',
            'areaid' => '区域ID',
            'provinceid' => '省份ID',
            'cityid' => '省份ID',
            'chn_name' => '中文名称',
            'eng_name' => '英文名称',
            'sort' => '城市排序',
        ];
    }
}
