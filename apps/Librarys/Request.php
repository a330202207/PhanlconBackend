<?php
/**
 * @purpose: HTTP请求
 * @author: NedRen<ned@pproject.co>
 * @date:2018/8/20
 * @version: 1.0
 */


namespace Apps\Librarys;

use Apps\Librarys\ErrorCode;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;

class Request
{
    /**
     * @notes: 直接进行请求
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/22
     * @param string $uri 请求地址
     * @param mixed $data 数据
     * @param string $method 请求方式
     * @param array $headers 自定义header
     * @param string $type 数据类型
     * @param bool $original 是否返回初始值
     * @param bool $verify 是否在请求时验证SSL证书行为
     * @param bool $decode 是否json转义
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \Exception
     * @version: 1.0
     */
    public static function Go($uri, $data = null, $method = 'POST', $type = 'json', $headers = [], $original = false, $verify = false, $decode = true)
    {
        $methodList = ['GET', 'POST'];
        if (empty($method) || !in_array(strtoupper($method), $methodList)) {
            $method = 'POST';
        }
        $typeList = ['form_params', 'json', 'body', 'multipart'];
        if (empty($type) || !in_array(strtolower($type), $typeList)) {
            $type = 'json';
        }
        try {
            $client = new GuzzleHttpClient();
            $options = [
                'headers' => $headers,
                'verify' => $verify
            ];
            if (!empty($data)) {
                $options[$type] = $data;
            }
            $response = $client->request($method, $uri, $options);
            if (true === $original) {
                return $response;
            }
            $contents = $response->getBody()->getContents();
            if (!empty($contents) && is_string($contents)) {
                //如果数据为json并解析成功
                if (is_json($contents)) {
                    return json_decode(trim($contents, chr(239) . chr(187) . chr(191)), true);
                }
                if (is_xml($contents)) {
                    return xml_decode($contents);
                }
            }
            return $contents;
        } catch (GuzzleException $exception) {
            throw new \Exception($exception->getMessage(), ErrorCode::FAILED);
        }
    }

}