<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "order_detail".
 *
 * @property integer $detailid
 * @property integer $orderid
 * @property integer $itemid
 * @property string $item_title
 * @property integer $item_num
 * @property string $item_price
 * @property integer $item_status
 * @property string $update_time
 * @property string $create_time
 */
class OrderDetail extends \yii\db\ActiveRecord
{
	const STATUS_NORMAL=0;
	const STATUS_CANCEL=1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orderid', 'item_num', 'item_price'], 'required'],
            [['orderid', 'item_num', 'item_status', 'itemid'], 'integer'],
            [['item_price'], 'number'],
            [['update_time', 'create_time'], 'safe'],
            [['item_title'], 'string', 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'detailid' => '订单详情ID',
            'orderid' => '订单ID',
            'itemid' => '商品id',
            'item_title' => '订单详情项目',
            'item_num' => '数量',
            'item_price' => '价格',
            'item_status' => '详情状态，0正常，1取消',
            'update_time' => '更新时间',
            'create_time' => '生成时间',
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
    
    /*获取商品信息*/
    public function getItemInfo()
    {
        return $this->hasOne(Item::className(), ['itemid' => 'itemid'])
                    ->where('item_status=0');
    }
    
    public function getItemInfoAll()
    {
    	return $this->hasOne(Item::className(), ['itemid' => 'itemid']);
    }
    
    /*根据order id获取订单详情*/
    public static function findOrderDetailInfo($orderid)
    {
        return static::find()
                       ->where('orderid='.$orderid.' and item_status=0')
                       ->with('itemInfo')
                       ->all();
    }
    
    public static function findOrderDetailInfoAll($orderid)
    {
    	return static::find()
    	->where('orderid='.$orderid)
    	->with('itemInfoAll')
    	->all();
    }
}
