<?php

namespace app\home\controller; 
use app\home\model\SysMsg as SysMsgModel;   
use app\common\model\SysMsg as SysMsgcModel;   
use app\home\model\FriendMsg as FriendMsgModel;   
use app\home\model\FriendNotice as FriendNoticeModel;   
use app\common\model\Order as OrderModel;   
use think\Db;
/**
 * 系统消息
 * Class Sysmsg 
 */
class Sysmsg extends Controller
{
	public function msg_list($page){
		$this->getuser();
		$sysmsg_model=new SysMsgModel;
		return $this->renderSuccess($sysmsg_model->msg_list($page,$this->user_id));
	}

	public function noread(){
		$this->getuser();
		$sys_model=new SysMsgModel;
		$data['sys_count']=$sys_model->noread_count($this->user_id);
		$notice_model=new FriendNoticeModel;
		$data['notice_count']=$notice_model->noread_count($this->user_id);
		$msg_model=new FriendMsgModel;
		$data['msg_count']=$msg_model->noread_count($this->user_id);
		return $this->renderSuccess($data);
		
	}
	
	public function sysmsg_add(){
		$sysmsg_model=new SysMsgcModel;
		$order_model=new OrderModel;
		/* $value=['user'=>'xurui','pay_time'=>'2019-10-1','title'=>'阜新古城内','activ_begin_time'=>'2019-10-2','act_begin_send'=>120];
		$sysmsg_model->act_begin_sysmsg($value); */
		//$order=Db::name('order')->where(['order_id'=>36])->find();
		/* $order=$order_model->with('user')->where(['order_id'=>36])->find();
		$sysmsg_model->order_sysmsg($order); */
		
		$sysmsg_model->send_audit_activity($order);
		
	}
	
	public function msg_detail($msg_id){
		$userdata=$this->getuser();
		$sysmsg_model=new SysMsgModel;
		return $this->renderSuccess($sysmsg_model->msg_detail($msg_id,$userdata['user_id']));
	}
}