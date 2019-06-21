<?php

namespace app\home\model;  
use app\common\model\Kind as KindModel;  
/**
 * 故事类型
 * Class Kind
 * @package app\store\model
 */
class Kind extends KindModel
{
	public function story_kind($top_id){
		return $this->where(['top_id'=>$top_id,'status'=>0])->select(); 
	}
	
	 public function kindlist(){ 
		$kind_model=new KindModel;
		return $kind_model->kindtree();
	 }
	
}