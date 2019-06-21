<?php

namespace app\home\controller; 
use app\home\model\Refund as RefundModel; 
use app\home\model\Order as OrderModel; 
use app\home\model\User as UserModel; 
use app\home\model\Reward as RewardModel;  
use app\common\model\Sendmsg as SendmsgModel; 
use think\Db;

/**
 * 退款中心
 * Class Refund
 * @package app\home\controller
 */
class Refund extends Controller
{
	public function save_refund($order_id){
		$data=input(); 
		if(!array_key_exists('reason',$data)||$data['reason']==''){
			return $this->renderError('请填写退款原因');
		}
		$house=[];
		if(array_key_exists('house',$data)&&$data['house']&&$data['flag']==0){
			$house=$data['house']=json_decode(html_entity_decode($data['house']),true); 
		}
		$userdata=$this->getuser();
		
		$refund_model=new RefundModel; 
		$person_num=0;
		$order_no=Db::name('order')->where(['order_id'=>$order_id])->value("order_no");
		$order_model=new OrderModel;
		$order=$order_model->getdetail($order_no);
		$data['user_id']=$order['user_id']; 
		$data['activity_id']=$order['activity_id'];
		$data['slot_id']=$order['slot_id'];
		if($order['ispay']!==1){
			return $this->renderError('该订单未支付');
		}
		if($order['status']==2){
			return $this->renderError('该订单已经退款成功');
		}
		
		if(array_key_exists('flag',$data)&&$data['flag']==1){
			$flag=1;
			//退全部 
			$person_num=$data['person_num']=$order['num'];
			$data['person_price']=$order['act_union_price']*$person_num; 
			foreach($order['house'] as $key=>$value){
				$house[$key]['oh_id']=$value['oh_id'];
				$house[$key]['num']=$value['num']; 
			} 
		}else{
			$flag=0;
			//退部分
			if(array_key_exists('person_num',$data)&&$data['person_num']){
				$person_num=$data['person_num'];
				$data['person_price']=$order['act_union_price']*$person_num;
			
			} 
		} 
		$result=$this->check_refund($order_id,$flag,$person_num,$house);
		if(!$result['code']){
			return $this->renderError($result['msg']);
		}
		$price=$this->refund_amount_calculate($order_id,$flag,$person_num,$house); 
		$data['balance']=$price['balance'];
		$data['pay_price']=$price['pay_price'];
		$data['total_price']=$price['pay_price']+$price['balance']; 
		$data['number']=date('Ymd').time().rand(1111,9999);
		if(!$refund_id=$refund_model->save_refund($data,$house)){
			return $this->renderError('提交退款失败');
		} 
		if((array_key_exists('type',$data)&&$data['type']==1)||$this->check_policy_calculate($order_id)){
			$data['refund_id']=$refund_id;
			$result=$this->refund_to_pay($data,$order); 
			if($result['code']==1){
				//提交退款策划者信誉分减少
				$user_model=new UserModel;
				$data['activity_user_id']=$userdata['user_id'];
				$user_model->update_score($data,$this->config());
				$sendmsg_model=new SendmsgModel;
				$sendmsg_model->success_refund($refund_id);
				return $this->renderSuccess($result['msg']);
			}else{
				return $this->renderError($result['msg']);
			}
		}else{
			//提交成功发短信 发系统消息 发邮件 
			$sendmsg_model=new SendmsgModel;
			$sendmsg_model->send_submit_refund($refund_id);
			//提交审核 
			return $this->renderSuccess('提交成功，请耐心等待');
			
		}
	}
	

	public function refund_amount($order_id,$flag=0,$person_num=0,$house=''){
		$house=json_decode(html_entity_decode($house),true); 
		$result=$this->check_refund($order_id,$flag,$person_num,$house);
		if(!$result['code']){
			return $this->renderError($result['msg']);
		}
		return $this->renderSuccess($this->refund_amount_calculate($order_id,$flag,$person_num,$house));
	}
	

	
	public function refund_amount_calculate($order_id,$flag,$person_num,$house){ 
		$refund_model=new RefundModel; 
		$order=$refund_model->order_detail($order_id);
		//是否已经有申请退款
		$refund_balance=Db::name('refund')->where(['order_id'=>$order_id,'status'=>0,'audit'=>['neq',2]])->sum('balance');
		$order['balance']-=$refund_balance;
		if($flag==0){
			$person_price=0;
			//退部分
			if($person_num>0){
				$person_price=$order['act_union_price']*$person_num;
			}
			$house_price=0;  
			if($house!=''){  
				foreach($house as $key=>$value){
					$oh_house=Db::name('order_house')->where(['oh_id'=>$value['oh_id']])->find();  
					$house_price+=$oh_house['union_price']*$value['num'];
				} 
			}
			$total_price=$house_price+$person_price;
			if($order['balance']>=$total_price){
				return ['balance'=>$total_price,'pay_price'=>0];
			}else{
				return ['balance'=>$order['balance'],'pay_price'=>$total_price-$order['balance']];
			} 
		}else{
			//退全部
			return ['balance'=>$order['balance'],'pay_price'=>$order['pay_price']];	
		}
	}
	
	public function check_refund($order_id,$flag,$person_num,$house){
		//判断申请人数是否正确
		$refund=Db::name('refund')->field("sum(person_num) as person_num,sum(total_price) as total_price")->where(['order_id'=>$order_id,'status'=>0,'audit'=>['neq',2]])->find();
		$refund_model=new RefundModel; 
		$order=$refund_model->order_detail($order_id);  
		if($order['num']<(intval($person_num)+intval($refund['person_num']))){
			return ['code'=>0,'msg'=>'退款人数已超过总数'];exit;
		}  
		$order_house=Db::name('order_house')->alias('a')->field("a.oh_id,a.house_id,(ifnull(sum(a.num), 0) - ifnull(sum(b.house_num), 0)) AS num")->join('refund c','a.order_id=c.order_id and c.audit<>2','LEFT')->join('refund_house b','c.refund_id = b.refund_id and a.oh_id=b.oh_id','LEFT')->where(['a.order_id'=>$order_id])->group('a.oh_id')->select();
		//pre(Db::name('order_house')->getlastsql());
		//pre($order_house);
		if($house){
			foreach($house as $key=>$value){
				$num=$value['num'];
				foreach($order_house as $house_key=>$house_value){
					if($value['oh_id']==$house_value['oh_id']){
						//var_dump($num.'---'.$house_value['num']);
						if($num>$house_value['num']){ 
							return ['code'=>0,'msg'=>'退款房源数量超过总数'];exit;
						} 
					} 
				}
				
			}
		}
		
			return ['code'=>1];
	}
	
	public function check_policy($order_id){
		if($this->check_policy_calculate($order_id)){
			return $this->renderSuccess('可以退全款');
		}else{
			return $this->renderError('该活动退款已经不能退全款，需要提交审核方可退款');
		}
		
	}
	
	public function check_policy_calculate($order_id){
		$nowtime=time();
		$refund_model=new RefundModel;
		$order=$refund_model->order_detail($order_id); 
		switch($order['return_policy']){
				case 0:
					$time_ago=0;
					break;
				case 1:
					$time_ago=86400;
					break;
				case 2:
					$time_ago=7*86400;
					break; 
			}
			if((($order['begin_time']-$time_ago)<$nowtime)||$order['begin_time']<$nowtime){
				//超过退订时间  提示不能退原价
				return false;  
			}else{
				return true;  
			}
	}
	
	public function refund_list(){
		$userdata=$this->getuser();
		$refund_model=new RefundModel;
		return $this->renderSuccess($refund_model->refund_list(input(),$userdata['user_id']));
		
	}
	
	public function refund_list_user(){
		$userdata=$this->getuser();
		$refund_model=new RefundModel;
		return $this->renderSuccess($refund_model->refund_list_user(input(),$userdata['user_id']));
	}
	
	public function refund_detail($refund_id){
		$userdata=$this->getuser();
		$refund_model=new RefundModel;
		return $this->renderSuccess($refund_model->refund_detail($refund_id));
	}
	
	public function agree_refund($refund_id,$type,$return_price=0,$refuse_reason=''){ 
		$refund=Db::name('refund')->where(['refund_id'=>$refund_id])->find();
		if($refund['audit']==1) return $this->renderError('该退款已经退款成功');
		if($refund['audit']==2) return $this->renderError('该退款已经拒绝');
		$refund_model=new RefundModel;
		if($type==1){
			
			if($return_price==0) return $this->renderError('请输入退款金额'); 
			//校验退款金额是否合理
			if(!$refund_model->check_return_price($refund_id,$return_price)){
				return $this->renderError('累计退款金额已经超过原始金额');
			}
			//同意
					$refund_model->agree($refund_id,$return_price);
					$data=Db::name('refund')->alias('a')->field('a.*,b.order_no')->join('order b','a.order_id=b.order_id','LEFT')->where(['a.refund_id'=>$refund_id])->find();
					$order_model=new OrderModel;
					$order=$order_model->getdetail($data['order_no']);  
					$result=$refund_model->refund_to_pay($data,$order,$this->config()); 	
					if($result['code']==1){
						//退款成功发短信 发系统消息 发邮件
						//$sendmail_model=new Sendmail;
						
						/* $sendmsg_model=new SendmsgModel;
						$sendmsg_model->success_refund($refund_id); */
						return $this->renderSuccess($result['msg']);
					}else{
						return $this->renderError($result['msg']);
					} 
		}else{
			//拒绝
			if($refuse_reason==''){
				return $this->renderError('请输入退款原因');
			}
			if($refund_model->refuse($refund_id,$refuse_reason)){
				//审核不通过后发短信 发系统消息 发邮件
				//$sendmail_model=new Sendmail;
				
				$sendmsg_model=new SendmsgModel;
				$sendmsg_model->audit_refund($refund_id);
				return $this->renderSuccess('已拒绝');
			}else{
				return $this->renderError('拒绝失败');
			}
		}
		
	}
	

}