<?php
/**
 * @purpose: RSA 加解密算法
 * @author: NedRen<ned@pproject.co>
 * @date:2018/8/22
 * @version: 1.0
 *
 * 1、Linux 生成RSA密钥：
 * 私钥：openssl genrsa -out rsa_private_key.pem 1024
 *
 * 2、将原始私钥转换为pkcs8格式：
 * openssl pkcs8 -topk8 -inform PEM -in rsa_private_key.pem -outform PEM -nocrypt
 *
 * 3、私钥生成公钥
 * openssl rsa -in rsa_private_key.pem -pubout -out ras_public_key.pem
 *
 * 小工具
 * http://web.chacuo.net/netrsakeypair
 *
 */


namespace Apps\Librarys\Security;


class RSA implements Crypt
{
    /**
     * @notes: 超长加密（由于秘钥有长度限制比如1024，2048约长表示接受的加密数据越多，否则会有超长加密不成功的问题）
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/27
     * @param $content
     * @param $public_key
     * @return mixed
     * 117表示长度，加密的时候可以从这开始分割加密（解密的时候从128开始解密即可）
     */
    public static function encrypt($content, $public_key)
    {
        $result = '';
        $data = str_split($content, 117);

        foreach ($data as $block) {
            openssl_public_encrypt($block, $dataEncrypt, $public_key, OPENSSL_PKCS1_PADDING);
            $result .= $dataEncrypt;
        }
        return base64_encode($result);
    }

    /**
     * @notes: 超长私钥解密（128开始截取解密）
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/27
     * @param $content
     * @param $private_key
     * @return mixed
     */
    public static function decrypt($content, $private_key)
    {
        $data = base64_decode($content);
        $result = '';
        $data = str_split(($data), 128);
        foreach ($data as $block) {
            openssl_private_decrypt($block, $dataDecrypt, $private_key, OPENSSL_PKCS1_PADDING);
            $result .= $dataDecrypt;
        }
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }


}