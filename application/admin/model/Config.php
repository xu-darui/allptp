<?php

namespace app\admin\model;

use app\common\model\Config as ConfigModel;

/**
 * 配置文件
 * Class Config
 * @package app\admin\model
 */
class Config extends ConfigModel
{
	public function save_config($where,$data){
		return $this->where($where)->update($data); 
	}
	
	public function get_config(){
		$config= ConfigModel::get(['key'=>'config']); 
		return (json_decode($config['values'],true)); 
	}
}