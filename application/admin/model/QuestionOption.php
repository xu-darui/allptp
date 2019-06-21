<?php

namespace app\admin\model;

use app\common\model\QuestionOption as QuestionOptionModel;

/**
 * 问题选项模型
 * Class QuestionOption
 * @package app\admin\model
 */
class QuestionOption extends QuestionOptionModel
{
	public function save_option($data){
		if(array_key_exists('option_id',$data)){
			 $this->allowField(true)->save($data,['option_id'=>$data['option_id']]);
			 return $data['option_id'];
		}else{
			 $this->allowField(true)->save($data);
			 return $this->option_id;
		}
		
	}
	
	public function del_option($option_id){
		return $this->where(['option_id'=>$option_id])->update(['status'=>1]);
	}

}