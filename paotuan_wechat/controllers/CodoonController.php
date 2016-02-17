<?php
namespace paotuan_wechat\controllers;

use Yii;
use common\component\WechatSDK\Codoon;
use paotuan_wechat\component\PaotuanController;
use yii\base\Exception;
use paotuan_wechat\models\Mileage;
use paotuan_wechat\models\MileageAlbum;
use paotuan_wechat\component\Util;
use paotuan_wechat\models\RunBind;
use common\component\CustomHelper;

/**
 * Codoon controller
 * 
 * @author weihu
 */
class CodoonController extends PaotuanController
{

    protected $user = false;
    
    // 咕咚用户授权
    public function actionUser()
    {
        if (! $this->user) {
            $this->checkWechatOauth('/codoon/user?type=1');
        }
        $result = $this->checkoutCodoonOauth('/codoon/user');
        if ($result) {
            return $this->renderPartial('bind', []);
        }
    }
    
    // 自动同步咕咚打卡数据
    public function actionMileage()
    {
        $user_id = \Yii::$app->request->post('user_id');
        $resource_id = \Yii::$app->request->post('resource_id');
        try {
            $CodoonBind = RunBind::findOne(['user_id' => $user_id,'bind_type' => 1,'bind_status'=>1]);
            if ($CodoonBind) {
                $Codoon = Codoon::getDefaultInstance();
                $result = $Codoon->getOauthMileage($CodoonBind->access_token, $resource_id);
                if ($result && isset($result['status']) && $result['status']=='OK' && $result['data'] && $result['data']['sports_type']=='跑步') {
                    $result = $result['data'];
                    $Mileage = new Mileage();
                    $week = date('W', strtotime($result['start_time']));
                    $year = date('Y', strtotime($result['start_time']));
                    $Mileage->uid = $CodoonBind->uid;
                    $Mileage->mileage = sprintf("%.2f",$result['total_length']/1000);
                    if ($Mileage->mileage != 0)
                    {
                        $Mileage->mileage_week = $year . '-' . $week;
                        $Mileage->mileage_month = date('Y-m', strtotime($result['start_time']));
                        $Mileage->mileage_year = $year;
                        $Mileage->mileage_date = date("Y-m-d", strtotime($result['start_time']));
                        $Mileage->duration = $result['total_time'];
                        $Mileage->format_duration = Util::getTimeFromSec($Mileage->duration);
                        $Mileage->from = Mileage::FROM_TYPE_CODOON;
                        $r = ceil($Mileage->duration / $Mileage->mileage);
                        $Mileage->pace = Util::getTimeFromSec($r);
                        if (! $Mileage->save(false)) {
                            throw new Exception('打开记录保存失败');
                        }
                        
                        // 保存路线图
                        if ($result['route_image']) {
                            $MileageAlbum = new MileageAlbum();
                            $MileageAlbum->mileage_id = $Mileage->id;
                            $MileageAlbum->image_url = $result['route_image'];
                            if (! $MileageAlbum->save(false)) {
                                throw new Exception('保存路线图失败');
                            }
                        }
                        
                        //发送微信消息
                        $param = array(
                            'uid'=>$CodoonBind->uid,
                            'type'=>1,
                            'mileageid'=>$Mileage->id
                        );
                        $url = \Yii::$app->params['site_url'].'v3/wechat/mileage';
                        $result = CustomHelper::http_post($url, $param);
                        \Yii::info(json_encode($result));
                    }
                }
            }
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
        }
        return true;
    }
    
    // 咕咚授权验证
    private function checkoutCodoonOauth($oAuthUrl)
    {
        $code = \Yii::$app->request->get("code", "");
        $type = \Yii::$app->request->get('type', '');
        if (! empty($code) && ! $type) {
            return $this->getCodoonUser();
        } else {
            $oAuthUrl = '/codoon/user';
            $hostInfo = \Yii::$app->request->hostInfo;
            if ($hostInfo == "http://www.runningtogether.net") {
                $redirectUrl = "http://www.paobuqu.com/getCodeForTest.php?redirectUrl=" . $oAuthUrl;
            } else {
                $redirectUrl = $hostInfo . $oAuthUrl;
            }
            \Yii::$app->response->redirect(Codoon::getDefaultInstance()->getOauthRedirect($redirectUrl));
            \Yii::$app->end();
        }
    }
    
    // 获取咕咚用户信息
    private function getCodoonUser()
    {
        $code = \Yii::$app->request->get("code");
        if ($code) {
            $Codoon = Codoon::getDefaultInstance();
            $result = $Codoon->getOauthAccessToken($code);
            if ($result) {
                $access_token = $result['access_token'];
                // 获取咕咚用户信息
                $Codoon_User = $Codoon->getOauthUserinfo($access_token);
                $CodoonBind = RunBind::findOne([
                    'user_id' => $result['user_id'],
                    'bind_status'=>1,
                    'bind_type'=>1
                ]);
                if ($CodoonBind)
                {
                    $CodoonBind->bind_status = 0;
                    $CodoonBind->save(false);
                }
                $CodoonBind = RunBind::findOne([
                    'uid' => $this->user->uid,
                    'bind_type'=>1
                ]);
                if (!$CodoonBind)
                {
                    $CodoonBind = new RunBind();
                }
                $CodoonBind->uid = $this->user->uid;
                $CodoonBind->user_id = $result['user_id'];
                $CodoonBind->access_token = $access_token;
                $CodoonBind->bind_status = 1;
                if ($Codoon_User) {
                    $CodoonBind->nick_name = $Codoon_User['nick'];
                }
                $CodoonBind->bind_type = 1;
                return $CodoonBind->save(false);
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