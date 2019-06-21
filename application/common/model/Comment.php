<?php

namespace app\common\model;

/**
 * 评论表
 * Class Comment
 * @package app\common\model
 */
class Comment extends BaseModel
{
	public function leavemsg(){
		return $this->hasMany('Leavemsg','table_id','comment_id');
	}

	public function image(){
		return $this->hasMany('Image','table_id','comment_id');
	}
	
	public function user(){
		return $this->hasOne('User','user_id','user_id');
	}
	public function report(){
		return $this->hasOne('Report','table_id','comment_id')->where(['flag'=>2]);
	}
	
	public function praise(){
		return $this->hasOne('Praise','table_id','comment_id')->where(['flag'=>3]);
	}
}