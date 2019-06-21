<?php

namespace app\home\model;

use app\common\model\UserNotice as UserNoticeModel;

/**
 * 联系方式
 * Class UserContacts
 * @package app\home\model
 */
class UserNotice extends UserNoticeModel
{
	public function add($data){
		if(UserNoticeModel::get(['user_id'=>$data['user_id']])){
			return $this->allowField(true)->save($data,['user_id'=>$data['user_id']]);
		}else{
			return $this->allowField(true)->save($data);
		} 
	} 
	
	public function detail($where){
		return $this->where($where)->find();
	}
}