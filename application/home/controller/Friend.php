<?php

namespace app\home\controller; 
use app\home\model\FriendNotice;      
use app\home\model\UserFriend;      
/**
 * 好友管理
 * Class Friend 
 */
class Friend extends Controller
{
	public function ask_friend($f_user_id,$msg=''){
		$this->getuser();
		if($this->user_id==$f_user_id){
			return $this->renderError('不能邀请自己为好友');
		}
		$notice_model=new FriendNotice;
		if($notice_model->save_notice(['user_id'=>$this->user_id,'f_user_id'=>$f_user_id,'msg'=>$msg])){
			return $this->renderSuccess('发送成功');
		}
			return $this->renderError('发送失败');
	}
	
	public function getlist($page){
		$this->getuser();
		$notice_model=new FriendNotice;
		$data=$notice_model->getlist($this->user_id,$page);
		return $this->renderSuccess($data);
	}
	
	public function agree($notice_id,$status){
		$this->getuser();
		$notice_model=new FriendNotice;
		if($notice_model->agree($notice_id,$status)){
			switch ($status){
				case 1:
				$msg='已同意';
				break;
				case 2:
				$msg='已拒绝';
				break;
				case 3:
				$msg='已忽略';
				break;
				
			}
			return $this->renderSuccess($msg);
		}else{
			return $this->renderError('操作失败');
		}
	}
	
	public function myfriend($page){
		$this->getuser();
		$friend_model=new UserFriend;
		$data=$friend_model->friend_list($this->user_id,$page);
		return $this->renderSuccess($data);
		
	}
	public function del($f_user_id){
		$this->getuser();
		$friend_model=new UserFriend;
		if($friend_model->del_friend($this->user_id,$f_user_id)){
			return $this->renderSuccess('删除成功');
		}else{
			return $this->renderError('删除失败');
		}
	}

}