<?php

namespace App\Providers;

use AlibabaCloud\Airec\V20181012\DeleteDataSet;
use App\Libraries\DataCrypto;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    const LSJ_TOKEN = "TBt8VRYICHR2BR0TeidYVWN5ZtSqutmRjd*ogRYeGwkpVlhdURZdXy8mZggVBQMABQoDBg0HC3dkGBNyQTRHcwgnZggVAVZVUQwCAAUfDiF/AhwFBHFRHXgicVQaAVMJUg9WVQBTW3B1Fh0TdzNHQgINJV9SFgoS0aSY1IOs3P/83J6X09KA1ezToaex0bqR0aW82K2i3MHq0b6JFmoXeRFhfhAGDAMeBgsMHwYDCGpzBB0AB38bAXh1agMFDB4IBxUEAAwcCHN*GgAEGn8DHHB0dhwGAh4BBgsbAAAGG2hkeF5WXShhWSwmZggGAQYCBg4ABQULFWYPR3lUVSJERSAxMFdFRxIKBhUXdV1cXhYpWFR4UDUXCmNwdgQEAQkBAAAXHRZ2UCohZl5dUQhUXSQwZggV0rqw0aWa1Yyh3NXeFkw@";
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        $this->app['auth']->viaRequest('api', function ($request) {
            $requestToken = $request->header('token');
            if ($requestToken) {
                /**
                 *  1.验证是否为有效Bos用户
                 */
                // 1.1 测试
                if($requestToken=='test333'){
                    return true;
                }
                // 1.2 正式
                // 1.2 验证权限(1是否有效用户 2 是否有天眼权限) 并返回用户信息
                $userInfo = $this->_checkIsBosUser($requestToken);
                return $userInfo;
            }
        });
    }

    /**
     * 判断是否有效Bos用户
     * @param $token
     * @return mixed
     */
    private function _checkIsBosUser($token){
        //return true;
        $dataCryptoObj = new DataCrypto();
        $resToken = $dataCryptoObj->SimpleDecrypBosToken($token);
        $userInfo = json_decode($resToken, TRUE);

        if(isset($userInfo['Id']) && $userInfo['Id']>0){
            //获得linkToken
            return $userInfo;
            //$linkToken = $dataCryptoObj->sysGetLinkToken();
            //if($this->_chekIsHasTianyanRight($userInfo['Id'], $linkToken)){
                //return $userInfo;
            //}
        }
    }

    /**
     * 判断用户是否有天眼权限点
     * @param $userId
     * @return bool
     */
    private function _chekIsHasTianyanRight($userId, $linkToken){
        $st = false;
        //秘钥
        $rds = Redis::connection();
        $tyRigntPoint = '0900';//天眼#0900
        $_key = 'BC:Permisssion:Bos:AdminId:'.$userId;
        $_val = $rds->get($_key);
        if(is_null($_val)){
            //1 初始化缓存
            //$url = 'http://beta.api.cementgo.com/Manage/Trade/api/System/Inside_InitCurUserPower';
            $url = trim(env('LINK_TOKEN_REQUEST_URL'));
            $params = array(
                'Id' => $userId,
                'Token' => $linkToken,
            );

            $header = [
                'Token' => $linkToken
            ];

            $response = $this->_curlPost($url, $params, $header);
            //2 再次查询
            $_val = $rds->get($_key);
        }
        $pointList = json_decode($_val);
        if(is_array($pointList) && in_array($tyRigntPoint, $pointList)){
            $st = true;
        }
        return $st;
    }

    /**
     * @param $url
     * @param $params
     * @return mixed
     */
    private function _curlPost($url, $params, $hearder){
        //配置信息
        //$url = $_config['baseUrl'].$url;
        $postStr = json_encode($params);//转出json字符串
        $requestHeader = array(
            'Token:' . $hearder['Token'],
            //'X-Tsign-Open-App-Id:' . $_config['appId'],
            //'X-Tsign-Open-App-Secret:' . $_config['appSecret'],
            'Content-Type:' . 'application/json'
        );
        /**
         * curl
         */
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HEADER, false); // 输出HTTP头 true
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postStr);// post传输数据
        curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeader);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }

}
