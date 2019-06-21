<?php

namespace app\home\model;

use app\common\model\SysMsg as SysMsgModel;
use think\Db;


/**
 * 系统消息
 * Class Sysmsg
 * @package app\home\model
 */
class SysMsg extends SysMsgModel
{
	public function msg_list($page,$user_id){  
		$where['status']=0;
		$where['issend']=1;  
		$data=$this->where(function($query) use($user_id){$query->where('user_list','like',$user_id.',%')->whereor('user_list','like','%,'.$user_id)->whereor('user_list','like','%,'.$user_id.',%')->whereor('user_list',$user_id)->whereor('all_receive',1);})->where($where)->order('send_time desc,msg_id desc')->paginate(10, false, ['query' => ["page"=>$page]]);
		$msg= json_decode(json_encode($data),true); 
		/* if(array_key_exists('data',$msg)&&$msg['data']){
			$msg_id=array_column($msg['data'],'msg_id');
			$msg_id=implode(',',$msg_id);
			Db::execute("update ptp_sys_msg set read_user_list =IF(read_user_list='','$user_id',CONCAT_WS(',',read_user_list,'$user_id')) where msg_id in ($msg_id)"); 
		}  */
		return $msg;
	}
	
	public function noread_count($user_id){ 
		$data= Db::query("SELECT count('id') as count FROM ptp_sys_msg WHERE ( FIND_IN_SET($user_id, user_list) OR all_receive = 1 ) AND issend = 1 AND `status` = 0 and read_user_list not like '%$user_id,%' and read_user_list not like '%,$user_id%' and read_user_list not like '%,$user_id,%' AND read_user_list <> $user_id");  
		return $data[0]['count'];
	}
	
	public function msg_detail($msg_id,$user_id){
		Db::execute("update ptp_sys_msg set read_user_list =IF(read_user_list='','$user_id',CONCAT_WS(',',read_user_list,'$user_id')) where msg_id = $msg_id"); 
		return $this->where(['msg_id'=>$msg_id])->find();
	}

}