<?php

namespace app\home\model;

use app\common\model\FriendMsg as FriendMsgModel;

/**
 * 好友
 * Class FriendNotice
 * @package app\home\model
 */
class FriendMsg extends FriendMsgModel
{
	public function add_msg($data){
		return $this->allowField(true)->save($data); 
	}
	
	public function msg_list($where,$page){
		$data=$this->where($where)->order('create_time desc')->paginate(10, false, ['query' => ["page"=>$page]])->toArray(); 
		if(array_key_exists('data',$data)&&$data['data']){
			$msg_id=array_column($data['data'],'msg_id'); 
			$this->where(['msg_id'=>['in',$msg_id]])->update(['isread'=>1]);
		}
		return $data;
	}
	public function noread_count($user_id){
		return $this->where(['f_user_id'=>$user_id,'isread'=>0])->count();
	}
	
}