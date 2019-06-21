<?php

namespace app\home\controller;  
use app\home\model\Comment as CommentModel;
use app\home\model\Leavemsg as LeavemsgModel;


/**
 * 消息控制器
 * Class Msg 
 */
class Msg extends Controller
{
	public function mysay($page=1){
		$userdata=$this->getuser();
		$comment_model=new CommentModel;
		return $this->renderSuccess($comment_model->mysay($userdata['user_id'],$page));
		
	}
	
	public function my_replay($page=1){
		$userdata=$this->getuser();
		$comment_model=new CommentModel;
		return $this->renderSuccess($comment_model->replay_list($userdata['user_id'],$page));
		/* $leavemsg_model=new LeavemsgModel;
		return $this->renderSuccess($leavemsg_model->replay_list($userdata['user_id'],$page)); */
	}
}