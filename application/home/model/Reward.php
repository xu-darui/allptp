<?php

namespace app\home\model;  
use app\common\model\Reward as RewardModel; 
use app\common\model\Order as OrderModel; 
use think\Db;
/**
 * 奖金
 * Class Reward
 * @package app\store\model
 */
class Reward extends RewardModel
{
	//计算各级提成
	public function reward_refund($order,$relation,$config){
		$profit=($order['total_price'])*(-1); 
		$server_fee=$order['total_price']*$config['server_fee']/100;
		$activity_fee=$order['total_price']-$server_fee;
		$reward=[];
		//pre($activity_fee);
		$income=0; 
		$act_user_id=Db::name('activity')->where(['activity_id'=>$order['activity_id']])->value('user_id');
		if($act_user_id){
			$reward=$this->packge($reward,6,$activity_fee,$act_user_id,$order['order_id'],$order['currency']); 
			$profit+=$activity_fee;
			$income+=$activity_fee;
		}
		$amount_o=0; 
		if($relation){
			foreach($relation as $key=>$value){ 
			//$user[$value['user_id']]=0;
				if($key==0){ 
					$amount_f=$amount=$server_fee*$config['relation_1_reward']/100; 
					if($amount>0.01){
						$profit+=$amount;
						$income+=$amount;
						//$user[$value['user_id']]+=$amount; 
						$reward=$this->packge($reward,5,$amount,$value['user_id'],$order['order_id'],$order['currency']);  
					}else{
						break;
					}   
				}else{
					$amount=$amount*$config['relation_o_reward']/100; 
					if($amount>0.01){
						$income+=$amount;
						$profit+=$amount;
						$amount_o+=	$amount; 
						//$user[$value['user_id']]+=$amount; 
						$reward=$this->packge($reward,5,$amount,$value['user_id'],$order['order_id'],$order['currency']);  
					}else{
						break;
					}
					
				}
			
			}		 
			if(!empty($reward)){
				$reward[1]['amount']=$amount_f-$amount_o;
				//$reward[0]['balance']=$user[$relation[0]['user_id']]-$amount_o;
				//$user[$relation[0]['user_id']]-=$amount_o;
			}  
			//如果退款时该用户是第一次购买  则退第一次购买奖励的金额
			if(!OrderModel::get(['user_id'=>$order['user_id'],'ispay'=>1,'status'=>0])){ 
					$income+=$config['first_reward'];
					$profit+=$config['first_reward']; 
					//$user[$relation[0]['user_id']]+=$amount;
					$amount=$config['first_reward']; 
					$reward=$this->packge($reward,4,$amount,$relation[0]['user_id'],$order['order_id'],$order['currency']); 
					
			}
		}
		
		$order_update['profit']=$profit; 
		$order_update['expend']=$order['total_price'];  
		$order_update['income']=$income;  
		$order_update['refund_id']=$order['refund_id'];  
		return ['reward'=>$reward,'order_update'=>$order_update]; 
	}
	
	public function packge($reward,$flag,$amount,$user_id,$order_id,$currency){
		$value=['amount'=>$amount,'user_id'=>$user_id,'flag'=>$flag,'order_id'=>$order_id,'currency'=>$currency,'status'=>2]; 
		array_push($reward,$value);  
		return $reward;
	}
	
	public function save_reward($reward){
		return $this->allowField(true)->saveAll($reward);
	}
	public function unpaid_amount($user_id){
		return $this->where(['user_id'=>$user_id,'status'=>0])->sum('amount');
	}
	public function amount_list($user_id,$page){
		return $this->with('order')->where(['user_id'=>$user_id])->order('reward_id desc')->paginate(10, false, ['query' => ["page"=>$page]]);
	}
	
	public function soon_list($where,$page){ 
		$where['a.status']=0;
		return Db::name('reward')
			->alias('a')
			->field("a.reward_id,a.flag,a.amount,FROM_UNIXTIME(a.create_time, '%Y-%c-%d %h:%i:%s' ) as create_time,c.family_name,c.middle_name,c.name")
			->join('order b','a.order_id=b.order_id')
			->join('user c','b.user_id=c.user_id')
			->where($where)
			->paginate(10, false, ['query' => ["page"=>$page]]);
	}
	public function save_active_fee_refund1($order,$config){
		$act_user_id=Db::name('activity')->where(['activity_id'=>$order['activity_id']])->value('user_id');
		$reward['user_id']=$act_user_id;
		$reward['amount']=($order['total_price']-$order['total_price']*$config['server_fee']/100)*(-1);
		$reward['flag']=6;
		$reward['order_id']=$order['order_id'];
		$this->allowField(true)->save($reward);
		return  $reward['amount'];
	}
	
	
	public function trading_list($where){ 
		return Db::view('trading')
		->field("order_id,title,amount,type,flag,FROM_UNIXTIME(create_time, '%Y-%c-%d %H:%i') as create_time")
		->where($where)
		->order('create_time desc')
		->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]]);
	}
	
	


}