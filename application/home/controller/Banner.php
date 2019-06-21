<?php
namespace app\home\controller; 
use app\home\model\Banner as BannerModel;;   
/**
 * 轮滚
 * Class Banner
 * @package app\admin\controller
 */
class Banner extends Controller
{
	public function bannerlist($flag){
		$banner_model=new BannerModel;
		return $this->renderSuccess($banner_model->bannerlist($flag));
	}
	

}