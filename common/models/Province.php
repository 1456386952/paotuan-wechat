<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "province".
 *
 * @property integer $provinceid
 * @property integer $countryid
 * @property integer $areaid
 * @property string $chn_name
 * @property string $eng_name
 * @property integer $sort
 */
class Province extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'province';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['countryid'], 'required'],
            [['countryid', 'areaid', 'sort'], 'integer'],
            [['chn_name', 'eng_name'], 'string', 'max' => 64]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'provinceid' => '省份ID',
            'countryid' => '国家ID',
            'areaid' => '区域ID',
            'chn_name' => '中文名称',
            'eng_name' => '英文名称',
            'sort' => '省份排序',
        ];
    }
    /*获取省份ID，如果省份不存在，则生成*/
    public static function FindProvinceId($province,$countryid)
    {
       $Province=self::findOne(['chn_name'=>$province,'countryid'=>$countryid]);
       if(!$Province){
           $Province=new self();
           $Province->countryid=$countryid;
           $Province->chn_name=$province;
           $Province->save();
       }
       return $Province->provinceid;
    }
}
