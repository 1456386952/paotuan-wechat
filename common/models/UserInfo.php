<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "user_info".
 *
 * @property integer $uid
 * @property string $passport_name
 * @property string $screen_name
 * @property integer $user_gender
 * @property integer $nationality
 * @property integer $id_type
 * @property string $id_number
 * @property integer $has_id_copy
 * @property string $birthday
 * @property string $tshirt_size
 * @property string $shoes_size
 * @property string $address
 * @property string $blood_type
 * @property integer $height
 * @property integer $weight
 * @property integer $morning_pulse
 * @property integer $waistline
 * @property string $allergen
 * @property string $medical_history
 * @property integer $has_medical_report
 * @property integer $has_cert
 * @property string $emerge_name
 * @property string $emerge_cell
 * @property string $emerge_ship
 * @property string $emerge_addr
 * @property string $user_email
 * @property string $user_cell
 * @property string $education
 * @property string $company_name
 * @property string $job_title
 * @property string $run_age
 * @property string $run_line
 * @property string $best_score
 * @property string $update_time
 * @property string $create_time
 */
class UserInfo extends \yii\db\ActiveRecord
{
	public $id_copy;
	public $id_copy_back;
	public $health;
	public $certs;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'required'],
            [['uid', 'user_gender', 'id_type', 'has_id_copy', 'height', 'weight', 'waistline', 'morning_pulse', 'has_medical_report','has_cert'], 'integer'],
            [['birthday', 'update_time', 'create_time',"id_copy","id_copy_back","health","certs"], 'safe'],
            [['passport_name', 'id_number', 'emerge_name','nationality', 'emerge_ship','screen_name','education','company_name','job_title','run_age'], 'string', 'max' => 64],
            [['tshirt_size', 'shoes_size'], 'string', 'max' => 4],
            [['address', 'allergen', 'emerge_addr', 'user_email'], 'string', 'max' => 120],
            [['blood_type'], 'string', 'max' => 6],
            [['medical_history','run_line'], 'string', 'max' => 640],
            [['emerge_cell', 'user_cell', 'best_score'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => '用户UID',
            'passport_name' => '姓名',
            'screen_name' => '网名',
            'user_gender' => '性别：0未设置，1男，2女',
            'nationality' => '国籍',
            'id_type' => '证件类型1身份证，2护照，3台胞证，4港澳通行证,0其它',
            'id_number' => '证件号码',
            'has_id_copy' => '是否有证件复印件',
            'birthday' => '生日',
            'tshirt_size' => '上衣尺码',
            'shoes_size' => '鞋子尺码',
            'address' => '通信地址',
            'blood_type' => '血型',
            'height' => '身高[CM]',
            'weight' => '体重[KG]',
            'morning_pulse' => '晨脉 次/分',
            'waistline' => '腰围[CM]',
            'allergen' => '过敏源',
            'medical_history' => '既往病史',
            'has_medical_report' => '体检报告复印件',
            'has_cert'=>'是否有完赛证明',
            'emerge_name' => '紧急联系人姓名',
            'emerge_cell' => '紧急联系人电话',
            'emerge_ship' => '紧急联系人关系',
            'emerge_addr' => '紧急联系人地址',
            'user_email' => '用户邮箱',
            'user_cell' => '用户手机',
            'education' => '学历',
            'company_name' => '公司名称',
            'job_title' => '职务',
            'run_age' => '跑龄',
            'run_line' => '日常跑步路线',
            'best_score' => '最好赛事成绩',
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
