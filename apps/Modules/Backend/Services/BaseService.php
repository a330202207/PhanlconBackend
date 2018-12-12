<?php
/**
 * @purpose: 基类服务
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/11/02
 * @version: 1.0
 */


namespace Apps\Modules\Backend\Services;


use Phalcon\Di;
use Phalcon\DiInterface;
use \Apps\Models\ModelFactory;

class BaseService
{
    /**
     * DI容器
     * @var \Phalcon\Di
     */
    private $_di;

    /**
     * BaseService constructor.
     * @param DiInterface|null $di
     */
    public function __construct(DiInterface $di = null)
    {
        $this->setDI($di);
    }

    /**
     * @notes:设置DI容器
     * @author: Feith<feith@pproject.co>
     * @date: 2018/11/5
     * @param DiInterface|null $di
     * @version: 1.0
     */
    public function setDI(DiInterface $di = null)
    {
        empty($di) && $di = Di::getDefault();
        $this->_di = $di;
    }


    /**
     * @notes:获取DI容器
     * @author: Feith<feith@pproject.co>
     * @date: 2018/11/5
     * @return Di
     * @version: 1.0
     */
    public function getDI()
    {
        return $this->_di;
    }

    /**
     * @notes:获取模型对象
     * @author: Feith<feith@pproject.co>
     * @date: 2018/11/5
     * @param $modelName
     * @return mixed
     * @throws \Exception
     * @version: 1.0
     */
    protected function get_model($modelName)
    {
        return ModelFactory::get_model($modelName);
    }
}