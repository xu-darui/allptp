<?php

namespace app\task\controller;  
use app\common\model\Config as ConfigModel;  
/**
 * 后台控制器基类
 * Class BaseController
 * @package app\admin\controller
 */
class Controller extends \think\Controller
{
	protected function config(){
		$config=ConfigModel::get(['key'=>'config']);
		return (json_decode($config['values'],true)); 
	}
}