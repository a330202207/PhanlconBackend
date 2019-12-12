<?php
/**
 * @purpose: 应用程序
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/25
 * @version: 1.0
 */


namespace Apps;

use Phalcon\DI\FactoryDefault\Cli as CliDi,
    Phalcon\CLI\Console as ConsoleApp,
    Phalcon\ClI\Dispatcher,
    Phalcon\Loader,
    Phalcon\Db\Adapter\Pdo\Mysql,
    Monolog\Logger,
    Monolog\Handler\StreamHandler,
    Monolog\Formatter\LineFormatter,
    Apps\Librarys\InvironMent,
    Phalcon\Queue\Beanstalk;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR);

ini_set('date.timezone', 'Asia/Shanghai');

/**
 * 加载composer类库
 */
require_once BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class CliTask
{
    protected $di;
    protected $console;
    protected $module;
    protected $arguments;
    protected $config;

    public function __construct($argv)
    {
        /**
         * 处理console应用参数
         */
        $this->module = ''; //模块
        $this->arguments = []; //参数


        foreach ($argv as $k => $arg) {
            if ($k == 1) {
                $this->module = $arg;
            } elseif ($k == 2) {
                $this->arguments['task'] = $arg;
            } elseif ($k == 3) {
                $this->arguments['action'] = $arg;
            } elseif ($k >= 4) {
                $this->arguments['params'][] = $arg;
            }
        }
        // 定义全局的参数， 设定当前任务及动作
        define('CURRENT_MODULE', (isset($argv[2]) ? $argv[2] : null));
        define('CURRENT_TASK', (isset($argv[2]) ? $argv[2] : null));
        define('CURRENT_ACTION', (isset($argv[3]) ? $argv[3] : null));

        $this->module = ucfirst($this->module);

        //验证是否已传入模块
        if (empty($this->module)) throw new \Exception('请传入参数指定对应模块!');

        //获取模块Dir
        $moduleDir = APP_PATH . 'Modules/' . $this->module;

        //验证该模块是否已存在
        if (!is_dir($moduleDir)) throw new \Exception('该模块不存在!');

        //验证主任务是否存在
        if (!is_file($moduleDir . '/Tasks/MainTask.php')) throw new \Exception('主任务文件不存在!');

        /**
         * 注册目录及命名空间
         */
        $loader = new Loader();
        $loader->registerDirs([
            $moduleDir . '/Tasks/'
        ], true);

        $loader->registerNamespaces([
            'Apps' => APP_PATH,
            'Apps\\Modules\\' . $this->module . '\\Tasks' => $moduleDir . 'Tasks/',
            'Apps\\Modules\\' . $this->module . '\\Models' => $moduleDir . 'Models/'
        ], true);


        $loader->registerFiles([
            APP_PATH . '/Helpers/framework.php', //框架函数库
            APP_PATH . '/Helpers/functions.php', //核心函数库
        ]);

        $loader->register();

        // 使用CLI工厂类作为默认的服务容器
        $this->di = new CliDi();

        //初始化服务
        $this->initializeServices();

        //创建应用
        $this->console = new ConsoleApp();
        $this->console->setDI($this->di);
        //注入应用
        $this->di->setShared('console', $this->console);
    }

    /**
     * 初始化配置
     */
    protected function initializeServices()
    {
        $module = $this->module;

        require APP_PATH . '/Librarys/InvironMent.php';

        defined('RUNTIME') || define('RUNTIME', InvironMent::getInv());

        /**
         * 获取配置
         */
        $config = array_merge(include BASE_PATH . "/config/config_" . RUNTIME . ".php", include BASE_PATH . "/config/modules.php");

        $this->config = $config;

        //注册配置
        $this->di->setShared('config', function () use ($config) {
            return $config;
        });

        //注册模块
        $this->di->setShared('module', function () use ($module) {
            return $module;
        });

        //注册派遣器
        $this->di->set('dispatcher', function () use ($module) {
            $dispatcher = new Dispatcher();
            $dispatcher->setTaskName('Apps\Modules\\' . $module . '\\Tasks\\');
            return $dispatcher;
        });

        //注册日志
        $this->di->set('log', function ($name = 'Cli', $filename = null, $type = 'DEBUG') use ($module) {
            $config = $this->getConfig();
            $filePath = $config['app']['log_path'] . 'cli/' . lcfirst($module) . '/';

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

        //注册数据库
        $this->di->set('db', function () {
            $config = $this->getConfig();
            $params = [
                'host' => $config['database']['host'],
                'port' => $config['database']['port'],
                'username' => $config['database']['username'],
                'password' => $config['database']['password'],
                'dbname' => $config['database']['dbname'],
                'charset' => $config['database']['charset'],
                'prefix' => $config['database']['prefix'],
            ];

            $connection = new Mysql($params);

            return $connection;
        });

        //注册Beanstalk
        $this->di->set('queue', function () {
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

    //执行
    public function run()
    {
        try {
            $this->console->handle($this->arguments);
        } catch (\Throwable $e) {
            echo $e->getMessage();
            exit(255);
        }
    }
}

//应用开始
$cli = new CliTask($argv);
$cli->run();
