<?php
namespace paotuan_wechat\controllers;

use Yii;
use paotuan_wechat\component\PaotuanController;
use yii\base\Exception;
use paotuan_wechat\models\Mileage;
use common\component\WechatSDK\Hupu;
use paotuan_wechat\component\Util;
use paotuan_wechat\models\RunBind;
use common\component\CustomHelper;

/**
 * Hupu controller
 * 
 * @author weihu
 */
class HupuController extends PaotuanController
{

    protected $user = false;
    
    // 虎扑用户授权
    public function actionUser()
    {
        if (! $this->user) {
            $this->checkWechatOauth('/hupu/user?type=1');
        }
        $result = $this->checkoutHupuOauth('/hupu/user');
        if ($result) {
            return $this->renderPartial('bind', []);
        }
    }
    
    // 自动同步虎扑打卡数据
    public function actionMileage()
    {
        $user_id = \Yii::$app->request->get('openid');
        $mileage = \Yii::$app->request->get('mileage');
        $duration = \Yii::$app->request->get('elapsedtime');
        $time = \Yii::$app->request->get('time');
        try {
            $hupuBind = RunBind::findOne([
                'user_id' => $user_id,
                'bind_type' => 2,
                'bind_status' => 1
            ]);
            if ($hupuBind) {
                $Mileage = new Mileage();
                $week = date('W', strtotime($time));
                $year = date('Y', strtotime($time));
                $Mileage->uid = $hupuBind->uid;
                $Mileage->mileage = sprintf("%.2f",$mileage/1000);
                $Mileage->mileage_week = $year . '-' . $week;
                $Mileage->mileage_month = date('Y-m', strtotime($time));
                $Mileage->mileage_year = $year;
                $Mileage->mileage_date = date("Y-m-d", strtotime($time));
                $Mileage->duration = $duration;
                $Mileage->format_duration = Util::getTimeFromSec($Mileage->duration);
                $Mileage->from = Mileage::FROM_TYPE_HUPU;
                $r = ceil($Mileage->duration / $Mileage->mileage);
                $Mileage->pace = Util::getTimeFromSec($r);
                if (! $Mileage->save(false)) {
                    throw new Exception('打开记录保存失败');
                }
                
                //发送微信消息
                $param = array(
                    'uid'=>$CodoonBind->uid,
                    'type'=>2,
                    'mileageid'=>$Mileage->id
                );
                $url = \Yii::$app->params['site_url'].'v3/wechat/mileage';
                $result = CustomHelper::http_post($url, $param);
                \Yii::info(json_encode($result));
            }
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
        }
        return true;
    }
    
    // 虎扑授权验证
    private function checkoutHupuOauth($oAuthUrl)
    {
        $code = \Yii::$app->request->get("code", "");
        $type = \Yii::$app->request->get('type', '');
        if (! empty($code) && ! $type) {
            return $this->getHupuUser();
        } else {
            $oAuthUrl = '/hupu/user';
            $hostInfo = \Yii::$app->request->hostInfo;
            if ($hostInfo == "http://www.runningtogether.net") {
                $redirectUrl = "http://www.paobuqu.com/getCodeForTest.php?redirectUrl=" . $oAuthUrl;
            } else {
                $redirectUrl = $hostInfo . $oAuthUrl;
            }
            \Yii::$app->response->redirect(Hupu::getDefaultInstance()->getOauthRedirect($redirectUrl));
            \Yii::$app->end();
        }
    }
    
    // 获取虎扑用户信息
    private function getHupuUser()
    {
        $code = \Yii::$app->request->get("code");
        if ($code) {
            $Hupu = Hupu::getDefaultInstance();
            $result = $Hupu->getOauthAccessToken($code);
            if ($result) {
                $access_token = $result['access_token'];
                $openid = $result['openid'];
                // 获取咕咚用户信息
                $Hupu_User = $Hupu->getOauthUserinfo($access_token,$openid);
                $HupuBind = RunBind::findOne([
                    'user_id' => $result['openid'],
                    'bind_status' => 1
                ]);
                if (! $HupuBind) {
                    $HupuBind = RunBind::findOne([
                        'user_id' => $result['openid'],
                        'bind_status' => 0
                    ]);
                    if ($HupuBind)
                    {
                        $HupuBind->bind_status = 1;
                    }
                    else 
                    {
                        $HupuBind = new RunBind();
                        $HupuBind->uid = $this->user->uid;
                        $HupuBind->user_id = $result['openid'];
                    }
                }
                $HupuBind->access_token = $access_token;
                if ($Hupu_User) {
                    $CodoonBind->nick_name = $Hupu_User['nickname'];
                }
                $HupuBind->bind_type = 2;
                return $HupuBind->save(false);
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