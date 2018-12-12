<?php
/**
 * @purpose: 接口模块
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/20
 * @version: 1.0
 */
namespace Apps\Modules\Api;

use Phalcon\DiInterface;
use Apps\Providers\ModuleProvider;
use Phalcon\Loader;


class Module extends ModuleProvider
{
    protected $moduleName = 'api';

}