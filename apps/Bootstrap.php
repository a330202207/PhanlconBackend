<?php
/**
 * @purpose: 应用程序
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/20
 * @version: 1.0
 */

namespace Apps;

use Phalcon\Di\FactoryDefault as Di;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Router;
use Apps\Librarys\InvironMent;

class Bootstrap
{
    protected $di;

    protected $serviceProviders = [];

    protected $app;

    protected $config;

    public function __construct()
    {

        //创建容器
        $this->di = new Di();

        //初始化服务
        $this->initializeServices();

        //创建应用
        $this->app = new Application;
        $this->di['app'] = $this->app;
        $this->app->setDI($this->di);

        /**
         * 注册模块
         */
        $this->app->registerModules($this->config['modules']);

    }

    /**
     * 初始化服务
     */
    protected function initializeServices()
    {
        require APP_PATH . '/Librarys/InvironMent.php';

        defined('RUNTIME') || define('RUNTIME', InvironMent::getInv());

        /**
         * 获取配置
         */
        $config = array_merge(include BASE_PATH . "/config/config_". RUNTIME . ".php", include BASE_PATH . "/config/modules.php");

        $this->config = $config;

        /**
         * 设置配置
         */
        $this->di->setShared('config', function () use ($config) {
            return $config;
        });

        /**
         * 注册路由
         */
        $this->di->set('router', function () {
            //获取模块配置
            $config = $this->getConfig();
            $modules = $config['modules'];

            $router = new Router();

            $defaultModule = ''; //默认模块

            if (!empty($modules)) {

                //设置路由模块归属
                foreach ($modules as $k => $module) {
                    //模块名称
                    $module_name = empty($module['name']) ? '' : '/' . $module['name'];

                    //判断是否默认
                    if ($module['isDefault']) {
                        $defaultModule = $k;
                    } else {
                        $router->add($module_name, [
                            'module' => $k,
                            'controller' => 1,
                            'action' => 2,
                        ]);
                        $router->add($module_name . '/:controller', [
                            'module' => $k,
                            'controller' => 1,
                            'action' => 2,
                        ]);
                        $router->add($module_name . '/:controller/:action', [
                            'module' => $k,
                            'controller' => 1,
                            'action' => 2,
                        ]);
                    }
                }

                //设置默认模块
                $router->setDefaultModule($defaultModule);
            }
            return $router;
        });
    }

    /**
     * 获取返回内容
     * @return bool|\Phalcon\Http\ResponseInterface|string
     */
    protected function getOutput()
    {
        return $this->app->handle()->getContent();
    }

    /**
     * 执行
     * @return bool|\Phalcon\Http\ResponseInterface|string
     */
    public function run()
    {
        try{
            return $this->getOutput();
        }catch(\Exception $e){

            echo '<pre>' . $e->getCode() . '</pre>';
            echo '<pre>' . $e->getMessage() . '</pre>';
            echo '<pre>' . $e->getFile() . '</pre>';
            echo '<pre>' . $e->getMessage() . '</pre>';
            echo '<pre>' . nl2br( $e->getTraceAsString()) . '</pre>';

            die;

            if (RUNTIME != 'prd' && RUNTIME != 'test') {
                echo '<pre>' . $e->getCode() . '</pre>';
                echo '<pre>' . $e->getMessage() . '</pre>';
                echo '<pre>' . $e->getFile() . '</pre>';
                echo '<pre>' . $e->getMessage() . '</pre>';
                echo '<pre>' . nl2br( $e->getTraceAsString()) . '</pre>';
            } else {
                if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                    $client_ip = $_SERVER['HTTP_CLIENT_IP'];
                } else {
                    $client_ip = "0";
                }

                if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $x_client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $x_client_ip = "0";
                }

                $log = [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'code' => $e->getCode(),
                    'msg' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'userAgent' => $_SERVER['HTTP_USER_AGENT'],
                    'userIp' => $_SERVER['REMOTE_ADDR'],
                    'clientIp' => $client_ip,
                    'xClintIp' => $x_client_ip,
                ];
                debug($log, 'ERROR');
            }
        }
    }

}