<?php

namespace app\common\model; 

/**
 * 好友用户表
 * Class UserFriend
 * @package app\common\model
 */
class UserFriend extends BaseModel
{
	public function user(){
		return $this->hasOne('User','user_id','f_user_id');
	}
}