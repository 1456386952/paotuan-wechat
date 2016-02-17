<?php

namespace paotuan_wechat\models\bisai;

use Yii;

/**
 * This is the model class for table "result_racer".
 *
 * @property integer $runner_id
 * @property integer $race_id
 * @property integer $course_id
 * @property integer $team_id
 * @property string $team_name
 * @property string $team_type
 * @property string $runner_no
 * @property string $chip_no
 * @property string $runner_name
 * @property integer $runner_gender
 * @property string $runner_cell
 * @property integer $runner_type
 * @property string $nationality
 * @property string $city
 * @property string $tshirt
 * @property string $blood_type
 * @property integer $age
 * @property string $course_group
 * @property string $gender_group
 * @property string $age_group
 * @property string $area_group
 * @property string $team_group
 * @property string $other_group
 * @property string $certificate
 * @property double $gun_time
 * @property double $official_time
 * @property integer $times
 * @property integer $cp_index
 * @property string $cp_name
 * @property integer $has_end
 */
class ResultRacer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'result_racer';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_bisai');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['race_id', 'course_id', 'runner_no', 'runner_name'], 'required'],
            [['race_id', 'course_id', 'team_id', 'runner_gender', 'runner_type', 'age', 'course_group', 'gender_group', 'age_group', 'area_group', 'team_group', 'other_group', 'times', 'cp_index', 'has_end'], 'integer'],
            [['gun_time', 'official_time'], 'number'],
            [['team_name', 'nationality'], 'string', 'max' => 80],
            [['team_type'], 'string', 'max' => 64],
            [['runner_no', 'chip_no', 'city'], 'string', 'max' => 20],
            [['runner_name'], 'string', 'max' => 40],
            [['runner_cell'], 'string', 'max' => 12],
            [['tshirt', 'blood_type'], 'string', 'max' => 10],
            [['certificate'], 'string', 'max' => 60],
            [['cp_name'], 'string', 'max' => 30],
            [['race_id', 'chip_no'], 'unique', 'targetAttribute' => ['race_id', 'chip_no'], 'message' => 'The combination of 赛事ID and 选手计时芯片号 has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'runner_id' => 'Runner ID',
            'race_id' => '赛事ID',
            'course_id' => '科目ID',
            'team_id' => '团队ID',
            'team_name' => '团队名称',
            'team_type' => 'Team Type',
            'runner_no' => '选手号，非ID',
            'chip_no' => '选手计时芯片号',
            'runner_name' => '选手姓名',
            'runner_gender' => '选手性别',
            'runner_cell' => '选手手机',
            'runner_type' => '选手的参赛类型，1个人赛，2团体赛，如果个人科目内分组，此时team_type为分组名称',
            'nationality' => '选手国籍',
            'city' => '户籍城市',
            'tshirt' => '上衣尺码',
            'blood_type' => '血型',
            'age' => '年龄',
            'course_group' => '项目分组',
            'gender_group' => '性别分组',
            'age_group' => '年龄分组',
            'area_group' => '地域分组',
            'team_group' => '队伍分组',
            'other_group' => '其他分组',
            'certificate' => 'Certificate',
            'gun_time' => 'Gun Time',
            'official_time' => 'Official Time',
            'times' => 'Times',
            'cp_index' => 'Cp Index',
            'cp_name' => 'Cp Name',
            'has_end' => 'Has End',
        ];
    }
}
