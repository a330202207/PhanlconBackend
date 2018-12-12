<?php
/**
 * @purpose: 管理员管理
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/12/7
 * @version: 1.0
 */

namespace Apps\Modules\Backend\Controllers;

use Apps\Models\Admin;
use Apps\Librarys\ErrorCode;

class AdminController extends BaseController
{
    /**
     * @notes: 管理员列表
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @throws \Exception
     * @version: 1.0
     */
    public function indexAction()
    {
        $data = [
            'username' => $this->params('username'),
            'page' => $this->params('page', 1),
            'page_set' => $this->params('page_set', 10),
        ];

        $page = (new Admin())->getAdminList($data);
        $paginator_render = $this->getPaginateRender($page->total_pages);
        $page->paginator_render = $paginator_render;
        $this->view->setVar('page', $page);
        $this->view->setVar('username', $data['username']);
        $this->view->pick('admin/index');
    }

    /**
     * @notes: 保存管理员
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/6
     * @throws \Exception
     * @version: 1.0
     */
    public function saveAction()
    {
        try {
            $id = $this->params('id');
            $data = [
                'username' => $this->params('username', null, null, true, '管理员名称不能为空'),
                'mobile' => $this->params('mobile', null, null, true, '电话不能为空'),
                'status' => $this->params('status') == 'on' ? 1 : 0,
            ];

            if (empty($id)) {
                $obj = Admin::findFirst(['username = :username: AND is_del = 0', 'bind' => ['username' => $data['username']]]);
                if ($obj) {
                    throw new \Exception('管理员已存在，请重新命名！');
                }
                $data['created_at'] = time();

                $res = (new Admin())->create($data);

            } else {
                $obj = Admin::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
                $data['updated_at'] = time();
                $res = $obj->save($data);
            }
            if ($res) {
                return $this->jsonApi->return(ErrorCode::SUCCESS, '保存成功', ['url' => '/admin/admin/index']);
            } else {
                throw new \Exception('保存失败');
            }

        } catch (\Throwable $e) {
            return $this->jsonApi->return(ErrorCode::FAILED, $e->getMessage());
        }
    }

    /**
     * @notes: 编辑管理员
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/6
     * @throws \Exception
     * @version: 1.0
     */
    public function editAction()
    {
        $action = $this->params('action');
        if ($action == 'edit') {
            $id = $this->params('id');
            $obj = Admin::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
            $this->view->setVar('data', $obj);
        }
        $this->view->setVar('title', $obj ? '编辑' : '添加');
        $this->view->pick('admin/edit');
    }

    /**
     * @notes: 删除管理员
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @throws \Exception
     * @version: 1.0
     */
    public function delAction()
    {
        try {
            $id = $this->params('id');
            $obj = Admin::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
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

    /**
     * @notes: 编辑管理员
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @version: 1.0
     */
    public function editPasswordAction()
    {
        $id = $this->params('id');
        $this->view->setVar('id', $id);
        $this->view->pick('admin/edit_password');
    }

    /**
     * @notes: 保存管理员
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @throws \Exception
     * @version: 1.0
     */
    public function savePasswordAction()
    {
        try {
            $id = $this->params('id');
            $password = $this->params('password', null, null, true, '新密码不能为空');
            $repeat_password = $this->params('repeat_password', null, null, true, '重复密码不能为空');

            if ($password != $repeat_password) {
                throw new \Exception('两次密码不一致');
            }

            $obj = Admin::findFirst(['id = :id:', 'bind' => ['id' => $id]]);

            if (!$obj) {
                throw new \Exception('未找到该管理员!');
            }

            $res = $obj->save([
                'password' => md5($password),
                'updated_at' => time(),
            ]);

            if ($res) {
                return $this->jsonApi->return(ErrorCode::SUCCESS, '保存成功!', ['url' => '/admin/admin/index']);
            } else {
                throw new \Exception('保存失败!');
            }
        } catch (\Throwable $e) {
            return $this->jsonApi->return(ErrorCode::FAILED, $e->getMessage());
        }

    }
}