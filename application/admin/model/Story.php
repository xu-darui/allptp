<?php

namespace app\admin\model;

use app\common\model\Story as StoryModel; 
use app\common\model\User as UserModel; 
use app\common\exception\BaseException;
use think\Db;
/**
 * 故事模型
 * Class Story
 * @package app\admin\model
 */
class Story extends StoryModel
{
	public function save_story($where,$data){ 
		$story=$this->field('story_id,user_id,status')->where(['story_id'=>$where['story_id']])->find();
		if($story&&$story['status']==1){
			throw new BaseException(['code' => 0, 'msg' => '该故事已经被删除']);
		}
		if($result= $this->where($where)->update($data)){
			UserModel::where(['user_id'=>$story['user_id']])->setDec('story_num');
		} 
		return $result;   
		
	}
	
	public function story_list($keywords ,$sort ,$page,$country ,$province ,$city ,$region ,$kind_id,$status){
		$story_model=new StoryModel;
			$where=[];
			//$where_kind='';	
			switch($sort){
				case 1;
					$order="collection_num desc";
					break;
				case 2; 
					$order="praise_num desc";
					break; 
				default:
					$order="story_id desc";
			}
			if($status!==''){
				$where['status']=$status;
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
			if($keywords){
				$where["title|content"]=['like','%'.$keywords.'%'];
				$data=$story_model
					->with([ 'user'=>function($query){$query->field("user_id,family_name,middle_name,name,country,CONCAT('m_code','mobile') as 'mobile'");},'image'=>function($query){$query->where(['flag'=>1]);},'kindpath'])
					->where($where) 
					->whereor("CONCAT(IFNULL(country,''),IFNULL(province,''),IFNULL(city,''),IFNULL(region,'')) like '%".$keywords."%'")
					->order($order)
					->paginate(10, false, ['query' => ["page"=>$page]]); 
			}else{ 
				$data=$story_model
					->with([ 'user'=>function($query){$query->field("user_id,family_name,middle_name,name,country,CONCAT('m_code','mobile') as 'mobile'");},'image'=>function($query){$query->where(['flag'=>1]);},'kindpath'])
					->where($where) 
					->order($order)
					->paginate(10, false, ['query' => ["page"=>$page]]);
				 //pre($activity_model->getlastsql());
			}
			return $data; 
		
	} 
	
	public function statistics_kind(){
		$kind=Db::name('kind')->field('kind_id,kind_name')->where(['top_id'=>0,'status'=>0])->select()->toArray();
		foreach($kind as $key=>$value){
			$where_kind="find_in_set('".$value['kind_id']."',kind_id)";
			$kind[$key]['count']=Db::name('story')->where(['status'=>0])->where($where_kind)->count('story_id');
		} 
		return $kind;
		
	}
	
	public function statistics($where,$format,$where_kind){
		
		$data=Db::name('story')->field("FROM_UNIXTIME(create_time, '".$format."' ) as time,count('id') as count")->group('time')->order('create_time desc')->where($where)->where($where_kind)->select();
		return $data;
		
	}
	
}