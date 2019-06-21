<?php

namespace app\common\model;

use think\Request;

/**
 * 问题
 * Class Question
 * @package app\common\model
 */
class Question extends BaseModel
{
	public function option(){
		
		return $this->hasMany('QuestionOption','question_id','question_id');
		
	}
	public function answer(){
		return $this->belongsTo('QuestionAnswer','question_id','question_id');
	}
	
	
}