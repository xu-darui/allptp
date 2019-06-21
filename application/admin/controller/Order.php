<?php
namespace app\admin\controller;  
use app\admin\model\Order as OrderModel;
/**
 * 订单模块
 * Class Order
 * @package app\admin\controller
 */
class Order extends Controller
{
	
	public function order_list(){
		$data=input();
		$order_model=new OrderModel;
		return $this->renderSuccess($order_model->order_list($data));
		
	}

}