<?php
/**
 * @purpose: 控制器供应商
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/24
 * @version: 1.0
 */


namespace Apps\Providers;

use Phalcon\Mvc\Controller;

abstract class ControllerProvider extends Controller
{

    public $di;

    private $isValidate = false;    //是否验证非空
    private $data;                  //数据
    private $name;                  //键值名
    private $default;               //默认值
    private $isArr;                 //是否数组


    /**
     * @notes: 404错误
     * @author: NedRen<ned@pproject.co>
     * @date: 2019/3/6
     * @version: 1.0
     */
    public function error404Action()
    {
        $this->view->pick('base/error404');
    }

    /**
     * @notes: 500错误
     * @author: NedRen<ned@pproject.co>
     * @date: 2019/3/6
     * @version: 1.0
     */
    public function error500Action()
    {
        $this->view->pick('base/error500');
    }

    /**
     * @notes: 获取 GET 参数
     * @author: NedRen<ned@pproject.co>
     * @date: 2019/3/7
     * @param string|null $name 参数名
     * @param string $default 默认值
     * @param bool $is_validate 是否验证，如果该值不为false，则会对参数进行校验，例如参数为空或不合法等，如果该值为字符串，则提示信息为该值
     * @param null $filter 过滤类型
     * @param bool $noRecursive 不递归过滤（即$data不作为数组循环过滤）
     * @return array|mixed|string
     * @throws \Exception
     * @version: 1.0
     * 支持 string、trim、absint、int、email、float、int!、float!、alphanum、striptags、lower、upper、url、special_chars
     * 当为 false 时，不使用默认过滤，
     * 当为字符串例如 'string,trim' 时采用参数过滤 ，
     * 当为数组例如 ['string','trim'] 时采用参数+默认过滤，当为 null 等其他值时时采用默认过滤
     */
    final protected function get(string $name = null, $default = '', bool $is_validate = false, $filter = null, bool $noRecursive = false)
    {
        $data = array_merge($this->request->getQuery(), $this->dispatcher->getParams());
        unset($data['_url']);
        return $this->sanitize($data, $name, $is_validate, $default, $filter, $noRecursive);
    }

    /**
     * @notes: 获取 POST 参数
     * @author: NedRen<ned@pproject.co>
     * @date: 2019/3/7
     * @param string|null $name 参数名
     * @param string $default 默认值
     * @param bool $is_validate 是否验证，如果该值不为false，则会对参数进行校验，例如参数为空或不合法等，如果该值为字符串，则提示信息为该值
     * @param null $filter 过滤类型
     * @param bool $noRecursive 不递归过滤（即$data不作为数组循环过滤）
     * @return array|mixed|string
     * @throws \Exception
     * @version: 1.0
     * 支持 string、trim、absint、int、email、float、int!、float!、alphanum、striptags、lower、upper、url、special_chars
     * 当为 false 时，不使用默认过滤，
     * 当为字符串例如 'string,trim' 时采用参数过滤 ，
     * 当为数组例如 ['string','trim'] 时采用参数+默认过滤，当为 null 等其他值时时采用默认过滤
     */
    final protected function post(string $name = null, $default = '', bool $is_validate = false, $filter = null, bool $noRecursive = false)
    {
        $data = $this->request->getPost();
        if (!empty($name)) {
            $data[$name] = $this->request->getPost($name);
        }
        return $this->sanitize($data, $name, $is_validate, $default, $filter, $noRecursive);
    }

    /**
     * @notes: 获取 POST 或者 GET 请求参数
     * @author: NedRen<ned@pproject.co>
     * @date: 2019/3/7
     * @param string|null $name 参数名
     * @param string $default 默认值
     * @param bool $is_validate 是否验证，如果该值不为false，则会对参数进行校验，例如参数为空或不合法等，如果该值为字符串，则提示信息为该值
     * @param null $filter 过滤类型
     * @param bool $noRecursive 不递归过滤（即$data不作为数组循环过滤）
     * @return array|mixed|string
     * @throws \Exception
     * @version: 1.0
     * 支持 string、trim、absint、int、email、float、int!、float!、alphanum、striptags、lower、upper、url、special_chars
     * 当为 false 时，不使用默认过滤，
     * 当为字符串例如 'string,trim' 时采用参数过滤 ，
     * 当为数组例如 ['string','trim'] 时采用参数+默认过滤，当为 null 等其他值时时采用默认过滤
     */
    final protected function request(string $name = null, $default = '', bool $is_validate = false, $filter = null, bool $noRecursive = false)
    {
        if (isset($name) && $name !== '') {
            return $this->post($name, $default, $is_validate, $filter, $noRecursive) ?? $this->get($name, $default, $is_validate, $filter, $noRecursive);
        }
        return array_merge($this->post(null, $default, $is_validate, $filter, $noRecursive), $this->get(null, $default, $is_validate, $filter, $noRecursive));
    }

    /**
     * @notes: 获取 JSON 请求参数
     * @author: NedRen<ned@pproject.co>
     * @date: 2019/3/7
     * @param string|null $name 参数名
     * @param string $default 默认值
     * @param bool $is_validate 是否验证，如果该值不为false，则会对参数进行校验，例如参数为空或不合法等，如果该值为字符串，则提示信息为该值
     * @param null $filter 过滤类型
     * @param bool $noRecursive 不递归过滤（即$data不作为数组循环过滤）
     * @return array|mixed|string
     * @throws \Exception
     * @version: 1.0
     * 支持 string、trim、absint、int、email、float、int!、float!、alphanum、striptags、lower、upper、url、special_chars
     * 当为 false 时，不使用默认过滤，
     * 当为字符串例如 'string,trim' 时采用参数过滤 ，
     * 当为数组例如 ['string','trim'] 时采用参数+默认过滤，当为 null 等其他值时时采用默认过滤
     */
    final protected function json(string $name = null, $default = '', bool $is_validate = false, $filter = null, bool $noRecursive = false)
    {
        $data = $this->request->getJsonRawBody(true);
        if (!is_array($data)) {
            return [];
        }
        return $this->sanitize($data, $name, $is_validate, $default, $filter, $noRecursive);
    }

    /**
     * @notes: 获取请求参数
     * @author: NedRen<ned@pproject.co>
     * @date: 2019/3/7
     * @param array $data 数据源
     * @param string|null $name 参数名
     * @param string $default 默认值
     * @param bool $is_validate 是否验证，如果该值不为false，则会对参数进行校验，例如参数为空或不合法等，如果该值为字符串，则提示信息为该值
     * @param null $filter 过滤类型
     * @param bool $noRecursive 不递归过滤（即$data不作为数组循环过滤）
     * @return array|mixed|string
     * @throws \Exception
     * @version: 1.0
     * 支持 string、trim、absint、int、email、float、int!、float!、alphanum、striptags、lower、upper、url、special_chars
     * 当为 false 时，不使用默认过滤，
     * 当为字符串例如 'string,trim' 时采用参数过滤 ，
     * 当为数组例如 ['string','trim'] 时采用参数+默认过滤，当为 null 等其他值时时采用默认过滤
     */
    public function sanitize(array $data, string $name = null, bool $is_validate = false, $default = '', $filter = null, bool $noRecursive = false)
    {

        if (true == $is_validate) {
            $this->checkValidate($data, $name);
        }
        $now_filter = null;
        if (is_string($filter) && !empty($filter)) {
            $now_filter = explode(',', $filter);
        } else if ($filter !== false) {
            $default_filter = $this->config['app']['filter']['default_filter'];
            $default_filter = isset($default_filter) ? explode(',', $default_filter) : [];
            if (is_array($filter)) {
                $default_filter = array_unique(array_merge($filter, $default_filter));
            }
            if (!empty($default_filter)) {
                $now_filter = $default_filter;
            }
        }
        if (isset($name) && $name !== '') {
            if (isset($data[$name]) && $data[$name] !== '') {
                $data = $data[$name];
            } else {
                $data = $default;
            }
        }
        if (isset($now_filter)) {
            if (is_array($data)) {
                foreach ($data as $key => $val) {
                    $data[$key] =
                        is_array($val) ? $this->sanitize($data[$key], null, $filter, $default, $noRecursive)
                            : $this->filter->sanitize($data[$key], $now_filter, $noRecursive);
                }
            } else {
                $data = $this->filter->sanitize($data, $now_filter, $noRecursive);
            }
        }
        return $data;
    }

    /**
     * @notes: 校验是否为空
     * @author: NedRen<ned@pproject.co>
     * @date: 2019/3/7
     * @param $data
     * @param $name
     * @throws \Exception
     * @version: 1.0
     */
    private function checkValidate($data, $name)
    {
        if (isset($data[$name]) && empty($data[$name])) {
            throw new \Exception($name . '值校验失败');
        } else {
            array_walk($data, function ($val, $key) {
                if ($val == '') {
                    throw new \Exception($key . '值校验失败');
                }
            });
        }
    }

    /**
     * @notes: 获取请求参数
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/10/24
     * @param null $name 参数名称，不传则为获取所有参数
     * @param string $default 默认值
     * @param null $filter 过滤函数，可以使用,分割的字符串以及正则表达式
     * @param bool $is_validate 是否验证，如果该值不为false，则会对参数进行校验，例如参数为空或不合法等，如果该值为字符串，则提示信息为该值
     * @param null string $msg
     * @return array|mixed|null|string
     * @throws \Exception
     * @version: 1.0
     */
    public function params($name = null, $default = '', $filter = null, $is_validate = false, string $msg = '')
    {
        $value = $this->request->get($name);

        if (isset($value) && !empty($value)) {
            $input = $value;
            if (isset($input['_url'])) {
                unset($input['_url']);
            }
        }
        if (empty($input) && !isset($input)) {
            $input_string = file_get_contents('php://input');
            $input_data = json_decode($input_string, true);
            if (is_array($input_data)) {
                $input = $input_data;
            } else {
                $input = [];
            }
        }
        if (empty($name) && !isset($name)) { //获取全部变量
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
                                debug('【参数值校验失败：' . $filters . '】，参数名称：' . $name, 'error');
                                throw new \Exception($name . '值校验失败');
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
                                    throw new \Exception($name . '值校验失败');
                                }
                                return isset($default) ? $default : null;
                            }
                        }
                    }
                }
            }
        } else {
            if (false !== $is_validate) {
                debug('【参数值校验失败】，参数名称：' . $name, 'error');
                $msg = $msg ?? $name.'校验失败';
                throw new \Exception($msg);
            }
            $data = isset($default) ? $default : null; //变量默认值
        }
        is_array($data) && array_walk_recursive($data, 'secure_filter');
        return $data;
    }


}