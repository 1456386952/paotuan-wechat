<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "email_template".
 *
 * @property integer $email_id
 * @property string $email_name
 * @property string $email_code
 * @property string $email_title
 * @property string $email_body
 * @property string $update_time
 * @property string $create_time
 */
class EmailTemplate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'email_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email_id', 'update_time', 'create_time'], 'required'],
            [['email_id'], 'integer'],
            [['update_time', 'create_time'], 'safe'],
            [['email_code'], 'string', 'max' => 32],
            [['email_name', 'email_title'], 'string', 'max' => 225],
            [['email_body'], 'string', 'max' => 4096],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email_id' => '邮件模板ID',
            'email_code' => '邮件模板code',
            'email_name' => '邮件名称',
            'email_title' => '邮件标题',
            'email_body' => '邮件内容',
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
}
