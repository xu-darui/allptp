<?php

namespace app\common\model;

use think\Cache;
use think\Db;

/**
 * 发送邮件
 * Class Sendmail
 * @package app\common\model
 */
class Sendmail extends BaseModel
{
	protected $header='
				<p style="text-align:center;">
					<img src="https://www.allptp.cn/web/uploads/20190527/3938d47ea4f4edff628e70b3be3fb7ec.png" alt="" /> 
				</p>
				<p>
					<br />
				</p>';	
	protected $footer='
			<p style="text-align:left;">
				<span style="font-size:18px;line-height:2.5;color:#666666;"><br />
			</span> 
			</p>
			<p>
				<br />
			</p>
			<p style="text-align:center;">
				<span style="color:#333333;font-size:24px;"><span style="font-size:24px;color:#333333;"><strong>登陆allptp</strong></span></span> 
			</p>
			<p style="text-align:center;">
				<span style="font-size:24px;"><span style="font-size:24px;">获取更多精彩内容</span></span> 
			</p>
			<p style="text-align:left;">
				<span><span style="font-size:18px;"><br />
			</span></span> 
			</p>
			<p style="text-align:center;">
				<span><span style="font-size:18px;"><a href="https://www.allptp.cn/#/" target="_blank"></a><a href="https://www.allptp.cn/#/" target="_blank"></a><a href="https://www.allptp.cn/#/" target="_blank"><img src="https://www.allptp.cn/web/uploads/20190527/27a712d4162f1ebe58686b0aa1c9d6ad.png" alt="" /></a><br />
			</span></span> 
			</p>
			<p style="text-align:center;">
				<span style="font-size:18px;line-height:2.5;color:#666666;"><br />
			</span> 
			</p>
			<p style="text-align:left;">
				<span style="font-size:18px;line-height:2.5;color:#666666;"><br />
			</span> 
			</p>
			<p style="text-align:left;">
				<span style="font-size:18px;line-height:2.5;color:#666666;"><br />
			</span> 
			</p>
			<p style="text-align:left;">
				<span style="font-size:18px;line-height:2.5;color:#666666;"><br />
			</span> 
			</p>
	'; 
	protected $name='ALLPTP'; 
	protected $regiseter='';
	
	protected $password_name='Allptp找回密码'; 
	protected $change_mail='Allptp更改邮箱'; 
	
	public function order_mail($order){
		//发给购买者
		$this->send_user($order);  
		//发给策划者
		$this->send_planner($order);
	
	}
	

	
	public function regiseter($toemail,$flag,$code=''){
		switch ($flag) {
			case 1:
			$content_body='您正在用邮箱号码注册'.$this->name.'平台,验证码为：'.$code;
			$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">顾客</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>';
			$content=$this->header.$body.$this->footer; 
			$title="Allptp邮箱验证";
			break;
			case 2:
			$content=$this->password_name;
			$title="找回密码";
			break;
			case 3:
			$content=$this->change_mail;
			$title="您正在更改邮箱账号";
			break;
			case 4: 
			$content_body='您正在用邮箱号码注册'.$this->name.'平台,验证码为：'.$code;
			$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">顾客</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>';
			$content=$this->header.$body.$this->footer; 
			$title="您正在修改邮箱";
			break;
		}
		//pre($content);
		return send_mail($toemail,$this->name,$title,$content);
	}
	
	
	

}