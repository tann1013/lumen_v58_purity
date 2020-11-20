<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2019-07-05
 * @version 1.0
 */

namespace App\Exceptions;
use Exception;

class ApiException extends \Exception
{
    /**
     * 为了兼容现在code msg 顺序
     * @param int $code
     * @param string $message
     * @param Exception|null $previous
     */
    public function __construct($code = 0, $message = "", Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}