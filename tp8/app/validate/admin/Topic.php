<?php
declare (strict_types = 1);

namespace app\validate\admin;

use think\Validate;

class Topic extends Validate
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
        'category_id|分类ID'=>'integer|>:0',
        'title|话题标题'=>'require|chsDash',
        'cover|封面图片' => 'require|url',
        'desc|话题描述' => 'chsDash',
        'id|话题ID'=>'require|integer|>:0',
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
        'index' => ['page',"limit","category_id"],
        'save' => ['title','cover','desc','category_id'],
        'update' => ['id','title','cover','desc','category_id'],
        'delete' => ['ids'],
    ];
}
