<?php

namespace app\common\model;

use think\Cache;

/**
 * 系统设置模型
 * Class Setting
 * @package app\common\model
 */
class Setting extends BaseModel
{
    protected $name = 'setting';
    protected $createTime = false;
	    /**
     * 设置项描述
     * @var array
     */
    protected $describe= [
        'sms' => '短信通知',
        'storage' => '上传设置',
        'store' => '商城设置',
        'trade' => '交易设置',
        'conf' => '参数设置',
    ];

    /**
     * 获取器: 转义数组格式
     * @param $value
     * @return mixed
     */
    public function getValuesAttr($value)
    {
        return json_decode($value, true);
    }

    /**
     * 修改器: 转义成json格式
     * @param $value
     * @return string
     */
    public function setValuesAttr($value)
    {
        return json_encode($value);
    }

    /**
     * 获取指定项设置
     * @param $key
     * @param $wxapp_id
     * @return array
     */
    public static function getItem($key, $wxapp_id = null)
    {
        $data = self::getAll($wxapp_id); 
        return isset($data[$key]) ? $data[$key]['values'] : [];
    }

    /**
     * 获取设置项信息
     * @param $key
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($key)
    {
        return self::get(compact('key'));
    }

    /**
     * 全局缓存: 系统设置
     * @param null $wxapp_id
     * @return array|mixed
     */
    public static function getAll($wxapp_id = null)
    {
        $self = new static;
        is_null($wxapp_id) && $wxapp_id = $self::$wxapp_id;
        if (!$data = Cache::get('setting_' . $wxapp_id)) {
            $data = array_column(collection($self::all())->toArray(), null, 'key');
            Cache::set('setting_' . $wxapp_id, $data);
        }
        return array_merge_multiple($self->defaultData(), $data);
    }

    /**
     * 默认配置
     * @return array
     */
    public function defaultData()
    {
        return [
            'store' => [
                'key' => 'store',
                'describe' => '商城设置',
                'values' => ['name' => '萤火小程序商城'],
            ],
            'trade' => [
                'key' => 'trade',
                'describe' => '交易设置',
                'values' => [
                    'order' => [
                        'close_days' => '0',
                        'receive_days' => '15',
                        'refund_days' => '0'
                    ],
                    'freight_rule' => '10',
                ]
            ],
            'storage' => [
                'key' => 'storage',
                'describe' => '上传设置',
                'values' => [
                    'default' => 'local',
                    'engine' => [
                        'qiniu' => [
                            'bucket' => '',
                            'access_key' => '',
                            'secret_key' => '',
                            'domain' => 'http://'
                        ],
                    ]
                ],
            ],
            'sms' => [
                'key' => 'sms',
                'describe' => '短信通知',
                'values' => [
                    'default' => 'aliyun',
                    'engine' => [
                        'aliyun' => [
                            'AccessKeyId' => '',
                            'AccessKeySecret' => '',
                            'sign' => '萤火科技',
                            'order_pay' => [
                                'is_enable' => '0',
                                'template_code' => '',
                                'accept_phone' => '',
                            ],'mobile_code' => [
                                'is_enable' => '0',
                                'template_code' => '',
                                'accept_phone' => '',
                            ],
                        ],
                    ],
                ],
            ],
            'conf' => [
                'key' => 'conf',
                'describe' => '参数设置',
                'values' => [
					'score_top'=>['score_top'=>'10'],
					'reception_continue'=>['num'=>'10','add_score'=>'0.5'],
					'add_score'=>['add_score'=>'1'],
					'bad_score'=>['bad_score'=>'0.5'],
					'advert_timeout'=>['advert_timeout'=>'4'],
					'score1'=>[
						'begin'=>'1',
						'end'=>'3',
						'value'=>'差'
					],
					'score2'=>[
						'begin'=>'3.5',
						'end'=>'5',
						'value'=>'中'
					], 
					'score3'=>[
						'begin'=>'5.5',
						'end'=>'7.5',
						'value'=>'良'
					], 
					'score4'=>[
						'begin'=>'8',
						'end'=>'10',
						'value'=>'忧'
					],    
					'black_bottom_score'=>['black_bottom_score'=>'0'],
					'black_continu'=>[
						'days'=>'3',
						'bad_score'=>'5'
					], 
					'month_total_bad_score'=>['month_total_bad_score'=>'10'],
					'reception'=>[
						'num'=>'100',
						'rate'=>'10'
					],
					'reception1'=>[
						'num'=>'50',
						'reward'=>'30'
					],
					'reception2'=>[
						'num'=>'100',
						'reward'=>'50'
					],
					'reception3'=>[
						'num'=>'200',
						'reward'=>'80'
					],
					'reception4'=>[
						'num'=>'500',
						'reward'=>'150'
					],
					'valid_branch_audit'=>['valid_branch_audit'=>'30'],
					'valid_add_item'=>['valid_add_item'=>'3'],
					'valid_day_reception'=>[
						'num'=>'1',
						'day'=>'3'
					],
					'valid_week_reception'=>[
						'num'=>'3',
						'valid_week'=>'7'
					],
					'agent_amount'=>['agent_amount'=>'0.1'],
				],
            ]
        ];
    }

}
