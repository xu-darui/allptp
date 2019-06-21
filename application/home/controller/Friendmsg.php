<?php

namespace app\home\controller; 
use app\home\model\FriendMsg as FriendMsgModel;   
/**
 * 好友管理
 * Class FriendMsg 
 */
class Friendmsg extends Controller
{
	public function add(){
		$this->getuser();
		$data=input();
		$data['user_id']=$this->user_id;
		$msg_model=new FriendMsgModel;
		if($msg_model->add_msg($data)){
			 return $this->renderSuccess('发送成功');
		}else{
			return $this->renderError('发送失败');
		}
	}
	public function msg_list($page){
		$this->getuser();
		$msg_model=new FriendMsgModel;
		$where['f_user_id']=$this->user_id;
		return $this->renderSuccess($msg_model->msg_list($where,$page));
	}

}