<?php

namespace app\common\model;

use think\Request;

/**
 * 报名表
 * Class Enroll
 * @package app\common\model
 */
class Enroll extends BaseModel
{
	public function user(){
		return $this->hasOne('User','user_id','user_id')->field('user_id,family_name,middle_name,name,score,head_image,six');
	}
	
	public function activity(){
		return $this->hasOne('Activity','activity_id','activity_id')->field('activity_id,title,cover_image,status,audit,online,score,comment_num,kind_id');
	}
}