<?php

namespace app\home\model;

use app\common\model\Question as QuestionModel;

/**
 * 问题模型
 * Class Question
 * @package app\store\model
 */
class Question extends QuestionModel
{
	public function select_quetion($where,$activity_id){
		$question_model=new QuestionModel;
		$where['status']=0;
		return $question_model->alias('a')->with(['option','answer'=>function($query) use($activity_id){$query->where('activity_id',$activity_id);}])->where($where)->select();
		
	}
}