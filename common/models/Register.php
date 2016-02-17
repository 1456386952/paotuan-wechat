<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use common\models\Act;
use common\models\ActCourse;
use common\models\OrderMaster;
use common\component\CustomHelper;
use paotuan_wechat\models\Club;

/**
 * This is the model class for table "register".
 *
 * @property integer $registerid
 * @property integer $uid
 * @property integer $actid
 * @property integer $courseid
 * @property integer $channelid
 * @property integer $teamid
 * @property string $passport_name
 * @property integer $user_gender
 * @property string $nationality
 * @property integer $id_type
 * @property string $id_number
 * @property string $id_copy_url
 * @property string $birthday
 * @property string $tshirt_size
 * @property string $shoes_size
 * @property string $address
 * @property string $blood_type
 * @property integer $height
 * @property integer $weight
 * @property integer $waistline
 * @property integer $morning_pulse
 * @property string $allergen
 * @property string $medical_history
 * @property string $medical_report_url
 * @property string $emerge_name
 * @property string $emerge_ship
 * @property string $emerge_cell
 * @property string $emerge_addr
 * @property string $user_email
 * @property string $user_cell
 * @property string $best_score
 * @property string $score_cert_url
 * @property integer $college_id
 * @property string $college_name
 * @property string $class_name
 * @property string $group_info
 * @property string $remark
 * @property string $invite_code
 * @property integer $role_type
 * @property integer $register_type
 * @property string $register_fee
 * @property integer $payment_status
 * @property integer $orderid
 * @property string $payment_time
 * @property string $runner_no
 * @property integer $register_status
 * @property string $update_time
 * @property string $create_time
 */
class Register extends \yii\db\ActiveRecord
{
	const  PAY_STATUS_FREE=0;
	const  PAY_STATUS_NEED_PAY=1;
	const  PAY_STATUS_NORMAL=2;
	const  PAY_STATUS_CANCEL=3;
	const  STATUS_NORMAL =1;
	const  STATUS_CANCEL =0;
	const  STATUS_REGISTER =2;
	public $id_copy;
	public $id_copy_back;
	public $medical_report;
	public $certs;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'register';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'actid', 'courseid'], 'required'],
            [['uid', 'actid', 'courseid','channelid', 'teamid', 'user_gender', 'id_type', 'height', 'weight', 'waistline', 'morning_pulse', 'college_id', 'role_type', 'register_type', 'payment_status', 'orderid', 'register_status'], 'integer'],
            [['birthday', 'payment_time', 'update_time', 'create_time'], 'safe'],
            [['register_fee'], 'number'],
            [['id_copy',"id_copy_back","medical_report","certs"],"string"],
            [['passport_name', 'nationality', 'emerge_name', 'emerge_ship', 'college_name', 'class_name', 'group_info'], 'string', 'max' => 64],
            [['id_number'], 'string', 'max' => 32],
            [['id_copy_url', 'address', 'allergen', 'medical_report_url', 'emerge_addr', 'user_email', 'best_score', 'score_cert_url'], 'string', 'max' => 120],
            [['tshirt_size', 'shoes_size'], 'string', 'max' => 4],
            [['blood_type', 'emerge_cell', 'user_cell', 'runner_no'], 'string', 'max' => 20],
            [['medical_history', 'remark'], 'string', 'max' => 640],
            [['invite_code'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'registerid' => '报名号',
            'uid' => '用户ID',
            'actid' => '活动ID',
            'courseid' => '活动科目',
            'channelid' => '通道ID',
            'teamid' => '队伍ID',
            'passport_name' => '姓名',
            'user_gender' => '性别1男，2女',
            'nationality' => '国籍',
            'id_type' => '证件类型1身份证，2护照，3台胞证，4港澳通行证',
            'id_number' => '证件号码',
            'id_copy_url' => '证件复印件',
            'birthday' => '生日',
            'tshirt_size' => '上衣尺码',
            'shoes_size' => '鞋子尺码',
            'address' => '通信地址',
            'blood_type' => '血型',
            'height' => '身高[CM]',
            'weight' => '体重[KG]',
            'waistline' => '腰围[CM]',
            'morning_pulse' => '晨脉 次/分',
            'allergen' => '过敏源',
            'medical_history' => '既往病史',
            'medical_report_url' => '体检报告复印件',
            'emerge_name' => '紧急联系人姓名',
            'emerge_ship' => '紧急联系人关系',
            'emerge_cell' => '紧急联系人电话',
            'emerge_addr' => '紧急联系人地址',
            'user_email' => '用户邮箱',
            'user_cell' => '用户手机',
            'best_score' => '最好成绩',
            'score_cert_url' => '成绩证书URL',
            'college_id' => '商学院ID',
            'college_name' => '商学院名称',
            'class_name' => '商学院班级信息',
            'group_info' => '分组信息,如志愿者服务类型：计时服务等等',
            'remark' => '报名备注',
            'invite_code' => '邀请码',
            'role_type' => '报名角色类型	0自己报名，1代报名',
            'register_type' => '报名方式	0正式报名，1预报名［不需要立即支付，正式报名后方可支付］',
            'register_fee' => '报名费用	0免费',
            'payment_status' => '支付状态	0免费，1未支付，2已支付，3取消支付',
            'orderid' => '支付订单号',
            'payment_time' => '支付时间',
            'runner_no' => '选手号',
            'register_status' => '报名状态	1正常，0取消，2名额锁定，3报名确认',
            'update_time' => '更新时间',
            'create_time' => '创建时间',
        ];
    }
    /*是否报名*/
    public static function isRegister($uid,$actid)
    {
        $isRegister=static::findOne(
                [
                    'actid=:actid AND uid=:uid AND register_status>0',
                    [':actid'=>$actid,':uid'=>$uid]
                ]
            );
        if($isRegister)
        {
            return TRUE;
        }
        return FALSE;
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
    
    public function getAct()
    {
        return $this->hasOne(Act::className(), ['actid' => 'actid']);
    }
    
    public function getCourse()
    {
        return $this->hasOne(ActCourse::className(), ['courseid' => 'courseid']);
    }
    
    public function getOrder()
    {
        return $this->hasOne(OrderMaster::className(), ['orderid' => 'orderid']);
    }
    
    /*获取用户报名的用户列表*/
    public static function findUserRegister($uid,$year,$offset=0,$limit=20)
    {
        if (!$year)
        {
            $year = date('Y');
        }
        $Result = static::find()
                ->where('uid='.$uid." AND payment_status=2 and YEAR(create_time)=".$year)
                ->orderBy(['update_time'=>SORT_DESC])
                ->offset($offset)->limit($limit)
                ->with('act','course')
                ->all();
        return $Result;
    }
    
    //按照列表格式返回数据
    public static function FormartRegActList($Lists)
    {
        if(!empty($Lists))
        {
            $arr=[];
            $i=0;
            foreach($Lists as $l)
            {
                $r=$l->act;
                $arr[$i]['actid']=$r->actid;
                $arr[$i]['act_name']=$r->act_name;
                $arr[$i]['country_logo']='';
                $arr[$i]['act_site']='';
                if(!empty($r->country))
                {
                    $country=$r->country;
                    $country_logo = '';
                    if (!empty($country->country_logo))
                    {
                        $country_logo = CustomHelper::CreateImageUrl($country->country_logo, 'img_logo');
                    }
                    $arr[$i]['country_logo']=$country_logo;
                }
                
                if(!empty($r->city))
                {
                    $city=$r->city;
                    $arr[$i]['act_site']=$city->chn_name;
                }
                $arr[$i]['act_start']=date('Y.m.d',  strtotime($r->act_day));
//                 $arr[$i]['register_start']=!empty($r->register_start)?date('Y.m.d H:i:s',  strtotime($r->register_start)):'';
                $act_logo = "";
                if(!empty($r->act_logo))
                {
                    $act_logo = CustomHelper::CreateImageUrl($r->act_logo, 'small80');
                }
                $arr[$i]['act_logo']=$act_logo;
//                 $arr[$i]['follow']=$r->follow_sum;
//                 $arr[$i]['wantgo']=$r->wantgo_sum;
//                 $courses=$r->regCourses;
//                 $arr[$i]['register_course'] = [];
//                 if(!empty($courses)){
//                     $j=0;
//                     foreach($courses as $course){
//                         $arr[$i]['register_course'][$j]['courseid']=$course->courseid;
//                         $arr[$i]['register_course'][$j]['course_name']=$course->course_name;
//                         $j++;
//                     }
//                 }
                //获取参赛的科目信息
                $ActCourse = $l->course;
                $arr[$i]['course_name'] = $ActCourse->course_name;
                $ActWantgo = ActWantgo::findOne(['actid'=>$r->actid]);
                $arr[$i]['entry_no'] = $ActWantgo->entry_no;
                
//                 //获取主办方的logo
//                 $actSponsors = $r->actSponsors;
//                 $sponsor_arr = array();
//                 if ($actSponsors)
//                 {
//                     foreach ($actSponsors as $actSponsor)
//                     {
//                         $Club = Club::findOne(['clubid'=>$actSponsor->clubid]);
//                         if ($Club)
//                         {
//                             $club_info['club_id'] = $Club->clubid;
//                             $club_info['club_logo'] = '';
//                             if ($Club->club_logo)
//                             {
//                                 $club_info['club_logo'] = CustomHelper::CreateImageUrl($Club->club_logo, 'img_logo');
//                             }
//                             array_push($sponsor_arr, $club_info);
//                         }
//                     }
//                 }
//                 $arr[$i]['club'] = $sponsor_arr;
                
                $i++;
            }
            return $arr;
        }
        return FALSE;
    }

    /*下载报名通道的报名信息*/
    public static function DownLoadRegister($channelid)
    {
        $RegObjs = static::find()->where("channelid = {$channelid} AND  register_status>0")
            ->orderBy(['create_time'=>SORT_DESC])
            ->all();
        $Str = chr(0xEF).chr(0xBB).chr(0xBF)."报名号,姓名,性别,赛事名称,联系地址,邮箱,手机号\r\n";
        if($RegObjs){
            
        foreach($RegObjs as $RegObj){
            $Str .= $RegObj->registerid.",".$RegObj->passport_name.",".($RegObj->user_gender==1?"男":"女").",".$RegObj->act->act_name.",".$RegObj->address.",".$RegObj->user_email.",".$RegObj->user_cell."\r\n";
          }
        }
        $road = Yii::getAlias('@runtime/logs');
        $file = $road."/{$channelid}.csv";
        $fp = fopen($file,"w+"); // 打开文件
        fwrite($fp,$Str);
        fclose($fp);
        return $file;
        return false;
    }
}
