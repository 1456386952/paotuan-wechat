<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "area".
 *
 * @property integer $areaid
 * @property integer $countryid
 * @property string $chn_name
 * @property string $eng_name
 * @property integer $sort
 */
class Area extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'area';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['countryid'], 'required'],
            [['countryid', 'sort'], 'integer'],
            [['chn_name', 'eng_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'areaid' => '区域ID',
            'countryid' => '国家ID',
            'chn_name' => '中文名称',
            'eng_name' => '英文名称',
            'sort' => '区域排序',
        ];
    }
    /*获取存在赛事的城市信息*/
    public function getCitys()
    {
        return $this->hasMany(City::className(), ['areaid' => 'areaid'])
            ->orderBy(['sort'=>SORT_DESC]);
    }
    /* 获取存在赛事的地区信息 */
    public static function getAreas()
    {
        return Area::findBySql("select DISTINCT ar.* from act a,area ar where a.areaid=ar.areaid")
              ->orderBy(['ar.sort'=>SORT_ASC])->all();
    }
}
