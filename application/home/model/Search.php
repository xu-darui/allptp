<?php

namespace app\home\model;

use app\common\model\Search as SearchModel;
use think\Db;

/**
 * 搜索模块
 * Class Search
 * @package app\store\model
 */
class Search extends SearchModel
{
	
	public function search_lately($user_id){ 
		$search_model=new SearchModel;
		return $search_model->where(['user_id'=>$user_id])->order('create_time desc')->group('flag,keywords')->select();
	}
	public function save_search($data){
		return $this->allowField(true)->save($data);
	}

}