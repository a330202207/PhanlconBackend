<?php
/**
 * @purpose: 模型供应商
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/20
 * @version: 1.0
 */


namespace Apps\Providers;

use Apps\Models\Payment;
use Phalcon\Mvc\Model;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

abstract class ModelsProvider extends Model
{
    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setup(
            [
                'notNullValidations' => false
            ]
        );
    }

    /**
     * @notes: 批量添加
     * @author: KevinRen<330202207@qq.com>
     * @date: 2018/4/9
     * @param $data array
     * @return mixed
     * @throws \Exception
     *
     * $data = [['id' => 1, 'name' => 'test1'], ['id' => 2, 'name' => 'test2']]
     *
     * batchInsert($data)
     *
     * INSERT INTO `table`( 'id','name' ) values ('1','test1') , ('2','test2')
     *
     */
    public function batchInsert(array $data)
    {
        if (count($data) == 0) {
            throw new \Exception('参数错误');
        }

        $arrKeys = array_keys(reset($data));

        $fields = implode(',', array_map(function ($value) {
            return "`" . $value . "`";
        }, $arrKeys));

        foreach ($data as $key => $val) {
            $arrValues[$key] = implode(',', array_map(function ($value) {
                return "'" . $value . "'";
            }, $val));
        }

        $values = "(" . implode(') , (', array_map(function ($value) {
                return $value;
            }, $arrValues)) . ")";

        $sql = "INSERT INTO `%s`( %s ) values %s ";

        $sql = sprintf($sql, $this->getSource(), $fields, $values);

        //DI中注册的数据库服务名称为"db"
        $result = $this->getDI()->get('db')->execute($sql);

        if (!$result) {
            throw new \Exception('批量添加失败');
        }

        return $result;
    }

    /**
     * @notes: 模块验证消息
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/9/3
     * @return string
     */
    public function getStringMessages()
    {
        if (!$this->getMessages()) {
            return '';
        }
        $messages = [];
        foreach ($this->getMessages() as $message) {
            $messages[] = $message->getMessage();
        }
        return implode('; ', $messages);
    }
}