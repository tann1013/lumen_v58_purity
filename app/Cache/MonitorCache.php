<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2019-08-13
 * @version 1.0
 */

namespace App\Cache;


use Illuminate\Support\Facades\Cache;

class MonitorCache
{
    const LUMEN_CACHE_PREFIX = 'BaseCacheV1:';

    //价格洞察缓存失效时间统一设置
    const CACHE_MINUTES_FOR_PRICE_TREND =  120 * 8;//8小时
    //缓存失效时间统一设置（用户监控）
    const CACHE_MINUTES_FOR_USER_MONITOR =  120 * 2;//6小时
    //首页统计#缓存时间统一设置（HomeStatController）
    const CACHE_MINUTES_FOR_HOME_STAT = 120 * 2;


    /**
     * @param $whichModel
     * @param $whichFunction
     * @param $whichInputs
     * @param $howManyMinutes
     * @return array
     */
    public static function _getCommonCacheSetting($whichModel, $whichFunction, $whichInputs, $howManyMinutes = 120 * 6){
        $_date_range = implode('_to_',$whichInputs);
        $_function_string = $whichModel.':'.$whichFunction;
        $_cache_key = self::LUMEN_CACHE_PREFIX . $_function_string.':'.$_date_range;
        $_cache_expire = $howManyMinutes;//分钟
        $_cache_enable = true;//env('CACHE_ENABLE');//false-关闭缓存
        $cache_list = null;

        return [
            '_cache_key' => $_cache_key,
            '_cache_expire' => $_cache_expire,
            '_cache_enable' => $_cache_enable
        ];
    }

    /**
     * @param $_cache_key
     */
    public static function _forgetCacheByKeyProcess($_cache_key){
        $v = env('CACHE_FORGET_ENABLE');
        if($v){
            Cache::forget($_cache_key);//删除缓存
        }
    }

}