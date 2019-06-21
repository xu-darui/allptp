<?php

namespace app\home\model; 
use app\common\model\Comment as CommentModel;
use app\common\model\Leavemsg as LeavemsgModel;
use app\common\model\Activity as ActivityModel;
use app\common\model\User as UserModel;
use app\common\model\Order as OrderModel;
use app\home\model\Image as ImageModel;
use think\Db;
/**
 * 评论
 * Class Comment
 * @package app\store\model
 */
class Comment extends CommentModel
{
	public function save_comment($data){
		Db::startTrans();
		try{  
			if(array_key_exists('order_id',$data)&&$data['order_id']){
				OrderModel::update(['isevaluate'=>2],['order_id'=>$data['order_id']]);
			 } 
			 $this->allowField(true)->save($data);
			  //累计
			 if($data['flag']==1){
				 $activity_model=new ActivityModel;
				 $activity_model->where(['activity_id'=>$data['table_id']])->setInc('comment_num');
			 }else if($data['flag']==2||$data['flag']==3){
				 $user_model=new UserModel;
				 $user_model->where(['user_id'=>$data['table_id']])->setInc('comment_num');
			 } 
			 
			 $comment_id= $this->comment_id; 
		 
		if(array_key_exists('image',$data)&&$data['image']){ 
			$image_model=new ImageModel;
			$image_model->save_image($data['image'],$comment_id,3);
			
		}
		$this->culucate_score($data); 
		// 提交事务
			Db::commit(); 
			return $comment_id;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}
		
	}
	public function del_comment($comment_id){
			// 启动事务
		Db::startTrans();
		try{
			CommentModel::where(['comment_id'=>$comment_id])->update(['status'=>1]); 
			LeavemsgModel::where(['table_id'=>$comment_id,'flag'=>4])->update(['status'=>1]);
			$data=CommentModel::where(['comment_id'=>$comment_id])->find(); 
			if($data['flag']==1){
				 $activity_model=new ActivityModel;
				 $activity_model->where(['activity_id'=>$data['table_id']])->setDes('comment_num');
			}else if($data['flag']==2||$data['flag']==3){
				 $user_model=new UserModel;
				 $user_model->where(['user_id'=>$data['table_id']])->setDes('comment_num');
			} 
			$this->culucate_score($data);
			// 提交事务
			Db::commit(); 
			return true;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}	
		
		
	}
	public function culucate_score($data){
		$score=CommentModel::where(['flag'=>$data['flag'],'table_id'=>$data['table_id'],'status'=>0])->avg('score');
		switch($data['flag']){
			case 1: 
				Db::name('activity')->where(['activity_id'=>$data['table_id']])->update(['score'=>$score]);
				break;
			case 2:
				Db::name('user')->where(['user_id'=>$data['table_id']])->update(['score'=>$score]);
				break;
		}
		
	}
	public function comment_list($where,$orderby,$page,$user_id){ 
		$comment_model=new CommentModel;
		$where_leave['status']=0; 
		if($where['a.flag']==1){
			$where_leave['leav_activity_id']=['gt',0];
		}else if($where['a.flag']==2){
			$where_leave['leav_user_id']=['gt',0];
		}
		$where['a.status']=0;
		$data= $comment_model->alias('a')
			->with(['image'=>function($query){$query->where(['flag'=>3]);},'user'=>function($query){$query->with('headimage')->field('user_id,head_image,family_name,middle_name,name');},'praise'=>function($query)use($user_id){$query->where(['user_id'=>$user_id,'status'=>0]);},'report'=>function($query)use($user_id){$query->where(['user_id'=>$user_id,'status'=>0]);}])
			->where($where)
			->order($orderby)
			->paginate(10, false, ['query' => ["page"=>input('page')]]);
		$leavemsg_model=new LeavemsgModel;
			foreach($data as $key=>$value){  
				$data[$key]['leavemsg']=$leavemsg_model->field('msg_id,content,praise_num,leaving_num,flag,table_id,user_id,top_user_id')->with(['user'=>function($query){$query->field('user_id,family_name,middle_name,name');},'topuser'=>function($query){$query->field('user_id,family_name,middle_name,name');}])->where(['flag'=>['in',[4,5]],'table_id'=>$value['comment_id']])->where($where_leave)->order('create_time desc')->limit(5)->select();
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
	
	public function mysay($user_id,$page){ 
		/* return Db::view('say_com_leav')
		->field('a.*,c.domain,c.image_url,c.thumb_url')
		->alias('a')
		->join('user b','a.user_id=b.user_id')
		->join('image c','b.head_image=b.image_id')
		->where(['a.user_id'=>$user_id])
		->paginate(10, false, ['query' => ["page"=>$page]]); */
		 $data= Db::view('ptp_say_com_leav','*') 
		->view('ptp_user','family_name,middle_name,name','ptp_say_com_leav.user_id=ptp_user.user_id','LEFT')
		->view('ptp_image','domain,image_url,themb_url','ptp_user.head_image=ptp_image.image_id','LEFT') 
		->where(['ptp_say_com_leav.user_id'=>$user_id])
		->order('ptp_say_com_leav.create_time desc')
		->paginate(10, false, ['query' => ["page"=>$page]]);  
		foreach($data as $key=>$value){
			if($value['flag']==1||$value['flag']==2){
				//如果是评论可能有图片
				$value['image']=Db::name('image')->where(['flag'=>3,'table_id'=>$value['say_id']])->order('sort desc')->select();
			}else{
				$value['image']=[];
			}
			$data[$key]=$value;
			
		}
		return $data;
	}
	
	public function replay_list($user_id,$page){
		 $data= Db::view('ptp_say_com_leav','*') 
		->view('ptp_user','family_name,middle_name,name','ptp_say_com_leav.user_id=ptp_user.user_id','LEFT')
		->view('ptp_image','domain,image_url,themb_url','ptp_user.head_image=ptp_image.image_id','LEFT')
		->view('ptp_leavemsg','content as content_top','ptp_say_com_leav.top_id=ptp_leavemsg.msg_id','LEFT')
		->where(['ptp_say_com_leav.link_user_id'=>$user_id])
		->order('ptp_say_com_leav.create_time desc')
		->paginate(10, false, ['query' => ["page"=>$page]]);  
		foreach($data as $key=>$value){
			if($value['flag']==1||$value['flag']==2){
				//如果是评论可能有图片
				$value['image']=Db::name('image')->where(['flag'=>3,'table_id'=>$value['say_id']])->order('sort desc')->select();
			}else{
				$value['image']=[];
			}
			
			if($value['flag']==8){
				//转发
				$value['forward']=Db::name('forward')->alias('a')->field("a.content,a.flag,a.table_id,if(a.flag=1,b.title,c.title) as title")->join('activity b','a.table_id=b.activity_id','LEFT')->join('story c','a.table_id=c.story_id','LEFT')->where(['forward_id'=>$value['table_id']])->find();
			}
			$data[$key]=$value;
			
		}
		return $data;
	}
	
	/* public function my_comment_list($user_id){ 
	} */
	
	public function comment_planner($user_id){ 
		$data=Db::view('ptp_comment_view','*')  
		->where(['ptp_comment_view.table_id'=>$user_id,'ptp_comment_view.flag'=>3])
		->order('ptp_comment_view.create_time desc')
		->paginate(10, false, ['query' => ["page"=>input('page')]]); 
		$leavemsg_model=new LeavemsgModel;
		foreach($data as $key=>$value){
			$where_leave['status']=0; 
			$value['long_ago']=long_ago($value['create_time'],1);
			//评论活动或者志愿者
			$value['leavemsg']=$leavemsg_model->field('msg_id,content,praise_num,leaving_num,flag,table_id,user_id,top_user_id')->with(['user'=>function($query){$query->field('user_id,family_name,middle_name,name');},'topuser'=>function($query){$query->field('user_id,family_name,middle_name,name');}])->where(['flag'=>['in',[4,5]],'table_id'=>$value['comment_id']])->where($where_leave)->order('create_time desc')->limit(5)->select();  
				//如果是评论可能有图片
			$value['image']=Db::name('image')->where(['flag'=>3,'table_id'=>$value['comment_id']])->order('sort desc')->select();
			$data[$key]=$value;
			
		} ;
		return $data;
		
	}
	
	/* public function comment_planner($user_id,$flag,$my_user_id){ 
		$where['ptp_com_act_volueer.create_user_id']=$user_id;
		if($flag) $where['ptp_com_act_volueer.flag']=$flag; 
		$data=Db::view('ptp_com_act_volueer')
		->view('ptp_user','family_name,middle_name,name,region','ptp_com_act_volueer.user_id=ptp_user.user_id')
		->view('ptp_praise','praise_id',"ptp_praise.table_id=ptp_com_act_volueer.comment_id and ptp_praise.status=0 and ptp_praise.flag=3 and ptp_praise.user_id=$my_user_id",'LEFT')
		->view('ptp_report','report_id',"ptp_report.table_id=ptp_com_act_volueer.comment_id and ptp_report.status=0 and ptp_report.flag=2 and ptp_report.user_id=$my_user_id",'LEFT')
		->view('ptp_image','domain,image_url,themb_url','ptp_user.head_image=ptp_image.image_id') 
		->where($where)
		->order('ptp_com_act_volueer.create_time desc')
		->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]]); 
		//pre(Db::view('ptp_com_act_volueer')->getlastsql());
		$leavemsg_model=new LeavemsgModel;
		
		foreach($data as $key=>$value){ 
			$value['create_time']=date("Y-m-d H:i",$value['create_time']);
			$value['long_ago']=long_ago($value['create_time']);
			$value['leavemsg']=$leavemsg_model->field('msg_id,content,praise_num,leaving_num,flag,table_id,user_id,top_user_id')->with(['user'=>function($query){$query->field('user_id,family_name,middle_name,name');},'topuser'=>function($query){$query->field('user_id,family_name,middle_name,name');}])->where(['flag'=>['in',[4,5]],'table_id'=>$value['comment_id']])->order('create_time desc')->limit(5)->select();
			$value['is_praise']=0;
			$value['is_report']=0;
			if($value['praise_id']) $value['is_praise']=1;
			if($value['report_id']) $value['is_report']=1;
			unset($value['praise_id']);
			unset($value['report_id']);
			$data[$key]=$value;
		}
		return $data;
	} */
	
/* 	public function he_comment_planner($user_id){
		
	} */
	
	public function comment_visiter($user_id){
		$data=Db::view('ptp_comment_view','*')  
		->where(['ptp_comment_view.link_user_id'=>$user_id,'ptp_comment_view.flag'=>['in',[1,2]]])
		->order('ptp_comment_view.create_time desc')
		->paginate(10, false, ['query' => ["page"=>input('page')]]); 
		$leavemsg_model=new LeavemsgModel;
		foreach($data as $key=>$value){
			$where_leave['status']=0; 
			$value['long_ago']=long_ago($value['create_time'],1);
			//评论活动或者志愿者
			$value['leavemsg']=$leavemsg_model->field('msg_id,content,praise_num,leaving_num,flag,table_id,user_id,top_user_id')->with(['user'=>function($query){$query->field('user_id,family_name,middle_name,name');},'topuser'=>function($query){$query->field('user_id,family_name,middle_name,name');}])->where(['flag'=>['in',[4,5]],'table_id'=>$value['comment_id']])->where($where_leave)->order('create_time desc')->limit(5)->select();  
				//如果是评论可能有图片
			$value['image']=Db::name('image')->where(['flag'=>3,'table_id'=>$value['comment_id']])->order('sort desc')->select();
			$data[$key]=$value;
			
		} ;
		return $data;
	}
}