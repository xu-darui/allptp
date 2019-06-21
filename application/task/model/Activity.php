<?php

namespace app\task\model;

use app\common\model\Activity as ActivityModel;    
use think\Db;

/**
 * 活动模型
 * Class Activity
 * @package app\task\model
 */
class Activity extends ActivityModel
{
	public function add_sale($order){
		$this->where('activity_id',$order['activity_id'])->setInc('sale_num',+1);
	}

	public function update_slot(){
		$nowtime=time(); 
		Db::name('activity_slot')->execute("update ptp_activity_slot set status=2 where end_time<$nowtime and status=0");
	}
}