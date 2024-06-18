<?php
declare (strict_types = 1);

namespace app\validate\admin;

use think\Validate;

class Report extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'page|页码'=>'require|integer|>:0',
        'state|处理结果'=>'require|in:success,fail',
        'id|举报ID'=>'require|integer|>:0',
        'ids'=>'require|array'
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
        'update' => ['id','state'],
        'delete' => ['ids']
    ];
}
