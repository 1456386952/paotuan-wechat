<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "club_config".
 *
 * @property integer $id
 * @property integer $club_id
 * @property string $col_name
 * @property string $col_title
 * @property integer $visible
 * @property integer $optional
 * @property string $comment
 */
class ClubConfig extends \yii\db\ActiveRecord
{
	const TYPE_TEXT=1;
	const TYPE_LIST=2;
	const TYPE_DATE=3;
	const TYPE_TIME=4;
	const TYPE_FILE=5;
	const TYPE_EMAIL=6;
	const TYPE_NUMBER=7;
	const TYPE_LONG_TEXT=8;
	const TYPE_CELLPHONE=44;
	const MODULE_NAME_BM="报名信息";
	const MODULE_NAME_BASE="会员基本信息";
	const MODULE_NAME_PT="跑团要求信息";
	const MODULE_NAME_EX="扩展信息";
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'club_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['club_id'], 'required'],
            [['club_id', 'visible', 'optional','col_type','system','sort'], 'integer'],
            [['col_name', 'col_title'], 'string', 'max' => 60],
            [['comment'], 'string', 'max' => 600],
        	[['col_list_values'], 'string', 'max' => 256]
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
            'col_name' => 'Col Name',
            'col_title' => 'Col Title',
            'visible' => 'Visible',
            'optional' => 'Optional',
            'comment' => 'Comment',
        ];
    }
}
