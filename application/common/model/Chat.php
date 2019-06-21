<?php

namespace app\common\model; 

/**
 * 聊天
 * Class Chat
 * @package app\common\model
 */
class Chat extends BaseModel
{
	protected $type = [ 
        'create_time'  =>  'timestamp:Y-m-d H:i:s', 
        'read_time'  =>  'timestamp:Y-m-d H:i:s', 
    ];	

	public function user(){
		return $this->belongsTo('User','user_id','user_id')->field('user_id,family_name,middle_name,name,head_image');
	}

	public function touser(){
		return $this->belongsTo('User','to_user_id','user_id')->field('user_id,family_name,middle_name,name,head_image');
	}
}