<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "activity_club".
 *
 * @property integer $id
 * @property integer $club_id
 * @property integer $act_id
 */
class ActivityClub extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_club';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['club_id', 'act_id'], 'integer']
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
            'act_id' => 'Act ID',
        ];
    }
}
