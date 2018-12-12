<?php

/**
 * @notes: 获取DI
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/8/30
 * @return \Phalcon\DiInterface
 */
function getDI()
{
    return \Phalcon\Di::getDefault();
}

/**
 * @notes: 获取数据库实例
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/8/30
 * @return mixed
 */
function getDbConnection()
{
    return getDI()->getShared('db');
}

/**
 * @notes: 调试日志
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/8/30
 * @param mixed $message 信息
 * @param string $type 类型（debug、error、info、notice、warning、alert、log）
 * @param boolean $simple 简单模式，如果为true则不显示文件和行号及类名和方法名
 * @param boolean $option 是否显示文件和行号及类名和方法名
 * @param string|array $filename 文件名或数组，数组格式：['payments', 'log']，第一个为文件路径，第二个为文件名，如果文件名为空字符串，则使用日期标识
 */
function debug($message, $type = 'DEBUG', $simple = false, $option = false, $filename = null)
{
    $type = strtoupper($type);
    $typeList = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];
    if (!in_array($type, $typeList)) {
        $type = 'DEBUG';
    }
    if (is_array($message)) {
        $message = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } elseif (is_object($message)) {
        $message = json_encode($message, JSON_FORCE_OBJECT);
    }
    if ($simple === false) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        if ($trace) {
            $file = $trace[0]['file'];
            $line = $trace[0]['line']; # 在那一行调用了debug方法
            $fun = '';
            $class = '';
            if (isset($trace[1])) {
                $fun = $trace[1]['function'];
                if (isset($trace[1]['class'])) {
                    $class = $trace[1]['class'];
                }
            }
            $ex = explode(BASE_PATH, $file);
            if (!isset($ex[1])) {
                $file = $ex[0];
            } else {
                $file = $ex[1];
            }
            if (!$class || !$fun) {
                if ($option) {
                    $message = "[{$file}:{$line}] " . $message;
                } else {
                    $message = ' ' . $message;
                }
            } else {
                if ($option) {
                    $message = "[{$file}:{$line}][{$class}:{$fun}] " . $message;
                } else {
                    $message = "[{$file}:{$line}] " . $message;
                }
            }
        }
    } else {
        $message = ' ' . $message;
    }
    $action = strtolower($type);
    getDI()->get('log', ['bxPayment', $filename, $type])->$action($message);
}

/**
 * @notes: 浏览器友好的变量输出
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/8/30
 * @param mixed $var 变量
 * @param bool $echo 是否输出 默认为true 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param bool $strict 是否严谨 默认为true
 * @return mixed|null|string|string[]
 */
function dumps($var, $echo = true, $label = NULL, $strict = true)
{
    if ($label === NULL) {
        $label = '';
    } else {
        $label = rtrim($label) . ' ';
    }
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return NULL;
    } else {
        return $output;
    }
}

/**
 * @notes: 文件上传
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/8/30
 * @param string $rootPath 文件上传保存的根路径
 * @param string $savePath 文件的保存路径（相对于根路径）
 * @param array $file 文件数组
 * @param bool $one 是否为单文件上传
 * @param mixed $saveName 上传文件的保存规则，支持数组和字符串方式定义
 * @param array $exts 允许上传的文件后缀
 * @param array $mimes 允许上传的文件类型
 * @param integer $maxSize 文件上传的最大文件大小（以字节为单位），0为不限大小
 * @param bool $replace 存在同名文件是否是覆盖，默认为true
 * @param bool $autoSub 自动使用子目录保存上传文件
 * @return array|bool
 * @throws Exception
 */
function upload($rootPath, $savePath, $file, $one = true, $saveName = null, $exts = [], $mimes = [], $maxSize = 0, $replace = true, $autoSub = false)
{
    $uploadConfig = [
        'maxSize' => $maxSize, //文件上传的最大文件大小（以字节为单位），0为不限大小
        'rootPath' => rtrim($rootPath . '/') . '/', //文件上传保存的根路径
        'savePath' => trim($savePath . '/'), //文件上传的保存路径（相对于根路径）
        'autoSub' => $autoSub, //自动使用子目录保存上传文件
        'replace' => $replace, //存在同名文件是否是覆盖
    ];
    if (!empty($saveName)) {
        $uploadConfig['saveName'] = $saveName; //上传文件的保存规则，支持数组和字符串方式定义
    }
    if (!empty($exts)) {
        $uploadConfig['exts'] = $exts; //允许上传的文件后缀
    }
    if (!empty($mimes)) {
        $uploadConfig['mimes'] = $mimes; //允许上传的文件类型
    }
    $upload = new \core\library\Think\Upload\Upload($uploadConfig); // 实例化上传类
    if ($one) {
        $uploadInfo = $upload->uploadOne($file);
    } else {
        $fileArray = [];
        for ($i = 0; $i < count($file['name']); $i++) {
            $fileArray[$i]['name'] = $file['name'][$i];
            $fileArray[$i]['type'] = $file['type'][$i];
            $fileArray[$i]['tmp_name'] = $file['tmp_name'][$i];
            $fileArray[$i]['error'] = $file['error'][$i];
            $fileArray[$i]['size'] = $file['size'][$i];
        }
        $uploadInfo = $upload->upload($fileArray);
    }
    if ($uploadInfo) {
        return $uploadInfo;
    } else {
        throw new \Exception($upload->getError());
    }
}

/**
 * @notes: 将数组分割拼接成,形式字符串
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/8/30
 * @param array $arr
 * @param $valName
 * @return string
 * $arr = [[id => '1'], [id => '2'], [id => '3']];
 * return '1,2,3'
 */
function spliceStr(array $arr, $valName)
{
    $data = array_column($arr, $valName);
    $str = implode(',', $data);
    return $str;
}

/**
 * @notes: 批量插入
 * @author: KevinRen<330202207@qq.com>
 * @date: 2018/4/8
 * @param $table string 表名
 * @param $arr  array 插入
 * @return bool|string
 *
 * $arr = [['id' => 1, 'name' => 'test1'], ['id' => 2, 'name' => 'test2']]
 *
 * array2Insert('post', $arr)
 *
 * INSERT INTO `post`( 'id','name' ) values ('1','test1') , ('2','test2')
 */
function batchInsert($table, array $arr)
{
    $arrKeys = array_keys(array_shift($arr));

    if (empty($table) || !is_array($arr)) {
        return false;
    }

    $fields = implode(',', array_map(function ($value) {
        return "`" . $value . "`";
    }, $arrKeys));

    foreach ($arr as $key => $val) {
        $arrValues[$key] = implode(',', array_map(function ($value) {
            return "'" . $value . "'";
        }, $val));
    }

    $values = "(" . implode(') , (', array_map(function ($value) {
            return $value;
        }, $arrValues)) . ")";

    $sql = "INSERT INTO `%s`( %s ) values %s ";

    $sql = sprintf($sql, $table, $fields, $values);
    return $sql;
}

/**
 * @notes: 批量删除
 * @author: KevinRen<330202207@qq.com>
 * @date: 2018/4/8
 * @param $table  string 表名
 * @param $data   array  待删除的数据，二维数组格式
 * @param $field  string 值不同的条件，默认为id
 * @return bool|string
 * $arr = [['id' => 1], ['id' => 2]]
 *
 * array2Delete('post', $arr, 'id')
 *
 * DELETE FROM `post`  WHERE `id` IN (`1`,`2`)
 */
function batchDelete($table, $data, $field)
{
    if (!is_array($data) || !$field) {
        return false;
    }

    $fields = array_column($data, $field);
    $fields = implode(',', array_map(function ($value) {
        return "'" . $value . "'";
    }, $fields));

    $sql = 'DELETE FROM `%s` WHERE `%s` IN (%s)';

    $sql = sprintf($sql, $table, $field, $fields);
    return $sql;
}

/**
 * @notes: 批量更新
 * @author: KevinRen<330202207@qq.com>
 * @date: 2018/4/8
 * @param $table  string 表名
 * @param $data   array  待更新的数据，二维数组格式
 * @param $field  string 值不同的条件，默认为id
 * @param $params array  值相同的条件，键值对应的一维数组
 * @return bool|string
 * $data = [
 *      ['id' => 1, 'parent_id' => 100, 'title' => 'A', 'sort' => 1],
 *      ['id' => 2, 'parent_id' => 100, 'title' => 'A', 'sort' => 3]
 * ];
 *
 * batchUpdate('post', $data, 'id', ['parent_id' => 100, 'title' => 'A']);
 *
 * UPDATE `post` SET
 *      `id` = CASE `id`
 *          WHEN '1' THEN '1'
 *          WHEN '2' THEN '2'
 *      END,
 *      `parent_id` = CASE `id`
 *          WHEN '1' THEN '100'
 *          WHEN '2' THEN '100'
 *      END,
 *      `title` = CASE `id`
 *          WHEN '1' THEN 'A'
 *          WHEN '2' THEN 'A'
 *      END,
 *      `sort` = CASE `id`
 *          WHEN '1' THEN '1'
 *          WHEN '2' THEN '3'
 *      END
 * WHERE `id` IN ('1','2')  AND `parent_id` = '100' AND `title` = 'A'
 *
 * $data = [
 *      ['id' => 1, 'sort' => 1],
 *      ['id' => 2, 'sort' => 2]
 * ];
 *
 *
 * batchUpdate('post', $data, 'id');
 *
 * UPDATE `post` SET
 *      `id` = CASE `id`
 *          WHEN '1' THEN '1'
 *          WHEN '2' THEN '2'
 *      END,
 *      `sort` = CASE `id`
 *          WHEN '1' THEN '1'
 *          WHEN '2' THEN '3'
 *      END
 * WHERE `id` IN ('1','2')
 *
 * $data = [
 *      ['id' => 1, 'sort' => 1],
 *      ['id' => 2, 'sort' => 2]
 * ];
 *
 */
function batchUpdate($table, $data, $field, array $params = [])
{
    if (!is_array($data) || !$field || !is_array($params)) {
        return false;
    }

    $updates = parseUpdate($data, $field);
    $where = parseParams($params);

    // 获取所有键名为$field列的值，值两边加上单引号，保存在$fields数组中
    $fields = array_column($data, $field);
    $fields = implode(',', array_map(function ($value) {
        return "'" . $value . "'";
    }, $fields));

    $sql = 'UPDATE `%s` SET %s WHERE `%s` IN (%s) %s';

    $sql = sprintf($sql, $table, $updates, $field, $fields, $where);

    return $sql;
}

/**
 * 将二维数组转换成CASE WHEN THEN的批量更新条件
 * @param $data array 二维数组
 * @param $field string 列名
 * @return string sql语句
 */
function parseUpdate($data, $field)
{
    $sql = '';
    $keys = array_keys(current($data));
    foreach ($keys as $column) {

        $sql .= sprintf("`%s` = CASE `%s` \n", $column, $field);
        foreach ($data as $line) {
            $sql .= sprintf("WHEN '%s' THEN '%s' \n", $line[$field], $line[$column]);
        }
        $sql .= "END,";
    }

    return rtrim($sql, ',');
}

/**
 * 解析where条件
 * @param $params
 * @return array|string
 */
function parseParams($params)
{
    $where = [];
    foreach ($params as $key => $value) {
        $where[] = sprintf("`%s` = '%s'", $key, $value);
    }

    return $where ? ' AND ' . implode(' AND ', $where) : '';
}

