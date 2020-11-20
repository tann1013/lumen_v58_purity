<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-04-23
 * @version 1.0
 */
namespace App\Foundation;

use Illuminate\Support\Facades\Redis;

class PrimaryKeyGenerator
{
    /**
     * 自增生成
     *
     * @param string $key
     * @return integer
     * @author Chuoke
     */
    public static function generate($key)
    {
        return Redis::incr($key);
    }

    /**
     * 设置过期
     *
     * @param string $key
     * @param integer $seconds
     * @return boolean
     * @author Chuoke
     */
    public static function expire($key, $seconds)
    {
        return Redis::expire($key, $seconds);
    }
}