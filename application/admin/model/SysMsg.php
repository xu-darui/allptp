<?php

namespace app\admin\model;

use app\common\model\SysMsg as SysMsgModel;
use app\common\model\User as UserModel;

/**
 * 系统消息
 * Class Sysmsg
 * @package app\admin\model
 */
class SysMsg extends SysMsgModel
{
	public function save_msg($data){ 
		if(!array_key_exists('msg_id',$data)){
			$this->allowField(true)->save($data);
			return $this->msg_id;
		}else{
			if($this->allowField(true)->save($data,['msg_id'=>$data['msg_id']])){
				return $data['msg_id'];
			}else{
				return false;
			}
			 
		} 
	}
	
	public function send_msg($msg_id,$issend){
		return $this->update(['issend'=>$issend,'send_time'=>time()],['msg_id'=>$msg_id]);
	}
	
	public function msg_list($where,$page){ 
		$where['status']=0;
		 $data=$this->with(['admin'=>function($query){$query->field('admin_id,user_name,real_name,mobile');}])->where($where)->order('update_time desc')->paginate(10, false, ['query' => ["page"=>$page]]);  
		 $user_model=new UserModel;
		 foreach($data as $key=>$value){ 
			 if($value['user_list']){
				 $data[$key]['user_list_name']=$user_model->where('user_id','in',$value['user_list'])->column('user_id,family_name,middle_name,name,mobile','user_id');
			 } 
			 if($value['read_user_list']){
				  $data[$key]['read_user_list_name']=$user_model->where('user_id','in',$value['read_user_list'])->column('user_id,family_name,middle_name,name,mobile','user_id');
			 }
			
			 
		 }
		 return $data;
	}
	public function del_msg($msg_id){
		return $this->update(['status'=>1],['msg_id'=>['in',$msg_id]]);
	}
	
}