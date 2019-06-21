<?php

namespace app\task\model;

use app\common\model\User as UserModel;
use app\common\model\RunningAmount;
use Think\Db;  

/**
 * 用户模型
 * Class User
 * @package app\task\model
 */
class User extends UserModel
{
	
	public function relation($userdata){
		if($userdata['user_relation']==''){
			return [];
		}else{
			return Db::query("select * from ptp_user where user_id in (".$userdata['user_relation'].") order by FIND_IN_SET('user_id','".$userdata['user_relation']."') desc");
		}
		
	}
	
	public function add_balance($reward){  
		if($reward){
			Db::startTrans();
			try{ 
				$user=[]; 
				foreach($reward as $key=>$value){ 
					if(!array_key_exists($value['user_id'],$user)){
						$balance=$this->where(['user_id'=>$value['user_id']])->value('balance');
						$user[$value['user_id']]['balance']=$balance;
					}
					$this->where(['user_id'=>$value['user_id']])->setInc('balance',$value['amount']); 
					$user[$value['user_id']]['balance']+=$value['amount']; 
					$reward[$key]['balance']=$user[$value['user_id']]['balance'];
				}
				$running_amount=new RunningAmount;
				$running_amount->allowField(true)->saveAll($reward);	
				// 提交事务
				Db::commit(); 
				return true;
			} catch (\Exception $e) {
				// 回滚事务
				Db::rollback();
			}
		}
	}
	
	/* public function add_act_balance($value,$config){
		Db::startTrans();
			try{ 
				$balance=$this->where(['user_id'=>$value['user_id']])->value('balance');
				$add_balance=$value['total_price']*$config['relation_1_reward']/100;
				$this->where(['user_id'=>$value['user_id']])->setInc('balance',+$add_balance);
				$reward['balance']=$balance+$add_balance;				
				$reward['amount']=$value['amount'];				
				$reward['user_id']=$value['user_id'];				
				$reward['flag']=2;				
				$running_amount=new RunningAmount;
				$running_amount->allowField(true)->add($reward);	
				// 提交事务
				Db::commit(); 
				return true;
			} catch (\Exception $e) {
				// 回滚事务
				Db::rollback();
			}	$running_amount->allowField(true)->addAll($reward);	
	} */
	
	public function reduce_balance($order){
		if($order['balance']>0){
			 $balance=$this->where(['user_id'=>$order['user_id']])->value('balance');
			 $this->where(['user_id'=>$order['user_id']])->setDec('balance',$order['balance']);
			 $running_amount=new RunningAmount;
			 $running['amount']=$order['balance'];
			 $running['flag']=4;
			 $running['balance']=$balance-$order['balance'];
			 $running['order_id']=$order['order_id'];
			 $running['user_id']=$order['user_id'];
			 $running_amount->allowField(true)->save($running);	
		}
	}
	
	public function update_score($config){
		if(date('w')==5&&date('H:i')=='10:46'&&(array(date('s'),['01','02','03']))){
			$this->where(['isplanner'=>1,'credit_score'=>['lt',$config['credit']['init_score']-$config['credit']['add_score_week']]])->setInc('credit_score',$config['credit']['add_score_week']);
			$this->where(['isplanner'=>1,'credit_score'=>['between',[$config['credit']['init_score'],$config['credit']['init_score']-$config['credit']['add_score_week']]]])->update(['credit_score'=>$config['credit']['init_score']]);	
		}
		
	}
}