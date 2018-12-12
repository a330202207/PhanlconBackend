<?php
/**
 * @purpose: 支付模型
 * @author: NedRen<ned@pproject.co>
 * @date:2018/8/23
 * @version: 1.0
 */
namespace Apps\Models;

use Apps\Providers\ModelsProvider;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Paginator\Adapter\QueryBuilder;

class Payment extends ModelsProvider
{

    /**
     * 
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $id;

    /**
     * 订单号ID
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $order_id;

    /**
     * 金额(单位分)
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $amount;

    /**
     * 调用支付平台类型；1：银河支付 2：Wispay支付 3：杉德支付
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $type;

    /**
     * 支付卡类型 1:储蓄卡 2:信用卡
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $card_type;

    /**
     * pay_type：1：网银支付、2：快捷支付、3：快捷H5、4：微信H5支付、5：微信扫码、6：微信公众号、7：支付宝H5支付、8：支付宝扫码支付、9：京东H5支付、10：京东钱包支付、11：京东扫码、12：银联H5支付、13：银联扫码支付、14：QQ钱包支付、15：QQ扫码支付、16：QQH5支付
     * @var integer
     * @Column(type="integer", length=2, nullable=false)
     */
    public $pay_type;

    /**
     * 支付状态 0：发起支付  1：支付完成  2：支付失败
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $status;

    /**
     * 请求来源名称
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $from_type;

    /**
     * 交易日期(yyyymmdd)
     * @var string
     * @Column(type="string", length=8, nullable=false)
     */
    public $trans_date;

    /**
     * 交易时间(HHmmss)
     * @var string
     * @Column(type="string", length=8, nullable=false)
     */
    public $trans_time;

    /**
     * 来源类型
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $channel;

    /**
     * 创建时间
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $created_at;

    /**
     * 更新时间
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $updated_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('pg_payment');
        parent::initialize();
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'pg_payment';
    }


    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();
        $validator->add(
            [
                'order_id',
                'amount',
                'card_type',
                'pay_type',
                'channel',
                'from_type',
                'type',
            ],
            new PresenceOf(
                [
                    'message' => [
                        'order_id' => 'The order_id is required',
                        'amount' => 'The amount is required',
                        'card_type' => 'The card_type is required',
                        'pay_type' => 'The pay_type is required',
                        'channel' => 'The channel is required',
                        'from_type' => 'The from_type is required',
                        'type' => 'The type is required',
                    ]
                ]
            )
        );
        $validator->add(
            [
                'amount',
                'type',
                'card_type',
                'pay_type',
            ],
            new Numericality(
                [
                    "message" => [
                        "amount"  => "amount is not numeric",
                        "type"  => "amount is not numeric",
                        "card_type"  => "card_type is not numeric",
                        "pay_type"  => "pay_type is not numeric",
                    ]
                ]
            )
        );
        return $this->validate($validator);
    }

    /**
     * Independent Column Mapping.
     * Keys are the real names in the table and the values their names in the application
     *
     * @return array
     */
    public function columnMap()
    {
        return [
            'id' => 'id',
            'order_id' => 'order_id',
            'amount' => 'amount',
            'type' => 'type',
            'card_type' => 'card_type',
            'pay_type' => 'pay_type',
            'status' => 'status',
            'from_type' => 'from_type',
            'trans_date' => 'trans_date',
            'trans_time' => 'trans_time',
            'channel' => 'channel',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ];
    }

    /**
     * @notes: 获取支付订单
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/4
     * @param array $params
     * @return \stdClass
     * @version: 1.0
     */
    public function getOrderList(array $params)
    {
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from('Apps\Models\Payment');

        //订单ID
        if (isset($params['id']) && !empty($params['id'])) {
            $builder->andwhere("id = :id:", ['id' => $params['id']]);
        }

        //订单号ID
        if (isset($params['order_id']) && !empty($params['order_id'])) {
            $builder->andwhere("order_id = :order_id:", ['order_id' => $params['order_id']]);
        }

        //支付中
        if (isset($params['status']) && $params['status'] == 1) {
            $builder->andwhere("status = :status:", ['status' => 0]);
        }

        //已完成
        if (isset($params['status']) && $params['status'] == 2) {
            $builder->andwhere("status = :status:", ['status' => 1]);
        }

        //已失败
        if (isset($params['status']) && $params['status'] == 3) {
            $builder->andwhere("status = :status:", ['status' => 2]);
        }

        //金额
        if (isset($params['amount']) && !empty($params['amount'])) {
            $builder->andwhere("amount = :amount:", ['amount' => $params['amount']]);
        }

        //支付类型
        if (isset($params['pay_type']) && !empty($params['pay_type'])) {
            $builder->andwhere("pay_type = :pay_type:", ['pay_type' => $params['pay_type']]);
        }

        //渠道
        if (isset($params['type']) && !empty($params['type'])) {
            $builder->andwhere("type = :type:", ['type' => $params['type']]);
        }

        //时间范围
        if (isset($params['start_time']) && !empty($params['start_time']) && isset($params['end_time']) && !empty($params['end_time'])) {
            $start_time = strtotime($params['start_time']);
            $end_time = strtotime($params['end_time']);
            $builder->betweenWhere("created_at", $start_time, $end_time);
        }

        $builder->orderBy('created_at DESC');

        $paginator = new QueryBuilder([
            'builder' => $builder,
            'limit' => $params['page_set'],
            'page' => $params['page'],
        ]);

        $page = $paginator->getPaginate();

        return $page;
    }

    /**
     * @notes: 获取订单总数信息
     * @author: NedRen<ned@pproject.co>
     * @date: 2018/12/12
     * @return mixed
     * @version: 1.0
     */
    public function getOrderTotalInfo()
    {
        $res = Payment::find([
            'columns' => 'COUNT(id) as total_num,AVG(amount) as avg_amount,SUM(amount) as sum_amount'
        ]);

        return $res->toArray()[0];
    }

}
