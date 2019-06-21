<?php

namespace app\common\model;

/**
 * 留言表表
 * Class Leavemsg
 * @package app\common\model
 */
class Leavemsg extends BaseModel
{
	public function user(){
		return $this->belongsTo('User','user_id','user_id');
	}
	
	public function topuser(){
		return $this->hasOne('User','user_id','top_user_id');
	}
	
	public function praise(){
		return $this->hasOne('Praise','table_id','msg_id')->where(['flag'=>4]);
	}
	
	public function report(){
		return $this->hasOne('Report','table_id','msg_id')->where(['flag'=>4]);
	}
	
	public function leavemsg(){
		return $this->hasMany('Leavemsg','table_id','msg_id');
	}
	
}