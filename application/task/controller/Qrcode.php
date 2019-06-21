<?php 
namespace app\task\controller;  
use think\Loader;
use wxpay\lib\phpqrcode;
//Loader::import('wxpay.lib.phpqrcode'); 
/**
 * 支付成功异步通知接口
 * Class Notify
 * @package app\api\controller
 */
class Qrcode extends Controller
{
	public function imageurl(){
	Vendor('phpqrcode.phpqrcode');
	$url = urldecode($_GET["data"]); 
	if(substr($url, 0, 6) == "weixin"){
		$object = new \QRcode();
		return $object::png($url, false, 3, 4, 2);
	}else{
		 header('HTTP/1.1 404 Not Found');
	}
	}
}