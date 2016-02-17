<?php

namespace paotuan_wechat\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "partner".
 *
 * @property integer $partnerid
 * @property integer $uid
 * @property string $passport_name
 * @property string $cell
 * @property string $birthday
 * @property string $address
 * @property string $reserve_time
 * @property string $update_time
 * @property string $create_time
 */
class Partner extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partnerid', 'uid', 'passport_name', 'cell', 'birthday', 'address', 'reserve_time', 'update_time', 'create_time'], 'required'],
            [['partnerid', 'uid'], 'integer'],
            [['birthday', 'reserve_time', 'update_time', 'create_time'], 'safe'],
            [['passport_name'], 'string', 'max' => 64],
            [['cell'], 'string', 'max' => 20],
            [['address'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'partnerid' => 'Partnerid',
            'uid' => 'Uid',
            'passport_name' => 'Passport Name',
            'cell' => 'Cell',
            'birthday' => 'Birthday',
            'address' => 'Address',
            'reserve_time' => 'Reserve Time',
            'update_time' => 'Update Time',
            'create_time' => 'Create Time',
        ];
    }
    
    /*新建*/
    public static function create($uid,$param)
    {
        if ($param && $uid) {
            $Partner = static::findOne(['uid'=>$uid]);
            if(!$Partner){
                $Partner = new Partner();
                $Partner->uid = $uid;
                $Partner->passport_name = $param['passport_name'];
                $Partner->cell = $param['cell'];
                $Partner->birthday = $param['birthday'];
                $Partner->address = $param['address'];
                $Partner->reserve_time = $param['reserve_time'];
                $Partner->update_time = new Expression('NOW()');
                $Partner->create_time = new Expression('NOW()');
                if (!$Partner->save(false))
                {
                    return false;
                }
            }
            return $Partner;
        }
        return false;
    }
}
