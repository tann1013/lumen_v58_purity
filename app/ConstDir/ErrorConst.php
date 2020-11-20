<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2019-07-05
 * @version 1.0
 */

namespace App\ConstDir;


class ErrorConst
{
    // 成功响应
    const SUCCESS_CODE = 0;
    const SUCCESS_CODE_MSG = 'success';

    // 错误通用code和message
    const COMMON_ERROR_CODE = 1;
    const COMMON_ERROR_CODE_MSG = 'error';

    //参数缺少
    const ERROR_CODE = 100;
    const ERROR_CODE_MSG = '缺少参数';
    //校验错误
    const CHECK_ERROR_CODE = 101;
    const CHECK_ERROR_CODE_MSG = '校验错误';
    //解析错误
    const RESOLVE_ERROR_CODE = 102;
    const RESOLVE_ERROR_CODE_MSG = '解析错误';
    //数据库写入错误
    const RESOLVE_DATA_CODE = 103;
    const RESOLVE_DATA_CODE_MSG = '数据库写入错误';

    //参数错误
    const PARAMETER_ERROR = 122;
    const PARAMETER_ERROR_MSG = '参数错误';

}