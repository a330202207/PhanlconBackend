<?php

namespace Apps\Librarys;

use Phalcon\Mvc\User\Component;

class InvironMent extends Component
{
    /**
     * @var array 环境配置
     */
    public static $hosts = [
        'xx' => 'prd',
        'xx' => 'test',
        'xx' => 'dev',
    ];

    /**
     * @notes: 获取环境
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/9/13
     * @return mixed
     * @version: 1.0
     */
    static function getInv()
    {
        if (isset(self::$hosts[gethostname()])) {
            return self::$hosts[gethostname()];
        } else {
            die("Inviroment error\nhostname: " . gethostname());
        }
    }
}
