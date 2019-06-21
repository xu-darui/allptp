<?php

namespace app\home\model;
use app\common\model\Translate as TranslateModel; 
use app\common\exception\BaseException;
/**
 * 志愿者报名
 * Class Enroll
 * @package app\store\model
 */
class Translate extends TranslateModel
{
	public static function detail($translate_id){
		return TranslateModel::where(['translate_id'=>$translate_id])->find();
	}
	public function save_translate($data){ 
		if(array_key_exists('translate_id',$data)&&$data['translate_id']>0){
			 $this->allowField(true)->save($data,['translate_id'=>$data['translate_id']]);
			 return $data['translate_id'];
		}else{
			 $this->allowField(true)->save($data);
			 return $this->translate_id;
		}
	}
	
	public function del_translate($translate_id){
		return $this->where(['translate_id'=>$translate_id])->update(['status'=>1]);
	}
	
	public function translate_list($where,$orderby,$user_id){
		$data= $this->with(['user.headimage','praise'=>function($query)use($user_id){$query->where(['user_id'=>$user_id,'status'=>0]);},'report'=>function($query)use($user_id){$query->where(['user_id'=>$user_id,'status'=>0]);}])->where($where)->order($orderby)->paginate(10, false, ['query' => ["page"=>input('page')==''?1:0]]);
		foreach($data as $key=>$value){
			$data[$key]['is_praise']=0; 
			$data[$key]['is_report']=0; 
			if($value['praise']) $data[$key]['is_praise']=1;
			if($value['is_report']) $data[$key]['is_report']=1;
			unset($data[$key]['praise']);
			unset($data[$key]['report']);
		}
		return $data;
		
	}
	
	public function translate_detail($translate_id){
		
		//return $this->
	}
}