<?php
declare (strict_types = 1);

namespace app\validate\api;

use think\Validate;
use app\model\Article as ArticleModel;
class BaseValidate extends Validate
{
    // [自定义规则] 帖子ID是否存在
    protected function isArticleExist($value,$rule,$data){
        $article = ArticleModel::field("id")->find($value);
        if(!$article) return '帖子不存在';
        return true;
    }
}
