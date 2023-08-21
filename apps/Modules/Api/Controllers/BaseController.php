<?php
/**
 * @purpose: 接口基类控制器
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/24
 * @version: 1.0
 */


namespace Apps\Modules\Api\Controllers;

use Apps\Providers\ControllerProvider;

class BaseController extends ControllerProvider
{
    private $token;     //令牌

    //不进行校验的路由列表
    protected static $not_check_routes = [
        'index/index',              //支付回调
    ];

    public function onConstruct()
    {
        $routes = strtolower($this->router->getControllerName() . '/' . $this->router->getActionName());
        if (!in_array($routes, self::$not_check_routes)) {
            $this->checkToken();
        }
    }

    /**
     * @notes: 检查Token
     * @author: NedRen<ned@pproject.co>
     * @date: 2019/3/7
     * @version: 1.0
     */
    private function checkToken()
    {
        try {
            $get_token = $this->request->getQuery('token');

            $post_token = $this->request->getPost('token');

            $this->token = !empty($get_token) ? $get_token : $post_token;

            if (empty($this->token)) {
                throw new \Exception('token不能为空！');
            }

            $token = md5($this->config['app']['token']);

            if ($this->token != $token) {
                throw new \Exception('token错误！');
            }

        } catch (\Throwable $e) {
            header('Content-type: application/json; charset=utf-8');
            echo json_encode(['msg' => $e->getMessage(), 'code' => ErrorCode::FAILED]);
            die;
        }
    }
}