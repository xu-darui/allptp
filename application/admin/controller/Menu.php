<?php
namespace app\admin\controller;
use app\admin\model\Menu as MenuModel;
use \think\Validate;
/**
 * 导航栏控制台
 * Class Index
 * @package app\admin\controller
 */
class Menu extends Controller
{
    public function menu($flag)
    {
		$admin=$this->getadmin(); 
		$menu_model=new MenuModel; 
		if($flag){
			return $this->renderSuccess($menu_model->menu($admin["pro_list"])); 
		}else{
			return $this->renderSuccess($menu_model->menu()); 
		}
		   
    }
	public function savemenu(){
		$data=input();
		$validate = new Validate([
			'name'  => 'require|max:6',
		],[
			'name.require'=>'导航栏名称必填',
			'name.max'=>'导航栏名称不能大于6个字', 
		]); 
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		$menu_model=new MenuModel;
		if($id=$menu_model->savemenu($data)){
			return $this->renderSuccess($id);
		}else{
			return $this->renderError('保存失败');
		}
	}
}
