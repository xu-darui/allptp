<?php

namespace app\home\model;

use app\common\model\Refund as RefundModel;
use app\common\model\RefundHouse ;
use app\common\model\RunningAmount ;
use app\common\model\User as UserModel ;
use app\common\model\Order as OrderModel ;
use app\common\model\Kind as KindModel ;
use app\home\model\Reward as RewardModel; 
use app\home\model\User as UserHModel; 
use app\home\model\Order as OrderHModel; 
use think\Db;


/**
 * 退款
 * Class Refund
 * @package app\home\model
 */
class Refund extends RefundModel
{
	public function save_refund($data,$house){
		Db::startTrans();
		try{ 
			$this->allowField(true)->save($data);
			$refund_id=$this->refund_id;
			if($house){
				foreach($house as $key=>$value){ 
					$oh_house=Db::name('order_house')->where(['oh_id'=>$value['oh_id']])->find();
					$save_house[$key]['oh_id']=$value['oh_id'];
					$save_house[$key]['house_id']=$oh_house['house_id'];
					$save_house[$key]['order_id']=$data['order_id'];
					$save_house[$key]['house_num']=$value['num'];
					$save_house[$key]['house_price']=$value['num']*$oh_house['union_price']; 
					$save_house[$key]['refund_id']=$refund_id; 
				}
				$house_model=new RefundHouse;
				$house_model->allowField(true)->saveAll($save_house);
			}
			Db::commit();
			return $refund_id;
		}catch(\Exception $e) {
				// 回滚事务
			Db::rollback();
			return false;	
		} 
	}
	
	public function order_detail($order_id){
		$order=Db::name('order')->alias('a')->field('a.order_id,a.activity_id,a.num,b.return_policy,a.activ_begin_time as begin_time,a.balance,a.pay_price,a.act_union_price')->join('activity b','a.activity_id=b.activity_id','LEFT')->where(['a.order_id'=>$order_id])->find();
		return $order;
	}
	
	public function refund_balance($refund){ 
		Db::startTrans();
		try{ 
			$userdata=UserModel::where(['user_id'=>$refund['user_id']])->find();
			$runing_amount_model=new RunningAmount;
			$runing_amount_model->allowField(true)->save([
				'user_id'=>$refund['user_id'],
				'amount'=>$refund['balance'],
				'flag'=>6,
				'balance'=>$userdata['balance']+$refund['balance'],
				'order_id'=>$refund['order_id']
				]);
			UserModel::where(['user_id'=>$refund['user_id']])->setInc('balance',$refund['balance']);	
			Db::commit();
			return true;
		}catch(\Exception $e) {
				// 回滚事务
			Db::rollback();
			return false;	
		} 
	}
	
	public function check_all_refund($order){
		$person_num=Db::name('refund')->where(['audit'=>1,'status'=>0,'order_id'=>$order['order_id']])->sum('person_num');
		if($order['num']!=$person_num){
			return false;
		}
		$order_house_num=$refund_house_num=0;
		foreach($order['house'] as $order_value){
			$order_house_num+=$order_value['num'];
		}
		$refund_house_num=Db::name('refund')->alias('a')->join('refund_house b','a.refund_id=b.refund_id','INNER')->where(['a.audit'=>1,'a.status'=>0,'order_id'=>$order['order_id']])->sum('house_num'); 
		if($order_house_num!=$refund_house_num){
			return false;
		}
		return true;
	}
	
	public function refund_list($data,$user_id){
		if(array_key_exists('activity_id',$data)&&$data['activity_id']>0){
			$where['b.activity_id']=$data['activity_id'];
		}else{
			$activity_id=Db::name('activity')->where(['user_id'=>$user_id])->column('activity_id');
			$where['b.activity_id']=['in',$activity_id];
		}
		if(array_key_exists('audit',$data)){
			$where['b.audit']=$data['audit'];
		}
		if(array_key_exists('slot_id',$data)&&$data['slot_id']){
			$where['b.slot_id']=$data['slot_id'];
		} 
		$refund=Db::name('refund')
		->alias('b')
		->field("b.refund_id,e.num,b.person_num,b.person_price,b.total_price,b.pay_price,b.balance,b.audit,b.flag,FROM_UNIXTIME(b.create_time,'%Y-%c-%d %h:%i:%s') as create_time,c.family_name,c.middle_name,c.name,d.domain,d.image_url,d.themb_url,d.extension")
		->join('order e','b.order_id=e.order_id','LEFT')
		->join('user c ','b.user_id=c.user_id','LEFT')
		->join('image d ','c.head_image=d.image_id','LEFT')
		->where($where)
		->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]]);
		//pre($refund);
		//pre(Db::name('refund')->getlastsql());
		foreach($refund as $key=>$value){
			$value['house']=Db::name('refund_house')
							->alias('a')
							->field("a.house_num,a.house_price,b.title,b.flag,b.max_person,c.domain,c.image_url,c.themb_url,c.extension")
							->join('activity_house b','a.house_id=b.house_id','LEFT')
							->join('image c','b.house_id=c.table_id and c.flag=4')
							->where(['a.refund_id'=>$value['refund_id']])->select();
			$refund[$key]=$value;
		}
		return $refund;
	}
	
	public function refund_list_user($data,$user_id){
		if(array_key_exists('audit',$data)&&$data['audit']){
			$where['audit']=$data['audit'];
		}
		$where['user_id']=$user_id;
		$refund=RefundModel::where($where)->order('create_time desc')->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]])->toArray(); 
		$refund_data=$refund;
		$refund_data['data']=[]; 
			foreach($refund['data'] as $key=>$value){
				$order_model=new OrderModel;
				$refund_value=$order_model->with(['cover','user'=>function($query){$query->field('user_id,family_name,middle_name,name');},'activity'=>function($query){$query->field('activity_id,kind_id,country,province,city,region');}])->where(['order_id'=>$value['order_id']])->find()->toArray(); 
				$refund_value['balance']=$value['balance'];
				$refund_value['audit']=$value['audit'];
				$refund_value['pay_price']=$value['pay_price'];
				$refund_value['total_price']=$value['total_price'];
				$refund_value['number']=$value['number']; 
				$refund_value['create_time']=$value['create_time'];  
				$refund_value['kind_id']=$refund_value['activity']['kind_id'];
				$refund_value['country']=$refund_value['activity']['country'];
				$refund_value['province']=$refund_value['activity']['province'];
				$refund_value['city']=$refund_value['activity']['city'];
				$refund_value['region']=$refund_value['activity']['region'];
				unset($refund_value['activity']);
				$refund_data['data'][]=$refund_value; 
			}  
			if($refund_data){
				$kind_model=new KindModel;
				$data=$kind_model->addkind_array($refund_data); 
			}
			return $refund_data;
			
	}
	
	public function refund_detail($refund_id){ 
		$refund=Db::name('refund')
				->alias('a')
				->field("a.order_id,a.balance,a.pay_price,a.total_price,a.person_num,a.flag,a.reason,FROM_UNIXTIME(a.create_time, '%Y-%c-%d' ) as create_time ,b.id as return_house_id,b.house_id,c.title,c.union_price,b.house_num,b.house_price,d.country,d.province,d.city,d.region,d.kind_id")
				->join('refund_house b','a.refund_id=b.refund_id','LEFT')
				->join('activity d','a.activity_id=d.activity_id','LEFT')
				->join('order_house c','b.oh_id=c.oh_id','LEFT')
				->where(['a.refund_id'=>$refund_id])
				->select();
		$data=[];
		if($refund){
			$house=[];
			foreach($refund as $key=>$value){
				if($value['return_house_id']){
					$house_value['id']=$value['return_house_id'];
					$house_value['house_id']=$value['house_id'];
					$house_value['title']=$value['title'];
					$house_value['union_price']=$value['union_price'];
					$house_value['house_num']=$value['house_num'];
					$house_value['house_price']=$value['house_price'];
					$house[]=$house_value;
				} 
			}
			$order_model=new OrderModel;
			$data= $order_model->with(['user'=>function($query){$query->with('headimage')->field('user_id,family_name,middle_name,name,mobile,head_image');},'cover'])->where(['order_id'=>$refund[0]['order_id']])->find()->toArray();  
			if($data){  
				$kind_model=new KindModel;
				$data['activity']['kind_id']=$refund[0]['kind_id'];
				$data=$kind_model->addkind_find($data);
				$data['country']=$refund[0]['country'];
				$data['province']=$refund[0]['province'];
				$data['city']=$refund[0]['city'];
				$data['region']=$refund[0]['region'];
				$data['balance']=$refund[0]['balance'];
				$data['pay_price']=$refund[0]['pay_price'];
				$data['total_price']=$refund[0]['total_price']; 
				$data['flag']=$refund[0]['flag']; 
				$data['person_num']=$refund[0]['person_num']; 
				$data['create_time']=$refund[0]['create_time']; 
				$data['reason']=$refund[0]['reason']; 
				$data['house']=$house;
				
				unset($data['activity']);
				
			}
		}
		
		return $data;
	}
	
	public function refuse($refund_id,$refuse_reason){
		$refund_model=new RefundModel;
		return $refund_model->where(['refund_id'=>$refund_id])->update(['audit'=>2,'refuse_reason'=>$refuse_reason]);
	}
	
	public function agree($refund_id,$return_price){ 
		$order=Db::name('refund')
		->alias('a') 
		->field('b.balance,b.pay_price')
		->join('order b','a.order_id=b.order_id','LEFT')
		->where(['a.refund_id'=>$refund_id])
		->find();
		if($order['balance']>0){
			if($order['balance']>=$return_price){
				$refund['balance']=$return_price;
				$refund['pay_price']=0;
				$refund['total_price']=$return_price;
			}
			if($order['balance']<$return_price){
				$refund['balance']=$order['balance'];
				$refund['pay_price']=$return_price-$order['balance'];
				$refund['total_price']=$return_price;
			}				
		}else{
			$refund['balance']=0;
			$refund['pay_price']=$return_price;
			$refund['total_price']=$return_price;
		}
		return Db::name('refund')->where(['refund_id'=>$refund_id])->update($refund);
	}
	
	public function check_return_price($refund_id,$return_price){ 
		$refund=Db::name('refund')->where(['refund_id'=>$refund_id])->find(); 
		$total_refund=Db::name('refund')->where(['order_id'=>$refund['order_id'],'audit'=>1])->sum('total_price');
		$total_price=Db::name('order')->where(['order_id'=>$refund['order_id']])->value('total_price');
		if(($total_price-$total_refund)<$return_price) return false;
		
		return true;
		
		
	}
	
	public function refund_to_pay($data,$order,$config){  
		$data['currency']=$order['currency'];
		$refund_model=new RefundModel;
		if($data['pay_price']>0){ 
			switch($order['pay_type']){
				case 1:
				//微信退款 
				$params=[
					'out_trade_no' => $order['order_no'],
					'total_fee' => $order['pay_price']*100,
					'refund_fee' =>$data['pay_price']*100,
					'out_refund_no' =>$data['number']
				];
				$result = \wxpay\Refund::exec($params);
				
				// 3.结果检验
				if(!(array_key_exists("return_code", $result)
					&& array_key_exists("result_code", $result)
					&& $result["return_code"] == "SUCCESS"
					&& $result["result_code"] == "SUCCESS"))
				{
					
					if(empty($result['return_msg']) || $result['return_msg'] == 'OK'){
						return ['code'=>0,'msg'=>'退款错误: '.$result['err_code']."  原因:".$result['err_code_des']];
						exit;
					}else{
						return ['code'=>0,'msg'=>'退款错误: '.$result['return_msg']]; 
						exit;
					}
				}
				break;
				case 2:
				//支付宝退款
				$params = [
					'out_trade_no' => $order['order_no'],  
					'refund_amount' => $data['pay_price'],
					'out_request_no' =>$data['number']
				];
				$result = \alipay\Refund::exec($params);
				//$result=json_decode($result,true);
				if (!empty($response['code']) && $response['code'] != '10000') {
					return ['code'=>0,'msg'=>'交易退款接口出错, 错误码: '.$response['code'].' 错误原因: '.$response['sub_msg']];
				}  
				break; 
			} 
		}
		if($data['balance']>0){
			$refund=$order;
			$refund['balance']=$data['balance'];	
			//如果用了基金   需退还基金
			if(!$this->refund_balance($order)){
				return ['code'=>0,'msg'=>'退款错误']; 
			}
		}  
		Db::name('refund')->where(['refund_id'=>$data['refund_id']])->update(['audit'=>1,'audit_time'=>time()]);
		Db::name('activity')->where(['activity_id'=>$data['activity_id']])->setDec('sale_num', intval($data['person_num']));
		//检验是否是全退
		if($this->check_all_refund($order)){ 
			//整个订单退款完成才可更改状态
			Db::name('order')->where(['order_id'=>$order['order_id']])->update(['status'=>2]);	
		}
		//算提成
		$reward_model=new RewardModel; 
		$user_h_model=new UserHModel; 
		$relation=$user_h_model->relation($order['user']); 
		/* $total_price=Db::name('refund')->where(['audit'=>1,'status'=>0])->sum('total_price');
		$order['total_price']-=$total_price */;
		$reward=$reward_model->reward_refund($data,$relation,$config);
		$reward_model->save_reward($reward['reward']);
		$order_h_model=new OrderHModel;
		$order_h_model->add_turnover($reward['order_update'],$order);
		if($order['iscomplete']==2){
			//已经结束   余额已到账   减下来
			$user_h_model->reduce_balance($reward['user']);
		}  
		return ['code'=>1,'msg'=>'退款成功']; 		
	}
	

}