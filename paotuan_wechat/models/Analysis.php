<?php

namespace paotuan_wechat\models;

use Yii;
use common\component\CustomHelper;

/**
 * This is the model class for table "club_analysis".
 *
 * @property integer $id
 * @property integer $club_id
 * @property integer $member_sum
 * @property integer $mileage_count
 * @property double $mileage_sum
 * @property integer $activity_sum
 * @property integer $activity_regs
 * @property integer $activity_checkins
 * @property string $create_date
 */
class Analysis extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'club_analysis';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['club_id'], 'required'],
            [['club_id', 'member_sum', 'mileage_count', 'activity_sum', 'activity_regs', 'activity_checkins'], 'integer'],
            [['mileage_sum'], 'number'],
            [['create_date'], 'safe']
        ];
    }
    
    public static function getRecentClubsData($province="all"){
    	$result =[];
    	 $total = self::findAll(["club_id"=>0]);
    	 if(count($total)>0){
    	 	if($province!="all"){
    	 		$result["data"] =  self::find()->innerJoin(Club::tableName()." as club","club.clubid = club_id")->where(["create_date"=>$total[count($total)-1]->create_date])
    	 		                 ->andWhere("province_id in ($province)")
    	 		                 ->orderBy("member_sum desc")->with("club")->all();
    	 		if(count($total)>1){
    	 			$result["data1"] =  self::find()->innerJoin(Club::tableName()." as club","club.clubid = club_id")->where(["create_date"=>$total[count($total)-2]->create_date])
    	 			->andWhere("province_id in ($province)")
    	 			->orderBy("member_sum desc")->all();
    	 		}
    	 	}else{
    	 		$result["data"] =  self::find()->where(["create_date"=>$total[count($total)-1]->create_date])
    	 		                    ->orderBy("member_sum desc")->with("club")->all();
    	 		if(count($total)>1){
    	 			$result["data1"] =  self::find()->where(["create_date"=>$total[count($total)-2]->create_date])
    	 			->orderBy("member_sum desc")->all();
    	 		}
    	 	}
    	 	
    	 }
    	 return $result;
    }
    
    public function getClub(){
    	return $this->hasOne(Club::className(), ["clubid"=>"club_id"]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'club_id' => 'Club ID',
            'member_sum' => '会员总人数',
            'mileage_count' => '打卡总次数',
            'mileage_sum' => '打卡总里程',
            'activity_sum' => '活动总数',
            'activity_regs' => '活动总报名数',
            'activity_checkins' => '活动总签到数',
            'create_date' => '采样日期',
        ];
    }
}
