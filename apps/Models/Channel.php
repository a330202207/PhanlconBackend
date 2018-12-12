<?php
/**
 * @purpose: 渠道模型
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/12/7
 * @version: 1.0
 */

namespace Apps\Models;

use Apps\Providers\ModelsProvider;
use Phalcon\Paginator\Adapter\QueryBuilder;

class Channel extends ModelsProvider
{
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('pg_channel');
        parent::initialize();
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'pg_channel';
    }

    /**
     * @notes: 获取渠道列表
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @param array $params
     * @return \stdClass
     * @version: 1.0
     */
    public function getChannelList(array $params)
    {
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from('Apps\Models\Channel');
        $builder->andwhere("is_del = :is_del:", ['is_del' => 0]);

        //订单ID
        if (isset($params['name']) && !empty($params['name'])) {
            $builder->andwhere("name like :name:", ['name' => "%{$params['name']}%"]);
        }

        $paginator = new QueryBuilder([
            'builder' => $builder,
            'limit' => $params['page_set'],
            'page' => $params['page'],
        ]);

        $page = $paginator->getPaginate();

        return $page;
    }
}