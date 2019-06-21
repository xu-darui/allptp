<?php

namespace app\home\model;  
use app\common\model\Draw as DrawModel; 
use app\common\model\User as UserModel; 
use app\common\model\RunningAmount; 
use think\Db;  
/**
 * 提现
 * Class Draw
 * @package app\home\model
 */
class Draw extends DrawModel
{
	public function save_draw($data){
		Db::startTrans();
		try{ 
			$user_model=new UserModel;
			$user_model->where(['user_id'=>$data['user_id']])->setDec('balance',$data['amount']);
			$running_model=new RunningAmount;
			$running_model->allowField(true)->save(['user_id'=>$data['user_id'],'amount'=>$data['amount'],'flag'=>5,'balance'=>$data['balance']-$data['amount']]);
			$this->allowField(true)->save($data);
			// 提交事务
			Db::commit(); 
			return true;
		}catch (\Exception $e) {
			pre($e);
			// 回滚事务
			Db::rollback();
		}
	}

}