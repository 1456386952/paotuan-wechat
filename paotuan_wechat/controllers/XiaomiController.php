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
use common\component\WechatSDK\Xiaomi;

/**
 * Xiaomi controller
 * 
 * @author weihu
 */
class XiaomiController extends PaotuanController
{

    protected $user = false;
    
    // 咕咚用户授权
    public function actionUser()
    {
        if ($this->user) {
            $result = $this->checkoutXiaomiOauth('/xiaomi/user');
            if ($result) {
                return $this->renderPartial('bind', []);
            }
        }
    }
    
    // 定时任务，获取小米当日跑步数据
    public function actionMileage()
    {
        try {
            $XiaomiBind = RunBind::findAll(['bind_type' => 4,'bind_status'=>1]);
            if ($XiaomiBind)
            {
                foreach ($XiaomiBind as $Bind)
                {
                    $param['mac_key'] = $Bind->mac_key;
                    $param['access_token'] = $Bind->access_token;
                    $param['fromdate'] = date("Y-m-d",strtotime("-1 day"));
                    $param['todate'] = date('Y-m-d');
                    $Xiaomi = Xiaomi::getDefaultInstance();
                    $result = $Xiaomi->getXiaomiData($param);
                    if ($result && $result['code']==1)
                    {
                        $result_data = $result['data'];
                        if ($result_data)
                        {
                            foreach ($result_data as $data)
                            {
                                $Mileage = new Mileage();
                                $week = date('W', strtotime($data['date']));
                                $year = date('Y', strtotime($data['date']));
                                $Mileage->uid = $Bind->uid;
                                $Mileage->mileage = sprintf("%.2f",$data['runDistance']/1000);
                                if ($Mileage->mileage != 0)
                                {
                                    $Mileage->mileage_week = $year . '-' . $week;
                                    $Mileage->mileage_month = date('Y-m', strtotime($data['date']));
                                    $Mileage->mileage_year = $year;
                                    $Mileage->mileage_date = date("Y-m-d", strtotime($data['date']));
                                    $Mileage->duration = $data['runTime']*60;
                                    $Mileage->format_duration = Util::getTimeFromSec($Mileage->duration);
                                    $Mileage->from = Mileage::FROM_TYPE_XIAOMI;
                                    $r = ceil($Mileage->duration / $Mileage->mileage);
                                    $Mileage->pace = Util::getTimeFromSec($r);
                                    if (! $Mileage->save(false)) {
                                        throw new Exception('打开记录保存失败');
                                    }
                                
                                    //发送微信消息
                                    $param = array(
                                        'uid'=>$Bind->uid,
                                        'type'=>4,
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
            }
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
        }
        return 'success';
    }
    
    // 益动授权验证
    private function checkoutXiaomiOauth($oAuthUrl)
    {
        $code = \Yii::$app->request->get("code", "");
//         $type = \Yii::$app->request->get('type', '');
        if ($code) {
            return $this->getXiaomiUser();
        } else {
            $oAuthUrl = '/xiaomi/user';
            $hostInfo = \Yii::$app->request->hostInfo;
            if ($hostInfo == "http://www.runningtogether.net") {
                $redirectUrl = "http://www.paobuqu.com/getCodeForTest.php?redirectUrl=" . $oAuthUrl;
            } else {
                $redirectUrl = $hostInfo . $oAuthUrl;
            }
            Xiaomi::getDefaultInstance()->getOauthRedirect($redirectUrl);
            \Yii::$app->end();
        }
    }
    
    // 获取咕咚用户信息
    private function getXiaomiUser()
    {
        $code = \Yii::$app->request->get("code");
        if ($code) {
            $Xiaomi = Xiaomi::getDefaultInstance();
            $result = $Xiaomi->getOauthAccessToken($code);
            if ($result) {
                $access_token = $result['access_token'];
                $macKey = $result['mac_key'];
                // 获取小米用户信息
                $Xiaomi_User = $Xiaomi->getOauthUserinfo($access_token,$macKey);
                if ($Xiaomi_User && $Xiaomi_User['result']='ok')
                {
                    $Xiaomi_User = $Xiaomi_User['data'];
                    $XiaomiBind = RunBind::findOne([
                        'user_id' => $Xiaomi_User['userId'],
                        'bind_status'=>1,
                        'bind_type'=>4
                    ]);
                    if ($XiaomiBind)
                    {
                        $XiaomiBind->bind_status = 0;
                        $XiaomiBind->save(false);
                    }
                    $XiaomiBind = RunBind::findOne([
                        'uid' => $this->user->uid,
                        'bind_type'=>4
                    ]);
                    if (!$XiaomiBind)
                    {
                        $XiaomiBind = new RunBind();
                    }
                    $XiaomiBind->uid = $this->user->uid;
                    $XiaomiBind->user_id = $Xiaomi_User['userId'];
                    $XiaomiBind->access_token = $access_token;
                    $XiaomiBind->mac_key = $macKey;
                    $XiaomiBind->bind_status = 1;
                    if ($Xiaomi_User) {
                        $XiaomiBind->nick_name = $Xiaomi_User['miliaoNick'];
                    }
                    $XiaomiBind->bind_type = 4;
                    return $XiaomiBind->save(false);
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