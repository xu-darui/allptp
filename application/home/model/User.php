<?php

namespace app\home\model;

use app\common\model\User as UserModel;
use app\common\model\UserStop as UserStopModel;
use app\common\model\Search as SearchModel;
use app\common\model\UserCancel as UserCancelModel;
use app\common\model\UserFriend as UserFriendModel;
use app\home\model\Collection as CollectionModel;  
use app\home\model\Activity as ActivityModel; 
use app\home\model\Order as OrderModel; 
use app\home\model\Story as StoryModel; 
use app\home\model\Forward as ForwardModel; 
use app\common\model\Md5Entry;
use app\common\model\Attention as AttentionModel;
use think\Db;


/**
 * 用户模型
 * Class User
 * @package app\store\model
 */
class User extends UserModel
{

    /**
     * 获取用户信息
     * @param $user_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function getuser($user_id)
    {
        return self::detail(['user_id' => $user_id]);
    }
	
	public static function  find_user($where){
		$where['status']=0;
		 return self::detail($where);
	}
	
	public function getuserall($user_id){
		$user_model=new UserModel;
		$data=$user_model->alias('a')->with(['useraddress'=>function($query){$query->where(['status'=>0]);},'usercontacts'=>function($query){$query->where(['status'=>0]);},'headimage','passportd','idcardz','idcardf'])->where(['a.user_id'=>$user_id])->select(); 
		if($data[0]['pay_password']==''){
			 $data[0]['is_setpwd']=0;
		}else{
			 $data[0]['is_setpwd']=1;
		}
		return $data;
	}
	
	public function getuserall_find($user_id,$self_user_id){
		$user_model=new UserModel;
		 $data=$user_model->alias('a')->with(['useraddress'=>function($query){$query->where(['status'=>0]);},'usercontacts'=>function($query){$query->where(['status'=>0]);},'headimage','passportd','idcardz','idcardf'])->where(['a.user_id'=>$user_id])->find();
		 //我的收藏
		 $collection_model=new CollectionModel;
		 $data['collect_group']=$collection_model->collegroup_list(['g.user_id'=>$user_id,'g.hide'=>0,'g.status'=>0],0,0);
		 //我创建的活动
		 $activity_model=new ActivityModel;
		 $data['create_activity']=$activity_model->complete_list(['a.status'=>0,'a.audit'=>1,'a.online'=>0,'a.user_id'=>$user_id]);
		 
		 //我参加过得体验
		 $order_model=new OrderModel;
		 $data['order_list']= $order_model->order_list_all(['ispay'=>1,'status'=>0,'user_id'=>$user_id]);
		//我创建的故事
		 $story_model=new StoryModel;
		 $data['story_list']= $story_model->create_list_all(['user_id'=>$user_id,'status'=>0]);
		 $data['is_attention']= AttentionModel::where(['status'=>0,'user_id'=>$self_user_id,'att_user_id'=>$user_id])->count('attention_id');
		 $data['is_friend']= UserFriendModel::where(['status'=>0,'user_id'=>$self_user_id,'f_user_id'=>$user_id])->count('friend_id');
		 if($data['pay_password']==''){
			 $data['is_setpwd']=0;
		}else{
			 $data['is_setpwd']=1;
		}
		 return $data;
	}
	
	
	public function login($where){
		return $this->with(['headimage','passportd','idcardz','idcardf'])->where($where)->find();
	}
	
	public function register($data){
		if(array_key_exists('password',$data)){
			 $data["password"]=Md5Entry::password($data["password"]);
		} 
		 $this->allowField(true)->save($data);
		 return $this->user_id;
	}
	public function saveuser($data,$where){  
		return $this->allowField(true)->save($data,$where); 
	}
	
	public function saveuser_update($data,$where){  
		return $this->where($where)->update($data); 
	}
	public function user_list($keywords,$sort,$page,$country,$province,$city,$region,$language,$user_id,$write_key=1,$flag,$score=0){
		$flag_key=0;
		if($flag==1){
			$where['isvolunteer']=1; 
			$flag_key=4;
		}else if($flag==2){
			$where['isplanner']=1; 
			$flag_key=5;
		} 
		switch($sort){
			case 1;
				$order="score desc";
				break; 
			case 2;
				$order="praise_num desc";
				break;  
			case 5;
				$order="leaving_num desc";
				break;
			case 6;
				$order="fans_num desc";
				break;
			case 7;
				$order="volun_num desc";
				break;
			default:
				$order="user_id desc";
		}
		if($country){
			$where['country']=['like','%'.$country.'%'];
		}
		if($province){
			$where['province']=['like','%'.$province.'%'];
		}
		if($city){
			$where['city']=['like','%'.$city.'%']; 
		}
		if($region){
			$where['region']=['like','%'.$region.'%'];
		}
		if($language){
			$where['language|other_language']=$language;
		} 
		if($score>0){
			$where['score']=['egt',$score];
		} 
		$where['status']=0;
		$user_model=new UserModel;
		if($keywords){
			if($user_id&&$write_key){ 
				$search_model=new SearchModel;
				 $search_model->save(['flag'=>$flag_key,'keywords'=>$keywords,'user_id'=>$user_id]);
			} 
			$where["religion|habits"]=['like','%'.$keywords.'%']; 
			$data=$user_model->alias('a')->with('headimage')->where($where)->whereor("CONCAT(IFNULL(country,''),IFNULL(province,''),IFNULL(city,''),IFNULL(region,''),IFNULL(address,'')) like '%".$keywords."%'")->with('head_image')->whereor("CONCAT(IFNULL(family_name,''),IFNULL(middle_name,''),IFNULL(name,'')) like '%".$keywords."%'")->order($order)->paginate(10, false, ['query' => ["page"=>$page]]);
		}else{
			$data=$user_model->alias('a')->with('headimage')->order($order)->where($where)->paginate(10, false, ['query' => ["page"=>$page]]);
		}
		
		return $data;		
	}
	public function relation($userdata){ 
	if($userdata['user_relation']==''){
		return [];
	}else{
		return Db::query("select * from ptp_user where user_id in (".$userdata['user_relation'].") order by FIND_IN_SET('user_id','".$userdata['user_relation']."') desc");
	} 
	}
	public function add_balance($reward){ 
		if($reward){
			foreach($reward as $key=>$value){
				$this->where(['user_id'=>$value['user_id']])->setInc('balance',+$value['amount']); 
			}
		}
	}
	
	
	public function user_cancel($user_id,$data){ 
		// 启动事务
		Db::startTrans();
		try{
			$data['user_id']=$user_id;
			$stop_model=new UserStopModel;
			$stop_model->allowField(true)->save($data);
			$this->where(['user_id'=>$user_id])->update(['status'=>2,'cancel_time'=>time()]);
			$activity_model=new ActivityModel;
			$activity_model->save_act(['status'=>2],['user_id'=>$user_id,'status'=>0]);
			$story_model=new StoryModel;
			$activity_model->save_story_one(['status'=>2],['user_id'=>$user_id,'status'=>0]);
			// 提交事务
			Db::commit();    
			return true;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
			return false;
		}   
	}
	
	public function set_pay_password($password,$user_id){
		$pay_password=Md5Entry::password($password);
		return $this->where(['user_id'=>$user_id])->update(['pay_password'=>$pay_password]);
		
	}
	
	public function dynamic($user_id){
		$data=Db::view('ptp_act_story_forw',"id,title,flag,user_id,create_time")
			->view('ptp_user','user_id,family_name,middle_name,name,head_image','ptp_user.user_id = ptp_act_story_forw.user_id')
			->view('ptp_image','domain,image_url,themb_url','ptp_user.head_image = ptp_image.image_id','LEFT')
			->where(['user_id'=>['in',$user_id]])
			->order('create_time desc')
			->paginate(10, false, ['query' => ["page"=>input('page')==''?1:input('page')]]);
//pre(Db::view('ptp_act_story_forw')->getlastsql());			
		$activity_model=new ActivityModel;
		$story_model=new StoryModel;
		$forward_model=new ForwardModel;
		foreach($data as $key=>$value){
			$value['create_time']=date("Y-m-d",$value['create_time']);
			switch($value['flag']){
				case 1:
				//创建活动
				$value['datas']=$activity_model->dynamic_activity(['activity_id'=>$value['id']]); 
				break;
				case 2:
				//创建故事
				$value['datas']=$story_model->dynamic_story(['story_id'=>$value['id']]);
				break;
				case 3:
				//转发
				$value['datas']=$forward_model->dynamic_forward(['forward_id'=>$value['id']]);
				break;
				
			}
			$data[$key]=$value;
		}
		
		return $data;
	}
	
		public function reduce_balance($reward){  
		if($reward){
			Db::startTrans();
			try{ 
				$user=[]; 
				foreach($reward as $key=>$value){ 
					if(!array_key_exists($value['user_id'],$user)){
						$balance=$this->where(['user_id'=>$value['user_id']])->value('balance');
						$user[$value['user_id']]['balance']=$balance;
					}
					$this->where(['user_id'=>$value['user_id']])->setDes('balance',$value['amount']); 
					$user[$value['user_id']]['balance']-=$value['amount']; 
					$reward[$key]['balance']=$user[$value['user_id']]['balance'];
				}
				$running_amount=new RunningAmount;
				$running_amount->allowField(true)->saveAll($reward);	
				// 提交事务
				Db::commit(); 
				return true;
			} catch (\Exception $e) {
				// 回滚事务
				Db::rollback();
			}
		}
	}
	public function update_score($data,$config){
		$where=['isplanner'=>1,'user_id'=>$data['activity_user_id']];
		$this->where(['credit_score'=>['egt',$config['credit']['refund_reduce_score']]])->where($where)->setDec('credit_score',$config['credit']['refund_reduce_score']);
		$this->where(['credit_score'=>['lt',$config['credit']['refund_reduce_score']]])->where($where)->update(['credit_score'=>0]);	 
	}
	
}
