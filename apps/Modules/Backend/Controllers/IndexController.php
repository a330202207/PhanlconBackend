<?php
/**
 * @purpose: 后台控制器
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/24
 * @version: 1.0
 */

namespace Apps\Modules\Backend\Controllers;

use Apps\Models\Payment;

class IndexController extends BaseController
{
    /**
     * @notes: 首页
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/12
     * @version: 1.0
     */
    public function indexAction()
    {
        $data = (new Payment)->getOrderTotalInfo();
        $this->view->setVar('total_num', $data['total_num']);
        $this->view->setVar('avg_amount', $data['avg_amount'] / 100);
        $this->view->setVar('sum_amount', $data['sum_amount'] / 100);
        $this->view->pick('index/index');
    }

}