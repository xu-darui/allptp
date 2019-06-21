<?php

namespace app\common\model;

use think\Request;

/**
 * 故事
 * Class Story
 * @package app\common\model
 */
class Story extends BaseModel
{
	public function image(){
		return $this->hasMany('Image','table_id','story_id')->where('flag',2)->order('sort asc');
	}
	public function user(){
		return $this->hasOne('User','user_id','user_id')->field('user_id,family_name,middle_name,name,mobile,language,head_image');
	}
	public function cover(){
		return $this->hasOne('Image','image_id','cover_image');
	}
	public function kindpath(){
		return $this->hasOne('Kind','kind_id','kind_id');
	}
	public function praise(){
		return $this->hasMany('Praise','table_id','story_id')->where(['flag'=>1,'status'=>0]);
	}
	public function collection(){
		return $this->hasMany('Collection','table_id','story_id')->where(['flag'=>2,'status'=>0]);
	}
	
}