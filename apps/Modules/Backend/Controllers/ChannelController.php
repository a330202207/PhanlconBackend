<?php
/**
 * @purpose: 渠道控制器
 * @author: Feith<feith@pproject.co>
 * @date: 2018/11/16 14:59
 * @version: 1.0
 */

namespace Apps\Modules\Backend\Controllers;

use Apps\Models\Channel;
use Apps\Models\ChannelInfo;
use Apps\Models\PayType;
use Apps\Librarys\ErrorCode;

class ChannelController extends BaseController
{
    /**
     * @notes: 渠道列表
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/5
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
        $page = (new Channel)->getChannelList($data);
        $paginator_render = $this->getPaginateRender($page->total_pages);
        $page->paginator_render = $paginator_render;
        $this->view->setVar('page', $page);
        $this->view->setVar('name', $data['name']);
        $this->view->pick('channel/index');
    }

    /**
     * @notes: 保存渠道
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
                'name' => $this->params('name', null, null, true, '渠道名称不能为空'),
                'val' => $this->params('val', null, null, true, '支付渠道值不能为空'),
                'member_id' => $this->params('member_id', null, null, true, '商户号不能为空'),
                'pay_url' => $this->params('pay_url', null, null, true, '渠道地址不能为空'),
                'key' => $this->params('key'),
                'public_key' => $this->params('public_key'),
                'private_key' => $this->params('private_key'),
                'status' => $this->params('status') == 'on' ? 1 : 0,
            ];

            if (empty($id)) {
                $obj = Channel::findFirst(['name = :name: AND is_del = 1', 'bind' => ['name' => $data['name']]]);
                if ($obj) {
                    throw new \Exception('渠道名称已存在，请重复命名');
                }
                $data['created_at'] = time();
                $res = (new Channel())->create($data);
            } else {
                $obj = Channel::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
                $data['updated_at'] = time();
                $res = $obj->save($data);
            }

            if ($res) {
                return $this->jsonApi->return(ErrorCode::SUCCESS, '保存成功', ['url' => '/admin/channel/index']);
            } else {
                throw new \Exception('保存失败');
            }
        } catch (\Throwable $e) {
            return $this->jsonApi->return(ErrorCode::FAILED, $e->getMessage());
        }
    }

    /**
     * @notes: 编辑渠道
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
            $obj = Channel::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
            $this->view->setVar('data', $obj);
        }
        $this->view->setVar('title', $obj ? '编辑' : '添加');
        $this->view->pick('channel/edit');
    }

    /**
     * @notes: 删除渠道
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/6
     * @throws \Exception
     * @version: 1.0
     */
    public function delAction()
    {
        try {
            $id = $this->params('id');
            $obj = Channel::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
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
     * @notes: 渠道下支付方式
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @throws \Exception
     * @version: 1.0
     */
    public function channelInfoAction()
    {
        $data = [
            'id' => $this->params('id'),
            'page' => $this->params('page', 1),
            'page_set' => $this->params('page_set', 10)
        ];

        $page = (new ChannelInfo())->getChannelInfo($data);

        $paginator_render = $this->getPaginateRender($page->total_pages);
        $page->paginator_render = $paginator_render;
        $this->view->setVar('cid', $data['id']);
        $this->view->setVar('page', $page);
        $this->view->pick('channel/channel_info');
    }

    /**
     * @notes: 添加/编辑支付方式
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @throws \Exception
     * @version: 1.0
     */
    public function channelInfoEditAction()
    {
        $action = $this->params('action');
        $cid = $this->params('cid');
        if ($action == 'edit') {
            $id = $this->params('id');
            $obj = ChannelInfo::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
            $this->view->setVar('data', $obj);
        }
        $pay_arr = PayType::find(['is_del = :is_del:', 'bind' => ['is_del' => 0]]);
        $this->view->setVar('pay_arr', $pay_arr->toArray());
        $this->view->setVar('cid', $cid);
        $this->view->setVar('title', $obj ? '编辑' : '添加');
        $this->view->pick('channel/channel_info_edit');
    }

    /**
     * @notes: 保存支付方式
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @throws \Exception
     * @version: 1.0
     */
    public function channelInfoSaveAction()
    {
        try {
            $id = $this->params('id');
            $data = [
                'pay_id' => $this->params('pay_id', null, null, true, '支付方式不能为空'),
                'channel_id' => $this->params('channel_id', null, null, true, '渠道ID不能为空'),
                'pay_name' => $this->params('pay_id', null, null, true, '支付值不能为空'),

                'max' => $this->params('max', null, null, true, '支付最大额度不能为空') * 100,
                'min' => $this->params('min', null, null, true, '支付最大额度不能为空') * 100,
                'status' => $this->params('status') == 'on' ? 1 : 0,
            ];

            if (empty($id)) {
                $obj = ChannelInfo::findFirst([
                    'channel_id = :channel_id: AND pay_id = :pay_id: AND is_del = 1',
                    'bind' => [
                        'channel_id' => $data['channel_id'],
                        'pay_id' => $data['pay_id'],
                    ]
                ]);
                if ($obj) {
                    throw new \Exception('支付方式已存在，请重复选择!');
                }
                $data['created_at'] = time();
                $res = (new ChannelInfo())->create($data);
            } else {
                $obj = ChannelInfo::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
                $data['updated_at'] = time();
                $res = $obj->save($data);
            }

            if ($res) {
                return $this->jsonApi->return(ErrorCode::SUCCESS, '保存成功', ['url' => '/admin/channel/channelInfo?id='.$data['channel_id']]);
            } else {
                throw new \Exception('保存失败');
            }
        } catch (\Throwable $e) {
            var_dump($e);die;
            return $this->jsonApi->return(ErrorCode::FAILED, $e->getMessage());
        }
    }

    /**
     * @notes: 删除渠道支付方式
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @version: 1.0
     */
    public function channelInfoDelAction()
    {
        try {
            $id = $this->params('id');
            $obj = ChannelInfo::findFirst(['id = :id:', 'bind' => ['id' => $id]]);
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