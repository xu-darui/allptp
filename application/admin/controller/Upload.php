<?php

namespace app\admin\controller;
use app\common\model\Image as ImageModel; 
 


/**
 * 文件库管理
 * Class Upload
 * @package app\store\controller
 */
class Upload extends Controller
{
    public function upload(){  
		$image_model=new ImageModel;
		return $this->renderSuccess($image_model->upload());
	} 
	 

}
