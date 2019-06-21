<?php

namespace app\home\controller;
use app\common\model\Country;  
use think\Db;  

/**
 * 浏览器
 * Class Browse
 * @package app\store\controller
 */
class Browse extends Controller
{ 
	public function index(){
		if(isset($_SERVER["HTTP_CLIENT_IP"]) and strcasecmp($_SERVER["HTTP_CLIENT_IP"], "unknown")){
			$ip= $_SERVER["HTTP_CLIENT_IP"];
		}
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) and strcasecmp($_SERVER["HTTP_X_FORWARDED_FOR"], "unknown")){
			$ip= $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		if(isset($_SERVER["REMOTE_ADDR"])){
			$ip= $_SERVER["REMOTE_ADDR"];
		} 
		if($ip){
			if(!Db::name('browse')->where(['ip'=>$ip])->count('id')){
				$url='https://apis.map.qq.com/ws/location/v1/ip?ip='.$ip.'&key=5BKBZ-QYEKR-7YOWW-WTALN-IOXV7-SGFCV';
				$result = curl($url,[]);
				$result = json_decode($result,true); 
				if($result){
					$country=Country::country();
					if(in_array($result['result']['ad_info']['nation'],$country)){
						$country_id=array_search($result['result']['ad_info']['nation'],Country::country());  
					}else{
						$country_id=-1;
					}
					Db::name('browse')->insert(['ip'=>$ip,'country_id'=>$country_id,'lng'=>$result['result']['location']['lng'],'lat'=>$result['result']['location']['lat'],'create_time'=>time(),'browse_type'=>input('browse_type')]);
					
				}
				
			}
		}
		
			
	}

}