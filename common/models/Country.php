<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "country".
 *
 * @property integer $countryid
 * @property string $chn_name
 * @property string $eng_name
 * @property string $country_logo
 * @property integer $sort
 */
class Country extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'country';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort'], 'integer'],
            [['chn_name', 'eng_name'], 'string', 'max' => 64],
            [['country_logo'], 'string', 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'countryid' => '国家ID',
            'chn_name' => '中文名称',
            'eng_name' => '英文名称',
            'country_logo' => '国家logo图',
            'sort' => '国家排序',
        ];
    }
    /*获取国家ID，如果国家不存在，则生成*/
    public static function FindCountryId($country)
    {
       $Country=self::findOne(['chn_name'=>$country]);
       if(!$Country){
           $Country=new self();
           $Country->chn_name=$country;
           $Country->save();
       }
       return $Country->countryid;
    }
    
    /**
     * 获取国外信息
     */
    public static function findForeignCoutrys()
    {
        return self::findBySql("select DISTINCT c.* from act a,country c where a.countryid=c.countryid and c.chn_name != '中国'")
             ->orderBy(['c.sort'=>SORT_ASC])
                     ->all();
    }
}
