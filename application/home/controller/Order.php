<?php

namespace app\home\controller;
use app\home\model\Order as OrderModel; 
use app\home\model\Activity as ActivityModel; 
use app\home\model\ActivitySlot as ActivitySlotModel; 
use app\home\model\ActivityHouse as ActivityHouseModel; 
use app\task\controller\Notify as NotifyModel; 
use app\common\exception\BaseException;
use \think\Validate;
use think\Db;
class Order extends Controller
{
    // 扫码支付
    public function add()
    {  
	/*  $order_id=10;
	$order = db('order')->field('order_no as out_trade_no,order_id as id,title as subject, pay_price as total_amount,status')->where('order_id', $order_id)->find(); 
						$result=\alipay\Pagepay::pay($order);
						
		exit; */
		//pre(input());
		$userdata=$this->getuser();
		$data=input();
		$data['user_id']=$userdata['user_id'];
		//pre($_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME']);
		/* $data['house'] =[
			['house_id'=>1,'num'=>3],
			['house_id'=>13,'num'=>2]
		]; 
		$data['person'] =[
			['name'=>'xurui','idcard'=>'5138','m_code'=>'86','mobile'=>'18180051676'],
			['name'=>'xurui2','idcard'=>'3434343','m_code'=>'86','mobile'=>'18282472277']
		];  */
			
	  // $credit_score=Db::name('activity')->alias('a')->join('user b','a.user_id=b.user_id','left')->value('b.credit_score');
		$activity_model=new ActivityModel;
		$activity_user=$activity_model->check_credit($data);
		$config=$this->config(); 
		if($activity_user['credit_score']<$config['credit']['order_score']){
			if(!(array_key_exists('isapp',$data)&&$data['isapp'])&&$data['pay_type']==2){
					//支付宝直接抛出异常
					throw new BaseException(['code' => 404, 'msg' => '该体验的创建者信誉分过低,暂时无法下单']);
				}else{
					return $this->renderError('该体验的创建者信誉分过低,暂时无法下单');
				}
		}
		$validate = new Validate([
			'activity_id'  => 'require',
			'num' => 'require|gt:0',
			'pay_type' => 'require|in:1,2,3,4', 
		],[
			'activity_id.require'=>'请选择活动',
			'num.require'=>'请选择订单人数', 
			'num.min'=>'订单人数必须大于0',   
			'pay_type.require'=>'请选择支付方式',   
			'pay_type.in'=>'选择支付方式不正确',   
		]); 
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		
		if(array_key_exists('balance',$data)&&$data['balance']>0){
			if($userdata['balance']<$data['balance']){ 
				if(!(array_key_exists('isapp',$data)&&$data['isapp'])&&$data['pay_type']==2){
					//支付宝直接抛出异常
					throw new BaseException(['code' => 404, 'msg' => '基金抵扣超额，请重新选择抵扣基金']);
				}else{
					return $this->renderError('基金抵扣超额，请重新选择抵扣基金');
				}
				
			}
		}
		$activity_model=new ActivityModel;
		if(!$activity_data=$activity_model->detail(['activity_id'=>$data['activity_id'],'complete'=>1,'status'=>0,'audit'=>1])){
			if(!(array_key_exists('isapp',$data)&&$data['isapp'])&&$data['pay_type']==2){
					//支付宝直接抛出异常
				throw new BaseException(['code' => 404, 'msg' => '没有该活动']);
			}else{
				return $this->renderError('没有该活动');
			}
			
		}
		if($activity_data['user_id']==$userdata['user_id']){
			//pre($data['pay_type']);
			if(!(array_key_exists('isapp',$data)&&$data['isapp'])&&$data['pay_type']==2){
					//支付宝直接抛出异常
				throw new BaseException(['code' => 404, 'msg' => '您不可以购买自己创建的活动']);
			}else{
				return $this->renderError('您不可以购买自己创建的活动');
			}
		}
		$data['title']=$activity_data['title'];
		$data['cover_image']=$activity_data['cover_image'];
		$data['place']=$activity_data['region'];
		if(array_key_exists('slot_id',$data)&&$data['slot_id']){
			$slot_model=new ActivitySlotModel;
			if(!$slot_data=Db::name('activity_slot')->where(['slot_id'=>$data['slot_id'],'status'=>['neq',1]])->find()){
				if(!(array_key_exists('isapp',$data)&&$data['isapp'])&&$data['pay_type']==2){
					//支付宝直接抛出异常
					throw new BaseException(['code' => 404, 'msg' => '没有该时间段信息']);
				}else{
					return $this->renderError('没有该时间段信息');
				} 
			}else if($slot_data['status']==2){ 
				if(!(array_key_exists('isapp',$data)&&$data['isapp'])&&$data['pay_type']==2){
					//支付宝直接抛出异常
					throw new BaseException(['code' => 404, 'msg' => '该时间段已过期']);
				}else{
					return $this->renderError('该时间段已过期');
				}
			}else if($activity_data['end_order']>=0){
				if(time()+$activity_data['end_order']>$slot_data['begin_time']){ 
					if(!(array_key_exists('isapp',$data)&&$data['isapp'])&&$data['pay_type']==2){
					//支付宝直接抛出异常
					throw new BaseException(['code' => 404, 'msg' => '该活动时间段已停止下单']);
				}else{
					return $this->renderError('该活动时间段已停止下单');
				}
				}
			}
			$data['act_union_price']=$unit_price=$slot_data['price'];
			$data['activ_begin_time']=$slot_data['begin_time'];
			$data['activ_end_time']=$slot_data['end_time'];
			$data['total_time']=$slot_data['total_time'];
			
		}else{ 	
			$data['act_union_price']=$unit_price=$activity_data['price'];
			$data['activ_begin_time']=$activity_data['activ_begin_time'];
			$data['activ_end_time']=$activity_data['activ_end_time'];
			$data['total_time']=$activity_data['total_time'];
			
		} 
		if(array_key_exists('is_book_whole',$data)&&$data['is_book_whole']){
			$activity_data=$activity_model->detail(['activity_id'=>$data['activity_id'],'complete'=>1,'status'=>0,'audit'=>1]);
			$data['act_union_price']=$unit_price=$activity_data['low_price'];
		}
		$data['act_price']=$unit_price*$data['num'];
		$order_model=new OrderModel;  
		/* $params = [
            'notice' => '支付测试',
            'order_no' => $order_no,
            'price' => 0.01,
            'user_id' => 9,
            'activity_id' => 1,
        ];
        db('order')->insert($params); */  
		if($order_id=$order_model->add_order($data)){
			$order=db('order')->field('pay_price,order_no')->where(['order_id'=>$order_id])->find();			
			//$order['pay_price']=0;
			//if($order['pay_price']==0&&$data['pay_type']==4){ 
			if(intval($order['pay_price'])==0){ 
			//实际支付金额为0  则免支付
				$notify_model=new NotifyModel;
				if($notify_model->balance($order['order_no'])){
					return $this->renderSuccess('支付成功');
				}else{
					return $this->renderError('支付失败');
				}
			}else{
				if(array_key_exists('isapp',$data)&&$data['isapp']){
					switch($data['pay_type']){
						//app端
						case 1:
							//微信支付
							$order=db('order')->field('order_no as out_trade_no,activity_id as product_id,title as body, pay_price*100 as total_fee')->where(['order_id'=>$order_id])->find(); 				
							$order['total_fee']=intval($order['total_fee']) ;  
							return \wxpay\AppPay::getParams($order);
							//echo $result;
						break;
						case 2:
							//	支付宝支付
							$order = db('order')->field('order_no as out_trade_no,order_id as id,title as subject, pay_price as total_amount,status')->where('order_id', $order_id)->find();
							//pre($order);
							/* $params = [
								'out_trade_no' => $order['order_no'],
								'total_amount' => $order['price'],
								'status'       => $order['status'],
								'id'           => $order['order_id'],
								'subject'           => "test"
							]; */ 
							$result=\alipay\Pagepay::pay($order);
							break;
						case 3:
							break;
					}
					
				}else{
					//pc端
					switch($data['pay_type']){
						case 1:
							//微信支付
							$order=db('order')->field('order_no as out_trade_no,activity_id as product_id,title as body, pay_price*100 as total_fee')->where(['order_id'=>$order_id])->find(); 				
							$order['total_fee']=intval($order['total_fee']) ;  
							$result = \wxpay\NativePay::getPayImage($order);
							return $this->renderSuccess(['order_id'=>$order_id,'url'=>$result]);
							//echo $result;
						break;
						case 2:
							//	支付宝支付
							$order = db('order')->field('order_no as out_trade_no,order_id as id,title as subject, pay_price as total_amount,status')->where('order_id', $order_id)->find();
							//pre($order);
							/* $params = [
								'out_trade_no' => $order['order_no'],
								'total_amount' => $order['price'],
								'status'       => $order['status'],
								'id'           => $order['order_id'],
								'subject'           => "test"
							]; */ 
							$result=\alipay\Pagepay::pay($order);
							break;
						case 3:
							break;
				} 
				}
				
				
				
			}
		}else{
			return $this->renderError('创建订单失败');
		}

		
        //echo $result;
    }
	
	
	public function test(){
		$order_no=time();
		Db::name('order')->insert(['order_no'=>$order_no,'pay_price'=>1,'user_id'=>9,'activity_id'=>1]); 
		$params=$this->getOrder($order_no); 
		file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . "生成预付1111". PHP_EOL, FILE_APPEND);	
		return \wxpay\AppPay::getParams($params);
	}
	
	  /**
     * 获取订单信息, 必须包含订单号和订单金额
     *
     * @return string $params['out_trade_no'] 商户订单
     * @return float  $params['total_amount'] 订单金额
     */
    public function getOrder($order_no)
    {
        // 以下仅示例
       // $order_no = $_POST['order_no'];
       // $order_no ="20181224154005";
        $order = Db::name('order')->where('order_no', $order_no)->find();
        $params = [
            'out_trade_no' => $order['order_no'],
            'total_fee' => intval($order['pay_price']), 
            'body'           => "test"
        ];

       return  $params;
    }

	public function get_slot_num($slot_id,$activity_id){
		$order_model=new OrderModel;
		$activity_model=new ActivityModel;
		//$mac_person_num=$activity_model->slot_detail(['a.slot_id'=>$slot_id,'a.activity_id'=>$activity_id]);
		$order_person_num=$order_model->order_person_num(['slot_id'=>$slot_id,'activity_id'=>$activity_id]);
		return $this->renderSuccess(['order_person_num'=>$order_person_num]);
	}
	public function pay_status($order_id){
		$order_model=new OrderModel;
		$pay_status=$order_model->pay_status(['order_id'=>$order_id]);
		return $this->renderSuccess(['pay_status'=>$pay_status]);
	}
	public function get_house_num($slot_id,$activity_id){
		//未完成
	}
	public function calculate_price($slot_id,$num=1,$isstay=0,$is_book_whole=0,$balance=0,$house=[]){
		/* $house =[ 
			['house_id'=>22,'num'=>2]
		]; */
		$userdata=$this->getuser();
		if($balance>$userdata['balance']){
			return $this->renderError("旅行基金不足");
		}
		
		$order_model=new OrderModel;
		$total_price=$order_model->calculate_price($slot_id,$num,$isstay,$is_book_whole,$balance,$house);
		//echo ($total_price);
		if($total_price<$balance){
			return $this->renderError('使用基金已超过总金额');
		}
		return $this->renderSuccess(['total_price'=>$total_price]);
	}
	
	
	public function detail($order_id){
		$order_model=new OrderModel;
		return $this->renderSuccess($order_model->detail_order($order_id));
		
	}
	
	public function to_pay($order_id,$pay_type){
		$order=Db::name('order')->where(['order_id'=>$order_id])->find();
		if($order['ispay']===1){
			return $this->renderError("该订单已经支付");
		}
		if($order['status']===1){
			return $this->renderError("该订单正在申请退款");
		}
		if($order['status']===2){
			return $this->renderError("该订单已经退款成功");
		} 
		Db::name('order')->where(['order_id'=>$order_id])->update(['pay_type'=>$pay_type]); 
			//$order['pay_price']=0;
		if($order['pay_price']==0&&$pay_type==4){ 
			//实际支付金额为0  则免支付
			$notify_model=new NotifyModel;
			if($notify_model->balance($order['order_no'])){
				return $this->renderSuccess('支付成功');
			}else{
				return $this->renderError('支付失败');
			}
		}else{ 
			switch($pay_type){
				case 1:
					//微信支付
					$order=db('order')->field('order_no as out_trade_no,activity_id as product_id,title as body, pay_price*100 as total_fee')->where(['order_id'=>$order_id])->find(); 				
					$order['total_fee']=intval($order['total_fee']) ;   
					$result = \wxpay\NativePay::getPayImage($order); 
					return $this->renderSuccess(['order_id'=>$order_id,'url'=>$result]);
					//echo $result;
				break;
				case 2:
					//	支付宝支付
					$order = db('order')->field('order_no as out_trade_no,order_id as id,title as subject, pay_price as total_amount,status')->where('order_id', $order_id)->find(); 
					$result=\alipay\Pagepay::pay($order);
					break;
				case 3:
					break;
				} 
				
			} 
		
	}
	
	public function price_update($order_id,$total_price){
		$order=Db::name('order')->where(['order_id'=>$order_id])->find();
		if($order['ispay']==1){
			return $this->renderError("该订单已经支付,不能修改价格");
		}
		if($order['balance']>=$total_price){
			$result=Db::name('order')->where(['order_id'=>$order_id])->update(['isedit_price'=>1,'balance'=>$total_price,'pay_price'=>0,'total_price'=>$total_price]);
		}else{
			$result=Db::name('order')->where(['order_id'=>$order_id])->update(['isedit_price'=>1,'pay_price'=>$total_price,'total_price'=>$total_price]);
		}
		if($result){
			return $this->renderSuccess('修改价格成功');
		}else{
			return $this->renderError('修改价格失败');
		}
		
	}
	
	public function house_use_num($activity_id,$slot_id){
		$end_time=Db::name('activity_slot')->where(['slot_id'=>$slot_id])->value('end_time'); 
		$house_model=new ActivityHouseModel;
		$house=$house_model->get_house_array($activity_id);
		$end_time=date('Y-m-d', $end_time);
		$between_time=[strtotime($end_time),strtotime($end_time)+86400]; 
		if(!$house){
			return $this->renderError('修改价格失败');
		} 
		$order_id_array=Db::name('order')->where(['activ_end_time'=>['between',$between_time],'activity_id'=>$activity_id,'ispay'=>1,'status'=>['neq',2]])->column('order_id'); 
		if($order_id_array){
			$order_house=Db::name('order_house')->alias('a')->field("a.oh_id,a.house_id,(ifnull(sum(a.num), 0) - ifnull(sum(b.house_num), 0)) AS num")->join('refund c','a.order_id=c.order_id and c.audit=1','LEFT')->join('refund_house b','c.refund_id = b.refund_id and a.oh_id=b.oh_id','LEFT')->where(['a.order_id'=>['in',$order_id_array]])->group('a.house_id')->select();  
			//pre($order_house);			
			foreach($house as $key=>$value){
				$num=$value['num'];
				foreach($order_house as $house_key=>$house_value){
					if($value['house_id']==$house_value['house_id']){
						//var_dump($num.'----'.$house_value['num']);
						$value['num']=$num-$house_value['num'];
						$house[$key]=$value; 
					}
					continue;
				}
				
			}
		}
		
		return $this->renderSuccess($house);
		
		
	}
	
    // 公众号支付
    public function jspay()
    {
        $params = [
            'body' => '支付测试',
            'out_trade_no' => mt_rand().time(),
            'total_fee' => 1,
        ];
        $result = \wxpay\JsapiPay::getPayParams($params);
        halt($result);
    }

    // 小程序支付
    public function smallapp()
    {
        $params = [
            'body'         => '支付测试',
            'out_trade_no' => mt_rand().time(),
            'total_fee'    => 1,
        ];
        $code = '08123gA41K4EQO1RH1B41uP2A4123gAW';
        $result = \wxpay\JsapiPay::getPayParams($params, $code);

        $openId = 'oCtoK0SjxW-N5qjEDgaMyummJyig';
        $result = \wxpay\JsapiPay::getParams($params, $openId);
    }

    // 刷卡支付
    public function micropay()
    {
        $params = [
            'body' => '支付测试',
            'out_trade_no' => mt_rand().time(),
            'total_fee' => 1,
        ];

        $auth_code = '134628839776154108';
        $result = \wxpay\MicroPay::pay($params, $auth_code);
        halt($result);
    }

    // H5支付
    public function wappay()
    {
        $params = [
            'body' => '支付测试',
            'out_trade_no' => mt_rand().time(),
            'total_fee' => 1,
        ];

        $result = \wxpay\WapPay::getPayUrl($params);
        halt($result);
    }

    // 订单查询
    public function query()
    {
        $out_trade_no = '290000985120170917160005';
        $result = \wxpay\Query::exec($out_trade_no);
        halt($result);
    }

    // 退款
    public function refund($order_no)
    {
        $params = [
            'out_trade_no' => $order_no,
            'total_fee' => 100,
            'refund_fee' => 100,
            'out_refund_no' => time()
        ];
        $result = \wxpay\Refund::exec($params);
        halt($result);
    }    
	
	// 支付宝退款 
	public function alipay_refund($order_no)
    {
        $params = [
            'out_trade_no' => $order_no,  
            'refund_amount' => 2,
            'out_request_no' => time()
        ];
        $result = \alipay\Refund::exec($params);
        halt($result);
    }

    // 退款查询
    public function refundquery()
    {
        $order_no = '290000985120170917160005';
        $result = \wxpay\RefundQuery::exec($order_no);
        halt($result);
    }

    // 下载对账单
    public function download()
    {
        $result = \wxpay\DownloadBill::exec('20170923');
        echo($result);
    }

    // 通知测试
    public function notify()
    {
        $notify = new \wxpay\Notify();
        $notify->Handle();
    }

	
}