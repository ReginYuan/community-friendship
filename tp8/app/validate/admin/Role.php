<?php
declare (strict_types = 1);

namespace app\validate\admin;

use think\Validate;

class Role extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'page|页码'=>'require|integer|>:0',
        "name|名称"=>"require|max:20",
        "desc|描述"=>"max:100",
        "id|id"=>"require|integer|>:0",
        "role_id|id"=>"require|integer|>:0",
        "rule_ids|权限"=>"require|array",
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];

     // 验证场景
     protected $scene = [
        'index' => ['page'],
        "save"=>["name","desc"],
        "update"=>["name","desc","id"],
        "delete"=>["id"],
        "setRule"=>["role_id","rule_ids"],
    ];
}
