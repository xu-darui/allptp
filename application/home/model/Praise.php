<?php

namespace app\home\model;  
use app\common\model\Praise as PraiseModel; 
use think\Db;
/**
 * 点赞
 * Class Praise
 * @package app\store\model
 */
class Praise extends PraiseModel
{
	
	public function get_praise($where){
		return $this->where($where)->find();
		
	}
	public function add_praise($data){
		Db::startTrans();
		try{ 
			
			$praise_data=$this->get_praise($data); 
			if($praise_data['status']===1){
				Db::name('praise')->where(['praise_id'=>$praise_data['praise_id']])->update(['create_time'=>time(),'status'=>0,'unpra_time'=>0]);
			}else{
				$data['create_time']=time();
				Db::name('praise')->insert($data);		
			} 
			switch($data['flag']){ 
				case 1:
					Db::name('story')->where(['story_id'=>$data['table_id']])->setInc('praise_num');
					//$story_model=new StoryModel;
					//$story_model->where(['story_id'=>$data['table_id']])->setInc('praise_num');
					break;
				case 2:
					//翻译
					Db::name('translate')->where(['translate_id'=>$data['table_id']])->setInc('praise_num');
					break;
				case 3: 
					Db::name('comment')->where(['comment_id'=>$data['table_id']])->setInc('praise_num');
					break;
				case 4: 
					Db::name('leavemsg')->where(['msg_id'=>$data['table_id']])->setInc('praise_num');
					break;
				case 5: 
					Db::name('forward')->where(['forward_id'=>$data['table_id']])->setInc('praise_num');
					break;
			}
			
		// 提交事务
			Db::commit(); 
			return true;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}
		
	}
	public function remove_praise($where){  
		Db::startTrans();
		try{
			Db::name('praise')->where($where)->update(['status'=>1,'unpra_time'=>time()]);  
			switch($where['flag']){
				case 1: 
					Db::name('story')->where(['story_id'=>$where['table_id']])->setDec('praise_num');
					break;
				case 2:
					//翻译
					Db::name('translate')->where(['translate_id'=>$where['table_id']])->setDec('praise_num'); 
					break;
				case 3: 
					Db::name('comment')->where(['comment_id'=>$where['table_id']])->setDec('praise_num');
					break;
				case 4: 
					Db::name('leavemsg')->where(['msg_id'=>$where['table_id']])->setDec('praise_num');
					break;
				case 5: 
					Db::name('forward')->where(['forward_id'=>$where['table_id']])->setDec('praise_num');
					break;
			}
		// 提交事务
			Db::commit(); 
			return true;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}
		
		
	}
	
	public function prase_user($story_id,$user_id){
		return Db::name('praise')
			->alias('p')
			->field('p.user_id,u.family_name,u.middle_name,u.name,i.domain,i.image_url,i.themb_url,if(a.attention_id>0,1,0) as is_attention')
			->join('user u','p.user_id=u.user_id')
			->join('image i','u.head_image=i.image_id','LEFT')
			->join('attention a',"a.att_user_id=u.user_id and a.status=0 and a.user_id=$user_id",'LEFT')
			->where(['p.flag'=>1,'p.table_id'=>$story_id,'p.status'=>0])
			->select();
	}
	
	public function praise_list($user_id,$page){ 
		return Db::view('ptp_praise_story_com',"praise_id,flag,create_time,user_id,content,title,praise_num") 
		->view('ptp_user','family_name,middle_name,name','ptp_praise_story_com.self_user_id= ptp_user.user_id')
		->view('ptp_image','domain,image_url,themb_url','ptp_user.head_image = ptp_image.image_id')
		->where(['ptp_praise_story_com.self_user_id'=>$user_id])
		->paginate(10, false, ['query' => ["page"=>$page]]);  
		
	}
	
	
}