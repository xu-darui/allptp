<?php

namespace app\home\controller; 
use app\home\model\User as UserModel;
use app\common\model\Config as ConfigModel;
use app\common\exception\BaseException;
use think\Cache;
/**
 * 后台控制器基类
 * Class BaseController
 * @package app\admin\controller
 */
class Controller extends \think\Controller
{
    const JSON_SUCCESS_STATUS = 1;
    const JSON_ERROR_STATUS = 0;  
	protected $token; 
	protected $user_id; 
	

    /**
     * 后台初始化
     */
    public function _initialize()
    {
		if($this->request->controller()!="Index"||$this->request->action()!="token"){
			//验证token
			$this->checktoken(); 
		};	
    }
	private function checktoken(){
		if($this->request->param("token")){
			if(!Cache::get($this->request->param("token"))){
				throw new BaseException(['code' => 3, 'msg' => 'token过期']);
			}
			$this->token=$this->request->param("token");
			if(!array_key_exists('user',Cache::get($this->token))){ 
				throw new BaseException(['code' => 3, 'msg' => 'token过期']);
			}
			$this->user_id=Cache::get($this->token)["user"]["user_id"];
			$this->save_token($this->user_id);
			
			
		}else{
			throw new BaseException(['code' => 4, 'msg' => '缺少token参数']);
		}
	} 
	public function save_token($user_id){ 
		// 保存登录状态
		Cache::set($this->token, [
			'user' => [
				'user_id' => $user_id, 
			],
		],86400*7);		
	}
   
   public function getuser(){ 
	   if($user_id=Cache::get($this->token)['user']['user_id']){
		   $user_model=new UserModel;
		   if($userdata=$user_model->getuser($user_id)){
				if($userdata['status']==1){
					  throw new BaseException(['code' => 5, 'msg' => '该用户已经停用']); 
				}else if($userdata['status']==2){
					throw new BaseException(['code' => 6, 'msg' => '该用户已经注销']); 
				}
				return $userdata;
		   }else{
			  throw new BaseException(['code' => 2, 'msg' => '没有该用户']); 
		   }
	   }else{
		   throw new BaseException(['code' => 2, 'msg' => '没有该用户']);
	   }
	   
   }
   
	//生成token
	public function maketoken(){
		return md5($this->request->controller().$this->request->action().time().'allptop');
	}

 

    /**
     * 返回封装后的 API 数据到客户端
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderJson($code = self::JSON_SUCCESS_STATUS, $msg = '', $data = [])
    {
        return compact('code', 'msg', 'url', 'data');
    }

    /**
     * 返回操作成功json
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderSuccess($data = [], $msg = 'success')
    {
        return $this->renderJson(self::JSON_SUCCESS_STATUS, $msg, $data);
    }

    /**
     * 返回操作失败json
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderError($msg = 'error', $data = [])
    {
        return $this->renderJson(self::JSON_ERROR_STATUS, $msg, $data);
    }

    /**
     * 获取post数据 (数组)
     * @param $key
     * @return mixed
     */
    protected function postData($key)
    {
        return $this->request->post($key . '/a');
    }
	
	protected function config(){
		$config=ConfigModel::get(['key'=>'config']);
		return (json_decode($config['values'],true)); 
	}

	

}
