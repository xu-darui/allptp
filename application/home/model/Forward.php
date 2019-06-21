<?php

namespace app\home\model;
use app\common\model\Forward as ForwardModel;  
use app\common\model\Activity as ActivityModel;  
use app\common\model\Story as StoryModel;  
use think\Db;
/**
 * 转发
 * Class Forward
 * @package app\store\model
 */
class Forward extends ForwardModel
{
	public function save_forward($data){
		Db::startTrans();
		try{ 
			$this->allowField(true)->save($data);
			switch($data['flag']){ 
					case 1:
					ActivityModel::where(['activity_id'=>$data['table_id']])->setInc('forward_num');
					break;
					case 2:
					StoryModel::where(['story_id'=>$data['table_id']])->setInc('forward_num');
					break; 
				} 
		// 提交事务
			Db::commit(); 
			return $this->forward_id;
		}catch (\Exception $e) {
				// 回滚事务
			Db::rollback();
				
		}  
	}
	
	public function del_forward($forward_id){
		Db::startTrans();
		try{
			$data=ForwardModel::where(['forward_id'=>$forward_id])->find();
			ForwardModel::where(['forward_id'=>$forward_id])->update(['status'=>1]);
			switch($data['flag']){ 
				case 1:
				ActivityModel::where(['activity_id'=>$data['table_id']])->setDec('forward_num');
				break;
				case 2:
				StoryModel::where(['story_id'=>$data['table_id']])->setDec('forward_num');
				break; 
			} 
		// 提交事务
			Db::commit(); 
			return true;
		}catch (\Exception $e) {
				// 回滚事务
			Db::rollback();
				
		}  
	}
	
	public function dynamic_forward($where){
		$forward_model=new ForwardModel;
		$activity_model=new ActivityModel;
		$story_model=new StoryModel;
		$forward_data=ForwardModel::where($where)->find();
		if($forward_data['flag']==1){
			//转发活动
			$data= $activity_model->field('activity_id,title,introduce,descripte,country,province,city,region,user_id,create_time,1 as flag')->with(['user.headimage','image'])->where(['activity_id'=>$forward_data['table_id']])->find();
			
		}else{
			//转发故事
			$data= $story_model->field('story_id,title,content,country,province,city,region,user_id,create_time,2 as flag')->with(['user.headimage','image'])->where(['story_id'=>$forward_data['table_id']])->find();
		}
		$data['praise_num']=$forward_data['praise_num'];
		$data['leaving_num']=$forward_data['leaving_num'];
		$data['collection_num']=$forward_data['collection_num'];
		return $data;
	}
	 
	
	
}