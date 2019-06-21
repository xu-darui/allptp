<?php

namespace app\admin\controller;
use app\common\model\Image as ImageModel; 
use app\admin\model\Banner as BannerModel;
use \think\Validate;

/**
 * 轮滚
 * Class Banner
 * @package app\admin\controller
 */
class Banner extends Controller
{
	public function add(){
		$data=input(); 
		$validate = new Validate([
			'image_id'  => 'require',
			'type' => 'require',
			'url' => 'require',
			'flag' => 'require',
		],[
			'image_id.require'=>'请选择图片',
			'type.require'=>'请选择布局样式', 
			'url.require'=>'请输入跳转链接', 
			'flag.require'=>'请选择轮滚放置位置', 
		]); 
		if (!$validate->check($data)){ 
			return $this->renderError($validate->getError());
		}
		$banner_model=new BannerModel;
		if($banner_model->banner_sava($data)){
			return $this->renderSuccess('保存成功');
		}else{
			return $this->renderError('保存失败');
		}
	}
	public function bannerlist($flag,$page=1){
		$banner_model=new BannerModel;
		return $this->renderSuccess($banner_model->bannerlist($flag,$page));
	}
	public function detail($id){
		$banner_model=new BannerModel;
		return $this->renderSuccess($banner_model->detail($id));
	}
	public function del($id){
		$banner_model=new BannerModel;
		if($banner_model->del($id)){
			return $this->renderSuccess('删除成功');
		}else{
			return $this->renderError('删除失败');
		}
		
	}
	public function upload_save(){
		$image_model=new ImageModel;
		$upload= $image_model->upload();
		$banner_model=new BannerModel;
		if($be_data=$banner_model->find(['flag'=>1])){
			$data['id']=$be_data['id'];
		}
		$data['flag']=1;
		$data['image_id']=$upload['image_id']; 
		$banner_data=$banner_model->banner_sava($data);
		if($banner_data){
			return $this->renderSuccess($upload);
		}else{
			return $this->renderError('保存失败');
		}
	}
}