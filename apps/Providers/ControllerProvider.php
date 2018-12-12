<?php
/**
 * @purpose: 控制器供应商
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/24
 * @version: 1.0
 */


namespace Apps\Providers;

use Phalcon\Mvc\Controller;
use Phalcon\Debug;



abstract class ControllerProvider extends Controller
{
    //404错误
    public function error404Action()
    {
        $this->view->pick('base/error404');
    }

    //500错误
    public function error500Action()
    {
        $this->view->pick('base/error500');
    }

    /**
     * @notes: 获取请求参数
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/10/24
     * @param null $name 参数名称，不传则为获取所有参数
     * @param string $default 默认值
     * @param null $filter 过滤函数，可以使用,分割的字符串以及正则表达式
     * @param bool $is_validate 是否验证，如果该值不为false，则会对参数进行校验，例如参数为空或不合法等，如果该值为字符串，则提示信息为该值
     * @param string 返回信息提示
     * @return array|mixed|null|string
     * @throws \Exception
     * @version: 1.0
     */
    public function params($name = null, $default = '', $filter = null, $is_validate = false, $msg = '')
    {
        if ($value = $this->request->get($name)) {
            $input = $value;
            if (isset($input['_url'])) {
                unset($input['_url']);
            }
        }
        if (empty($input)) {
            $input_string = file_get_contents('php://input');
            $input_data = json_decode($input_string, true);
            if (is_array($input_data)) {
                $input = $input_data;
            } else {
                $input = [];
            }
        }
        if (empty($name)) { //获取全部变量
            $data = $input;
            $filters = isset($filter) ? $filter : 'htmlspecialchars';
            if ($filters) {
                if (is_string($filters)) {
                    $filters = explode(',', $filters);
                }
                foreach ($filters as $filter) {
                    $data = array_map_recursive($filter, $data); //参数过滤
                }
            }
        } elseif ((is_array($input) && isset($input[$name])) || is_string($input)) { //取值操作
            if (is_string($input)) {
                $data = $input;
            } else {
                $data = $input[$name];
            }
            $filters = isset($filter) ? $filter : 'htmlspecialchars';
            if ($filters) {
                if (is_string($filters)) {
                    if (0 === strpos($filters, '/')) {
                        if (1 !== preg_match($filters, (string)$data)) { //支持正则验证
                            if (false !== $is_validate) {
                                debug('【参数值校验失败：'.$filters.'】，参数名称：' . $name);
                                throw new \Exception('参数值校验失败');
                            }
                            return isset($default) ? $default : null;
                        }
                    } else {
                        $filters = explode(',', $filters);
                    }
                } elseif (is_int($filters)) {
                    $filters = array($filters);
                }
                if (is_array($filters)) {
                    foreach ($filters as $filter) {
                        if (function_exists($filter)) {
                            $data = is_array($data) ? array_map_recursive($filter, $data) : $filter($data); //参数过滤
                        } else {
                            $data = filter_var($data, is_int($filter) ? $filter : filter_id($filter));
                            if (false === $data) {
                                if (false !== $is_validate) {
//                                    debug(['msg' => '参数值校验失败,参数名称:' . $name, 'filters' => $filters], 'error');
                                    throw new \Exception('参数值校验失败');
                                }
                                return isset($default) ? $default : null;
                            }
                        }
                    }
                }
            }
        } else {
            if (false !== $is_validate) {
                debug('【参数值校验失败】，参数名称：' . $name, 'debug');
                $msg = $msg ?? $name.'校验失败';
                throw new \Exception($msg);
            }
            $data = isset($default) ? $default : null; //变量默认值
        }
        is_array($data) && array_walk_recursive($data, 'secure_filter');
        return $data;
    }


}