<?php

namespace app\admin\controller;  
 
use app\admin\model\Story as StoryModel; 
use \think\Validate;
/**
 * 故事模块
 * Class Story
 * @package app\admin\controller
 */
class Story extends Controller
{
	public function del_story($story_id,$flag,$reason=''){
		switch($flag){
			case 1:
				//删除
				$data['del_time']=time();
				$msg="删除";
				$data['status']=1;
			break; 
		}
		$story_model=new StoryModel;
		if($story_model->save_story(['story_id'=>$story_id],$data)){
			 return $this->renderSuccess($msg.'成功');
		}else{
			return $this->renderError($msg.'失败');	
		}
	}
	
	public function story_list($keywords='',$sort=1,$page=1, $country='',$province='',$city='',$region='', $kind_id=0,$status=''){ 
		$story_model=new StoryModel;
		$data=$story_model->story_list($keywords ,$sort ,$page,$country,$province ,$city,$region,$kind_id,$status);
		return $this->renderSuccess($data);
	} 
	 
}