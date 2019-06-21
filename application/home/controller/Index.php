<?php
namespace app\home\controller;
use app\home\model\Visit as VisitModel; 
use app\home\model\Search as SearchModel; 
use app\home\model\Kind as KindModel; 
use app\common\model\JpushM; 
use think\Cache;
use JPush\Client as JPush;
/**
 * 后台首页
 * Class Index
 * @package app\store\controller
 */
class Index extends Controller
{
	
	public function visit_lately($page){
		$visit_model=new VisitModel;
		$data=$visit_model->visit_lately($this->user_id,$page);
		return $this->renderSuccess($data);
	}	
	
	public function search_lately(){
		$search_model=new SearchModel;
		$data=$search_model->search_lately($this->user_id);
		return $this->renderSuccess($data);
	}
	
	public function story_kind($top_id){
		$kind_model=new KindModel;
		$data=$kind_model->story_kind($top_id);
		return $this->renderSuccess($data);
	}
	
	public function token(){	
		if($token=$this->maketoken()){
			Cache::set($token, [
            'user' => [
                'user_id' =>0, 
            ], 
        ],86400*7); 
			return $this->renderSuccess($token);
		}else{
			return $this->renderError("token生成错误");
		}
		
	} 
	public function index(){
		//$res=JpushM::pushNewInfoNotice(['100d8559090d20d2871','1a0018970aec47ef0c7'],'allptp测试推送','Wish','测试content');
		$res=JpushM::pushNewInfoNotice('all','allptp测试推送','Wish','测试content');
		pre($res);
	}
	
}
