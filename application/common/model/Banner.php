<?php

namespace app\common\model; 

/**
 * 轮滚
 * Class Banner
 * @package app\common\model
 */
class Banner extends BaseModel
{
	public function image(){
		return $this->hasOne('Image','image_id','image_id');
	}
	
}