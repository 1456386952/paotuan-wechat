<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "act_course_info".
 *
 * @property integer $infoid
 * @property integer $courseid
 * @property string $info_title
 * @property string $text_1
 * @property string $image_1
 * @property string $text_2
 * @property string $image_2
 * @property string $text_3
 * @property string $image_3
 * @property integer $sort
 * @property integer $info_status
 * @property string $update_time
 * @property string $create_time
 */
class ActCourseInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_course_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['courseid', 'update_time', 'create_time'], 'required'],
            [['courseid', 'sort', 'info_status'], 'integer'],
            [['update_time', 'create_time'], 'safe'],
            [['info_title'], 'string', 'max' => 64],
            [['text_1', 'text_2', 'text_3'], 'string', 'max' => 640],
            [['image_1', 'image_2', 'image_3'], 'string', 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'infoid' => '科目信息ID',
            'courseid' => '科目ID',
            'info_title' => '信息标题',
            'text_1' => '文字介绍1',
            'image_1' => '图片1',
            'text_2' => '文字介绍2',
            'image_2' => '图片2',
            'text_3' => '文字介绍3',
            'image_3' => '图片3',
            'sort' => '科目内多条信息排序',
            'info_status' => '信息状态',
            'update_time' => '更新时间',
            'create_time' => '创建时间',
        ];
    }
}
