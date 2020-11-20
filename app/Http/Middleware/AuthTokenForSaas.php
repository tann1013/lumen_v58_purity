<?php

namespace App\Http\Middleware;

use App\ConstDir\BaseConst;
use Closure;
use Auth;

class AuthTokenForSaas
{
	public function handle($request, Closure $next)
	{
        $httpCode = Auth::check();
        // token认证失败，401无权限
        if($httpCode){

            //验证是否为司机身份
            $users = \Illuminate\Support\Facades\Auth::user();

            if(!(isset($users['Id']) && $users['Id']>0)){
                return  wmResponse()->code(401, '暂无SAAS权限，请联系客服人员开通!')->json();
            }else{
                return $next($request);
            }

        }else{
            return  wmResponse()->code(401, '暂无SAAS权限，请联系客服人员开通!')->json();
        }
	}
}
