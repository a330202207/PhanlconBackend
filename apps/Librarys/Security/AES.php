<?php
/**
 * @purpose: AES 加解密算法
 * @author: NedRen<ned@pproject.co>
 * @date:2018/8/22
 * @version: 1.0
 */

namespace Apps\Librarys\Security;

class AES implements Crypt
{
    /**
     * @notes: 加密
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/22
     * @param $str
     * @param $key
     * @return string
     */
    static function encrypt($str, $key)
    {
        //AES, 256 cbc模式加密数据
        $encrypt_str = openssl_encrypt($str, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode(KeyConfig::$iv));
        return base64_encode($encrypt_str);
    }

    /**
     * @notes: 解密
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/22
     * @param $str
     * @param $key
     * @return string
     */
    static function decrypt($str, $key)
    {
        //AES, 256 cbc模式解密数据
        $str = base64_decode($str);
        $encrypt_str = openssl_decrypt($str, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode(KeyConfig::$iv));

        return $encrypt_str;
    }

    /**
     * @notes: 填充算法(chr()用于返回ASCII指定的字符，如chr(97)返回a)
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/22
     * @param $source
     * @return string
     */
    static function addPKCS7Padding($source)
    {
        $source = trim($source);
        $block = mcrypt_get_block_size('rijndael-128', 'cbc');
        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }

    /**
     * @notes: 移去填充算法(ord()函数用于返回一个字符的ASCII值)
     * @author: KevinRen<330202207@qq.com>
     * @date: 2018/8/22
     * @param $source
     * @return bool|string
     */
    static function stripPKSC7Padding($source)
    {
        $char = substr($source, -1);
        $num = ord($char);
        $source = substr($source, 0, -$num);
        return $source;
    }

    /**
     * @notes: aes128加密
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/22
     * @param $data
     * @param $key
     * @param $iv
     * @return string
     */
    public static function aes128Encrypt($data, $key, $iv)
    {
        if (is_array($data)) {
            $encrypt_data = json_encode($data);
        } elseif (is_object($data)) {
            $encrypt_data = json_encode($data, JSON_FORCE_OBJECT);
        } else {
            $encrypt_data = $data;
        }
        $encrypted = openssl_encrypt($encrypt_data, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encrypted);
    }

    /**
     * @notes: aes128解密
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/22
     * @param $data
     * @param $key
     * @param $iv
     * @return string
     */
    public static function aes128Decrypt($data, $key, $iv)
    {
        $decrypted = rtrim(openssl_decrypt(base64_decode($data), 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv));
        return $decrypted;
    }

}
