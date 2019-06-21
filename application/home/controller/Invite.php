<?php

namespace app\home\controller;     
use app\home\model\Invite as InviteModel; 
use \think\Validate; 
use think\Db;
use app\common\model\Sendmail; 
use app\common\model\Sendmsg as SendmsgModel;
/**
 * 邀请志愿者
 * Class Invite
 * @package app\home\controller
 */
class Invite extends Controller
{
	public function invite($isapp=0){

		$userdata=$this->getuser();
		$data=input(); 
		 
		//$data['slot_id']=[1,2];
		if($isapp) $slot=$data['slot_id']=json_decode($data['slot_id'],true);
		
		$data['invi_user_id']=$userdata['user_id'];
		$validate = new Validate([ 
			'user_id' => 'require', 
			'slot_id' => 'require', 
		],[ 
			'user_id.require'=>'请选择邀请志愿者',   
			'slot_id.require'=>'请选择体验时间段',  
		]); 
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
			//$data['slot_id']=[1,2];
		
		$data['slot_id']=implode(',',$data['slot_id']);		
		$is_invite=Db::name('invite')->where(['user_id'=>$data['user_id'],'slot_id'=>$data['slot_id'],'status'=>0,'audit'=>['neq',2]])->count();
		if($is_invite){
			return $this->renderError("该时间已经邀请过该志愿者");
		}
		$invite_model=new InviteModel; 
		if($id=$invite_model->save_invite($data)){
				//策划者邀请自愿者发短信 发邮件 
				$sendmsg_model=new SendmsgModel;
				$sendmsg_model->submit_invite($id);  
			 return $this->renderSuccess("邀请成功");
		}else{
			return $this->renderError("邀请失败");
		} 
	}
	
	public function del_invite($invite_id){
		$invite_model=new InviteModel;
		if($id=$invite_model->save_invite(['status'=>1,'invite_id'=>$invite_id])){
			 return $this->renderSuccess("撤回成功");
		}else{
			return $this->renderError("撤回失败");
		} 
	} 
	
	public function enroll_list($activity_id){
		$enroll_model=new EnrollModel;
		$where['activity_id']=$activity_id;
		$where['status']=0;
		return $this->renderSuccess($enroll_model->enroll_list($where));
	}
	
	public function audit_invite($invite_id,$flag){
		$invite_model=new InviteModel;
		$status=Db::name('invite')->where(['invite_id'=>$invite_id])->value('status');
		if($status){
			return $this->renderError('该邀请已经撤回');
		}
		if($invite_model->save_invite(['audit'=>$flag,'invite_id'=>$invite_id])){
			switch($flag){
				case 1:$msg='已同意';break;
				case 2:$msg='已谢绝';break;
			}
			//策划者邀请自愿者发短信 发邮件
			//$sendmail_model=new Sendmail;
				
			$sendmsg_model=new SendmsgModel;
			$sendmsg_model->audit_invite($invite_id); 
			 return $this->renderSuccess($msg);
		}else{
			return $this->renderError('操作失败');
		} 
	}
	
	public function my_invite_list(){
		$data=input();
		$userdata=$this->getuser();
		$where['a.invi_user_id']=$userdata['user_id'];
		if(array_key_exists('audit',$data)){
			$where['a.audit']=$data['audit'];
		}
		$where['a.activity_id']=$data['activity_id'];
		$where['a.status']=0;
		//pre($where);
		$invite_model=new InviteModel;
		return $this->renderSuccess($invite_model->my_invite_list($where));
	}
	
	public function invite_list(){
		$data=input();
		$userdata=$this->getuser();
		$where['user_id']=$userdata['user_id'];
		if(array_key_exists('audit',$data)){
			$where['audit']=$data['audit'];
		}
		$where['status']=0;
		$invite_model=new InviteModel;
		return $this->renderSuccess($invite_model->invite_list($where));
	}
}