<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\Menu;
use think\Cache;



/**
 * 商户认证
 * Class Passport
 * @package app\store\controller
 */
class Passport extends Controller
{
    /**
     * 商户后台登录
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($user_name,$password)
    {
		
		//$data=input("post."); 
		$data=["user_name"=>$user_name,"password"=>$password];
        $admin_model = new Admin;
        if ($admin=$admin_model->login($data,$this->maketoken())){
            return $this->renderSuccess($admin);
        }
        return $this->renderError($admin_model->getError() ?: '登录失败');
    }

	


    /**
     * 退出登录
     */
    public function logout()
    {
		 Cache::rm($this->token); 
         return $this->renderSuccess("退出成功");
    }
	
	public function gettoken(){
		
		 return $this->renderSuccess($this->maketoken());
	}
		

}
