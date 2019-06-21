<?php

namespace app\home\controller;
use app\common\model\Image as ImageModel;  
use app\home\model\Image as ImagetModel;  
use think\Request;
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

	public function upload_many(){  
		$image_model=new ImageModel;
		return $this->renderSuccess($image_model->upload_many());
	} 
  	public function upload_many1(){  
		$image_model=new ImageModel;
		return $this->renderSuccess($image_model->upload_many1());
	}   
	public function upload_kindeditor(){  
		$image_model=new ImageModel;
		return $image_model->upload_test();
	}   
	public function getqrcode($url,$level=3,$size=4)
    {
              Vendor('phpqrcode.phpqrcode');
              $errorCorrectionLevel =intval($level) ;//容错级别 
              $matrixPointSize = intval($size);//生成图片大小 
			  $domain= request()->domain(); 
             //生成二维码图片 
              $object = new \QRcode(); 
			  $name=md5(time().round(1,99999));
			  $filename = './uploads/qrcode/'.$name.'.png';
			  $path = '/allptp/web/uploads/qrcode/'.$name.'.png';
			   $object::png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
			  $QR = $filename;        //已经生成的原始二维码图片文件
			  $QR = imagecreatefromstring(file_get_contents($QR));
			  //输出图片 
			  return $this->renderSuccess(["src"=>$domain.$path]); 
    }
	
	public function upload_base64(){
		$image_model=new ImageModel;
		return $this->renderSuccess($image_model->uploads_base64());
	}
	/* 
	public function test($url){
		$TEST_MODEL=new ImagetModel;
		$TEST_MODEL->save_headimageurl($url);
	} */

	
	


}
