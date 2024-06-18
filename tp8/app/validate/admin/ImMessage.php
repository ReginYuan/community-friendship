<?php
declare (strict_types = 1);

namespace app\validate\admin;

use think\Validate;

class ImMessage extends Validate
{
   /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        "keyword|关键词"=>'max:255',
        'page|页码'=>'require|integer|>:0',
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
        'index' => ['page',"keyword"],
        'delete' => ['ids']
    ];
}
