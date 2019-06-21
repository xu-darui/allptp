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
class Qqcallback extends Controller
{
	public function callback(){
		$token=input('token');
		$top_user_id=input('top_user_id');
		$baseurl=Cache::get($token.'_base_url'); 
		$qc = new \kuange\QC();
        $access_token = $qc->qq_callback();
        $openid = $qc->get_openid();
        $user = json_decode($this->CurlGet("https://graph.qq.com/user/get_user_info?access_token=" . $access_token . "&oauth_consumer_key=" . Config('qqconnect.appid') . "&openid=" . $openid));
		if($user){
				$image_model=new ImageModel; 
				if($user->{'figureurl_qq_2'}){
					$image_id=$image_model->save_headimageurl($user->{'figureurl_qq_2'});
					$data['head_image']=$image_id; 
				}  
				$data['name']=$user->{'nickname'};
				$data['six']=$user->{'gender'}=='女'?2:1;
				$user_model=new UserModel; 
				$user_model->saveuser_update($data,['qq_openid'=>$openid]);
				$userdata=$user_model->login(['qq_openid'=>$openid]);
			if($userdata){
				if($userdata['status']==1){
					throw new BaseException(['code' => 404, 'msg' => '该用户已经冻结']); exit;
				}else if($userdata['status']==2){
					throw new BaseException(['code' => 404, 'msg' => '该用户已经被注销']); exit; 
				} 
			}else{ 
				$image_model=new ImageModel; 
				if($user->{'figureurl_qq_2'}){
					$image_id=$image_model->save_headimageurl($user->{'figureurl_qq_2'});
				} 
				$data['head_image']=$image_id;
				$data['qq_openid']=$openid;
				$data['name']=$user->{'nickname'};
				$data['six']=$user->{'gender'}=='女'?2:1;
				if($top_user_id>0){
					$data['top_user_id']=$top_user_id;
					$top_user_data=$user_model->getuser($top_user_id);
					$data['user_relation']=$top_user_data['user_relation']==''?$top_user_id:$top_user_data['user_relation'].','.$top_user_id;  
				} 
				$user_model->register($data);
				$userdata=$user_model->login(['qq_openid'=>$openid]); 	
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
		//pre($user);
        //$nickname = $user->{'nickname'};
        //$figureurl_qq_2 = $user->{'figureurl_qq_2'};
        //pre($figureurl_qq_2); 
		Header("Location: $baseurl");
        exit();
	}
	    //get请求
    function CurlGet($url)
    {
        return $this->CurlPost($url, "");
    }

    //curl 的post请求
    function CurlPost($url, $data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        curl_setopt($curl, CURLOPT_URL, $url);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

}