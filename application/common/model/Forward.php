<?php

namespace app\common\model;

use think\Request;

/**
 * 转发
 * Class Forward
 * @package app\common\model
 */
class Forward extends BaseModel
{
	public function user(){
		return $this->hasOne('User','user_id','user_id')->field('user_id,family_name,middle_name,name,score,head_image');
	} 
}