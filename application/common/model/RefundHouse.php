<?php

namespace app\common\model; 

/**
 * 退款房间
 * Class RefundHouse
 * @package app\common\model
 */
class RefundHouse extends BaseModel
{
	public function refund(){
		return $this->hasMany('OrderRefund','refund_id','refund_id');
	}
	
}