<?php

namespace common\models;

use Yii;
use common\models\UserInfo;
use yii\db\Expression;

/**
 * This is the model class for table "user_paper".
 *
 * @property integer $paperid
 * @property integer $uid
 * @property string $paper_url
 * @property integer $paper_type
 * @property integer $paper_status
 * @property string $create_time
 */
class UserPaper extends \yii\db\ActiveRecord
{
	const  PAPER_TYPE_ID_COPY=5;
	const  PAPER_TYPE_HEALTH=2;
	const  PAPER_TYPE_COMPLETE=3;
	const  PAPER_TYPE_BAG=4;
	const  PAPER_TYPE_ID_COPY_BACK=6;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_paper';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'paper_type'], 'required'],
            [['uid', 'paper_type', 'paper_status'], 'integer'],
            [['create_time'], 'safe'],
            [['paper_url'], 'string', 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'paperid' => '资料ID',
            'uid' => '用户ID',
            'paper_url' => '资料存储路径',
            'paper_type' => '资料类型：1证件照，2体检，3完赛证明',
            'paper_status' => '0正常，1删除',
            'create_time' => '创建时间',
        ];
    }  
    
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($this->isNewRecord){
                $this->create_time=new Expression('NOW()');
            }   
            return true;
        }
        return false;
    }
    
    /*添加图片*/
    public static function addPaper($params,$mothed)
    {
        $User= UserInfo::findOne(['uid'=>$params['uid']]);
        switch ($mothed)
        {
            case 'idcopy':
                $type=self::PAPER_TYPE_ID_COPY;
                $User->has_id_copy=1;
                break;
             case 'idcopyback':
                	$type=self::PAPER_TYPE_ID_COPY_BACK;
                	$User->has_id_copy=1;
                	break;
            case 'report':
                $User->has_medical_report=1;
                $type=2;
                break;
            case 'cert':
                $User->has_cert=1;
                $type=3;
                break;
        }
        $Paper=new self();
        $Paper->paper_type=$type;
        $Paper->paper_url=$params['url'];
        $Paper->uid=$params['uid'];
        if($Paper->save())
        {
           $User->save();
        }
        return $Paper;
    }
}
