<?php
/**
 * @purpose: 模块供应商
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/20
 * @version: 1.0
 */


namespace Apps\Providers;


use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\DiInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Events\Event;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Mvc\Model\Manager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Phalcon\Config\Adapter\Php;
use Phalcon\Config;
use Apps\Librarys\RepositoryFactory;
use Phalcon\Queue\Beanstalk;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Apps\Librarys\ResponseMsg;

abstract class ModuleProvider implements ModuleDefinitionInterface
{
    protected $moduleName;

    /**
     * 注册自动加载
     * @param DiInterface|null $di
     */
    public function registerAutoloaders(DiInterface $di = null) {}

    /**
     * 注册服务
     * @param $di
     */
    public function registerServices(DiInterface $di)
    {
        $moduleName = ucfirst($this->moduleName);

        //注册派遣器
        $di->set('dispatcher', function () use ($moduleName) {
            $eventsManager = new EventsManager;
            //错误处理
            $eventsManager->attach('dispatch:beforeException', function (Event $event, Dispatcher $dispatcher, \Exception $exception) use ($moduleName) {
                if ($exception instanceof DispatcherException) {
                    switch ($exception->getCode()) {
                        case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                        case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                            $dispatcher->forward([
                                'controller' => 'base',
                                'action' => 'error404'
                            ]);
                            return false;
                    }
                }
                $dispatcher->forward([
                    'controller' => 'base',
                    'action' => 'error500'
                ]);
                return false;
            });

            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('Apps\Modules\\' . $moduleName . '\Controllers\\');
            $dispatcher->setEventsManager($eventsManager);
            return $dispatcher;
        });

        /**
         * 加载当前模块配置文件
         * 模块内使用方法：$this->moduleConfig->名称
         * 注意：该方式与系统本身配置获取方式不同，如需使用系统核心配置请使用Config::get()方式
         */
        if (file_exists(BASE_PATH . '/apps/Modules/' . $moduleName . '/Config/config.php')) {
            $di->set('moduleConfig', function () use ($di, $moduleName) {
                $module_config = include BASE_PATH.  '/apps/Modules/' . $moduleName . '/Config/config.php';
                return new Config($module_config);
            });
        }

        //注册url
        $di->set('url', function () use ($moduleName) {
            //获取配置
            $urlName = $this->getConfig()['modules'][lcfirst($moduleName)]['name'];
            $url = new UrlResolver();
            $url->setBaseUri($urlName ? '/' . $urlName . '/' : '/');
            return $url;
        }, true);

        //注册视图
        $di->set('view', function () use ($moduleName) {
            $view = new View();
            $view->setViewsDir(BASE_PATH . '/apps/Modules/' . $moduleName . '/Views/');

            $view->registerEngines([
                '.phtml' => function ($view) use ($moduleName) {

                    $volt = new VoltEngine($view, $this);

                    $dirPath = BASE_PATH . '/storage/cache/' . lcfirst($moduleName) . '/';

                    //检测目录是否存在
                    if (!is_dir($dirPath)) mkdir($dirPath, 0777, true);

                    $volt->setOptions([
                        'compiledPath' => $dirPath,
                        'compiledSeparator' => '_'
                    ]);

                    return $volt;
                },
//                '.phtml' => PhpEngine::class

            ]);
            return $view;
        });

        //注册session
        $di->setShared('session', function () {
            $session = new SessionAdapter();
            $session->start();

            return $session;
        });

        //注册数据库
        $di->setShared('db', function () {
            $config = $this->getConfig();
            $params = [
                'host'     => $config['database']['dbMaster']['host'],
                'port'     => $config['database']['dbMaster']['port'],
                'username' => $config['database']['dbMaster']['username'],
                'password' => $config['database']['dbMaster']['password'],
                'dbname'   => $config['database']['dbMaster']['dbname'],
                'charset'  => $config['database']['dbMaster']['charset']
            ];

            $connection = new Mysql($params);

            return $connection;
        });

        //注册redis
        $di->setShared('redis', function () use ($di) {
            $config = $this->getConfig();
            $count = count($config);
            //单点
            if (1 == $count) {
                $redis = new \Redis();
                list($host, $port) = explode(':', $config[0]);
                if (!$redis->connect($host, $port)) {
                    return false;
                }
                $redis->auth($cfg->redis->auth ?? '');
            }
            $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
            return $redis;
        });

        //注册aip地址
        $di->setShared('apiList', function () use ($di) {
            $apiList = new Php(BASE_PATH . "/config/host_api.php");
            return $apiList;
        });

        //注册modelsManager
        $di->setShared('modelsManager', function () use ($di) {
            return new Manager();
        });

        //注册仓库工厂
        $di->set("repository", function () {
            $repository = new RepositoryFactory();
            return $repository;
        });

        //注册日志
        $di->set('log', function ($name = 'api', $filename = null, $type = 'DEBUG') use ($moduleName) {
            $config = $this->getConfig();
            $filePath = $config['app']['log_path'] . lcfirst($moduleName) . '/';

            if (is_array($filename) && count($filename) == 2) {
                $filename = array_values($filename);
                $filePath .= '/' . $filename[0];
                $filename = $filename[1];
            } else {
                $filePath .= '/' . strtolower($type);
            }

            is_dir($filePath) or mkdir($filePath, 0777, true);

            if (empty($filename)) {
                $filename = date('Ymd') . '.log';
            }

            $path = $filePath . '/' . $filename;
            if (!file_exists($path)) {
                $fp = fopen($path, 'w');
                chmod($path, 0777);
                fclose($fp);
            }
            // 创建日志频道
            $logger = new Logger($name);
            $formatter = new LineFormatter(null, 'Y-m-d H:i:s');
            $stream = new StreamHandler($path, Logger::class . '::' . strtoupper($type));

            $stream->setFormatter($formatter);
            $logger->pushHandler($stream);
            return $logger;
        });

        //注册返回消息
        $di->setShared('jsonApi', function () use ($di) {
            $validator = new ResponseMsg($di);
            return $validator;
        });

       //注册Beanstalk
        $di->set('queue', function (){
            $config = $this->getConfig();
            return new Beanstalk([
                'host' => $config['beanstalkd']['host'],
                'port' => $config['beanstalkd']['port'],
                'connect_timeout' => $config['beanstalkd']['connect_timeout'],
                'persistent' => $config['beanstalkd']['persistent'],
            ]);
        },
            true
        );
    }
}