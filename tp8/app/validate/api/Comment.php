<?php
declare (strict_types = 1);

namespace app\validate\api;

use think\Validate;

class Comment extends BaseValidate
{
     /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|评论ID'=>'require|integer',
        'reply_id|回复ID' =>'require|integer',
        'article_id|帖子ID' =>'require|integer',
        'page|分页' =>'require|integer',
        'id|ID' =>'require|integer',
        'content|评论内容' =>'require|max:255',
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
        "read" => ['id'],
        'delete' => ['id'],
    ];

    // index场景验证场景定义
    protected function sceneIndex(){
        if(strpos(request()->url(), "replies") !== false){
            return $this->only(['comment_id','page']);
        } 
        return $this->only(['article_id','page']);
    }

    // save场景验证场景定义
    protected function sceneSave(){
        if(strpos(request()->url(), "reply") !== false){
            return $this->only(['reply_id','content']);
        } 
        return $this->only(['article_id','content'])
        ->append("article_id","isArticleExist");
    }

}
