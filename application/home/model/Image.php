<?php

namespace app\home\model;

use app\common\model\Image as ImageModel;

/**
 * 保存图片
 * Class Question
 * @package app\store\model
 */
class Image extends ImageModel
{
	public function save_image($data,$table_id,$flag){
		$this->where(['table_id'=>$table_id,'flag'=>$flag])->update(['table_id'=>0,'flag'=>0]);
		$sort=0;
		
		foreach($data as $key=>$value){ 
			$data[$key]['image_id']=$value['image_id']; 
			$data[$key]['table_id']=$table_id;
			$data[$key]['flag']=$flag; 
			$data[$key]['sort']=$sort; 
			$sort++;
		}
			
			$this->allowField(true)->saveAll($data);		
				
	}
	
	public function image_delete($where,$data){
		return $this->where($where)->update($data);
	}
	
	public function save_headimageurl($url){
		$headimgurl_array=parse_url($url);  
		$image['domain']=$headimgurl_array['scheme'].'://'.$headimgurl_array['host'];
		$image['image_url']=$image['themb_url']=$headimgurl_array['path'].(array_key_exists('query',$headimgurl_array)?('?'.$headimgurl_array['query']):'').(array_key_exists('fragment',$headimgurl_array)?('#'.$headimgurl_array['fragment']):'');
		$data['extension']='jpg';
		$image_model=new ImageModel;
		$image_model->allowField(true)->save($image);
		return $image_model->image_id;
		
	}

}