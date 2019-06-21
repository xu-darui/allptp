<?php

namespace app\home\model;

use app\common\model\ActivitySlot as ActivitySlotModel;

/**
 * 问题模型
 * Class Question
 * @package app\store\model
 */
class ActivitySlot extends ActivitySlotModel
{
	public function save_long_slot($data,$activity_id){	
		foreach($data as $key=>$value){  
			$slot_value[$key]['activity_id']=$activity_id;
			$slot_value[$key]['max_person_num']=$value['max_person_num'];
			$slot_value[$key]['price']=$value['price'];
			$slot_value[$key]['begin_time']=$begin_time=strtotime($value['begin_date'].''.$value['begin_time']);
			$slot_value[$key]['end_time']=$end_time=strtotime($value['end_date'].''.$value['end_time']);
			$slot_value[$key]['total_time']=($end_time-$begin_time);
			$slot_value[$key]['begin_date']=strtotime($value['begin_date']); 
			$slot_value[$key]['end_date']=strtotime($value['end_date']); 
			$slot_value[$key]['status']=0; 
			if(array_key_exists('slot_id',$value)&&$value['slot_id']){ 
				$slot_value[$key]['slot_id']=$value['slot_id'];
			}  
		}	
			$this->allowField(true)->saveAll($slot_value);			
	}
	
	public function save_day_slot($data,$activity_id){
		$i=0;
		foreach($data as $key_day=>$value_day){
			foreach($value_day['list'] as $key=>$value){
				$slot_value[$i]['activity_id']=$activity_id;
				$slot_value[$i]['max_person_num']=$value['personNum'];
				$slot_value[$i]['price']=$value['price']; 
				$slot_value[$i]['begin_time']=$begin_time=strtotime($value_day['day'].''.$value['time'][0]);
				$slot_value[$i]['end_time']=$end_time=strtotime($value_day['day'].''.$value['time'][1]);
				$slot_value[$i]['total_time']=($end_time-$begin_time);
				$slot_value[$i]['date']=strtotime($value_day['day']); 
				$slot_value[$i]['status']=0;
				if(array_key_exists('slot_id',$value)&&$value['slot_id']){ 
				$slot_value[$i]['slot_id']=$value['slot_id'];
				}
				$i++;
			} 
			
		}	
		$this->allowField(true)->saveAll($slot_value);

		
		/* foreach($data as $key=>$value){  
			$value['activity_id']=$activity_id;
			$value['begin_time']=strtotime($data['date'].''.$value['begin_time']);
			$value['end_time']=strtotime($data['date'].''.$value['end_time']);
			$value['total_time']=($value['end_time']-$value['begin_time'])/60;
			$value['date']=strtotime($data['date']); 
			$value['status']=0; 
			if(array_key_exists('slot_id',$value)&&$value['slot_id']){ 
				$this->allowField(true)->update($value,['slot_id'=>$value['slot_id']]);
			}else{
				$value['slot_id']='';
				$this->allowField(true)->isUpdate(false)->save($value);
			}			
		}  */
				
	}
	
	public function delete_slot($activity_id){
		$this->where(['status'=>['neq',1],'activity_id'=>$activity_id])->update(['status'=>1]);
	}
	
	public function get_date_day($startdate, $enddate){

    $stimestamp = strtotime($startdate);
    $etimestamp = strtotime($enddate);

    // 计算日期段内有多少天
    $days = ($etimestamp-$stimestamp)/86400+1;

    // 保存每天日期
    $date = array();

    for($i=0; $i<$days; $i++){
        $date[] = date('Y-m-d', $stimestamp+(86400*$i));
    }

    return $date;
}
	
	public function detail($where){
		return ActivitySlotModel::get($where); 
		//return $this->with('activity')->where(['slot'=>$slot])->find();
	}
	
	public function create_slot_array($slot){  
		$slot_day=array_unique(array_column($slot,'date')); 
		//pre($slot_day);
		$i=0;
		$all_slot=[];
		foreach($slot_day as  $day_value){
			$all_slot[$i]['day']=$day_value;
			$all_slot[$i]['status']=2;
			foreach($slot as $key=>$value){
				if($day_value==$value['date']){ 
					if(array_key_exists('order_num',$value)) $slot_value['order_num']=$value['order_num'];
					if(array_key_exists('refund_num',$value)) $slot_value['refund_num']=$value['refund_num'];
					if(array_key_exists('enroll_count',$value)) $slot_value['enroll_count']=$value['enroll_count'];
					$slot_value['online']=$value['online'];
					$slot_value['status']=$value['status'];
					$slot_value['price']=$value['price'];
					$slot_value['personNum']=$value['max_person_num'];
					$slot_value['total_time']=$value['total_time'];
					$slot_value['slot_id']=$value['slot_id'];
					$slot_value['time']=[$value['begin_time'],$value['end_time']];
					$list[$day_value][]=$slot_value; 
					if($value['status']==0){
						$all_slot[$i]['status']=0;
					}
				} 
			}
			$all_slot[$i]['list']=$list[$day_value];
			$i++;
		}
		
		return $all_slot;
	}
	
	public function save_slot($data,$where){
		return $this->where($where)->update($data);
	}
	
	public function all_status($activity_id){
		return $this->where(['activity_id'=>$activity_id,'status'=>0])->column('slot_id');
	}
	
	public function save_slot_one($data){
		if($data['long_day']){
			//短时间 
			$data['begin_date']=0;
			$data['end_date']=0;
			$data['begin_time']=strtotime($data['date'].''.$data['begin_time']);
			$data['end_time']=strtotime($data['date'].''.$data['end_time']);
			$data['date']=strtotime($data['date']); 
		}else{ 
			$data['date']=0;
			//长时间
			$data['begin_time']=strtotime($data['begin_date'].''.$data['begin_time']);
			$data['end_time']=strtotime($data['end_date'].''.$data['end_time']);
			$data['begin_date']=strtotime($data['begin_date']);
			$data['end_date']=strtotime($data['end_date']); 
		}
		$data['total_time']=($data['end_time']-$data['begin_time'])/60;
		if(array_key_exists('slot_id',$data)&&$data['slot_id']>0){
			$result=$this->allowField(true)->save($data,['slot_id'=>$data['slot_id']]);
		}else{
			unset($data['slot_id']);
			$result=$this->allowField(true)->save($data);
		}
		if($result){
			return true;
		}else{
			return false; 
		}
		
	}
	


}