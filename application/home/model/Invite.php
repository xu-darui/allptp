<?php

namespace app\home\model;
use app\common\model\Invite as InviteModel;  
use app\common\model\Kind as KindModel;  
use think\Db;
/**
 * 邀请志愿者
 * Class Invite
 * @package app\store\model
 */
class Invite extends InviteModel
{
	public function save_invite($data){
		if(array_key_exists('slot_id',$data)){
			if(!$data['activity_id']=Db::name('activity_slot')->where(['slot_id'=>['in',$data['slot_id']]])->value('activity_id')){
				return false;
			}
		}
		
		if(array_key_exists('invite_id',$data)&&$data['invite_id']>0){
			return $this->allowField(true)->save($data,['invite_id'=>$data['invite_id']]);
		}else{
			return $this->allowField(true)->save($data);
		}
	}	
	
	public function my_invite_list($where){ 
		$data= Db::name('invite')
		->alias('a')
		->field("a.invite_id,a.activity_id,a.slot_id,FROM_UNIXTIME(a.create_time, '%Y-%c-%d' ) as create_time,a.status,a.audit,c.family_name,c.middle_name,c.name,c.six,c.language,c.introduce,c.country,c.score,d.domain,d.themb_url,d.image_url") 
		->join('user c','a.user_id=c.user_id','LEFT')
		->join('image d','c.head_image=d.image_id','LEFT')
		->where($where)
		->order('a.create_time desc')
		->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]]);
		foreach($data as $key=>$value){
			$slot_id=explode(',',$value['slot_id']);
			$value['slot']=Db::name('activity_slot')->field("slot_id,FROM_UNIXTIME(begin_time, '%Y-%c-%d %h:%i' ) as begin_time,FROM_UNIXTIME(end_time, '%Y-%c-%d %h:%i' ) as end_time")->where(['slot_id'=>['in',$slot_id]])->order('slot_id desc')->select();
			$data[$key]=$value;
		}
		return $data;
	}
	
	public function invite_list($where){
		$invite_model=new InviteModel;
		$data=$invite_model->with(['invuser.head_image','activity'=>function($query){$query->with('cover')->field('activity_id,title,introduce,descripte,score,comment_num,kind_id,cover_image');}])->where($where)->order('create_time desc')->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]]);  
		foreach($data as $key=>$value){ 
			$slot_id=explode(',',$value['slot_id']); 
			$value['slot']=Db::name('activity_slot')->field("slot_id,price,FROM_UNIXTIME(begin_time, '%Y-%c-%d %h:%i' ) as begin_time,FROM_UNIXTIME(end_time, '%Y-%c-%d %h:%i' ) as end_time")->where(['slot_id'=>['in',$slot_id]])->order('slot_id desc')->select()->toArray();
			
			$value['price']=$value['slot'][0]['price'];
			$value['title']=$value['activity']['title'];
			$value['introduce']=$value['activity']['introduce'];
			$value['descripte']=$value['activity']['descripte'];
			$value['score']=$value['activity']['score'];
			$value['comment_num']=$value['activity']['comment_num'];
			$value['kind_id']=$value['activity']['kind_id'];
			$value['cover']=$value['activity']['cover'];
			unset($value['activity']);
			$data[$key]=$value; 
		}
		if($data){
			$kind_model=new KindModel;
			$data=$kind_model->addkind($data); 
		} 
		
		return $data;
	}

}