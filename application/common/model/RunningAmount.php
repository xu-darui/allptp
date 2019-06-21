<?php

namespace app\common\model; 

/**
 * 奖金
 * Class RunningAmount
 * @package app\common\model
 */
class RunningAmount extends BaseModel
{
	public function order(){
		return $this->belongsTo('order','order_id','order_id');
	}
	
}