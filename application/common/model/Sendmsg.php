<?php

namespace app\common\model;
use app\common\library\sms\Driver as SmsDriver;
use app\common\model\SysMsg as SysMsgModel;
use think\Cache;
use think\Db;

/**
 * 发短信
 * Class Sendmsg
 * @package app\common\model
 */
class Sendmsg extends BaseModel
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
	protected $footer_sys='
			<p style="text-align:right;">
				<span style="font-size:18px;"><strong>allptp 项目组</strong></span> 
			</p>
	';	
	protected $name='ALLPTP'; 
	
	
	protected $password_name='Allptp找回密码'; 
	protected $change_mail='Allptp更改邮箱'; 
	
	private static function config(){
		return ["default"=>"aliyun",
			"engine"=>[
				"aliyun"=>[
					"AccessKeyId"=>"LTAIYjFAvgLoSPaP",
					"AccessKeySecret"=>"RcZWat4eA1pHGeeTdQBLhPxGkU53yC",
					"sign"=>"人人游",
					//系统消息
					"system"=>[ 
						"template_code"=>"SMS_163705153",
						"accept_phone"=>""
					],//找回密码
					"find_pwd"=>[ 
						"template_code"=>"SMS_154100158",
						"accept_phone"=>""
					], 
					//注册
					"register"=>[ 
						"template_code"=>"SMS_154100159",
						"accept_phone"=>""
					],
					//登陆
					"login"=>[ 
						"template_code"=>"SMS_154100161",
						"accept_phone"=>""
					],
					//提前推送给用户
					"advance_sent_user"=>[ 
						"template_code"=>"SMS_158900438",
						"accept_phone"=>""
					],
					//提前推送给志愿者
					"advance_sent_joiner"=>[ 
						"template_code"=>"SMS_158900438",
						"accept_phone"=>""
					],
					//预定成功后发送给策划者
					"order_for_planner"=>[ 
						"template_code"=>"SMS_153991189",
						"accept_phone"=>""
					],
					//预定成功后发送给用户
					"order_for_user"=>[ 
						"template_code"=>"SMS_153996168",
						"accept_phone"=>""
					],
					//预定成功后发送给参与者
					"order_for_joiner"=>[ 
						"template_code"=>"SMS_153991195",
						"accept_phone"=>""
					],
					//更改手机号码用
					"change_mobile"=>[ 
						"template_code"=>"SMS_162737949",
						"accept_phone"=>""
					],
					//SMS_168415198
					"set_paypassword"=>[ 
						"template_code"=>"SMS_168415198",
						"accept_phone"=>""
					],
					//绑定手机号码
					"bind_mobile"=>[ 
						"template_code"=>"SMS_168410137",
						"accept_phone"=>""
					],
					//策划者提交体验通知
					"submit_activity"=>[ 
						"template_code"=>"SMS_165411426",
						"accept_phone"=>""
					],
					//系统审核完成通知
					"audit_activity"=>[ 
						"template_code"=>"SMS_165416264",
						"accept_phone"=>""
					],
					
					//顾客提交提交退款申请成功 顾客收到的短信
					"submit_refund"=>[ 
						"template_code"=>"SMS_165412652",
						"accept_phone"=>""
					],
					
					//顾客提交退款申请 策划者收到退款短信
					"submit_refund_planner"=>[ 
						"template_code"=>"SMS_165412833",
						"accept_phone"=>""
					],
					//策划者退款审核通过
					"audit_refund"=>[ 
						"template_code"=>"SMS_165412669",
						"accept_phone"=>""
					],
					//退款成功 发短信给顾客
					"success_refund"=>[ 
						"template_code"=>"SMS_165417525",
						"accept_phone"=>""
					],
					//退款成功 发短信给策划者
					"success_refund_planner"=>[ 
						"template_code"=>"SMS_165412678",
						"accept_phone"=>""
					],
					//申请退款不通过 发送给顾客
					"error_refund"=>[ 
						"template_code"=>"SMS_165417685",
						"accept_phone"=>""
					],
					//策划者实名认证结果通知
					"planner_check"=>[ 
						"template_code"=>"SMS_165676808",
						"accept_phone"=>""
					],
					//策划者实名认证提交成功通知
					"submit_planner_check"=>[ 
						"template_code"=>"SMS_165691685",
						"accept_phone"=>""
					],
					
					//志愿者报名提交成功 志愿者收短信
					"submit_enroll"=>[ 
						"template_code"=>"SMS_165677306",
						"accept_phone"=>""
					],
					
					//志愿者报名提交成功 策划者收短信
					"submit_enroll_planner"=>[ 
						"template_code"=>"SMS_165692055",
						"accept_phone"=>""
					],
					//志愿者收到报名通过的结果（通过）
					"check_enroll_ok"=>[ 
						"template_code"=>"SMS_166000068",
						"accept_phone"=>""
					],
					//志愿者收到报名不通过的结果（不通过）
					"check_enroll_fail"=>[ 
						"template_code"=>"SMS_166081213",
						"accept_phone"=>""
					],
					//志愿者收到策划者邀请	
					"submit_invite"=>[ 
						"template_code"=>"SMS_166000058",
						"accept_phone"=>""
					],
					//志愿者同意策划者的服务邀请	
					"check_invite_ok"=>[ 
						"template_code"=>"SMS_165678424",
						"accept_phone"=>""
					],
					//志愿者拒绝策划者的服务邀请
					"check_invite_fail"=>[ 
						"template_code"=>"SMS_165678426",
						"accept_phone"=>""
					],
					
					"system_en"=>[ 
						"template_code"=>"SMS_163621403",
						"accept_phone"=>""
					],
					"register_en"=>[ 
						"template_code"=>"SMS_153990925",
						"accept_phone"=>""
					],
					"find_pwd_en"=>[ 
						"template_code"=>"SMS_153995895",
						"accept_phone"=>""
					],
					"order_for_user_en"=>[ 
						"template_code"=>"SMS_153991164",
						"accept_phone"=>""
					],
					"order_for_planner_en"=>[ 
						"template_code"=>"SMS_153991171",
						"accept_phone"=>""
					],
					"order_for_joiner_en"=>[ 
						"template_code"=>"SMS_153991177",
						"accept_phone"=>""
					],
					"change_mobile_en"=>[ 
						"template_code"=>"SMS_162732956",
						"accept_phone"=>""
					],
				]
			]
		];
	}
	 
	public function sendmsg($key,$m_code,$mobile,$templateParams){
		$config=$this->config();
		switch($m_code){
			case '81':
				$mobile=substr($mobile,0,1)==0?$m_code.substr($mobile,1,strlen($mobile)-1):$m_code.$mobile; 
				break;
			case '86':
				$mobile=$mobile; 
				break;
			default:
				$mobile=$m_code.$mobile; 
				break;
		} 
		$config['engine']['aliyun'][$key]['accept_phone']=$mobile;
		 $SmsDriver = new SmsDriver($config);
        return $SmsDriver->sendSms($key, $templateParams);
	}
	
	public function act_begin_mobile($value){
		$content="您在$value[pay_time]预定的$value[title]的活动，开始时间为$value[activ_begin_time]，距离开始时间还有$value[act_begin_send]分钟,请您务必准时参加"; 
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($value['user']).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		//发送系统消息 
		$title='预定活动开始预通知';
		$data['content']=$template_sys; 
		$data['user_list']=$value['user']['user_id'];
		$data['send_time']=time();
		$data['title']=$title;
		$data['issend']=1; 
		$sysmsg_model=new SysMsgModel;
		$sysmsg_model->allowField(true)->save($data);
		//发短信
		if($value['user']['mobile']&&$value['user']['m_code']=='86'){
			$config=$this->config();  
			$templateParams=[
							'user_name'=>mb_substr(send_name($value['user']),0,20),
							'title'=>mb_substr($value['title'],0,20),
							'pay_time'=>$value['pay_time'],
							'activ_begin_time'=>$value['activ_begin_time'],
							'act_begin_send'=>$value['act_begin_send']
							];
			$config['engine']['aliyun']['advance_sent_user']['accept_phone']=$value['user']['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms('advance_sent_user', $templateParams);
		}
		
		//发送邮件
		if($value['user']['email']){
			send_mail($value['user']['email'],$this->name,$title,$template);
		} 
		
	}
	
	public function order_mobile($order){
		//发给购买者
		$this->send_user($order); 
		//发给购买的同行者
		$this->send_joiner($order);
		//发给策划者
		$this->send_planner($order);
	}
	//发给购买者
	public function send_user($order){ 
		$content="您已经成功预定”$order[activ_begin_time]“ 的”$order[title]“体验活动，已支付$order[total_price]元";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($order['user']).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$title='购买通知';
		$data['content']=$template_sys; 
		$data['user_list']=$order['user']['user_id'];
		$data['send_time']=time();
		$data['create_time']=time();
		$data['update_time']=time();
		$data['title']=$title;
		$data['issend']=1;   
		Db::name('sys_msg')->insert($data);
		
		//发短信
		if($order['user']['mobile']){
			$config=$this->config();  
			if($order['user']['m_code']=='86'){
				$key='order_for_user';
			}else{
				$key='order_for_user_en';
			}
			$templateParams=[
							'date'=>mb_substr($order['activ_begin_time'],0,20),
							'activity_name'=>mb_substr($order['title'],0,20),
							'price'=>$order['total_price']
							];
			$config['engine']['aliyun'][$key]['accept_phone']=$order['user']['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms($key, $templateParams);
		}
		if($order['user']['email']){
			return send_mail($order['user']['email'],$this->name,$title,$template); 
		}
		
	}

	//发给同行者
	public function send_joiner($order){
		$joiner=Db::name('order_person')->where(['order_id'=>$order['order_id']])->select();
		$config=$this->config(); 
		if($joiner){
			foreach($joiner as $key=>$value){
				if($order['user']['m_code']=='86'){
					$key='order_for_joiner';
				}else{
					$key='order_for_joiner_en';
				}
				$templateParams=[
								'date'=>mb_substr($order['activ_begin_time'],0,20),
								'user_name'=>mb_substr(send_name($order['user']),0,20)
								];
				$config['engine']['aliyun'][$key]['accept_phone']=$value['mobile'];
				$SmsDriver = new SmsDriver($config); 
				$SmsDriver->sendSms($key, $templateParams);
			}
		}
		
	}
	
		//发给策划者
	public function send_planner($order){
		$activity=Db::name('activity')->alias('a')->field('u.user_id,u.family_name,u.middle_name,u.name,u.email,u.m_code,u.mobile')->join('user u','u.user_id = a.user_id')->where(['a.activity_id'=>$order['activity_id']])->find();
		$content=send_name($order['user'])."已经预定您的“$order[title]  $order[activ_begin_time]”体验活动，支付金额$order[total_price]元";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($activity).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$title='预定通知';
		$data['content']=$template_sys; 
		$data['user_list']=$activity['user_id'];
		$data['send_time']=time();
		$data['create_time']=time();
		$data['update_time']=time();
		$data['title']=$title;
		$data['issend']=1; 
		$result=Db::name('sys_msg')->insert($data);
		
		//发送短信
		if($activity['mobile']){
			$config=$this->config(); 
			if($activity['m_code']=='86'){
				$key='order_for_planner';
			}else{
				$key='order_for_planner_en';
			}
			$templateParams=[
							'date'=>$order['activ_begin_time'],
							'user_name'=>mb_substr(send_name($order['user']),0,20),
							'price'=>$order['total_price']
							];
			$config['engine']['aliyun'][$key]['accept_phone']=$activity['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms($key, $templateParams);
		}
		//发送邮件
		if($activity['email']){
			send_mail($activity['email'],$this->name,$title,$template);
		}
		
	}
	

	
	public function send_submit_activity($activity_id){  
		$activity_data=Db::name('activity')->alias('a')->join('user b','a.user_id=b.user_id','LEFT')->field('a.title,b.user_id,b.email,b.family_name,b.middle_name,b.name,b.mobile,b.m_code')->where(['a.activity_id'=>$activity_id])->find(); 
		$name=send_name($activity_data);
		$title='提交体验审核通知';
		$content="您的体验活动$activity_data[title]已经提交成功，系统将在1-5个工作日审核完成，请耐心等待";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($activity_data).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$data['content']=$template_sys; 
		$data['user_list']=$activity_data['user_id'];
		$data['send_time']=time(); 
		$data['title']=$title;
		$data['issend']=1; 
		$sysmsg_model=new SysMsgModel;
		$sysmsg_model->allowField(true)->save($data); 
		
		//发送短信
		if($activity_data['m_code']&&$activity_data['mobile']){
			$config=$this->config();
			$templateParams=[
				'name'=>mb_substr(send_name($activity_data),0,20),
				'title'=>mb_substr($activity_data['title'],0,20)
				
			];
			$config['engine']['aliyun']['submit_activity']['accept_phone']=$activity_data['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms('submit_activity', $templateParams);
		}
		//发送邮件
		if($activity_data['email']){
			send_mail($activity_data['email'],$this->name,$title,$template);
		}
	}
	
		public function send_audit_activity($activity_id,$content){
		$activity_data=Db::name('activity')->alias('a')->join('user b','a.user_id=b.user_id','LEFT')->field('a.title,a.audit,b.user_id,b.email,b.family_name,b.middle_name,b.name,b.mobile,b.m_code')->where(['a.activity_id'=>$activity_id])->find();   
		$content_body="您提交的$activity_data[title]活动$content";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($activity_data).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		$title='活动审核结果';
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$data['content']=$template_sys; 
		$data['user_list']=$activity_data['user_id'];
		$data['send_time']=time(); 
		$data['title']=$title;
		$data['issend']=1; 
		$sysmsg_model=new SysMsgModel;
		$sysmsg_model->allowField(true)->save($data);
		
		//发送短信 
		if($activity_data['m_code']&&$activity_data['mobile']){
			$config=$this->config();
			$templateParams=[
				'name'=>mb_substr(send_name($activity_data),0,20),
				'title'=>mb_substr($activity_data['title'],0,20),
				'content'=>mb_substr($content,0,20)
			];
			$config['engine']['aliyun']['audit_activity']['accept_phone']=$activity_data['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms('audit_activity', $templateParams);
		}
		//发送邮件
		if($activity_data['email']){
			send_mail($activity_data['email'],$this->name,$title,$template);
		}
	}
	
	public function send_submit_refund($refund_id){
		$refund=Db::name('refund')->alias('a')->field("a.*,b.email,b.family_name,b.middle_name,b.name,b.mobile,b.m_code,c.title")->join('user b','a.user_id=b.user_id','LEFT')->join('order c','c.order_id=a.order_id','LEFT')->where(['refund_id'=>$refund_id])->find();
		$activity_data=Db::name('activity')->alias('a')->field('a.title,b.user_id,b.email,b.family_name,b.middle_name,b.name,b.mobile,b.m_code')->join('user b','a.user_id=b.user_id','LEFT')->where(['a.activity_id'=>$refund['activity_id']])->find(); 
		$config=$this->config();
		$planner_name=send_name($activity_data); 
		$content_body="您已提交体验$refund[title]的退款申请，策划者$planner_name 将于3个工作日内审核";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($refund).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		//发给申请者
		$title='提交退款申请成功';
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$data['content']=$template_sys; 
		$data['user_list']=$refund['user_id'];
		$data['send_time']=time(); 
		$data['create_time']=time(); 
		$data['update_time']=time(); 
		$data['title']=$title;
		$data['issend']=1; 
		Db::name('sys_msg')->insert($data);
		//发送短信
		if($refund['m_code']&&$refund['mobile']){
			$key='submit_refund';
			$templateParams=[
				'name'=>mb_substr(send_name($refund),0,20),
				'title'=>mb_substr($refund['title'],0,20) ,
				'planner_name'=>mb_substr(send_name($activity_data),0,20)
			];
			$config['engine']['aliyun'][$key]['accept_phone']=$refund['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms($key, $templateParams);
		}
		if($refund['email']){
			send_mail($refund['email'],$this->name,$title,$template);
		}
		//发给策划者
		$content_body="您的体验$refund[title]有顾客申请退款，您可于3个工作日内审核，逾期将自动退款给顾客";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($activity_data).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>';
				
		$title='收到体验退款申请';
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$data=$this->create_sysmsg($template_sys,$activity_data['user_id'],$title);  
		//发送短信
		if($activity_data['m_code']&&$activity_data['mobile']){
			$key='submit_refund_planner';
			$templateParams=[
				'name'=>mb_substr(send_name($activity_data),0,20),
				'title'=>mb_substr($activity_data['title'],0,20)
				
			];
			$config['engine']['aliyun'][$key]['accept_phone']=$activity_data['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms($key, $templateParams);
		}
		//发送邮件
		if($activity_data['email']){
			send_mail($activity_data['email'],$this->name,$title,$template);
		}
	}
	
	public function audit_refund($refund_id){
		$refund=Db::name('refund')->alias('a')->field("a.*,b.email,b.family_name,b.middle_name,b.name,b.mobile,b.m_code,c.title")->join('user b','a.user_id=b.user_id','LEFT')->join('order c','c.order_id=a.order_id','LEFT')->where(['refund_id'=>$refund_id])->find();
		$activity_data=Db::name('activity')->alias('a')->field('a.title,b.user_id,b.email,b.family_name,b.middle_name,b.name,b.mobile,b.m_code')->join('user b','a.user_id=b.user_id','LEFT')->where(['a.activity_id'=>$refund['activity_id']])->find(); 
		$config=$this->config();
		$title='退款审核结果';
		//发给申请者  
		if($refund['audit']==1){
			//同意
			$content_body="您提交活动$refund[title]的退款申请，策划者".send_name($activity_data)."已审核通过，退款金额$refund[total_price]将于3个工作日内退回您的账户";
			$body='<p style="text-align:left;">
						<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($refund).'</span></u></span></strong> 
					</p>
					<p style="text-align:left;">
						<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
					</p>'; 
			$template=$this->header.$body.$this->footer; 
			$template_sys=$this->header.$body.$this->footer_sys; 
			$data=$this->create_sysmsg($template_sys,$activity_data['user_id'],$title);  
			if($refund['m_code']&&$refund['mobile']){
				$key='audit_refund';
				$templateParams=[
					'name'=>mb_substr(send_name($refund),0,20),
					'title'=>mb_substr($refund['title'],0,20),
					'planner_name'=>mb_substr(send_name($activity_data),0,20),
					'amount'=>$refund['total_price']
				];
				$config['engine']['aliyun'][$key]['accept_phone']=$refund['mobile'];
				$SmsDriver = new SmsDriver($config); 
				$SmsDriver->sendSms($key, $templateParams);
			}
			if($refund['email']){
				send_mail($refund['email'],$this->name,$title,$template);
			}
		}else if($refund['audit']==2){
			//审核不通过
			$content_body="您提交活动$refund[title]的退款申请，策划者".send_name($activity_data)."已拒绝";
			$body='<p style="text-align:left;">
						<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($refund).'</span></u></span></strong> 
					</p>
					<p style="text-align:left;">
						<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
					</p>';
					 
			$template=$this->header.$body.$this->footer; 
			$template_sys=$this->header.$body.$this->footer_sys; 
			$data=$this->create_sysmsg($template_sys,$activity_data['user_id'],$title);   
			if($refund['m_code']&&$refund['mobile']){
				$key='error_refund';
				$templateParams=[
					'name'=>mb_substr(send_name($refund),0,20),
					'title'=>mb_substr($refund['title'],0,20) ,
					'planner_name'=>mb_substr(send_name($activity_data),0,20)
				];
				$config['engine']['aliyun'][$key]['accept_phone']=$refund['mobile'];
				$SmsDriver = new SmsDriver($config); 
				$SmsDriver->sendSms($key, $templateParams);
			}
			if($refund['email']){
				send_mail($refund['email'],$this->name,$title,$template);
			}
		}
		
	}
	
	public function success_refund($refund_id){
		$refund=Db::name('refund')->alias('a')->field("a.*,b.email,b.family_name,b.middle_name,b.name,b.mobile,b.m_code,c.title")->join('user b','a.user_id=b.user_id','LEFT')->join('order c','c.order_id=a.order_id','LEFT')->where(['refund_id'=>$refund_id])->find();
		$activity_data=Db::name('activity')->alias('a')->field('a.title,b.user_id,b.email,b.family_name,b.middle_name,b.name,b.mobile,b.m_code')->join('user b','a.user_id=b.user_id','LEFT')->where(['a.activity_id'=>$refund['activity_id']])->find(); 
		$content_body="您申请的活动$refund[title]的退款，退款金额$refund[total_price]已退回您的原始账户，详情查看您的账户";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($refund).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		$config=$this->config(); 
		//发给顾客
		
			$title='退款成功';
			$template=$this->header.$body.$this->footer; 
			$template_sys=$this->header.$body.$this->footer_sys; 
			$data=$this->create_sysmsg($template_sys,$refund['user_id'],$title); 
			
			//发送短信
			if($refund['m_code']&&$refund['mobile']){
				$key='success_refund';
				$templateParams=[
					'name'=>mb_substr(send_name($refund),0,20),
					'title'=>mb_substr($refund['title'],0,20),
					'amount'=>$refund['total_price']
				];
				$config['engine']['aliyun'][$key]['accept_phone']=$refund['mobile'];
				$SmsDriver = new SmsDriver($config); 
				$SmsDriver->sendSms($key, $templateParams);
			}
			//发送邮件
			if($refund['email']){
				send_mail($refund['email'],$this->name,$title,$template);
			}
			
			
		//发给策划者
			$content_body="您申请的活动$refund[title]的退款，退款金额$refund[total_price]已退回您的原始账户，详情查看您的账户";
			$body='<p style="text-align:left;">
						<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($refund).'</span></u></span></strong> 
					</p>
					<p style="text-align:left;">
						<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
					</p>'; 
			$title='退款成功';
			$template=$this->header.$body.$this->footer; 
			$template_sys=$this->header.$body.$this->footer_sys; 
			$data=$this->create_sysmsg($template_sys,$activity_data['user_id'],$title);  
			//发送短信
			if($activity_data['m_code']&&$activity_data['mobile']){
				$key='success_refund_planner';
				$templateParams=[
					'planner_name'=>mb_substr(send_name($activity_data),0,20),
					'title'=>mb_substr($refund['title'],0,20),
					'name'=>mb_substr(send_name($refund),0,20)
				];
				$config['engine']['aliyun'][$key]['accept_phone']=$activity_data['mobile'];
				$SmsDriver = new SmsDriver($config); 
				$SmsDriver->sendSms($key, $templateParams);
			}
			//发送邮件
			if($activity_data['email']){
				send_mail($activity_data['email'],$this->name,$title,$template);
			}
		
	}
	
	public function submit_check_planner($user_id){
		$userdata=Db::name('user')->field('user_id,family_name,middle_name,name,audit_face,refuse_reason,m_code,mobile,email')->where(['user_id'=>$user_id])->find();
		$config=$this->config(); 
		//发给顾客
		$content_body="您的实名认证申请已经提交,系统将在1-5个工作日审核完成，请您耐心等待";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($userdata).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		$title='策划者提交实名验证申请';
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$data=$this->create_sysmsg($template_sys,$userdata['user_id'],$title); 
		//发送短信
		if($userdata['m_code']&&$userdata['mobile']){
			$key='submit_planner_check';
			$templateParams=[
				'planner_name'=>mb_substr(send_name($userdata),0,20)
			];
			$config['engine']['aliyun'][$key]['accept_phone']=$userdata['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms($key, $templateParams);
		}
		//发邮件
		if($userdata['email']){
			send_mail($userdata['email'],$this->name,$title,$template);
		}
	}
	
	public function send_check_planner($user_id){
		$userdata=Db::name('user')->field('user_id,family_name,middle_name,name,audit_face,refuse_reason,m_code,mobile,email')->where(['user_id'=>$user_id])->find();
		if($userdata['audit_face']==2){
			//通过
			$result='已经通过';
		}else{
			//不通过
			$result='不通过,原因:'.$userdata['refuse_reason'];
		}	
		//发给顾客
		$content_body="您的实名认证 $result";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($userdata).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		$title='策划者实名验证结果通知';
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$data=$this->create_sysmsg($template_sys,$userdata['user_id'],$title); 
		$config=$this->config(); 
		//发短信
		if($userdata['m_code']&&$userdata['mobile']){
			$key='planner_check';
			$templateParams=[
				'planner_name'=>mb_substr(send_name($userdata),0,20),
				'result'=>mb_substr($result,0,20)
			];
			$config['engine']['aliyun'][$key]['accept_phone']=$userdata['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms($key, $templateParams);
		}
		//发邮件
		if($userdata['email']){
			send_mail($userdata['email'],$this->name,$title,$template);
		}
	}
	
	
	public function submit_enroll($enroll_id){ 
		$enroll=Db::name('enroll')->alias('a')->field("a.activity_id,a.user_id,b.title,FROM_UNIXTIME(c.begin_time,'%Y-%c-%d %h:%i:%s') AS begin_time,d.family_name,d.middle_name,d.name,d.m_code,d.mobile,d.email")->join('activity b','a.activity_id = b.activity_id','LEFT')->join('activity_slot c','a.slot_id = c.slot_id','LEFT')->join('user d','a.user_id = d.user_id','left')->where(['a.enroll_id'=>$enroll_id])->find();  
		$activity_data=Db::name('activity')->alias('a')->field('a.title,b.user_id,b.email,b.family_name,b.middle_name,b.name,b.mobile,b.m_code')->join('user b','a.user_id=b.user_id','LEFT')->where(['a.activity_id'=>$enroll['activity_id']])->find(); 
		$config=$this->config(); 
		//发给顾客
		$content_body="您作为志愿者报名参加$enroll[title] $enroll[begin_time]体验的申请已经提交给策划者，策划者将在1-5个工作日内审核，请您耐心等待";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($enroll).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		$title='报名提交成功';
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$data=$this->create_sysmsg($template_sys,$enroll['user_id'],$title); 
		//发送短信
		if($enroll['m_code']&&$enroll['mobile']){
			$key='submit_enroll';
			$templateParams=[
				'name'=>mb_substr(send_name($enroll),0,20),
				'title'=>mb_substr($enroll['title'].' '.$enroll['begin_time'],0,20)
			]; 
			$config['engine']['aliyun'][$key]['accept_phone']=$enroll['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms($key, $templateParams);
		}
		//发送邮件
		if($enroll['email']){
			send_mail($enroll['email'],$this->name,$title,$template);
		}
		//发给策划者
		$content_body=send_name($enroll)."报名参加了您创建的$enroll[title] $enroll[begin_time]体验，为保证您的体验正常进行,请点击我的策划查看详情";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($enroll).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		$title='有志愿者报名参加您的活动';
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$data=$this->create_sysmsg($template_sys,$activity_data['user_id'],$title); 
		//发短信
		if($activity_data['m_code']&&$activity_data['mobile']){
			$key='submit_enroll_planner';
			$templateParams=[
				'planner_name'=>mb_substr(send_name($activity_data),0,20),
				'name'=>mb_substr(send_name($enroll),0,20),
				'title'=>mb_substr($enroll['title'].' '.$enroll['begin_time'],0,20)
				
			];
			$config['engine']['aliyun'][$key]['accept_phone']=$activity_data['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms($key, $templateParams);
		}
		//发送邮件
		if($activity_data['email']){
			send_mail($activity_data['email'],$this->name,$title,$template);
		} 
	}
	public function audit_enroll($enroll_id){
		$enroll=Db::name('enroll')->alias('a')->field("a.activity_id,a.user_id,a.audit,b.title,FROM_UNIXTIME(c.begin_time,'%Y-%c-%d %h:%i:%s') AS begin_time,d.family_name,d.middle_name,d.name,d.m_code,d.mobile,d.email")->join('activity b','a.activity_id = b.activity_id','LEFT')->join('activity_slot c','a.slot_id = c.slot_id','LEFT')->join('user d','a.user_id = d.user_id','left')->where(['a.enroll_id'=>$enroll_id])->find();
		$activity_data=Db::name('activity')->alias('a')->field('a.title,b.user_id,b.email,b.family_name,b.middle_name,b.name,b.mobile,b.m_code')->join('user b','a.user_id=b.user_id','LEFT')->where(['a.activity_id'=>$enroll['activity_id']])->find();
		$config=$this->config();
		//发给申请者  
		if($enroll['audit']==1){
			//同意
			$content_body="您报名作为志愿者活动$enroll[title] $enroll[begin_time]的体验，策划者".send_name($activity_data)."已同意，请准时参加活动";
			$body='<p style="text-align:left;">
						<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($enroll).'</span></u></span></strong> 
					</p>
					<p style="text-align:left;">
						<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
					</p>'; 
			$title='志愿者报名审核已通过';
			$template=$this->header.$body.$this->footer; 
			$template_sys=$this->header.$body.$this->footer_sys; 
			$data=$this->create_sysmsg($template_sys,$enroll['user_id'],$title);
			//发送短信
			if($enroll['m_code']&&$enroll['mobile']){
				$key='check_enroll_ok';
				$templateParams=[ 
					'title'=>mb_substr($enroll['title'],0,20),
					'planner_name'=>mb_substr(send_name($activity_data),0,20),
					'date'=>$enroll['begin_time']
				];
				$config['engine']['aliyun'][$key]['accept_phone']=$enroll['mobile'];
				$SmsDriver = new SmsDriver($config); 
				$SmsDriver->sendSms($key, $templateParams);
			}
			//发送邮件
			if($enroll['email']){
				send_mail($enroll['email'],$this->name,$title,$template);
			} 
		}else if($enroll['audit']==2){
			//审核不通过
			$content_body="您报名作为志愿者活动$enroll[title] $enroll[begin_time]的体验，策划者".send_name($activity_data)."已谢绝";
			$body='<p style="text-align:left;">
						<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($enroll).'</span></u></span></strong> 
					</p>
					<p style="text-align:left;">
						<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
					</p>'; 
			$title='志愿者报名审核不通过';
			$template=$this->header.$body.$this->footer; 
			$template_sys=$this->header.$body.$this->footer_sys; 
			$data=$this->create_sysmsg($template_sys,$enroll['user_id'],$title);
			//发送短信
			if($enroll['m_code']&&$enroll['mobile']){
				$key='check_enroll_fail';
				$templateParams=[ 
					'title'=>mb_substr($enroll['title'],0,20) ,
					'planner_name'=>mb_substr(send_name($activity_data),0,20)
				];
				$config['engine']['aliyun'][$key]['accept_phone']=$enroll['mobile'];
				$SmsDriver = new SmsDriver($config); 
				$SmsDriver->sendSms($key, $templateParams);
			}
			//发送邮件
			if($enroll['email']){
				send_mail($enroll['email'],$this->name,$title,$template);
			} 
			
		}
		
	}
	
	public function submit_invite($invite_id){
		$invite=Db::name('invite')->alias('a')->field("a.activity_id,b.title,FROM_UNIXTIME(c.begin_time,'%Y-%c-%d %h:%i:%s') AS begin_time,d.user_id,d.family_name,d.middle_name,d.name,d.m_code,d.mobile,d.email,e.family_name as planner_family_name,e.middle_name as planner_middle_name,e.name as planner_name,e.m_code as planner_m_code,e.mobile as planner_mobile,e.email as planner_email")->join('activity b','a.activity_id = b.activity_id','LEFT')->join('activity_slot c','a.slot_id = c.slot_id','LEFT')->join('user d','a.user_id = d.user_id','left')->join('user e','a.invi_user_id=e.user_id','left')->where(['a.invite_id'=>$invite_id])->find(); 
		$config=$this->config(); 
		$planner_name=[
					'family_name'=>$invite['planner_family_name'],
					'middle_name'=>$invite['planner_middle_name'],
					'name'=>$invite['planner_name'],
					'm_code'=>$invite['planner_m_code'],
					'mobile'=>$invite['planner_mobile'],
					'email'=>$invite['planner_email']
				];
		$content_body="策划者".send_name($planner_name)."向您发出活动$invite[title].' '.$invite[begin_time]的志愿者邀请,请及时处理";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($invite).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>'; 
		$title='策划者邀请';
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$data=$this->create_sysmsg($template_sys,$invite['user_id'],$title);
		//发给顾客
		if($invite['m_code']&&$invite['mobile']){
			$key='submit_invite'; 
			$templateParams=[
				'planner_name'=>mb_substr(send_name($planner_name),0,20),
				'title'=>mb_substr($invite['title'].' '.$invite['begin_time'],0,20)
			]; 
			$config['engine']['aliyun'][$key]['accept_phone']=$invite['mobile'];
			$SmsDriver = new SmsDriver($config); 
			$SmsDriver->sendSms($key, $templateParams);
		} 
		if($invite['email']){
			send_mail($invite['email'],$this->name,$title,$template);
		}
		
	}
	
	
	public function audit_invite($invite_id){
		$invite=Db::name('invite')->alias('a')->field("a.activity_id,a.audit,b.title,FROM_UNIXTIME(c.begin_time,'%Y-%c-%d %h:%i:%s') AS begin_time,d.family_name,d.middle_name,d.name,d.m_code,d.mobile,d.email,e.family_name as planner_family_name,e.middle_name as planner_middle_name,e.name as planner_name,e.m_code as planner_m_code,e.mobile as planner_mobile,e.email as planner_email,e.user_id as planner_user_id")->join('activity b','a.activity_id = b.activity_id','LEFT')->join('activity_slot c','a.slot_id = c.slot_id','LEFT')->join('user d','a.user_id = d.user_id','left')->join('user e','a.invi_user_id=e.user_id','left')->where(['a.invite_id'=>$invite_id])->find(); 
		$config=$this->config(); 
		$title='志愿者审核结果';
		//发给策划者
		if($invite['audit']==1){
			//同意
			$content_body="您向".send_name($invite)."发出的志愿者邀请，志愿者".send_name($invite)."已同意，为保证体验顺利进行，您可以联系志愿者";
			$body='<p style="text-align:left;">
						<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($invite).'</span></u></span></strong> 
					</p>
					<p style="text-align:left;">
						<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
					</p>';  
			$template=$this->header.$body.$this->footer; 
			$template_sys=$this->header.$body.$this->footer_sys; 
			$data=$this->create_sysmsg($template_sys,$invite['planner_user_id'],$title); 
			if($invite['planner_m_code']&&$invite['planner_mobile']){
				$key='check_invite_ok';
				$templateParams=[ 
					'name'=>mb_substr(send_name($invite),0,20),
					'name_1'=>mb_substr(send_name($invite),0,20)
				];
				$config['engine']['aliyun'][$key]['accept_phone']=$invite['planner_mobile'];
				$SmsDriver = new SmsDriver($config); 
				$SmsDriver->sendSms($key, $templateParams);
			}
			if($invite['planner_email']){
				send_mail($invite['email'],$this->name,$title,$template);
			}
		}else if($invite['audit']==2){
			//不同意
			$content_body="您向".send_name($invite)."发出的志愿者邀请，志愿者".send_name($invite)."已谢绝，查看更多志愿者";
			$body='<p style="text-align:left;">
						<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($invite).'</span></u></span></strong> 
					</p>
					<p style="text-align:left;">
						<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
					</p>';  
			$template=$this->header.$body.$this->footer; 
			$template_sys=$this->header.$body.$this->footer_sys; 
			$data=$this->create_sysmsg($template_sys,$invite['planner_user_id'],$title);  
			if($invite['planner_m_code']&&$invite['planner_mobile']){
				$key='check_invite_fail';
				$templateParams=[ 
					'name'=>mb_substr(send_name($invite),0,20),
					'name_1'=>mb_substr(send_name($invite),0,20)
				];
				$config['engine']['aliyun'][$key]['accept_phone']=$invite['planner_mobile'];
				$SmsDriver = new SmsDriver($config); 
				$SmsDriver->sendSms($key, $templateParams);
			}
			if($invite['planner_email']){
				send_mail($invite['email'],$this->name,$title,$template);
			}
		}
		
	}
	public function colunteer_become($userdata){
		$content_body="恭喜您已成为志愿者，赶快去看看那些你感兴趣的体验吧";
		$body='<p style="text-align:left;">
					<strong><span style="font-size:24px;"><span style="line-height:2.5;color:#333333;font-size:24px;">敬爱的</span><u><span style="line-height:2.5;color:#333333;font-size:24px;">'.send_name($userdata).'</span></u></span></strong> 
				</p>
				<p style="text-align:left;">
					<span style="font-size:18px;line-height:2.5;color:#333333;">'.$content_body.'<a href="https://www.allptp.cn/#/" target="_blank"></a>;
				</p>';  
		$template=$this->header.$body.$this->footer; 
		$template_sys=$this->header.$body.$this->footer_sys; 
		$title="成为自愿者";
		$data=$this->create_sysmsg($template_sys,$userdata['user_id'],$title);  
	}
	
	
	public function create_sysmsg($template,$user_id,$title){ 
		$data=['content'=>$template,'user_list'=>$user_id,'send_time'=>time(),'create_time'=>time(),'update_time'=>time(),'title'=>$title,'issend'=>1];
		Db::name('sys_msg')->insert($data);	
	}
	
}