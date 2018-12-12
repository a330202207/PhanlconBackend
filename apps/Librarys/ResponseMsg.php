<?php
/**
 * @purpose: Json 返回消息
 * @author: NedRen<ned@pproject.co>
 * @date:2018/8/20
 * @version: 1.0
 */

namespace Apps\Librarys;

use \Phalcon\DiInterface;

class ResponseMsg
{


    /**
     * DI对象
     * @var \Phalcon|DI
     */
    public $di;

    protected $_jsonApiContentType = 'application/vnd.api+json';

    public function __construct(DiInterface $di)
    {
        $this->setDI($di);
    }

    /**
     * DI对象赋值
     * @param DiInterface $di
     */
    public function setDI(DiInterface $di)
    {
        $this->di = $di;
    }

    /**
     * 获取DI对象
     * @return DI|\Phalcon
     */
    public function getDI()
    {
        return $this->di;
    }


    /**
     * @notes: json返回数据格式
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/22
     * @param int $errorCode
     * @param string $errorMsg
     * @param null $data
     * @param int $status
     * @return mixed
     * @version: 1.0
     */
    public function return(int $errorCode, string $errorMsg = '', $data = null, int $status = 200)
    {
        $extraData = [
            'status' => $errorCode,
            'msg' => $errorMsg,
        ];

        if (!empty($data)) {
            $extraData['data'] = $data;
        }

        return $this->getDI()->getResponse()
                ->setStatusCode($status)
                ->setContentType($this->_jsonApiContentType, 'utf-8')
                ->setJsonContent($extraData);
    }


}