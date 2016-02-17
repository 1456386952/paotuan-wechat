<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "note_scanlog".
 * 
 * @property integer $scanlogid
 * @property integer $uid
 * @property string $noteid
 * @property string $scanlog_ip
 * @property string $create_time
 */
class NoteScanlog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'note_scanlog';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scanlogid', 'uid', 'noteid'], 'integer'],
            [['create_time'], 'safe'],
            [['scanlog_ip'], 'string', 'max' => 15],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'scanlogid' => '浏览id',
            'uid' => '用户id',
            'noteid' => '帖子id',
            'scanlog_ip' => '浏览ip',
            'create_time' => '创建时间',
        ];
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->create_time = new Expression('NOW()');
            }
            return true;
        }
        return false;
    }
    
}
