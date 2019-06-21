<?php

// 应用公共函数库文件

use think\Request;

/**
 * 打印调试函数
 * @param $content
 * @param $is_die
 */
function pre($content, $is_die = true)
{
    header('Content-type: text/html; charset=utf-8');
    echo '<pre>' . print_r($content, true);
    $is_die && die();
}

/**
 * 驼峰命名转下划线命名
 * @param $str
 * @return string
 */
function toUnderScore($str)
{
    $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
        return '_' . strtolower($matchs[0]);
    }, $str);
    return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
}

/**
 * 生成密码hash值
 * @param $password
 * @return string
 */
function yoshop_hash($password)
{
    return md5(md5($password) . 'yoshop_salt_SmTRx');
}

/**
 * 获取当前域名及根路径
 * @return string
 */
function base_url()
{
    $request = Request::instance();
    $subDir = str_replace('\\', '/', dirname($request->server('PHP_SELF')));
    return $request->scheme() . '://' . $request->host() . $subDir . ($subDir === '/' ? '' : '/');
}

/**
 * 写入日志
 * @param string|array $values
 * @param string $dir
 * @return bool|int
 */
function write_log($values, $dir)
{
    if (is_array($values))
        $values = print_r($values, true);
    // 日志内容
    $content = '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $values . PHP_EOL . PHP_EOL;
    try {
        // 文件路径
        $filePath = $dir . '/logs/';
        // 路径不存在则创建
        !is_dir($filePath) && mkdir($filePath, 0755, true);
        // 写入文件
        return file_put_contents($filePath . date('Ymd') . '.log', $content, FILE_APPEND);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * curl请求指定url
 * @param $url
 * @param array $data
 * @return mixed
 */
function curl($url, $data = [])
{
    // 处理get数据
    if (!empty($data)) {
        $url = $url . '?' . http_build_query($data);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
function https_request($url,$data,$type){
	 if($type=='json'){
		 $headers = array("Content-type: application/json;charset=UTF-8","Accept: application/json","Cache-Control: no-cache", "Pragma: no-cache");
		 $data=json_encode($data);
	 }
	 $curl = curl_init();
	 curl_setopt($curl, CURLOPT_URL, $url);
	 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	 if (!empty($data)){
		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
	 }
	 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	 curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers ); 
	 $output = curl_exec($curl);
	 curl_close($curl);
	 return $output;
}

function https_request_bank($url,$headers,$method='GET',$data=''){
	$curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	if ($data!==''){
		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
	 }
   // curl_setopt($curl, CURLOPT_HEADER, true);
    if (1 == strpos("$".$url, "https://"))
    { 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
	$output = curl_exec($curl);
	curl_close($curl);
	return $output;
}
function https_request_passport($url,$headers,$method='POST',$body=''){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_FAILONERROR, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($curl, CURLOPT_HEADER, true);
	if (1 == strpos("$".$url, "https://"))
	{
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	}
	curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
	$result = curl_exec($curl);
	// curl_close($curl); 
	$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE); 
	$rheader = substr($result, 0, $header_size); 
	$rbody = substr($result, $header_size); 
	$httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
	if($httpCode == 200){ 
		return ['code'=>1,'msg'=>'验证成功','data'=> $rbody];
		
	}else{
		return ['code'=>0,'msg'=>'护照验证失败','data'=>[]]; 
	}
}

if (!function_exists('array_column')) {
    /**
     * array_column 兼容低版本php
     * (PHP < 5.5.0)
     * @param $array
     * @param $columnKey
     * @param null $indexKey
     * @return array
     */
    function array_column($array, $columnKey, $indexKey = null)
    {
        $result = array();
        foreach ($array as $subArray) {
            if (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
                $result[] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
            } elseif (array_key_exists($indexKey, $subArray)) {
                if (is_null($columnKey)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = $subArray;
                } elseif (array_key_exists($columnKey, $subArray)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 多维数组合并
 * @param $array1
 * @param $array2
 * @return array
 */
function array_merge_multiple($array1, $array2)
{
    $merge = $array1 + $array2;
    $data = [];
    foreach ($merge as $key => $val) {
        if (
            isset($array1[$key])
            && is_array($array1[$key])
            && isset($array2[$key])
            && is_array($array2[$key])
        ) {
            $data[$key] = array_merge_multiple($array1[$key], $array2[$key]);
        } else {
            $data[$key] = isset($array2[$key]) ? $array2[$key] : $array1[$key];
        }
    }
    return $data;
}

/**
 * 系统邮件发送函数
 * @param string $tomail 接收邮件者邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 * @author static7 <static7@qq.com>
 */
function send_mail($tomail, $name, $subject = '', $body = '', $attachment = null) {
	Vendor('phpmailer.phpmailer.src.PHPMailer');
	Vendor('phpmailer.phpmailer.src.SMTP');
	$mail = new phpmailer\phpmailer\PHPMailer();
	
   // $mail = new \PHPMailer();           //实例化PHPMailer对象
    $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                    // 设定使用SMTP服务
    $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';          // 使用安全协议
    $mail->Host = "smtp.mxhichina.com"; // SMTP 服务器
    $mail->Port = 465;                  // SMTP服务器的端口号
    $mail->Username = "auth@allptp.com";    // SMTP服务器用户名
    $mail->Password = "Allptp@RenRenYou!@#123";     // SMTP服务器密码
    $mail->SetFrom('auth@allptp.com', 'Allptp');
    $replyEmail = '';                   //留空则为发件人EMAIL
    $replyName = '';                    //回复名称（留空则为发件人名称）
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($tomail, $name);
	
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
	
    return $mail->Send() ? true : $mail->ErrorInfo;

}

	function long_ago($begin_time,$flag=0){
		if(!$flag){
			$begin_time=strtotime($begin_time);
		} 
		$now_time=time();  
		if(($day=intval(($now_time-$begin_time)/86400))>=1){
			return $day.'天前';
		}else if(($hour=intval(($now_time-$begin_time)/3600))>=1){
			return $hour.'小时前';
		}else if(($minute=intval(($now_time-$begin_time)/60))>=1){
			return $minute.'分钟前';
		}else{
			$second=$now_time-$begin_time;
			return $second.'秒前';
		}
			
	}
	
	function  how_long($total_time){ 
		$total_time=intval($total_time);	
		if(($day=round($total_time/86400,2))>=1){
			return $day.'天';
		}else if(($hour=round($total_time/3600,2))>=1){
			return $hour.'小时';
		}else if(($minute=round($total_time/60,2))>=1){
			return $minute.'分钟';
		}else{
			if($total_time){
				return $total_time.'秒';
			}else{
				return '';
			}
			
		}
	}
	
	function send_name($user){
		$name=$user['family_name'].$user['middle_name'].$user['name'];
		//$name=$name==''?$user['mobile']:$name;
		//$name=$name==''?$user['email']:$name; 
		$name=$name==''?'匿名用户':$name;  
		return $name;
	}

	
