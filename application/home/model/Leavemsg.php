<?php

namespace app\home\model;  
use app\common\model\Leavemsg as LeavemsgModel;
use app\common\model\Comment as CommentModel;
use app\common\model\Activity as ActivityModel;
use app\common\model\Story as StoryModel;
use app\common\model\User as UserModel;
use app\common\model\Forward as ForwardModel;
use think\Db;
/**
 * 留言
 * Class Leavemsg
 * @package app\store\model
 */
class Leavemsg extends LeavemsgModel
{
	public function save_leavemsg($data){
		$table_id=$data['table_id'];		
		Db::startTrans();
		try{ 
				switch($data['flag']){ 
					case 2:
					$data['leav_story_id']=$data['table_id'];
					break;
					case 3:
					$data['leav_user_id']=$data['table_id'];
					break;
					case 4:
					$top_data=CommentModel::get($data['table_id']); 
					
					$data['path']=$data['table_id'];
					$data['top_id']=$data['table_id'];
					$data['table_id']=$data['table_id']; 
					$data['flag']=$data['flag'];  
					if($top_data['flag']==1){
						//活动
						$data['leav_activity_id']=$top_data['table_id']; 
					}
					if($top_data['flag']==2){
						$data['leav_user_id']=$top_data['table_id']; 
					}  
					$data['top_user_id']=$top_data['user_id'];   
					break;
					case 5:
					$top_data=LeavemsgModel::get($data['table_id']); 
					$data['path']=$top_data['path']==''?$data['table_id']:$top_data['path'].','.$data['table_id'];
					$data['top_id']=$data['table_id'];
					$data['table_id']=$top_data['table_id'];  
					$data['flag']=$data['flag']; 
					$data['leav_activity_id']=$top_data['leav_activity_id'];
					$data['leav_story_id']=$top_data['leav_story_id'];
					$data['leav_user_id']=$top_data['leav_user_id']; 
					$data['top_user_id']=$top_data['user_id'];
					$data['leav_forward_id']=$top_data['leav_forward_id'];
					break; 
					case 6:
					$data['leav_forward_id']=$data['table_id'];
					break;
					
				} 
				/* if(array_key_exists('top_id',$data)&&$data['top_id']){
					if($data['flag']==4){
						$top_data=CommentModel::get($data['top_id']); 
						if($top_data['flag']==1){
							$data['leav_activity_id']=$top_data['table_id'];
						}else{
							$data['leav_user_id']=$top_data['table_id'];
						}
						$data['path']=$data['top_id'];
						$data['top_user_id']=$top_data['user_id'];
						
					}else{
						$top_data=LeavemsgModel::get($data['top_id']); 
						$data['leav_activity_id']=$top_data['leav_activity_id'];
						$data['leav_story_id']=$top_data['leav_story_id'];
						$data['leav_user_id']=$top_data['leav_user_id'];
						$data['path']=$top_data['path']==''?$data['top_id']:$top_data['path'].','.$data['top_id'];
						$data['top_user_id']=$top_data['user_id'];
					}
					
					 
				} */ 
				$this->allowField(true)->save($data);   
				switch($data['flag']){ 
					case 2:
					StoryModel::where(['story_id'=>$table_id])->setInc('leaving_num');
					break;
					case 3:
					UserModel::where(['user_id'=>$table_id])->setInc('leaving_num');
					break;
					case 4:
					CommentModel::where(['comment_id'=>$table_id])->setInc('leaving_num');
					break;
					case 5:
					LeavemsgModel::where(['msg_id'=>$table_id])->setInc('leaving_num');
					break;
					case 6:
					ForwardModel::where(['forward_id'=>$table_id])->setInc('leaving_num');
					break; 
				} 				 
				 // 提交事务
				Db::commit(); 
				return $this->msg_id;
			}catch (\Exception $e) {
				// 回滚事务
				Db::rollback();
				
				} 
	}
	
	public function del_leavemsg($msg_id){
		
		Db::startTrans();
		try{
			$leave_data=LeavemsgModel::get($msg_id);//LeavemsgModel::where(['msg_id'=>$msg_id])->whereor(['path'=>['like',[$leave_data['path'].'/'.$msg_id.'%']]])->update(['status'=>1]); 
			LeavemsgModel::where(['msg_id'=>$msg_id])->whereor("FIND_IN_SET($msg_id,path)")->update(['status'=>1]);  
			switch($leave_data['flag']){ 
					case 2:
					StoryModel::where(['story_id'=>$leave_data['table_id']])->setDec('leaving_num');
					break;
					case 3:
					UserModel::where(['user_id'=>$leave_data['table_id']])->setDec('leaving_num');
					break;
					case 4:
					CommentModel::where(['comment_id'=>$leave_data['table_id']])->setDec('leaving_num');
					break;
					case 5:
					LeavemsgModel::where(['msg_id'=>$leave_data['table_id']])->setDec('leaving_num');
					break;
					case 6:
					ForwardModel::where(['forward_id'=>$leave_data['table_id']])->setDec('leaving_num');
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
	
	/* public function replay_list($user_id,$page){
		return Db::name('leavemsg')
		->alias('a')
		->field("a.*,CONCAT_WS('',g.family_name,g.middle_name,g.name) as user_name,CONCAT_WS('',i.family_name,i.middle_name,i.name) as top_user_name,h.domain,h.image_url,h.themb_url,b.content as top_content,c.title as activity_title,e.title as story_title,CONCAT_WS('',f.family_name,f.middle_name,f.name) as user_title")
		->join('user i','a.top_user_id=i.user_id','LEFT')
		->join('user g','a.user_id=g.user_id','LEFT')
		->join('image h','g.head_image=h.image_id','LEFT')
		->join('leavemsg b','a.top_id=b.msg_id','LEFT')
		->join('activity c','a.leav_activity_id=c.activity_id','LEFT')
		->join('story e','a.leav_story_id=e.story_id','LEFT')
		->join('user f','a.leav_user_id=f.user_id','LEFT')
		->order('a.create_time desc')
		->where(['a.flag'=>5,'a.top_user_id'=>$user_id,'a.status'=>0])
		->paginate(10, false, ['query' => ["page"=>$page]]);; 
	}
	 */
	public function leave_list($where,$orderby,$page,$user_id){
		$where['status']=0;
		$where_leave['status']=0; 
		if($where['flag']==2){
			$where_leave['leav_story_id']=['gt',0];
		}else if($where['flag']==3){
			$where_leave['leav_user_id']=['gt',0];
		}
		$data= $this->field("msg_id,flag,content,user_id,top_user_id,praise_num,leaving_num,create_time")
			->with(['user'=>function($query){$query->with(['headimage'])->field('user_id,family_name,middle_name,name,head_image');},'topuser'=>function($query){$query->with(['headimage'])->field('user_id,family_name,middle_name,name,head_image');},'praise'=>function($query)use($user_id){$query->where(['user_id'=>$user_id,'status'=>0]);},'report'=>function($query)use($user_id){$query->where(['user_id'=>$user_id,'status'=>0]);}])
			->where($where)
			->order($orderby)
			->paginate(10, false, ['query' => ["page"=>$page]]);
		foreach($data as $key=>$value){
			$msg_id=$data[$key]['msg_id'];
			$data[$key]['leavemsg']=$this->field('msg_id,content,praise_num,leaving_num,flag,table_id,user_id,top_user_id')->with(['user'=>function($query){$query->field('user_id,family_name,middle_name,name');},'topuser'=>function($query){$query->field('user_id,family_name,middle_name,name');}])->where(['flag'=>5])->where("FIND_IN_SET($msg_id,path)")->order('create_time desc')->limit(5)->where($where_leave)->select();
			$data[$key]['is_praise']=0;
			$data[$key]['is_report']=0;
			if($value['praise']){
				$data[$key]['is_praise']=1;
			}
			if($value['report']){
				$data[$key]['is_report']=1;
			}
			unset($data[$key]['praise']);
			unset($data[$key]['report']);
		}	
		return $data;	
	}
	
	public function comment_leave_list($where,$orderby,$page,$user_id){
		$where['status']=0;
		$where['flag']=5;
		$data=$this	->field("msg_id,flag,content,user_id,top_user_id,praise_num,leaving_num,create_time")
					->with(['user'=>function($query){$query->with(['headimage'])->field('user_id,family_name,middle_name,name,head_image');},'topuser'=>function($query){$query->with(['headimage'])->field('user_id,family_name,middle_name,name,head_image');},'praise'=>function($query)use($user_id){$query->where(['user_id'=>$user_id,'status'=>0]);},'report'=>function($query)use($user_id){$query->where(['user_id'=>$user_id,'status'=>0]);}]) 
					->where($where)
					->order($orderby)
					->paginate(10, false, ['query' => ["page"=>$page]]);
		foreach($data as $key=>$value){
			$data[$key]['is_praise']=0;
			$data[$key]['is_report']=0;
			if($value['praise']){
				$data[$key]['is_praise']=1;
			}
			if($value['report']){
				$data[$key]['is_report']=1;
			}
			unset($data[$key]['praise']);
			unset($data[$key]['report']);
		}	
		return $data;
	}
	
	public function  leave_leave_list($flag,$table_id,$orderby,$page,$user_id){ 
		$where['status']=0;  
		$where['flag']=$flag;  
		$data=$this	->field("msg_id,flag,content,user_id,top_user_id,praise_num,leaving_num,create_time")
					->with(['user'=>function($query){$query->with(['headimage'])->field('user_id,family_name,middle_name,name,head_image');},'topuser'=>function($query){$query->with(['headimage'])->field('user_id,family_name,middle_name,name,head_image');},'praise'=>function($query)use($user_id){$query->where(['user_id'=>$user_id,'status'=>0]);},'report'=>function($query)use($user_id){$query->where(['user_id'=>$user_id,'status'=>0]);}]) 
					->where($where)
					->where("find_in_set($table_id,path)")
					->order($orderby)
					->paginate(10, false, ['query' => ["page"=>$page]]);
		foreach($data as $key=>$value){
			$data[$key]['is_praise']=0;
			$data[$key]['is_report']=0;
			if($value['praise']){
				$data[$key]['is_praise']=1;
			}
			if($value['report']){
				$data[$key]['is_report']=1;
			}
			unset($data[$key]['praise']);
			unset($data[$key]['report']);
		}	
		return $data;
	}
}