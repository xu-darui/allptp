<?php

namespace app\home\controller;

use app\home\model\Reward as RewardModel;
use app\home\model\Order as OrderModel;

use app\home\model\RunningAmount; 
use think\Db;

/**
 * 会员中心
 * Class Vip
 * @package app\home\controller
 */
class Vip extends Controller
{
	
	public function check_balance($page=1){
		$userdata=$this->getuser();
		$reward_model=new RewardModel;
		$reward['due_balance']=$userdata['balance'];
		$reward['unpaid_amount']=$reward_model->unpaid_amount($userdata['user_id']);
		$reward['amount_list']=$reward_model->amount_list($userdata['user_id'],$page); 
		return $this->renderSuccess($reward);
			
	}
   
	public function running($page=1){
		$userdata=$this->getuser();
		$running_model=new RunningAmount;   
		return $this->renderSuccess($running_model->running_list($userdata['user_id'],$page));
	}
	
			
	public function order_list($flag='',$page,$status='',$isevaluate='',$iscomplete=''){
		$userdata=$this->getuser(); 
		$order_model=new OrderModel;
		$where=['user_id'=>$userdata['user_id']];
		if($flag!=''){
			$where['ispay']=$flag;
		}else{
			$where['ispay']=['neq',99];
		}
		if($iscomplete!=''){
			$where['iscomplete']=$iscomplete;
		}
		if($isevaluate!==''){
			$where['isevaluate']=$isevaluate;
		} 
		//$where['status']=['neq',2];
		return $this->renderSuccess($order_model->order_list($where,$page,$status));
	}
	//我参见的活动
	public function activ_list($keywords='',$iscomplete=0,$page=1){
		$userdata=$this->getuser();
		$order_model=new OrderModel;
		$data=$order_model->my_act_list($userdata['user_id'],$keywords ,$iscomplete,$page);
		return $this->renderSuccess($data);
	}
	
	public function test(){ 
	if(isset($_SERVER["HTTP_CLIENT_IP"]) and strcasecmp($_SERVER["HTTP_CLIENT_IP"], "unknown")){
        $ip= $_SERVER["HTTP_CLIENT_IP"];
    }
    if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) and strcasecmp($_SERVER["HTTP_X_FORWARDED_FOR"], "unknown")){
        $ip= $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    if(isset($_SERVER["REMOTE_ADDR"])){
        $ip= $_SERVER["REMOTE_ADDR"];
    } 
	  $url='https://apis.map.qq.com/ws/location/v1/ip?ip='.$ip.'&key=5BKBZ-QYEKR-7YOWW-WTALN-IOXV7-SGFCV';
         $result = curl($url,[]);
        $result = json_decode($result,true);
        dump($result);
   
	}
	
	public function trad_list(){ 
		$userdata=$this->getuser();
		$where['user_id']=$userdata['user_id'];
		$data=input();
		if(array_key_exists('begin_time',$data)&&$data['begin_time']>0&&array_key_exists('end_time',$data)&&$data['end_time']>0){
			$where['create_time']=['between',[strtotime($data['begin_time']),strtotime($data['end_time'])]];
			//strtotime($data['begin_time']);
		}
		if(array_key_exists('type',$data)){
			$where['flag']=$data['type']==2?0:1;
		}
		$reward_model=new RewardModel; 
		return $this->renderSuccess($reward_model->trading_list($where));
	}

	public function order_list_planner($flag='',$page,$status='',$isevaluate='',$iscomplete='',$activity_id='',$slot_id='',$isevaluate_planner=''){
		$userdata=$this->getuser(); 
		$order_model=new OrderModel;
		if($flag!=''){
			$where=['ispay'=>$flag];
		} 
		if($iscomplete!=''){
			$where['iscomplete']=$iscomplete;
		}
		if($isevaluate!=''){
			$where['isevaluate']=$isevaluate;
		}
		if($isevaluate_planner!=''){
			$where['isevaluate_planner']=$isevaluate_planner;
		}
		if($activity_id!=''){
			$where['activity_id']=$activity_id;
		}else{
			$activity_id_array=Db::name('activity')->where(['user_id'=>$userdata['user_id'],'complete'=>1,'audit'=>1])->column('activity_id');   
			$where['activity_id']=['in',$activity_id_array];
		}
		if($status!=''){
			if($status==0){
				$where['status']=['neq',2];
			}else{
				$where['status']=$status;
			}
			
		}
		if($slot_id!=''){
			$where['slot_id']=$slot_id;
		}
		return $this->renderSuccess($order_model->order_list_planner($where));
	}

	
}