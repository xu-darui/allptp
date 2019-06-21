<?php

namespace app\common\model; 

/**
 * 活动提供房间模型
 * Class OrderHouse
 * @package app\common\model
 */
class OrderHouse extends BaseModel
{
	public function acthouse(){
		return $this->hasOne('ActivityHouse','house_id','house_id');
	}
	
	public function image(){
		return $this->hasMany('Image','table_id','house_id')->where(['flag'=>4]);
	}
	
}