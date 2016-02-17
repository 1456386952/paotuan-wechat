<?php

namespace paotuan_wechat\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "partner_coupon".
 *
 * @property integer $couponid
 * @property string $coupon_code
 * @property integer $uid
 * @property string $update_time
 * @property string $create_time
 */
class PartnerCoupon extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_coupon';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['coupon_code', 'update_time', 'create_time'], 'required'],
            [['uid'], 'integer'],
            [['update_time', 'create_time'], 'safe'],
            [['coupon_code'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'couponid' => 'Couponid',
            'coupon_code' => 'Coupon Code',
            'uid' => 'Uid',
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
}
