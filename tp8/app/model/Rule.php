<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\facade\Db;
/**
 * @mixin \think\Model
 */
class Rule extends Model
{
    // 删除之后
    public static function onAfterDelete($rule)
    {
        // 获取子权限ID
        $ruleIds = $rule->children->column("id");
        $ruleIds[] = $rule->id;
        // 删除role_rule关联
        Db::table("role_rule")->where("rule_id","in", $ruleIds)->delete();
        // 删除子权限
        $rule->children->delete();
    }
    
    // 关联子权限
    public function children()
    {
        return $this->hasMany(Rule::class, 'rule_id', 'id');
    }

}
