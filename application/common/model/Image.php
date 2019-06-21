<?php

namespace app\common\model; 
use think\Request;
use app\common\exception\BaseException;
/**
 * 图片模型
 * Class Image
 * @package app\common\model
 */
class Image extends BaseModel
{
	
	protected $hidden = [
        'create_time' ,
		'sort'
    ];
	public function upload(){   
		// 获取表单上传文件 
		$file= request()->file("file"); 
		//$file=input('file'); 
		//$file=request()->param(); 
		//pre($file);
		$path='./uploads'; 
		$save_path='/allptp/web/uploads/'; 
		if($file){
			// 移动到框架应用根目录/web/uploads/ 目录下 
			$info = $file->validate(['ext'=>'jpg,png,gif,mp4,avi,jpeg,3GP'])->move($path);  
			//$image->text("allptp",ROOT_PATH.'web/statics/font/Houschka Rounded Alt Extra Bold Italic.ttf',30,'#FF5A5F',Image::WATER_SOUTHEAST,-10);
			//$image->water('./statics/images/logo.png',Image::WATER_NORTHWEST,50); 
			if($info){
				// 成功上传后 获取上传信息
				// 输出 jpg 
				$fileextens= $info->getExtension();  
				$file_save_path= $info->getSaveName();  
				$themb_url_array=explode("\\",$file_save_path); 
				$domain= request()->domain(); 
				$themb_url="";
				if(in_array($fileextens,["jpg","png","gif","jpeg","pjpeg"])){ 
				$image = \think\Image::open("./uploads/".$file_save_path); 
					//$image = \think\Image::open(request()->file('file'));
					//如果是图片  压缩
					$param=request()->param();
					$themb_url=$themb_url_array[0]."/"."themb_".$themb_url_array[1];
					if(array_key_exists('iswater',$param)&&$param['iswater']){
						//不打水印
						$image->save($path."/".$file_save_path); 
						$image->thumb(500,500)->save($path."/".$themb_url);
					}else{
						// 自适应logo图 
						$logo_width = $image->width()/7;
						$logo_height = $image->height()/11; 
						// 临时水印路径
						$water_url='water/temp_'.uniqid().'.png';
						$temp_logo = $path.'/'.$water_url;
						\think\Image::open('./water.png')->thumb($logo_width, $logo_height,\think\Image::THUMB_SCALING,true)->save($temp_logo); 
						$image->water($temp_logo,9,100)->save($path."/".$file_save_path); 
						$image->water($temp_logo,9,100)->thumb(500,500)->save($path."/".$themb_url);
						// 销毁临时水印
						file_exists($temp_logo) && unlink($temp_logo);
					} 
					$themb_url=$save_path.$themb_url;
				}  
				$data=["domain"=>$domain,"image_url"=>$save_path.$themb_url_array[0]."/".$themb_url_array[1],"themb_url"=>$themb_url,"extension"=>$fileextens];
				if($this->allowField(true)->save($data)){
					$data["image_id"]=$this->image_id;
					return $data;
				}else{ 
					throw new BaseException(['code' => 0, 'msg' => '上传失败']);
				}			
			}else{
				// 上传失败获取错误信息
				throw new BaseException(['code' => 0, 'msg' => $file->getError()]); 
			} 
		}else{
			throw new BaseException(['code' => 0, 'msg' => '上传失败']);  
		}	 
	}	
 /**
     * 保存图片
     */
    public function uploads_base64()
    {
		//$param['image']=base64_encode(file_get_contents("https://www.allptp.cn/web/uploads/20190522/1195960f51500ecfb7244b41e7e6372a.jpg")); 
        // $file = base64_decode(request()->file('image'));//图片
        $param = input('file');

		$up_dir='./uploads/'.date('Ymd');
        //$up_dir = ROOT_PATH . 'public' . DS . 'uploads/';//存放在当前目录的upload文件夹下 
		if(!is_dir($up_dir)){
			mkdir($up_dir, 0777);
		}
        $base64_img = trim($param['file']); 
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','png'))){
				$file_save_path=MD5(time().rand(1,999999));
                $new_file = $up_dir.$file_save_path.'.'.$type;
                if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
					pre($new_file);
                    $img_path = str_replace('../../..', '', $new_file);
                    return  $img_path;
                }else{
                    throw new BaseException(['code' => 0, 'msg' => '图片上传失败']); 
                }
            }else{
                //文件类型错误
               throw new BaseException(['code' => 0, 'msg' => '图片上传类型错误']); 
            }
        }else{
			 throw new BaseException(['code' => 0, 'msg' => '格式错误']); 
		}
        
    }
	
	public function upload_many(){ 
		
		//$files=request()->file("file"); 	
		$files=input(); 	
		// 获取表单上传文件 
		file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . "请求成功". PHP_EOL, FILE_APPEND); 
		file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . json_encode($files). PHP_EOL, FILE_APPEND); 
//pre($files);		
		$path='./uploads'; 
		$save_path='/allptp/web/uploads/';
		if($files){
			foreach($files as $file){
				// 移动到框架应用根目录/web/uploads/ 目录下 
				$info = $file->validate(['ext'=>'jpg,png,gif,mp4,avi'])->move($path);  
				//$image->text("allptp",ROOT_PATH.'web/statics/font/Houschka Rounded Alt Extra Bold Italic.ttf',30,'#FF5A5F',Image::WATER_SOUTHEAST,-10);
				//$image->water('./statics/images/logo.png',Image::WATER_NORTHWEST,50); 
				if($info){
					// 成功上传后 获取上传信息
					// 输出 jpg 
					$fileextens= $info->getExtension();  
					$file_save_path= $info->getSaveName();  
					$themb_url_array=explode("\\",$file_save_path); 
					$domain= request()->domain(); 
					$themb_url="";
					if(in_array($fileextens,["jpg","png","gif"])){ 
					$image = \think\Image::open("./uploads/".$file_save_path);
						//$image = \think\Image::open(request()->file('file'));
						//如果是图片  压缩
						$themb_url=$themb_url_array[0]."/"."themb_".$themb_url_array[1];
						$image->thumb(200,200)->save($path."/".$themb_url);
						$themb_url=$save_path.$themb_url;
					}  
					$data=["domain"=>$domain,"image_url"=>$save_path.$themb_url_array[0]."/".$themb_url_array[1],"themb_url"=>$themb_url,"extension"=>$fileextens];
					if($this->allowField(true)->save($data)){
						$data["image_id"]=$this->image_id;
						$datas[]=$data;
						return $datas;
					}else{ 
						throw new BaseException(['code' => 0, 'msg' => '上传失败']);
					}			
				}else{
					// 上传失败获取错误信息
					throw new BaseException(['code' => 0, 'msg' => $file->getError()]); 
				} 
			}
		}else{
			throw new BaseException(['code' => 0, 'msg' => '上传失败']);  
		}	 
	}	
	public function upload_many1(){
		$files= request()->file("file"); 
		//return [1,2];
//pre(input());		
		// 获取表单上传文件 
		file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . "请求成功". PHP_EOL, FILE_APPEND);
		//$files= ["file:///storage/emulated/0/Pictures/images/image-29e2a31a-4c75-4f2f-a9e1-b972951b8bc0.jpg","file:///storage/emulated/0/Pictures/images/image-794df4c8-890a-497f-8703-072dfa85967d.jpg"]; 
		//file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $files. PHP_EOL, FILE_APPEND);
		//$files=json_decode($files,true);	
		//file_put_contents("./log.txt" ,'[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $a. PHP_EOL, FILE_APPEND);
//pre($files);		
		$path='./uploads'; 
		$save_path='/allptp/web/uploads/';
		if($files){
			foreach($files as $file){
				// 移动到框架应用根目录/web/uploads/ 目录下 
				$info = $file->validate(['ext'=>'jpg,png,gif,mp4,avi'])->move($path);  
				//$image->text("allptp",ROOT_PATH.'web/statics/font/Houschka Rounded Alt Extra Bold Italic.ttf',30,'#FF5A5F',Image::WATER_SOUTHEAST,-10);
				//$image->water('./statics/images/logo.png',Image::WATER_NORTHWEST,50); 
				if($info){
					// 成功上传后 获取上传信息
					// 输出 jpg 
					$fileextens= $info->getExtension();  
					$file_save_path= $info->getSaveName();  
					$themb_url_array=explode("\\",$file_save_path); 
					$domain= request()->domain(); 
					$themb_url="";
					if(in_array($fileextens,["jpg","png","gif"])){ 
					$image = \think\Image::open("./uploads/".$file_save_path);
						//$image = \think\Image::open(request()->file('file'));
						//如果是图片  压缩
						$themb_url=$themb_url_array[0]."/"."themb_".$themb_url_array[1];
						$image->thumb(200,200)->save($path."/".$themb_url);
						$themb_url=$save_path.$themb_url;
					}  
					$data=["domain"=>$domain,"image_url"=>$save_path.$themb_url_array[0]."/".$themb_url_array[1],"themb_url"=>$themb_url,"extension"=>$fileextens];
					if($this->allowField(true)->save($data)){
						$data["image_id"]=$this->image_id;
						$datas[]=$data;
						return $datas;
					}else{ 
						throw new BaseException(['code' => 0, 'msg' => '上传失败']);
					}			
				}else{
					// 上传失败获取错误信息
					throw new BaseException(['code' => 0, 'msg' => $file->getError()]); 
				} 
			}
		}else{
			throw new BaseException(['code' => 0, 'msg' => '上传失败']);  
		}	 
	}
	   /**
     * 单图上传
     * @param   string  $dir        保存在upload文件夹下的目录
     * @param   bool    $is_thumb   是否开启压缩图
     * @param   integer $width      缩略图最大宽度
     * @param   integer $height     缩略图最大高度
     * @param   int      $type      缩略图裁剪类型
     * @return array
     * @throws  Error
     */
    function _upload( $dir="api",$is_water = false,$is_thumb=true,$width=1024,$height=1024,$type=1)
    {
        // 简单校验
        $validate = [
            'size' => 1567800,
            'ext' => 'jpg,gif,png,bmp,jpeg,JPG'
        ];
        // 上传
        if ($file = request()->file('file')) {
            // 保存路径
            $savePath = ROOT_PATH . 'upload' . DS . $dir . DS . date('Ym') . DS . date('d') . DS ;
            // 访问路径
            $_file_path = ROOT_DIR . 'upload/' . $dir . '/' . date('Ym/d/');
            // 创建目录
            !is_dir($savePath) && mkdir(iconv("UTF-8", "GBK", $savePath),0777,true);
            // 移动文件
            $info = $file->rule('uniqid')->validate($validate)->move($savePath);
 
            if ($info) {
 
                // 压缩图片
                $savePath = $savePath . $info->getSaveName();
                $is_thumb && \think\Image::open($savePath)->thumb($width, $height, $type)->save($savePath);
 
                // 添加水印
                if(true == $is_water){
                    // 自适应logo图
                    $image = \think\Image::open($savePath);
                    $logo_width = $image->width()/4;
                    $logo_height = $image->height()/4;
                    // 原水印路径
                    $logo_img = config('api.water_logo');
                    // 临时水印路径
                    $temp_logo = ROOT_PATH.'upload/temp_'.uniqid().'.png';
                    \think\Image::open($logo_img)->thumb($logo_width, $logo_height)->save($temp_logo);
 
                    // 加水印
                    $image->water($temp_logo,9,60)->save($savePath);
                    // 销毁临时水印
                    file_exists($temp_logo) && unlink($temp_logo);
                }
 
                $_file = $_file_path . str_replace('\\', '/', $info->getSaveName());
 
                return [
                    'code' => 1,
                    'msg' => '上传成功',
                    'data' => $_file
                ];
 
            } else {
                // 上传失败获取错误信息
                throw new Error($file->getError(), 47002);
            }
        } else {
            throw new Error('上传失败，请检查参数', 47001);
        }
	}
}