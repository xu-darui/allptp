<?php

namespace app\task\controller;
use think\worker\Server;
use workerman\Lib\Timer;
use think\Db;
use app\task\model\Order as OrderModel; 
use app\task\model\Activity as ActivityModel; 
use app\task\model\User as UserModel; 
use app\common\model\Config as ConfigModel;
use app\common\model\Chat as ChatModel;
use Workerman\Worker as WorkerModel;

class Worker extends Server
{
   protected $socket = 'websocket://192.168.0.112:2346';
	
	// ====这里进程数必须必须必须设置为1====
	//$this->worker_model->count = 1;
	// 新增加一个属性，用来保存uid到connection的映射(uid是用户id或者客户端唯一标识)
	//$this->worker_model->uidConnections = array();
	//$socket->uidConnections = array();
    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
		file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . "收到消息". PHP_EOL, FILE_APPEND);	
        $connection->send('我收到你的信息了');
    }
    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    { 
		file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . "建立连接". PHP_EOL, FILE_APPEND);	 
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . "断开连接". PHP_EOL, FILE_APPEND);	
		/* global $worker;
		if(isset($connection->uid))
		{
			// 连接断开时删除映射
			unset($worker->uidConnections[$connection->uid]);
		} */
    }
    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
		$workerpro = new WorkerPro();
		$order_model = new OrderModel();
		$activity_model = new ActivityModel();
		$user_model = new UserModel();
		$config=ConfigModel::get(['key'=>'config']);
		$config=(json_decode($config['values'],true)); 
		// 10秒后执行发送邮件任务，最后一个参数传递false，表示只运行一次
		Timer::add(3,  array($workerpro, 'order_hanle'), array($config, $order_model,$activity_model,$user_model)); 
    }

	
 	
	

}

class WorkerPro
{
    // 注意，回调函数属性必须是public
    public function order_hanle($config, $order_model,$activity_model,$user_model)
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
		//增加策划者信誉分 
		$user_model->update_score($config);
    }
}