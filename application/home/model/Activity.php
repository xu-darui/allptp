<?php

namespace app\home\model;

use app\common\model\Activity as ActivityModel;
use app\common\model\Collection as CollectionModel;
use app\common\model\Question as QuestionModel;
use app\home\model\QuestionAnswer;
use app\home\model\ActivitySlot;
use app\home\model\Image as ImageModel;
use app\common\model\Search as SearchModel;
use app\common\model\Kind as KindModel;
use app\common\model\Report as ReportModel;
use app\common\model\Translate as TranslateModel;
use app\common\model\Enroll as EnrollModel; 
use app\common\model\Sendmsg as SendmsgModel; 
use app\home\model\Order as OrderModel;
use app\common\model\Refund as RefundModel;
use think\Db;
/* use app\home\model\ActivityPlace; */

/**
 * 活动模型
 * Class Activity
 * @package app\home\model
 */
class Activity extends ActivityModel
{
	public function save_activity($data){ 
	
		if(array_key_exists('introduce',$data)&&$data['introduce']){ 
			$data['introduce']=htmlspecialchars_decode($data['introduce']); 
		}
		if(array_key_exists('title',$data)&&$data['title']){ 
			$data['title']=htmlspecialchars_decode($data['title']); 
		}
		if(array_key_exists('descripte',$data)&&$data['descripte']){ 
			$data['descripte']=htmlspecialchars_decode($data['descripte']); 
		}
		if(array_key_exists('activ_place',$data)&&$data['activ_place']){ 
			$data['activ_place']=htmlspecialchars_decode($data['activ_place']); 
		}
		if(array_key_exists('activ_bring',$data)&&$data['activ_bring']){ 
			$data['activ_bring']=htmlspecialchars_decode($data['activ_bring']); 
		}
		if(array_key_exists('go_place',$data)&&$data['go_place']){ 
			$data['go_place']=htmlspecialchars_decode($data['go_place']); 
		}
		//
		if(array_key_exists('kind_id',$data)&&$data['kind_id']){
			$kind=KindModel::get(['kind_id'=>$data['kind_id'],'status'=>0]); 
			$data['kind_id']=$kind['path']==''?$data['kind_id']:$kind['path'].','.$data['kind_id'];
		}
		/* if(array_key_exists('country',$data)&&array_key_exists('province',$data)&&array_key_exists('city',$data)&&array_key_exists('address',$data)){
			$data['set_address']=$data['country'].$data['province'].$data['city'].$data['address'];
		}  */ 
		if(array_key_exists('activ_begin_time',$data)){
			$data['activ_begin_time']=strtotime($data['activ_begin_time']);
		}
		if(array_key_exists('activ_end_time',$data)){
			$data['activ_end_time']=strtotime($data['activ_end_time']);
		} 
		if(array_key_exists('activity_id',$data)&&$data['activity_id']){
			$step=$this->where(['activity_id'=>$data['activity_id']])->value('step');
			if(!in_array($data['step'],explode(',',$step))){
				$data['step']=$step==''?$data['step']:$step.','.$data['step'];	
			}else{
				unset($data['step']);
			} 			
			$this->allowField(true)->save($data,['activity_id'=>$data['activity_id']]);
			if(array_key_exists('complete',$data)&&$data['complete']==1){ 
				$sendmsg_model=new SendmsgModel;
				$sendmsg_model->send_submit_activity($data['activity_id']);
				
			}
			$activity_id=$data['activity_id'];
		}else{
			 $this->allowField(true)->save($data);
			 $activity_id= $this->activity_id; 
		}
			
		if(array_key_exists('question',$data)&&$data['question']){ 
		 
			//保存单选问题
			if(array_key_exists('isapp',$data)&&$data['isapp']) $data['question']=json_decode(html_entity_decode($data['question']),true);
			$answer_model=new QuestionAnswer; 
			$answer_model->save_answer($data['question'],$activity_id);
		}
		if(array_key_exists('issatay',$data)&&$data['issatay']==0){
			$house_model=new ActivityHouse;
			$house_model->delete_house($activity_id);
		}
		if((array_key_exists('issatay',$data)&&$data['issatay']==1)&&array_key_exists('house',$data)){
			if(array_key_exists('isapp',$data)&&$data['isapp']) $data['house']=json_decode(html_entity_decode($data['house']),true);
			$house_model=new ActivityHouse;
			$house_model->delete_house($activity_id);
			$house_model->save_house($data['house'],$activity_id);
		}
		if(array_key_exists('slot',$data)&&$data['slot']){
			if(array_key_exists('isapp',$data)&&$data['isapp']) $data['slot']=json_decode(html_entity_decode($data['slot']),true);
			$slot_model=new ActivitySlot; 
			//先把该活动所有的时间段删除   再添加
			$slot_model->delete_slot($activity_id);
			if($data['long_day']==1){
				//一天多个时间段
				$slot_model->save_day_slot($data['slot'],$activity_id);
				
			}else{ 
				//长时间活动
				$slot_model->save_long_slot($data['slot'],$activity_id);
			}
			
		}
		if(array_key_exists('house_image',$data)&&$data['house_image']){
				$image_model=new ImageModel; 
				$image_model->save_image($data['house_image'],$activity_id,6);
			}
		if(array_key_exists('image',$data)&&$data['image']){
			if(array_key_exists('isapp',$data)&&$data['isapp']) $data['image']=json_decode(html_entity_decode($data['image']),true);
			$image_model=new ImageModel;
			$image_model->save_image($data['image'],$activity_id,1);
			
		}
		
	/* 	if(array_key_exists('place',$data)&&$data['place']){
			
			$place_model=new ActivityPlace;
			$place_model->save_place($data['place'],$activity_id);
			
		}  */
		return $activity_id;
	}
	
	public function activity_edit($where){	
		$activity_model=new ActivityModel;
		
		$data=$activity_model->alias('a')->with(['slot'=>function($query){$query->where(['status'=>['neq',1]]);},'answer','image','houseimage','house.image' ,'cover'])->where($where)->find()->toArray(); 
		
		if($data['long_day']){
			//一天多个时间段
			$slot=$data['slot'];
			$slot_model=new ActivitySlot; 
			$data['slot']=$slot_model->create_slot_array($slot);
			
		} 
		$question_model=new QuestionModel;
		$data['question_option']=$question_model->alias('a')->with('option')->where(['a.flag'=>['in',[0,1,2,3,4,5]]])->select(); 
		return $data;
	}
	
	public function get_activity($where,$user_id,$translate_id,$language){ 
		$where['a.status']=0; 
		$activity_model=new ActivityModel; 
		$data=$activity_model->alias('a')->with(['user.headimage','answer','slot'=>function($query){$query->where(['status'=>['neq',1],'online'=>0]);},'image','houseimage','house.image' ,'cover'])->where($where)->find()->toArray();
		$total_time=Db::name('activity_slot')->where(['activity_id'=>$data['activity_id'],'status'=>0])->order('begin_time asc')->value('total_time');
		$data['total_time']=how_long($total_time);
		if($data['long_day']){
			//一天多个时间段
			$slot=$data['slot'];
			$slot_model=new ActivitySlot; 
			$data['slot']=$slot_model->create_slot_array($slot);
			
		}
		$data['is_collection']=0;
		if($user_id){
			$collection_num=Db::name('collection')->where(['table_id'=>$data['activity_id'],'status'=>0,'flag'=>1,'user_id'=>$user_id])->count(); 
			$data['is_collection']=$collection_num>0?1:0; 
			$order_count=Db::name('order')->where(['ispay'=>1,'status'=>0,'user_id'=>$user_id,'iscomplete'=>2])->count('activity_id');
			$data['is_order']=$order_count>0?1:0;
		} 
		$order_model=new OrderModel;  
		foreach($data['slot'] as $key_slot=>$slot_value){
			if($data['long_day']){
				if(array_key_exists('list',$slot_value)){
					foreach($slot_value['list'] as $key=>$value ){
						$data['slot'][$key_slot]['list'][$key]['order_person_num']=$order_model->order_person_num(['slot_id'=>$value['slot_id'],'activity_id'=>$data['activity_id']]);
					}
				}
			}else{ 
				$data['slot'][$key_slot]['order_person_num']=$order_model->order_person_num(['slot_id'=>$slot_value['slot_id'],'activity_id'=>$slot_value['activity_id']]);
				
			} 
		} 
		$question_model=new QuestionModel;
		$data['question_option']=$question_model->alias('a')->with('option')->where(['a.flag'=>['in',[0,1,2,3,4,5]]])->select(); 
		$kind_model=new KindModel;
		$data['kind']=$kind_model->field('kind_id,kind_name')->where(['kind_id'=>['in',$data['kind_id']]])->select()->toArray(); 
		$data['is_report']=ReportModel::where(['status'=>0,'flag'=>1,'table_id'=>$data['activity_id'],'user_id'=>$user_id])->count();
		$translate_model=new TranslateModel;
		if($translate_id>0){  
			$translate=$translate_model->with(['user.headimage'])->where(['translate_id'=>$translate_id,'activity_id'=>$data['activity_id']])->find();  
			$data['introduce']=$translate['t_introduce'];
			$data['descripte']=$translate['t_descripte'];
			$data['translate_user']=$translate['user'];
		}else{
			$translate=$translate_model->where(['activity_id'=>$data['activity_id'],'status'=>0,'language'=>$language])->order("praise_num desc")->find();
			$data['best_introduce']=$translate['t_introduce'];
			$data['best_descripte']=$translate['t_descripte'];
		}
		//var_dump($data);
		return $data;
		
	}
	public function del_activity($where,$user_id){
		$activity_model=new ActivityModel;
		$num=$activity_model->user_acti_num($user_id);
		Db::name('user')->where(['user_id'=>$user_id])->update(['activ_num'=>$num]);
		return $this->allowField(true)->save(['status'=>1],$where);
		
	}
	
	public function activity_list($keywords ,$sort ,$page ,$price_low ,$price_high ,$country ,$province ,$city ,$region ,$activ_begin_time ,$activ_end_time ,$laguage='',$kind_id,$user_id,$write_key=1,$max_person_num=0){
		
		$activity_model=new ActivityModel; 
			$where_kind='';	
			switch($sort){
				case 1;
					$order="a.score desc";
					break;
				case 2; 
					$order="a.praise_num desc";
					break;
				case 3; 
					$order="a.collection_num desc";
					break;
				case 4; 
					$order="a.comment_num desc";
					break;
				case 5;
					$order="a.leaving_num desc";
					break;
				default:
					$order="a.activity_id desc";
			}
			 
			$where['a.status']=0;
			$where['a.audit']=1;
			$where['a.online']=0;
			if($country){
				$where['a.country']=['like','%'.$country.'%'];
			}
			if($province){
				$where['a.province']=['like','%'.$province.'%'];
			}
			if($city){
				$where['a.city']=['like','%'.$city.'%']; 
			}
			if($region){
				$where['a.region']=['like','%'.$region.'%'];
			}  
			if($kind_id){
				$where_kind="find_in_set($kind_id,a.kind_id)";
			}
			if($price_high){
				$where['b.price']=['between',[$price_low ,$price_high ]];
			}
			if($activ_begin_time){
				$where['b.begin_time']=['egt',$activ_begin_time];
			}
			if($max_person_num){
				$where['b.max_person_num']=['egt',$max_person_num];
			}
			if($activ_end_time){
				$where['b.end_time']=['elt',$activ_end_time];
			}
			if($laguage!=''){
				$where['a.main_laguage|a.other_laguage']=$laguage;
			}
			$is_volunteen=input('is_volunteen'); 
			if($is_volunteen!=''){
				$where['a.is_volunteen']=$is_volunteen;
			}
			//pre($where);
			$where_keywords='';
			$where_keywords_transtlate='';
			$where_keywords_kind='';
			if($keywords){ 
				$kind_array=Db::name('kind')->where(['kind_name'=>['like','%'.$keywords.'%'],'status'=>0])->column('kind_id');
				if($kind_array){
					$kind_count=count($kind_array)-1;  
					foreach($kind_array as $key=>$kind_id){ 
						if($kind_count==$key){
							$where_keywords_kind=$where_kind."find_in_set($kind_id,kind_id)";
						}else{
							$where_keywords_kind=$where_kind."find_in_set($kind_id,kind_id) or ";
						} 	
					}   
					 
				}
				$activity_id_array=Db::name('translate')->where(['t_introduce|t_descripte'=>['like','%'.$keywords.'%'],'status'=>0])->column('activity_id');
				//pre($activity_id_array);
				if($activity_id_array){
					$where_keywords_transtlate=implode(',',$activity_id_array);
					//pre($where_keywords_transtlate);
					$where_keywords_transtlate="a.activity_id in ($where_keywords_transtlate)";
				} 
				//$where["a.title|a.introduce|a.descripte|a.set_address"]=['like','%'.$keywords.'%'];
				//$where_address="CONCAT(IFNULL(a.country,''),IFNULL(a.province,''),IFNULL(a.city,''),IFNULL(a.region,'')) like '%".$keywords."%'";
				$where_keywords="CONCAT(IFNULL(a.country,''),IFNULL(a.province,''),IFNULL(a.city,''),IFNULL(a.region,'')) like '%".$keywords."%' or a.title like '%".$keywords."%' or a.introduce like '%".$keywords."%' or a.set_address like '%".$keywords."%' or a.descripte like '%".$keywords."%'"; 
				if($where_keywords_kind){
					$where_keywords=$where_keywords.' or '.$where_keywords_kind;
				}
				if($where_keywords_transtlate){
					$where_keywords=$where_keywords.' or '.$where_keywords_transtlate;
				}
				
				if($user_id&&$write_key){
					$search_model=new SearchModel;
					$search_model->save(['flag'=>2,'keywords'=>$keywords,'user_id'=>$user_id]);
				} 
			}
			//pre($where_keywords);
			$data=Db::name('activity')
			->alias('a')
			->field('a.*,b.price,(b.end_time-b.begin_time) as total_time,c.domain,c.image_url,c.themb_url,if(d.collection_id>0,1,0) as is_collection')
			->join('activity_slot b','a.activity_id=b.activity_id and b.status=0','LEFT')
			->join('image c','a.cover_image=c.image_id','LEFT')
			->join('collection d',"d.table_id=a.activity_id and d.flag=1 and d.status=0 and d.user_id=$user_id",'LEFT')
			->where($where)
			->where($where_keywords)
			->where($where_kind)
			->group('a.activity_id')
			->paginate(10, false, ['query' => ["page"=>$page]])->toArray(); 
			//pre(Db::name('activity')->getlastsql());
			foreach($data['data'] as $key=>$value){
				$data['data'][$key]['total_time']=how_long($value['total_time']);
			}
			if($data){
				$kind_model=new KindModel;
				$data=$kind_model->addkind_array($data); 
			} 
			return $data; 
		
	}
	
	public function add_sale($order){
		$this->where('activity_id',$order['activity_id'])->setInc('sale_num',+1);
	}
	
	
	public function complete_list($where,$keywords="",$kind_id=0,$type='',$date=[]){
		if($type=='activity_list_planner'){
			$where['a.audit']=1;
			$where['a.status']=0;
			$where['a.complete']=1;
		} 
		$where_actiivty_id=''; 
		if($date){
			$date_array=[];
			$date_str='';
			$date_str_a='';
			$count=count($date)-1; 
			foreach($date as $key=>$date_value){
				$date_value_time=strtotime($date_value); 
				if($key===$count){ 
					$new_str="date=$date_value_time or ( begin_date <= $date_value_time AND end_date >= $date_value_time)";
					$new_str_a="a.date=$date_value_time or ( a.begin_date <= $date_value_time AND a.end_date >= $date_value_time)";
				}else{
					$new_str="date=$date_value_time or  (begin_date <= $date_value_time AND end_date >= $date_value_time) or ";
					$new_str_a="a.date=$date_value_time or  (a.begin_date <= $date_value_time AND a.end_date >= $date_value_time) or ";
				} 
				$date_str.=$new_str;
				$date_str_a.=$new_str_a;
			}  
			$actiivty_id_array=Db::name('activity_slot')->where($date_str)->where(['status'=>['neq',1]])->column("DISTINCT(activity_id)"); 
			//pre($actiivty_id_array->);
		//$actiivty_id_array=ActivitySlot::where(['status'=>['neq',1],'online'=>0])->where(function($query) use($date){$query->where(['begin_time'=>['lt',$date],'end_time'=>['gt',($date+86400)]])->whereor(['begin_time'=>['between',[$date,($date+86400)]]]);})->column('activity_id');
			$actiivty_id_array=implode(',',$actiivty_id_array);
			if($actiivty_id_array){
				$where_actiivty_id="a.activity_id in ($actiivty_id_array)";
			}
		}
		$activity_model=new ActivityModel;
		$where_keywords='';
		if($keywords){  
			$where_keywords="CONCAT(IFNULL(a.country,''),IFNULL(a.province,''),IFNULL(a.city,''),IFNULL(a.region,'')) like '%".$keywords."%' or a.title like '%".$keywords."%' or a.introduce like '%".$keywords."%' or a.set_address like '%".$keywords."%' or a.descripte like '%".$keywords."%'"; 
		}
		$where_kind='';
		if($kind_id){
			$where_kind="find_in_set($kind_id,a.kind_id)";
		}
		$data= $activity_model->alias('a')
			->field("a.activity_id,a.user_id,a.title,a.step,a.cover_image,a.complete,a.audit,a.create_time,a.kind_id,a.status,a.score,a.comment_num,a.leaving_num,a.collection_num,a.sale_num,a.long_day")
			->with(['cover','user'=>function($query){$query->field("user_id,idcard_z,idcard_f");}])
			->where($where)
			->where($where_keywords)
			->where($where_kind)
			->where($where_actiivty_id)
			->order('a.activity_id desc')
			->select()
			->toArray(); 
			//pre($activity_model->getlastsql());
		if($data){
			$kind_model=new KindModel; 
			$data=$kind_model->addkind($data);
			foreach($data as $key=>$value){ 
				$data[$key]['price']=ActivitySlot::where(['activity_id'=>$value['activity_id'],'status'=>0])->value('price');
				$doing_count=ActivitySlot::where(['begin_time'=>['lt',time()],'end_time'=>['gt',time()],'status'=>0,'activity_id'=>$value['activity_id']])->count('slot_id');
				$data[$key]['is_doing']=$doing_count>0?1:0;
				$data[$key]['enroll_count']=EnrollModel::where(['activity_id'=>$value['activity_id'],'status'=>0])->count('enroll_id');
				$data[$key]['refund_num']=RefundModel::where(['activity_id'=>$value['activity_id'],'status'=>0])->count('refund_id');
				if($type=='activity_list_planner'){
					if($date){
						$slot=Db::name('activity_slot')->alias('a')->field("a.slot_id,a.activity_id,FROM_UNIXTIME(a.date, '%Y-%c-%d') AS date,FROM_UNIXTIME(a.begin_date, '%Y-%c-%d') as begin_date,FROM_UNIXTIME(a.end_date, '%Y-%c-%d') as end_date,FROM_UNIXTIME(a.begin_time, '%H:%i') as begin_time,FROM_UNIXTIME(a.end_time, '%H:%i') as end_time,a.total_time,a.max_person_num,a.price,a.online,a.status,ifnull(sum(b.num), 0) AS order_num,ifnull(sum(c.person_num), 0) AS refund_num,IFNULL(count(d.enroll_id), 0) AS enroll_count")->join('order b','a.slot_id = b.slot_id and b. status <> 2 ','left')->join('refund c','c.order_id = b.order_id','left')->join('enroll d','a.slot_id = d.slot_id and d.audit <> 2 and d. status = 0','left')->where($date_str_a)->where(['a.activity_id'=>$value['activity_id'],'a.status'=>['neq',1]])->group('a.slot_id')->order('a.begin_time asc')->select()->toArray(); 
					}else{
						$slot=Db::name('activity_slot')->alias('a')->field("a.slot_id,a.activity_id,FROM_UNIXTIME(a.date, '%Y-%c-%d') AS date,FROM_UNIXTIME(a.begin_date, '%Y-%c-%d') as begin_date,FROM_UNIXTIME(a.end_date, '%Y-%c-%d') as end_date,FROM_UNIXTIME(a.begin_time, '%H:%i') as begin_time,FROM_UNIXTIME(a.end_time, '%H:%i') as end_time,a.total_time,a.max_person_num,a.price,a.online,a.status,ifnull(sum(b.num), 0) AS order_num,ifnull(sum(c.person_num), 0) AS refund_num,IFNULL(count(d.enroll_id), 0) AS enroll_count")->join('order b','a.slot_id = b.slot_id and b. status <> 2 ','left')->join('refund c','c.order_id = b.order_id','left')->join('enroll d','a.slot_id = d.slot_id and d.audit <> 2 and d. status = 0','left')->where(['a.activity_id'=>$value['activity_id'],'a.status'=>['neq',1]])->group('a.slot_id')->order('a.begin_time asc')->select()->toArray(); 
					}
					$data[$key]['slot']=$slot; 
					if($value['long_day']){
						//一天多个时间段 
						$slot_model=new ActivitySlot; 
						$data[$key]['slot']=$slot_model->create_slot_array($slot);
						
					}
				}
				
			}
		}
		
		return $data;
	}
	
	public function detail($where){  
		return ActivityModel::get($where);
	}
	
	public function similar($kind_array,$activity_id=0,$user_id){
		$where_kind='';
		$data=[];
		if($kind_array){
			$kind_count=count($kind_array)-1;  
			foreach($kind_array as $key=>$kind_id){ 
				if($kind_count==$key){
					$where_kind=$where_kind."find_in_set($kind_id,kind_id)";
				}else{
					$where_kind=$where_kind."find_in_set($kind_id,kind_id) or ";
				} 	
			}   
			 
		} 
		$where['a.status']=0;
			$where['a.audit']=1;
			$where['a.online']=0;
			$where['a.activity_id']=['neq',$activity_id];
			$data=Db::name('activity')
			->alias('a')
			->field('a.*,b.price,c.domain,c.image_url,c.themb_url,if(d.collection_id>0,1,0) as is_collection')
			->join('activity_slot b','a.activity_id=b.activity_id and b.status=0','LEFT')
			->join('image c','a.cover_image=c.image_id','LEFT')
			->join('collection d',"d.table_id=a.activity_id and d.flag=1 and d.status=0 and d.user_id=$user_id",'LEFT')
			->where($where) 
			->where($where_kind)
			->group('a.activity_id')
			->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]])->toArray();  
			if($data){
				$kind_model=new KindModel;
				$data=$kind_model->addkind_array($data); 
			} 
			return $data;  
	}
	
	public function slot_detail($where){
		return Db::name('activity_slot')
		->alias('a')  
		->where($where)
		->value('a.max_person_num');
	}
	
	public function add_search($activity_array){ 
		ActivityModel::where('activity_id','in',$activity_array)->setInc('search_num');
	}
	
	public function popular_list(){
		return  Db::name('activity')->field("count(activity_id) as create_num,sum(sale_num) as sale_num,sum(search_num) as search_num,city,city_id")->group('province_id,city_id')->order('create_num desc ,sale_num desc ,search_num desc')->where(['status'=>0,'online'=>0,'complete'=>1,'audit'=>1])->select();
		
	}

	public function popular_act_list(){
		$data['data']= Db::name('activity')->field("activity_id,title,search_num,kind_id")->where(['status'=>0,'online'=>0,'complete'=>1,'audit'=>1])->order('search_num desc')->limit(20)->select(); 
		if($data){
			$kind_model=new KindModel;  
			$data=$kind_model->addkind_array($data);
		}
		return $data['data'];
	}
	
	/* public function soon_activity(){
		$nowtime=time();
		return Db::name('activity')->alias('a')->join('activity_slot b','a.activity_id=b.activity_id','INNER')->where(['a.status'=>0,'a.audit'=>1,'a.complete'=>1,'b.begin_time'=>['egt',$nowtime]])->select();
	} */

	public function guess_activity($user_id){ 
		$kind_id=Db::view('act_story')->field("GROUP_CONCAT('',kind_id) as kind_array")->where(['user_id'=>$user_id])->order('create_time desc')->select(); 
		$data=[];
		if($kind_id[0]['kind_array']){
			$kind_array=array_values(array_unique(explode(',',$kind_id[0]['kind_array']))); 

			$data=$this->similar($kind_array,0,$user_id); 
		}
		return $data;
		
	}
	
	public function dynamic_activity($where){ 
		$where['status']=0; 
		$where['audit']=1; 
		$activity_model=new ActivityModel;
		return $activity_model->field('activity_id,title,introduce,descripte,comment_num,collection_num,score,country,province,city,region,leaving_num,forward_num,user_id')->with(['image'])->where($where)->find();
		
	}
	
	public function save_act($data,$where){
		return $this->where($where)->update($data);
	}
	
	public function check_credit($data){
		return $credit_score=Db::name('activity')->alias('a')->join('user b','a.user_id=b.user_id','left')->where(['a.activity_id'=>$data['activity_id']])->find();
	}
	
	
	
	
}