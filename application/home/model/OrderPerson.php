<?php

namespace app\home\model;   
use app\common\model\OrderPerson as OrderPersonModel;   
/**
 * 订单同行人员
 * Class OrderPerson
 * @package app\home\model
 */
class OrderPerson extends OrderPersonModel
{
	
	public function add_person($order_id,$data){
		$person_data=$data['person']; 
		foreach($person_data as $key=>$value){
			
			$person_data[$key]['order_id']=$order_id;
			$person_data[$key]['user_id']=$data['user_id'];	
		}    
		$this->allowField(true)->saveAll($person_data); 
	}

}