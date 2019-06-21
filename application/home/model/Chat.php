<?php

namespace app\home\model; 
use app\common\model\Chat as ChatModel; 
use think\Db;

/**
 * 聊天记录
 * Class Chat
 * @package app\store\model
 */
class Chat extends ChatModel
{
	public function chat_list($where){
		$chat_model=new ChatModel;
		$data= $chat_model->with(['user.headimage','touser.headimage'])->where($where)->order('create_time desc')->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]]);  
	}

}