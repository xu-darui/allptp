<?php

namespace app\common\model;

use think\Cache;

/**
 * 代理商模型
 * Class Setting
 * @package app\common\model
 */
class System extends BaseModel
{
    public function user()
    {
        return $this->hasOne('User', 'user_id', 'user_id');
    }


}