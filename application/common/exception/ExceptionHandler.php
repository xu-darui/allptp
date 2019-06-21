<?php

namespace app\common\exception;

use think\exception\Handle;
use think\Log;
use Exception;
use think\Request;
/**
 * 重写Handle的render方法，实现自定义异常消息
 * Class ExceptionHandler
 * @package app\common\library\exception
 */
class ExceptionHandler extends Handle
{
    private $code;
    private $message;

    /**
     * 输出异常信息
     * @param Exception $e
     * @return \think\Response|\think\response\Json
     */
    public function render(Exception $e)
    {
        if ($e instanceof BaseException) {
            $this->code = $e->code;
            $this->message = $e->message;
			
        } else {
            if (config('app_debug')) {
                return parent::render($e);
            } 
			$this->code = 99;
            $this->message = $e->getMessage() ?: '很抱歉，服务器内部错误';
            $this->recordErrorLog($e);
        }
		if($this->code==404){ 
			//跳转页面报错  
			header("Location:".Request()->domain().Request()->root()."/Error?message=".$e->message);exit;
		}else{ 
			return json(['msg' => $this->message, 'code' => $this->code]);
		} 
    }

    /**
     * 将异常写入日志
     * @param Exception $e
     */
    private function recordErrorLog(Exception $e)
    {
        Log::record($e->getMessage(), 'error');
        Log::record($e->getTraceAsString(), 'error');
    }

}
