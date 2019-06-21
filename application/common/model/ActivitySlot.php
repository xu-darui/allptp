<?php

namespace app\common\model; 

/**
 * 活动时间段
 * Class ActivitySlot
 * @package app\common\model
 */
class ActivitySlot extends BaseModel
{
	
	protected $type = [ 
		'date'=> 'timestamp:Y-m-d',
		'begin_date'=> 'timestamp:Y-m-d',
		'end_date'=> 'timestamp:Y-m-d',
        'begin_time'  =>  'timestamp:H:i',
        'end_time'  =>  'timestamp:H:i',
    ];
	
	public function activity(){
		return $this->belongsTo('Activity','activity_id','activity_id');
	}
	
	
}