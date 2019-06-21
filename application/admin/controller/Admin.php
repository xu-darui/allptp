<?php

namespace app\admin\controller;

use app\admin\model\Admin as AdminModel;
/**
 * 用户管理
 * Class Admin
 * @package app\admin
 */
class Admin extends Controller
{
	
	public function save(){
		$admin_model=new AdminModel; 
		if($admin_model->save_admin(input())){
			 return $this->renderSuccess("保存成功");
		}else{
			 return $this->renderError("保存失败");
		}
	}
	
	public function del($admin_id){
		$admin_model=new AdminModel; 
		if($admin_model->del_admin($admin_id)){
			return $this->renderSuccess("删除成功");
		}else{
			return $this->renderError("删除失败");
		}
	}
	public function adminlist($keywords=null,$page=1){
		$admin_model=new AdminModel; 
		if($keywords){
			$where["user_name|real_name"]=["like","%".$keywords."%"];
		}
		$input=input(); 
		if(array_key_exists('role',$input)){
			$where["role"]=input('role');	
		} 
		$where["status"]=0; 
		return $this->renderSuccess($admin_model->select_admin($where,$page));
	}	
}