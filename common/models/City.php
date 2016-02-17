<?php

namespace common\models;

use Yii;
use common\models\Province;

/**
 * This is the model class for table "city".
 *
 * @property integer $cityid
 * @property integer $countryid
 * @property integer $areaid
 * @property integer $provinceid
 * @property string $chn_name
 * @property string $eng_name
 * @property integer $sort
 */
class City extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'city';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['countryid','provinceid'], 'required'],
            [['countryid', 'areaid', 'provinceid', 'sort'], 'integer'],
            [['chn_name', 'eng_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cityid' => '省份ID',
            'countryid' => '国家ID',
            'areaid' => '区域ID',
            'provinceid' => '省份ID',
            'chn_name' => '中文名称',
            'eng_name' => '英文名称',
            'sort' => '城市排序',
        ];
    }
    /*获取城市ID，如果城市不存在，则生成*/
    public static function FindCityId($city,$countryid,$provinceid)
    {
       $Province=  Province::findOne(['provinceid'=>$provinceid]);
       if(in_array($Province->chn_name, ['北京','上海','天津','重庆'])){
           $city = $Province->chn_name;
       }
       $City=self::findOne(['chn_name'=>$city,'countryid'=>$countryid]);
       if(!$City){
           $City=new self();
           $City->countryid=$countryid;
           $City->provinceid=$provinceid;
           $City->chn_name=$city;
           $City->save();
       }
       return $City->cityid;
    }
    
    /**
     * 根据区域编号获取有赛事的城市信息
     * @param int $areaId
     */
    public static function findCitys($areaId)
    {
        return self::findBySql("select DISTINCT c.* from act a,city c where a.cityid=c.cityid and c.areaid=".$areaId)
              ->orderBy(['c.sort'=>SORT_ASC])
              ->all();
    }
}
