<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use common\models\Act;
use common\component\CustomHelper;
use paotuan_wechat\models\Club;
/**
 * This is the model class for table "act_follow".
 *
 * @property integer $followid
 * @property integer $actid
 * @property integer $uid
 * @property integer $follow_type
 * @property integer $follow_status
 * @property string $update_time
 * @property string $create_time
 */
class ActFollow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_follow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['actid', 'uid'], 'required'],
            [['actid', 'uid', 'follow_type', 'follow_status', 'followid'], 'integer'],
            [['update_time', 'create_time'], 'safe'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'followid' => '关注流水ID',
            'actid' => '活动ID',
            'uid' => '关注者ID',
            'follow_type' => '关注类型：0 关注 ，1 点赞',
            'follow_status' => '关注状态，0关注，1取消关注',
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
    /*关注：数据库触发器更新act主表的相应信息*/
    public static function addFollow($uid,$actid,$type=0)
    {
        $IsFollow=  static::findOne(['actid'=>$actid,'uid'=>$uid,'follow_type'=>$type]);
        $act = Act::findOne(["actid"=>$actid]);
        if(!empty($IsFollow))
        {
            if($IsFollow->follow_status==1)
            {
                $IsFollow->follow_status=0;
                $IsFollow->save();
                
                // act关注数加1
                if (!$type)
                {
                    self::updateActFollowNum($actid, true);
                }
            }
            return TRUE;
        }
        else
        {
            $AddFollow=new ActFollow();
            $AddFollow->uid=$uid;
            $AddFollow->actid=$actid;
            $AddFollow->follow_status=0;
            $AddFollow->follow_type=$type;
            $AddFollow->save();
            
            // act关注数加1
            if (!$type)
            {
                self::updateActFollowNum($actid, true);
            }
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * 修改活动的关注数
     * @param int $act_id
     * @param boolean $follow_flag 增加=true/减少=false
     */
    public static function updateActFollowNum($act_id, $follow_flag)
    {
        $act = Act::findOne(['actid'=>$act_id]);
        if($follow_flag)
        {
            // act关注数加1
            $act->follow_sum = $act->follow_sum + 1;
            $act->save(false);
        }
        else 
        {
            // act关注数减1
            $follow_sum = $act->follow_sum;
            if ($follow_sum > 0)
            {
                $act->follow_sum = $act->follow_sum - 1;
                $act->save(false);
            }
        }
    }
    
    /*取消关注：数据库触发器更新act主表的相应信息*/
    public static function cancelFollow($uid,$actid)
    {
        $IsFollow=  static::findOne(['actid'=>$actid,'uid'=>$uid]);
        if(!empty($IsFollow))
        {
            $IsFollow->follow_status=1;
            $IsFollow->save();
            
            // act关注数减1
            self::updateActFollowNum($actid, false);
            return TRUE;
        }
        return FALSE;
    }
    
    /*是否关注/点赞*/
    public static function isFollow($uid,$actid,$follow_type=0)
    {
        $isFollow=  static::findOne(['actid'=>$actid,'uid'=>$uid,'follow_status'=>0,'follow_type'=>$follow_type]);
        if($isFollow)
        {
            return 1;
        }
        return 0;
    }
    
    public function getAct()
    {
        return $this->hasOne(Act::className(), ['actid' => 'actid']);
    }
    
    public function getUserInfo()
    {
        return $this->hasOne(UserMaster::className(), ['uid' => 'uid']);
    }
    
    /*获取用户关注的活动*/
    public static function userFollow($uid,$offset=0,$limit=20)
    {
        $Result = static::find()
                ->where("uid=".$uid." AND follow_type=0 and follow_status=0")
                ->orderBy(['update_time'=>SORT_DESC])
                ->offset($offset)->limit($limit)
                ->with('act')
                ->all();
        return $Result;
    }
    
    /*获取点赞人数*/
    public static function getPointNum($actid)
    {
        $point_num = static::findBySql("select count(1) from act_follow where actid=".$actid." and follow_type=1 and follow_status=0")->count();
        return (int)$point_num;
    }
    
    //按照列表格式返回数据
    public static function FormartFollowActList($Lists)
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
                $arr[$i]['country_logo'] = '';
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
                
                //是否有名额转让
                $arr[$i]['has_transfer'] = 0;
                if (!empty($r->noteInfo))
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
                if(!empty($r->act_logo))
                {
                    $act_logo = CustomHelper::CreateImageUrl($r->act_logo, 'small80');
                }
                $arr[$i]['act_logo']= $act_logo;
//                 $arr[$i]['follow']=$r->follow_sum;
                $arr[$i]['wantgo']=$r->wantgo_sum;
                
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
    
    /*获取关注列表*/
    public static function findFollowList($actid)
    {
        return static::find()
                        ->where("actid=".$actid)
                        ->orderBy(['create_time'=>SORT_DESC])
                        ->with('userInfo')
                        ->all();
    }
}
