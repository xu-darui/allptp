<?php

namespace app\admin\model;

use app\common\model\Dispute as DisputeModel;
use app\common\model\Image as ImageModel;
use app\common\model\Kind as KindModel;
use think\Db;

/**
 * 纠纷模型
 * Class Dispute
 * @package app\admin\model
 */
class Dispute extends DisputeModel
{
	public function dispute_list(){
		$dispute_model=new DisputeModel;
		$input=input();
		$where=[];
		$where_kind='';
		if(array_key_exists('keywords',$input)&&$input['keywords']!==''){
			$where['a.content|b.title']=['like','%'.$input['keywords'].'%'];
		}
		if(array_key_exists('kind_id',$input)){
			$where_kind="find_in_set(".$input['kind_id'].",f.kind_id)";
			
		}if(array_key_exists('status',$input)){
			$where['a.status']=$input['status'];
		} 
		$data=Db::name('dispute')
		->alias('a')
		->field("a.dispute_id,a.content,FROM_UNIXTIME(a.create_time, '%Y-%c-%d') as create_time ,a.status,a.activity_id,a.order_id,b.title,FROM_UNIXTIME(b.pay_time, '%Y-%c-%d') as pay_time ,e.name as option_name,c.family_name,c.middle_name,c.name,c.m_code,c.mobile,c.isplanner,c.isvolunteer,d.family_name as dis_family_name,d.middle_name as dis_middle_name,d.name as dis_name,d.m_code as dis_m_code,d.mobile as dis_mobile,f.kind_id")
		->join('order b','a.order_id=b.order_id','INNER') 
		->join('user c','a.user_id=c.user_id','LEFT')
		->join('user d','a.dis_user_id=d.user_id','LEFT')
		->join('question_option e','a.option_id=e.option_id','LEFT')
		->join('activity f','a.activity_id=f.activity_id','INNER')
		->where($where)
		->where($where_kind)
		->paginate(10, false, ['query' => ["page"=>array_key_exists('page',$input)?$input['page']:1]])->toArray(); 
		if($data){
			$kind_model=new KindModel;
			$data=$kind_model->addkind_array($data); 
			foreach($data as $key=>$value){
				$value['image']=ImageModel::where(['flag'=>5,'table_id'=>$value['dispute_id']])->order('sort asc')->select();
				$data[$key]=$value;
			}
		} 
		return $data;
		
	}
}