<?php
declare (strict_types = 1);

namespace app\validate\api;

use think\Validate;
use app\model\Article as ArticleModel;
class Support extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'article_id|帖子ID' =>'require|integer|isArticleExist',
        'type|操作类型' =>'require|in:ding,cai',
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
        'action' => ['article_id','type']
    ];

}
