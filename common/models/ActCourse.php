<?php

namespace common\models;

use Yii;
use common\models\Act;
use common\models\ActCourseInfo;

/**
 * This is the model class for table "act_course".
 *
 * @property integer $courseid
 * @property integer $actid
 * @property string $course_name
 * @property string $course_intro
 * @property string $course_intro_url
 * @property integer $course_type
 * @property string $course_prereg_start
 * @property string $course_prereg_end
 * @property string $course_prereg_invite
 * @property string $course_register_start
 * @property string $course_register_end
 * @property string $course_payment_end
 * @property string $course_pack_time
 * @property string $course_start
 * @property string $course_close
 * @property string $course_addr
 * @property double $course_lat
 * @property double $course_lng
 * @property double $course_mileage
 * @property integer $need_register
 * @property double $register_fee
 * @property integer $course_status
 * @property string $update_time
 * @property string $create_time
 */
class ActCourse extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_course';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['actid', 'course_type', 'update_time', 'create_time'], 'required'],
            [['actid', 'course_type', 'need_register', 'course_status'], 'integer'],
            [['course_prereg_start', 'course_prereg_end', 'course_register_start', 'course_register_end', 'course_payment_end', 'course_pack_time', 'course_start', 'course_close', 'update_time', 'create_time'], 'safe'],
            [['course_lat', 'course_lng', 'course_mileage', 'register_fee'], 'number'],
            [['course_name'], 'string', 'max' => 20],
            [['course_intro'], 'string', 'max' => 4096],
            [['course_intro_url', 'course_addr'], 'string', 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'courseid' => '活动科目ID',
            'actid' => '活动ID',
            'course_name' => '科目名称',
            'course_intro' => '科目简介',
            'course_intro_url' => '科目介绍URL',
            'course_type' => '科目类型：1个人赛，2团体赛，3志愿者招募,4个人团体赛[参加团体赛的人也可以有个人排名]',
            'course_prereg_start' => '预报名开始时间',
            'course_prereg_end' => '预报名介绍时间',
            'course_register_start' => '科目报名开始时间',
            'course_register_end' => '科目报名结束时间',
            'course_payment_end' => '报名付款截止时间',
            'course_pack_time' => '科目赛包领取时间',
            'course_start' => '科目开始时间',
            'course_close' => '科目关门时间',
            'course_addr' => '科目举行地址',
            'course_lat' => '科目纬度',
            'course_lng' => '科目经度',
            'course_mileage' => '科目的里程',
            'need_register' => '是否需要报名，0不需要，1需要',
            'register_fee' => '官网报名费',
            'course_status' => '科目状态，0正常，1取消',
            'update_time' => '更新时间',
            'create_time' => '创建时间',
        ];
    }
    
    /*获取活动内科目介绍信息*/
    public function getCourseInfo()
    {
        return $this->hasMany(ActCourseInfo::className(), ['courseid' => 'courseid'])
            ->where('info_status=0')
            ->orderBy(['sort'=>SORT_ASC]);
    }
    
    public function getActInfo()
    {
        return $this->hasOne(Act::className(), ['actid' => 'actid']);
    }
}
