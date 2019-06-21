<?php

namespace app\admin\controller; 
use app\admin\model\Admin as AdminModel;
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
	protected $admin_id; 
	

    /**
     * 后台初始化
     */
    public function _initialize()
    {
		
		if($this->request->controller()!="Passport"||$this->request->action()!="login"){
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
			$this->admin_id=Cache::get($this->token)["admin"]["admin_id"];
			
		}else{
			throw new BaseException(['code' => 4, 'msg' => '缺少token参数']);
		}
	} 
    /**
     * 获取当前用户信息
     * @return mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    protected function getadmin()
    {	
	 
        if (!$admin = AdminModel::getadmin($this->admin_id)) {
           throw new BaseException(['code' => 2, 'msg' => '没有找到用户信息']);
        }
        return $admin;
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
	

}
