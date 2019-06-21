<?php 
namespace app\task\controller; 
use app\common\exception\BaseException;
use app\home\model\Image as ImageModel;
use app\home\model\User as UserModel;
use think\Cache;
/**
 * QQ登录跳转
 * Class Qqcallback
 * @package app\task\controller
 */
class Wxcallback extends Controller
{
	public function callback(){
		$wchat = new \wechat\WechatOauth();
		$token=input('token');
		$code = request()->param('code',"");
		$top_user_id = request()->param('top_user_id',0);
		$baseurl=Cache::get($token.'_base_url'); 
		$user = $wchat->getUserAccessUserInfo($code,$baseurl);
		if($user){
			$image_model=new ImageModel; 
			if($user['headimgurl']){
				$image_id=$image_model->save_headimageurl($user['headimgurl']);
				$data['head_image']=$image_id; 
			}  
			$data['name']=$user['nickname'];
			$data['six']=$user['sex'];
			$user_model=new UserModel; 
			$user_model->saveuser_update($data,['wecha_openid'=>$user['unionid']]);  
			$userdata=$user_model->login(['wecha_openid'=>$user['unionid']]); 
			if($userdata){
				if($userdata['status']==1){
					throw new BaseException(['code' => 404, 'msg' => '该用户已经冻结']); exit;
				}else if($userdata['status']==2){
					throw new BaseException(['code' => 404, 'msg' => '该用户已经被注销']); exit; 
				} 
			}else{  
				$data['wecha_openid']=$user['unionid'];
				$data['name']=$user['nickname'];
				$data['six']=$user['sex'];
				if($top_user_id>0){
					$data['top_user_id']=$top_user_id;
					$top_user_data=$user_model->getuser($top_user_id);
					$data['user_relation']=$top_user_data['user_relation']==''?$top_user_id:$top_user_data['user_relation'].','.$top_user_id;  
				} 
				$user_model->register($data);
				$userdata=$user_model->login(['wecha_openid'=>$user['unionid']]);
			}  
			// 保存登录状态
			Cache::set($token, [
				'user' => [
					'user_id' => $userdata['user_id'], 
				],
			],86400*7);	 
		
	   }else{
		   throw new BaseException(['code' => 404, 'msg' => '获取用户信息失败']); exit;  
	   } 
		Header("Location: $baseurl");
        exit();
	}
	 

}