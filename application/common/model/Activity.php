<?php

namespace app\common\model; 

/**
 * 活动
 * Class Activity
 * @package app\common\model
 */
class Activity extends BaseModel
{
	protected $type = [ 
        'activ_begin_time'  =>  'timestamp:Y-m-d',
        'activ_end_time'  =>  'timestamp:Y-m-d',
        'del_time'  =>  'timestamp:Y-m-d',
    ];
	public function answer(){
		
		return $this->hasMany('QuestionAnswer','activity_id','activity_id');
		
	}
	public function slot(){
		
		return $this->hasMany('ActivitySlot','activity_id','activity_id');
		
	}
	public function image(){ 
		return $this->hasMany('Image','table_id','activity_id')->where('flag',1)->order('sort asc');
		
	}	
	public function houseimage(){
		
		return $this->hasMany('Image','table_id','activity_id')->where('flag',6)->order('sort asc');
		
	}
	public function user(){
		return $this->hasOne('User','user_id','user_id')->field('user_id,family_name,middle_name,name,mobile,language,head_image,idcard_z,idcard_f,face_image,audit_idcard,audit_idcard');
	}
	
	public function cover(){
		return $this->hasOne('Image','image_id','cover_image');
	}
	
	public function kindpath(){
		return $this->hasOne('Kind','kind_id','kind_id');
	}
	public function house(){
		return $this->hasMany('ActivityHouse','activity_id','activity_id')->where(['status'=>0]);
	}
	
	public function  collection(){
		return $this->hasMany('Collection','table_id','activity_id')->where(['flag'=>1,'status'=>0]);
	}
	
	public function user_acti_num($user_id){
		return $this->where(['user_id'=>$user_id,'complete'=>1,'online'=>0,'audit'=>1,'status'=>0])->count('activity_id');
	}
}