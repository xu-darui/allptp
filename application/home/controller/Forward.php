<?php

namespace app\home\controller;     
use app\home\model\Forward as ForwardModel; 
use \think\Validate; 
use think\Db;
/**
 * 转发
 * Class Forward
 * @package app\home\controller
 */
class Forward extends Controller
{
	public function forward($flag,$table_id,$content,$type,$forw_user_id=0,$circle){
		$userdata=$this->getuser();
		$data=['flag'=>$flag,'table_id'=>$table_id,'content'=>$content,'type'=>$type,'forw_user_id'=>$forw_user_id,'user_id'=>$userdata['user_id']];
		if($type==1&&$forw_user_id==0){
			return $this->renderError("请选择转发对象");
		} 
		$forward_model=new ForwardModel;
		if($id=$forward_model->save_forward($data)){ 
			 return $this->renderSuccess("转发成功");
		}else{
			return $this->renderError("转发失败");
		} 
	}
	
	public function del_forward($forward_id){
		$forward_model=new ForwardModel;
		if($forward_model->del_forward($forward_id)){
			 return $this->renderSuccess("删除成功");
		}else{
			return $this->renderError("删除失败");
		} 
	} 
}