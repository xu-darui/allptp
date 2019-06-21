<?php

namespace app\common\model;

use think\Request; 

/**
 * 导航栏
 * Class Menu
 * @package app\common\model
 */
class Menu extends BaseModel
{
	 function menutree($pro_list){
  $json_data = array(); 
  //$sql="select * from oa_group order by id desc";
  if($pro_list==''){
	  $data = $this->order('id desc,sort asc')->column('id,top_id,name,image,url,subscript','id');
  }else{
	  $data = $this->order('id desc,sort asc')->where(["id"=>["in",$pro_list]])->column('id,top_id,name,image,url,subscript','id');  
  }
    
   // $data = Db::name('kind')->order('id desc')->select()->toArray();  
  foreach($data as $v){ 
      $json_data[$v['id']] = isset($json_data[$v['id']]) ? $v + $json_data[$v['id']] : $v; 
      if($v['top_id'] != 0){
          $json_data[$v['top_id']]['children'][] = $json_data[$v['id']];
          unset($json_data[$v['id']]);
      }
      //$json_data[$v['id']] = $v;
  }
  ksort($json_data);
  return $json_data;
}
	
}