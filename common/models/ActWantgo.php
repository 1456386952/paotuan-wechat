<?php

namespace common\models;

use Yii;
use yii\db\Expression;
/**
 * This is the model class for table "act_wantgo".
 *
 * @property integer $wantgoid
 * @property integer $actid
 * @property integer $courseid
 * @property string $entry_no
 * @property integer $uid
 * @property integer $wantgo_status
 * @property string $update_time
 * @property string $create_time
 */
class ActWantgo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_wantgo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['actid', 'uid'], 'required'],
            [['actid', 'courseid', 'uid', 'wantgo_status'], 'integer'],
            [['entry_no'], 'string', 'max' => 12],
            [['update_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'wantgoid' => '关注流水ID',
            'actid' => '活动ID',
            'courseid' => '科目ID',
            'entry_no' => '参赛号',
            'uid' => '关注者ID',
            'wantgo_status' => '想去状态，0想去，1取消想去',
            'update_time' => '更新时间',
            'create_time' => '创建时间',
        ];
    }
    /*更新时间*/
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
    
    /*获取用户信息*/
    public function getUserInfo()
    {
        return $this->hasOne(UserMaster::className(), ['uid' => 'uid']);
    }
    
    /*获取科目信息*/
    public function getActCourse()
    {
        return $this->hasOne(ActCourse::className(), ['courseid' => 'courseid']);
    }
    
    /*想去：数据库触发器更新act主表的相应信息*/
    public static function addWantgo($uid,$actid,$courseid,$entry_no='')
    {
        if (empty($actid))
        {
            $ActCourse = ActCourse::findOne(['courseid'=>$courseid]);
            $actid=$ActCourse->actid;
        }
        $IsWantgo=  static::findOne(['actid'=>$actid,'uid'=>$uid]);
        if(!empty($IsWantgo))
        {
            if($IsWantgo->wantgo_status==1)
            {
                $IsWantgo->wantgo_status=0;
                if ($entry_no)
                {
                    $IsWantgo->entry_no = $entry_no;
                }
                $IsWantgo->save();
                
                // 会去数加1
                self::updateActWantgoNum($actid, true);
            }
            else if ($entry_no)
            {
                $IsWantgo->entry_no = $entry_no;
                $IsWantgo->save();
            }
            return TRUE;
        }
        else
        {
            $AddWantgo=new ActWantgo();
            $AddWantgo->uid=$uid;
            $AddWantgo->actid=$actid;
            $AddWantgo->courseid = $courseid;
            $AddWantgo->entry_no = $entry_no;
            $AddWantgo->wantgo_status=0;
            $AddWantgo->save();
            
            // 会去数加1
            self::updateActWantgoNum($actid, true);
            return TRUE;
        }
        return FALSE;
    }
    /*取消想去：数据库触发器更新act主表的相应信息*/
    public static function cancelWantgo($uid,$actid)
    {
        $IsFollow=  static::findOne(['actid'=>$actid,'uid'=>$uid]);
        if(!empty($IsFollow))
        {
            $IsFollow->wantgo_status=1;
            $IsFollow->save();
            
            // 会去数减1
            self::updateActWantgoNum($actid, false);
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * 更新act的会去数量
     * @param int $act_id
     * @param boolean $wangtgo_flag 会去=true/不会去=false
     */
    public static function updateActWantgoNum($act_id, $wangtgo_flag)
    {
        $act = Act::findOne(['actid'=>$act_id]);
        if($wangtgo_flag)
        {
            // act会去数加1
            $act->wantgo_sum = $act->wantgo_sum + 1;
            $act->save(false);
        }
        else
        {
            // act会去数减1
            $follow_sum = $act->wantgo_sum;
            if ($follow_sum > 0)
            {
                $act->wantgo_sum = $act->wantgo_sum - 1;
                $act->save(false);
            }
        }
    }
    
    /*是否想去*/
    public static function isWantgo($uid,$actid)
    {
        $isWantgo=  static::findOne(['actid'=>$actid,'uid'=>$uid,'wantgo_status'=>0]);
        if($isWantgo)
        {
            return 1;
        }
        return 0;
    }
    
    /*获取赛事的会去用户信息*/
    public static function findWantgoUser($uid, $actid,$offset=0,$limit=20)
    {
//         return static::findBySql('select DISTINCT aw.uid from act_wantgo aw where aw.actid='.$actid.' and wantgo_status=0 order by case when aw.uid='.$uid.' then 1 else 2 end asc')
//                        ->orderBy(['aw.create_time'=>SORT_DESC])
//                        ->offset($offset)->limit($limit)
//                        ->all();
        
        return static::find()->where('actid='.$actid.' and wantgo_status=0')
                        ->orderBy(['`uid`='.$uid=>SORT_DESC,'create_time'=>SORT_DESC])
                        ->offset($offset)->limit($limit)
                        ->all();
    }
}
