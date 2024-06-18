<?php
declare (strict_types = 1);

namespace app\validate\admin;

use think\Validate;

class User extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'password|密码' =>'require',
        'keyword|关键词'=>'max:50',
        'page|页码'=>'require|integer|>:0',
        'username|用户名'=>'require',
        'phone|手机号'=>'require|mobile',
        'email|邮箱'=>'email',
        'avatar|头像'=>'url',
        'status|状态'=>'require|in:0,1',
        'ids'=>'require|array',
        'id|ID'=>'require|integer|>:0',
        'role_ids|角色ID'=>'array',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];

    // 配置场景
    protected $scene = [
        // 列表
        'index'=>["keyword","page"],
        // 用户密码登录
        'login'=>['username','password'],
        // 删除
        'delete'=>['ids'],
        // 设置角色
        'setRole'=>['id','role_ids'],
    ];

    // 添加场景
    public function sceneSave()
    {
    	return $this->only(['username','password','email','phone','avatar','status'])
        ->append("username","length:6,20")
        ->append("password","length:6,20");
    }  

    // 修改场景
    public function sceneUpdate()
    {
    	return $this->only(['id','username','password','email','phone','avatar','status'])
        ->remove('password', 'require')
        ->append("username","length:6,20")
        ->append("password","length:6,20");
    } 
}
