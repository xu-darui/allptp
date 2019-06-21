<?php

namespace app\task\model;

use app\common\model\Order as OrderModel;
use app\common\model\Reward as RewardModel;
use app\task\model\User as UserModel;  
use think\Db; 
use app\common\model\Sendmsg as SendmsgModel; 
use app\common\model\Turnover as TurnoverModel; 
use app\home\model\Refund as RefundModel;  
/**
 * 订单模型
 * Class Order
 * @package app\common\model
 */
class Order extends OrderModel
{
    /**
     * 待支付订单详情
     * @param $order_no
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function payDetail($order_no)
    {
		return Db::name('order')->field('order_no as out_trade_no,pay_price as total_amount')->where(['order_no' => $order_no, 'ispay' => 0])->find();  
    }

	public function getdetail($order_no){ 
		return $this->with("user")->where(['order_no'=>$order_no])->find()->toArray(); 	
	}
	
	public function order_update($order_update,$order){
		/* if(!array_key_exists('profit',$order_update)){
			$order_update=$order['total_price']; 
		} */		
		$update['ispay']=1;
		$update['pay_time']=time();
		return $this->allowField(true)->save($update,['order_id'=>$order['order_id']]);
	}
	
	public function add_turnover($order_update,$order){
		$turnover_model=new TurnoverModel;
		$data['order_id']=$order['order_id'];
		$data['income']=$order_update['income'];
		$data['expend']=$order_update['expend'];
		$data['profit']=$order_update['profit'];
		$data['flag']=0;
		$data['user_id']=$order['user_id'];
		$turnover_model->allowField(true)->save($data);
	}
	
	public function cancel($config){
		$now_time=time(); 
		$this->where(['ispay'=>0,'create_time'=>['lt',$now_time-$config['order_cancel']*60]])->update(['ispay'=>99]);
	}
	//活动开始提前推送
	public function act_advance_send($config){
		$list= $this->with('user')->where(['activ_begin_time'=>['between',[time(),(time()+$config['act_begin_send']*60)]],'issend'=>0,'ispay'=>1,'status'=>0])->select()->toArray();
		$sendmsg_model=new SendmsgModel;
			if($list){	 		
				foreach($list as $key=>$value){  
					$value['act_begin_send']=$config['act_begin_send'];
					$sendmsg_model->act_begin_mobile($value);
				} 
				$order_id=array_column($list,'order_id');
				$this->where(['order_id'=>['in',$order_id]])->update(['issend'=>1]);
			}
	}
	public function act_begin($config){
		$now_time=time();
		$order_id= $this->where(['activ_begin_time'=>['elt',$now_time,'activ_end_time'=>['gt',$now_time]],'iscomplete'=>0,'ispay'=>1,'status'=>0])->column('order_id'); 
		//修改状态
		$this->where(['order_id'=>['in',$order_id]])->update(['iscomplete'=>1]);
		
	}
	//活动完成计算
	public function act_end($config){
		$list= $this->where(['activ_end_time'=>['elt',time()],'iscomplete'=>1,'ispay'=>1,'status'=>['neq',2]])->select()->toArray();  
		if($list){  
			$reward_model=new RewardModel; 
			$user_model=new UserModel;
			foreach($list as $key=>$value){ 
				$reward_id=[];
				$reward=$reward_model->field('reward_id,flag,amount,user_id,order_id,currency')->where(['order_id'=>$value['order_id'],'status'=>0,'flag'=>['in',[1,2,3]]])->select()->toArray(); 
				$refund_reward=$reward_model->field('reward_id,flag,sum(amount) as amount,user_id,order_id,currency')->where(['order_id'=>$value['order_id'],'status'=>2,'flag'=>['in',[4,5,6]]])->group('flag,user_id')->select()->toArray(); 
				foreach($reward as $key_re=>$value_re){
					array_push($reward_id,$value_re['reward_id']);
					foreach($refund_reward as $key_ref=>$value_ref){
						if(($value_ref['flag']===($value_re['flag']+3))&&($value_re['user_id']===$value_ref['user_id'])){
							$value_re['amount']-=$value_ref['amount']; 
						}
						if($value_re['amount']==0){
							unset($reward[$key_re]);
							continue;
						}
						$reward[$key_re]=$value_re;
					}
				} 
				//添加提成余额
				if($reward){
					$user_model->add_balance($reward); 	 
				}
				if($reward_id){
					$reward_model->where(['reward_id'=>['in',$reward_id]])->update(['status'=>1]);	
				} 
			}
			
			$order_id=array_column($list,'order_id');
			//修改状态
			$this->where(['order_id'=>['in',$order_id]])->update(['iscomplete'=>2]);
		}
	}
	//自动退款
	public function auto_refund($config){
		//5*86400
		$data_list=Db::name('refund')->alias('a')->field('a.*,b.order_no')->join('order b','a.order_id=b.order_id','LEFT')->where(['a.audit'=>0,'a.create_time'=>['lt',time()-$config['refund_time']*86400]])->select()->toArray();
		if($data_list){
			$refund_model=new RefundModel;
			$order_model=new OrderModel; 
			foreach($data_list as $key=>$data){ 
				$refund_model->agree($data['refund_id'],$data['total_price']); 
				$order=$order_model->with(['user','house'])->where(['order_no'=>$data['order_no']])->find()->toArray();
				$result=$refund_model->refund_to_pay($data,$order,$config); 	
				if($result['code']==1){
					//退款成功发短信 发系统消息 发邮件 
					$sendmsg_model=new SendmsgModel;
					$sendmsg_model->success_refund($data['refund_id']); 
				}
			}
		}
		
		
	}
	
	

}
