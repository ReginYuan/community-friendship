<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Base extends Model
{
    // 定义全局的查询范围
    // protected $globalScope = ['order'];

    // public function scopeOrder($query)
    // {
    //     $query->order('id',"desc");
    // }
}
