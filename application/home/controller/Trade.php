<?php

namespace app\home\controller;

use app\home\model\Reward as RewardModel;
use app\home\model\Order as OrderModel;
use app\home\model\RunningAmount;

/**
 * 交易中心
 * Class Trade
 * @package app\home\controller
 */
class Trade extends Controller
{
	public function soon($page){
		$userdata=$this->getuser();
		$reward_model=new RewardModel;
		return $this->renderSuccess($reward_model->soon_list(['a.user_id'=>$userdata['user_id']],$page));
	}

}