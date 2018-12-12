<?php
/**
 * @purpose: 后台模块
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/20
 * @version: 1.0
 */

namespace Apps\Modules\Backend;

use Phalcon\DiInterface;
use Apps\Providers\ModuleProvider;
use Phalcon\Loader;

class Module extends ModuleProvider
{
    protected $moduleName = 'backend';
}