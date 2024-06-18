<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ArticleReadLog extends Model
{
    // 关联帖子
    public function article()
    {
        return $this->belongsTo('Article');
    }

    // 更新阅读记录
    public static function updateReadLog($article_id,$article = null){
        // 获取当前用户ID
        $user_id = getCurrentUserIdByToken();
        // 获取帖子
        if(!$article){
            $article = Article::find($article_id);
        }

        $ip = request()->ip();
        $where = [
            'ip' => $ip,
            'article_id' => $article_id,
        ];
        if($user_id){
            $where['user_id'] = $user_id;
        }
        $log = self::where($where)->find();

        // 更新最后一次阅读时间
        if($log){
            $log->update_time = time();
            $log->save();
            return $article;
        }
        
        // 阅读数+1
        $article->read_count += 1;
        $article->save();

        // 添加阅读记录
        self::create($where);

        return $article;
    }
}
