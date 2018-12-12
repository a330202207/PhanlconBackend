<?php
/**
 * @purpose: 渠道列表
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/12/10
 * @version: 1.0
 */

namespace Apps\Models;

use Apps\Providers\ModelsProvider;
use Phalcon\Paginator\Adapter\QueryBuilder;

class Admin extends ModelsProvider
{
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('pg_admin');
        parent::initialize();
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'pg_admin';
    }

    /**
     * @notes: 获取渠道列表
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/10
     * @param array $params
     * @return \stdClass
     * @version: 1.0
     */
    public function getAdminList(array $params)
    {
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from('Apps\Models\Admin');
        $builder->andwhere("is_del = :is_del:", ['is_del' => 0]);

        //订单ID
        if (isset($params['username']) && !empty($params['username'])) {
            $builder->andwhere("username like :username:", ['username' => "%{$params['username']}%"]);
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