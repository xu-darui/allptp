<?php

namespace app\home\model;   
use app\common\model\OrderHouse as OrderHouseModel;  
use app\common\model\ActivityHouse as ActivityHouseModel;  
use app\common\exception\BaseException;
/**
 * 订单房间
 * Class OrderHouse
 * @package app\home\model
 */
class OrderHouse extends OrderHouseModel
{
	public function add_house($order_id,$data){ 
	$house_data=$data['house']; 
		$house_price=0;
		foreach($house_data as $key=>$value){
			if(!$house=ActivityHouseModel::get(['house_id'=>$value['house_id'],'status'=>0])){
				throw new BaseException(['code' => 0, 'msg' => '没有该房间']);
			}
			$house_data[$key]['order_id']=$order_id; 
			$house_data[$key]['union_price']=$house['price']; 
			$house_data[$key]['price']=$house['price']*$value['num']; 
			$house_data[$key]['title']=$house['title']; 
			$house_data[$key]['max_person']=$house['max_person']; 
			$house_price+=$house['price']*$value['num'];
		}  
		$this->allowField(true)->saveAll($house_data); 	
		return $house_price;
	}
}