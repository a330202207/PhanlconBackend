<?php
/**
 * @purpose: 后台登陆
 * @author: Feith<feith@pproject.co>
 * @date: 2018/11/4
 * @version: 1.0
 */

namespace Apps\Modules\Backend\Controllers;

use Apps\Models\Admin;
use Apps\Providers\ControllerProvider;
use Apps\Librarys\ErrorCode;

class LoginController extends ControllerProvider
{

    /**
     * @notes:后台登陆首页
     * @author: Feith<feith@pproject.co>
     * @date: 2018/11/4 3:09
     * @version: 1.0
     */
    public function indexAction()
    {
        $this->view->setMainView('login/index');
    }

    /**
     * @notes: 登录
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     * @version: 1.0
     */
    public function loginAction()
    {
        try {
            $username = $this->params('username');
            $password = $this->params('password');

            $obj = Admin::findFirst(['username = :username:', 'bind' => ['username' => $username]]);
            if (!$obj) {
                throw new \Exception('无此用户！');
            }

            $user_info = $obj->toArray();
            if ($user_info['password'] != md5($password)) {
                throw new \Exception('密码错误！');
            }

            $obj->save([
                'last_login_time' => time(),
                'last_login_ip' => getIp(),
            ]);

            unset($user_info['password']);
            $this->session->set('user', $user_info);
            return $this->jsonApi->return(ErrorCode::SUCCESS);
        } catch (\Throwable $e) {
            return $this->jsonApi->return(ErrorCode::FAILED, $e->getMessage());
        }
    }

    /**
     * @notes:退出登陸
     * @author: Feith<feith@pproject.co>
     * @date: 2018/11/4 22:38
     * @version: 1.0
     */
    public function logoutAction()
    {
        // 销毁全部session会话
        $this->session->destroy();
        $this->response->redirect("login/index");
    }
}