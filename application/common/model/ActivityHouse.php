<?php

namespace app\common\model;

use think\Request;

/**
 * 住宿模型
 * Class ActivityHouse
 * @package app\common\model
 */
class ActivityHouse extends BaseModel
{
	public function image(){ 
		return $this->hasMany('Image','table_id','house_id')->where('flag',4); 
	}	 
}