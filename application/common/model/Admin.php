<?php

namespace app\common\model;

/**
 * 商家用户模型
 * Class StoreUser
 * @package app\common\model
 */
class Admin extends BaseModel
{

	protected $hidden = [
        'password' 
    ];
    /**
     * 获取用户信息
     * @param $where
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
		return self::get($where);
    }

 

}
