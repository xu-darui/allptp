<?php

namespace app\admin\model;

use app\common\model\Activity as ActivityModel; 
use think\Db;

/**
 * 活动模型
 * Class Activity
 * @package app\admin\model
 */
class Activity extends ActivityModel
{
	public function save_activity($where,$data){  
		$activity_model=new ActivityModel;
		$user_id=$this->where(['activity_id'=>$where['activity_id']])->value('user_id');
		$num=$activity_model->user_acti_num($user_id);
		Db::name('user')->where(['user_id'=>$user_id])->update(['activ_num'=>$num]);
		return  $this->where($where)->update($data);   
		
	}
	
	public function activity_list($keywords ,$sort ,$page,$country ,$province ,$city ,$region ,$kind_id,$status,$audit,$online){
		$activity_model=new ActivityModel;  
			//$where_kind='';	
			switch($sort){
				case 1;
					$order="score desc";
					break;
				case 2; 
					$order="sale_num desc";
					break;
				case 3; 
					$order="collection_num desc";
					break;
				case 4; 
					$order="comment_num desc";
					break;
				case 5;
					$order="leaving_num desc";
					break;
				default:
					$order="activity_id desc";
			}
			if($status!==''){
				$where['status']=$status;
			} 
			if($online!==''){
				$where['online']=$online;
			} 
			if($audit!==''){
				$where['audit']=$audit;
			}
			
			if($country){
				$where['country']=['like','%'.$country.'%'];
			}
			if($province){
				$where['province']=['like','%'.$province.'%'];
			}
			if($city){
				$where['city']=['like','%'.$city.'%']; 
			}
			if($region){
				$where['region']=['like','%'.$region.'%'];
			} 
			if($kind_id){
				$kind_id=Db::name('kind')->where("find_in_set($kind_id,path)")->whereor('kind_id',$kind_id)->column('kind_id');  
				$where['kind_id']=['in',$kind_id];
				//$where_kind="find_in_set($kind_id,kind_id)";
			}   
			$where['complete']=1;
			//pre($where);
			//pre($where);
			if($keywords){
				$where["title|introduce|descripte"]=['like','%'.$keywords.'%'];
				$data=$activity_model
					->with(['slot','user'=>function($query){$query->field("user_id,family_name,middle_name,name,country,CONCAT('m_code','mobile') as 'mobile'");},'image'=>function($query){$query->where(['flag'=>1]);},'cover','kindpath'])
					->where($where) 
					->whereor("CONCAT(IFNULL(country,''),IFNULL(province,''),IFNULL(city,''),IFNULL(region,'')) like '%".$keywords."%'")
					->order($order)
					->paginate(10, false, ['query' => ["page"=>$page]]); 
			}else{ 
				$data=$activity_model
					->with(['slot','user'=>function($query){$query->field("user_id,family_name,middle_name,name,country,CONCAT('m_code','mobile') as 'mobile'");},'image'=>function($query){$query->where(['flag'=>1]);},'cover','kindpath'])
					->where($where) 
					->order($order)
					->paginate(10, false, ['query' => ["page"=>$page]]);
				 //pre($activity_model->getlastsql());
			}
			return $data; 
		
	} 
	
	public function statistics($where,$format,$where_kind){
		
		$data=Db::name('activity')->field("FROM_UNIXTIME(audit_time, '".$format."' ) as time,count('id') as count")->group('time')->order('audit_time desc')->where($where)->where($where_kind)->select();
		return $data;
		
	}

	public function statistics_kind(){
		$kind=Db::name('kind')->field('kind_id,kind_name')->where(['top_id'=>0,'status'=>0])->select()->toArray();
		foreach($kind as $key=>$value){  
			$where_kind="find_in_set('".$value['kind_id']."',kind_id)";
			$kind[$key]['count']=Db::name('activity')->where(['status'=>0,'audit'=>1])->where($where_kind)->count('activity_id');
		} 
		return $kind;
		
	}
	//成为策划者
	public function become_planner($activity_id){
		$user_id=Db::name('activity')->where(['activity_id'=>$activity_id])->value('user_id');
		if(!Db::name('user')->where(['user_id'=>$user_id])->value('isplanner')){
			Db::name('user')->where(['user_id'=>$user_id])->update(['isplanner'=>1]);
		}
	}
	
	
}