<?php

namespace app\home\controller;
 
use app\home\model\Draw as DrawModel;
use app\home\model\UserBank as UserBankModel;
use app\common\model\Md5Entry;


/**
 * 提现中心
 * Class Draw
 * @package app\home\controller
 */
class Draw extends Controller
{
	public function draw_submit($amount,$bank_id,$pay_password){
		$userdata=$this->getuser(); 
		if(Md5Entry::password($pay_password)!==$userdata['pay_password']){
			return $this->renderError('输入密码不正确');
		}
		$config=$this->config();
		if($config['draw_mini']>$amount){
			return $this->renderError('提现额度最少为'.$config['draw_mini'].'元');
		}
		if($amount>$userdata['balance']){
			return $this->renderError('申请提现金额大于余额');
		}
		$bank_model=new UserBankModel;
		if(!$bank_model->detail($bank_id)){
			return $this->renderError('没有该银行卡');
		}
		$draw_model=new DrawModel;
		if($draw_model->save_draw(['amount'=>$amount,'user_id'=>$userdata['user_id'],'balance'=>$userdata['balance'],'bank_id'=>$bank_id])){
			return $this->renderSuccess('提现成功');
		}else{
			return $this->renderError('提现失败');
		}
		
	}
	
}