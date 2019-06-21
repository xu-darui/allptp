<?php

namespace app\home\model;

use app\common\model\ActivityPlace as ActivityPlaceModel;

/**
 * 问题模型
 * Class Question
 * @package app\store\model
 */
class ActivityPlace extends ActivityPlaceModel
{
	public function save_place($data,$activity_id){
		foreach($data as $key=>$value){
			$value['activity_id']=$activity_id;
			if(array_key_exists('place_id',$value)&&$value['place_id']){ 
				$this->allowField(true)->update($value,['place_id'=>$value['place_id']]);
			}else{
				$value['place_id']='';
				$this->allowField(true)->isUpdate(false)->save($value);
			}			
		}		
	}

}