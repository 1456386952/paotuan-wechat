<?php

namespace paotuan_wechat\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "marketing_club".
 *
 * @property integer $id
 * @property integer $club_id
 * @property integer $uid
 * @property integer $marketing_id
 * @property integer $like_sum
 * @property string $create_time
 */
class MarketingClub extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'marketing_club';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['club_id', 'uid', 'marketing_id', 'create_time'], 'required'],
            [['club_id', 'uid', 'marketing_id', 'like_sum'], 'integer'],
            [['create_time'], 'safe']
        ];
    }
    
    
    public static function mileageRank($marketing_id,$uid,$start_time,$end_time){
    	$query = new Query();
    	return $query->select("club_eng,club_name,club.clubid,club_slogan,club_logo,club_status,mc.like_sum,mv1.uid as vote_uid,sum(m.mileage) as mileage,count(m.uid) as u_count,sum(m.mileage)/count(m.uid) as avg")
    	->from(MarketingClub::tableName()." as mc")
    	->innerJoin(Club::tableName()." as club","mc.club_id=club.clubid")
    	->leftJoin("(select club_id from ".MarketingVote::tableName()." group by club_id) mv ","mv.club_id = mc.club_id")
    	->leftJoin("(select club_id,uid from ".MarketingVote::tableName()." where uid = $uid) mv1 ","mv1.club_id = mc.club_id")
    	->leftJoin("(select club_id,uid from club_member group by uid having count(uid)=1".
     			   " union (select club_id,uid from club_member where is_default=1)) cm "," club.clubid=cm.club_id")
    	->leftJoin("(select sum(mileage) as mileage,uid from  mileage where create_date between :start_time and :end_time and mileage_date between :start_time and :end_time and status=0 GROUP BY uid) m","m.uid = cm.uid")
    	->where("mc.marketing_id=:marketing_id and club_type=".Club::CLUB_TYPE_CLUB." and (club_status=".Club::CLUB_STATUS_NORMAL." or club_status=".Club::CLUB_STATUS_PROCESS.")")
    	->groupBy("club.clubid")
    	->addParams([":marketing_id"=>$marketing_id,":start_time"=>$start_time,":end_time"=>$end_time])
    	->orderBy("avg desc")->all();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'club_id' => 'Club ID',
            'uid' => 'Uid',
            'marketing_id' => 'Marketing ID',
            'like_sum' => '被投票的数量',
            'create_time' => 'Create Time',
        ];
    }
    
    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert)) {
    		if ($this->isNewRecord) {
    			$this->create_time =  date("Y-m-d H:i:s");
    		}
    		return true;
    	}
    	return false;
    }
}
