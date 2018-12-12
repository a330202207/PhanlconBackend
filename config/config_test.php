<?php
/**
 * @purpose: 系统配置--测试环境
 * @author: NedRen<ned@pproject.co>
 * @date:2018/8/15
 */

return [
    'app' => [
        //日志根目录
        'log_path' => BASE_PATH . '/storage/logs/',

        //模型缓存目录
        'models_cache_path' => BASE_PATH . '/storage/cache/db/',
    ],

    'database' => [
        //数据库连接信息
        'dbMaster' => [
            'adapter' => 'Mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => 'root',
            'dbname' => 'pg_payment',
            'charset' => 'utf8',
        ],
        'dbSlave' => [
            'adapter' => 'Mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => 'root',
            'dbname' => 'pg_payment',
            'charset' => 'utf8',
        ],
        //表前缀
        'prefix' => 'pg',
    ],

    'beanstalkd' => [
        'host' => '127.0.0.1',
        'port' => 11301,
        'connect_timeout' => 5,
        'persistent' => false
    ],

    'redis' => [
        'servers' => [
            '127.0.0.1:6379',
        ],
        'auth' => '1354243', //目前只有启动单点才有效
    ],
];