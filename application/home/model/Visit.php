<?php

namespace app\home\model;

use app\common\model\Visit as VisitModel;
use app\common\model\Kind as KindModel;
use think\Db;

/**
 * 访问记录
 * Class Visit
 * @package app\store\model
 */
class Visit extends VisitModel
{
	public function add($data){
		$this->allowField(true)->save($data);
		return $this->visit_id;
	}
	
	public function visit_lately($user_id,$page){
		$visit_model=new VisitModel;
		$data=Db::view('act_story')
		->field("FROM_UNIXTIME(create_time,'%Y-%c-%d') as create_time,FROM_UNIXTIME(update_time,'%Y-%c-%d') as update_time")
		->where(['user_id'=>$user_id,'status'=>0,'audit'=>1,'online'=>0])
		->order('create_time desc')
		->paginate(10, false, ['query' => ["page"=>$page]])->toArray();
		if($data){
			$kind_model=new KindModel;  
			$data=$kind_model->addkind_array($data);
		} 
		return $data;
	}
		

}