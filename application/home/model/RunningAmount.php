<?php

namespace app\home\model;  
use app\common\model\RunningAmount as RunningAmountModel;

use think\Db;  
/**
 * 余额流水
 * Class RunningAmount
 * @package app\home\model
 */
class RunningAmount extends RunningAmountModel
{
	public function running_list($user_id,$page){ 
		return $this->where(['user_id'=>$user_id])->order('run_id desc')->paginate(10, false, ['query' => ["page"=>$page]]);
	}
	
	
	


}