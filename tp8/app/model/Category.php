<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Category extends Model
{
    // 删除之前
    public static function onBeforeDelete($category)
    {
        $count = Article::where("category_id",$category->id)->count();
        if($count > 0){
            ApiException("该分类下还有帖子，请先删除该分类下的帖子");
            return false;
        }
    }

    public function scopeType($query, $type)
    {
    	$query->field('id,title')->where("type",$type)->where('status',1);
    }
    
    // 关联帖子
    public function articles()
    {
        return $this->hasMany('Article','category_id');
    }
    // 关联话题
    public function topics()
    {
        return $this->hasMany('Topic','category_id');
    }
}
