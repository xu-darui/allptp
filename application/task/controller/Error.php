<?php 
namespace app\task\controller;   
use think\Request;
/**
 * 404报错页面
 * Class Error
 * @package app\task\controller
 */
class Error extends Controller
{
	public function index($message){ 
		echo '<p style="text-align:center;">
				<img style="width:300px;height:300px;" src="'.Request()->domain().'/allptp/web/uploads/image/error.png" alt="" /> 
			</p>
			<h1 style="text-align:center;">
				<strong><span style="color:#999999;background-color:#FFFFFF;font-family:&quot;font-size:24px;"><span style="color:#E56600;font-size:32px;">系统提示：</span><span style="font-size:24px;">'.$message.'</span></span></strong> 
			</h1>';
		exit;
	} 
}