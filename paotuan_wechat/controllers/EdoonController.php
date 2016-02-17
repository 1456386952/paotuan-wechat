<?php
namespace paotuan_wechat\controllers;

use Yii;
use paotuan_wechat\component\PaotuanController;
use yii\base\Exception;
use paotuan_wechat\models\Mileage;
use paotuan_wechat\models\MileageAlbum;
use paotuan_wechat\component\Util;
use paotuan_wechat\models\RunBind;
use common\component\CustomHelper;
use common\component\WechatSDK\Edoon;

/**
 * Edoon controller
 * 
 * @author weihu
 */
class EdoonController extends PaotuanController
{

    protected $user = false;
    
    // 咕咚用户授权
    public function actionUser()
    {
        if (! $this->user) {
            $this->checkWechatOauth('/edoon/user?type=1');
        }
        $result = $this->checkoutEdoonOauth('/edoon/user');
        if ($result) {
            return $this->renderPartial('bind', []);
        }
    }
    
    // 自动同步益动打卡数据
    public function actionMileage()
    {
        $result = json_decode(\Yii::$app->request->post('data'));
        $user_id = $result->user_id;
        $locations = $result->locations;
        try {
            $EdoonBind = RunBind::findOne(['user_id' => $user_id,'bind_type' => 3,'bind_status'=>1]);
            if ($EdoonBind) {
                if ($locations) {
                    foreach ($locations as $location)
                    {
                        if ($location->sportType==0)
                        {
                            $Mileage = new Mileage();
                            $week = date('W', strtotime($location->startTime));
                            $year = date('Y', strtotime($location->startTime));
                            $Mileage->uid = $EdoonBind->uid;
                            $Mileage->mileage = sprintf("%.2f",$location->distance/1000);
                            if ($Mileage->mileage != 0)
                            {
                                $Mileage->mileage_week = $year . '-' . $week;
                                $Mileage->mileage_month = date('Y-m', strtotime($location->startTime));
                                $Mileage->mileage_year = $year;
                                $Mileage->mileage_date = date("Y-m-d", strtotime($location->startTime));
                                $Mileage->duration = strtotime($location->endTime)-strtotime($location->startTime);
                                $Mileage->format_duration = Util::getTimeFromSec($Mileage->duration);
                                $Mileage->from = Mileage::FROM_TYPE_EDOON;
                                $r = ceil($Mileage->duration / $Mileage->mileage);
                                $Mileage->pace = Util::getTimeFromSec($r);
                                if (! $Mileage->save(false)) {
                                    throw new Exception('打开记录保存失败');
                                }
                                
                                // 保存路线图
                                if ($location->location) {
                                    $MileageAlbum = new MileageAlbum();
                                    $MileageAlbum->mileage_id = $Mileage->id;
                                    $MileageAlbum->image_url = $location->location;
                                    if (! $MileageAlbum->save(false)) {
                                        throw new Exception('保存路线图失败');
                                    }
                                }
                                
                                //发送微信消息
                                $param = array(
                                    'uid'=>$EdoonBind->uid,
                                    'type'=>3,
                                    'mileageid'=>$Mileage->id
                                );
                                $url = \Yii::$app->params['site_url'].'v3/wechat/mileage';
                                $result = CustomHelper::http_post($url, $param);
                                \Yii::info(json_encode($result));
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
        }
        return 'success';
    }
    
    // 益动授权验证
    private function checkoutEdoonOauth($oAuthUrl)
    {
        $code = \Yii::$app->request->get("code", "");
        $type = \Yii::$app->request->get('type', '');
        if (! empty($code) && ! $type) {
            return $this->getEdoonUser();
        } else {
            $oAuthUrl = '/edoon/user';
            $hostInfo = \Yii::$app->request->hostInfo;
            if ($hostInfo == "http://www.runningtogether.net") {
                $redirectUrl = "http://www.paobuqu.com/getCodeForTest.php?redirectUrl=" . $oAuthUrl;
            } else {
                $redirectUrl = $hostInfo . $oAuthUrl;
            }
            Edoon::getDefaultInstance()->getOauthRedirect($redirectUrl);
            \Yii::$app->end();
        }
    }
    
    // 获取咕咚用户信息
    private function getEdoonUser()
    {
        $code = \Yii::$app->request->get("code");
        if ($code) {
            $Edoon = Edoon::getDefaultInstance();
            $result = $Edoon->getOauthAccessToken($code);
            if ($result) {
                $access_token = $result['access_token'];
                // 获取益动用户信息
                $Edoon_User = $Edoon->getOauthUserinfo($access_token);
                if ($Edoon_User && $Edoon_User['user_id'])
                {
                    $EdoonBind = RunBind::findOne([
                        'user_id' => $Edoon_User['user_id'],
                        'bind_status'=>1,
                        'bind_type'=>3
                    ]);
                    if ($EdoonBind)
                    {
                        $EdoonBind->bind_status = 0;
                        $EdoonBind->save(false);
                    }
                    $EdoonBind = RunBind::findOne([
                        'uid' => $this->user->uid,
                        'bind_type'=>3
                    ]);
                    if (!$EdoonBind)
                    {
                        $EdoonBind = new RunBind();
                    }
                    $EdoonBind->uid = $this->user->uid;
                    $EdoonBind->user_id = $Edoon_User['user_id'];
                    $EdoonBind->access_token = $access_token;
                    $EdoonBind->bind_status = 1;
                    if ($Edoon_User) {
                        $EdoonBind->nick_name = $Edoon_User['nickname'];
                    }
                    $EdoonBind->bind_type = 3;
                    return $EdoonBind->save(false);
                }
            }
        }
        return false;
    }
    
    // 授权跳转页面
    public function actionRedirect()
    {
        $redirectUrl = str_replace("$", "#", \Yii::$app->request->get("redirectUrl"));
        $redirectUrl = str_replace("@", "&", $redirectUrl);
        return $this->renderPartial("redirect", [
            "redirectUrl" => $redirectUrl
        ]);
    }
}