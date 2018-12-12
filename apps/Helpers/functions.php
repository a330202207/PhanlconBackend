<?php

/**
 * 基础函数库
 * @author: wangyu <wangyu@ledouya.com>
 * @createTime: 2018/3/15 17:51
 * @version 1.4.0
 */

/**
 * 判断当前服务是https还是http协议
 * @return string
 */
function httpType()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ? 'https://' : 'http://';
}

function startWith($str, $needle)
{
    return strpos($str, $needle) === 0;
}

//第一个是原串,第二个是 部份串
function endWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}

/**
 * php时间戳转成mysql timestamp 类型
 *
 * @param bool $format_u
 * @param null $time
 * @return false|string
 */
function timestamp($format_u = false, $time = null)
{
    $time = $time ?? microtime(true);
    $timestamp = floor($time);
    $milliseconds = round(($time - $timestamp) * 1000000);
    $format = \Phwoolcon\DateTime::MYSQL_DATETIME;
    $str = date($format, $timestamp);
    if ($format_u) {
        $str .= '.' . $milliseconds;
    }
    return $str;
}

/**
 * 使用CURL进行请求
 * @param string $url
 * @param bool $https 是否为https请求
 * @param string $post post数据，不传递则为GET请求
 * @param array $header 请求头
 * @param string $cookie 提交的Cookie
 * @param int $returnCookie 是否返回Cookie
 * @return mixed|string
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/3/24 17:26
 */
function curlRequest($url, $https = FALSE, $post = '', $header = [], $cookie = '', $returnCookie = 0)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, '');
    if ($post) {
        if (is_array($post)) {
            $post = http_build_query($post);
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    }
    if ($header) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }
    if ($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if ($https) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
    if ($returnCookie) {
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        $matches = array();
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie'] = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    } else {
        return $data;
    }
}

/**
 * 将内容写入到文件
 * @param string $path 文件路径
 * @param string $file 文件名
 * @param string $content 内容
 * @return void
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/3/15 18:05
 */
function writeFile($path, $file, $content)
{
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
    $fp = fopen(rtrim(str_replace('\\', '/', $path), '/') . $file, 'w');
    flock($fp, LOCK_EX);
    fwrite($fp, $content);
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * XML编码
 * @param mixed $data 数据
 * @param string $root 根节点名
 * @param string $item 数字索引的子节点名
 * @param string $attr 根节点属性
 * @param string $id 数字索引子节点key转换的属性名
 * @param string $encoding 数据编码
 * @return string
 */
function xml_encode($data, $root = 'application', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8')
{
    if (is_array($attr)) {
        $_attr = array();
        foreach ($attr as $key => $value) {
            $_attr[] = "{$key}=\"{$value}\"";
        }
        $attr = implode(' ', $_attr);
    }
    $attr = trim($attr);
    $attr = empty($attr) ? '' : " {$attr}";
    $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
    $xml .= "<{$root}{$attr}>";
    $xml .= data_to_xml($data, $item, $id);
    $xml .= "</{$root}>";
    return $xml;
}

/**
 * XML解码
 * @param string $xml XML字符串
 * @return bool|mixed
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/17 11:10
 */
function xml_decode($xml)
{
    if (!$xml) {
        return false;
    }
    // 检查xml是否合法
    $xml_parser = xml_parser_create();
    if (!xml_parse($xml_parser, $xml, true)) {
        xml_parser_free($xml_parser);
        return false;
    }
    libxml_disable_entity_loader(true); //禁止引用外部xml实体
    $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true); //将XML转为array
    return $data;
}

/**
 * 数据XML编码
 * @param mixed $data 数据
 * @param string $item 数字索引时的节点名称
 * @param string $id 数字索引key转换为的属性名
 * @return string
 */
function data_to_xml($data, $item = 'item', $id = 'id')
{
    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if (is_numeric($key)) {
            $id && $attr = " {$id}=\"{$key}\"";
            $key = $item;
        }
        $xml .= "<{$key}{$attr}>";
        $xml .= (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
        $xml .= "</{$key}>";
    }
    return $xml;
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type = 0)
{
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

/**
 * 区分大小写的文件存在判断
 * @param string $filename 文件地址
 * @return boolean
 */
function file_exists_case($filename)
{
    if (is_file($filename)) {
        if (IS_WIN) {
            if (basename(realpath($filename)) != basename($filename)) {
                return false;
            }
        }
        return true;
    }
    return false;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv = false)
{
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 不区分大小写的in_array方法
 * @param string $value 规定要在数组搜索的值
 * @param array $array 规定要搜索的数组
 * @return bool
 */
function in_array_case($value, $array)
{
    return in_array(strtolower($value), array_map('strtolower', $array));
}

/**
 * 获取内容中第一张图片
 * @param string $data 内容
 * @return string 图片链接
 * @author KingRainy <kingrainy@163.com>
 */
function firstPic($data)
{
    $matches = array();
    preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', htmlspecialchars_decode($data), $matches);
    $first_img = $matches[1][0];
    if (empty($first_img)) {
        return false;
    } else {
        return $first_img;
    }
}

/**
 * 获取用户IP及端口号
 * @param bool $port 是否获取端口号
 * @param bool $portJoin 端口号是否追加至IP后
 * @return string
 * @author KingRainy <kingrainy@163.com>
 */
function getIp($port = false, $portJoin = false)
{
    $realip = '';
    $unknown = 'unknown';
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($arr as $ip) {
                $ip = trim($ip);
                if ($ip != 'unknown') {
                    $realip = $ip;
                    break;
                }
            }
        } else if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
            $realip = $_SERVER['REMOTE_ADDR'];
        } else {
            $realip = $unknown;
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)) {
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)) {
            $realip = getenv("REMOTE_ADDR");
        } else {
            $realip = $unknown;
        }
    }
    $matches = array();
    $ipMatches = preg_match("/[\d\.]{7,15}/", $realip, $matches);
    if ($ipMatches) {
        $realip = $matches[0];
    } else {
        $realip = $unknown;
    }
    if ($port) {
        $ipport = intval($_SERVER['REMOTE_PORT']);
        if ($portJoin) {
            $realip = $realip . ':' . $ipport;
        } else {
            return $ipport;
        }
    }
    return $realip;
}

/**
 * 根据IP获取地理位置
 * @param string $ip IP地址
 * @param string $option 参数（string、array）
 * @param string $separator 参数为string的分隔符
 * @return string
 * @author KingRainy <kingrainy@163.com>
 */
function getIpLookup($ip = '', $option = 'string', $separator = ' ')
{
    $ip = empty($ip) ? GetIp() : $ip;
    /*$result = file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
    if (empty($result)) {
        return '无法获取';
    }
    $jsonMatches = array();
    preg_match('#\{.+?\}#', $result, $jsonMatches);
    if (!isset($jsonMatches[0])) {
        return '无法获取';
    }
    $data = json_decode($jsonMatches[0], true);
    if (isset($data['ret']) && $data['ret'] == 1) {
        $data['ip'] = $ip;
        unset($data['ret']);
    } else {
        return '无法获取';
    }
    if ($option == 'string') {
        return $data['country'] . $separator . $data['province'] . $separator . $data['city'];
    } elseif ($option == 'array') {
        return $data;
    } else {
        return $data[$option];
    }*/
    $result = curlRequest('http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip);
    if (empty($result)) {
        return '无法获取';
    }
    $data = json_decode($result, true);
    if (!isset($data['code']) || $data['code'] != 0 || empty($data['data'])) {
        return '无法获取';
    }
    if ($option == 'string') {
        return $data['data']['country'] . $separator . $data['data']['region'] . $separator . $data['data']['city'];
    } elseif ($option == 'array') {
        return $data['data'];
    } else {
        return $data['data'][$option];
    }
}

/**
 * 获取用户所使用系统的名称
 * @return string
 * @author KingRainy <kingrainy@163.com>
 */
function getSysName()
{
    $os = '';
    $Agent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/win/i', $Agent) && strpos($Agent, '95')) {
        $os = 'Windows 95';
    } else if (preg_match('/win 9x/i', $Agent) && strpos($Agent, '4.90')) {
        $os = 'Windows ME';
    } else if (preg_match('/win/i', $Agent) && preg_match('/98/', $Agent)) {
        $os = 'Windows 98';
    } else if (preg_match('/win/i', $Agent) && preg_match('/nt 5.0/i', $Agent)) {
        $os = 'Windows 2000';
    } else if (preg_match('/win/i', $Agent) && preg_match('/nt 5.1/i', $Agent)) {
        $os = 'Windows XP';
    } else if (preg_match('/win/i', $Agent) && preg_match('/nt 5.2/i', $Agent)) {
        $os = 'Windows Server 2003';
    } else if (preg_match('/win/i', $Agent) && preg_match('/nt 6.0/i', $Agent)) {
        $os = 'Windows Vista';
    } else if (preg_match('/win/i', $Agent) && preg_match('/nt 6.1/i', $Agent)) {
        $os = 'Windows 7';
    } else if (preg_match('/win/i', $Agent) && preg_match('/nt 6.2/i', $Agent)) {
        $os = 'Windows 8';
    } else if (preg_match('/win/i', $Agent) && preg_match('/nt 6.3/i', $Agent)) {
        $os = 'Windows 8.1';
    } else if (preg_match('/win/i', $Agent) && preg_match('/nt 6.4/i', $Agent)) {
        $os = 'Windows 10 Technical Preview';
    } else if (preg_match('/win/i', $Agent) && preg_match('/nt 10/i', $Agent)) {
        $os = 'Windows 10';
    } else if (preg_match('/win/i', $Agent) && preg_match('/nt/i', $Agent)) {
        $os = 'Windows NT';
    } else if (preg_match('/win/i', $Agent) && preg_match('/32/', $Agent)) {
        $os = 'Windows 32';
    } else if (preg_match('/linux/i', $Agent)) {
        $os = 'Linux';
    } else if (preg_match('/unix/i', $Agent)) {
        $os = 'Unix';
    } else if (preg_match('/sun/i', $Agent) && preg_match('/os/i', $Agent)) {
        $os = 'SunOS';
    } else if (preg_match('/ibm/i', $Agent) && preg_match('/os/i', $Agent)) {
        $os = 'IBM OS/2';
    } else if (preg_match('/Mac/i', $Agent) && preg_match('/PC/i', $Agent)) {
        $os = 'Macintosh';
    } else if (preg_match('/PowerPC/i', $Agent)) {
        $os = 'PowerPC';
    } else if (preg_match('/AIX/i', $Agent)) {
        $os = 'AIX';
    } else if (preg_match('/HPUX/i', $Agent)) {
        $os = 'HPUX';
    } else if (preg_match('/NetBSD/i', $Agent)) {
        $os = 'NetBSD';
    } else if (preg_match('/BSD/i', $Agent)) {
        $os = 'BSD';
    } else if (preg_match('/OSF1/i', $Agent)) {
        $os = 'OSF1';
    } else if (preg_match('/IRIX/i', $Agent)) {
        $os = 'IRIX';
    } else if (preg_match('/FreeBSD/i', $Agent)) {
        $os = 'FreeBSD';
    } else if ($os == '') {
        $os = 'Unknown';
    }
    return $os;
}

/**
 * 获取用户所使用浏览器的名称
 * @return string
 * @author KingRainy <kingrainy@163.com>
 */
function getBrowserName()
{
    $agent = $_SERVER["HTTP_USER_AGENT"];
    if (strpos($agent, 'MSIE') !== false || strpos($agent, 'rv:11.0')) { //ie11判断
        return "IE";
    } else if (strpos($agent, 'Firefox') !== false) {
        return "Firefox";
    } else if (strpos($agent, 'Chrome') !== false) {
        return "Chrome";
    } else if (strpos($agent, 'Opera') !== false) {
        return 'Opera';
    } else if ((strpos($agent, 'Chrome') == false) && strpos($agent, 'Safari') !== false) {
        return 'Safari';
    } else {
        return 'Unknown';
    }
}

/**
 * 获取用户所使用浏览器的版本号
 * @return string
 * @author KingRainy <kingrainy@163.com>
 */
function getBrowserVer()
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) { //当浏览器没有发送访问者的信息的时候
        return 'Unknow';
    }
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $regs = array();
    if (preg_match('/MSIE\s(\d+)\..*/i', $agent, $regs)) {
        return $regs[1];
    } elseif (preg_match('/FireFox\/(\d+)\..*/i', $agent, $regs)) {
        return $regs[1];
    } elseif (preg_match('/Opera[\s|\/](\d+)\..*/i', $agent, $regs)) {
        return $regs[1];
    } elseif (preg_match('/Chrome\/(\d+)\..*/i', $agent, $regs)) {
        return $regs[1];
    } elseif ((strpos($agent, 'Chrome') == false) && preg_match('/Safari\/(\d+)\..*$/i', $agent, $regs)) {
        return $regs[1];
    } else {
        return 'Unknown';
    }
}

/**
 * 获取用户所使用浏览器的名称及版本
 * @param string $separator 分隔符
 * @return string
 * @author KingRainy <kingrainy@163.com>
 */
function getBrowser($separator = '')
{
    return getBrowserName() . $separator . getBrowserVer();
}

/**
 * 导出数据为excel
 * @param array $data 二维数组,结构如同从数据库查出来的数组
 * @param array $title 表格的第一行标题,一维数组,如果为空则没有标题
 * @param string $filename 文件名,如果为空则为当前日期时间
 * @return bool
 * @author KingRainy <kingrainy@163.com>
 */
function exportExcel($data = array(), $title = array(), $filename = '')
{
    if (empty($data)) {
        return false;
    }
    if (empty($filename)) {
        $filename = date('YmdHis');
    }
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Type: application/vnd.ms-excel");
    header("Accept-Ranges: bytes");
    header("Content-Disposition: attachment; filename=" . $filename . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    if (!empty($title)) {
        echo iconv("UTF-8", "GBK", implode("\t", $title)) . "\n";
    }
    foreach ($data as $values) {
        echo iconv("UTF-8", "GBK", implode("\t", $values)) . "\n";
    }
    return true;
}

/**
 * 加解密函数
 * @param string $string 字符串
 * @param string $operation DECODE为解密，其它表示加密
 * @param string $key 密钥
 * @param int $expiry 密文有效期
 * @return string 加解密后的字符串
 */
function authCode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key ? $key : \core\service\security\KeyConfig::SECRET_KEY_AES_LESTORE);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 获取当前页面URL
 * @return string
 * @author KingRainy <kingrainy@163.com>
 */
function getCurrUrl()
{
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
}

/**
 * 四舍五入
 * @param float|int|string $var 数值
 * @return string 返回四舍五入后的值
 * @author KingRainy <kingrainy@163.com>
 */
function floatNumber($var = 0.00)
{
    return number_format(floatval(round($var, 2)), 2);
}

/**
 * 友好显示距离
 * @param int|float|string $distance 距离
 * @return string
 * @author KingRainy <kingrainy@163.com>
 */
function showDistance($distance)
{
    if (!$distance) {
        return false;
    }
    if ($distance < 1000) {
        return round($distance) == 0 ? '1m' : round($distance) . 'm';
    } else {
        return floatval(round($distance / 1000, 2)) . 'km';
    }
}

/**
 * 获取指定月份的第一天开始和最后一天结束的时间戳
 * @param string|int $y 年
 * @param string|int $m 月
 * @return array
 * @author KingRainy <kingrainy@163.com>
 */
function mFristAndLast($y = '', $m = '')
{
    if ($y == '') {
        $y = date('Y');
    }
    if ($m == '') {
        $m = date('m');
    }
    $m = sprintf('%02d', intval($m));
    $y = str_pad(intval($y), 4, '0', STR_PAD_RIGHT);
    $m > 12 || $m < 1 ? $m = 1 : $m = $m;
    $firstday = strtotime($y . $m . '01000000');
    $firstdaystr = date('Y-m-01', $firstday);
    $lastday = strtotime(date('Y-m-d 23:59:59', strtotime($firstdaystr . ' + 1 month -1 day')));
    return array(
        'firstday' => $firstday,
        'lastday' => $lastday
    );
}

/**
 * 计算两个日期之间相差的天数 (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param $day1
 * @param $day2
 * @return float|int
 * @author KingRainy <kingrainy@163.com>
 */
function diffBetweenTwoDays($day1, $day2)
{
    $second1 = strtotime($day1);
    $second2 = strtotime($day2);
    if ($second1 < $second2) {
        $tmp = $second2;
        $second2 = $second1;
        $second1 = $tmp;
    }
    return ($second1 - $second2) / 86400;
}

/**
 * 计算两个日期直接相差月份
 * @param $day1
 * @param $day2
 * @return float|int
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:34
 */
function diffBetweenTwoMonths($day1, $day2)
{
    $second1 = strtotime($day1);
    $second2 = strtotime($day2);
    list($date_1['y'], $date_1['m']) = explode("-", date('Y-m', $second1));
    list($date_2['y'], $date_2['m']) = explode("-", date('Y-m', $second2));
    return abs($date_1['y'] - $date_2['y']) * 12 + $date_2['m'] - $date_1['m'];
}

/**
 * 获取指定时间戳开始小时的时间戳
 * @param null $time
 * @return false|int
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:29
 */
function beginOfHour($time = null)
{
    if (!$time) {
        $time = time();
    }
    return strtotime(date('Y-m-d H:00:00', $time));
}

/**
 * 获取指定时间戳结束小时的时间戳
 * @param null $time
 * @return false|int
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:29
 */
function endOfHour($time = null)
{
    if (!$time) {
        $time = time();
    }
    return strtotime(date('Y-m-d H:59:59', $time));
}

/**
 * 获取指定时间戳开始天的时间戳
 * @param null $time
 * @return false|int
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:30
 */
function beginOfDay($time = null)
{
    if (!$time) {
        $time = time();
    }
    return strtotime(date('Y-m-d 00:00:00', $time));
}

/**
 * 获取指定时间戳结束天的时间戳
 * @param null $time
 * @return false|int
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:31
 */
function endOfDay($time = null)
{
    if (!$time) {
        $time = time();
    }
    return strtotime(date('Y-m-d 23:59:59', $time));
}

/**
 * 获取指定时间戳开始月的时间戳
 * @param null $time
 * @return false|int
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:31
 */
function beginOfMonth($time = null)
{
    if (is_null($time)) {
        $time = time();
    }
    return strtotime(date('Y-m-01 00:00:00', $time));
}

/**
 * 获取指定时间戳结束月的时间戳
 * @param null $time
 * @return false|int
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:31
 */
function endOfMonth($time = null)
{
    if (is_null($time)) {
        $time = time();
    }
    return strtotime(date("Y-m-d 23:59:59", $time));
}

/**
 * 获取指定时间戳开始年的时间戳
 * @param null $time
 * @return false|int
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:32
 */
function beginOfYear($time = null)
{
    if (!$time) {
        $time = time();
    }
    return strtotime(date('Y-01-01 00:00:00', $time));
}

/**
 * 获取指定时间戳结束年的时间戳
 * @param null $time
 * @return false|int
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:32
 */
function endOfYear($time = null)
{
    if (!$time) {
        $time = time();
    }
    $year = date('Y', $time);
    $year += 1;
    return strtotime("{$year}-01-01 00:00:00") - 1;
}

/**
 * 判断是否在微信中打开
 * @return bool
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/3/16 15:39
 */
function is_Wechat()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

/**
 * 是否为json
 * @param $string
 * @param $strip_bom
 * @return bool
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:38
 */
function is_json($string, $strip_bom = true)
{
    if ($strip_bom) {
        json_decode(trim($string, chr(239) . chr(187) . chr(191)));
    } else {
        json_decode($string);
    }
    return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * 是否为xml
 * @param $xml
 * @return bool
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:39
 */
function is_xml($xml)
{
    $xml_parser = xml_parser_create();
    if (!xml_parse($xml_parser, $xml, true)) {
        xml_parser_free($xml_parser);
        return false;
    } else {
        return true;
    }
}

/**
 * 金额转换为元
 * @param $price
 * @return mixed
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:37
 */
function price2Yuan($price)
{
    return str_replace(',', '', number_format($price / 100, 2));
}

/**
 * 金额转换为分
 * @param $price
 * @return float|int
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:37
 */
function price2Fen($price)
{
    return $price * 100;
}

/**
 * 二进制转文本
 * @param $bin_str
 * @return string
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:38
 */
function bin2text($bin_str)
{
    $text_str = '';
    $chars = explode("\n", chunk_split(str_replace("\n", '', $bin_str), 8));
    $_I = count($chars);
    for ($i = 0; $i < $_I; $text_str .= chr(bindec($chars[$i])), $i++) ;
    return $text_str;
}

/**
 * 文本转二进制
 * @param $txt_str
 * @return string
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:38
 */
function text2bin($txt_str)
{
    $len = strlen($txt_str);
    $bin = '';
    for ($i = 0; $i < $len; $i++) {
        $bin .= strlen(decbin(ord($txt_str[$i]))) < 8 ? str_pad(decbin(ord($txt_str[$i])), 8, 0, STR_PAD_LEFT) : decbin(ord($txt_str[$i]));
    }
    return $bin;
}

/**
 * 异步将远程链接上的内容(图片或内容)写入到本地
 * @param string $url
 * @param string $saveName
 * @param string $path
 * @return boolean
 * @author KingRainy <kingrainy@163.com>
 */
function put_file_from_url_content($url, $saveName, $path)
{
    set_time_limit(0); //设置运行时间为无限制
    if ($url) {
        $url = trim($url);
    } else {
        return false;
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $file = curl_exec($curl);
    curl_close($curl);
    $filename = $path . $saveName;
    $write = @fopen($filename, 'w');
    if ($write == false) {
        return false;
    }
    if (fwrite($write, $file) == false) {
        return false;
    }
    if (fclose($write) == false) {
        return false;
    }
    return true;
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pk ID名
 * @param string $pid 父节点ID
 * @param string $child child标记字段
 * @param int $root 根节点值
 * @return array
 */
function generateTree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    $tree = array();
    $packData = array();
    foreach ($list as $data) {
        $packData[$data[$pk]] = $data;
    }
    foreach ($packData as $key => $val) {
        if ($val[$pid] == $root) {
            $tree[] = &$packData[$key];
        } else {
            $packData[$val[$pid]][$child][] = &$packData[$key];
        }
    }
    return $tree;
}

/**
 * 获取设备类型
 * @return string
 * @author KingRainy <kingrainy@163.com>
 */
function getDeviceType()
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $is_pc = (strpos($agent, 'windows nt')) ? true : false;
    $is_iphone = (strpos($agent, 'iphone')) ? true : false;
    $is_ipad = (strpos($agent, 'ipad')) ? true : false;
    $is_android = (strpos($agent, 'android')) ? true : false;
    if ($is_pc) {
        return 'PC';
    } elseif ($is_iphone) {
        return 'iPhone';
    } elseif ($is_ipad) {
        return 'iPad';
    } elseif ($is_android) {
        return 'Android';
    } else {
        return '未知';
    }
}

/**
 * 删除文件夹及文件夹内所有文件
 * @param string $dir 目录
 * @return bool
 */
function delDir($dir)
{
    //先删除目录下的文件：
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
            $fullpath = $dir . "/" . $file;
            if (!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }
    closedir($dh);
    //删除当前文件夹：
    if (rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 将图片转换成base64编码
 * @param string $image_file
 * @return string
 * @author KingRainy <kingrainy@163.com>
 */
function base64EncodeImage($image_file)
{
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}

/**
 * @notes: 将参数数组格式化成url参数
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/8/31
 * @param array $params
 * @param bool $flag
 * @return string
 */
function arrayToUrlParams(array $params, $flag = false)
{
    //ASCII码从小到大排序
    if ($flag == true) {
        ksort($params);
        reset($params);
    }
    $data = [];
    foreach ($params as $k => $v) {
        $data[] = $k . '=' . $v;
    }

    $string = implode('&', $data);

    return $string;

}

/**
 * Ajax方式返回数据到客户端
 * @param mixed $data 要返回的数据
 * @param String $type AJAX返回数据格式
 * @param integer $json_option 传递给json_encode的option参数
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/7 19:00
 */
function ajaxReturn($data, $type = 'JSON', $json_option = 0)
{
    ob_clean();
    switch (strtoupper($type)) {
        case 'JSON':
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($data, $json_option));
        case 'XML':
            header('Content-Type:text/xml; charset=utf-8');
            exit(xml_encode($data));
        case 'EVAL':
            header('Content-Type:text/html; charset=utf-8');
            exit($data);
        default:
    }
}

/**
 * 将数组拼接成字符串
 * @param $array
 * @return null|string
 */
function array2StringWithQuotes($array)
{
    if ($array && count($array) > 0) {
        $uuids = $array;
        $str = null;
        for ($i = 0; $i < count($uuids); $i++) {

            $str = $str . "'" . $uuids[$i] . "'";
            if ($i < count($uuids) - 1) {
                $str = $str . ",";
            }
        }
        return $str;
    }
    return null;
}

/**
 * 返回数组维度
 * @param $array
 * @return int|mixed
 */
function array_depth($array)
{
    if (!is_array($array)) {
        return 0;
    }
    $max_depth = 1;
    foreach ($array as $value) {
        if (is_array($value)) {
            $depth = array_depth($value) + 1;
//            if ($depth > $max_depth) {
//                $max_depth = $depth;
//            }
            $max_depth = max($max_depth, $depth);
        }
    }
    return $max_depth;
}

/**
 * 获取HTTP请求头内容
 * @param $header
 * @return mixed
 * @athor wangyu <wangyu@ledouya.com>
 * @createTime 2018/4/13 15:40
 */
function getHttpHeader($header)
{
    if (!empty($header)) {
        return !empty($_SERVER['HTTP_' . strtoupper($header)]) ? $_SERVER['HTTP_' . strtoupper($header)] : false;
    }
}

/**
 * 随机生成字符串
 * @param mixed $length 长度
 * @param bool $numeric 仅为数字
 * @return string
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/3/21 18:40
 */
function random($length, $numeric = false)
{
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    if ($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

/**
 * 生成0~1随机小数
 * @param Int $len
 * @param Int $min
 * @param Int $max
 * @return Float
 */
function randomFloat($len = 2, $min = 0, $max = 1)
{
    return round($min + mt_rand() / mt_getrandmax() * ($max - $min), $len);
}

/**
 * 调用系统随机数文件 /dev/urandom 生成随机数
 * @param int $len
 * @return string
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 8:24
 */
function randomFromDev($len = 32)
{

    $fp = @fopen('/dev/urandom', 'rb');
    $result = '';
    if ($fp !== FALSE) {
        $result .= @fread($fp, $len);
        @fclose($fp);
    } else {
        trigger_error('Can not open /dev/urandom.');
    }
    // convert from binary to string
    $result = base64_encode($result);
    // remove none url chars
    $result = strtr($result, '+/', '-_');
    // Remove = from the end
    $result = str_replace('=', ' ', $result);
    return trim($result);
}

/**
 * 生成32位随机字符串
 * @return string
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 15:19
 */
function uuid32()
{
    $uuid = md5(uniqid(md5(microtime(true)), true));
    return $uuid;
}

/**
 * 获取文件后缀
 * @param $file
 * @return mixed
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/12 15:45
 */
function getExt($file)
{
    $temp = pathinfo($file);
    return isset($temp['extension']) ? $temp['extension'] : false;
}

/**
 * 订单号生成（32位字符串）
 * @param null $custom 自定义字符，需为大于0小于100的数字，可用来做业务方自定义数据使用
 * @return string
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/4/25 15:50
 */
function trade_no($custom = null)
{
    if (!empty($custom) && $custom > 0 && $custom < 99) {
        $custom = str_pad($custom, 2, '0', STR_PAD_LEFT);
    } else {
        $custom = rand(10, 99);
    }
    list($usec, $sec) = explode(' ', microtime());
    $usec = substr(str_replace('0.', '', $usec), 0, 6);
    $str = substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 10);
    return date('YmdHis') . $usec . $str . $custom;
}

/**
 * 获取当前时间
 * @param string $format 格式化
 * @return array
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/3/21 19:11
 */
function datetime($format = 'Y-m-d H:i:s')
{
    list($usec, $sec) = explode(' ', microtime());
    $usec = substr(str_replace('0.', '', $usec), 0, 6);
    return [
        'sec' => $sec,
        'usec' => $usec,
        'date' => date($format, $sec),
        'time' => date('Y-m-d H:i:s', $sec) . '.' . $usec
    ];
}

/**
 * 编号生成（20为纯数字）
 * @param null $custom 自定义字符，需为大于0小于100的数字，可用来做业务方自定义数据使用
 * @return string
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/4/25 15:50
 */
function serial_number($custom = null)
{
    if (!empty($custom) && $custom > 0 && $custom < 99) {
        $custom = str_pad($custom, 2, '0', STR_PAD_LEFT);
    } else {
        $custom = rand(10, 99);
    }
    $dateA = date('a') == 'am' ? '01' : '02';
    return date('Ymd') . random(8, true) . $dateA . $custom;
}

/**
 * 命令行输出彩色化
 * @param string $text 文本内容
 * @param string $status 状态
 * @return string
 * @throws Exception
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/22 17:19
 */
function commandLineColorize($text, $status = 'NOTE')
{
    $instructions_list = [
        '37', //白色前景
    ];
    $status = strtoupper($status);
    switch ($status) {
        case 'ERROR':
            $background_color = '41'; //红色背景
            break;
        case 'SUCCESS':
            $background_color = '42'; //绿色背景
            break;
        case 'WARNING':
            $background_color = '43'; //黄色背景
            break;
        case 'NOTE':
            $background_color = '44'; //蓝色背景
            break;
        default:
            throw new \Exception('Invalid status: ' . $status);
    }
    $instructions_list[] = $background_color;
    $instructions = implode(';', $instructions_list);
    return chr(27) . '[' . $instructions . 'm' . $text . chr(27) . '[0m';
}

/**
 * 命令行输出
 * @param string $message 内容
 * @param string $status 状态
 * @throws Exception
 * @author wangyu <wangyu@ledouya.com>
 * @createTime 2018/5/22 17:21
 */
function commandLineOutput($message, $status = 'NOTE')
{
    if (is_array($message)) {
        $message = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } elseif (is_object($message)) {
        $message = json_encode($message, JSON_FORCE_OBJECT);
    }
    echo commandLineColorize($message, $status) . PHP_EOL;
}


/**
 * @notes: 参数过滤
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/8/30
 * @param $filter
 * @param $data
 * @return array
 */
function array_map_recursive($filter, $data)
{
    $result = array();
    foreach ($data as $key => $val) {
        $result[$key] = is_array($val) ? array_map_recursive($filter, $val) : call_user_func($filter, $val);
    }
    return $result;
}

if (!function_exists('secure_filter')) {
    /**
     * 安全过滤
     * @param string $value 值
     */
    function secure_filter(&$value)
    {
        // 过滤查询特殊字符
        if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
            $value .= ' ';
        }
    }
}

