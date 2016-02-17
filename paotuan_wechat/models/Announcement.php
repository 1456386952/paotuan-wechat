<?php

namespace paotuan_wechat\models;

use Yii;
use common\models\UserMaster;

/**
 * This is the model class for table "announcement".
 *
 * @property string $id
 * @property string $uid
 * @property string $clubid
 * @property string $author
 * @property string $subject
 * @property string $content
 * @property string $create_time
 * @property string $views
 * @property string $coverurl
 * @property integer $status
 */
class Announcement extends \yii\db\ActiveRecord
{
	
	const STATUS_NORMAL=1;
	const STATUS_DEL=2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'announcement';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'clubid', 'create_time', 'views', 'status'], 'integer'],
            [['content'], 'required'],
            [['content'], 'string'],
            [['author'], 'string', 'max' => 50],
            [['subject'], 'string', 'max' => 100],
            [['coverurl'], 'string', 'max' => 150]
        ];
    }
    
    
    public function getUser()
    {
    	return $this->hasOne(UserMaster::className(), ['uid' => 'uid']);
    }
    
    public function getClub()
    {
    	return $this->hasOne(Club::className(), ['clubid' => 'clubid']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'clubid' => 'Clubid',
            'author' => 'Author',
            'subject' => 'Subject',
            'content' => 'Content',
            'create_time' => 'Create Time',
            'views' => 'Views',
            'coverurl' => 'Coverurl',
            'status' => 'Status',
        ];
    }
}
