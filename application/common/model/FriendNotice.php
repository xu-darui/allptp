<?php

namespace app\common\model;

use think\Request;

/**
 * 好友通知
 * Class FriendNotice
 * @package app\common\model
 */
class FriendNotice extends BaseModel
{
	public function user(){

		return $this->hasOne('User','user_id','f_user_id');
	}

	public function fuser(){

		return $this->hasOne('User','user_id','user_id');
	}
	
}