<?php

namespace app\home\model;

use app\common\model\Attention as AttentionModel;
use think\Db;

/**
 * 关注模型
 * Class Attention
 * @package app\store\model
 */
class Attention extends AttentionModel
{
	public function get_attention($where){
		return $this->where($where)->find();
		
	}
	public function add_attention($data){
		Db::startTrans();
		try{
			
			$attention_data=$this->get_attention($data); 
			if($attention_data['status']===1){
				Db::name('attention')->where(['attention_id'=>$attention_data['attention_id']])->update(['create_time'=>time(),'status'=>0,'unsub_time'=>0]);
			}else{			
			$data['create_time']=time();
			Db::name('attention')->insert($data); 
			}			
			Db::name('user')->where(['user_id'=>$data['att_user_id']])->setInc('fans_num'); 
			
			Db::name('user')->where(['user_id'=>$data['user_id']])->setInc('attention_num'); 
		// 提交事务
			Db::commit(); 
			return true;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}
	}
	public function remove_attention($where){
		Db::startTrans();
		try{ 
			
			Db::name('attention')->where($where)->update(['status'=>1,'unsub_time'=>time()]);		
			Db::name('user')->where(['user_id'=>$where['att_user_id']])->setDec('fans_num'); 
			Db::name('user')->where(['user_id'=>$where['user_id']])->setDec('attention_num'); 
		// 提交事务
			Db::commit(); 
			return true;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}
	}
	
	public function my_att_list($user_id,$page){
		return  Db::name('attention')
		->alias('a')
		->field("FROM_UNIXTIME(a.create_time, '%Y-%c-%d' ) as create_time,a.user_id,b.family_name,b.middle_name,b.name,b.introduce,b.activ_num,b.volun_num,c.domain,c.image_url,c.themb_url,if(d.attention_id,1,0) as is_mutualatt")
		->join('user b','a.user_id=b.user_id')
		->join('image c','c.image_id=b.head_image','LEFT')
		->join('attention d','d.att_user_id=a.user_id and d.user_id=a.att_user_id','LEFT')
		->where(['a.att_user_id'=>$user_id])
		->paginate(10, false, ['query' => ["page"=>$page]]);
	}

	public function att_other_list($user_id,$page){
		return  Db::name('attention')
		->alias('a')
		->field("FROM_UNIXTIME(a.create_time, '%Y-%c-%d' ) as create_time,a.user_id,b.family_name,b.middle_name,b.name,b.introduce,b.activ_num,b.volun_num,c.domain,c.image_url,c.themb_url,if(d.attention_id,1,0) as is_mutualatt")
		->join('user b','a.att_user_id=b.user_id')
		->join('image c','c.image_id=b.head_image','LEFT')
		->join('attention d','d.att_user_id=a.user_id and d.user_id=a.att_user_id','LEFT')
		->where(['a.user_id'=>$user_id])
		->paginate(10, false, ['query' => ["page"=>$page]]);
	}

}