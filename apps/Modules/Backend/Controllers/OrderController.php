<?php
/**
 * @purpose: 订单控制器
 * @author: Feith<feith@pproject.co>
 * @date: 2018/11/16 14:59
 * @version: 1.0
 */

namespace Apps\Modules\Backend\Controllers;

use Apps\Models\Payment;
use Apps\Models\Channel;
use Apps\Models\PayType;

class OrderController extends BaseController
{
    /**
     * @notes:
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/5
     * @throws \Exception
     * @version: 1.0
     */
    public function indexAction()
    {

        $data = [
            'id' => $this->params('id'),
            'amount' => $this->params('amount') * 100,  //转换成元
            'order_id' => $this->params('order_id'),
            'pay_type' => $this->params('pay_type'),
            'status' => $this->params('status'),
            'start_time' => $this->params('start_time'),
            'end_time' => $this->params('end_time'),
            'type' => $this->params('type'),
            'page' => $this->params('page', 1),
            'page_set' => $this->params('page_set', 10),
        ];

        //支付类型
        $pay_type = array_column(PayType::find([
            'columns' => 'name,val',
            'is_del = 0',
        ])->toArray(), 'name', 'val');

        //渠道类型
        $channel = array_column(Channel::find([
            'columns' => 'name,val',
            'is_del = 0',
        ])->toArray(), 'name', 'val');
        $page = (new Payment)->getOrderList($data);

        $paginator_render = $this->getPaginateRender($page->total_pages);
        $page->paginator_render = $paginator_render;
        $this->view->setVar('page', $page);
        $this->view->setVar('start_time', $data['start_time']);
        $this->view->setVar('end_time', $data['end_time']);
        $this->view->setVar('order_id', $data['order_id']);
        $this->view->setVar('order_status', $data['status']);
        $this->view->setVar('channel', $channel);
        $this->view->setVar('pay_type', $pay_type);
        $this->view->setVar('status', ['支付中', '支付完成', '支付失败']);
        $this->view->pick('order/index');
    }
}