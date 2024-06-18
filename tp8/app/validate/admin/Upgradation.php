<?php
declare (strict_types = 1);

namespace app\validate\admin;

use think\Validate;

class Upgradation extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'page|页码'=>'require|integer|>:0',
        'id'=>'require|integer|>:0',
        'appid|appid'=>'require',
        'name|应用名称'=>'require',
        'title|应用名称'=>'require',
        'contents|内容'=>'require',
        "platform|平台"=>"require|in:android,ios",
        "version|版本"=>"require",
        "url|安装包url"=>"require|url",
        "stable_publish|是否发布"=>"require|in:0,1",
        "is_mandatory|强制更新"=>"require|in:0,1"
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
        'save' => ['appid','name','title','contents','platform','version','url','stable_publish','is_silently'],
        'update' => ['id','appid','name','title','contents','platform','version','url','stable_publish','is_silently'],
        'delete' => ['ids']
    ];
}
