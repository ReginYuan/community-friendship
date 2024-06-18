<?php
declare (strict_types = 1);

namespace app\validate\admin;

use think\Validate;

class Rule extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     * 
     * @var array
     */
    protected $rule = [
        'page|页码'=>'require|integer|>:0',
        'name|名称'=>'require|max:50',
        'rule_id|父级权限id'=>'require|integer|>=:0',
        'status|状态'=>'require|integer|in:0,1',
        'order|排序'=>'require|integer|>=:0',
        'ismenu|是否菜单'=>'require|integer|in:0,1',
        'icon|图标'=>'requireIf:ismenu,1|max:100',
        'method|请求方式'=>'requireIf:ismenu,0|max:10|in:GET,POST,PUT,DELETE',
        'condition|条件'=>'requireIf:ismenu,0|max:100',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];

    protected $scene = [
        'index'=>["page"],
        'save'=>["name","rule_id","status","order","ismenu","icon","method","condition"],
        'update'=>["name","id","rule_id","status","order","ismenu","icon","method","condition"],
        'delete'=>["id"],
    ];
}
