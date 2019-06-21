<?php

namespace app\common\model;

use think\Request;

/**
 * 收藏
 * Class Collection
 * @package app\common\model
 */
class Collection extends BaseModel
{
	public function activity(){
		return $this->hasOne('Activity','activity_id','table_id');
	}
	public function story(){
		return $this->hasOne('Story','story_id','table_id');
	}
	
	public function add_collect($data){
		foreach($data as $key=>$value){
			if($value['collection']){
				$data[$key]['is_collection']=1;
			}else{
				$data[$key]['is_collection']=0;
			}
			unset($data[$key]['collection']);
		}
		return $data;
	}
	
	
}