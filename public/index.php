<?php

use Apps\Bootstrap;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: accept, content-type, token, key');
header('Access-Control-Expose-Headers: *');
header('content-type:text/html;charset=utf-8');

ini_set('date.timezone', 'Asia/Shanghai');

define('BASE_PATH', dirname(__DIR__));

//自动加载
include BASE_PATH . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'autoload.php';

$bootstrap = new Bootstrap();
echo $bootstrap->run();
