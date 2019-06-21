<?php

namespace app\common\model;
use think\Request;
use think\Cache;
use think\Db;


/**
 * 系统消息
 * Class SysMsg
 * @package app\common\model
 */
class SysMsg extends BaseModel
{
	 protected $type = [ 
        'send_time'  =>  'timestamp:Y-m-d',
    ];
	public function admin(){
		return $this->hasOne('Admin','admin_id','admin_id');
	}
	
	protected $header='
				<p style="text-align:center;">
					<img src="https://www.allptp.cn/web/uploads/20190527/3938d47ea4f4edff628e70b3be3fb7ec.png" alt="" /> 
				</p>
				<p>
					<br />
				</p>';	
	protected $footer='
			<p style="text-align:right;">
				<span style="font-size:18px;"><strong>allptp 项目组</strong></span> 
			</p>
	';	

}