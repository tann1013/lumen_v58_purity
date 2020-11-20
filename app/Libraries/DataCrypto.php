<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2019-07-05
 * @version 1.0
 */

namespace App\Libraries;

//与BOS对接
//解析token和内部通讯秘钥
use phpDocumentor\Reflection\Types\Self_;

class DataCrypto
{
    //link
    const LINK_TOKEN_KEY = 'fccf47eaf4a947071c346a389cb6d7d5';
    //info
    const INFO_TOKEN_KEY = '7951429DF4114F50ACD2740030F4FFCB';
    //Bos Token解密方法
    public function SimpleDecrypBosToken($string) {
        return $this->SimpleDecryp(str_replace("*", "+",str_replace("@", "=",$string)));
    }

    // Simple字符串解密方法
    public function SimpleDecryp($string) {
        // 加密秘钥
        $key = '7951429DF4114F50ACD2740030F4FFCB';
        $key_length = 24;
        /*加解密用到的key*/
        $decrypt_key = substr($key, 0, $key_length);

        $origin = $string;
        $string = base64_decode( $string );

        $arr = [];

        /*进行异或运算*/
        for( $i = 0; $i < strlen( $string )  ; $i++) {
            $arr[] = $string[ $i ] ^ $decrypt_key[ $i % $key_length ];
        }

        $uidd = implode('', $arr);

        return $uidd;
    }

    // Simple字符串加密方法
    public function SimpleEncryp($string) {
        $key = '7951429DF4114F50ACD2740030F4FFCB';
        $key_length = 24;
        /*加解密用到的key*/
        $decrypt_key = substr($key, 0, $key_length);

        $origin = $string;

        $arr = [];

        for ($i=0; $i < strlen($string); $i++) {
            $arr[]  = chr( ord($string[$i]) ) ^ $decrypt_key[ $i % $key_length ];
        }
        $uidd = implode('', $arr);
        $lv = base64_encode( $uidd );
        //var_dump( $lv );die;
        return  $lv;
    }


    /**
     * 多语言平台加解密函数 php .net javascript java
     * str 要加密的内容，pwd 加解密用到的key
     * @return       String
     * @author       iclubs <iclubs@126.com>
     * @copyright    Copyright (c) Date, Openver.com
     * @link         http://www.openver.com
     */
    private function _sysStrEncrypt($str, $pwd) {
        if (empty($pwd) || strlen($pwd) <= 0) {
            // die("请输入用于加密信息的密码。");
            return false;
        }
        $str = (string) $str;
        $prand = "";
        for ($i = 0; $i < strlen($pwd); $i++) {
            $prand .= ord($pwd[$i]);
        }
        $sPos = (int) floor(strlen($prand) / 5);
        $mult = intval($prand[$sPos] . $prand[$sPos * 2] . $prand[$sPos * 3] . $prand[$sPos * 4] . (strlen($prand) > $sPos * 5 ? $prand[$sPos * 5] : ''));
        $incr = ceil(strlen($pwd) / 2);
        $modu = pow(2, 31) - 1;
        if ($mult < 2) {
            // die("算法找不到合适的哈希值，请选择一个更复杂或更长的密码。");
            return false;
        }
        $salt = round($this->_sysRandom(0, 1) * 1000000000) % 100000000;
        $prand .= $salt;
        $k = strlen($prand) - 11;
        while (strlen($prand) > 10) {
            if (strlen($prand) <= $k) {
                $k = 10;
            }

            $prand = substr($prand, 0, 10) + substr($prand, $k, strlen($prand) - 10);
        }
        $prand = (int) fmod(($mult * $prand + $incr), $modu);
        $enc_chr = "";
        $enc_str = "";
        for ($i = 0; $i < strlen($str); $i++) {
            $enc_chr = intval(ord($str[$i]) ^ floor(($prand / $modu) * 255));
            if ($enc_chr < 16) {
                $enc_str .= "0" . dechex($enc_chr);
            } else {
                $enc_str .= dechex($enc_chr);
            }
            $prand = (int) fmod(($mult * $prand + $incr), $modu);
        }
        $salt = dechex($salt);
        while (strlen($salt) < 8) {
            $salt = "0" . $salt;
        }

        return $enc_str . $salt;
    }
    private function _sysStrDecrypt($str, $pwd) {
        if (empty($str) || strlen($str) < 8) {
            // die("解密失败！加密消息长度太短无法提取salt值。");
            return false;
        }
        if (empty($pwd) || strlen($pwd) <= 0) {
            // die("解密失败！请输入用于加密信息的密码。");
            return false;
        }
        $prand = "";
        for ($i = 0; $i < strlen($pwd); $i++) {
            $prand .= ord($pwd[$i]);
        }
        $sPos = (int) floor(strlen($prand) / 5);
        $mult = intval($prand[$sPos] . $prand[$sPos * 2] . $prand[$sPos * 3] . $prand[$sPos * 4] . (strlen($prand) > $sPos * 5 ? $prand[$sPos * 5] : ''));
        $incr = ceil(strlen($pwd) / 2);
        $modu = pow(2, 31) - 1;
        $tmp_len = strlen($str) - 8;
        $salt = intval(substr($str, $tmp_len, strlen($str) - $tmp_len), 16);
        $str = substr($str, 0, $tmp_len);
        $prand .= $salt;
        $k = strlen($prand) - 11;
        while (strlen($prand) > 10) {
            if (strlen($prand) <= $k) {
                $k = 10;
            }

            $prand = substr($prand, 0, 10) + substr($prand, $k, strlen($prand) - 10);
        }
        $prand = (int) fmod(($mult * $prand + $incr), $modu);
        $enc_chr = "";
        $enc_str = "";
        for ($i = 0; $i < strlen($str); $i += 2) {
            $enc_chr = intval(intval(substr($str, $i, 2), 16) ^ floor(($prand / $modu) * 255));
            $enc_str .= $this->_sysFromCharCode($enc_chr);
            $prand = (int) fmod(($mult * $prand + $incr), $modu);
        }
        return $enc_str;
    }

    //实现 js random()
    private function _sysRandom($min = 0, $max = 1) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    //实现 js _fromCharCode()
    private function _sysFromCharCode($codes) {
        if (is_scalar($codes)) {
            $codes = func_get_args();
        }

        $str = '';
        foreach ($codes as $code) {
            $str .= chr($code);
        }

        return $str;
    }

    /**
     * @param $infoToken
     * @return String
     */
    public function sysGetLinkToken(){
        $timpstamp = time();
        $tokenKey = self::LINK_TOKEN_KEY;
        $linkToken = $this->_sysStrEncrypt($timpstamp, $tokenKey);
        return $linkToken;
    }
}