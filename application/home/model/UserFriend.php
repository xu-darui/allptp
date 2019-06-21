<?php

namespace app\home\model;

use app\common\model\UserFriend as UserFriendModel;

/**
 * 用户好友
 * Class UserFriend
 * @package app\store\model
 */
class UserFriend extends UserFriendModel
{
	public function friend_list($user_id,$page){ 
		return $this->with(['user'=>function($query){$query->with(['headimage'])->field('user_id,family_name,middle_name,name,head_image');}])->where(['user_id'=>$user_id,'status'=>0])->group('f_user_id')->order('friend_id desc')->paginate(10, false, ['query' => ["page"=>$page]]);
	}
	public function del_friend($user_id,$f_user_id){
		return $this->where(function($query) use($user_id,$f_user_id){$query->where(['user_id'=>$user_id,'f_user_id'=>$f_user_id]);})->whereor(function($query) use($user_id,$f_user_id){$query->where(['user_id'=>$f_user_id,'f_user_id'=>$user_id]);})->update(['status'=>1,'update_time'=>time()]); 
	}
}