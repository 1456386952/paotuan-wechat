<?php
namespace common\component;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use yii\filters\auth\QueryParamAuth;
/**
 * Description of SubHttpBearerAuth
 *
 * @author wubaoxin
 */
class SubQueryParamAuth extends QueryParamAuth {
    //put your code here
    public function authenticate($user, $request, $response)
    {
        $accessToken = $request->getHeaders()->get($this->tokenParam);
        $accessToken = !empty($accessToken)? $accessToken : $request->get($this->tokenParam);
        if (is_string($accessToken)) {
            $identity = $user->loginByAccessToken($accessToken, get_class($this));
            if ($identity !== null) {
                return $identity;
            }
        }
        if ($accessToken !== null) {
            $this->handleFailure($response);
        }
        
        return null;
    }
}
