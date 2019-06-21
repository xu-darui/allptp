<?php
namespace app\home\controller;
use app\home\model\Order as OrderModel;   
 use app\common\controller\NotifyHandler;
use think\Db;
use think\Cache; 
/**
 * 订单首页
 * Class Order
 * @package app\store\controller
 */
class Order extends NotifyHandler
{
    protected $params; // 订单信息

    public function index()
    {
        parent::init();
    }
	public function add(){
		$order_no=time();
		Db::name('order')->insert(['order_no'=>$order_no,'price'=>0.01,'user_id'=>9,'activity_id'=>1]); 
		$this->getOrder($order_no);
		$aa=\alipay\Pagepay::pay($this->params);
	}

    /**
     * 获取订单信息, 必须包含订单号和订单金额
     *
     * @return string $params['out_trade_no'] 商户订单
     * @return float  $params['total_amount'] 订单金额
     */
    public function getOrder($order_no)
    {
        // 以下仅示例
       // $order_no = $_POST['order_no'];
       // $order_no ="20181224154005";
        $order = Db::name('order')->where('order_no', $order_no)->find();
        $params = [
            'out_trade_no' => $order['order_no'],
            'total_amount' => $order['price'],
            'status'       => $order['status'],
            'id'           => $order['order_id'],
            'subject'           => "test"
        ];

        $this->params = $params;
    }

    /**
     * 检查订单状态
     *
     * @return Boolean true表示已经处理过 false表示未处理过
     */
    public function checkOrderStatus()
    {
        // 以下仅示例
        if($this->params['status'] == 0) {
            // 表示未处理
            return false;
        } else {
            return true;
        }
    }

    /**
     * 业务处理
     * @return Boolean true表示业务处理成功 false表示处理失败
     */
    public function handle()
    {
        // 以下仅示例
        $result = Db::name('order')->where('id', $this->params['id'])->update(['status'=>1]);
        if($result) {
            return true;
        } else {
            return false;
        }
    }
	
}