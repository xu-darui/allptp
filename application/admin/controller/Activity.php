<?php

namespace app\admin\controller;  
 
use app\admin\model\Activity as ActivityModel;  
use app\common\model\Sendmsg as SendmsgModel; 
use \think\Validate;
use think\Db;
/**
 * 活动模块
 * Class Activity
 * @package app\admin\controller
 */
class Activity extends Controller
{
	public function change($activity_id,$flag,$reason=''){
		$activity_data=Db::name('activity')->alias('a')->join('user b','a.user_id=b.user_id','LEFT')->field('a.title,a.audit,b.user_id,b.email,b.family_name,b.middle_name,b.name,b.mobile,b.m_code')->where(['a.activity_id'=>$activity_id])->find();
		switch($flag){
			case 1:
				//删除
				$data['del_time']=time();
				$msg="删除";
				$data['status']=1;
			break;
			case 2:
				//上架
				$msg="上架";
				$data['online']=0;
				break;
			case 3:
				//下架
				$msg="下架";
				$data['online']=1;
				break; 
			case 4:
				//审核通过
				if($activity_data['audit']<>0){
					return $this->renderError('该活动已经审核');
				}
				$msg="审核通过";
				$data['audit_time']=time();
				$data['reason']=''; 
				$data['audit']=1;
				$content="审核通过";
				break;
			case 5:
				//审核拒绝
				if($activity_data['audit']<>0){
					return $this->renderError('该活动已经审核');
				}
				$msg="审核拒绝";
				$data['audit_time']=time();
				$data['audit']=2; 
				$data['reason']=$reason; 
				$content="审核失败，失败原因：$reason";
				if (!$reason){
					return $this->renderError('请输入拒绝理由');
				}
				break;
		}
		$activity_model=new ActivityModel;
		if($activity_model->save_activity(['activity_id'=>$activity_id],$data)){
			if($flag==4){
				$activity_model->become_planner($activity_id);
			}
			if($flag==4||$flag==5){
				
				//创建完成发短信 发系统消息  
				$sendmsg_model=new SendmsgModel;
				$sendmsg_model->send_audit_activity($activity_id,$content);
				
			}
			 return $this->renderSuccess($msg.'成功');
		}else{
			return $this->renderError($msg.'失败');	
		}
	}
	
	public function activ_list($keywords='',$sort=1,$page=1, $country='',$province='',$city='',$region='', $kind_id=0,$status='',$audit='',$online=''){ 
		$activity_model=new ActivityModel;
		$data=$activity_model->activity_list($keywords ,$sort ,$page,$country,$province ,$city,$region,$kind_id,$status,$audit,$online);
		return $this->renderSuccess($data);
	} 
	 
}