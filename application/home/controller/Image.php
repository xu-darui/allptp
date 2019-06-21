<?php
namespace app\home\controller;  
use app\home\model\Image as ImageModel; 
use think\Cache;
/**
 * 图片操作
 * Class Image
 * @package app\home\controller
 */
class Image extends Controller
{
	public function image_delete($image_id,$table_id,$flag){
		$image_model=new ImageModel;
		if($flag==1){
			if($image_model->image_delete(['image_id'=>$image_id,'table_id'=>$table_id],['table_id'=>0,'flag'=>0,'sort'=>0])){
				return $this->renderSuccess('删除成功');
			}else{
				return $this->renderError('删除失败');	
			}
		}
		
	}
	
}