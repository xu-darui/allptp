<?php

namespace app\admin\model;

use app\common\model\Admin as AdminModel; 
use app\common\model\Menu as MenuModel; 


use think\Cache;



class Admin extends AdminModel
{
    /**
     * 后台用户登录
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($data,$token)
    {
		
        // 验证用户名密码是否正确
        if (!$admin = self::useGlobalScope(false)->where([
            'user_name' => $data["user_name"],
            'password' => md5($data["password"])
        ])->find()) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }
        // 保存登录状态
        Cache::set($token, [
            'admin' => [
                'admin_id' => $admin['admin_id'],
                'user_name' => $admin['user_name'],
            ],
            'is_login' => true,
        ],86400*7); 
		$admin["token"]=$token;
        return $admin;
    }
	
	    /**
     * 获取用户信息
     * @param $admin_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function getadmin($admin_id)
    {
        return self::detail(['admin_id' => $admin_id]);
    }
	
	public function save_admin($data){
		if(array_key_exists("password",$data)){
			$data["password"]=md5($data["password"]);
		}  
		if(array_key_exists('pro_list',$data)&& is_array($data['pro_list'])){
			$data['pro_list']=implode(',',$data['pro_list']);
		} 
		if(array_key_exists("admin_id",$data)&& $data["admin_id"]>0){
			$where["admin_id"]=$data["admin_id"];
			return $this->allowField(true)->save($data,$where);
		}
		return $this->allowField(true)->save($data);
			
	}
	
	public function del_admin($admin_id){ 
		return AdminModel::update(['status' => 1],['admin_id'=>['in',$admin_id]]);
	}
	
	public function select_admin($where,$page){ 
		$list= $this->where($where)->order("admin_id desc")->paginate(10, false, ['query' => ["page"=>$page]]); 
		$menu_model=new MenuModel;
		foreach($list as $key=>$value){
			$list[$key]['pro']=$menu_model->where(['id'=>['in',$value['pro_list']]])->select();
		}
		return $list;
		
	}
  
}
