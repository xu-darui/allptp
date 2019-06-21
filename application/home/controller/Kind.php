<?php
namespace app\home\controller;  
use app\home\model\Kind as KindModel; 
use think\Cache;
/**
 * 后台首页
 * Class Index
 * @package app\store\controller
 */
class Kind extends Controller
{
	public function kindlist(){
		$kind_model=new KindModel;
		return $this->renderSuccess($kind_model->kindlist());
	}
	public function kind_sub($top_id){
		$kind_model=new KindModel;
		return $this->renderSuccess($kind_model->sub_kindlist($top_id));
	}
	
}