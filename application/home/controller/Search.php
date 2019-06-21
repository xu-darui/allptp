<?php

namespace app\home\controller; 
use app\home\model\Story as StoryModel;  
use app\home\model\Activity as ActivityModel;  
use app\home\model\Search as SearchModel;  
use app\home\model\User as UserModel;  
use \think\Validate;
use think\Cache;
/**
 * 搜索
 * Class Search
 * @package app\home\controller
 */
class Search extends Controller
{
	
	public function search($keywords,$page=1){
		$user_id=Cache::get($this->token)['user']['user_id'];
		if($keywords!==''){
			if($user_id){
				$search_model=new SearchModel;
				 $search_model->save_search(['flag'=>1,'keywords'=>$keywords,'user_id'=>$user_id]);
			} 
		} 
		$activity_model=new ActivityModel; 
		$activity=$activity_model->activity_list($keywords,1,$page,0,0,'','','','',0,0,'',0,0,$user_id,0); 	
		$data['activity']=$activity;
		$story_model=new StoryModel;
		$story=$story_model->story_list($keywords,$page,2,0,'','','','',0,$user_id,0); 
		$data['story']=$story;
		$user_model=new UserModel;
		$user=$user_model->user_list($keywords,$page,2,'','','','',1,$user_id,0,1,0); 
		$data['user']=$user;
		return $this->renderSuccess($data);
	}
/* 	public function index($keywords='',$flag=1,$page=1,$sort=1,$kind_1=0,$city=''){
		switch($sort){
			case 1;
				$order="score desc";
				break;
			case 2; 
				$order="praise_num desc";
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
		}
		$search_model=new SearchModel;
		switch($flag){
			case 1; 
			$activity_model=new ActivityModel; 
			$activity=$activity_model->activity_list($keywords,$page,$order,$city); 
			$data['activity']=$activity;
			$story_model=new StoryModel;
			$story=$story_model->story_list($keywords,$page,$order,$kind_1); 
			$data['story']=$story;
			$user_model=new UserModel;
			$user=$user_model->user_list($keywords,$page,$order); 
			$data['user']=$user;
			break; 
			case 2;
			$activity_model=new ActivityModel; 
			$activity=$activity_model->activity_list($keywords,$page,$order,$city); 
			$data['activity']=$activity;
			break;
			case 3;
			$story_model=new StoryModel;  
			$story=$story_model->story_list($keywords,$page,$order,$kind_1);  
			$data['story']=$story;
			break;
			default:
			
		} 
		return $this->renderSuccess($data);
		$search_model->add_search(['keywords'=>$keywords,'flag'=>$flag]);
	} */
}