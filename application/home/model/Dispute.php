<?php

namespace app\home\model;  
use app\common\model\Dispute as DisputeModel; 
use app\home\model\Image as ImageModel; 
use app\common\model\Activity as ActivityModel; 
use think\Db;
/**
 * 举报
 * Class Report
 * @package app\store\model
 */
class Dispute extends DisputeModel
{
	public function save_dispute($data){ 
		$data['dis_user_id']=ActivityModel::where(['activity_id'=>$data['activity_id']])->value('user_id');
		$this->allowField(true)->save($data);
		$dispute_id=$this->dispute_id;
		if(array_key_exists('image',$data)){
			$image_model=new ImageModel;
			$image_model->save_image($data['image'],$dispute_id,5); 
		}
		return true;
		
	}
}