<?php

namespace app\admin\model;

use app\common\model\User as UserModel;
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
	
	public function user_list($keywords,$role,$sort,$page,$type=''){
		$user_list=new UserModel;
		$where=[];
		if($type=='user_check_list'){
			$where['audit_face']=1;
		}
		switch($sort){
			case 1;
				$order="score desc";
				break;
			case 2; 
				$order="balance desc";
				break;
			case 3; 
				$order="praise_num desc";
				break;
			case 4; 
				$order="fans_num desc";
				break;
			case 5;
				$order="activ_num desc";
				break;
			default:
				$order="user_id desc";
		}
		switch($role){ 
			case 0; 
				break;
			case 1;
				$where['isplanner']=1;
				break;
			case 2; 
				$where['isvolunteer']=1;
				break;
			case 3; 
				$where['isvolunteer']=array('neq',1);
				$where['isplanner']=array('neq',1);
				break;
		}
		 
		if($keywords){
			$where["email|mobile|introduce"]=['like','%'.$keywords.'%'];
			$data=$user_list
					->with(['useraddress','usercontacts','headimage','passportd','idcardz','idcardf','faceimage'])
					->where($where) 
					->whereor("CONCAT(IFNULL(family_name,''),IFNULL(middle_name,''),IFNULL(name,'')) like '%".$keywords."%'")
					->order($order)
					->paginate(10, false, ['query' => ["page"=>$page]]); 
		}else{
			$data=$user_list
					->with(['useraddress','usercontacts','headimage','passportd','idcardz','idcardf','faceimage'])
					->where($where)  
					->order($order)
					->paginate(10, false, ['query' => ["page"=>$page]]); 
		}
		return $data;
	}
	
	public function user_role_kind(){
		$statis['planner']['china']=$this->where(['status'=>0,'isplanner'=>1,'country_id'=>0])->count('user_id');
		$statis['planner']['foreign']=$this->where(['status'=>0,'isplanner'=>1,'country_id'=>['neq',0]])->count('user_id');
		$statis['volunteer']['china']=$this->where(['status'=>0,'isvolunteer'=>1,'country_id'=>0])->count('user_id');
		$statis['volunteer']['foreign']=$this->where(['status'=>0,'isvolunteer'=>1,'country_id'=>['neq',0]])->count('user_id');
		$statis['tourist']['china']=$this->where(['status'=>0,'isplanner'=>['neq',1],'isvolunteer'=>['neq',1],'country_id'=>['neq',0]])->count('user_id');
		$statis['tourist']['foreign']=$this->where(['status'=>0,'isplanner'=>['neq',1],'isvolunteer'=>['neq',1],'country_id'=>['neq',0]])->count('user_id');
		return $statis;
	}
	
	public function statistics_add($where,$format){
		
		return Db::name('user')->field("FROM_UNIXTIME(create_time, '".$format."' ) as time,count('user_id') as count")->group('time')->order('create_time desc')->where($where)->select(); 
		
	}

	public function statistics_cancel($where,$format){
		
		return Db::name('user')->field("FROM_UNIXTIME(cancel_time, '".$format."' ) as time,count('user_id') as count")->group('time')->order('cancel_time desc')->where($where)->select(); 
	}
	
	public function save_user($data,$where){
		return $this->where($where)->update($data);
	}
}
