<?php

namespace app\admin\model;

use app\common\model\Question as QuestionModel;

/**
 * 问题模型
 * Class Question
 * @package app\admin\model
 */
class Question extends QuestionModel
{
	public function save_question($data){
		if(array_key_exists('question_id',$data)){
			 $this->allowField(true)->save($data,['question_id'=>$data['question_id']]);
			 return $data['question_id'];
		}else{
			 $this->allowField(true)->save($data);
			 return $this->question_id;
		}
		
	}
	
	public function del_question($question_id){
		return $this->where(['question_id'=>$question_id])->update(['status'=>1]);
	}
	
	public function question_list(){ 
		return $this->with(['option'=>function($query){$query->where(['status'=>0]);}])
			 ->where(['status'=>0])
			 ->order('question_id desc')
			 ->paginate(10, false, ['query' => ["page"=>input('page')]]); 
		
	}

}