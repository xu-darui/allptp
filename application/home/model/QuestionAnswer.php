<?php

namespace app\home\model;

use app\common\model\QuestionAnswer as QuestionAnswerModel;

/**
 * 答案模型
 * Class QuestionAnswer
 * @package app\store\model
 */
class QuestionAnswer extends QuestionAnswerModel
{
	public function save_answer($data,$activity_id){
		foreach($data as $key=>$value){
			$value['activity_id']=$activity_id;
			if(array_key_exists('answer_id',$value)&&$value['answer_id']){  
				$this->allowField(true)->update($value,['answer_id'=>$value['answer_id']]);
			}else{
				$value['answer_id']='';
				$this->allowField(true)->isUpdate(false)->save($value);
			}			
		}
	}
	
}