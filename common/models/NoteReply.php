<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "note_reply".
 * 
 * @property integer $replyid
 * @property integer $uid
 * @property string $noteid
 * @property string $reply_body
 * @property integer $reply_status
 * @property string $update_time
 * @property string $create_time
 */
class NoteReply extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'note_reply';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['replyid', 'uid', 'noteid', 'reply_status'], 'integer'],
            [['update_time', 'create_time'], 'safe'],
            [['reply_body'], 'string', 'max' => 320],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'replyid' => '回帖id',
            'uid' => '回帖用户id',
            'noteid' => '帖子id',
            'reply_body' => '回帖内容',
            'reply_status' => '回帖状态（0正常，1发帖人删除，2系统屏蔽）',
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
