<?php

namespace app\admin\model;

use app\common\model\Banner as BannerModel;

/**
 * 轮滚
 * Class Banner
 * @package app\admin\model
 */
class Banner extends BannerModel
{
	public function banner_sava($data){
		if(array_key_exists('id',$data)){
			return $this->allowField(true)->save($data,['id'=>$data['id']]);
		}else{
			return $this->allowField(true)->save($data);
		}
	}
	public function bannerlist($flag,$page){
		$banner_model=new BannerModel;
		return $banner_model->with('image')->where(['status'=>0,'flag'=>$flag])->order('sort desc,id desc')->paginate(10, false, ['query' => ["page"=>$page]]); 
	}
	public function detail($id){
		$banner_model=new BannerModel;
		return $banner_model->with('image')->where(['id'=>$id])->find(); 
	}
	public function del($id){
		return $this->allowField(true)->save(['status'=>1],['id'=>$id]);
	}

}