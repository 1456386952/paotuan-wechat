<?php

namespace paotuan_wechat\models;

use Yii;

/**
 * This is the model class for table "activity_config".
 *
 * @property integer $id
 * @property integer $act_id
 * @property string $col_name
 * @property string $col_title
 * @property integer $visible
 * @property integer $optional
 * @property string $comment
 * @property integer $col_type
 * @property string $col_list_values
 * @property integer $sort
 * @property integer $system
 */
class ActivityConfig extends \yii\db\ActiveRecord
{
	const TYPE_TEXT=1;
	const TYPE_LIST=2;
	const TYPE_DATE=3;
	const TYPE_TIME=4;
	const TYPE_FILE=5;
	const TYPE_EMAIL=6;
	const TYPE_NUMBER=7;
	const TYPE_LONG_TEXT=8;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['act_id'], 'required'],
            [['act_id', 'visible', 'optional', 'col_type', 'sort', 'system'], 'integer'],
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
            'act_id' => '活动ID',
            'col_name' => '字段名',
            'col_title' => '字段title',
            'visible' => '字段是否展示',
            'optional' => '字段是否必填',
            'comment' => '备注',
            'col_type' => '1:文本,2:列表,3:日期,4:时间,5:文件,6:email,7:数字,8:长文本',
            'col_list_values' => '列表的值选项，当col_type为列表时，此字段起作用',
            'sort' => '排序号 ',
            'system' => '是否系统字段',
        ];
    }
}
