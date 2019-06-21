<?php
namespace app\admin\controller;
use app\admin\model\SysMsg as SysMsgModel;
/**
 * 发送系统消息
 * Class Sysmsg
 * @package app\admin\controller
 */
class Sysmsg extends Controller
{
	public function add(){
		$admin=$this->getadmin(); 
		$data=input();
		$data['admin_id']=$this->admin_id;
		if($data['all_receive']==0&&$data['user_list']==''){
			return $this->renderError('请选择指定发送的用户');
		}
		if($data['issend']){
			$data['send_time']=time();
		}
		$sysmsg_model=new SysMsgModel; 
		if($msg_id=$sysmsg_model->save_msg($data)){
			return $this->renderSuccess('保存成功');
		}else{
			return $this->renderError('保存失败');
		}	
	}
	public function send($msg_id,$issend){	
		$sysmsg_model=new SysMsgModel; 
		if($sysmsg_model->send_msg($msg_id,$issend)){
			if($issend==1){
				return $this->renderSuccess('发送成功');
			}else {
				return $this->renderSuccess('撤回成功');
			}
			
		}else{
			return $this->renderError('操作失败');
		} 	
	}
	public function msg_list($keywords='',$date='',$page=1){ 
		$where=[];
		if($date){
			$where['update_time']=['between',[strtotime($date),strtotime($date)+86400]];
		}
		if($keywords){
			$where['title|content']=['like','%'.$keywords.'%'];
		}
		$sysmsg_model=new SysMsgModel; 
		return $this->renderSuccess($sysmsg_model->msg_list($where,$page));
			
	}
	public function del_msg($msg_id){
		$sysmsg_model=new SysMsgModel; 
		if($sysmsg_model->del_msg($msg_id)){
			return $this->renderSuccess('删除成功');
		}else{
			return $this->renderError('删除失败');
		}
	}
}