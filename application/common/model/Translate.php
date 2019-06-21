<?php

namespace app\common\model;

use think\Request;

/**
 * 翻译表
 * Class Translate
 * @package app\common\model
 */
class Translate extends BaseModel
{
	
	public function user(){
		return $this->hasOne('User','user_id','user_id')->field('user_id,family_name,middle_name,name,language,head_image');
	}
	
	public function praise(){
		return $this->hasOne('Praise','table_id','translate_id')->where(['flag'=>2]);
	}
	public function report(){
		return $this->hasOne('Report','table_id','translate_id')->where(['flag'=>5]);
	}
	
}