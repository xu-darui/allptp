<?php

namespace app\admin\model;

use app\common\model\Menu as MenuModel;


/**
 * 导航
 * Class Menu
 * @package app\admin\model
 */
class Menu extends MenuModel
{
	
	public function menu($pro_list=''){  
		return $this->menutree($pro_list);
	}
	public function savemenu($data){
		if(array_key_exists('id',$data)&&$data['id']){
			 $this->allowField(true)->save($data,['id'=>$data['id']]);
			 return $data['id'];
		}else{
			unset($data['id']);
			 $this->allowField(true)->save($data);
			 return $this->id;
		}
	}


}
