<?php
/**
 * @purpose: 首页服务
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/22
 * @version: 1.0
 */


namespace Apps\Modules\Api\Services;


class IndexService extends BaseService
{
    /**
     * @notes: 获取首页信息
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/10/22
     * @param string $string
     * @return string
     * @version: 1.0
     */
    public static function getIndexInfo(string $string)
    {
        return $string;
    }
}