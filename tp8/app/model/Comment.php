<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Comment extends Model
{
    // 设置json类型字段
	protected $json = ['quote'];

    // 发表评论前
    public static function onBeforeInsert($comment)
    {
        // 作者ID
        $target_id = 0;
        // 回复
        if($comment->comment_id){
            $target_id = \think\facade\Db::name("comment")->where("id",$comment->comment_id)->value("user_id");
        } 
        // 评论
        else {
            $target_id = $comment->_article->user_id;
        }
        if(Blacklist::isBlackedByTarget($target_id)){
            ApiException("我已经被对方拉黑");
            return false;
        }
    }

    // 发表评论后
    public static function onAfterInsert($comment)
    {
        // 用户评论数+1
        User::updateCommentsCount($comment->_user);
        // 评论回复数+1
        if($comment->comment_id){
            self::updateReplyCount($comment->comment_id);
        } else {
            // 文章评论数+1
            Article::updateCommentCount($comment->_article);
        }
    }

    // 删除评论后
    public static function onAfterDelete($comment)
    {
        // 用户评论数-1
        if($comment->_user){
            User::updateCommentsCount($comment->_user);
        }
        // 评论回复数-1
        if($comment->comment_id){
            self::updateReplyCount($comment->comment_id);
        } else {
            // 文章评论数-1
            Article::updateCommentCount($comment->_article);
        }
    }

    // 关联是否关注该作者
    public function isFollowCurrentUser()
    {
        return $this->hasOne('Follow','follow_id','user_id')
        ->field("user_id,follow_id")
        ->bind([
            "isfollow" => "current_follow"
        ]);
    }

    // 关联用户
    public function user()
    {
        return $this->belongsTo('User','user_id')->bind([
            'avatar',
            'name'
        ]);
    }

    public function _user()
    {
        return $this->belongsTo('User','user_id');
    }

    // 关联帖子
    public function article()
    {
        return $this->belongsTo('Article','article_id')->bind([
            'article_cover'=>"cover",
            'article_title'=>"title"
        ]);
    }
    public function _article()
    {
        return $this->belongsTo('Article','article_id');
    }


    // 发表帖子评论
    public static function addComment($article_id, $content){
        $user = request()->currentUser;
        $data = [
            'article_id' => $article_id,
            'content' => $content,
            'user_id' => $user->id
        ];
        $comment = new self();
        $comment->save($data);
        $comment = $comment->toArray();
        return [
            "id"=> $comment["id"],
            "article_id"=> $comment["article_id"],
            "comment_id"=> null,
            "user_id"=> $comment["user_id"],
            "reply_count"=> 0,
            "content"=> $comment["content"],
            "create_time"=> $comment["create_time"],
            "avatar"=> $comment["avatar"],
            "name"=> $comment["name"]
        ];
    }

    // 回复帖子评论
    public static function addReply($reply_id,$content){
        $user = request()->currentUser;
        
        // 评论是否存在
        $reply = self::with("user")->find($reply_id);
        if(!$reply){
            ApiException('你要回复的评论不存在');
        } 
        
        $data = [
            'article_id' => $reply->article_id,
            'content' => $content,
            'user_id' => $user->id,
            'comment_id'=>$reply_id
        ];

        // 引用评论
        if($reply->comment_id){
            $data["comment_id"] = $reply->comment_id;
            $data["quote"] = [
                "content" => $reply->content,
                "user_id" => $reply->user_id,
                "name" => $reply->getData("name"),
                "avatar" => $reply->avatar
            ];
        }

        $comment = new self();
        $comment->save($data);
        return [
            "id"=> $comment->id,
            "article_id"=> $comment->article_id,
            "comment_id"=>$comment->comment_id,
            "user_id"=> $comment->user_id,
            "reply_count"=> 0,
            "content"=> $comment->content,
            "create_time"=> $comment->create_time,
            "avatar"=> $comment->avatar,
            "name"=> $comment->getData("name"),
            "quote" => array_key_exists("quote",$data) ? $data["quote"] : $comment->quote,
        ];
    }

    // 更新评论回复数
    public static function updateReplyCount($comment_id)
    {
        $count = self::where("comment_id",$comment_id)->count();
        self::where("id",$comment_id)->update([
            "reply_count" => $count
        ]);
    }
}
