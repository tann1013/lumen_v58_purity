<?php

namespace App\Http\Middleware;

use App\ConstDir\BaseConst;
use Closure;
use Auth;

class AuthToken
{
	public function handle($request, Closure $next)
	{
        $httpCode = Auth::check();
        // token认证失败，401无权限
        return $httpCode ? $next($request) : wmResponse()->code(401, '暂无权限，请联系客服人员开通!')->json();
	}
}
