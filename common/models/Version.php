<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "version".
 *
 * @property integer $version_id
 * @property string $version_type
 * @property string $version_num
 * @property integer $version_status
 * @property string $version_des
 * @property string $version_size
 * @property string $version_name
 * @property string $update_time
 * @property string $create_time
 */
class Version extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'version';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version_id'], 'required'],
            [['update_time', 'create_time'], 'safe'],
            [['version_id', 'version_status'], 'integer'],
            [['version_type', 'version_num', 'version_size', 'version_name'], 'string', 'max' => 32],
            [['version_des'], 'string', 'max' => 225]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'version_id' => '版本ID',
            'version_type' => '版本类型：android/ios',
            'version_num' => '版本号',
            'version_status' => '版本状态：1表示需要更新，0表示不需要更新',
            'version_des' => '版本描述',
            'version_size' => '版本大小',
            'version_name' => '版本名称',
            'update_time' => '修改时间',
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
}
