<?php

namespace app\admin\controller;  
 
use app\admin\model\Browse as BrowseModel;  
use app\admin\model\Activity as ActivityModel;  
use app\admin\model\Story as StoryModel;  
use app\admin\model\User as UserModel;  
use think\helper\Time;
/**
 * 统计模块
 * Class Statistics
 * @package app\admin\controller
 */
class Statistics extends Controller
{
	
	public function browse(){ 
		$browse_model=new BrowseModel;
		$input=input();
		$where=$this->constract_where($input);  
		if(array_key_exists('time_type',$input)&&$input['time_type']==2){
			$return_data=$browse_model->statistics($where['where'],$where['format']);
			$statics=$this->quarter($return_data);
			
		}else{
			$statics=$browse_model->statistics($where['where'],$where['format']);
		}
		return $this->renderSuccess($statics);
	}
	
	public function activity(){
		$activity_model= new ActivityModel;
		$input=input();
		$where_kind='';
		if(array_key_exists('kind_id',$input)){
			$where_kind="find_in_set('".$input['kind_id']."',kind_id)";
		} 
		$where=$this->constract_where($input,'audit_time');
		if(array_key_exists('time_type',$input)&&$input['time_type']==2){
			$return_data=$activity_model->statistics($where['where'],$where['format'],$where_kind);
			$statics=$this->quarter($return_data);
			
		}else{
			$statics=$activity_model->statistics($where['where'],$where['format'],$where_kind);
		}
		
		return $this->renderSuccess($statics);
	}	
	
	public function story(){
		$story_model= new StoryModel;
		$input=input();
		$where_kind='';
		if(array_key_exists('kind_id',$input)){
			$where_kind="find_in_set('".$input['kind_id']."',kind_id)";
		} 
		$where=$this->constract_where($input,'create_time');
		if(array_key_exists('time_type',$input)&&$input['time_type']==2){
			$return_data=$story_model->statistics($where['where'],$where['format'],$where_kind);
			$statics=$this->quarter($return_data);
		}else{
			$statics=$story_model->statistics($where['where'],$where['format'],$where_kind);
		}
		
		return $this->renderSuccess($statics);
	}
	
	public function statistics_kind($flag){
		if($flag==1){
			$activity_model= new ActivityModel;  
			$statics=$activity_model->statistics_kind();
		}else{ 
			$story_model= new StoryModel;  
			$statics=$story_model->statistics_kind();
		}
		return $this->renderSuccess($statics);
		
		
	}
	
	
	public function user_role_kind(){
		$user_model=new UserModel;
		return $this->renderSuccess($user_model->user_role_kind());
	}
	
	
	public function user(){
		$user_model= new UserModel;
		$input=input(); 
		$where=$this->constract_where($input,'create_time');
		if(array_key_exists('time_type',$input)&&$input['time_type']==2){
			$return_data=$user_model->statistics_add($where['where'],$where['format']);
			$statics['register']=$this->quarter($return_data);
		}else{
			$statics['register']=$user_model->statistics_add($where['where'],$where['format']);
		}
		
		$where_cancel=$this->constract_where($input,'cancel_time');
		
		if(array_key_exists('time_type',$input)&&$input['time_type']==2){
			$return_data=$user_model->statistics_cancel($where_cancel['where'],$where_cancel['format']);
			$statics['cancel']=$this->quarter($return_data);
		}else{
			$statics['cancel']=$user_model->statistics_cancel($where_cancel['where'],$where_cancel['format']);
		} 
		return $this->renderSuccess($statics);
	}



	
	private function quarter($return_data){
			$quarter_1=[1,2,3];
			$quarter_2=[4,5,6];
			$quarter_3=[7,8,9];
			$quarter_4=[10,11,12];
			$statics=[
				['create_time_copy'=>'1季度','count'=>0],
				['create_time_copy'=>'2季度','count'=>0],
				['create_time_copy'=>'3季度','count'=>0],
				['create_time_copy'=>'4季度','count'=>0],
				];
			foreach($return_data as $key=>$value){ 
				$create_time_copy=explode('-',$value['time']);	
				if(in_array($create_time_copy[1],$quarter_1)){ 
					$statics[0]['count']=$statics[0]['count']+$value['count'];
				}else if(in_array($create_time_copy[1],$quarter_2)){ 
					$statics[1]['count']=$statics[1]['count']+$value['count'];
				}else if(in_array($create_time_copy[1],$quarter_3)){ 
					$statics[2]['count']=$statics[2]['count']+$value['count'];
				}else{ 
					$statics[3]['count']=$statics[3]['count']+$value['count'];
				}
			} 
			return $statics;
	}
	
	private function constract_where($input,$create_time='create_time'){
		$where=[];
		if(array_key_exists('begin_time',$input)&&$input['begin_time']&&array_key_exists('end_time',$input)&&$input['end_time']){
			$where[$create_time]=['between',[strtotime($input['begin_time']),strtotime($input['end_time'])]]; 
		}
		$format='%Y-%c-%d';
		if(array_key_exists('time_type',$input)){
			switch($input['time_type']){
				//0 天/周 
				case 0:
				$list=Time::week();
				$format='%Y-%c-%d';
				$where[$create_time]=['between',$list];
				break;
				
				//1 月/年 
				case 1:
				$list=Time::year();
				$format='%Y-%c';
				$where[$create_time]=['between',$list];
				break;
				
				//2季度/年
				case 2:
				$list=Time::year();
				$format='%Y-%c';
				$where[$create_time]=['between',$list];
				break;
				
				//3 年/5年
				case 3:
				$list=Time::lastYear5();
				$format='%Y';
				$where[$create_time]=['between',$list];
				break;
				
			}
		}
		if(array_key_exists('type',$input)){
			
			switch($input['type']){
				//1国内
				case 1: 
				$where['country_id']=0; 
				break;
				
				// 2国外
				case 2: 
				$where['country_id']=['neq',0]; 
				break;	
				
			}
		}
		
		if(array_key_exists('country_id',$input)){
			$where['country_id']=$input['country_id']; 
		}	
		
		if(array_key_exists('register_type',$input)){
			$where['register_type']=$input['register_type']; 
		}
		
		if(array_key_exists('role',$input)){
			switch($input['role']){
				//策划者
				case 1:
					$where['isplanner']=1;
					break;
				//志愿者
				case 2:
					$where['isvolunteer']=1;
					break;
					
				//游客
				case 3:
					$where['isvolunteer']=['neq',1];
					$where['isplanner']=['neq',1];
					break;
			}
			
		}
		
			
			return ['where'=>$where,'format'=>$format];
	}

}