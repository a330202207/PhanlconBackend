<?php
/**
 * @purpose: aip地址列表
 * @author: NedRen<ned@pproject.co>
 * @date:2018/8/22
 */

$host = [
    'test'=>[
        'dev' => 'http://dev.com',
        'test'=> 'http://test.com',
        'prd' => 'http://prd.com',
    ],
];

return [
    'test' => $host['test'][RUNTIME],
];