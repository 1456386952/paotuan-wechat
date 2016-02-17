<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "register_config".
 *
 * @property integer $courseid
 * @property integer $need_passport_name
 * @property integer $need_user_gender
 * @property integer $need_nationality
 * @property integer $need_id_number
 * @property integer $need_id_copy
 * @property integer $need_birthday
 * @property integer $need_tshirt_size
 * @property integer $need_shoes_size
 * @property integer $need_address
 * @property integer $need_blood_type
 * @property integer $need_height
 * @property integer $need_weight
 * @property integer $need_waistline
 * @property integer $need_morning_pulse
 * @property integer $need_allergen
 * @property integer $need_medical_history
 * @property integer $need_medical_report
 * @property integer $need_emerge_info
 * @property integer $need_emerge_ship
 * @property integer $need_emerge_cell
 * @property integer $need_emerge_addr
 * @property integer $need_user_email
 * @property integer $need_user_cell
 * @property integer $need_business_college
 * @property integer $need_class_name
 * @property integer $need_best_score
 * @property integer $need_score_cert
 * @property integer $need_remark
 * @property integer $need_group
 * @property string $group_info
 * @property string $register_fee
 * @property string $register_fee_remark
 */
class RegisterConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'register_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['courseid'], 'required'],
            [['courseid', 'need_passport_name', 'need_user_gender', 'need_nationality', 'need_id_number', 'need_id_copy', 'need_birthday', 'need_tshirt_size', 'need_shoes_size', 'need_address', 'need_blood_type', 'need_height', 'need_weight', 'need_waistline', 'need_morning_pulse', 'need_allergen', 'need_medical_history', 'need_medical_report', 'need_emerge_info', 'need_emerge_ship', 'need_emerge_cell', 'need_emerge_addr', 'need_user_email', 'need_user_cell', 'need_business_college', 'need_class_name', 'need_best_score', 'need_score_cert', 'need_remark', 'need_group'], 'integer'],
            [['register_fee'], 'number'],
            [['group_info'], 'string', 'max' => 320],
            [['register_fee_remark'], 'string', 'max' => 640]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'courseid' => '科目ID',
            'need_passport_name' => '是否需要姓名',
            'need_user_gender' => '是否需要性别',
            'need_nationality' => '是否需要国籍',
            'need_id_number' => '是否需要证件',
            'need_id_copy' => '是否需要证件复印件',
            'need_birthday' => '是否需要生日',
            'need_tshirt_size' => '是否需要上衣尺码',
            'need_shoes_size' => '是否需要鞋子尺码',
            'need_address' => '是否需要通信地址',
            'need_blood_type' => '是否需要血型',
            'need_height' => '是否需要身高[CM]',
            'need_weight' => '是否需要体重[KG]',
            'need_waistline' => '是否需要腰围[CM]',
            'need_morning_pulse' => '是否需要晨脉 次/分',
            'need_allergen' => '是否需要过敏源',
            'need_medical_history' => '是否需要既往病史',
            'need_medical_report' => '是否需要体检报告复印件',
            'need_emerge_info' => '是否需要紧急联系人姓名',
            'need_emerge_ship' => '是否需要紧急联系人关系',
            'need_emerge_cell' => '是否需要紧急联系人电话',
            'need_emerge_addr' => '是否需要紧急联系人地址',
            'need_user_email' => '是否需要用户邮箱',
            'need_user_cell' => '是否需要用户手机',
            'need_business_college' => '是否需要商学院信息',
            'need_class_name' => '是否需要商学院班级信息',
            'need_best_score' => '是否需要最好赛事成绩',
            'need_score_cert' => '是否需要成绩证书复印件',
            'need_remark' => '是否允许写备注信息',
            'need_group' => '是否需要设置分组信息',
            'group_info' => '自定义分组信息,如志愿者服务类型，计时服务,引导服务,医疗服务,补给服务等等;以字符串，逗号分隔符存储，以供选择',
            'register_fee' => '报名费用',
            'register_fee_remark' => '费用说明',
        ];
    }
    
    public function getActCourse()
    {
        return $this->hasOne(ActCourse::className(), ['courseid' => 'courseid']);
    }
}
