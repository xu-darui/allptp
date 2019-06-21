<?php

namespace app\common\model;

use think\Request;

/**
 * 访问记录
 * Class Visit
 * @package app\common\model
 */
class Visit extends BaseModel
{
	public function activity(){
		return $this->hasOne('Activity','activity_id','table_id');
	}
	
}