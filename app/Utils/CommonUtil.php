<?php

namespace App\Utils;

use App\ConstDir\CategoryConst;
use App\Exceptions\ApiException;


class CommonUtil
{
    /**
     * 抛异常
     * @param $code
     * @param $msg
     * @throws ApiException
     */
    public static function throwException($code, $msg)
    {
        throw new ApiException($code, $msg);
    }

    /**
     * 获取头信息
     * @return string
     */
    public static function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    public static function isMobile($userAgent = '')
    {
        //正则表达式,批配不同手机浏览器UA关键词。
        $regex_match = "/(nokia | iphone | android | motorola | ^mot\- | softbank | foma | docomo | kddi | up\.browser | up\.link | ";
        $regex_match .= "htc | dopod | blazer | netfront | helio | hosin | huawei | novarra | CoolPad | webos | techfaith | palmsource | ";
        $regex_match .= "blackberry | alcatel | amoi | ktouch | nexian | samsung | ^sam\- | s[cg]h | ^lge | ericsson | philips | sagem | wellcom | bunjalloo | maui | ";
        $regex_match .= "symbian | smartphone | midp | wap | phone | windows ce | iemobile | ^spice | ^bird | ^zte\- | longcos | pantech | gionee | ^sie\- | portalmmm | ";
        $regex_match .= "jig\s browser | hiptop | ^ucweb | ^benq | haier | ^lct | opera\s * mobi | opera\*mini | 320×320 | 240×320 | 176×220)/i";
        return isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE']) or preg_match($regex_match, strtolower($userAgent == '' ? self::getUserAgent() : $userAgent));
    }

    /**
     * 取得随机代码
     * @param int $length
     * @return string
     */
    public static function getRandStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}