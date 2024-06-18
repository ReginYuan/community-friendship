<?php
declare (strict_types = 1);

namespace app\validate\api;

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
        'id|ID' =>'require|integer|>:0',
        'password|密码' =>'require|alphaDash',
        'phone|手机号'=>'require|mobile|checkMobile',
        'code|验证码'=>'require|length:6|checkSms',
        'keyword|关键词'=>'require|chsDash',
        'page|页码'=>'require|integer|>:0',
        'username|用户名'=>'require',
        'name|昵称'=>'require|chsDash',
        'sex|性别'=>'require|in:0,1,2',
        'qg|情感'=>'require|in:0,1,2',
        'birthday|生日'=>'require|dateFormat:Y-m-d',
        'path|所在地'=>'require|chsDash',
        'avatar|头像'=>'image|fileSize:10240',
        'user_id|ID' =>'require|integer|>:0',
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
        // 发送验证码
        'sendCode'=>['phone'],
        // 手机号登录
        'phoneLogin'=>['phone','code'],
        // 搜索
        'search'=>["keyword","page"],
        // 用户密码登录
        'login'=>['username','password'],
        // 绑定手机号
        'bindPhone' => ['phone',"code"],
        // 修改头像
        'changeAvatar' => ['avatar'],
        // 修改资料
        'changeInfo' => ['name','sex','qg','birthday','path'],
        // 获取用户详情
       'read' => ['id'],
        // 获取用户评论列表
        'comments' => ['user_id','page']
    ];

    // 修改密码验证场景
    public function sceneChangepwd()
    {
        if(request()->currentUser && request()->currentUser->password){
            return $this->only(['password','code'])
            ->append("password","length:6,20");
        }
    	return $this->only(['password']);
    }   

    // 忘记密码验证场景
    public function sceneForget()
    {
        return $this->only(['phone','password','code'])
            ->append("password","length:8,20");
    }  

    // [自定义验证规则] 验证码验证
    protected function checkSms($value, $rule='', $data='', $field='')
    {
        $action = request()->action();
        // 需要登录才能获取验证码的场景
        $needLogin = ['changepwd'];
        if(in_array($action,$needLogin)){
            $data["phone"] = request()->currentUser->getData("phone");
            if(!$data["phone"]){
                ApiException("请先绑定手机号");
            }
        }
        return checkSms($data["phone"],$value);
    }
    
    // [自定义验证规则] 验证手机号
    protected function checkMobile($value, $rule='', $data='', $field='')
    {
        if($value == "15100000000" || $value == "15200000000"){
            return "换个手机号吧，该手机号不存在";
        }
        return true;
    }

}
