<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;
use common\component\HashCreate;
use common\models\City;
use common\models\UserInfo;

/**
 * This is the model class for table "user".
 *
 * @property integer $uid
 * @property string $nick_name
 * @property integer $country_id
 * @property integer $provinceid
 * @property integer $cityid
 * @property string $user_face
 * @property integer $user_gender
 * @property string $user_email
 * @property integer $is_bind_email
 * @property string $user_cell
 * @property integer $is_bind_cell
 * @property string $create_source
 * @property integer $user_status
 * @property string $closure_start
 * @property string $closure_end
 * @property string $auth_key
 * @property string $user_name
 * @property string $password
 * @property string $update_time
 * @property string $create_time
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 0;
    const ROLE_USER = 10;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['countryid', 'provinceid', 'cityid', 'user_gender', 'is_bind_email', 'is_bind_cell', 'user_status'], 'integer'],
            [['closure_start', 'closure_end', 'update_time', 'create_time'], 'safe'],
            [['update_time', 'create_time'], 'required'],
            [['nick_name', 'user_name'], 'string', 'max' => 60],
            [['user_face', 'user_email', 'auth_key', 'password'], 'string', 'max' => 240],
            [['user_cell'], 'string', 'max' => 16],
            [['create_source'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => '用户UID',
            'nick_name' => '用户昵称',
            'countryid' => '用户国家或地区',
            'provinceid' => '用户省份',
            'cityid' => '用户城市',
            'user_face' => '用户头像',
            'user_gender' => '用户性别',
            'user_email' => '用户绑定邮箱',
            'is_bind_email' => '是否绑定邮箱',
            'user_cell' => '用户手机',
            'is_bind_cell' => '是否绑定手机',
            'create_source' => '用户来源',
            'user_status' => '用户状态',
            'closure_start' => '用户封禁开始时间',
            'closure_end' => '用户封禁结束时间',
            'auth_key' => '永久访问密钥',
            'user_name' => '用户名',
            'password' => '密码',
            'update_time' => '用户最后更新时间',
            'create_time' => '用户创建时间',
        ];
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->update_time = new Expression('NOW()');
            if ($this->isNewRecord) {
                $this->auth_key = HashCreate::create();
                $this->create_time = new Expression('NOW()');
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
            //延长access_token有效访问时间,24分钟
            $AccessToken->expiretime=time() + \Yii::$app->params['token_expire_time'];
            $AccessToken->save();
        }
        return $AccessToken;
    }
    
    public function getCity()
    {
        return $this->hasOne(City::className(), ['cityid' => 'cityid']);
    }
    
    public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['uid' => 'uid']);
    }
    
    public static function getUserAll($uid)
    {
        $Result = static::findOne(['uid'=>$uid])
                ->with('city','userInfo')
                ->all();
        return $Result;
    }

    public function getAuthKey() {
        return $this->auth_key;
    }

    public function getId() {
        return $this->uid;
    }

    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    public static function findIdentity($id) {
        return static::findOne(['uid' => $id, 'user_status' => self::STATUS_ACTIVE]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['user_name' => $username, 'user_status' => self::STATUS_ACTIVE]);
    }
}
