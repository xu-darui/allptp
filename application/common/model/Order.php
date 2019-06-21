<?php

namespace app\common\model;

/**
 * 订单
 * Class Order
 * @package app\common\model
 */
class Order extends BaseModel
{
	protected $type = [ 
        'activ_begin_time'  =>  'timestamp:Y-m-d H:i:s',
        'activ_end_time'  =>  'timestamp:Y-m-d H:i:s',
        'pay_time'  =>  'timestamp:Y-m-d',
    ];
	public function user(){
		return $this->hasOne('User','user_id','user_id');
	}
	public function activity(){
		return $this->hasOne('Activity','activity_id','activity_id');
	}
	public function house(){
		return $this->hasMany('OrderHouse','order_id','order_id');
	}
	public function person(){
		return $this->hasMany('OrderPerson','order_id','order_id');
	}
	public function cover(){
		return $this->hasOne('Image','image_id','cover_image');
	}
	public function comment(){
		return $this->hasOne('Comment','order_id','order_id')->field('comment_id,order_id,content')->where(['flag'=>3,'status'=>0]);
	}
}