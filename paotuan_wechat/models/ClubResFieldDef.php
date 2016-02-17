<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "club_reserved_field_def".
 *
 * @property integer $id
 * @property integer $club_id
 * @property string $field_column_name
 * @property string $field_column_title
 * @property integer $optional
 */
class ClubResFieldDef extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'club_reserved_field_def';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['club_id', 'optional'], 'integer'],
            [['field_column_name', 'field_column_title'], 'string', 'max' => 45]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'club_id' => 'Club ID',
            'field_column_name' => 'Field Column Name',
            'field_column_title' => 'Field Column Title',
            'optional' => 'Optional',
        ];
    }
}
