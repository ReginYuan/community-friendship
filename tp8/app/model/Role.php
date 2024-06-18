<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\facade\Db;
/**
 * @mixin \think\Model
 */
class Role extends Model
{
    // 删除之后
    public static function onAfterDelete($role)
    {
        // 删除role_rule关联
        Db::table("role_rule")->where("role_id",$role->id)->delete();

        // 删除user_role关联
        Db::table("user_role")->where("role_id",$role->id)->delete();
    }
    
    // 多对多关联权限
    public function rules()
    {
        return $this->belongsToMany(Rule::class, 'role_rule', 'rule_id', 'role_id');
    }
    
    // 关联中间表
    public function roleRules()
    {
        return $this->hasMany(RoleRule::class, 'role_id', 'id');
    }

    // 根据多个角色ID验证请求方式和路由
    public static function hasAuthByRoleIds($roleIds,$condition,$method)
    {
        // 获取当前路由权限
        $ruleIds = Db::table("role_rule")->where("role_id","in",$roleIds)->column("rule_id");
        $rule = Db::table("rule")
        ->where("id","in",$ruleIds)
        ->where("condition",$condition)
        ->where("method",$method)
        ->where("status",1)
        ->where("ismenu",0)
        ->value("id");

        return $rule ? true : false;
    }

    // 根据用户获取角色ID
    public static function getRoleIdsByUserId($userId)
    {
        // 获取当前路由权限
        $roleIds = Db::table("user_role")->where("user_id",$userId)->column("role_id");
        return $roleIds;
    }

    // 根据多个角色ID获取所有权限
    public static function getRulesByRoleIds($roleIds)
    {
        if(count($roleIds) == 0){
            return [];
        }
        // 获取当前路由权限
        $ruleIds = Db::table("role_rule")
        ->where("role_id","in",$roleIds)
        ->column("rule_id");

        $rules = Db::table("rule")
        ->withoutField("create_time,update_time")
        ->where("id","in",$ruleIds)
        ->where("status",1)
        ->order("order,id asc")
        ->select();
        
        return $rules;
    }

    // 根据用户ID获取所有权限
    public static function getRulesByUserId($userId)
    {
        $roleIds = self::getRoleIdsByUserId($userId);
        return self::getRulesByRoleIds($roleIds);
    }

    // 根据用户ID获取菜单和权限
    public static function getMenusAndRulesByUserId($userId)
    {
        $rs = self::getRulesByUserId($userId);

        $menus = [];
        $rules = [];
        $rs->each(function($item) use(&$menus,&$rules){
            if($item["ismenu"]){
                $menus[] = [
                    "name"=>$item["name"],
                    "page"=>$item["condition"],
                    "icon"=>$item["icon"]
                ];
            } else {
                $rules[] = $item["condition"].",".$item["method"];
            }
        });

        return [
            'menus'=>$menus,
            'rules'=>array_unique($rules)
        ];
    }
}
