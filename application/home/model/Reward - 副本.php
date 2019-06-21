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
	public function reward($order,$relation,$config){
		$profit=$order['total_price']; 
		$server_fee=$order['total_price']*$config['server_fee']/100;
		$reward=[];
		$amount_o=0;
		foreach($relation as $key=>$value){
			$user[$value['user_id']]=0;
			if($key==0){ 
				$amount_f=$amount=$server_fee*$config['relation_1_reward']/100;
				if($amount>0.01){
					$profit-=$amount;
					$user[$value['user_id']]+=$amount;
					$reward=$this->packge($reward,2,$amount,$value['user_id'],$order['order_id'],$order['currency'],$value['balance']+$user[$value['user_id']]);  
				}else{
					break;
				}  
				
			}else{
				$amount=$amount*$config['relation_o_reward']/100; 
				if($amount>0.01){
					$amount_o+=	$amount;  
					$user[$value['user_id']]+=$amount;
					$reward=$this->packge($reward,2,$amount,$value['user_id'],$order['order_id'],$order['currency'],$value['balance']+$user[$value['user_id']]);  
				}else{
					break;
				}
				
			}
			
		}
		if(!empty($reward)){
			$reward[0]['amount']=$amount_f-$amount_o;
			$reward[0]['balance']=$user[$relation[0]['user_id']]-$amount_o;
			$user[$relation[0]['user_id']]-=$amount_o;
		}  
		//如果是第一次购买   则有首次购买奖
		if(!OrderModel::get(['user_id'=>$order['user_id'],'ispay'=>1,'status'=>0])){ 
				$amount=$config['first_reward'];
				$user[$relation[0]['user_id']]+=$amount;
				$reward=$this->packge($reward,1,$amount,$relation[0]['user_id'],$order['order_id'],$order['currency'],$relation[0]['balance']+$user[$relation[0]['user_id']]); 
				$profit-=$amount; 
		}
		$order_update['profit']=$profit; 
		$order_update['expend']=$order['total_price']-$profit;  
		return ['reward'=>$reward,'order_update'=>$order_update]; 
	}
	
	public function packge($reward,$flag,$amount,$user_id,$order_id,$currency,$balance){
		$value=['amount'=>$amount,'user_id'=>$user_id,'flag'=>$flag,'order_id'=>$order_id,'currency'=>$currency,'balance'=>$balance]; 
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
	
	
	


}