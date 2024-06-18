<?php
declare (strict_types = 1);

namespace app\validate\admin;

use think\Validate;

class Category extends Validate
{
   /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'page|页码'=>'require|integer|>:0',
        'limit|每页显示条数'=>'integer|>:0|<:1000',
        'type|类型'=>'in:article,topic',
        'ids'=>'require|array',
        'id|ID'=>'require|integer|>:0',
        'title|分类标题'=>'require',
        'status|状态'=>'require|in:0,1',
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
        'index' => ['page','limit',"type"],
        'delete' => ['ids']
    ];

    // save验证场景
    protected function sceneSave()
    {
        return $this->only(['type','title','status'])
                    ->append('type', 'require');
    }

    // update验证场景
    protected function sceneUpdate()
    {
        return $this->only(['id','type','title','status'])
                    ->append('type', 'require');
    }
}
