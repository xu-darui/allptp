<?php 
namespace app\task\controller; 
use app\task\model\Order as OrderModel; 
use app\task\model\User as UserModel; 
use app\task\model\Reward as RewardModel; 
use app\task\model\Activity as ActivityModel;  
use app\common\model\Sendmsg;   
use think\Db;
use app\common\exception\BaseException;

/**
 * 支付成功异步通知接口
 * Class Notify
 * @package app\api\controller
 */
class Notify extends Controller
{
	private $config; // 微信支付配置
    /**
     * 支付成功异步通知
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function order()
    { 
		
		 
		$out_trade_no=input("out_trade_no");
		//$out_trade_no='2019031915529803588940';
	   file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . json_encode(input()). PHP_EOL, FILE_APPEND);
		if(\alipay\Notify::check(OrderModel::payDetail($out_trade_no))){
			// file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . "成功". PHP_EOL, FILE_APPEND);
			$config=$this->config();
			$order_model=new OrderModel;
			$order=$order_model->getdetail($out_trade_no);
			if($order['ispay']==1){
				echo "fail";exit;
			}
			$user_model=new UserModel;
			$relation=$user_model->relation($order['user']);
			Db::startTrans(); //启动事务
				try {
					$user_model->reduce_balance($order);
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
					echo "success";
				} catch (\PDOException $e) {
					Db::rollback(); //回滚事务
					 echo "fail";
			}
		}else{
			 echo "fail";
		}
	  
    }
	
	public function weixin(){
		if (!$xml = file_get_contents('php://input')) {
            $this->returnCode(false, 'Not found DATA');
        }
		/* $xml="<xml><appid><![CDATA[wx9448cd625654d292]]></appid>
<bank_type><![CDATA[CFT]]></bank_type>
<cash_fee><![CDATA[1]]></cash_fee>
<fee_type><![CDATA[CNY]]></fee_type>
<is_subscribe><![CDATA[N]]></is_subscribe>
<mch_id><![CDATA[1524985871]]></mch_id>
<nonce_str><![CDATA[nldt5gc2ecldjlbipe77cauzxka79n7l]]></nonce_str>
<openid><![CDATA[ohnEi1eLlTE8CNoAbzLWAA_eOwWk]]></openid>
<out_trade_no><![CDATA[9840925201548749333]]></out_trade_no>
<result_code><![CDATA[SUCCESS]]></result_code>
<return_code><![CDATA[SUCCESS]]></return_code>
<sign><![CDATA[B8ECF47911B490A7A45B1F55E04F8A62]]></sign>
<time_end><![CDATA[20190129160902]]></time_end>
<total_fee>1</total_fee>
<trade_type><![CDATA[NATIVE]]></trade_type>
<transaction_id><![CDATA[4200000264201901294113894874]]></transaction_id>
</xml>"; */
        // 将服务器返回的XML数据转化为数组
        $data = $this->fromXml($xml); 
		file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . json_encode($data). PHP_EOL, FILE_APPEND);
        // 记录日志 
        // 订单信息
		$order_model=new OrderModel; 
		$order=$order_model->getdetail($data['out_trade_no']); 
        empty($order) && $this->returnCode(false, '订单不存在');
		$this->config=Db::name('payconfig')->field('app_id,merchant_private_key as apikey')->where(['type'=>1])->find(); 
		//file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . json_encode($this->config). PHP_EOL, FILE_APPEND);
        // 保存微信服务器返回的签名sign
        $dataSign = $data['sign'];
        // sign不参与签名算法
        unset($data['sign']);
        // 生成签名
        $sign = $this->makeSign($data);
        // 判断签名是否正确  判断支付状态
        if (($sign === $dataSign)
            && ($data['return_code'] == 'SUCCESS')
            && ($data['result_code'] == 'SUCCESS')) {
				$config=$this->config(); 
				//file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . json_encode($order).PHP_EOL, FILE_APPEND);
				if($order['ispay']==1){
					  $this->returnCode(false, '已经支付');
				}
				$user_model=new UserModel;
				$relation=$user_model->relation($order['user']);
				Db::startTrans(); //启动事务
					try {
						$user_model->reduce_balance($order);
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
						$this->returnCode(true, 'OK');
					} catch (\PDOException $e) {
						Db::rollback(); //回滚事务
						 $this->returnCode(false, '回滚失败');
				}	 
        }
        // 返回状态
        $this->returnCode(false, '签名失败');
	}
	
	public function balance($order_no){
		 // 订单信息
		$order_model=new OrderModel; 
		$order=$order_model->getdetail($order_no);  
		$config=$this->config();
		$user_model=new UserModel;
		$relation=$user_model->relation($order['user']);
		Db::startTrans(); //启动事务
			try {
				
				$user_model->reduce_balance($order);
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
				return true;
			}catch (\PDOException $e) {
					Db::rollback(); //回滚事务
					return false;
			}	 		
	}
	
	    /**
     * 将xml转为array
     * @param $xml
     * @return mixed
     */
    private function fromXml($xml)
    {
        // 禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
	
	    /**
     * 返回状态给微信服务器
     * @param bool $is_success
     * @param string $msg
     */
    private function returnCode($is_success = true, $msg = null)
    {
        $xml_post = $this->toXml([
            'return_code' => $is_success ? $msg ?: 'SUCCESS' : 'FAIL',
            'return_msg' => $is_success ? 'OK' : $msg,
        ]);
        die($xml_post);
    }
	
	    /**
     * 生成签名
     * @param $values
     * @return string 本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    private function makeSign($values)
    {
        //签名步骤一：按字典序排序参数
        ksort($values);
        $string = $this->toUrlParams($values);
        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $this->config['apikey'];
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
	
	    /**
     * 格式化参数格式化成url参数
     * @param $values
     * @return string
     */
    private function toUrlParams($values)
    {
        $buff = '';
        foreach ($values as $k => $v) {
            if ($k != 'sign' && $v != '' && !is_array($v)) {
                $buff .= $k . '=' . $v . '&';
            }
        }
        return trim($buff, '&');
    }
	    /**
     * 输出xml字符
     * @param $values
     * @return bool|string
     */
    private function toXml($values)
    {
        if (!is_array($values)
            || count($values) <= 0
        ) {
            return false;
        }

        $xml = "<xml>";
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
	/* 
		public function test(){  
		//时间段是否已经过期 
		$activity_model = new ActivityModel;
		$activity_model->update_slot();
	} */
	public function test(){
		$order_model=new OrderModel;
		$config=$this->config();
		$user_model = new UserModel();
		$user_model->update_score($config);
		//活动时间前2小时发送提醒
		//$order_model->act_advance_send($config);
		//活动完成计算提成+余额
		//$order_model->act_end($config);
	}


}
