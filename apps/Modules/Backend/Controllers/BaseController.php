<?php
/**
 * @purpose: 后台基类控制器
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/24
 * @version: 1.0
 */

namespace Apps\Modules\Backend\Controllers;

use Apps\Providers\ControllerProvider;

class BaseController extends ControllerProvider
{
    private $action;

    /**
     * @notes: 初始化
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/11/5 0:56
     * @version: 1.0
     */
    public function initialize()
    {
        $this->action = $this->di->get('router')->getRewriteUri();
        $this->checkLogin();
        $this->setCommonVars();
    }

    /**
     * @notes: 设置公共变量
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/12
     * @version: 1.0
     */
    private function setCommonVars()
    {
        $this->view->setVars(array(
            'menu' => $this->action,
        ));
    }

    /**
     * @notes: 检查是否登录
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/12
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     * @version: 1.0
     */
    private function checkLogin()
    {
        if (empty($this->session->get('user'))) {
            return $this->response->redirect('login/index');
        } else {
            $this->view->setVar('user_info', $this->session->get('user'));
        }
    }


    /**
     * @notes: 获取分页结果显示
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/4
     * @param $totalPage 总页数
     * @return string
     * @version: 1.0
     */
    public function getPaginateRender($totalPage)
    {
        $str = '';
        $path = $this->action;
        $str .= '<ul class="pagination">';

        if (!empty($_GET)) {
            $urlPrefix = $path . '&page=';
        } else {
            $urlPrefix = $path . '?page=';
        }

        $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

        if ($currentPage < 1) {
            $currentPage = 1;
        }

        if ($currentPage > $totalPage) {
            $currentPage = $totalPage;
        }

        $prePage = $currentPage - 1;
        $nextPage = $currentPage + 1;

        if ($prePage < 1) {
            $str .= '<li class="disabled" rel="prev"><span>«</span></li>';
        } else {
            $str .= '<li><a href="' . $urlPrefix . $prePage . '" rel="prev">«</a></li>';
        }

        for ($i = 1; $i <= $totalPage; $i++) {
            if ($i == $currentPage) {
                $str .= '<li class="active"><span>' . $i . '</span></li>';
            } else {
                if ($totalPage > 10) {
                    if ($currentPage <= 5) {
                        if ($i >= 10) {
                            $str .= '<li class="disabled"><span>...</span></li>';
                            break;
                        } else {
                            $str .= '<li><a href="' . $urlPrefix . $i . '">' . $i . '</a></li>';
                        }
                    } else {
                        if (abs($i - $currentPage) == 5) {
                            $str .= '<li class="disabled"><span>...</span></li>';
                        }

                        if (abs($i - $currentPage) < 5) {
                            $str .= '<li><a href="' . $urlPrefix . $i . '">' . $i . '</a></li>';
                        }

                        if ($i - $currentPage > 4) {
                            break;
                        }
                    }
                } else {
                    $str .= '<li><a href="' . $urlPrefix . $i . '">' . $i . '</a></li>';
                }
            }
        }

        if ($nextPage > $totalPage) {
            $str .= '<li class="disabled" rel="next"><span>»</span></li>';
        } else {
            $str .= '<li><a href="' . $urlPrefix . $nextPage . '"' . ' rel="next">»</a></li>';
        }

        $str .= '</ul>';

        return $str;
    }

}