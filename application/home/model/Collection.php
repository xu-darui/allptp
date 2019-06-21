<?php

namespace app\home\model; 
use app\common\model\Collection as CollectionModel; 
use app\common\model\Collegroup as CollegroupModel; 
use app\common\model\Story as StoryModel; 
use app\common\model\Activity as ActivityModel; 
use app\common\model\User as UserModel; 
use app\common\model\Kind as KindModel; 
use app\common\model\Forward as ForwardModel; 

use think\Db;
/**
 * 收藏
 * Class Collection
 * @package app\store\model
 */
class Collection extends CollectionModel
{
	public function get_collection($where){
		return $this->where($where)->find();
		
	}
	public function add_collection($data){
		Db::startTrans();
		try{
			/* $other_group=$this->get_collection(['flag'=>$data['flag'],'table_id'=>$data['table_id'],'status'=>0,'user_id'=>$data['user_id']]);
			if($other_group){
				$this->remove_collection(['flag'=>$data['flag'],'table_id'=>$data['table_id'],'group_id'=>$other_group['group_id']]);
			}  */
			$collection_data=$this->get_collection($data); 
			if($collection_data['status']===1){
				$this->allowField(true)->where(['collection_id'=>$collection_data['collection_id']])->update(['create_time'=>time(),'status'=>0,'uncol_time'=>0]);
			}else{
				$this->allowField(true)->save($data); 
			}
			switch($data['flag']){
				case 1:
					$activity_model=new ActivityModel;
					$activity_model->where(['activity_id'=>$data['table_id']])->setInc('collection_num');
					$user_model=new UserModel;
					$user_model->where(['user_id'=>$data['user_id']])->setInc('col_activ_num');
					break;
				case 2:
					$story_model=new StoryModel;
					$story_model->where(['story_id'=>$data['table_id']])->setInc('collection_num');
					$user_model=new UserModel;
					$user_model->where(['user_id'=>$data['user_id']])->setInc('col_story_num');
					break;
				case 3: 
					ForwardModel::where(['forward_id'=>$data['table_id']])->setInc('collection_num');
					break;
			}
		// 提交事务
			Db::commit(); 
			return true;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}
		
	}
	public function remove_collection($where){ 
		Db::startTrans();
		try{
			CollectionModel::where($where)->update(['status'=>1,'uncol_time'=>time()]);
			switch($where['flag']){
				case 1:
					$activity_model=new ActivityModel;
					$activity_model->where(['activity_id'=>$where['table_id']])->setDec('collection_num');
					$user_model=new UserModel;
					$user_model->where(['user_id'=>$where['user_id']])->setDec('col_story_num');
					break;
				case 2:
					$story_model=new StoryModel;
					$story_model->where(['story_id'=>$where['table_id']])->setDec('collection_num');
					$user_model=new UserModel;
					$user_model->where(['user_id'=>$where['user_id']])->setDec('col_story_num');
					break;
				case 3: 
					ForwardModel::where(['forward_id'=>$data['table_id']])->setDec('collection_num');
					break;	
			
			}
		// 提交事务
			Db::commit(); 
			return true;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}
		
		
	}
	public function add_collegroup($data){
		$collegroup_model=new CollegroupModel;
		if(array_key_exists('group_id',$data)&& $data['group_id']){
			$collegroup_model->allowField(true)->save($data,['group_id'=>$data['group_id']]);
			return $data['group_id'];
		}else{
			unset($data['group_id']);
			$collegroup_model->allowField(true)->save($data);
			return $collegroup_model->group_id;
		}
		
		
		
	}
	
	public function del_collegroup($group_id){
		Db::startTrans();
		try{
			$this->where(['group_id'=>$group_id])->update(['status'=>1,'uncol_time'=>time()]);
			$collegroup_model=new CollegroupModel;
			$collegroup_model->where(['group_id'=>$group_id])->update(['status'=>1]);
		// 提交事务
			Db::commit(); 
			return true;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}	
	}
	public function collegroup_list($where,$flag,$table_id){
		
		$data=Db::name('collegroup')
		->alias('g')
		->field("g.group_id,g.group_name,count(c.table_id) as count,c.flag,c.table_id,'' as cover,g.hide")
		->join('collection c','g.group_id=c.group_id and c.status=0','LEFT')
		->group('g.group_id')
		->where($where)
		->select(); 
		//pre(Db::name('collegroup')->getlastsql());
		if($flag>0&&$table_id>0){
			$group_id= Db::name('collection')->where(['flag'=>$flag,'table_id'=>$table_id,'user_id'=>$where['g.user_id'],'status'=>0])->column('group_id');
		}
		foreach($data as $key=>$value){
			if($flag>0&&$table_id>0){
				$value['is_this_colle']=0; 
				if(in_array($value['group_id'],$group_id)){
					$value['is_this_colle']=1;
				}
			}
			if($value['flag']==1){
				$value['cover']= Db::name('activity')->alias('a')->field('i.*')->join('image i','i.image_id=a.cover_image','LEFT')->where(['a.activity_id'=>$value['table_id']])->find();
			}
			if($value['flag']==2){
				$value['cover']=Db::name('story')->alias('s')->field('i.*')->join('image i','i.image_id=s.cover_image','LEFT')->where(['s.story_id'=>$value['table_id']])->find();
			} 
			$data[$key]=$value;
		}
		return $data;
	}
	
	public function col_act_list($user_id,$group_id,$page){ 
	if($group_id>0){
		//通过分组返回  
		$data=Db::view('col_act_story')  
		->where(['group_id'=>$group_id])
		->paginate(10, false, ['query' => ["page"=>$page]])->toArray();   
	}else{
		//返回全部
		$data=Db::view('col_act_story') 
		->view('ptp_collegroup','group_name','ptp_collegroup.group_id=col_act_story.group_id')
		->where(['col_act_story.user_id'=>$user_id])
		->paginate(10, false, ['query' => ["page"=>$page]])->toArray(); 
	} 
		if($data){
			$kind_model=new KindModel;
			$data=$kind_model->addkind_array($data);
		} 
		return $data;
	}
	
}