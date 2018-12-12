<?php
/**
 * @purpose: Redis 操作数据类
 * @author: NedRen<ned@pproject.co>
 * @date:2018/8/30
 * @version: 1.0
 */

namespace Apps\Librarys;

use Phalcon\Di;

class Redis
{
    /**
     * 类单例
     * @var object
     */
    protected static $_instance;

    /**
     * Redis的连接句柄
     *
     * @var object
     */
    private $redis;

    /**
     * 私有化构造函数，防止类外实例化
     */
    public function __construct()
    {
        try {
            $this->redis = Di::getDefault()->getRedis();
            if (!$this->redis) {
                throw new \Exception('Redis链接失败', -1);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 私有化克隆函数，防止类外克隆对象
     */
    private function __clone()
    {

    }

    /**
     * @notes: 类的唯一公开静态方法，获取类单例的唯一入口
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/30
     * @return object|Redis
     */
    public static function getRedis()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 获取redis的连接实例
     *
     * @return Redis
     */
    public function getRedisConn()
    {
        return $this->redis;
    }

    /********************* Key(键) ************************/

    /**
     * 从Redis中删除指定的key
     * @param   string | array
     * @return  int  被删除 key 的数量
     */
    public function del($keys)
    {
        return $this->redis->del($keys);
    }

    /**
     * 检查 key 是否存在
     * @param   string $key
     * @return  bool    如果存在返回 TRUE, 不存在返回 FALSE
     */
    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * 设置key过期时间，以秒为单位
     * @param  string $key 要设置过期时间的key
     * @param  int $expireTime 过期时间,秒为单位，例如设置有效期为1小时，$expireTime = 3600
     * @return bool    成功返回 TRUE，失败返回 FALSE
     */
    public function setExpireTime($key, $expireTime)
    {
        $nowTime = time();
        return $this->redis->expireAt($key, $nowTime + $expireTime);
    }

    /********************* String(字符串)************************/

    /**
     * 将 value 追加到 key 原来的值的末尾
     * key 不存在，将给定 key 设为 value
     * @param   string $key
     * @param   string $value
     * @return  int:    追加 value 之后， key 中字符串的长度
     */
    public function append($key, $value)
    {
        return $this->redis->append($key, $value);
    }

    /**
     * 原子性递减一个 key 的值
     * @param   string $key key
     * @param   int $value 减量
     * @return  int     减去 value 之后，key 的值
     */
    public function decr($key, $value = 0)
    {
        if (empty($value)) {
            $result = $this->redis->decr($key);
        } else {
            $result = $this->redis->decrBy($key, $value);
        }

        return $result;
    }

    /**
     * 获取指定 key 中存储的值
     * @param   string $key
     * @return  string | bool: 如果 key 不存在，返回FALSE,否则返回 key 中存储的值
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * 原子性递增一个 key 的值
     * @param   string $key key
     * @param   int $value 增量
     * @return  int:    加上 value 之后，key 的值
     */
    public function incr($key, $value = 0)
    {
        if (empty($value)) {
            $result = $this->redis->incr($key);
        } else {
            $result = $this->redis->incrBy($key, $value);
        }

        return $result;
    }

    /**
     * 获取所有(一个或多个)给定 key 的值
     * @param   string $key
     * @return  array
     */
    public function mget($key)
    {
        return $this->redis->mget($key);
    }

    /**
     * 设置一个或多个 key-value 对
     * @param   array $array
     * @return  bool
     */
    public function mset($array)
    {
        return $this->redis->mset($array);
    }

    /**
     * 设置一个或多个 key-value 对，给定 key 都不存在
     * @param   array $array
     * @return  int    设置成功返回 1，失败返回 0
     */
    public function msetnx($array)
    {
        return $this->redis->msetnx($array);
    }

    /**
     * 将字符串值 value 关联到 key
     * @param   string $key
     * @param   string $value
     * @return  bool: TRUE 添加成功
     */
    public function set($key, $value)
    {
        return $this->redis->set($key, $value);
    }

    /**
     * 获取 key 所储存的 String 值的长度
     * @param   string $key
     * @return  int  返回String 值的长度，当 key 不存在时，返回 0
     */
    public function strlen($key)
    {
        return $this->redis->strlen($key);
    }

    /**
     * @notes: 设置一个有过期时间的key
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/30
     * @param $key
     * @param $expire
     * @param $value
     * @return mixed
     */
    public function setex($key,$expire,$value)
    {
        return $this->redis->setex($key,$expire,$value);
    }

    /********************* Hash（哈希表）************************/

    /**
     * 删除 Hash 表 key 中 field 域
     * @param   string $key
     * @param   string $value
     * @return  int     返回已删除字段数
     */
    public function hDel($key, $value)
    {
        return $this->redis->hDel($key, $value);
    }

    /**
     * 查看 Hash 表 key 中 field 域是否存在
     * @param   string $key
     * @param   string $value
     * @return  int     存在返回 1，不存在返回 0
     */
    public function hExists($key, $value)
    {
        return $this->redis->hExists($key, $value);
    }

    /**
     * 获取 Hash 表 key 中 field 域的值
     * @param   string $key
     * @param   string $value
     * @return  string
     */
    public function hGet($key, $value)
    {
        return $this->redis->hGet($key, $value);
    }

    /**
     * 获取 Hash 表 key 中，所有的域和值
     * @param   string $key
     * @return  array   当 key 不存在时，返回空表
     */
    public function hGetAll($key)
    {
        return $this->redis->hGetAll($key);
    }

    /**
     * 获取 Hash 表 key 中，所有的域
     * @param   string $key
     * @return  array   当 key 不存在时，返回空表
     */
    public function hKeys($key)
    {
        return $this->redis->hKeys($key);
    }

    /**
     * 获取 Hash 表 key 中域的数量
     * @param   string $key
     * @return  int     当 key 不存在时，返回FALSE
     */
    public function hLen($key)
    {
        return $this->redis->hLen($key);
    }

    /**
     * 获取 Hash 表 key 中，一个或多个给定域的值
     * @param   string $hash
     * @param   array $array
     * @return  array
     */
    public function hMget($hash, $array)
    {
        return $this->redis->hMget($hash, $array);

    }

    /**
     * 同时将多个 field-value (域-值)对设置到 Hash 表 key 中
     * @param   string $hash
     * @param   array $array
     * @return  bool
     * 此命令会覆盖 Hash 表中已存在的域
     */
    public function hMset($hash, $array)
    {
        return $this->redis->hMset($hash, $array);

    }

    /**
     * 将 Hash 表key 中的域 field 的值设为 value
     * @param   string $hash
     * @param   string $key
     * @param   string $value
     * @return  int
     * 如果 field 是 Hash 表中的一个新建域，并且值设置成功，返回 1
     * 如果 Hash 表中域 field 已经存在且旧值已被新值覆盖，返回 0
     */
    public function hSet($hash, $key, $value)
    {
        return $this->redis->hSet($hash, $key, $value);
    }

    /**
     * 将 Hash 表key 中的域 field 的值设为 value,当域 field 不存在
     * 若域 field 已经存在，该操作无效
     * @param   string $hash
     * @param   string $key
     * @param   string $value
     * @return  bool
     */
    public function hSetNx($hash, $key, $value)
    {
        return $this->redis->hSetNx($hash, $key, $value);
    }

    /**
     * 获取 Hash 表 key 中所有域的值
     * @param   string $key
     * @return  array
     * 当 key 不存在时，返回一个空表
     */
    public function hVals($key)
    {
        return $this->redis->hVals($key);
    }

    /********************* List（列表）************************/

    /**
     * 获取 列表 key 中，下标为 index 的元素
     * @param   string $key
     * @param   int $index
     * @return  String
     * 如果 index 参数的值不在列表的区间范围内(out of range)，返回 FALSE
     */
    public function lIndex($key, $index)
    {
        return $this->redis->lIndex($key, $index);
    }

    /**
     * 将值 value 插入到列表 key 当中，位于值 pivot 之前或之后
     * @param   string $key
     * @param   int $type 1:AFTER，其他:BEFORE
     * @param   string $pivot
     * @param   string $value
     * @return  int     返回插入操作完成之后，列表的长度
     * 如果没有找到 pivot ，返回 -1
     * 如果 key 不存在或为空列表，返回 0
     */
    public function lInsert($key, $type, $pivot, $value)
    {
        $type = $type == 1 ? Redis::AFTER : Redis::BEFORE;
        return $this->redis->lInsert($key, $type, $pivot, $value);
    }

    /**
     * 获取列表 key 的长度
     * @param   string $key
     * @return  int     列表 key 的长度
     */
    public function lLen($key)
    {
        return $this->redis->lLen($key);
    }

    /**
     * 删除列表的第一个元素
     * @param   string $key
     * @return  string  返回列表的头元素
     * 当 key 不存在时，返回 FALSE
     */
    public function lPop($key)
    {
        return $this->redis->lPop($key);
    }

    /**
     * 将一个或多个值 value 插入到列表 key 的表头
     * @param   string $key
     * @param   string $value
     * @return  int     返回执行 lPush 操作后，列表的长度
     * 如果 key 不存在，一个空列表会被创建并执行 lPush 操作
     * 当 key 存在但不是列表类型时，返回 FALSE
     */
    public function lPush($key, $value)
    {
        return $this->redis->lPush($key, $value);
    }

    /**
     * 当 key 存在并且是一个列表时，将 value 插入到列表 key 的表头
     * @param   string $key
     * @param   string $value
     * @return  int     返回执行 lPushx 操作后，列表的长度
     * 如果 key 不存在，lPushx 命令什么也不做
     * 当 key 存在但不是列表类型时，返回 FALSE
     */
    public function lPushx($key, $value)
    {
        return $this->redis->lPushx($key, $value);
    }

    /**
     * 获取列表 key 中指定区间内的元素，区间以偏移量 start 和 end 指定。
     * @param   string $key
     * @param   int $start 获取成员的起始位置
     * @param   int $end 获取成员的结束位置
     * @return  array   返回指定区间内的元素
     */
    public function lRange($key, $start, $end)
    {
        return $this->redis->lRange($key, $start, $end);
    }

    /**
     * 根据 count 的值，移除列表中与 value 相等的元素
     * @param   string $key
     * @param   string $value
     * @param   int $count
     * @return  int     被移除元素的数量
     * count > 0 : 从表头开始向表尾搜索，移除与 value 相等的元素，数量为 count
     * count < 0 : 从表尾开始向表头搜索，移除与 value 相等的元素，数量为 count 的绝对值
     * count = 0 : 移除表中所有与 value 相等的值
     * 因为不存在的 key 被视作空表(empty list)，所以当 key 不存在时， lRem 命令总是返回 0 。
     */
    public function lRem($key, $value, $count = 0)
    {
        return $this->redis->lRem($key, $value, $count);
    }

    /**
     * 将列表 key 下标为 index 的元素的值设置为 value
     * @param   string $key
     * @param   int $index
     * @param   int $value
     * @return  bool
     * 当 index 参数超出范围，或对一个空列表( key 不存在)进行 lSet 时，返回 FALSE
     */
    public function lSet($key, $index, $value)
    {
        return $this->redis->lSet($key, $index, $value);
    }

    /**
     * 删除 List 的最后一个元素
     * @param   string $key
     * @return  string  返回列表的尾元素
     * 当 key 不存在时，返回 FALSE
     */
    public function rPop($key)
    {
        return $this->redis->rPop($key);
    }

    /**
     * 将一个或多个值 value 插入到列表 key 的表尾(最右边)
     * @param   string $key
     * @param   string $value
     * @return  int     执行 rPush 操作后，列表的长度
     * 如果 key 不存在，一个空列表会被创建并执行 rPush 操作
     * 当 key 存在但不是列表类型时，返回 FALSE
     */
    public function rPush($key, $value)
    {
        return $this->redis->rPush($key, $value);
    }

    /**
     * 当 key 存在并且是一个列表时，将 value 插入到列表 key 的表尾(最右边)
     * @param   string $key
     * @param   string $value
     * @return  int     执行 rPushx 操作后，列表的长度
     * 如果 key 不存在，rPushx 命令什么也不做
     * 当 key 存在但不是列表类型时，返回 FALSE
     */
    public function rPushx($key, $value)
    {
        return $this->redis->rPushx($key, $value);
    }

    /********************* Set（集合）************************/

    /**
     * 将一个或多个 value 值加入到集合 key 当中，已经存在于集合的 value 值将被忽略
     * @param   string $key
     * @param   string $value
     * @return  int     被添加到集合中的新元素的数量，不包括被忽略的元素。
     */
    public function sAdd($key, $value)
    {
        return $this->redis->sAdd($key, $value);
    }

    /**
     * 获取集合 key 的基数(集合中元素的数量)
     * @param   string $key
     * @return  int
     * 当 key 不存在时，返回 0
     */
    public function sCard($key)
    {
        return $this->redis->sCard($key);
    }

    /**
     * 获取集合 key1 与 key2 的差集
     * @param   string $key1
     * @param   string $key2
     * @return  array
     * 当 key 不存在时为空集
     */
    public function sDiff($key1, $key2)
    {
        return $this->redis->sDiff($key1, $key2);
    }

    /**
     * 获取集合 key1 与 key2 的交集
     * @param   string $key1
     * @param   string $key2
     * @return  array
     * 当 key 不存在时为空集
     * 当 key1 或者 key2 其中为一个空集时，结果也为空集
     */
    public function sInter($key1, $key2)
    {
        return $this->redis->sInter($key1, $key2);
    }

    /**
     * 判断 value 是否存在 key 集合
     * @param   string $key
     * @param   string $value
     * @return  bool
     */
    public function sIsMember($key, $value)
    {
        return $this->redis->sIsMember($key, $value);
    }

    /**
     * 获取集合 key 中的所有成员
     * @param   string $key
     * @return  array
     */
    public function sMembers($key)
    {
        return $this->redis->sMembers($key);
    }

    /**
     * 删除集合 key 中的一个或多个 value 元素
     * @param   string $key
     * @param   string $value
     * @return  int     返回删除成功元素的数量
     */
    public function sRem($key, $value)
    {
        return $this->redis->sRem($key, $value);
    }

    /**
     * 获取集合 key1 与 key2 的并集
     * @param   string $key1
     * @param   string $key2
     * @return  array
     * 当 key 不存在时为空集
     */
    public function sUnion($key1, $key2)
    {
        return $this->redis->sUnion($key1, $key2);
    }

    /********************* SortedSet（有序集合）************************/

    /**
     * 将一个或多个 member 元素及其 score 值加入到有序集合 key
     * @param   string $key
     * @param   int|float $score
     * @param   string $member
     * @return  int
     * 如果 key 不存在，则创建一个空的有序集合并执行 zAdd 操作
     * 当 key 存在但不是有序集类型时，返回 FALSE
     */
    public function zAdd($key, $score, $member)
    {
        return $this->redis->zAdd($key, $score, $member);
    }

    /**
     * 获取有序集合 key 的基数
     * @param   string $key
     * @return  int
     * 当 key 不存在时，返回0
     */
    public function zCard($key)
    {
        return $this->redis->zCard($key);
    }

    /**
     * 获取有序集合 key 中， score 值在 min 和 max 之间(默认包括 score 值等于 min 或 max )的成员的数量
     * @param   string $key
     * @param   string $start
     * @param   string $end
     * @return  int
     */
    public function zCount($key, $start, $end)
    {
        return $this->redis->zCount($key, $start, $end);
    }

    /**
     * 删除有序集合指定的成员
     * @param   string $key
     * @param   string $member
     * @return  int     返回删除数量
     */
    public function zDelete($key, $member)
    {
        return $this->redis->zDelete($key, $member);
    }

    /**
     * 获取有序集合 key 中，指定区间内的成员
     * @param   string $key
     * @param   int $start
     * @param   int $end
     * @param   bool $withScore 是否返回 score
     * @param   int $order 1按照 score 倒序排列结果， 0 按 score 正序排列结果
     * @return  array
     */
    public function zRange($key, $start, $end, $withScore = TRUE, $order = 1)
    {
        if ($order == 1) {
            return $this->redis->zRevRange($key, $start, $end, $withScore);
        } else {
            return $this->redis->zRange($key, $start, $end, $withScore);
        }
    }

    /**
     * 获取有序集 key 中，成员 member 的 score 值
     * @param   string $key
     * @param   string $member
     * @return  string|float   返回 member 成员的 score 值
     */
    public function zScore($key, $member)
    {
        return $this->redis->zScore($key, $member);
    }

    /**
     * 以原子性增加成员的score值
     * @param   string $key 有序集合的 key
     * @param   string $member 要增加 score 的成员名称
     * @param   int $increment 要增加的 score 值（可以转换成双精度的值，可以为负数，负数就减去相应的值）
     * @return  string|float        返回 member 成员的新 score 值
     */
    public function zIncrBy($key, $member, $increment = 1)
    {
        return $this->redis->zIncrBy($key, $increment, $member);
    }

    /**
     * 关闭服务器链接
     */
    public function close()
    {
        return $this->redis->close();
    }

    /**
     * 关闭所有连接
     */
    public function closeAll()
    {
        foreach (static::$_instance as $o) {
            if ($o instanceof self)
                $o->close();
        }
    }

    /**
     * 返回一个随机key
     */
    public function randomKey()
    {
        return $this->redis->randomKey();
    }

    /**
     * 得到当前数据库ID
     * @return int
     */
    public function getDbId()
    {
        return $this->dbId;
    }

    /**
     * 返回当前密码
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * 返回当前地址
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * 返回当前端口号
     */
    public function getPort()
    {
        return $this->port;
    }

    /*********************事务的相关方法************************/

    /**
     * 取消当前链接对所有key的watch
     *  EXEC 命令或 DISCARD 命令先被执行了的话，那么就不需要再执行 UNWATCH 了
     */
    public function unwatch()
    {
        return $this->redis->unwatch();
    }

    /**
     * 开启一个事务
     * 事务的调用有两种模式Redis::MULTI和Redis::PIPELINE，
     * 默认是Redis::MULTI模式，
     * Redis::PIPELINE管道模式速度更快，但没有任何保证原子性有可能造成数据的丢失
     */
    public function multi($type = \Redis::MULTI)
    {
        return $this->redis->multi($type);
    }

    /**
     * 执行一个事务
     * 收到 EXEC 命令后进入事务执行，事务中任意命令执行失败，其余的命令依然被执行
     */
    public function exec()
    {
        return $this->redis->exec();
    }

    /**
     * 回滚一个事务
     */
    public function discard()
    {
        return $this->redis->discard();
    }

    /**
     * 测试当前链接是不是已经失效
     * 没有失效返回+PONG
     * 失效返回false
     */
    public function ping()
    {
        return $this->redis->ping();
    }

    /*********************自定义的方法,用于简化操作************************/

    /**
     * @notes: 得到一组的ID号
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/30
     * @param $prefix
     * @param $ids
     * @return array|bool
     */
    public function hashAll($prefix, $ids)
    {
        if ($ids == false)
            return false;
        if (is_string($ids))
            $ids = explode(',', $ids);
        $arr = array();
        foreach ($ids as $id) {
            $key = $prefix . '.' . $id;
            $res = $this->hGetAll($key);
            if ($res != false)
                $arr[] = $res;
        }

        return $arr;
    }

    /**
     * @notes: 生成一条消息，放在redis数据库中。使用0号库。
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/8/30
     * @param $lkey
     * @param $msg
     * @return string
     */
    public function pushMessage($lkey, $msg)
    {
        if (is_array($msg)) {
            $msg = json_encode($msg);
        }
        $key = md5($msg);

        //如果消息已经存在，删除旧消息，已当前消息为准
        //echo $n=$this->lRem($lkey, 0, $key)."\n";
        //重新设置新消息
        $this->lPush($lkey, $key);
        $this->setex($key, 3600, $msg);
        return $key;
    }

}