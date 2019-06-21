<?php
namespace app\home\controller;
use app\home\model\Order as OrderModel; 
use app\home\model\User as UserModel; 
use app\home\model\Reward as RewardModel; 
use app\home\model\Activity as ActivityModel;; 
use app\common\model\Sendmail;  
use app\common\model\Sendmsg;  
use app\common\model\SysMsg;    
use think\Db;
/**
 * 支付回调
 * Class Notify
 * @package app\home\controller
 */
class Notify extends Controller
{
	
	public function test(){
		echo 111;
	}
		//支付回调
	public function notify(){
		pre(date('w'));
		//$data=$GLOBALS['TTP_RAW_POST_DATA'];
        //$result=xmlToArray($data);
        //$order_no = $result['out_trade_no'];
		$order_no='2019061915609294888649';
		$config=$this->config();
		$order_model=new OrderModel;
		$order=$order_model->getdetail($order_no);
		//pre($order);
		$user_model=new UserModel;
		$relation=$user_model->relation($order['user']);
		Db::startTrans(); //启动事务
			try {
				//$user_model->reduce_balance($order);
				$reward_model=new RewardModel;  
				//提成 
				$reward=$reward_model->reward($order,$relation,$config); 
				$reward_model->save_reward($reward['reward']); 
				$order_update=$reward['order_update'];  
				//加销量
				$activity_model=new ActivityModel;
				$activity_model->add_sale($order); 
				$sendmsg_model=new Sendmsg; 
				$sendmsg_model->order_mobile($order);
				$order_model->order_update($order_update,$order); 
				$order_model->add_turnover($order_update,$order);
				Db::commit(); //提交事务
			} catch (\PDOException $e) {
				Db::rollback(); //回滚事务
		}
		
		
		
	}
}