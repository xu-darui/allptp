<?php

namespace app\admin\controller;

use app\admin\model\Kind as KindModel;

/**
 * 类别
 * Class Kind
 * @package app\admin\controller
 */
class Kind extends Controller
{
	public function kind_save($kind_id=0,$series=0,$sort=0,$top_id=0,$kind_name,$desc,$image_id){
		$data=['kind_id'=>$kind_id,'kind_name'=>$kind_name,'series'=>$series,'sort'=>$sort,'top_id'=>$top_id,'desc'=>$desc,'image_id'=>$image_id];
		if($top_id){
			if(!$kind=KindModel::getdetail(['kind_id'=>$top_id])){
				return $this->renderError('没有上级分类信息');
			} 
			$data['path']=$kind['path']==''?$top_id:$kind['path'].','.$top_id;  	
		}
		$kind_model=new KindModel; 
		if($kind_model->kind_save($data)){
			return $this->renderSuccess('保存成功');
		}else{
			return $this->renderError('保存失败');
		}
	}
	public function kindlist(){
		$kind_model=new KindModel;
		return $this->renderSuccess($kind_model->kindlist());
	}
	public function kind_del($kind_id){
		$kind_model=new KindModel;
		if($kind_model->kind_del($kind_id)){
			return $this->renderSuccess('删除成功');
		}else{
			return $this->renderError('保存失败');
		}
	}
	
	
}