<?php

namespace app\home\controller; 
use app\home\model\User as UserModel; 
use app\home\model\Activity as ActivityModel; 
use app\home\model\Question as QuestionModel; 
use app\home\model\Visit as VisitModel; 
use app\home\model\Enroll as EnrollModel; 
use app\home\model\Order as OrderModel; 
use app\home\model\ActivitySlot; 
use app\home\model\ActivityHouse; 

use \think\Validate;
use think\Cache;
use think\Db;
/**
 * 活动模块
 * Class Activity
 * @package app\home\controller
 */
class Activity extends Controller
{
	public function save_activity(){
		$userdata=$this->getuser();
		$data=input(); 
		if(!array_key_exists('activity_id',$data)){
			$config=$this->config();
			if(intval($userdata['credit_score'])<intval($config['credit']['create_act_score'])){
				return $this->renderError('您的信誉积分太低,不可创建体验');  
			}
		}
		/* $data['question'] =[
			['answer_id'=>1,'question_id'=>1,'option_id'=>1,'other'=>'test'],
			['question_id'=>4,'option_id'=>9,'other'=>'test'],
		
		];  */ 
		/* $data['slot'] =[
			['begin_date'=>'2018-12-24','end_date'=>'2018-12-27','begin_time'=>'10:10','end_time'=>'12:20','price'=>'234','max_person_num'=>1],
			['begin_date'=>'2018-12-24','end_date'=>'2018-12-27','begin_time'=>'10:20','end_time'=>'12:50','price'=>'1111','max_person_num'=>2],
			['begin_date'=>'2018-12-24','end_date'=>'2018-12-27','begin_time'=>'10:50','end_time'=>'13:20','price'=>'1111','max_person_num'=>3],
			
		]; */
		
		/* $data['slot'] =[
			[
				'day'=>'2018-12-24','list'=>[
					['personNum'=>23,'price'=>412,'time'=>['10:10','12:20']],
					['personNum'=>33,'price'=>413,'time'=>['10:20','12:30']],
					['personNum'=>57,'price'=>415,'time'=>['10:30','12:40']],
				]
			], 
			[
				'day'=>'2018-12-25','list'=>[
					['personNum'=>2,'price'=>412,'time'=>['10:10','12:20']],
					['personNum'=>3,'price'=>413,'time'=>['10:20','12:30']],
					['personNum'=>4,'price'=>415,'time'=>['10:30','12:40']],
				]
			], 
		]; */
		/* $data['image']=[
			['image_id'=>10],
			['image_id'=>11],
			['image_id'=>12], 	
		]; */
		

			/*if(array_key_exists('question',$data)){
			$data['question']=(json_decode(html_entity_decode($data['question']),true)); 
		}
	 if(array_key_exists('slot',$data)){ 
			$data['slot']=(json_decode(html_entity_decode($data['slot']),true));
		}*/
		/* if(array_key_exists('image',$data)){ 
			$data['image']=(json_decode(html_entity_decode($data['image']),true));
			
		} */
		/*if(array_key_exists('place',$data)){ 
			$data['place']=(json_decode(html_entity_decode($data['place']),true));
		} */
		/* $data['house'] =[
			['house_id'=>1,'num'=>'1','max_person'=>'2','price'=>'11','descript'=>'舒适','flag'=>'1','image'=>[['image_id'=>10],['image_id'=>11],['image_id'=>12]]],
			['num'=>'2','max_person'=>'4','price'=>'11','descript'=>'巴士','flag'=>'2'],
			['num'=>'3','max_person'=>'6','price'=>'11','descript'=>'舒适','flag'=>'3']
		];   */
		//pre($data['image']);
		//pre($data);
		//pre($data);
		//return $this->renderSuccess('已进入');  
		$data['user_id']=$userdata['user_id'];
		$activity_model=new ActivityModel;
		if($activity_id=$activity_model->save_activity($data)){
			return $this->renderSuccess($activity_id);  
		}else{
			return $this->renderError('保存失败');  
		}
		 
	}
	public function activity_edit($activity_id){ 
		$activity_model=new ActivityModel;
		if($data=$activity_model->activity_edit(['a.activity_id'=>$activity_id])){
			return $this->renderSuccess($data);
		}else{
			return $this->renderError('暂无数据');
		}
	}
	
	public function get_activity($activity_id,$visit=0,$translate_id=0,$language=0){
		
		if($visit){
			$visit_model=new VisitModel;	
			$visit_model->add(['user_id'=>$this->user_id,'flag'=>1,'table_id'=>$activity_id]);	 
		}
		$activity_model=new ActivityModel;
		if($data=$activity_model->get_activity(['a.activity_id'=>$activity_id],Cache::get($this->token)['user']['user_id'],$translate_id,$language)){
			return $this->renderSuccess($data);
		}else{
			return $this->renderError('暂无数据');
		}
	}
	
	public function question($flag,$activity_id=0){
		$question_model=new QuestionModel;
		if($question=$question_model->select_quetion(['a.flag'=>$flag],$activity_id)){
			 return $this->renderSuccess($question);
		}else{
			return $this->renderError('暂无数据');	
		}
		
	}
	public function del_activity($activity_id){
		$activity_model=new ActivityModel;
		if($activity_model->del_activity(['activity_id'=>$activity_id],Cache::get($this->token)['user']['user_id'])){
			 return $this->renderSuccess('删除成功');
		}else{
			return $this->renderError('删除失败');	
		}
	}
	
	public function activ_list($keywords='',$sort=1,$page=1,$price_low=0,$price_high=0,$country='',$province='',$city='',$region='',$activ_begin_time=0,$activ_end_time=0,$laguage=0,$kind_id=0,$max_person_num=0){
		$activity_model=new ActivityModel;
		$data=$activity_model->activity_list($keywords ,$sort ,$page ,$price_low ,$price_high ,$country ,$province ,$city ,$region ,$activ_begin_time ,$activ_end_time ,$laguage,$kind_id,Cache::get($this->token)['user']['user_id'],1,$max_person_num);
		if($keywords){
			$activity_array=array_column($data['data'], 'activity_id');
			if($activity_array){
				$activity_model->add_search($activity_array);
			}
			
		} 
		return $this->renderSuccess($data);
	}
	
	
	
	public function complete($keywords='',$kind_id=0,$flag=0){
		$userdata=$this->getuser();
		$where=['a.user_id'=>$userdata['user_id']]; 
		$where['a.status']=0;
		switch($flag){
			case 1:
				// 未提交
				$where['a.audit']=0; 
				$where['a.complete']=0; 
				break;
			case 2:
				// 待审核
				$where['a.audit']=0;
				$where['a.complete']=1; 
				break;
			case 3:
				//审核通过
				$where['a.audit']=1;
				$where['a.complete']=1; 
				break;
			case 4:
				//审核不通过
				$where['a.audit']=2;
				$where['a.complete']=1; 
				break;
			
		}
		/* if($flag==1){
			$nowtime=time();
			$where['a.audit']=1;
			$where['a.online']=0;
			$slot_data=Db::name('activity_slot')->where(['begin_time'=>['lt',$nowtime],'end_time'=>['gt',$nowtime]])->group('activity_id')->column('activity_id'); 
			$where['a.activity_id']=['in',$slot_data];
		}else if($flag==2){ 
			$where['a.audit']=1;
			$where['a.online']=0;
		}else if($flag==3){
			$where['a.online']=1;
		}  */
		$activity_model=new ActivityModel;
		return $this->renderSuccess($activity_model->complete_list($where,$keywords,$kind_id));
	}
	
	public function similar($activity_id){
		$activity_model=new ActivityModel;
		$activity_data=$activity_model->detail(['activity_id'=>$activity_id]); 
		if(!$activity_data){
			return $this->renderError('没有该活动');
		}
		$data=[];
		if($activity_data['kind_id']){
			$kind_array=explode(',',$activity_data['kind_id']);
			$data=$activity_model->similar($kind_array,$activity_id,Cache::get($this->token)['user']['user_id']);
		}
		
		return $this->renderSuccess($data);
	}
	
	public function popular_list(){
		$activity_model=new ActivityModel;
		return $this->renderSuccess($activity_model->popular_list());
	}
	
	public function popular_act_list(){
		$activity_model=new ActivityModel;
		return $this->renderSuccess($activity_model->popular_act_list());
	}

	public function soon_activity(){
		$activity_model=new ActivityModel;
		return $this->renderSuccess($activity_model->activity_list('',1,1,0,0,'','','','',time(),0,'',0,Cache::get($this->token)['user']['user_id']));
	}
	
	public function guess_activity(){
		$activity_model=new ActivityModel;
		$data=$activity_model->guess_activity(Cache::get($this->token)['user']['user_id']);
		return $this->renderSuccess($data);
	}
	
	public function activity_cancel($activity_id,$flag){ 
		
		$activity_model=new ActivityModel;
		$where['activity_id']=$activity_id;
		if($flag==2){
			$slot_model=new ActivitySlot; 
			$slot_id=$slot_model->all_status($activity_id);
			$order_model=new OrderModel;
			if($order_model->isorder($slot_id)) return $this->renderError("有为完成的订单，无法取消"); 
		}
		
		switch($flag){
			case 1:
			$data['online']=0;
			$msg='发布体验';
			break; 
			case 2:
			$data['online']=1;
			$msg='取消体验';
			break; 
		}
		if($activity_model->save_act($data,$where)){
			return $this->renderSuccess($msg.'成功');  
		}else{
			return $this->renderError($msg.'失败');  
		}
	}
	
	public function slot_cancel($slot_id,$flag,$is_all=0,$activity_id){ 
		$slot_id=json_decode($slot_id,true); 
		$slot_model=new ActivitySlot; 
		$activity_model=new ActivityModel; 
		if($flag==2){
			if($is_all) $slot_id=$slot_model->all_status($activity_id);
			$order_model=new OrderModel;
			if($order_model->isorder($slot_id)) return $this->renderError("已有预定订单，无法取消"); 
		} 
		switch($flag){
			case 1:
			$data['online']=0;
			$msg='发布该时间';
			break; 
			case 2:
			$data['online']=1;
			$msg='取消该时间';
			break; 
		}
		if($is_all){	
			$where['activity_id']=$activity_id;
			$sctivity_model->save_act($data,$where);
		}else{
			$where['slot_id']=['in',$slot_id]; 
		}
		

		if($slot_model->save_slot($data,$where)){
			return $this->renderSuccess($msg.'成功');  
		}
			return $this->renderError($msg.'失败');  
		
	}
	
	public function house_save(){ 
		$this->getuser();
		$data=input(); 
		if(!array_key_exists('activity_id',$data)||$data['activity_id']==0){
			return $this->renderError('请输入体验id'); 
		}
		$house_model=new ActivityHouse; 
		$house_model->house_save_one($data);
		return $this->renderSuccess('保存成功');  
	}
	
	public function house_del($house_id){
		$this->getuser();
		$house_model=new ActivityHouse; 
		if($house_model->house_del_one($house_id)){
			return $this->renderSuccess('删除成功');  
		}
			return $this->renderError('删除失败');  
	}
	
	public function activity_list_planner($date=''){ 
		//$date=strtotime($date); 
		if($date===0||$date==''){ 
			$date=[];
		}else{ 
			$date=json_decode(html_entity_decode($date),true);
		}  
		$userdata=$this->getuser();
		$where=['a.user_id'=>$userdata['user_id']]; 
		$where['a.status']=0; 
		$activity_model=new ActivityModel;
		return $this->renderSuccess($activity_model->complete_list($where,'',0,'activity_list_planner',$date));
	}
	
	public function slot_save(){
		$data=input(); 
		$validate = new Validate([
			'activity_id'  => 'require',
			'long_day' => 'require',
			'begin_time' => 'require', 
			'end_time' => 'require', 
			'max_person_num' => 'require', 
			'price' => 'require', 
		],[
			'activity_id.require'=>'请选择修改活动id',
			'long_day.require'=>'请选择活动时间方式',
			'begin_time.require'=>'请输入开始时间',
			'end_time.require'=>'请输入结束时间',
			'max_person_num.require'=>'请输入最大参加人数',  
			'price.require'=>'请输入价格',      
		]); 
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		if(array_key_exists('slot_id',$data)){
			if($order_model->isorder($data['slot_id'])) return $this->renderError("已有预定订单，无法取消"); 
		}
		$slot_model=new ActivitySlot;
		if($slot_model->save_slot_one($data)){
			return $this->renderSuccess('保存成功');
		}else{
			return $this->renderSuccess('保存失败');
		}	
	}
	
	public function slot_delete($slot_id){
		$slot_id=json_decode(html_entity_decode($slot_id),true); 
		$slot_model=new ActivitySlot;
		$order_model=new OrderModel;
		if($order_model->isorder($slot_id)) return $this->renderError("已有预定订单，无法取消"); 
		if($slot_model->save_slot(['status'=>1],['slot_id'=>['in',$slot_id]])){
			return $this->renderSuccess('删除成功');
		}else{
			return $this->renderSuccess('删除失败');
		}	
	}
	
	

	

}