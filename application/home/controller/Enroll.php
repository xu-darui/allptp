<?php

namespace app\home\controller;     
use app\home\model\Enroll as EnrollModel; 
use \think\Validate; 
use think\Db;
use app\common\model\Sendmail; 
use app\common\model\Sendmsg as SendmsgModel;
/**
 * 报名
 * Class Enroll
 * @package app\home\controller
 */
class Enroll extends Controller
{
public function enroll(){
		$userdata=$this->getuser();
		$data=input();
		if(array_key_exists('isapp',$data)&&$data['isapp']){
			$data['language']=json_decode($data['language'],true);
			$data['slot_id']=json_decode($data['slot_id'],true);
			$data['free_time']=json_decode($data['free_time'],true);
		}
		//$data['language']=['中文','english'];
		//$data['slot_id']=[1,2];
		//$data['free_time']=['2019-04-17','2019-04-18'];
		$validate = new Validate([
			'language'  => 'require',
			'skill' => 'require', 
			'activity_id' => 'require',
			'slot_id' => 'require',
			'introduce' => 'require',
		],[
			'language.require'=>'请输入您会的语言',
			'skill.require'=>'请输入您的技能',  
			'activity_id.require'=>'请选择体验', 
			'slot_id.require'=>'请选择体验时间段', 
			'introduce.require'=>'请介绍您自己', 
		]); 
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		$data['user_id']=$userdata['user_id'];
		$data['language']=implode(',',$data['language']);
		$data['slot_id']=implode(',',$data['slot_id']);
		$data['free_time']=implode(',',$data['free_time']);
		$enroll_model=new EnrollModel;
		if($id=$enroll_model->save_enroll($data)){
				//志愿者报名提交成功发短信 发邮件 
				$sendmsg_model=new SendmsgModel;
				$sendmsg_model->submit_enroll($id); 
			 return $this->renderSuccess("报名成功");
		}else{
			return $this->renderError("保存失败");
		} 
	}
	
	public function del_enroll($enroll_id){
		$enroll_model=new EnrollModel;
		if($id=$enroll_model->save_enroll(['status'=>1,'enroll_id'=>$enroll_id])){
			 return $this->renderSuccess("撤回成功");
		}else{
			return $this->renderError("撤回失败");
		} 
	} 
	
	public function enroll_list($activity_id=0,$slot_id=0,$audit=''){
		$enroll_model=new EnrollModel;
		if($activity_id){
			$where['activity_id']=$activity_id;
		}else{
			$userdata=$this->getuser();
			$activity_id_array=Db::name('activity')->where(['user_id'=>$userdata['user_id'],'complete'=>1,'audit'=>1])->column('activity_id');
			$where['activity_id']=['in',$activity_id_array];
		} 
		if($audit!=''){
			$where['audit']=$audit;
		}
		$where['status']=0;
		if($slot_id){
			$where['slot_id']=$slot_id;
		}
		return $this->renderSuccess($enroll_model->enroll_list($where));
	}
	
	public function audit_enroll($enroll_id,$flag){
		$enroll_model=new EnrollModel;
		$status=Db::name('enroll')->where(['enroll_id'=>$enroll_id])->value('status');
		if($status){
			return $this->renderError('该报名已经撤回');
		}
		if($enroll_model->save_enroll(['audit'=>$flag,'enroll_id'=>$enroll_id])){
			switch($flag){
				case 1:$msg='已同意';break;
				case 2:$msg='已谢绝';break;
			}
				//策划者审核完后发短信 发邮件
				//$sendmail_model=new Sendmail;
				
				$sendmsg_model=new SendmsgModel;
				$sendmsg_model->audit_enroll($enroll_id); 
			 return $this->renderSuccess($msg);
		}else{
			return $this->renderError('操作失败');
		} 
	}
	
	public function my_enroll_list(){
		$userdata=$this->getuser();
		$enroll_model=new EnrollModel;
		$where['user_id']=$userdata['user_id'];
		return $this->renderSuccess($enroll_model->my_enroll_list($where));
		
	}
}