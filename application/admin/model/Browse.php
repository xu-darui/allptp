<?php

namespace app\admin\model;

use app\common\model\Browse as BrowseModel;
use think\Db;

/**
 * 浏览记录
 * Class Browse
 * @package app\admin\model
 */
class Browse extends BrowseModel
{
	public function statistics($where,$format){
		
		$data=Db::name('browse')->field("FROM_UNIXTIME(create_time, '".$format."' ) as create_time_copy,count('id') as count")->group('create_time_copy')->order('create_time desc')->where($where)->select();
		return $data;
		
	}
	
}