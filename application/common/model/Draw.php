<?php

namespace app\common\model; 

/**
 * 提成
 * Class Draw
 * @package app\common\model
 */
class Draw extends BaseModel
{
	public function user(){
		return $this->belongsTo('User','user_id','user_id');
	}
}