<?php
declare (strict_types = 1);

namespace app\validate\api;

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
        'topic_id|话题ID' =>'integer',
        'user_id|用户ID' =>'require|integer',
        'page|分页' =>'require|integer',
        'keyword|关键字' =>'require|max:255',
        'id|ID' =>'require|integer',
        'content|内容'=>'require',
        'images|图片'=>'array',
        'category_id|分类ID'=>'require|integer|>=:0',
        'order|排序' => 'in:new,hot'
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
        'search' => ['keyword','page'],
        'read' => ['id'],
        'delete' => ['id'],
        'getFollowArticles' => ['page']
    ];

    // 验证话题/分类下的帖子列表
    public function sceneIndex()
    {
        if(strpos(request()->url(), "topic") !== false){
            return $this->only(['topic_id','page','order']);
        } 
        if(strpos(request()->url(), "user") !== false){
            return $this->only(['user_id','page']);
        }
        return $this->only(['category_id','page']);
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
