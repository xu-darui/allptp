<?php

namespace app\common\model;
 

/**
 * 国家id
 * Class Country
 * @package app\common\model
 */
class Country extends BaseModel
{
	public static function country(){
		return ['中国','美国','日本','菲律宾','泰国','俄罗斯','韩国'];
	}
	
}
 