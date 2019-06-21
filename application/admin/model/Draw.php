<?php

namespace app\admin\model;
use app\common\model\Draw as DrawModel;
use think\Db;

/**
 * 提现申请
 * Class Draw
 * @package app\admin\model
 */
class Draw extends DrawModel
{
	public function draw_pass($draw_id){
		return $this->where(['draw_id'=>$draw_id])->update(['status'=>1]);
	}
	
	public function draw_list($keywords,$sort,$page){
		$where_key='';
		$where=[];
		if(array_key_exists('status',input())){
			$where['d.status']=input('status'); 
		}
		if($keywords){
			$where_key="CONCAT(IFNULL(u.family_name,''),IFNULL(u.middle_name,''),IFNULL(u.name,'')) like '%".$keywords."%'";
		}
		switch($sort){
			case 1:
				$order="d.amount desc";
				break; 
			default:
				$order="d.draw_id desc";
		} 
			return Db::name('draw')
			->alias('d')
			->field("d.draw_id,d.amount,FROM_UNIXTIME(d.create_time,'%Y-%c-%d') as create_time,FROM_UNIXTIME(d.audit_time,'%Y-%c-%d') as audit_time,u.family_name,u.middle_name,u.name,b.bank_name,b.card_number,b.user_name")
			->join('user u','d.user_id = u.user_id','left')
			->join('user_bank b','d.bank_id=b.bank_id','left')
			->where($where)
			->where($where_key) 
			->order($order)
			->paginate(10, false, ['query' => ["page"=>$page]]);  
		
		
	}

}