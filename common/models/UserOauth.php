<?php

namespace common\models;

use Yii;
use common\models\UserMaster;
use common\models\UserInfoOld;
use common\models\Country;
use common\models\Province;
use common\models\City;
use yii\db\Expression;
use common\component\HashCreate;
use common\models\UserInfo;

/**
 * This is the model class for table "user_oauth".
 *
 * @property integer $oauth_id
 * @property string $oauth_openid
 * @property integer $oauth_type
 * @property string $oauth_nick
 * @property string $oauth_face
 * @property integer $uid
 * @property integer $oauth_status
 * @property string $last_login
 * @property string $create_time
 */
class UserOauth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_oauth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oauth_type', 'uid', 'last_login'], 'required'],
            [['oauth_type', 'uid', 'oauth_status'], 'integer'],
            [['last_login', 'create_time', 'create_time'], 'safe'],
            [['oauth_openid', 'oauth_face'], 'string', 'max' => 120],
            [['oauth_nick'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'oauth_id' => '主键',
            'oauth_openid' => '第三方登陆openid',
            'oauth_type' => '第三方类型：1微信',
            'oauth_nick' => '第三方昵称',
            'oauth_face' => '第三方头像',
            'uid' => '绑定的用户UID',
            'oauth_status' => '是否激活',
            'last_login' => '最后登录时间',
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
    
    public static function getByOpenid($openid){
    	return self::findOne(["oauth_openid"=>$openid,"oauth_type"=>2]);
    }
    
    public function getUser()
    {
        return $this->hasOne(UserMaster::className(), ['uid' => 'uid']);
    }
    
    /*第三方用户登录*/
    public static function login($oauther,$oauth_type=1)
    {
        $result=[];
        if(isset($oauther['auth_key'])){
            $UO=UserMaster::findOne(['auth_key'=>$oauther['auth_key']]);
            if(!$UO)
            {
                throw new Exception('用户信息不存在！',0);
            }
            $result=['uid'=>$UO->uid,'auth_key'=>$UO->auth_key];
        }
        else
        {
            $UO=  self::findOne(
                ['oauth_type'=>$oauth_type,'oauth_openid'=>$oauther['openid']]
            );
            if(!$UO)
            {
                //生成注册用户，处理常住地
                $User=new UserMaster();
                $User->nick_name=$oauther['nickname'];
                $countryid =  Country::FindCountryId($oauther['country']);
                $User->countryid = $countryid;
                $provinceid = Province::FindProvinceId($oauther['province'], $countryid);
                $User->provinceid = $provinceid;
                $cityid = City::FindCityId($oauther['city'], $countryid, $provinceid);
                $User->cityid=$cityid;
                $User->user_gender=(int)$oauther['sex'];
                $User->user_face=$oauther['headimgurl'];
                $User->create_source=(string)$oauth_type;
                if($User->save()){
                    $UO = new self();
                    $UO->uid = $User->uid;
                    $UO->oauth_openid = $oauther['openid'];
                    $UO->oauth_nick = $oauther['nickname'];
                    $UO->oauth_status = 1;
                    $UO->oauth_type=$oauth_type;
                    /*根据openid查找用户旧数据，更新进user_info*/
                    $Uinfo=new UserInfo();
                    $Uinfo->uid=$User->uid;
                    $Uinfo->user_gender=(int)$oauther['sex'];
                    $Uinfo->save();
                }
            }
            //更新最后登陆时间
            $UO->last_login = new Expression('NOW()');
            $UO->save();
            $result=['uid'=>$UO->uid];
        }
        return $result;
    }
    
}
