<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use workerman\Lib\Timer;
use app\task\model\Order as OrderModel; 
use app\task\model\Activity as ActivityModel; 
use app\common\model\Config as ConfigModel;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        Gateway::sendToClient($client_id, json_encode(array(
            'type'      => 'init',
            'client_id' => $client_id
        )));
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
        //file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $client_id."----".$message. PHP_EOL, FILE_APPEND);	 
	  
        // 向所有人发送 
        //Gateway::sendToAll(json_encode(['type'=>'msg','msg'=>'dddd']));
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       // 向所有人发送 
      // GateWay::sendToAll("$client_id logout\r\n");
   }
   
      // 进程启动时设置个定时器。Events中支持onWorkerStart需要Gateway版本>=2.0.4
    public static function onWorkerStart()
    {
        $workerpro = new WorkerPro();
		$config=ConfigModel::get(['key'=>'config']);
		//$order_model = new OrderModel();
		//$activity_model = new ActivityModel();
		//$config=ConfigModel::get(['key'=>'config']);
		//$config=(json_decode($config['values'],true)); 
		//$config=[]; 
		// 10秒后执行发送邮件任务，最后一个参数传递false，表示只运行一次
		//Timer::add(3,  array($workerpro, 'order_hanle'), array($config, $order_model,$activity_model)); 
    }
}
class WorkerPro
{
    // 注意，回调函数属性必须是public
    public function order_hanle($config, $order_model,$activity_model)
    { 
	echo 1;
		//时间段是否已经过期 
		//$activity_model->update_slot();
		//超时没有支付的订单取消
		//$order_model->cancel($config);  
		//活动时间前2小时发送提醒
		//$order_model->act_advance_send($config);
		//活动开始加标记
		//$order_model->act_begin($config);
		//活动完成计算提成+余额
		//$order_model->act_end($config);
		
    }
}
