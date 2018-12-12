<?php
/**
 * @purpose: 支付类型控制器
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/11/16 14:59
 * @version: 1.0
 */

namespace Apps\Modules\Backend\Controllers;

use Apps\Models\PayType;
use Apps\Librarys\ErrorCode;

class PayTypeController extends BaseController
{
    /**
     * @notes: 支付方式列表
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @throws \Exception
     * @version: 1.0
     */
    public function indexAction()
    {
        $data = [
            'name' => $this->params('name'),
            'page' => $this->params('page', 1),
            'page_set' => $this->params('page_set', 10),
        ];

        $page = (new PayType())->getPayTypeList($data);
        $paginator_render = $this->getPaginateRender($page->total_pages);
        $page->paginator_render = $paginator_render;
        $this->view->setVar('page', $page);
        $this->view->setVar('name', $data['name']);
        $this->view->pick('paytype/index');
    }

    /**
     * @notes: 保存支付方式
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @throws \Exception
     * @version: 1.0
     */
    public function saveAction()
    {
        try {
            $id = $this->params('id');
            $data = [
                'name' => $this->params('name', null, null, true, '支付方式名称不能为空'),
                'val' => $this->params('val', null, null, true, '支付方式值不能为空'),
                'status' => $this->params('status') == 'on' ? 1 : 0,
            ];

            if (empty($id)) {
                $obj = PayType::findFirst(['name = :name:', 'bind' => ['name' => $data['name']]]);
                if ($obj) {
                    throw new \Exception('支付指已存在，请重新命名！');
                }
                $data['created_at'] = time();

                $res = (new PayType())->create($data);

            } else {
                $obj = PayType::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
                $data['updated_at'] = time();
                $res = $obj->save($data);
            }

            if ($res) {
                return $this->jsonApi->return(ErrorCode::SUCCESS, '保存成功', ['url' => '/admin/PayType/index']);
            } else {
                throw new \Exception('保存失败');
            }
        } catch (\Throwable $e) {
            return $this->jsonApi->return(ErrorCode::FAILED, $e->getMessage());
        }



    }

    /**
     * @notes: 编辑支付方式
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @throws \Exception
     * @version: 1.0
     */
    public function editAction()
    {
        $action = $this->params('action');
        if ($action == 'edit') {
            $id = $this->params('id');
            $obj = PayType::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
            $this->view->setVar('data', $obj);
        }
        $this->view->setVar('title', $obj ? '编辑' : '添加');
        $this->view->pick('paytype/edit');
    }

    /**
     * @notes: 删除支付方式
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @throws \Exception
     * @version: 1.0
     */
    public function delAction()
    {
        try {
            $id = $this->params('id');
            $obj = PayType::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
            $res = $obj->save([
                'is_del' => 1,
                'updated_at' => time()
            ]);
            if ($res) {
                return $this->jsonApi->return(ErrorCode::SUCCESS, '删除成功');
            } else {
                throw new \Exception('删除失败!');
            }
        } catch (\Throwable $e) {
            return $this->jsonApi->return(ErrorCode::FAILED, $e->getMessage());
        }
    }

}