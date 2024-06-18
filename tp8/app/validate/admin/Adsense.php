<?php
declare (strict_types = 1);

namespace app\validate\admin;

use think\Validate;

class Adsense extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'page|页码'=>'require|integer|>:0',
        'type|广告位'=>'in:my',
        'src|广告图'=>'require|url',
        'url|广告地址'=>'require|url',
        'ids'=>'require|array',
        'id|ID'=>'require|integer|>:0',
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
        'update' => ['id','src','url','type'],
        'save' => ['src','url','type'],
        'delete' => ['ids'],
    ];
}
