<?php

namespace app\common\model; 

/**
 * 收益
 * Class Turnover
 * @package app\common\model
 */
class Turnover extends BaseModel
{
	public function order(){
		return $this->belongsTo('order','order_id','order_id');
	}
	
}