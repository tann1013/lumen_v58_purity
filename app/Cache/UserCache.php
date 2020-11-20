<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2019-07-11
 * @version 1.0
 */

namespace App\Cache;


class UserCache
{
    const USER_DEDUCT_SCORE_MAP = 'user_deduct_score_map';

    public function __construct(string $class = 'codis')
    {
        parent::__construct($class);
    }

    /**
     * 获取用户被扣分记录
     * @param $userinfoId
     * @return bool|string
     */
    public function hGetUserDeductScoreMap($userinfoId)
    {
        return $this->hGet(self::getKey(self::USER_DEDUCT_SCORE_MAP), $userinfoId);
    }

    /**
     * 设置用户被扣分记录
     * @param $userinfoId
     * @return bool|string
     */
    public function hSetUserDeductScoreMap($userinfoId, $value, $ttl)
    {
        return $this->hSet(self::getKey(self::USER_DEDUCT_SCORE_MAP), $userinfoId, $value, $ttl);
    }

}