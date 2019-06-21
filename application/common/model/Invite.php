<?php

namespace app\common\model;

use think\Request;

/**
 * 邀请志愿者
 * Class Invite
 * @package app\common\model
 */
class Invite extends BaseModel
{
	public function user(){
		return $this->hasOne('User','user_id','user_id')->field('user_id,family_name,middle_name,name,score,head_image,six');
	}
	
	public function invuser(){
		return $this->hasOne('User','user_id','invi_user_id')->field('user_id,family_name,middle_name,name,score,head_image,six');
	}
	
	public function activity(){
		return $this->belongsTo('Activity','activity_id','activity_id');
	}
}