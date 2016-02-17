<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use common\component\CustomHelper;

/**
 * This is the model class for table "note".
 * 
 * @property integer $noteid
 * @property integer $uid
 * @property integet $actid
 * @property integer $courseid
 * @property integer $place_gender
 * @property string $note_fee
 * @property string $note_title
 * @property string $note_body
 * @property integer $note_power
 * @property string $note_pic_urls
 * @property integer $note_type
 * @property integer $note_status
 * @property integer $userhits
 * @property string $update_time
 * @property string $create_time
 */
class Note extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'note';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['noteid', 'uid', 'actid', 'courseid', 'place_gender', 'note_power', 'note_type', 'note_status','userhits'], 'integer'],
            [['update_time', 'create_time'], 'safe'],
            [['note_title'], 'string', 'max' => 20],
            [['note_body'], 'string', 'max' => 225],
            [['note_fee'], 'number'],
            [['note_pic_urls'], 'string', 'max' => 320],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'noteid' => '帖子id',
            'uid' => '发帖人id',
            'actid' => '活动ID',
            'courseid' => '科目ID',
            'place_gender' => '名额的男女类型（1 男，2 女）',
            'note_fee' => '名额转让价格',
            'note_title' => '帖子标题',
            'note_body' => '帖子内容',
            'note_power' => '帖子权限：1,正常2,置顶',
            'note_pic_urls' => '帖子图片url（最多三张，以逗号隔开）',
            'note_type' => '帖子类型（0二手名额转让，1我要二手名额）',
            'note_status' => '帖子状态0正常交流贴，1发帖人设置关闭交流，2发帖人删除，9系统屏蔽',
            'userhits' => '看过的用户数量',
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
    
    /*获取回复帖子*/
    public function getNotereply(){
        return $this->hasMany(NoteReply::className(), ['noteid'=>'noteid'])
                    ->where('reply_status=0')
                    ->orderBy('create_time');
    }
    
    /*获取科目信息*/
    public function getActcourse(){
        return $this->hasOne(ActCourse::className(), ['courseid'=>'courseid']);
    }
    
    /*获取转让名额列表信息*/
    public static function findNoteList($actid,$uid='',$note_type=0)
    {
        if ($uid)
        {
            return static::find()->where("actid=".$actid." and note_type=".$note_type)
                                 ->orderBy(['`uid`='.$uid=>SORT_DESC,'note_status'=>SORT_ASC,'create_time'=>SORT_ASC])
                                 ->with('notereply','actcourse')
                                 ->all();
        }
        else
        {
            return static::find()->where("actid=".$actid." and note_type=".$note_type)
                                 ->orderBy(['note_status'=>SORT_ASC,'create_time'=>SORT_ASC])
                                 ->with('notereply','actcourse')
                                 ->all();
        }
    }
    
    /*返回名额转让列表*/
    public static function formatNote($NoteArr,$uid)
    {
        $note_arr = array();
        if ($NoteArr)
        {
            foreach ($NoteArr as $note)
            {
                $note_data = [];
                $note_data['noteid'] = $note->noteid;
                /*用户信息*/                
                $note_data['uid'] = $note->uid;
                $UserMaster = UserMaster::findOne(['uid'=>$note->uid]);
                $note_data['nick_name'] = '';
                $note_data['user_face'] = CustomHelper::CreateImageUrl('http://xiaoi.b0.upaiyun.com/face/f/d/userface_rect.jpg', 'img_logo');
                if ($UserMaster)
                {
                    $note_data['nick_name'] = $UserMaster->nick_name;
                    if ($UserMaster->user_face)
                    {
                        $note_data['user_face'] = CustomHelper::CreateImageUrl($UserMaster->user_face, 'img_logo');
                    }
                }
                /*赛事科目信息*/
                $ActCourse = $note->actcourse;
                $note_data['course_name'] = '';
                $note_data['course_mileage'] = '';
                if ($ActCourse)
                {
                    $note_data['course_name'] = $ActCourse->course_name;
                    $note_data['course_mileage'] = round($ActCourse->course_mileage);
                }
                /*名额转让信息*/
                $note_data['place_gender'] = $note->place_gender?$note->place_gender:'';
                $note_data['note_fee'] = $note->note_fee?$note->note_fee:0;
                $note_data['note_status'] = $note->note_status;
                $note_data['note_type'] = $note->note_type;
                
                /*是否是自己发的帖*/
                $is_self = 0;
                if ($uid == $note->uid)
                {
                    $is_self = 1;
                }
                $note_data['is_self'] = $is_self;
                //记入日志
                \Yii::info('————————是否是个人发帖：'.$uid.'__'.$note->uid);
                
                /*回复信息*/
                $note_reply = $note->notereply;
                $reply_arr = array();
                if ($note_reply)
                {
                    foreach ($note_reply as $reply)
                    {
                        $reply_data['uid'] = $reply->uid;
                        $UserMaster = UserMaster::findOne(['uid'=>$reply->uid]);
                        $reply_data['nick_name'] = '';
                        $reply_data['user_face'] = '';
                        if ($UserMaster)
                        {
                            $reply_data['nick_name'] = $UserMaster->nick_name;
                            if ($UserMaster->user_face)
                            {
                                $reply_data['user_face'] = CustomHelper::CreateImageUrl($UserMaster->user_face, 'img_logo');
                            }
                        }
                        $reply_data['reply_body'] = $reply->reply_body;
                        $reply_data['create_time'] = $reply->create_time;
                        array_push($reply_arr, $reply_data);
                    }
                }
                $note_data['reply_arr'] = $reply_arr;
                array_push($note_arr, $note_data);
            }
        }
        return $note_arr;
    }
    
}
