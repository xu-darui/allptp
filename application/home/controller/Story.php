<?php

namespace app\home\controller; 
use app\home\model\Story as StoryModel;  
use app\common\model\Kind as KindModel;
use app\home\model\Visit as VisitModel;   
use app\home\model\Praise as PraiseModel;   
use \think\Validate;
use think\Cache;
/**
 * 故事
 * Class Story
 * @package app\home\controller
 */
class Story extends Controller
{
	public function get_kind($top_id=0){ 
		$kind_model=new KindModel; 
		if($kind=$kind_model->with('subkind')->where(['top_id'=>$top_id,'status'=>0])->select()){
			return $this->renderSuccess($kind);
		}else{
			return $this->renderError('暂无数据');
		}
		
	}
	public function save_story(){
		$this->getuser();
		$data=input();
		/* if(array_key_exists('image',$data)){
			$data['image']=(json_decode(html_entity_decode($data['image']),true));  
		}  */
		$data['user_id']=$this->user_id;
		$story_model=new StoryModel;
		if($story_model->save_story($data)){
			return $this->renderSuccess('保存成功');
		}else{
			return $this->renderError('保存失败');
		}
	}
	public function get_story($story_id,$visit=0){
		//$this->getuser(); 
		$story_model=new StoryModel;
		if($visit&&$this->user_id){
			$visit_model=new VisitModel;	
			$visit_model->add(['user_id'=>$this->user_id,'flag'=>2,'table_id'=>$story_id]);	 
		} 
		if($story=$story_model->get_story(['story_id'=>$story_id],Cache::get($this->token)['user']['user_id'])){
			return $this->renderSuccess($story);
		}else{
			return $this->renderError('暂无数据');
		}
		
	}
	public function del_story($story_id){
		$userdata=$this->getuser();
		$story_model=new StoryModel;
		if($story_model->del_story(['story_id'=>$story_id],$userdata['user_id'])){
			 return $this->renderSuccess('删除成功');
		}else{
			return $this->renderError('删除失败');	
		}
	}
	public function story_list($keywords='',$page=1,$sort=1,$kind_id=0,$country='',$province='',$city='',$region=''){
		$story_model=new StoryModel;
		$story=$story_model->story_list($keywords,$page,$sort,$kind_id,$country ,$province ,$city ,$region,Cache::get($this->token)['user']['user_id']);
		return $this->renderSuccess($story);
	}
	
	public function similar($story_id){
		$story_model=new StoryModel;
		$story_data=$story_model->detail(['story_id'=>$story_id]); 
		if(!$story_data){
			return $this->renderError('没有该活动');
		}
		$kind_array=explode(',',$story_data['kind_id']);
		return $this->renderSuccess($story_model->similar($kind_array,$story_id,$this->user_id));
	}	
	
	public function praise($story_id){
		$praise_model=new PraiseModel;
		return $this->renderSuccess($praise_model->prase_user($story_id,Cache::get($this->token)['user']['user_id']));
	}
	
	public function create_list($keywords='',$page=1,$sort=1,$kind_id=0){
		$userdata=$this->getuser();
		$story_model=new StoryModel;
		return $this->renderSuccess($story_model->create_list($keywords,$page,$sort,$kind_id,$userdata['user_id']));
	}
	
	public function popular_list(){
		$story_model=new StoryModel;
		return $this->renderSuccess($story_model->popular_story_list());
	}

}