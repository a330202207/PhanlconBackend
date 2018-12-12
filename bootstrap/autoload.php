<?php
/**
 * @purpose: 自动加载
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/20
 * @version: 1.0
 */

use Phalcon\Loader;

define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR);

/**
 * 加载composer类库
 */
require_once BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/**
 * 加载
 */
$loader = new Loader();

/**
 * 注册公共命名空间
 */
$loader->registerNamespaces([
    'Apps' => APP_PATH
]);

$loader->registerFiles([
    APP_PATH . '/Helpers/framework.php', //框架函数库
    APP_PATH . '/Helpers/functions.php', //核心函数库
]);

$loader->register(true);