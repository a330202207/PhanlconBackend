<?php
/**
 * @purpose: 接口控制器
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/24
 * @version: 1.0
 */


namespace Apps\Modules\Api\Controllers;

use Apps\Librarys\ErrorCode;
use Apps\Modules\Api\Services\IndexService;

class IndexController extends BaseController
{
    /**
     * @notes: 获取首页信息
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/10/24
     * @return mixed
     * @throws \Exception
     * @version: 1.0
     */
    public function indexAction()
    {
        $string = $this->get('string', 'Hello,World!');
        $data = IndexService::getIndexInfo($string);
        return $this->jsonApi->return(ErrorCode::SUCCESS, $data);
    }

}