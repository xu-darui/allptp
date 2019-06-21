<?php

namespace app\home\model;

use app\common\model\Banner as BannerModel;

/**
 * 轮播
 * Class Banner
 * @package app\store\model
 */
class Banner extends BannerModel
{
	public function bannerlist($flag){
		return $this->with('image')->where(['status'=>0,'flag'=>$flag])->select();
	}
}