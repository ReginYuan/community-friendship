<?php
declare (strict_types = 1);

namespace app\validate\admin;

use think\Validate;

class Feedback extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'page|页码'=>'require|integer|>:0',
        'user_id|用户ID'=>'require|integer|>:0',
        'content|内容' =>'require|max:255',
        'images|图片' => 'array',
        'id|反馈ID'=>'require|integer|>:0',
        'ids'=>'require|array',
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
        'delete' => ['ids']
    ];
}
