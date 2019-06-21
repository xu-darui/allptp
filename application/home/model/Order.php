<?php

namespace app\home\model;  
use app\common\model\Order as OrderModel; 
use app\common\model\Kind as KindModel; 
use app\common\model\ActivityHouse; 
use app\common\model\Turnover as TurnoverModel; 
use app\home\model\OrderPerson as OrderPersonModel; 
use app\home\model\OrderHouse as OrderHouseModel; 
use app\common\exception\BaseException;
use think\Db;
/**
 * 订单
 * Class Order
 * @package app\store\model
 */
class Order extends OrderModel
{
	public function add_order($data){ 
		$data['order_no']=date('Ymd').time().rand(1111,9999);
		Db::startTrans();
		try{ 
			$this->allowField(true)->save($data);   		
			if(array_key_exists('person',$data)){
				$orderperson_model=new OrderPersonModel;
				$orderperson_model->add_person($this->order_id,$data);  
			} 
			$house_price=0;
			if(array_key_exists('house',$data)&&$data['isstay']){
				$orderhouse_model=new OrderHouseModel;
				$house_price=$orderhouse_model->add_house($this->order_id,$data);
				
			}
			$this->where(['order_id'=>$this->order_id])->update(['total_price'=>$data['act_price']+$house_price,'pay_price'=>$data['act_price']+$house_price-(array_key_exists('balance',$data)?$data['balance']:0),'house_price'=>$house_price]);  
			//$this->where(['order_id'=>$this->order_id])->update(['total_price'=>$data['act_price']+$house_price,'pay_price'=>0,'house_price'=>$house_price]); 
			// 提交事务
			Db::commit(); 
			return $this->order_id;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}
		
	}
	
	public function getdetail($order_no){ 
		return $this->with(['user','house'])->where(['order_no'=>$order_no])->find()->toArray(); 	
	}
	
	public function order_update($order_update,$order){
		if(!array_key_exists('profit',$order_update)){
			$order_update=$order['price']; 
		}		
		$order_update['ispay']=1;
		$order_update['pay_time']=time();
		return $this->allowField(true)->save($order_update,['order_id'=>$order['order_id']]);
	}
	
	public function order_list($where,$page,$status){
		if($status!=''){
			$where['status']=$status;
		}else{
			$where['status']=['neq',2];
		}
		
		//pre($where);
		$data= $this->with(['cover','user'=>function($query){$query->field('user_id,family_name,middle_name,name');},'activity'=>function($query){$query->field('activity_id,kind_id,country,province,city,region');}])->where($where)->order('order_id desc')->paginate(10, false, ['query' => ["page"=>$page]]);
		if($data){
			foreach($data as $key=>$value){ 
				$data[$key]['kind_id']=$value['activity']['kind_id'];
				$data[$key]['country']=$value['activity']['country'];
				$data[$key]['province']=$value['activity']['province'];
				$data[$key]['city']=$value['activity']['city'];
				$data[$key]['region']=$value['activity']['region'];
				unset($data[$key]['activity']);
			}
			$kind_model=new KindModel;
			$data=$kind_model->addkind($data); 
		}
		return $data;
	}
	
	public function order_list_all($where){
		return $this->field('order_id,activity_id,slot_id,title,place,cover_image')->with(['cover'])->where($where)->select();
	}
	public function my_act_list($user_id,$keywords,$iscomplete ,$page){ 
		$where=['ispay'=>1,'iscomplete'=>$iscomplete,'user_id'=>$user_id];
		$where['status']=['neq',2];
		if($keywords){
			$where["title"]=['like','%'.$keywords.'%'];
		}
		$data=$this->field('order_id,activity_id,slot_id,title,activ_begin_time,activ_end_time,iscomplete,cover_image')->with(['cover','activity'=>function($query){$query->field('kind_id,activity_id');}])->where($where)->order('create_time desc')->paginate(10, false, ['query' => ["page"=>$page]]); 
		if($data){
			foreach($data as $key=>$value){ 
				$data[$key]['kind_id']=$value['activity']['kind_id'];
				unset($data[$key]['activity']);
			}
			$kind_model=new KindModel;
			$data=$kind_model->addkind($data); 
		}
		return $data;
	}
	
	public function order_person_num($where){
		$where['ispay']=1;
		$where['status']=['neq',2];
		$order_id=$this->where($where)->column('order_id');
		$refund_num=Db::name('refund')->where(['order_id'=>['in',$order_id],'audit'=>1])->sum('person_num');
		$order_num= $this->where($where)->sum('num');
		return $order_num-$refund_num;
	}
	
	public function calculate_price($slot_id,$num,$isstay,$is_book_whole,$balance,$house){
			$house_price=0;
		if($is_book_whole==1){
			//包场
			$slot_price=Db::name('activity_slot')->alias('a')->join('activity b','a.activity_id=b.activity_id')->where(['a.slot_id'=>$slot_id])->value('b.low_price');
		}else{
			//正常
			$slot_price=Db::name('activity_slot')->where(['slot_id'=>$slot_id])->value('price');
			$slot_price=$slot_price*$num;
		} 
		if($isstay==1){
			if($house){ 
				foreach($house as $key=>$value){
					$price=Db::name('activity_house')->where(['house_id'=>$value['house_id']])->value('price');
					$house_price+=$price*$value['num'];
				}
			}
		}
		return $slot_price+$house_price-$balance;
	}
	public function detail_order($order_id){
		$data= $this->with(['user'=>function($query){$query->with('headimage')->field('user_id,family_name,middle_name,name,mobile,head_image');},'house'=>function($query){$query->with(['image','acthouse'=>function($query){$query->field('house_id,flag');}]);},'person','cover','activity'])->where(['order_id'=>$order_id])->find();
		if($data){
			$kind_model=new KindModel;
			$data=$kind_model->addkind_find($data);
			$data['country']=$data['activity']['country'];
			$data['province']=$data['activity']['province'];
			$data['city']=$data['activity']['city'];
			$data['region']=$data['activity']['region'];
			unset($data['activity']);  
		}
		return $data;
	}
	
	public function pay_status($where){
		return $this->where($where)->value('ispay');
	}
	
	public function isorder($slot_id){
		return $this->where(['slot_id'=>['in',$slot_id],'ispay'=>1,'status'=>['neq',2],'iscomplete'=>['neq',2]])->count();
	}
	
	public function order_list_planner($where){   
		$data= $this->with(['user'=>function($query){$query->with(['headimage'])->field('user_id,family_name,middle_name,name,mobile,head_image');},'house'=>function($query){$query->with('image')->field('house_id,order_id,num,union_price,price,title,max_person');},'comment','cover'])->where($where)->order('order_id desc')->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]]); 
		return $data;
	}
	
	public function add_turnover($order_update,$order){
		$turnover_model=new TurnoverModel;
		$data['order_id']=$order['order_id'];
		$data['refund_id']=$order_update['refund_id'];
		$data['income']=$order_update['income'];
		$data['expend']=$order_update['expend'];
		$data['profit']=$order_update['profit'];
		$data['flag']=1;
		$data['user_id']=$order['user_id'];
		$turnover_model->allowField(true)->save($data);
	}
	

	

}