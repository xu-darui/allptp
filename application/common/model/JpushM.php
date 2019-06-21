<?php

namespace app\common\model;
use JPush\Client as JPush;

/**
 * 推送手机消息
 * Class Jpush
 * @package app\common\model
 */
class JpushM extends BaseModel
{
	 
	const  jpush_conf=['app_key'=>'fe71c9c705de8580173805aa','master_secret'=>'2a4c3776b0e0924d0a53e080'];
	 /**
     * 通过别名发送极光推送消息
     * @param $title // 标题
     * @param $content // 内容
     * @param $alias // 别名
     * @param array $params // 扩展字段
     * @param string $ios_badge // ios 角标数
     * @param array $platform // 推送设备
     * @return array|bool
     * @author huangzhicheng 2018年08月29日
     */
    public static function pushMessageByAlias ($title, $content, $alias, $params = [], $ios_badge = '0', $platform = ['ios', 'android'])
    {
        $jpush_conf =self::jpush_conf; // 获取配置信息 app_key 和 master_secret
        $app_key = $jpush_conf[ 'app_key' ];
        $master_secret = $jpush_conf[ 'master_secret' ];
        try {
            // 初始化
            $client = new JPush($app_key, $master_secret);

            $push = $client->push()
                ->setPlatform ($platform)
                ->iosNotification (
                    $content, [
                    'sound' => '1',
                    'badge' => (int)$ios_badge,
                    'content-available' => true,
                    'category' => 'jiguang',
                    'extras' => $params,
                ])
                ->androidNotification ($content, [
                    'title' => $title,
                    //'build_id' => 2,
                    'extras' => $params,
                ])
                ->options ([
                    'sendno' => 100,
                    'time_to_live' => 86400,
                    'apns_production' => false, // ios推送证书的选择，True 表示推送生产环境，False 表示要推送开发环境
                    //'big_push_duration' => 10,
                ]);
			    if (!is_array ($alias)&&$alias=='all'){
				   $push->addAllAudience ();
			   }else{
				   $push->addAlias ($alias);
			   }  
               $result=$push->send();
            return $result;
        } catch (\Exception $e) {
            write_log($e,__DIR__);
        }
    } 
	
	const PUSH_TYPE = [
        'push_new_info' => '1',
        'push_visitor_alert' => '2'

    ];

	const APP_NAME = "****";
		
	public static function pushNewInfoNotice ($uids, $title, $url, $txt, $type = '1')
	{

		$ext = [
			'push_type' => strval (self::PUSH_TYPE[ 'push_new_info' ]),
			'info_type' => strval ($type),//1-资讯,2-项目
			'title' => empty($title) ? self::APP_NAME : $title,
			'content' => $txt,
			'redirect_url' => $url
		];

		$res = JpushM::pushMessageByAlias ($title, $txt, $uids, $ext);
		return $res;
	}

}