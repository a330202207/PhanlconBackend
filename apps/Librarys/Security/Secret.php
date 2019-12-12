<?php
/**
 * @purpose: 加解密算法
 * @author: NedRen<ned@pproject.co>
 * @date:2018/8/22
 * @version: 1.0
 */


namespace Apps\Librarys\Security;


class Secret
{

    /**
     * @notes: 加密
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/27
     * @param $way
     * @param $str
     * @param $key
     * @return string
     */
    public static function encrypt($way, $str, $key)
    {
        switch ($way) {
            case 'AES':
                return AES::encrypt($str, $key);
            case 'MD5':
                return md5($str);
            case 'RAS' :
                return RSA::encrypt($str, $key);
            default:
                return AES::encrypt($str, $key);
        }

    }

    /**
     * @notes: 解密
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/27
     * @param $way
     * @param $str
     * @param $key
     * @return string
     */
    public static function decrypt($way, $str, $key)
    {
        switch ($way) {
            case 'AES':
                return AES::decrypt($str, $key);
            case 'RAS' :
                return RSA::decrypt($str, $key);
            default:
                return AES::decrypt($str, $key);
        }
    }


}
