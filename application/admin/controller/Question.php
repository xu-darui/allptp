<?php

namespace app\admin\controller;  
 
use app\admin\model\Question as QuestionModel; 
use app\admin\model\QuestionOption as QuestionOptionModel; 
use \think\Validate;
/**
 * 问答模块
 * Class Question
 * @package app\admin\controller
 */
class Question extends Controller
{
	
	public function save_question(){
		$data=input();
		$validate = new Validate([
			'title'  => 'require|min:1', 
			'flag'  => 'require', 
		],[
			'title.require'=>'请输入题目',  
			'flag.require'=>'请选择问题填放位置',  
		]); 
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		$question_model=new QuestionModel;
		if($question_id=$question_model->save_question($data)){
			 return $this->renderSuccess($question_id);
		}else{
			 return $this->renderSuccess('保存失败');
		}
	}
	
	public function save_option(){
		$data=input();
		$validate = new Validate([
			'name'  => 'require|min:1', 
			'question_id'  => 'require', 
		],[
			'name.require'=>'请输入选项名称',  
			'question_id.require'=>'请选择该选项题目',  
		]); 
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		$option_model=new QuestionOptionModel;
		if($option_id=$option_model->save_option($data)){
			 return $this->renderSuccess($option_id);
		}else{
			 return $this->renderSuccess('保存失败');
		}
	}
	
	public function del_question($question_id){
		$question_model=new QuestionModel;
		if($question_model->del_question($question_id)){
			 return $this->renderSuccess('删除成功');
		}else{
			 return $this->renderSuccess('删除失败');
		}
	}

	public function del_option($option_id){
		$option_model=new QuestionOptionModel;
		if($option_model->del_option($option_id)){
			 return $this->renderSuccess('删除成功');
		}else{
			 return $this->renderSuccess('删除失败');
		}
	}
	
	public function question_list(){
		$question_model=new QuestionModel; 
		return $this->renderSuccess($question_model->question_list());
	
	}

}