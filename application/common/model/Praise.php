<?php

namespace app\common\model;

use think\Request;

/**
 * 点赞
 * Class Praise
 * @package app\common\model
 */
class Praise extends BaseModel
{
	public function user(){
		return $this->belongsTO('User','user_id','user_id');
	}
	
}