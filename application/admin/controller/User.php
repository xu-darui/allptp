<?php

namespace app\admin\controller;

use app\admin\model\User as UserModel;
use app\common\model\Sendmail; 
use app\common\model\Sendmsg as SendmsgModel;
/**
 * 用户管理
 * Class User
 * @package app\admin\controller
 */
class User extends Controller
{
    /**
     * 用户列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function user_list($keywords='',$role=0,$sort=0,$pagec){
		$user_model=new UserModel;
		 return $this->renderSuccess($user_model->user_list($keywords,$role,$sort,$page));
		
        
    }
	
	
	public function user_check_list($keywords='',$page=1){
		$user_model=new UserModel;
		 return $this->renderSuccess($user_model->user_list($keywords,0,0,$page,'user_check_list'));
	}
	
	public function check_planner($user_id,$flag,$reason=''){
		$user_model=new UserModel; 
		$data['audit_face']=$flag; 
		switch($flag){
			case 2:
				$msg="已通过";
				$data['audit_idcard']=1;  
				break;
			case 3:
				$msg="不通过";
				break;
			default:
				return $this->renderError('参数错误');
			
		}
		$data['refuse_reason']=$reason;
		$where['user_id']=$user_id; 
		if($user_model->save_user($data,$where)){
			//审核实名认证完成后发短信 发系统消息
			//$sendmail_model=new Sendmail;
				
			$sendmsg_model=new SendmsgModel;
			$sendmsg_model->send_check_planner($user_id);
			return $this->renderSuccess('审核操作成功');
		}else{
			return $this->renderError('审核失败');
		}
	}
	

}
