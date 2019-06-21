<?php

namespace app\common\model;
use think\Db;


/**
 * 分类
 * Class Kind
 * @package app\common\model
 */
class Kind extends BaseModel
{
	public function subkind(){
		return $this->hasMany('Kind','top_id','kind_id');
	} 
	
	function kindtree(){
		  $json_data = array();
		  //$sql="select * from oa_group order by id desc";
			$data = Db::name('kind')->alias('a')->join('image b','a.image_id=b.image_id','LEFT')->field("a.kind_id,a.kind_name,a.path,a.top_id,a.sort,FROM_UNIXTIME(a.create_time,'%Y-%m-%d') as create_time,FROM_UNIXTIME(a.update_time,'%Y-%m-%d') as update_time,a.image_id,b.domain,b.image_url,b.themb_url,b.extension")->where(['a.status'=>0])->order('a.kind_id desc,a.sort asc')->select(); 	
		   // $data = Db::name('kind')->order('kind_id desc')->select()->toArray();  
		  foreach($data as $v){ 
			  $json_data[$v['kind_id']] = isset($json_data[$v['kind_id']]) ? $v + $json_data[$v['kind_id']] : $v; 
			  if($v['top_id'] != 0){
				  $json_data[$v['top_id']]['children'][] = $json_data[$v['kind_id']];
				  unset($json_data[$v['kind_id']]);
			  }
			  //$json_data[$v['id']] = $v;
		  }
		  ksort($json_data);
		  return $json_data;
	}
		
	public function addkind($data){
		foreach($data as $key=>$value){ 
			$data[$key]['kind']=$this->field('kind_id,kind_name')->where(['kind_id'=>['in',$value['kind_id']]])->select()->toArray(); 
		}
		return $data;
	}

	public function addkind_array($data){
		foreach($data['data'] as $key=>$value){   
			$value['kind']=$this->field('kind_id,kind_name')->where(['kind_id'=>['in',$value['kind_id']]])->select()->toArray();
			$data['data'][$key]=$value;
		}
		return $data;
	}
	
	public function addkind_find($data){
		$data['kind']=$this->field('kind_id,kind_name')->where(['kind_id'=>['in',$data['activity']['kind_id']]])->select()->toArray();
		return $data;
	}
	
	public function sub_kindlist($top_id){
		return   Db::name('kind')->alias('a')->join('image b','a.image_id=b.image_id','LEFT')->field("a.kind_id,a.kind_name,a.path,a.top_id,a.sort,FROM_UNIXTIME(a.create_time,'%Y-%m-%d') as create_time,FROM_UNIXTIME(a.update_time,'%Y-%m-%d') as update_time,a.image_id,b.domain,b.image_url,b.themb_url,b.extension")->where(['a.top_id'=>$top_id,'a.status'=>0])->order('a.kind_id asc,a.sort desc')->select(); 
	}
	
	
	
}