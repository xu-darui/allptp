<?php

namespace app\home\model;
use app\common\model\Enroll as EnrollModel; 
use app\home\model\Kind as KindModel; 
use app\common\model\ActivitySlot; 
use think\Db;
/**
 * 志愿者报名
 * Class Enroll
 * @package app\store\model
 */
class Enroll extends EnrollModel
{
	public function save_enroll($data){
		if(array_key_exists('enroll_id',$data)&&$data['enroll_id']>0){
			$this->allowField(true)->save($data,['enroll_id'=>$data['enroll_id']]);
			return $data['enroll_id'];
		}else{
			$this->allowField(true)->save($data);
			return $this->enroll_id;
		}
	}
	 
	public function enroll_list($where){
		$data=$this->with(['user.headimage','activity.cover'])->where($where)->order('enroll_id desc')->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]]);
		$slot_model=new ActivitySlot;
		foreach($data as $key=>$value){
			$data[$key]['language']=explode(',',$value['language']);  
			$data[$key]['free_time']=explode(',',$value['free_time']);  
			$data[$key]['slot_id']=Db::name('activity_slot')->field("slot_id,FROM_UNIXTIME(begin_time, '%Y-%c-%d %h:%i' )  as begin_time,FROM_UNIXTIME(end_time, '%Y-%c-%d %h:%i' ) as end_time")->Where(['slot_id'=>['in',explode(',',$value['slot_id'])]])->select();
			
		}
		return $data;
		
	}
	
	public function my_enroll_list($where){
		$data=$this->with(['activity.cover','user.headimage'])->where($where)->order('enroll_id desc')->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]]);
			$kind_model=new KindModel;
		foreach($data as $key=>$value){ 
			$data[$key]['language']=explode(',',$value['language']);  
			$data[$key]['free_time']=explode(',',$value['free_time']); 
			$slot_id=Db::name('activity_slot')->field("slot_id,FROM_UNIXTIME(begin_time, '%Y-%c-%d %h:%i' )  as begin_time,FROM_UNIXTIME(end_time, '%Y-%c-%d %h:%i' ) as end_time,price")->Where(['slot_id'=>['in',explode(',',$value['slot_id'])]])->order('slot_id asc')->select();			
			$data[$key]['slot_id']=$slot_id;
			$data[$key]['price']=$slot_id[0]['price']; 
			$data[$key]['activity']['kind']=$kind_model->field('kind_id,kind_name')->where(['kind_id'=>['in',$value['activity']['kind_id']]])->select()->toArray();
		}
		return $data;
	}
	
}