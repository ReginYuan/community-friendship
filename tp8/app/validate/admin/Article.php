<?php
declare (strict_types = 1);

namespace app\validate\admin;

use think\Validate;

class Article extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|ID'=>'require|integer|>:0',
        'ids'=>'require|array',
        'page|页码'=>'require|integer|>:0',
        'keyword|关键词'=>'chsDash',
        'topic_id|话题ID' =>'integer',
        'content|内容'=>'require',
        'images|图片'=>'array',
        'category_id|分类ID'=>'require|integer|>=:0'
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
        'index' => ['page','keyword'],
        'delete' => ['ids']
    ];

    // update验证场景
    protected function sceneUpdate()
    {
        return $this->only(['id','category_id','topic_id','content','images'])
                    ->append('category_id', 'isCategoryExist');
    }

    // save验证场景
    protected function sceneSave()
    {
        return $this->only(['category_id','topic_id','content','images'])
                    ->append('category_id', 'isCategoryExist');
    }

    // [自定义规则] 验证话题是否存在
    protected function isTopicExist($value, $rule='', $data='', $field='')
    {
        $topic = \app\model\Topic::find($value);
        if(!$topic){
            return "话题不存在";
        }
        return true;
    }

    // [自定义规则] 验证分类是否存在
    protected function isCategoryExist($value, $rule='', $data='', $field=''){
        $category = \app\model\Category::find($value);
        if(!$category){
            return "分类不存在";
        }
        return true;
    }
}
