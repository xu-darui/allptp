<?php

namespace app\home\controller;
use app\home\model\Comment as CommentModel; 
use app\home\model\Leavemsg as LeavemsgModel; 
use app\home\model\Collection as CollectionModel;  
use app\home\model\Praise as PraiseModel; 
use app\home\model\Attention; 
use app\home\model\Report as ReportModel; 
use app\home\model\Dispute as DisputeModel; 
use think\Cache;
use think\Db;
use \think\Validate;

/**
 * 评论
 * Class Comment
 * @package app\store\controller
 */
class Comment extends Controller
{
	public function save_comment(){ 
		$userdata=$this->getuser();
		$data=input();
		$data['user_id']=$this->user_id;
		/* if(array_key_exists('image',$data)&&$data['image']){
		$data['image']=(json_decode(html_entity_decode($data['image']),true));
		} */
		/* $data['image']=[
			['image_id'=>10],
			['image_id'=>11],
			['image_id'=>12], 	
		]; */
		if($data['flag']==1){
			$user_id=Db::name('activity')->where(['activity_id'=>$data['table_id']])->value('user_id');
			if($user_id==$this->user_id){
				return $this->renderError('不能评论自己的体验活动');
			}
		}
		$comment_model=new CommentModel;
		if($comment_model->save_comment($data)){
			 return $this->renderSuccess('评论成功');
		}else{
			return $this->renderError('评论失败');
		}
		
		
	}
	
	public function del_comment($comment_id){
		$userdata=$this->getuser();
		$comment_model=new CommentModel;
		if($comment_model->del_comment($comment_id)){
			return $this->renderSuccess('删除评论成功');
		}else{
			return $this->renderError('删除评论失败');
		}
		
	}
	public function save_leavemsg(){ 
		$userdata=$this->getuser();
		$data=input();
		$data['user_id']=$this->user_id;
		$leavemsg_model=new LeavemsgModel;
		if($leavemsg_model->save_leavemsg($data)){
			return $this->renderSuccess('留言成功');
		}else{
			return $this->renderError('留言失败');
		}
	}
	
	public function del_leavemsg($msg_id){
		$userdata=$this->getuser();
		$leavemsg_model=new LeavemsgModel;
		if($leavemsg_model->del_leavemsg($msg_id)){
			return $this->renderSuccess('取消留言成功');
		}else{
			return $this->renderError('取消留言失败');
		}
		
	}
	
	

	public function collection(){
		$userdata=$this->getuser();
		$input=input(); 
		$collection_model=new CollectionModel;
		$data=$input;
		
		if($input['type']==1){
			$data=['flag'=>$input['flag'],'table_id'=>$input['table_id'],'group_id'=>$input['group_id'],'user_id'=>$userdata['user_id']];
			$collection_data=$collection_model->get_collection($data);
			if($collection_data['status']===0){
				return $this->renderError('不能重复收藏');
			}
			if($collection_model->add_collection($data)){
				return $this->renderSuccess('收藏成功');
			}else{
				return $this->renderError('收藏失败');
			}
		}
		if($input['type']==2){
			$data=['flag'=>$input['flag'],'table_id'=>$input['table_id'],'group_id'=>$input['group_id'],'user_id'=>$userdata['user_id']];
			if($collection_model->remove_collection($data)){
				return $this->renderSuccess('移除收藏');
			}else{
				return $this->renderError('移除失败');
			}
		}
	}
	
	public function  add_collegroup(){
		$userdata=$this->getuser();
		$data=input();
		$data['user_id']=$this->user_id;
		$collection_model=new CollectionModel;
		if($collection_model->add_collegroup($data)){
			return $this->renderSuccess('保存成功');
		}else{
			return $this->renderError('保存失败');
		}
		
	}
	
	public function del_collegroup($group_id){
		$userdata=$this->getuser();
		$collection_model=new CollectionModel;
		if($collection_model->del_collegroup($group_id)){
			return $this->renderSuccess('删除成功');
		}else{
			return $this->renderError('删除失败');
		}
	}
	
	public function praise(){
		$userdata=$this->getuser();
		$input=input(); 
		$praise_model=new PraiseModel;
		$data=['table_id'=>$input['table_id'],'flag'=>$input['flag'],'user_id'=>$this->user_id];
		if($input['type']==1){
			$praise_data=$praise_model->get_praise($data);
			if($praise_data['status']===0){
				return $this->renderError('不能重复点赞');
			}
			if($praise_model->add_praise($data)){
				return $this->renderSuccess('点赞成功');
			}else{
				return $this->renderError('点赞失败');
			}
		}
		if($input['type']==2){
			$praise_data=$praise_model->get_praise($data);
			if($praise_data['status']===1){
				return $this->renderError('不能重复取消');
			}
			if($praise_model->remove_praise($data)){
				return $this->renderSuccess('已经取消');
			}else{
				return $this->renderError('取消失败');
			}
		}
		
	}
	
	public function attention($att_user_id,$type){
		$userdata=$this->getuser();
		$attention_model=new Attention;
		$data=['att_user_id'=>$att_user_id,'user_id'=>$this->user_id];
		if($type==1){
			$attention_data=$attention_model->get_attention($data);
			if($attention_data['status']===0){
				return $this->renderError('不能重复关注');
			}
			if($attention_model->add_attention($data)){
				return $this->renderSuccess('关注成功');
			}else{
				return $this->renderError('关注失败');
			}
		}
		if($type==2){
			if($attention_model->remove_attention($data)){
				return $this->renderSuccess('取消关注');
			}else{
				return $this->renderError('取消失败');
			}
		}
		
	}
	
	public function save_report(){
		$userdata=$this->getuser();
		$data=input();
		$data['user_id']=$this->user_id;
		$report_model=new ReportModel;
		if($report_model->save_report($data)){
			return $this->renderSuccess('保存成功');
		}else{
			return $this->renderError('保存失败');
		}
	}	
	
	
	public function save_dispute(){
		$validate = new Validate([
			'activity_id'  => 'require',
			'order_id' => 'require', 
		],[
			'activity_id.require'=>'请选择产生纠纷体验',
			'order_id.require'=>'请选择订单编号',   
		]); 
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		$userdata=$this->getuser();
		$data=input();
		$data['user_id']=$this->user_id;
		$data['image']=[['image_id'=>1], ['image_id'=>2], ['image_id'=>3] ];
		$dispute_model=new DisputeModel;
		if($dispute_model->save_dispute($data)){
			return $this->renderSuccess('提交纠纷成功');
		}else{
			return $this->renderError('提交纠纷失败');
		}
	}

	/* public function cancel_report(){
		$userdata=$this->getuser();
		$data=input();
		$data['user_id']=$this->user_id;
		$report_model=new ReportModel;
		if($report_model->save_report($data)){
			return $this->renderSuccess('保存成功');
		}else{
			return $this->renderError('保存失败');
		}
	} */
	public function comment_list($flag,$table_id,$order=1,$page=1){
		switch($order){
			case 1:$orderby='a.create_time desc ,a.praise_num desc';break;
			case 2:$orderby='a.create_time asc ,a.praise_num desc';break;
			default:
		}
		$comment_model=new CommentModel;
		$comment_data=$comment_model->comment_list(['a.flag'=>$flag,'a.table_id'=>$table_id],$orderby,$page,Cache::get($this->token)['user']['user_id']);
		if($comment_data){
			return $this->renderSuccess($comment_data);
		}else{
			return $this->renderError('暂无评论');
		}
	}
	public function collegroup_list($flag=0,$table_id=0){
		$userdata=$this->getuser();
		$collection_model=new CollectionModel;
		$data=$collection_model->collegroup_list(['g.user_id'=>$userdata['user_id'],'g.status'=>0],$flag,$table_id);
		return $this->renderSuccess($data);
	}
	//获取我的粉丝列表
	public function my_att_list($user_id,$page=1){
		//$userdata=$this->getuser();
		$attention_model=new Attention;
		return $this->renderSuccess($attention_model->my_att_list($user_id,$page));
	}
	
	public function col_act_list($group_id,$page=1){
		$userdata=$this->getuser();
		$collection_model=new CollectionModel;
		return $this->renderSuccess($collection_model->col_act_list($userdata['user_id'],$group_id,$page));
	}
	
	public function praise_list($user_id,$page=1){
		//$userdata=$this->getuser();
		$praise_model=new PraiseModel;
		return $this->renderSuccess($praise_model->praise_list($user_id,$page));
	}
	
	
	public function att_other_list($user_id,$page){
		$attention_model=new Attention;
		return $this->renderSuccess($attention_model->att_other_list($user_id,$page));
	}

	public function leave_list($flag,$table_id,$order=1,$page=1){
		switch($order){
			case 1:$orderby='create_time desc ,praise_num desc';break;
			case 2:$orderby='create_time asc ,praise_num desc';break;
			default:
		}
		$leavemsg_model=new LeavemsgModel;
		switch($flag){
			case 1:
			case 2:
			case 3:
				$data=$leavemsg_model->leave_list(['flag'=>$flag,'table_id'=>$table_id],$orderby,$page,Cache::get($this->token)['user']['user_id']);
				break;
			case 4:
				$data=$leavemsg_model->comment_leave_list(['table_id'=>$table_id],$orderby,$page,Cache::get($this->token)['user']['user_id']);
				break;
			case 5:
				$data=$leavemsg_model->leave_leave_list($flag,$table_id,$orderby,$page,Cache::get($this->token)['user']['user_id']);
				break;
		}
		
		return $this->renderSuccess($data); 
	}
	
	/* public function my_comment_list(){
		$userdata=$this->getuser();
		$comment_model=new CommentModel;
		$comment_model->my_comment_list();
		 
	}  */
	
	public function comment_planner($user_id){
		
		$comment_model=new CommentModel;
		return $this->renderSuccess($comment_model->comment_planner($user_id));
	}
	/* public function he_comment($user_id){
		$comment_model=new CommentModel;
		return $this->renderSuccess($comment_model->he_comment_planner($user_id));
	} */
	
	public function comment_visiter($user_id){
		$comment_model=new CommentModel;
		return $this->renderSuccess($comment_model->comment_visiter($user_id));
	}
	public function comment_planner_save($content,$order_id){
		$userdata=$this->getuser();
		$comment_model=new CommentModel;
		$order=Db::name('order')->where(['order_id'=>$order_id])->find();
		$data=['flag'=>3,'table_id'=>$order['user_id'],'content'=>$content,'order_id'=>$order_id,'user_id'=>$userdata['user_id']];
		if($comment_model->save_comment($data)){
			 Db::name('order')->where(['order_id'=>$order_id])->update(['isevaluate_planner'=>2]);
			 return $this->renderSuccess('评论成功');
		}else{
			return $this->renderError('评论失败');
		}
	}
	 
}