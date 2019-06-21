<?php

namespace app\home\model;

use app\common\model\UserContacts as UserContactsModel;

/**
 * 联系方式
 * Class UserContacts
 * @package app\home\model
 */
class UserContacts extends UserContactsModel
{
	public function save_contacts($data){
		if(array_key_exists('contacts_id',$data)){
			return $this->allowField(true)->save($data,['contacts_id'=>$data['contacts_id']]);
		}else{
			return $this->allowField(true)->save($data);
		}
	}
	public function del_contacts($contacts_id){
		return $this->where('contacts_id','in',$contacts_id)->update(["status"=>1]);
	}	
}