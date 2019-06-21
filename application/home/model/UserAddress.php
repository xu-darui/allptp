<?php

namespace app\home\model;

use app\common\model\UserAddress as UserAddressModel;

/**
 * 收货地址
 * Class UserAddress
 * @package app\store\model
 */
class UserAddress extends UserAddressModel
{
	public function save_address($data){ 
		if(array_key_exists('address_id',$data)){
			return $this->allowField(true)->save($data,['address_id'=>$data['address_id']]);
		}else{
			return $this->allowField(true)->save($data);
		}
	}
	
	public function del_address($address_id){
		return $this->where('address_id','in',$address_id)->update(["status"=>1]);
	}
	
	public function address_list($user_id){
		return  $this->where(['user_id'=>$user_id,'status'=>0])->select();

	}
}