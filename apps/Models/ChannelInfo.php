<?php
/**
 * @purpose: 渠道信息模型
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/12/7
 * @version: 1.0
 */

namespace Apps\Models;

use Apps\Providers\ModelsProvider;
use Phalcon\Paginator\Adapter\QueryBuilder;

class ChannelInfo extends ModelsProvider
{
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('pg_channel_info');
        parent::initialize();
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'pg_channel_info';
    }

    /**
     * @notes: 获取渠道信息
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/7
     * @param array $params
     * @return \stdClass
     * @version: 1.0
     */
    public function getChannelInfo(array $params)
    {
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(['c' => 'Apps\Models\ChannelInfo']);
        $builder->columns(['p.name,c.id,c.pay_name,c.min,c.max,c.status,c.created_at,c.updated_at']);
        $builder->leftJoin("Apps\Models\PayType", 'p.id = c.pay_id', 'p');
        $builder->where("c.channel_id = :channel_id: AND c.is_del = 0", ['channel_id' => $params['id']]);
        $builder->orderBy('c.created_at DESC');
        $paginator = new QueryBuilder([
            'builder' => $builder,
            'limit' => $params['page_set'],
            'page' => $params['page'],
        ]);

        $page = $paginator->getPaginate();

        return $page;
    }
}