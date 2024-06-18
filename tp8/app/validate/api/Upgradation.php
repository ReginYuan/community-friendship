<?php
declare (strict_types = 1);

namespace app\validate\api;

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
        'appid'=> 'require',
        'appVersion'=> 'require',
        'wgtVersion'=> 'require',
        'platform'=>'require|in:android,ios'
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
        'index' => ['appid','appVersion','wgtVersion','platform']
    ];
}
