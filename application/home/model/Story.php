<?php

namespace app\home\model;

use app\common\model\Story as StoryModel;
use app\home\model\Image as ImageModel;
use app\common\model\Collection as CollectionModel;
use app\common\model\Praise as PraiseModel;
use app\common\model\Search as SearchModel;
use app\common\model\Kind as KindModel;
use app\common\model\Attention as AttentionModel;
use app\common\model\Report as ReportModel;
use think\Db;

/**
 * 保存故事
 * Class Story
 * @package app\store\model
 */
class Story extends StoryModel
{
	public function save_story($data){  
		if($data['kind_id']){
			$kind=KindModel::get(['kind_id'=>$data['kind_id'],'status'=>0]);  
			$data['kind_id']=$kind['path']==''?$data['kind_id']:$kind['path'].','.$data['kind_id'];
		}   
		if(array_key_exists('story_id',$data)){
			 $this->allowField(true)->save($data,['story_id'=>$data['story_id']]);
			 $story_id=$data['story_id'];
		}else{
			 $this->allowField(true)->save($data);
			 Db::name('user')->where(['user_id'=>$data['user_id']])->setInc('story_num');
			 $story_id= $this->story_id; 
		} 
		if(array_key_exists('image',$data)&&$data['image']){
			if(array_key_exists('isapp',$data)&&$data['isapp']) $data['image']=json_decode(html_entity_decode($data['image']),true);
			$image_model=new ImageModel;
			$image_model->save_image($data['image'],$story_id,2);
			
		}
		return $story_id;
	}
	public function get_story($where,$user_id){ 
	$story_model=new StoryModel;
	$kind_model=new KindModel; 
	$story=$story_model 
		->with(['user.headimage','image','cover','kindpath'])
		->where($where)
		->find()
		->toArray();   
	$story['kind']=$kind_model->field('kind_id,kind_name')->where(['kind_id'=>['in',$story['kind_id']]])->select();
	$collection_count=CollectionModel::where(['flag'=>2,'status'=>0,'table_id'=>$story['story_id'],'user_id'=>$user_id])->count('collection_id');	
	$story['is_collection']=$collection_count>0?1:0;
	$praise_count=PraiseModel::where(['flag'=>1,'status'=>0,'table_id'=>$story['story_id'],'user_id'=>$user_id])->count('praise_id');	
	$story['is_praise']=$praise_count>0?1:0;
	$story['user']['is_attention']=AttentionModel::where(['status'=>0,'att_user_id'=>$story['user']['user_id'],'user_id'=>$user_id])->count('attention_id');
	$story['is_report']=ReportModel::where(['status'=>0,'table_id'=>$story['story_id'],'flag'=>3,'user_id'=>$user_id])->count('report_id');
	
	 return $story;
	
	}
	public function del_story($where,$user_id){
		 Db::name('user')->where(['user_id'=>$user_id])->setDec('story_num');
		return $this->allowField(true)->save(['status'=>1],$where);
		
	}

	public function  story_list($keywords,$page,$sort,$kind_id,$country ,$province ,$city ,$region,$user_id,$write_key=1){ 
			switch($sort){
				case 2; 
					$order="praise_num desc";
					break;
				case 3; 
					$order="collection_num desc";
					break;
				case 5;
					$order="leaving_num desc";
					break;
				default:
					$order="story_id desc";
			} 
			$where['status']=0; 
			$where_kind='';		
			$where_keywords='';
			$where_keywords_kind='';			
			if($country){
				$where['country']=['like','%'.$country.'%'];
			}
			if($province){
				$where['province']=['like','%'.$province.'%'];
			}
			if($city){
				$where['city']=['like','%'.$city.'%']; 
			}
			if($region){
				$where['region']=['like','%'.$region.'%'];
			}
			if($kind_id){
				$where_kind="find_in_set($kind_id,kind_id)";
			} 
			$story_model=new StoryModel;
			if($keywords){
			$kind_array=Db::name('kind')->where(['kind_name'=>['like','%'.$keywords.'%'],'status'=>0])->column('kind_id');
				if($kind_array){
					$kind_count=count($kind_array)-1;  
					foreach($kind_array as $key=>$kind_id){ 
						if($kind_count==$key){
							$where_keywords_kind=$where_kind."find_in_set($kind_id,kind_id)";
						}else{
							$where_keywords_kind=$where_kind."find_in_set($kind_id,kind_id) or ";
						} 	
					}   
					 
				} 			
				$where_keywords="CONCAT(IFNULL(country,''),IFNULL(province,''),IFNULL(city,''),IFNULL(region,'')) like '%".$keywords."%' or title like '%".$keywords."%' or content like '%".$keywords."%' or address like '%".$keywords."%'";
				if($where_keywords_kind){
					$where_keywords=$where_keywords.' or '.$where_keywords_kind;
				}
				//$where["title|content|address"]=['like','%'.$keywords.'%']; 
				if($user_id&&$write_key){
					$search_model=new SearchModel;
					$search_model->save(['flag'=>3,'keywords'=>$keywords,'user_id'=>$user_id]);
				}
			} 		
			$data=$story_model
				->with(['cover','user.headimage'])
				->withCount(['collection'=>function($query)use($user_id){$query->where(['user_id'=>$user_id]);}])
				->withCount(['praise'=>function($query)use($user_id){$query->where(['user_id'=>$user_id]);}])
				->where($where)
				->where($where_keywords)
				->where($where_kind)
				->order($order)
				->paginate(10, false, ['query' => ["page"=>$page]]); 
			if($data){
				$kind_model=new KindModel;
				$data=$kind_model->addkind($data);
				foreach($data as $key=>$value){
					$data[$key]['is_collection']=$value['collection_count']>0?1:0;
					$data[$key]['is_praise']=$value['praise_count']>0?1:0;
					unset($data[$key]['collection_count']);
					unset($data[$key]['praise_count']);
				}
			} 
			return $data; 
	}
	
		public function detail($where){  
			return StoryModel::get($where);
		}
	
		public function similar($kind_array,$story_id,$user_id){
		$where='';
		$kind_count=count($kind_array)-1; 
		
		foreach($kind_array as $key=>$kind_id){ 
			if($kind_count==$key){
				$where=$where."find_in_set($kind_id,kind_id)";
			}else{
				$where=$where."find_in_set($kind_id,kind_id) or ";
			} 	
		}  
		$data=$this->field('story_id,title,address,kind_id,cover_image,user_id,leaving_num,praise_num')
			->with(['cover','user.headimage'])
			->withCount(['collection'=>function($query)use($user_id){$query->where(['user_id'=>$user_id]);}])
			->withCount(['praise'=>function($query)use($user_id){$query->where(['user_id'=>$user_id]);}])
			->where($where)
			->where(['status'=>0])
			->where(['story_id'=>['neq',$story_id]])
			->order('praise_num desc')
			->limit(10)
			->select(); 
		if($data){
			$kind_model=new KindModel;
			$data=$kind_model->addkind($data);
			foreach($data as $key=>$value){
					$data[$key]['is_collection']=$value['collection_count']>0?1:0;
					$data[$key]['is_praise']=$value['praise_count']>0?1:0;
					unset($data[$key]['collection_count']);
					unset($data[$key]['praise_count']);
			}
		}
		return $data;
	}
	
	public function create_list($keywords,$page,$sort,$kind_id,$user_id){
		switch($sort){
				case 1; 
					$order="create_time desc";
					break;
				case 2; 
					$order="praise_num desc";
					break;
				case 3; 
					$order="collection_num desc";
					break;
				case 5;
					$order="leaving_num desc";
					break;
				default:
					$order="story_id desc";
			} 
			$where['status']=0; 
			$where['user_id']=$user_id; 
			$where_kind='';		 
			if($kind_id){
				$where_kind="find_in_set($kind_id,kind_id)";
			}
			if($keywords){
				$where["title|content"]=['like','%'.$keywords.'%']; 
			}
			$story_model=new StoryModel;
			$data=$story_model->alias('a')->with(['cover','user.headimage'])->where($where)->where($where_kind)->order($order)->paginate(10, false, ['query' => ["page"=>$page]]);
			if($data){
				$kind_model=new KindModel;
				$data=$kind_model->addkind($data);
			}
			return $data;
	}
	
	public function create_list_all($where){ 
		$data= $this->field("story_id,country,province,city,region,address,title,cover_image,kind_id,create_time")->with(['cover'])->where($where)->order("create_time desc")->select();
		if($data){
			$kind_model=new KindModel;
			$data=$kind_model->addkind($data);
		}
		 return $data;
	}
	
	public function popular_story_list(){
		return  Db::name('story')->field("count(story_id) as create_num,sum(praise_num) as praise_num,sum(collection_num) as collection_num,city")->group('city')->order('create_num desc ,praise_num desc ,collection_num desc')->where(['status'=>0])->select();
	}
	public function dynamic_story($where){
	$story_model=new StoryModel; 
	return $story_model 
		->field('story_id,title,content,collection_num,leaving_num,praise_num,country,province,city,region,forward_num,user_id')
		->with(['image'])
		->where($where)
		->find(); 
	}
	
	public function save_story_one($data,$where){
		return $this->where($where)->save($data);
	}
	
}