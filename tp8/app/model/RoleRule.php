<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class RoleRule extends Model
{
    // 关联权限
    public function rule()
    {
        return $this->belongsTo(Rule::class, 'rule_id', 'id');
    }

    
}
