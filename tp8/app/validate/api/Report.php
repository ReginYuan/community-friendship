<?php
declare (strict_types = 1);

namespace app\validate\api;

use think\Validate;

class Report extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'content|举报内容' =>'require|max:255',
        'report_uid|被举报用户ID' =>'require|integer',
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
        "save" => ['content','article_id'],
    ];
}
