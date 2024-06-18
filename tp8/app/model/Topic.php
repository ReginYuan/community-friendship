<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Topic extends Model
{
    // 关联今日文章
    public function todayArticle()
    {
        return $this->hasMany('Article')->whereTime('article.create_time', 'today');
    }

    // 关联分类
    public function category()
    {
        return $this->belongsTo('Category')->bind([
            'category_name' => 'title'
        ]);
    }

    // 获取话题列表
    public static function getTopicList($page = 1,$where = [])
    {
        $query = self::page($page,10)->order("id","desc");
        if(count($where)){
            $query = $query->where($where);
        }
        return $query->withCount(['todayArticle'])->paginate(10);
    }

    // 统计话题帖子数
    public static function updateArticlesCount($topic)
    {
        $count = Article::where("topic_id",$topic->id)->count();
        $topic->article_count = $count;
        $topic->save();
    }
}
