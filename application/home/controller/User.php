<?php

namespace app\home\controller;

use app\home\model\User as UserModel;
use app\home\model\UserAddress as UserAddressModel;
use app\home\model\UserContacts as UserContactsModel; 
use app\home\model\UserNotice as UserNoticeModel;
use app\home\model\UserBank as UserBankModel;
use app\common\model\Sendmsg as SendmsgModel;
use app\common\model\Md5Entry;
use app\home\model\Image as ImageModel;
use \think\Validate;
use think\Cache;
use think\Db;
use app\common\model\Sendmail; 
/**
 * 用户管理
 * Class User
 * @package app\store\controller
 */
class User extends Controller
{
	//手机密码登录
   public function login_psw($mobile,$password,$m_code){ 
		$validate = new Validate([
			'mobile'  => 'require',
			'password' => 'require',
			'm_code' => 'require',
		],[
			'mobile.require'=>'请输入手机号',
			'password.require'=>'请输入密码', 
			'm_code.require'=>'请输入电话号国家代码', 
		]); 
		if (!$validate->check(['mobile'=>$mobile,'m_code'=>$m_code,'password'=>$password])){
			return $this->renderError($validate->getError());
		}
		$user_model=new UserModel; 
		if($userdata=$user_model->login(['mobile'=>$mobile,'m_code'=>$m_code,'password'=>Md5Entry::password($password)])){ 
			if($userdata['status']==1){
				return $this->renderError('该用户已经冻结');
			}else if($userdata['status']==2){
				return $this->renderError('该用户已经被注销');
			}
			$this->save_token($userdata['user_id']);
			return $this->renderSuccess($userdata);
		}else{
			return $this->renderError('登录名或者密码错误');
		} 
	   
   } 
   //手机验证码登录
   public function login_sms($mobile,$sms_code,$m_code){ 
	   $validate = new Validate([
			'mobile'  => 'require|max:11',
			'sms_code' => 'require', 
			'm_code' => 'require', 
		],[
			'mobile.require'=>'请输入电话号码', 
			'sms_code.require'=>'请输入验证码', 
			'm_code.require'=>'请输入国家手机区号', 
		]);
		$data=['mobile'=>$mobile,'sms_code'=>$sms_code,'m_code'=>$m_code];
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}  
		if(!Cache::get($m_code.$mobile)){
			return $this->renderError('验证码已过期');
		}
		if(Cache::get($m_code.$mobile)!==$sms_code){
			return $this->renderError('验证码输入错误');
		}
		$user_model=new UserModel;
		if($userdata=$user_model->login(['mobile'=>$mobile,'m_code'=>$m_code])){
			if($userdata['status']==1){
				return $this->renderError('该用户已经冻结');
			}else if($userdata['status']==2){
				return $this->renderError('该用户已经被注销');
			}
			$this->save_token($userdata['user_id']);
			return $this->renderSuccess($userdata);
		}else{
			return $this->renderError('没有该手机号');
		}
	   
   }
   
  public function find_pwd($mobile,$sms_code,$m_code,$password){ 
	  $validate = new Validate([
			'mobile'  => 'require|max:11',
			'sms_code' => 'require', 
			'm_code' => 'require', 
			'password' => 'require', 
		],[
			'mobile.require'=>'请输入电话号码', 
			'sms_code.require'=>'请输入验证码', 
			'm_code.require'=>'请输入国家手机区号', 
			'password.require'=>'请输入新设置的密码', 
		]);
		$data=['mobile'=>$mobile,'sms_code'=>$sms_code,'m_code'=>$m_code,'password'=>$password];
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}  
		if(!Cache::get($m_code.$mobile)){
			return $this->renderError('验证码已过期');
		}
		if(Cache::get($m_code.$mobile)!==$sms_code){
			return $this->renderError('验证码输入错误');
		}
		 $user_model=new UserModel;
		 if($user_model->saveuser(['password'=>Md5Entry::password($password)],['mobile'=>$mobile])){
			 return $this->renderSuccess('密码设置成功');
		 }else{
			 return $this->renderError('密码设置失败');
		 }
		
  }
  
   //验证是否有该手机号
   public function validate_mobile($m_code,$mobile){
	  if(!$mobile){
		  return $this->renderError('手机号不能为空');
	  }
	   $user_model=new UserModel;
	   if($user_model->login(['mobile'=>$mobile,'m_code'=>$m_code])){
			return $this->renderError('该手机号已注册');
		}else{
			return $this->renderSuccess('该手机号没有注册');
		} 
   }

   //验证是否有该邮箱
   public function check_email($email){
	  if(!$email){
		  return $this->renderError('邮箱不能为空');
	  }
	   $user_model=new UserModel;
	   if($user_model->login(['email'=>$email])){
			return $this->renderError('该手机号已注册');
		}else{
			return $this->renderSuccess('该手机号没有注册');
		} 
   }
   
   public function email_login($email,$password){
	   $validate = new Validate([
			'email'  => 'require', 
			'password' => 'require', 
		],[
			'email.require'=>'请输入邮箱号码', 
			'password.require'=>'请输入登录密码',  
		]);
		$data=['email'=>$email,'password'=>$password];
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}    
		$user_model=new UserModel;
		if($userdata=$user_model->login(['email'=>$email,'password'=>Md5Entry::password($password)])){
			if($userdata['status']==1){
				return $this->renderError('该用户已经冻结');
			}else if($userdata['status']==2){
				return $this->renderError('该用户已经被注销');
			}
			$this->save_token($userdata['user_id']);
			return $this->renderSuccess($userdata);
		}else{
			return $this->renderError('没有该邮箱');
		}
    }
   
   public function register($m_code,$mobile,$sms_code,$password,$repassword,$flag=0,$top_user_id=0){ 
	   $validate = new Validate([
			'mobile'  => 'require|max:11',
			'sms_code' => 'require',
			'm_code' => 'require',
			'password'=>'require',
			'repassword'=>'require|confirm:password',
		],[
			'mobile.require'=>'请输入电话号码',
			'm_code.require'=>'请输入国家手机区号',
			'password.require'=>'请输入密码',
			'repassword.confirm'=>'确认密码和设置密码不一致',
			'mobile.max'=>'电话格式错误',
			'sms_code.require'=>'请输入验证码', 
		]);
		$data=['m_code'=>$m_code,'mobile'=>$mobile,'sms_code'=>$sms_code,'password'=>$password,'repassword'=>$repassword];
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		if(!Cache::get($m_code.$mobile)){
			return $this->renderError('验证码已过期');
		}
		if(Cache::get($m_code.$mobile)!==$sms_code){
			return $this->renderError('验证码输入错误');
		} 
		   $user_model=new UserModel;
		   switch($flag){
			   case 1:$data['isvolunteer']=1;break;
			   case 2:$data['isplanner']=1;break; 
			   default:
		   }
		   if($top_user_id){
			   $data['top_user_id']=$top_user_id;
			   $top_user_data=$user_model->getuser($top_user_id);
			   $data['user_relation']=$top_user_data['user_relation']==''?$top_user_id:$top_user_data['user_relation'].','.$top_user_id; 
		   }
		   if(UserModel::find_user(['mobile'=>$mobile])){
			   return $this->renderError('该手机号已存在'); 
		   }
		   if($user_id=$user_model->register($data)){
			   $this->save_token($user_id); 
				return $this->renderSuccess($user_id);
		   }else{
			    return $this->renderError('注册失败'); 
		   }
	   
   }  
   public function register_email($email,$sms_code,$password,$repassword,$flag=0,$top_user_id=0){ 
	   $validate = new Validate([
			'email'  => 'require',
			'sms_code' => 'require', 
			'password'=>'require',
			'repassword'=>'require|confirm:password',
		],[
			'email.require'=>'请输入邮箱', 
			'password.require'=>'请输入密码',
			'repassword.confirm'=>'确认密码和设置密码不一致', 
			'sms_code.require'=>'请输入验证码', 
		]);
		$data=['email'=>$email,'sms_code'=>$sms_code,'password'=>$password,'repassword'=>$repassword];
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		if(!Cache::get($email)){
			return $this->renderError('验证码已过期');
		}
		if(Cache::get($email)!==$sms_code){
			return $this->renderError('验证码输入错误');
		} 
		   $user_model=new UserModel;
		   switch($flag){
			   case 1:$data['isvolunteer']=1;break;
			   case 2:$data['isplanner']=1;break; 
			   default:
		   }
		   if($top_user_id){
			   $data['top_user_id']=$top_user_id;
			   $top_user_data=$user_model->getuser($top_user_id);
			   $data['user_relation']=$top_user_data['user_relation']==''?$top_user_id:$top_user_data['user_relation'].','.$top_user_id; 
		   }
		   if(UserModel::find_user(['email'=>$email])){
			   return $this->renderError('该邮箱已存在'); 
		   }
		   if($user_id=$user_model->register($data)){
			   $this->save_token($user_id); 
				return $this->renderSuccess($user_id);
		   }else{
			    return $this->renderError('注册失败'); 
		   }
	   
   }
   public function saveuser(){
	   $userdata=$this->getuser();
	   $data=input();
	   $user_model=new UserModel;
	   if($user_model->saveuser($data,['user_id'=>$userdata['user_id']])){
		   return $this->renderSuccess('保存成功');
	   }else{
		   return $this->renderError('保存失败');
	   }
	   
   }
   
   public function get_user(){ 
		$user_model=new UserModel;
		if(Cache::get($this->token)['user']['user_id']){
			$userdata=$user_model->getuserall(Cache::get($this->token)['user']['user_id']);
			return $this->renderSuccess($userdata);
		}else{
			return $this->renderError('未登录');
		}
		
   }
   
   public function save_address(){
	    $validate = new Validate([
			'country'  => 'require',
			'province' => 'require',
			'city'=>'require',
			'region'=>'require',
			'p_code'=>'require', 
			'name'=>'require', 
			'mobile'=>'require', 
		],[
			'country.require'=>'请输入所在国家',
			'province.require'=>'请输入所在省',
			'city.require'=>'请输入所在市',
			'region.require'=>'请输入所在区',
			'p_code.require'=>'请输入邮编', 
			'name.require'=>'请输入联系人姓名', 
			'mobile.require'=>'请输入联系人电话', 
		]);
		$data=input();
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
	   $userdata=$this->getuser(); 
	   $data['user_id']=$userdata['user_id'];
	   $user_address_model=new UserAddressModel;
	   if($user_address_model->save_address($data)){
		   return $this->renderSuccess('保存成功');
	   }else{
		   return $this->renderError('保存失败');
	   }
	   
   }
   
   public function save_contacts(){
	    $validate = new Validate([
			'name'  => 'require',
			'relation' => 'require',
			'mobile'=>'require',
			'm_code'=>'require',
			'email'=>'require',
			'language'=>'require', 
		],[
			'name.require'=>'请输入姓名',
			'relation.require'=>'请输入联系人和您的关系',
			'mobile.require'=>'请输入电话',
			'email.require'=>'请输入email',
			'language.require'=>'请输入首选语言', 
			'm_code.require'=>'请输入国家电话代码', 
		]);
		$data=input();
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}	   
	   $userdata=$this->getuser(); 
	   $data['user_id']=$userdata['user_id'];
	   $user_contacts_model=new UserContactsModel;
	   if($user_contacts_model->save_contacts($data)){
		   return $this->renderSuccess('保存成功');
	   }else{
		   return $this->renderError('保存失败');
	   }
   }
   
   public function del_address($address_id){
	    $user_address_model=new UserAddressModel;
		if($user_address_model->del_address($address_id)){
			return $this->renderSuccess('删除成功');
		}else{
			return $this->renderError('删除失败');
		}
   } 
   public function del_contacts($contacts_id){
	    $user_contacts_model=new UserContactsModel;
		if($user_contacts_model->del_contacts($contacts_id)){
			return $this->renderSuccess('删除成功');
		}else{
			return $this->renderError('删除失败');
		}
   }
   
   public function quit(){
		 Cache::rm($this->token); 
         return $this->renderSuccess("退出成功");
   }
   
   public function user_list($keywords='',$sort=1,$page=1,$country='',$province='',$city='',$region='',$language='',$score=0){
		$user_model=new UserModel;
		$data=$user_model->user_list($keywords,$sort,$page,$country,$province,$city,$region,$language,$this->user_id,1,1,$score);
		return $this->renderSuccess($data);
	   
   }  
   public function planner_list($keywords='',$sort=1,$page=1,$country='',$province='',$city='',$region='',$language=''){
		$user_model=new UserModel;
		$data=$user_model->user_list($keywords,$sort,$page,$country,$province,$city,$region,$language,$this->user_id,1,2);
		return $this->renderSuccess($data);
	   
   }
   public function send_msg($flag,$m_code,$mobile){
	   switch($flag){
			case 1:
				$key='find_pwd';
				break;
			case 2:
				$key='register';
				break;
			case 3:
				$key='login';
				break;
			case 4:
				$key='change_mobile';
				break;
			case 5:
				$key='bind_mobile';
				break;
			case 6:
				$key='set_paypassword';
				break; 
	   } 
	   $code=rand(111111,999999);
	   Cache::set(($m_code.$mobile),$code,600);
	   $sendmsg_model=new SendmsgModel; 
	   if($sendmsg_model->sendmsg($key,$m_code,$mobile,['code'=>$code])){
		   return $this->renderSuccess("发送成功");
	   }else{
		   return $this->renderError("发送失败");
	   }
   }
   
   public function config_add(){
	   $userdata=$this->getuser();
	   $data=input();
	   $data['user_id']=$userdata['user_id']; 
	   $notice_model=new UserNoticeModel;
	   if($notice_model->add($data)){
		   return $this->renderSuccess("修改成功");
	   }else{
		   return $this->renderError("修改失败");
	   }
	   
   }
   public function config_get(){
	    $userdata=$this->getuser();
		$notice_model=new UserNoticeModel;
		 return $this->renderSuccess($notice_model->detail(['user_id'=>$userdata['user_id']]));
   }
   
   public function edit_pwd(){
	   $userdata=$this->getuser();
	   $validate = new Validate([
			'ori_password'  => 'require',
			'password' => 'require',
			're_password'=>'require',  
		],[
			'ori_password.require'=>'请输入原始密码',
			'password.require'=>'请输入新密码',
			're_password.require'=>'请输入重复密码', 
		]);
		$data=input();
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		$user_model=new UserModel; 
		if($userdata['password']==Md5Entry::password($data['password'])){
			return $this->renderError('新密码不能和原始密码一样');
		}
		if($userdata['password']!==Md5Entry::password($data['ori_password'])){
			return $this->renderError('输入原始密码错误');
		}
		if($data['password']!==$data['re_password']){
			return $this->renderError('两次输入密码不一致');
		}
		 if($user_model->saveuser(['password'=>Md5Entry::password($data['password'])],['user_id'=>$userdata['user_id']])){
			 return $this->renderSuccess('密码设置成功');
		 }else{
			 return $this->renderError('密码设置失败');
		 }
		
		
		
   }
   
   public function  get_otheruser($user_id){
	   // $userdata=$this->getuser();
		$user_model=new UserModel;
		return $this->renderSuccess($user_model->getuserall_find($user_id,Cache::get($this->token)['user']['user_id']));
   }
   
   public function bank_save(){
	   $data=input(); 
	   $appcode=config('appcode');
	   $user_name=$data['user_name'];
	   $card_number=preg_replace('# #','',$data['card_number']);
	   $querys = "acct_name=$user_name&acct_pan=$card_number&needBelongArea=true";
	   $header=["Authorization:APPCODE " . $appcode];
	   $result=https_request_bank("https://ali-bankcard4.showapi.com/bank2".'?'.$querys,$header,'GET'); 
	   $result=json_decode($result,true);  
	   if($result['showapi_res_body']['code']==0){
		   $userdata=$this->getuser();
			$data['user_id']=$userdata['user_id'];
			$bank_model=new UserBankModel;
			if($bank_model->bank_save($data)){
				return $this->renderSuccess('保存成功');
			}else{
				return $this->renderError('保存失败');
			}
	   }else{
			return $this->renderError($result['showapi_res_body']['msg']);   
	   }
	   
   }
   
   
   public function identify_card(){
	   $this->identify_card_planner('identify_card');
   }
   
   public function bank_del($bank_id){
	   $bank_model=new UserBankModel;
	    if($bank_model->bank_del($bank_id)){
		   return $this->renderSuccess('删除成功');
	   }else{
		   return $this->renderError('删除失败');
	   }
   }
   
   public function bank_list(){
	   $userdata=$this->getuser();
	   $bank_model=new UserBankModel;
	   return $this->renderSuccess($bank_model->bank_list($userdata['user_id'])); 
   }
   
   public function address_list(){
	   $userdata=$this->getuser();
	   $user_address_model=new UserAddressModel; 
	   return $this->renderSuccess($user_address_model->address_list($userdata['user_id'])); 
   }
   
   public function user_cancel(){
		 $data=input();
	     $userdata=$this->getuser();
		 $user_model=new UserModel;
		if($user_model->user_cancel($userdata['user_id'],$data)){
			 return $this->renderSuccess('注销成功');
		}else{
			 return $this->renderError('注销失败');
		}
   }
   
   public function set_pay_password($password,$re_password){
		 if($password!==$re_password){
			 return $this->renderError('两次密码输入不一致');
	 	}
	     $userdata=$this->getuser();
		 $user_model=new UserModel;
		 if($user_model->set_pay_password($password,$userdata['user_id'])){
			  return $this->renderSuccess('支付密码设置成功');
		 }else{
			  return $this->renderError('支付密码设置失败');
		 }
   }
   
      public function edit_pay_pwd(){
	   $userdata=$this->getuser();
	   $validate = new Validate([
			'ori_password'  => 'require',
			'password' => 'require',
			're_password'=>'require',  
		],[
			'ori_password.require'=>'请输入原始密码',
			'password.require'=>'请输入新密码',
			're_password.require'=>'请输入重复密码', 
		]);
		$data=input();
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}
		$user_model=new UserModel; 
		if($userdata['pay_password']!==Md5Entry::password($data['ori_password'])){
			return $this->renderError('输入原始密码错误');
		}
		if($userdata['pay_password']==Md5Entry::password($data['password'])){
			return $this->renderError('新密码不能和原始密码一样');
		}
		
		if($data['password']!==$data['re_password']){
			return $this->renderError('两次输入密码不一致');
		}
		if($user_model->set_pay_password($data['password'],$userdata['user_id'])){
			  return $this->renderSuccess('支付密码重新设置成功');
		 }else{
			  return $this->renderError('支付密码重新设置失败');
		 } 
		
   }
    public function find_pay_pwd($mobile,$sms_code,$m_code,$password,$re_password){ 
	  $userdata=$this->getuser();
	  $validate = new Validate([
			'mobile'  => 'require|max:11',
			'sms_code' => 'require', 
			'm_code' => 'require', 
			'password' => 'require', 
			're_password' => 'require', 
		],[
			'mobile.require'=>'请输入电话号码', 
			'sms_code.require'=>'请输入验证码', 
			'm_code.require'=>'请输入国家手机区号', 
			'password.require'=>'请输入新设置的密码', 
			're_password.require'=>'请输入确认密码', 
		]);
		$data=['mobile'=>$mobile,'sms_code'=>$sms_code,'m_code'=>$m_code,'password'=>$password,'re_password'=>$re_password];
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}  
		if(!Cache::get($m_code.$mobile)){
			return $this->renderError('验证码已过期');
		}
		if(Cache::get($m_code.$mobile)!==$sms_code){
			return $this->renderError('验证码输入错误');
		}
		 $user_model=new UserModel;
		 if($user_model->set_pay_password($password,$userdata['user_id'])){
			  return $this->renderSuccess('支付密码重新设置成功');
		 }else{
			  return $this->renderError('支付密码重新设置失败');
		 } 
		
	}
  
	public function edit_mobile($mobile,$sms_code,$m_code){
	   $this->getuser();
	   $validate = new Validate([
			'mobile'  => 'require|max:11',
			'sms_code' => 'require', 
			'm_code' => 'require', 
		],[
			'mobile.require'=>'请输入电话号码', 
			'sms_code.require'=>'请输入验证码', 
			'm_code.require'=>'请输入国家手机区号', 
		]);
		$data=['mobile'=>$mobile,'sms_code'=>$sms_code,'m_code'=>$m_code];
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}  
		if(!Cache::get($m_code.$mobile)){
			return $this->renderError('验证码已过期');
		}
		if(Cache::get($m_code.$mobile)!==$sms_code){
			return $this->renderError('验证码输入错误');
		}
		$user_model=new UserModel;
		if($user_model->save(['mobile'=>$mobile,'m_code'=>$m_code],['user_id'=>$this->user_id])){
			return $this->renderSuccess('修改成功');
		}else{
			return $this->renderError('修改失败');
		}
	}
	
	public function dynamic($user_id=0){
		if($user_id==0){
			//查所有关注的人
			$userdata=$this->getuser();
			$user_id=Db::name('attention')->where(['user_id'=>$userdata['user_id']])->column('att_user_id');
			array_push($user_id,$this->user_id);
		}else{
			$user_id=[$user_id];
		} 
		$user_model=new UserModel; 
		return $this->renderSuccess($user_model->dynamic($user_id));
	}
	 
	public function identify_card_planner($type='identify_card_planner'){
		$data=input();
		$user_model=new UserModel; 
		$userdata=$this->getuser();
		if(!in_array($data['flag'],[1,2,3]))
		{
			return $this->renderError('缺失重要参数flag');
		}
		if($data['flag']==1)
		{
			if(array_key_exists('idcard_z',$data)&&$data['idcard_z']){
			    $image_data=Db::name('image')->where(['image_id'=>$data['idcard_z']])->find(); 
			   //验证身份证c
				$appcode=config('appcode');
				$headers = array();
				array_push($headers, "Authorization:APPCODE " . $appcode);
				//根据API的要求，定义相对应的Content-Type
				array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8"); 
				//$bodys = 'image=https://www.allptp.cn/web/uploads/20190416/6dc9f596eb632a3e79090243672ae236.jpg'; //图片参数
				$bodys = 'image='.$image_data['domain'].$image_data['image_url']; //图片参数
				$result=https_request_bank("https://ocr2idcard.market.alicloudapi.com/OcridCard",$headers,'POST',$bodys); 
				$result=json_decode($result,true); 				
				if($result['code']=='01'){
					$data['idcard_n']=$result['ocr']['idCard']; 
					$data['audit_idcard']=1;
					
				}else{
					if($result['msg']){
						return $this->renderError($result['msg']); 
					}else{
						return $this->renderError('上传证件不正确'); 
					}
					 
				} 
			}else{
				return $this->renderError('请上传身份证正面');  	
			}
			if(!(array_key_exists('idcard_f',$data)&&$data['idcard_f'])){
				return $this->renderError('请上传身份证反面');  
			}
		}
		if($data['flag']==2){		
			if(array_key_exists('passport',$data)&&$data['passport']){
			   $image_data=Db::name('image')->where(['image_id'=>$data['passport']])->find(); 
			   //验证身份证c
				$appcode=config('appcode');  
				$file = $image_data['domain'].$image_data['image_url']; 
				//$file = "https://www.allptp.cn/web/uploads/20190522/1195960f51500ecfb7244b41e7e6372a.jpg"; 
				//如果没有configure字段，configure设为空
				/* $configure = array(
					"side" => "face"
				); */
				$configure = array();

				$base64=base64_encode(file_get_contents($file));  
				$headers = array(); 
				array_push($headers, "Authorization:APPCODE " . $appcode);
				//根据API的要求，定义相对应的Content-Type
				array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
				$querys = "";
				$request = array(
					"image" => "$base64"
				);
				if(count($configure) > 0){
					$request["configure"] = json_encode($configure);
				}
				$bodys = json_encode($request);
				$result=https_request_passport("https://ocrhz.market.alicloudapi.com/rest/160601/ocr/ocr_passport.json",$headers,'POST',$bodys);
				if($result['code']){
					$return_data=$result['data'];
					$return_data=json_decode($return_data,true);
					if($userdata['family_name'].$userdata['middle_name'].$userdata['name']==''){
							$data['name']=$return_data['name_cn'];
					}
					if($type=='identify_card'){
						$data['audit_idcard']=1;
					}
				}else{
					return $this->renderError($result['msg']);
				}
			}else{
				return $this->renderError('请上传护照');
			} 
		} 
		if($data['flag']==3){
			
			if(!array_key_exists('face_image',$data)&&$data['face_image']){
				return $this->renderError('请上传手持身份证照片');
			}
		}
		$data['audit_face']=0;
		if($user_model->saveuser($data,['user_id'=>$userdata['user_id']])){ 
			if(array_key_exists('issubmit',$data)&&$data['issubmit']==1){ 
				$user_model->saveuser(['audit_face'=>1],['user_id'=>$userdata['user_id']]);
				//审核实名认证完成后发短信 发系统消息
				//$sendmail_model=new Sendmail; 
				$sendmsg_model=new SendmsgModel;
				$sendmsg_model->submit_check_planner($userdata['user_id']);
			} 
			return $this->renderSuccess('保存成功');
		}else{
			return $this->renderError('保存失败');
		}  
	}
	
	public function volunteer_become($other_language,$introduce){
		$userdata=$this->getuser();
		$user_model=new UserModel;
		if($user_model->saveuser(['other_language'=>$other_language,'introduce'=>$introduce,'isvolunteer'=>1],['user_id'=>$userdata['user_id']])){
			 $sendmsg_model=new SendmsgModel;
			 $sendmsg_model->colunteer_become($userdata);
			 return $this->renderSuccess('已成为志愿者');
		}else{
			return $this->renderError('操作失败');
		}
	}
	
/* 	public function wecha_login(){
	   $wchat = new \wechat\WechatOauth(); 
       $code = request()->param('code',"");
       $baseurl = request()->param('baseurl',"");
       $user = $wchat->getUserAccessUserInfo($code,$baseurl);
	   if($user){
			$user_model=new UserModel;
			$userdata=$user_model->login(['wecha_openid'=>$user['unionid']]);
			if($userdata){
				$image_model=new ImageModel; 
				if($user['headimgurl']){
					$image_id=$image_model->save_headimageurl($user['headimgurl']);
					$data['head_image']=$image_id;
				}  
				$data['wecha_openid']=$user['unionid'];
				$data['name']=$user['nickname'];
				$data['six']=$user['sex'];
				$user_model->saveuser_update($data,['wecha_openid'=>$user['unionid']]);
				if($userdata['status']==1){
					echo '该用户已经冻结';exit;
					//return $this->renderError('该用户已经冻结');
				}else if($userdata['status']==2){
					echo '该用户已经被注销';exit;
				} 
			}else{ 
				$image_model=new ImageModel; 
				if($user['headimgurl']){
					$image_id=$image_model->save_headimageurl($user['headimgurl']);
					$data['head_image']=$image_id;
				}  
				$data['wecha_openid']=$user['unionid'];
				$data['name']=$user['nickname'];
				$data['six']=$user['sex'];
				$user_model->register($data);
				$userdata=$user_model->login(['wecha_openid'=>$user['unionid']]); 	
			}
			$this->save_token($userdata['user_id']);
			return $this->renderSuccess($userdata); 	
	   }else{
		   return $this->renderError('获取用户信息失败');
	   }
	} */
	public function wecha_login(){
	//微信登录第二版
	   $wchat = new \wechat\WechatOauth(); 
       $code = request()->param('code',"");
       $baseurl = request()->param('baseurl',"");
       $top_user_id = request()->param('top_user_id',0);
	   Cache::set(input('token').'_base_url',$baseurl,1800);
       $wchat->getUserAccessUserInfo($code,config('wxconnect.callback')."?token=".input('token').'&top_user_id='.$top_user_id);
	   
	}
	public function qq_login($baseurl){   
		$qc = new \kuange\QC();  
		Cache::set(input('token').'_base_url',$baseurl,1800);
		$top_user_id = request()->param('top_user_id',0);
        return redirect($qc->qq_login(config('qqconnect.callback')."?token=".input('token').'&top_user_id='.$top_user_id));
	}
	
	/* public function callback_login()
    {
		
        $qc = new \kuange\QC();
        $access_token = $qc->qq_callback();
        $openid = $qc->get_openid();
//        qq互联请求地址
//        https://graph.qq.com/user/get_user_info?access_token=YOUR_ACCESS_TOKEN&oauth_consumer_key=YOUR_APP_ID&openid=YOUR_OPENID
        $user = json_decode($this->CurlGet("https://graph.qq.com/user/get_user_info?access_token=" . $access_token . "&oauth_consumer_key=" . Config('qqconnect.appid') . "&openid=" . $openid));
        $nickname = $user->{'nickname'};
        $figureurl_qq_2 = $user->{'figureurl_qq_2'};
        //pre($figureurl_qq_2);


    } */
	
	public function bind_mobile_link($m_code,$mobile,$sms_code){
		$userdata=$this->getuser();
		$validate = new Validate([
			'mobile'  => 'require|max:11',
			'sms_code' => 'require', 
			'm_code' => 'require', 
		],[
			'mobile.require'=>'请输入电话号码', 
			'sms_code.require'=>'请输入验证码', 
			'm_code.require'=>'请输入国家手机区号', 
		]);
		$data=['mobile'=>$mobile,'sms_code'=>$sms_code,'m_code'=>$m_code];
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}  
		if(!Cache::get($m_code.$mobile)){
			return $this->renderError('验证码已过期');
		}
		if(Cache::get($m_code.$mobile)!==$sms_code){
			return $this->renderError('验证码输入错误');
		}
		$user_model=new UserModel;
		if($user_model->save(['mobile_link'=>$mobile,'m_code_link'=>$m_code],['user_id'=>$userdata['user_id']])){
			return $this->renderSuccess('绑定成功');
		}else{
			return $this->renderError('绑定失败');
		}
	}	
	public function bind_email_link($email,$sms_code){
		$userdata=$this->getuser();
		$validate = new Validate([
			'email'  => 'require',
			'sms_code' => 'require',   
		],[
			'email.require'=>'请输入邮箱', 
			'sms_code.require'=>'请输入验证码', 
		]);
		$data=['email'=>$email,'sms_code'=>$sms_code];
		if (!$validate->check($data)){
			return $this->renderError($validate->getError());
		}  
		if(!Cache::get($email)){
			return $this->renderError('验证码已过期');
		}
		if(Cache::get($email)!==$sms_code){
			return $this->renderError('验证码输入错误');
		}
		$user_model=new UserModel;
		if($user_model->save(['email_link'=>$email],['user_id'=>$userdata['user_id']])){
			return $this->renderSuccess('绑定成功');
		}else{
			return $this->renderError('绑定失败');
		}
	}

}
