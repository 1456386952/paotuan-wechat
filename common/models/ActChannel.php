<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use paotuan_wechat\models\Club;
use common\models\Act;

/**
 * This is the model class for table "act_channel".
 * 
 * @property integer $channelid
 * @property integer $actid
 * @property integer $clubid
 * @property string $cannel_desc
 * @property string $invite_code
 * @property integer $channel_status
 * @property string $channel_start
 * @property string $channel_end
 * @property string $update_time
 * @property string $create_time
 */
class ActChannel extends \yii\db\ActiveRecord
{
	
	const CHANNEL_STATUS_NORMAL = 0;
	const CHANNEL_STATUS_DOWN = 1;
	const CHANNEL_STATUS_CLOSED = 2;
	const CHANNEL_STATUS_TODO = 3;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_channel';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channelid', 'actid', 'clubid', 'channel_status','limit_range'], 'integer'],
            [['channel_start', 'channel_end', 'update_time', 'create_time'], 'safe'],
            [['cannel_desc'], 'string', 'max' => 255],
            [['invite_code'], 'string', 'max' => 10],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'channelid' => '报名通道id',
            'actid' => '活动ID',
            'clubid' => '发起人ID',
            'cannel_desc' => '通道描述',
            'invite_code' => '邀请码(null表示无需邀请码)',
            'channel_status' => '通道状态：0正常，1关闭',
            'channel_start' => '通道开始时间',
            'channel_end' => '通道截止时间',
            'update_time' => '更新时间',
            'create_time' => '创建时间',
        ];
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->update_time = new Expression('NOW()');
            if ($this->isNewRecord) {
                $this->create_time = new Expression('NOW()');
            }
            return true;
        }
        return false;
    }
    /*获取通道开通方信息*/
    public function getClubInfo()
    {
        return $this->hasOne(Club::className(), ['clubid' => 'clubid']);
    }
    /*获取赛事信息*/
    public function getActInfo()
    {
        return $this->hasOne(Act::className(), ['actid' => 'actid']);
    }
    /*获取报名通道信息*/
    public static function findChannelList($actid, $courseid)
    {
        return static::find()->where('actid='.$actid.' and channelid in (select i.channelid from item i where i.courseid='.$courseid.' and i.item_status=0 and i.item_end>NOW() and i.item_buy_sum<i.item_num_limit)')
                             ->orderBy(['create_time'=>SORT_DESC])
                             ->with('clubInfo')->all();
    }
    /**
     * 获取商铺通道信息
     */
    public static function findClubChannel($clubid,$offset = 0)
    {
        return static::find()->where('clubid=:clubid  and channel_status=0',[':clubid'=>$clubid])
                    ->with('clubInfo','actInfo')
                    ->orderBy(['create_time'=>SORT_DESC])
                    ->offset($offset)
                    ->all();
    }

    /*获取没有关闭的的通道*/
    public static function findClubChannelUnclosed($clubid,$offset = 0)
    {
        return static::find()->where('clubid=:clubid  and channel_status<>2',[':clubid'=>$clubid])
            ->with('clubInfo','actInfo')
            ->orderBy(['create_time'=>SORT_DESC])
            ->offset($offset)
            ->all();
    }
    
    public static function findConfirmClubChannelUnclosed($clubid)
    {
    	return static::find()->where('(clubid=:clubid and channel_status<>2) or  channel_status=3',[':clubid'=>$clubid])
    	->with('clubInfo','actInfo')
    	->orderBy(['create_time'=>SORT_DESC])
    	->all();
    }
    
    public static function findAvailableChannel($channel_id)
    {
    	return static::find()->where("channelid=:channel_id and channel_status = :channel_status")
    						 ->addParams([":channel_id"=>$channel_id,":channel_status"=>self::CHANNEL_STATUS_NORMAL])
    	                     ->with('clubInfo','actInfo')
    	                     ->one();
    }
}
