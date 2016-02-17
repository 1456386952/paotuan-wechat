<?php

namespace common\models;

use Yii;
use common\models\Country;
use common\models\City;
use common\models\ActCourse;
use common\models\Area;
use common\component\CustomHelper;
use yii\db\Expression;
use running_api\models\ActSponsor;
use running_api\models\Club;
use common\component\ImageLibrary;

/**
 * This is the model class for table "act".
 *
 * @property integer $actid
 * @property string $act_name
 * @property string $act_sub_title
 * @property string $act_logo
 * @property string $short_name
 * @property string $official_url
 * @property integer $countryid
 * @property integer $areaid
 * @property integer $provinceid
 * @property integer $cityid
 * @property string $act_site
 * @property string $act_addr
 * @property double $act_lat
 * @property double $act_lng
 * @property integer $act_type
 * @property integer $act_sub_type
 * @property string $act_day
 * @property string $register_start
 * @property string $register_end
 * @property string $act_start
 * @property string $act_end
 * @property integer $has_score
 * @property integer $has_register
 * @property integer $has_post
 * @property integer $act_status
 * @property integer $creater_uid
 * @property string $act_intro
 * @property string $act_detail
 * @property string $act_detail_url
 * @property string $act_image
 * @property integer $follow_sum
 * @property integer $wantgo_sum
 * @property integer $sort
 * @property string $update_time
 * @property string $create_time
 */
class Act extends \yii\db\ActiveRecord
{
	const ACT_STATUS_NORMAL=0;
	const ACT_STATUS_CANCEL=1;
	const ACT_STATUS_END=2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['countryid', 'areaid', 'provinceid', 'cityid', 'act_type', 'act_sub_type', 'has_score', 'has_register', 'has_post', 'act_status', 'creater_uid', 'follow_sum', 'wantgo_sum','sort'], 'integer'],
            [['act_lat', 'act_lng'], 'number'],
            [['act_type', 'act_day'], 'required'],
            [['act_day', 'register_start', 'register_end', 'act_start', 'act_end', 'update_time', 'create_time'], 'safe'],
            [['act_detail'], 'string'],
            [['act_name', 'act_sub_title', 'act_logo', 'official_url', 'act_site', 'act_addr', 'act_detail_url', 'act_image'], 'string', 'max' => 120],
            [['short_name'], 'string', 'max' => 15],
            [['act_intro'], 'string', 'max' => 4096],
            [['short_name'], 'unique']
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'actid' => '活动ID',
            'act_name' => '活动名称',
            'act_sub_title' => '活动子标题',
            'act_logo' => '活动LOGO',
            'short_name' => '活动英文短名称，唯一',
            'official_url' => '活动官网URL',
            'countryid' => '国家或地区ID',
            'areaid' => '举办区域ID',
            'provinceid' => '省市ID',
            'cityid' => '活动举办城市ID',
            'act_site' => '活动举办地点',
            'act_addr' => '活动具体地址',
            'act_lat' => '活动纬度',
            'act_lng' => '活动经度',
            'act_type' => '活动类型：1赛事，2约跑，3讲座，4训练，5约跑联盟',
            'act_sub_type' => '赛事类型：act_type为赛事时有效，1马拉松，2越野赛，3路跑，4天空马拉松，5铁人三项，6定向赛',
            'act_day' => '活动日期',
            'register_start' => '活动报名开始时间',
            'register_end' => '活动报名结束时间',
            'act_start' => '活动开始时间',
            'act_end' => '活动结束时间',
            'has_score' => '系统内是否有赛事成绩，0没有，1有',
            'has_register' => '系统内是否有赛事报名，0没有，1有',
            'has_post' => '系统内是否有赛包带领，0没有，1有',
            'act_status' => '活动状态，0-正常，1-已取消，2-已结束',
            'creater_uid' => '活动创建者',
            'act_intro' => '活动简介',
            'act_detail' => '活动图文详情',
            'act_detail_url' => '活动图文介绍url',
            'act_image' => '活动主图',
            'follow_sum' => '关注人数',
            'wantgo_sum' => '想去人数',
            'sort' => '排序值,数值越大，推荐的程度越高',
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
    /*获取活动所在的国家*/
    public function getCountry()
    {
        return $this->hasOne(Country::className(),['countryid'=>'countryid']);
    }
    /*获取活动所在的区域*/
    public function getArea()
    {
        return $this->hasOne(Area::className(),['areaid'=>'areaid']);
    }
    /*获取活动所在的省份*/
    public function getProvince()
    {
        return $this->hasOne(Province::className(),['provinceid'=>'provinceid']);
    }
    /*获取活动所在的城市*/
    public function getCity()
    {
        return $this->hasOne(City::className(),['cityid'=>'cityid']);
    }
    /*获取可报名的科目*/
    public function getRegCourses()
    {
        return $this->hasMany(ActCourse::className(), ['actid' => 'actid'])
                    ->where('need_register=1 and course_status=0 and NOW() between course_register_start and course_register_end')
                    ->orderBy('courseid');
    }
    /*获取活动内科目*/
    public function getCourses()
    {
        return $this->hasMany(ActCourse::className(), ['actid' => 'actid'])
            ->where('course_status=0')
            ->orderBy('courseid');
    }
    /*获取赛事主办方关联信息*/
    public function getActSponsors()
    {
        return $this->hasMany(ActSponsor::className(), ['actid' => 'actid'])
            ->where('sponsor_status=0')
            ->orderBy('create_time');
    }
    /*获取发帖信息*/
    public function getNoteInfo()
    {
        return $this->hasMany(Note::className(), ['actid' => 'actid'])
                    ->where('note_status=0')
                    ->orderBy('create_time');
    }
    /*获取推荐活动*/
    public static function Recommend($offset=0,$limit=20)
    {
        $Result = static::find()
                ->where("act_day is not null and act_status=0")
                ->orderBy(['sort'=>SORT_DESC])->offset($offset)->limit($limit)
                ->with('country','city','courses','actSponsors','noteInfo')
                ->all();
        return $Result;
    }
    /*获取热门活动*/
    public static function Hot($offset=0,$limit=20)
    {
        $Result = static::find()
                ->where("act_day is not null and act_status=0")
                ->orderBy(['follow_sum'=>SORT_DESC,'wantgo_sum'=>SORT_DESC])
                ->offset($offset)->limit($limit)
                ->with('country','city','courses','actSponsors','noteInfo')
                ->all();
        return $Result;
    }
    /*获取近期活动*/
    public static function Recent($offset=0,$limit=20)
    {
        // 删除的查询条件 DATE(register_start)>DATE_SUB(CURDATE(), INTERVAL 10 DAY) or DATE(act_start)>DATE_SUB(CURDATE(), INTERVAL 10 DAY)
        $Result = static::find()
                ->where('act_day is not null and act_status=0 and act_day >= now()')
                ->orderBy(['act_day'=>SORT_ASC])
                ->offset($offset)->limit($limit)
                ->with('country','city','courses','regCourses','noteInfo')
                ->all();
        return $Result;
    }
    
    /*获取区域内活动*/
    public static function findByArea($AreaID=1,$offset=0,$limit=20)
    {
        $Result = static::find()
                ->where('act_day is not null and act_status=0 and areaid='.$AreaID)
                ->orderBy(['act_day'=>SORT_ASC])
                ->offset($offset)->limit($limit)
                ->with('country','city','courses','regCourses')
                ->all();
        return $Result;
    }
    /*获取城市内活动*/
    public static function findByCity($CityID=1,$offset=0,$limit=20)
    {
        $Result = static::find()
                ->where('act_day is not null and act_status=0 and cityid='.$CityID)
                ->orderBy(['act_day'=>SORT_ASC])
                ->offset($offset)->limit($limit)
                ->with('country','city','courses','regCourses')
                ->all();
        return $Result;
    }
    /* 获取国家活动  */
    public static function findByCountry($countryId=1,$offset=0,$limit=20)
    {
       $Result = static::find()
                ->where('act_day is not null and act_status=0 and countryid='.$countryId)
                ->orderBy(['act_day'=>SORT_ASC])
                ->offset($offset)->limit($limit)
                ->with('country','city','courses','regCourses')
                ->all();
        return $Result;
    }
    /*获取活动类型活动*/
    public static function findByCategory($act_type=1,$offset=0,$limit=20)
    {
        $act_types = ['race'=>1]; 
        $Result = static::find()
                ->where('act_day is not null and act_status=0 and act_type='.$act_types[$act_type])
                ->orderBy(['act_day'=>SORT_ASC])
                ->offset($offset)->limit($limit)
                ->with('country','city','courses','regCourses')
                ->all();
        return $Result;
    }
    /*获取活动子类型活动*/
    public static function findBySubCategory($act_sub_type=1,$offset=0,$limit=20)
    {
        $sub_types = [ 'marathon' => 1, 'motocross' => 2,'roadrace' => 3 ,'placerace' => 4];
        $typeIndex=!empty($sub_types[$act_sub_type])?$sub_types[$act_sub_type]:1;
        $Result = static::find()
                ->where('act_day is not null and act_status=0 and act_sub_type='.$typeIndex)
                ->orderBy(['act_day'=>SORT_ASC])
                ->offset($offset)->limit($limit)
                ->with('country','city','courses','regCourses')
                ->all();
        return $Result;
    }
    /*根据时间区间获取活动*/
    /*1:报名中*/
    public static function findByRegistration($offset=0,$limit=20)
    {
        $Result = static::find()
                ->where('act_day is not null and act_status=0 and NOW() BETWEEN register_start AND register_end')
                ->orderBy(['act_day'=>SORT_ASC])
                ->offset($offset)->limit($limit)
                ->with('country','city','courses','regCourses')
                ->all();
        // 只显示可以参加的赛事，排除没有科目的赛事
        $act_arr = array();
        if(!empty($Result))
        {
            foreach ($Result as $act)
            {
                $courses=$act->regCourses;
                if(!empty($courses))
                {
                    array_push($act_arr, $act);
                }
            }
        }
        return $act_arr;
    }
    /*2:年份*/
    public static function findByYear($year,$offset=0,$limit=20)
    {
        $Result = static::find()
                ->where('act_day is not null and act_status=0 and YEAR(act_day) = '.$year.' OR YEAR(act_day) = '.$year)
                ->orderBy(['act_day'=>SORT_ASC])
                ->offset($offset)->limit($limit)
                ->with('country','city','courses','regCourses')
                ->all();
        return $Result;
    }
    /*3:月份*/
    public static function findByMonth($month,$offset=0,$limit=20)
    {
        $Result = static::find()
                ->where("act_day is not null and act_status=0 and DATE_FORMAT(act_day,'%Y%m') = ".$month)
                ->orderBy(['act_day'=>SORT_ASC])
                ->offset($offset)->limit($limit)
                ->with('country','city','courses','regCourses')
                ->all();
        return $Result;
    }
    /*搜索活动*/
    /*根据传人的Tag搜索*/
    public static function findByTag($tag,$offset=0,$limit=20)
    {
        $Result = static::find()
                ->where("act_day is not null and act_status=0 and act_name LIKE '%".$tag."%' OR act_site LIKE '%".$tag."%'")
                ->orderBy(['act_day'=>SORT_ASC])
                ->offset($offset)->limit($limit)
                ->with('country','city','courses','regCourses','noteInfo')
                ->all();
        return $Result;
    }
    
    // 联合查询
    public static function findJointByCondition($param,$offset=0,$limit=20)
    {
        $time_type = $param["time_type"];
        $time_value = $param["time_value"];
        $site_type = $param["site_type"];
        $site_value = $param["site_value"];
        $category_type = $param["category_type"];
        $category_value = $param["category_value"];
        $fun_type = $param["fun_type"];
        
        // 时间查询条件
        $time_where = "";
        if ($time_type)
        {
            switch ($time_type)
            {
                case 'year':
                    $time_where = " and YEAR(act_day) = ".$time_value;
                    break;
                case 'month':
                    $time_where = " and DATE_FORMAT(act_day,'%Y%m') = ".$time_value;
                    break;
                case 'time':
                    $time_where = " and has_register=1 and NOW() BETWEEN register_start AND register_end";
                    break;
                default:
                    $time_where = " and act_day >= now()";
                    break;
            }
        }
        // 地点查询条件 
        $site_where = "";
        if ($site_type)
        {
            switch ($site_type)
            {
                case 'area':
                    $site_where = " and areaid=".$site_value;
                    break;
                case 'city':
                    $site_where = " and cityid=".$site_value;
                    break;
                case 'country':
                    $site_where = " and countryid=".$site_value;
                    break;
            }
        }
        // 类型查询条件
        $category_where = "";
        if ($category_type)
        {
            switch ($category_type)
            {
                case 'category':
                    $act_types = ['race'=>1];
                    $category_where = " and act_type=".$act_types[$category_value];
                    break;
                case 'subcategory':
                    $sub_types = [ 'marathon' => 1, 'motocross' => 2,'roadrace' => 3 ,'placerace' => 4,'ironman' => 5,'directional' => 6];
                    $typeIndex=!empty($sub_types[$category_value])?$sub_types[$category_value]:1;
                    $category_where = " and act_sub_type=".$typeIndex;
                    break;
            }
        }
        
        // 功能类型查询条件
        $fun_where = "";
        if ($fun_type)
        {
            switch ($fun_type)
            {
                case 'racePackage':
                    $fun_where = " and has_post=1";
                    break;
                case 'transfer':
                    $fun_where = " and actid in (select n.actid from note n where n.note_status=0) ";
                    break;
            }
        }
        
        $Result = static::find()
                        ->where("act_day is not null and act_status=0 ".$time_where.$site_where.$category_where.$fun_where)
                        ->orderBy(['act_day'=>SORT_ASC])
                        ->offset($offset)->limit($limit)
                        ->with('country','city','courses','regCourses','actSponsors','noteInfo')
                        ->all();
        return $Result;
    }
    
    //按照列表格式返回数据
    public static function FormartList($Lists)
    {
        if(!empty($Lists))
        {
            $arr=[];
            $i=0;
            foreach($Lists as $r)
            {
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
//                 if (!empty($r->act_site))
//                 {
//                     $arr[$i]['act_site'].=','.$r->act_site;
//                 }
                
                //是否有名额转让
                $arr[$i]['has_transfer'] = 0;
//                 $act_note = Act::find()->where("actid=".$r->actid." and register_start is not null and act_start is not null and NOW() between register_start and act_start")->one();
                $note_num = Note::find()->andWhere(['actid'=>$r->actid,'note_status'=>0])->count();
                $act_note = Act::find()->where("actid=".$r->actid." and act_day is not null and act_day > now()")->one();
                if ($note_num && $act_note)
                {
                    $arr[$i]['has_transfer'] = 1;
                }
                
                //是否可以代领赛包
                $arr[$i]['has_post'] = 0;
                if (!empty($r->has_post))
                {
                    $arr[$i]['has_post'] = 1;
                }
                
                //是否在报名中
                $arr[$i]['has_register'] = 0;
                if (!empty($r->has_register))
                {
                    $arr[$i]['has_register'] = 1;
                }
                
                $arr[$i]['act_start']=date('Y.m.d',  strtotime($r->act_day));
//                 $arr[$i]['register_start']=!empty($r->register_start)?date('Y.m.d H:i:s',  strtotime($r->register_start)):'';
                $act_logo = "";
                if (!empty($r->act_logo))
                {
                    $act_logo = CustomHelper::CreateImageUrl($r->act_logo, 'small80');
                }
                $arr[$i]['act_logo'] = $act_logo;
//                 $arr[$i]['follow']=$r->follow_sum;
                $arr[$i]['wantgo']=$r->wantgo_sum;
                
//                 获取可报名的科目，没有则为空
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

                //获取主办方的logo
                $actSponsors = $r->actSponsors;
                $sponsor_arr = array();
                if ($actSponsors)
                {
                    foreach ($actSponsors as $actSponsor)
                    {
                        $Club = Club::findOne(['clubid'=>$actSponsor->clubid]);
                        if ($Club)
                        {
                            $club_info['club_id'] = $Club->clubid;
                            $club_info['club_logo'] = '';
                            if ($Club->club_logo)
                            {
                                $club_info['club_logo'] = CustomHelper::CreateImageUrl($Club->club_logo, 'img_logo');
                            }
                            array_push($sponsor_arr, $club_info);
                        }
                    }
                }
                $arr[$i]['club'] = $sponsor_arr;
                
                $i++;
            }
            return $arr;
        }
        return FALSE;
    }
    
    public static function FormartAct($Act)
    {
//         $arr['actid']=$Act->actid;
        $arr['act_url']= \Yii::$app->params['site_url'].'act/'.$Act->actid;
//         $arr['act_name']=$Act->act_name;
//         $arr['country_logo'] = '';
//         $arr['act_site']='';
//         if(!empty($Act->country))
//         {
//             $country=$Act->country;
//             $country_logo = '';
//             if (!empty($country->country_logo))
//             {
//                 $country_logo = CustomHelper::CreateImageUrl($country->country_logo, 'img_logo');
//             }
//             $arr['country_logo']=$country_logo;
//         }

//         if(!empty($Act->city))
//         {
//             $city=$Act->city;
//             $arr['act_site']=$city->chn_name;
//         }
        
//         if (!empty($Act->act_site))
//         {
//             $arr['act_site'].=','.$Act->act_site;
//         }
        
//         //是否有名额转让
//         $arr['has_transfer'] = 0;
//         if (!empty($r->noteInfo))
//         {
//             $arr['has_transfer'] = 1;
//         }
        
//         //是否可以代领赛包
//         $arr['has_post'] = 0;
//         if (!empty($r->has_post))
//         {
//             $arr['has_post'] = 1;
//         }
        
//         //是否在报名中
//         $arr['has_register'] = 0;
//         if (!empty($r->has_register))
//         {
//             $arr['has_register'] = 1;
//         }
        
//         $arr['act_start']=date('Y.m.d',  strtotime($Act->act_day));
//         $arr['register_start']=!empty($Act->register_start)?date('Y.m.d H:i:s',  strtotime($Act->register_start)):'';
//         $act_logo="";
//         if(!empty($Act->act_logo))
//         {
//             $act_logo = CustomHelper::CreateImageUrl($Act->act_logo, 'small80');
//         }
//         $arr['act_logo']=$act_logo;
        $act_image = "";
        if (!empty($Act->act_image))
        {
            $act_image = CustomHelper::CreateImageUrl($Act->act_image, 'auto640');
        }
        $arr['act_image']=$act_image;
        $act_intro = "";
        if ($Act->act_intro)
        {
            $act_intro = $Act->act_intro;
        }
        $arr['act_intro']=$act_intro;
        $arr['act_detail_url']=$Act->act_detail_url?$Act->act_detail_url:'';
        $arr['follow']=$Act->follow_sum;
        $arr['wantgo']=$Act->wantgo_sum;
        
//         // 获取可报名的科目
//         $regCourses=$Act->regCourses;
//         $arr['register_course'] = [];
//         if(!empty($regCourses)){
//             $j=0;
//             foreach($regCourses as $regCourse){
//                 $arr['register_course'][$j]['courseid']=$regCourse->courseid;
//                 $arr['register_course'][$j]['course_name']=$regCourse->course_name;
//                 $j++;
//             }
//         }
        
        // 获取科目详情信息
        $arr['act_course'] = [];
        $courses=$Act->courses;
        if(!empty($courses)){
            $i=0;
            foreach($courses as $course){
                $arr['act_course'][$i]['courseid']=$course->courseid;
                $arr['act_course'][$i]['course_name']=$course->course_name;
                $arr['act_course'][$i]['course_mileage']=$course->course_mileage?round($course->course_mileage):0;
                $arr['act_course'][$i]['register_fee'] = $course->register_fee?$course->register_fee:0;
//                 $course_infos=$course->courseInfo;
//                 if(!empty($course_infos)){
//                     $j=0;
//                     foreach ($course_infos as $course_info)
//                     {
//                         foreach ($course_info as $k => $val)
//                         {
//                             if(!empty($val)){
//                                 $arr['act_course'][$i]['course_info'][$j][$k]=$val;
//                             }
//                         }
//                         $j++;
//                     }
//                 }
                $i++;
            }
        }
        
//         //获取主办方的logo
//         $actSponsors = $Act->actSponsors;
//         $sponsor_arr = array();
//         if ($actSponsors)
//         {
//             foreach ($actSponsors as $actSponsor)
//             {
//                 $Club = Club::findOne(['clubid'=>$actSponsor->clubid]);
//                 if ($Club)
//                 {
//                     $club_info['club_id'] = $Club->clubid;
//                     $club_info['club_logo'] = '';
//                     if ($Club->club_logo)
//                     {
//                         $club_info['club_logo'] = CustomHelper::CreateImageUrl($Club->club_logo, 'img_logo');
//                     }
//                     array_push($sponsor_arr, $club_info);
//                 }
//             }
//         }
//         $arr['club'] = $sponsor_arr;

        
        //获取该科目有商品的报名通道
        $channel_arr = array();
        $ActChannel = ActChannel::findAll(['actid'=>$Act->actid,'channel_status'=>0]);
        if ($ActChannel)
        {
            foreach ($ActChannel as $channel)
            {
                $channel_data = [];
                $channel_data['club_name'] = '';
                $channel_data['club_logo'] = '';
                $channel_data['channel_id'] = $channel->channelid;
                $channel_data['has_invite_code'] = 0;
                $channel_data['invite_code'] = $channel->invite_code;
                $channel_data['item_list'] = [];
                $Club = $channel->clubInfo;
                if ($channel->invite_code)
                {
                    $channel_data['has_invite_code'] = 1;
                }
                if ($Club)
                {
                    $channel_data['club_name'] = $Club->club_name;
                    if ($Club->club_logo)
                    {
                        $channel_data['club_logo'] = CustomHelper::CreateImageUrl($Club->club_logo, 'img_logo');
                    }
        
                }
                $ItemList = Item::findAll(['channelid'=>$channel->channelid,'item_status'=>0]);
                if ($ItemList)
                {
                    $item_arr = array();
                    foreach ($ItemList as $item)
                    {
                        $item_type = '';
                        switch ($item->item_type)
                        {
                            case 0:
                                $item_type = '名额';
                                break;
                            case 51:
                                $item_type = '住宿';
                                break;
                            case 52:
                                $item_type = '交通';
                                break;
                            case 99:
                                $item_type = '其它';
                                break;
                        }
                        if (!in_array($item_type, $item_arr))
                        {
                            array_push($item_arr,$item_type);
                        }
                    }
                    $channel_data['item_list'] = $item_arr;
                }
                //判断通道名额是否已满
                $channel_data['quotad_full'] = 0;
                $Item_data = Item::find()->where('channelid='.$channel->channelid.' and item_status=0 and item_type=0 and item_buy_sum < item_num_limit')->one();
                if (!$Item_data)
                {
                    $channel_data['quotad_full'] = 1;
                }
                array_push($channel_arr, $channel_data);
            }
        }
        $arr['channel_arr'] = $channel_arr;
        
        //是否可以名额转让
        $arr['has_transfer'] = 0;
        // 是否可以创建报名通道
        $arr['has_channel'] = 0;
        $act_note = Act::find()->where("actid=".$Act->actid." and act_day is not null and act_day > now()")->one();
        if ($act_note)
        {
            $arr['has_transfer'] = 1;
            $arr['has_channel'] = 1;
        }
        
        // 名额转让
//         $arr['has_transfer'] = 0;
        $arr['transfer_num'] = 0;
        $note_num = Note::find()->andWhere(['actid'=>$Act->actid])->count();
        if ($note_num)
        {
//             $arr['has_transfer'] = 1;
            $arr['transfer_num'] = $note_num;
        }
        
        // 是否可以领取赛包
        $arr['has_post'] = $Act->has_post;
        $ItemPost = Item::findOne(['actid'=>$Act->actid,'item_type'=>1]);
        if (!$ItemPost)
        {
            $arr['has_post'] = 0;
        }
        
        // 是否可以进行关网报名
        $arr['has_official'] = 0;
        if ($Act->register_start && strtotime($Act->register_start) < time() && $Act->official_url)
        {
            $arr['has_official'] = 1;
        }
        $arr['official_url'] = $Act->official_url?$Act->official_url:'';
        
        return $arr;
    }
    
    //微信获取活动    add by shizheli
   public static function queryRegisterAct($params,$offset=0,$limit=20){
   	    if(is_array($params)){
   	    	$values=array();
   	    	$strWhere="";
   	    	if(isset($params["operation"])&&!empty($params["operation"])){
   	    		switch(trim(strtolower($params["operation"]))){
   	    			case "bm":$strWhere = $strWhere." and has_register=1";break;
   	    			case "zr":$strWhere = $strWhere." and actid in (select n.actid from note n where n.note_status=0) ";break;
   	    			case "dl":$strWhere = $strWhere." and has_post=1";break;
   	    		}
   	    	}
   	    	
   	    	if(isset($params["act_name"])&&!empty($params["act_name"])){
   	    		$strWhere = $strWhere." and (act_name like :act_name ";
   	    		$values["act_name"]="%".trim($params["act_name"])."%";
   	    	}
   	    	
   	    	if(isset($params["act_site"])){
   	    		$strWhere = $strWhere." or act_site like :act_site) ";
   	    		$values["act_site"]="%".trim($params["act_site"])."%";
   	    	}
   	    	
   	    	$Result = static::find()
   	    	->where('act_day is not null and act_status=0 '.$strWhere)
   	    	->params($values)
   	    	->orderBy(['act_day'=>SORT_ASC])
   	    	->offset($offset)->limit($limit)
   	    	->with('country','city','courses','regCourses')
   	    	->all();
   	    }
   		
   		return $Result;
    }
  
    //获取活动列表
    public static function queryAct($params,$offset=0,$limit=20,$method='data')
    {
        $where = '';
        $value = array();
        if ($params)
        {
            if ($params['act_name'])
            {
                $where .= ' and act_name like :act_name';
                $value['act_name'] = '%'.trim($params['act_name']).'%';
            }
            if ($params['act_start'] && $params['act_end'])
            {
                $where .= ' and act_day between :act_start and :act_end';
                $value['act_start'] = $params['act_start'];
                $value['act_end'] = $params['act_end'];
            }
            if ($params['act_start'] && !$params['act_end'])
            {
                $where .= ' and act_day >= :act_start';
                $value['act_start'] = $params['act_start'];
            }
            if ($params['act_end'] && !$params['act_start'])
            {
                $where .= ' and act_day <= :act_end';
                $value['act_end'] = $params['act_end'];
            }
            if ($params['act_site'])
            {
                $where .= ' and (act_site like :act_site or act_addr like :act_site)';
                $value['act_site'] = '%'.trim($params['act_site']).'%';
            }
        }
        
        if ($method == 'data')
        {
            return static::find()
            ->where('act_day is not null and act_status=0 '.$where)
            ->params($value)
            ->orderBy(['create_time'=>SORT_DESC])
            ->offset($offset)->limit($limit)
            ->with('country','city','courses','regCourses')
            ->all();
        }
        else if ($method == 'count')
        {
            $act_count = static::findBySql("select count(1) from act where act_status=0 ".$where)->params($value)->count();
            return (int)$act_count;
        }
    }
    
    //更新赛事信息
    public static function updateAct($Act)
    {
        //上传logo图
        $file=$_FILES['act_logo'];//获取上传的图片文件信息
        if ($file && $file['size'])
        {
            $dir=DIR_UPLOADS."image/logo/2015/";//文件的存储文件夹
            if(!is_dir($dir))@mkdir($dir,0777,true);//如果文件夹不存在则创建之
            $filename = rand(10000,99999).time().'.'.pathinfo($file['name'], PATHINFO_EXTENSION);
            $filedir=$dir.$filename;//定义上传后的文件名
            if(is_uploaded_file($file['tmp_name'])){
                //判断图片文件是否已经存在于临时目录中
                move_uploaded_file($file['tmp_name'],$filedir);//上传图片
                $success = ImageLibrary::upyun($filename, 'image/logo/2015');
            
                if( ! $success ){
                    echo "无法上传到云端!";exit();
                }
            }
        }
        
        //上传赛事主图
        $file_image=$_FILES['act_image'];//获取上传的图片文件信息
        if ($file_image && $file_image['size'])
        {
            $dir=DIR_UPLOADS."image/theme/2015/";//文件的存储文件夹
            if(!is_dir($dir))@mkdir($dir,0777,true);//如果文件夹不存在则创建之
            $file_image_name = rand(10000,99999).time().'.'.pathinfo($file_image['name'], PATHINFO_EXTENSION);
            $filedir=$dir.$file_image_name;//定义上传后的文件名
            if(is_uploaded_file($file_image['tmp_name'])){
                //判断图片文件是否已经存在于临时目录中
                move_uploaded_file($file_image['tmp_name'],$filedir);//上传图片
                $success = ImageLibrary::upyun($file_image_name, 'image/theme/2015');
        
                if( ! $success ){
                    echo "无法上传到云端!";exit();
                }
            }
        }
        
        $countryid = Country::FindCountryId(trim($Act['country']));
        $areaid = Area::findAreaId(trim($Act['area']), $countryid);
        $provinceid = Province::FindProvinceId(trim($Act['province']), $countryid,$areaid);
        $cityid = City::FindCityId(trim($Act['city']), $countryid, $provinceid);
        $Act['countryid'] = $countryid;
        $Act['areaid'] = $areaid;
        $Act['provinceid'] = $provinceid;
        $Act['cityid'] = $cityid;
        unset($Act['country']);
        unset($Act['area']);
        unset($Act['province']);
        unset($Act['city']);
        if ($Act['actid'])
        {
            $Act_data = self::findOne(['actid'=>$Act['actid']]);
        }
        else 
        {
            $Act_data = new Act();
        }
        $Act_data->setAttributes((array)$Act);
        //获取图片的存储路径
        if (isset($filename) && $filename)
        {
            $Act_data->act_logo = '/image/logo/2015/'.$filename;
        }
        if (isset($file_image_name) && $file_image_name)
        {
            $Act_data->act_image = '/image/theme/2015/'.$file_image_name;
        }
        if ($Act_data->save(false)) {
            @unlink($filedir);
        }
        return true;
    }
    
    //删除赛事
    public static function deleteAct($actid)
    {
        $Act = Act::findOne(['actid'=>$actid,'act_status'=>0]);
        if ($Act)
        {
            $Act->act_status = 1;
            $Act->save();
        }
        return true;
    }
}
