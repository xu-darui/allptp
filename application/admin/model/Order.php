<?php

namespace app\admin\model;
use app\common\model\Order as OrderModel;

/**
 * 订单模型
 * Class Order
 * @package app\admin\model
 */
class Order extends OrderModel
{
	public function order_list($data){
		$where=[];
		$orderby='';
		if(array_key_exists('sort',$data)&&$data['sort']){
			switch ($data['sort']){
				case 1:$orderby="pay_time desc";break;
				case 2:$orderby="total_Price desc";break;
				case 3:$orderby="total_Price asc";break;
				default:$orderby="order_id desc";break;
			}
		}
		if(array_key_exists('keywords',$data)&&$data['keywords']){
			$where['title']=$data['keywords'];
		}
		if(array_key_exists('ispay',$data)&&$data['ispay']){
			$where['ispay']=$data['ispay'];
		}else{
			$where['ispay']=['neq',99];
		}
		if(array_key_exists('status',$data)&&$data['status']){
			$where['status']=$data['status'];
		} 
		$order_model=new OrderModel;
		$order_model->with(['user'=>function($query){$query->field('user_id,family_name,middle_name,name');},'cover','person','house'])
			->where($where)
			->order($orderby)
			->paginate(10, false, ['query' => ["page"=>input('page')]]); 
	}

}