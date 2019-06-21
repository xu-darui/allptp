<?php

namespace app\common\model; 

/**
 *退款模型
 * Class Refund
 * @package app\common\model
 */
class Refund extends BaseModel
{
	/* protected $type = [ 
        'create_time'  =>  'timestamp:Y-m-d H:i', 
    ]; */
	public function refund(){
		return $this->hasMany('OrderRefund','order_id','order_id');
	}
	
	public function activity(){
		return $this->hasOne('Activity','activity_id','activity_id');
	}
	public function user(){
		return $this->hasOne('User','user_id','user_id');
	}
	
	public function house(){
		return $this->hasMany('RefundHouse','refund_id','refund_id');
	}
	
}