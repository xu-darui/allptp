<?php

namespace app\admin\model;

use app\common\model\Kind as KindModel;

/**
 * 类型管理
 * Class Kind
 * @package app\admin\model
 */
class Kind extends KindModel
{
	 public static function getdetail($where){
		 return KindModel::get($where);
	 }
	 
	 public function kind_save($data){
		 if($data['kind_id']){
			return  $this->allowField(true)->save($data,['kind_id'=>$data['kind_id']]);
		 }else{
			return  $this->allowField(true)->save($data);
		 }
	 }
	 
	 public function kindlist(){ 
		$kind_model=new KindModel;
		return $kind_model->kindtree();
	 }
	 public function kind_del($kind_id){
		return $this->allowField(true)->save(['status'=>1],['kind_id'=>$kind_id]);
	 }

}