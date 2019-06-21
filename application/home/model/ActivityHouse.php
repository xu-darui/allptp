<?php

namespace app\home\model;

use app\common\model\ActivityHouse as ActivityHouseModel;
use app\home\model\Image as ImageModel;
use think\Db;

/**
 * 问题模型
 * Class Question
 * @package app\store\model
 */
class ActivityHouse extends ActivityHouseModel
{
	public function save_house($data,$activity_id){
	
		foreach($data as $key=>$value){  
			$value['activity_id']=$activity_id;
			$value['status']=0;
			if(array_key_exists('house_id',$value)&&$value['house_id']){ 
				$house=$value;
				unset($house['image']);
				$this->where(['house_id'=>$value['house_id']])->update($house); 
				$house_id=$value['house_id'];
			}else{
				$value['house_id']=''; 
				$this->allowField(true)->isUpdate(false)->save($value); 
				$house_id=$this->house_id;
			} 
			if(array_key_exists('image',$value)&&$value['image']){
				$image_model=new ImageModel; 
				$image_model->save_image($value['image'],$house_id,4);
			}			
		}		
	}
	
	public function delete_house($activity_id){
		$this->where(['status'=>['neq',1],'activity_id'=>$activity_id])->update(['status'=>1]);
	}
	
	public function house_save_one($data){  
		if(array_key_exists('house_id',$data)&&$data['house_id']>0){ 
			$this->allowField(true)->save($data,['house_id',$data['house_id']]); 
			$house_id=$data['house_id'];
		}else{
			$this->allowField(true)->isUpdate(false)->save($data);
			$house_id=$this->house_id;
		}
		if(array_key_exists('image',$data)&&$data['image']){
			$data['image']=json_decode((html_entity_decode($data['image'])),true);
			$image_model=new ImageModel; 
			$image_model->save_image($data['image'],$house_id,4);
		} 
		
	}
	
	public function house_del_one($house_id){
		return $this->where(['house_id'=>$house_id])->update(['status'=>1]);
	}
	
	public function get_house_array($activity_id){
		return $this->with('image')->where(['activity_id'=>$activity_id,'status'=>0])->select()->toArray();
	}

}