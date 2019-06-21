<?php

namespace app\home\model;  
use app\common\model\Report as ReportModel; 
use think\Db;
/**
 * 举报
 * Class Report
 * @package app\store\model
 */
class Report extends ReportModel
{
	public function save_report($data){
		switch($data['flag']){
			case 1:
				$activity_data=Db::name('activity')->where(['activity_id'=>$data['table_id']])->find();	
				$data['re_user_id']=$activity_data['user_id'];
				break;
			case 2:
				$comment_data=Db::name('comment')->where(['comment_id'=>$data['table_id']])->find();
				$data['re_user_id']=$comment_data['user_id'];
				break;
			case 3:
				$story_data=Db::name('story')->where(['story_id'=>$data['table_id']])->find();
				$data['re_user_id']=$story_data['user_id'];
				break;
			case 4:
				$story_data=Db::name('leavemsg')->where(['msg_id'=>$data['table_id']])->find();
				$data['re_user_id']=$story_data['user_id'];
				break;	
			case 5:
				$translate_data=Db::name('translate')->where(['translate_id'=>$data['table_id']])->find();
				$data['re_user_id']=$translate_data['user_id'];
				break;
		}
		return $this->allowField(true)->save($data);
		
	}
}