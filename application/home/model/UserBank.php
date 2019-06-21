<?php

namespace app\home\model; 
use app\common\model\UserBank as  UserBankModel;   

/**
 * 银行卡号
 * Class UserBank
 * @package app\admin\model
 */
class UserBank extends UserBankModel
{
	public function detail($bank_id){
		return UserBankModel::get(['bank_id'=>$bank_id,'status'=>0]);
	}
	public function bank_save($data){
		if(array_key_exists('bank_id',$data)&&$data['bank_id']){
			return $this->allowField(true)->save($data,['bank_id'=>$data['bank_id']]);
		}else{
			unset($data['bank_id']);
			return $this->allowField(true)->save($data);
		}
		
	}
	
	public function bank_del($bank_id){
		return $this->where(['bank_id'=>$bank_id])->update(['status'=>1]);
	}
	
	public function bank_list($user_id){
		return $this->where(['user_id'=>$user_id,'status'=>0])->order('bank_id desc')->select();
	}
}