<?php

namespace app\admin\controller;

use app\admin\model\Config as ConfigModel;
use think\Db;

/**
 * 配置管理
 * Class Config
 * @package app\store\controller
 */
class Config extends Controller
{
	public function add(){
		
		$input=input();
		$config_model=new ConfigModel;
		$config=$config_model->get_config();
		unset($input['token']);
		$input['credit']=json_decode(html_entity_decode($input['credit']),true); 
		if(intval($config['credit']['init_score'])!==intval($input['credit']['init_score'])){
			//如果修改了初始分值  则所有初始分值都要对应增加
			$change_score=$input['credit']['init_score']-$config['credit']['init_score'];
			if($change_score>0){
				Db::name('user')->where(['isplanner'=>1,'status'=>0])->setInc('credit_score',$change_score);
			}else{
				$change_score=abs($change_score);
				Db::name('user')->where(['isplanner'=>1,'status'=>0,'credit_score'=>['lt',$change_score]])->update(['credit_score'=>0]);
				Db::name('user')->where(['isplanner'=>1,'status'=>0,'credit_score'=>['gt',$change_score]])->setDec('credit_score',$change_score);
				
			}
			
		}
		$data['values']=json_encode($input);
		if($config_model->save_config(['key'=>'config'],$data)){
			return $this->renderSuccess("修改成功");
		}else{
			return $this->renderError("修改失败");
		}
	}
	public function get(){
		$config_model=new ConfigModel;
		return $this->renderSuccess($config_model->get_config());
	}
}