<?php

namespace app\task\model;

use app\common\model\Reward as RewardModel;  
use app\common\model\Order as OrderModel;  
use think\Db;
/**
 * 用户模型
 * Class User
 * @package app\task\model
 */
class Reward extends RewardModel
{
		//计算各级提成
	public function reward($order,$relation,$config){
		$income=$profit=$order['total_price']; 
		$server_fee=$order['total_price']*$config['server_fee']/100;
		$activity_fee=$order['total_price']-$server_fee;
		$reward=[];
		$act_user_id=Db::name('activity')->where(['activity_id'=>$order['activity_id']])->value('user_id');
		if($act_user_id){
			$reward=$this->packge($reward,3,$activity_fee,$act_user_id,$order['order_id'],$order['currency']); 
			$profit-=$activity_fee; 
		}
		$amount_o=0;
		foreach($relation as $key=>$value){
			$user[$value['user_id']]=0;
			if($key==0){ 
				$amount_f=$amount=$server_fee*$config['relation_1_reward']/100;
				if($amount>0.01){
					$profit-=$amount; 
					$user[$value['user_id']]+=$amount;
					$reward=$this->packge($reward,2,$amount,$value['user_id'],$order['order_id'],$order['currency']);  
				}else{
					break;
				}  
				
			}else{
				$amount=$amount*$config['relation_o_reward']/100; 
				if($amount>0.01){
					$amount_o+=	$amount;  			
					$user[$value['user_id']]+=$amount;
					$reward=$this->packge($reward,2,$amount,$value['user_id'],$order['order_id'],$order['currency']);  
				}else{
					break;
				}
				
			}
			
		}
		if(!empty($reward)){
			$reward[1]['amount']=$amount_f-$amount_o; 
			$user[$relation[1]['user_id']]-=$amount_o;
		}  
		//如果是第一次购买   则有首次购买奖
		if(!OrderModel::get(['user_id'=>$order['user_id'],'ispay'=>1,'status'=>0])){ 
				$amount=$config['first_reward'];
				$user[$relation[1]['user_id']]+=$amount;
				$reward=$this->packge($reward,1,$amount,$relation[1]['user_id'],$order['order_id'],$order['currency']); 
				$profit-=$amount; 
		}
		$order_update['profit']=$profit; 
		$order_update['expend']=$income-$profit;   
		$order_update['income']=$income;   
		return ['reward'=>$reward,'order_update'=>$order_update]; 
	}
		
	public function packge($reward,$flag,$amount,$user_id,$order_id,$currency){
		$value=['amount'=>$amount,'user_id'=>$user_id,'flag'=>$flag,'order_id'=>$order_id,'currency'=>$currency]; 
		array_push($reward,$value);  
		return $reward;
	}
	public function save_reward($reward){ 
		$reward_model=new RewardModel;
		return $reward_model->allowField(true)->saveAll($reward);
	}
	
	public function save_active_fee($order,$config){
		$act_user_id=Db::name('activity')->where(['activity_id'=>$order['activity_id']])->value('user_id');
		$reward['user_id']=$act_user_id;
		$reward['amount']=$order['total_price']-$order['total_price']*$config['server_fee']/100;
		$reward['flag']=3;
		$reward['order_id']=$order['order_id'];
		$this->allowField(true)->save($reward);
	}
	
}