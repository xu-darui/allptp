<?php
namespace app\admin\controller;  
use app\admin\model\Draw as DrawModel;
/**
 * 提现管理
 * Class Draw
 * @package app\store\controller
 */
class Draw extends Controller
{
	public function draw_pass($draw_id){
		$draw_model=new DrawModel;
		if($draw_model->draw_pass($draw_id)){
			return $this->renderSuccess('审核成功');
		}else{
			return $this->renderError('审核失败');
		}
			
	}
	
	public function draw_list($keywords='',$sort=0,$page=1){
		$draw_model=new DrawModel;
		return $this->renderSuccess($draw_model->draw_list($keywords,$sort,$page));
	}
   
}
