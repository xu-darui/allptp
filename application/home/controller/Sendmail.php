<?php

namespace app\home\controller;    
use app\common\model\Sendmail as SendmailModel;  
use think\Request;
use think\Cache;
/**
 * 发送邮箱
 * Class Sendmail 
 */
class Sendmail extends Controller
{
/**
     * tp5邮件
     * @param
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function email($toemail,$flag) {
		$sendmail_model=new SendmailModel; 
		$code=rand(111111,999999);
		Cache::set(($toemail),$code,600);
        if($sendmail_model->regiseter($toemail,$flag,$code)){
			return $this->renderSuccess("发送成功");
		}else{
			return $this->renderError("发送失败");
		}
    }

}