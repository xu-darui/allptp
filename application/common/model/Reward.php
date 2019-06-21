<?php

namespace app\common\model;

use think\Request;

/**
 * 奖金
 * Class Reward
 * @package app\common\model
 */
class Reward extends BaseModel
{
	public function order(){
		return $this->belongsTo('Order','order_id','order_id');
	}

	
}