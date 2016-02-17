<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "item".
 *
 * @property integer $itemid
 * @property integer $channelid
 * @property integer $actid
 * @property integer $courseid
 * @property integer $clubid
 * @property string $item_name
 * @property integer $item_type
 * @property integer $item_num_limit
 * @property integer $item_buy_sum
 * @property string $item_price
 * @property string $item_desc
 * @property string $item_pic_url
 * @property integer $set_per_order_num
 * @property integer $item_status
 * @property string $item_end
 * @property string $update_time
 * @property string $create_time
 */
class Item extends \yii\db\ActiveRecord
{
	
	const STATUS_NORMAL=0;
	const STATUS_END=1;
	const TYPE_REGISTER=0;
	const TYPE_HOTEL=51;
	const TYPE_TRAFFIC=52;
	const TYPE_OTHER=99;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channelid', 'actid', 'courseid', 'clubid', 'item_type', 'item_num_limit', 'item_buy_sum', 'set_per_order_num', 'item_status'], 'integer'],
            [['item_price'], 'number'],
            [['item_end', 'update_time', 'create_time'], 'safe'],
            [['item_name', 'item_pic_url'], 'string', 'max' => 120],
            [['item_desc'], 'string', 'max' => 320]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'itemid' => 'Itemid',
            'channelid' => 'Channelid',
            'actid' => 'Actid',
            'courseid' => 'Courseid',
            'clubid' => 'Clubid',
            'item_name' => 'Item Name',
            'item_type' => 'Item Type',
            'item_num_limit' => 'Item Num Limit',
            'item_buy_sum' => 'Item Buy Sum',
            'item_price' => 'Item Price',
            'item_desc' => 'Item Desc',
            'item_pic_url' => 'Item Pic Url',
            'set_per_order_num' => 'Set Per Order Num',
            'item_status' => 'Item Status',
            'item_end' => 'Item End',
            'update_time' => 'Update Time',
            'create_time' => 'Create Time',
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

    /**
     * 获取商品列表信息
     * @param integer $channelid
     * @param integer $courseid
     */
    public static function findItemList($channelid)
    {
        return static::find()->where('channelid=:channelid and item_status=0 and item_end>NOW()',[":channelid"=>$channelid])
            ->orderBy(['create_time'=>SORT_DESC])
            ->all();
    }
    
    public static function findCanRegItemList($actid){
    	return static::find()->where("actid=:actid and item_status=".self::STATUS_NORMAL." and item_end>NOW()",[":actid"=>$actid])
    	->orderBy("create_time desc")
    	->all();
    }
}
