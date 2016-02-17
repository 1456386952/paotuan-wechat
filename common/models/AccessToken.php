<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;
use common\component\HashCreate;
use common\models\UserInfo;
use common\models\UserMaster;

/**
 * This is the model class for table "access_token".
 *
 * @property integer $uid
 * @property string $access_token
 * @property string $create_time
 * @property integer $update_time
 * @property integer $expiretime
 * @property integer $login_type
 */
class AccessToken extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'access_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'required'],
            [['uid',  'expiretime', 'login_type'], 'integer'],
            [['update_time','create_time'], 'safe'],
            [['access_token'], 'string', 'max' => 255],
            [['access_token'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => '用户ID',
            'access_token' => '临时访问密钥',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'expiretime' => '过期时间',
            'login_type' => '登录类型',
        ];
    }
    
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            $this->update_time=new Expression('NOW()');
            $this->expiretime = time() + \Yii::$app->params['token_expire_time'];
            if($this->isNewRecord){
                $OldAccess=  self::findOne(['uid'=>$this->uid]);
                if($OldAccess)
                {
                    $OldAccess->delete();
                }
                $this->access_token = HashCreate::create();
                $this->create_time=new Expression('NOW()');
            }   
            return true;
        }
        return false;
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $AccessToken=AccessToken::find()
                ->where('access_token=:token AND expiretime > :expire',[':token'=>$token,':expire'=>time()])
                ->one();
        
        if($AccessToken){
            //延长access_token有效访问时间
            $AccessToken->save();
        }
        return $AccessToken;
    }
    
    public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['uid' => 'uid']);
    }
    
    public function getUser()
    {
        return $this->hasOne(UserMaster::className(), ['uid' => 'uid']);
    }

    public function getAuthKey() {
        return $this->user->auth_key;
    }

    public function getId() {
        return $this->uid;
    }

    public function getUsername() {
        return $this->user->user_name;
    }

    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    public static function findIdentity($id) {
        $user = UserMaster::findOne(['uid' => $id, 'user_status' => self::STATUS_ACTIVE]);
        if($user){
            return self::findOne(['uid' => $id]);
        }
        return false;
    }
}
