<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Collection extends Model
{
    // 判断当前用户是否收藏该帖子
    public static function isCurrentUserCollectArticle($article_id)
    {
        $user_id = getCurrentUserIdByToken();
        if(!$user_id){
            return false;
        }
        $collection = self::where(['user_id' => $user_id, 'article_id' => $article_id])->find();
        if($collection){
            return true;
        }
        return false;
    }

    // 获取我是否已收藏
    public function getCurrentCollectAttr($value,$data)
    {
        return true;
    }

    public function article()
    {
        return $this->belongsTo('Article','article_id');
    }

    // 是否在收藏列表里
    public static function isCollection($user_id,$article_id){
        $collection = self::where(['user_id' => $user_id, 'article_id' => $article_id])->find();
        if($collection){
            return $collection;
        }
        return false;
    }

    // 收藏帖子
    public static function addCollection($article_id){
        $user_id = request()->currentUser->id;
        if(!self::isCollection($user_id,$article_id)){
            // 被收藏帖子ID是否存在
            $article = Article::find($article_id);
            if(!$article){
                ApiException('被收藏帖子不存在');
            }

            $collection = new self();
            $collection->user_id = $user_id;
            $collection->article_id = $article_id;
            if($collection->save()){
                // 收藏数+1
                return Article::updateCollectCount($article, "+");
            }
            ApiException('收藏失败');
        }
        ApiException('已经收藏过了');
    }

    // 取消收藏帖子
    public static function removeCollection($article_id){
        $user_id = request()->currentUser->id;
        $collection = self::isCollection($user_id,$article_id);
        if($collection){
            if($collection->delete()){
                // 收藏数-1
                $count = Article::updateCollectCount($article_id, "-");
                return $count;
            }
            ApiException('取消收藏失败');
        }
        ApiException('你还没有收藏过');
    }
}
