<?php
namespace app\home\controller; 
use app\home\model\Translate as TranslateModel;  
use \think\Validate;
/**
 * 翻译
 * Class Translate
 * @package app\home	\controller
 */
class Translate extends Controller
{
	public function translate(){
		$data=input();
		$validate = new Validate([
			'language'  => 'require', 
			'user_id' => 'require',
			'activity_id' => 'require',  
		],[
			'language.require'=>'请输入您想翻译的语言', 
			'user_id.require'=>'用户异常', 
			'activity_id.require'=>'活动异常',    
		]); 
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		$translate_model=new TranslateModel;
		if($id=$translate_model->save_translate($data)){
			 return $this->renderSuccess($id);
		}else{
			return $this->renderError("保存失败");
		} 		
	}
	
	public function del_translate($translate_id){
		$translate_model=new TranslateModel;
		if($id=$translate_model->del_translate($translate_id)){
			 return $this->renderSuccess("删除成功");
		}else{
			return $this->renderError("删除失败");
		} 
	}

	public function translate_list($activity_id,$language,$sort=1){
		switch ($sort){
			case 1:$orderby="create_time";break;
			case 2:$orderby="praise_num";break;
		}
		if($sort>0){
			$orderby.="  desc";
		}else{
			$orderby.="  asc";
		}
		$where['activity_id']=$activity_id;
		$where['language']=$language;
		$translate_model=new TranslateModel;
		return $this->renderSuccess($translate_model->translate_list($where,$orderby,$this->user_id));
		
	}
	
	public function translate_detail($translate_id){
		$detail=TranslateModel::detail($translate_id);
		if($detail['status']==1){
			return $this->renderError("该留言已经删除");
		}
		return $this->renderSuccess($translate_model->translate_detail($translate_id));
	}
	

}