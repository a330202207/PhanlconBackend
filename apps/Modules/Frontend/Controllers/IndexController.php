<?php
/**
 * @purpose: 前端控制器
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/24
 * @version: 1.0
 */


namespace Apps\Modules\Frontend\Controllers;


class IndexController extends BaseController
{

    public function indexAction()
    {
        $this->view->title = '前台';
    }
}