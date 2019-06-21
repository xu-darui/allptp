<?php

namespace app\home\model;

use app\common\model\FriendNotice as FriendNoticeModel; 
use app\common\model\UserFriend as UserFriendModel; 
use think\DB;

/**
 * 好友
 * Class FriendNotice
 * @package app\home\model
 */
class FriendNotice extends FriendNoticeModel
{
	public function save_notice($data){
		$notice_count=FriendNoticeModel::where(['f_user_id'=>$data['f_user_id'],'user_id'=>$data['user_id'],'status'=>['in',[0,1]]])->count();
		$frend_count=UserFriendModel::where(['f_user_id'=>$data['f_user_id'],'user_id'=>$data['user_id'],'status'=>0])->count();
		if(!$notice_count||($notice_count&&!$frend_count)){
			$this->allowField(true)->save($data);
		} 
		return true;
	}
	
	public function getlist($user_id,$page){
		$notice_model=new FriendNoticeModel;
		$data=$notice_model->alias('a')->with(['fuser'=>function($query){$query->with(['headimage'])->field('user_id,family_name,middle_name,name,head_image');}])->where(['a.f_user_id'=>$user_id,'status'=>['in',[0,1,2]]])->order('notice_id desc')->paginate(10, false, ['query' => ["page"=>$page]])->toArray(); 
		$notice_id = array_column($data['data'],'notice_id');
		$notice_model->where(['notice_id'=>['in',$notice_id]])->update(['isread'=>1]);
		return $data;
	}
	public function agree($notice_id,$status){
		// 启动事务
		Db::startTrans();
		try{
			$notice=FriendNoticeModel::get($notice_id);
			if($notice['status']!==0){
				return false;
			}
			if($status==1){ 
				$friend_model=new UserFriendModel; 
				$friend_model->allowField(true)->saveAll([['user_id'=>$notice['user_id'],'f_user_id'=>$notice['f_user_id']],['f_user_id'=>$notice['user_id'],'user_id'=>$notice['f_user_id']]]);
			}
			$this->allowField(true)->save(['status'=>$status],['notice_id'=>$notice_id]);
			// 提交事务
			Db::commit(); 
			return true;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
			return false;
		}		
		
	}
	public function noread_count($user_id){
		return $this->where(['f_user_id'=>$user_id,'isread'=>0])->count();
		
	}
	
}