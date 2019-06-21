<?php

namespace app\common\model;

use think\Request;

/**
 * 用户模型类
 * Class User
 * @package app\common\model
 */
class User extends BaseModel
{
	 protected $hidden = [
        'password' ,
        'pay_password' ,
		'balance'
    ];
	
	public function useraddress(){
		return $this->hasMany('UserAddress','user_id','user_id');
	}
	public function usercontacts(){
		return $this->hasMany('UserContacts','user_id','user_id');
	}
	
	public function headimage(){
		return $this->hasOne('Image','image_id','head_image');
	}
	public function passportd(){
		return $this->hasOne('Image','image_id','passport');
	}
	public function idcardz(){
		return $this->hasOne('Image','image_id','idcard_z');
	}
	public function idcardf(){
		return $this->hasOne('Image','image_id','idcard_f');
	}
	public function faceimage(){
		return $this->hasOne('Image','image_id','face_image');
	}
    public function getList()
    {
        $request = Request::instance();
        return $this->order(['create_time' => 'desc'])
            ->paginate(15, false, ['query' => $request->request()]);
    }
	
	public function attention(){
		return $this->belongsTo('Attention','att_user_id','user_id');
	}

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
