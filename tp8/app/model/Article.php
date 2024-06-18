<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Article extends Model
{
    // 发帖成功之后
    public static function onAfterInsert($article)
    {
        // 更新用户帖子数
        User::updateArticlesCount($article->user);
        // 更新话题帖子数
        if($article->topic_id){
            Topic::updateArticlesCount($article->topic);
        }
    }

    // 删帖成功之后
    public static function onAfterDelete($article)
    {
        // 更新用户帖子数
        User::updateArticlesCount($article->user);
        // 更新话题帖子数
        if($article->topic_id){
            Topic::updateArticlesCount($article->topic);
        }
        // 删除观看记录
        ArticleReadLog::where("article_id",$article->id)->delete();
        // 删除收藏记录
        Collection::where("article_id",$article->id)->delete();
        // 删除顶踩记录
        Support::where("article_id",$article->id)->delete();
    }

    
    // 关联作者
    public function user()
    {
        return $this->belongsTo('User')->bind([
            'name',
            'avatar',
            "user_status" => "status"
        ]);
    }

    // 关联话题
    public function topic()
    {
        return $this->belongsTo('Topic')->bind([
            'topic_name' => 'title'
        ]);
    }

    // 关联分类
    public function category()
    {
        return $this->belongsTo('Category')->bind([
            'category_name' => 'title'
        ]);
    }

    // 关联support
    public function support()
    {
        return $this->hasMany('Support');
    }

    // 关联我的support
    public function mySupport()
    {
        return $this->hasOne('Support')
        ->field("id,type,user_id,article_id")
        ->bind([
            "user_support_action"
        ]);
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

    // 自动设置标题
    public function setContentAttr($value,$data)
    {
        $content = $value;
        // 从content字段中截取前100个字符作为标题   
        $title = mb_substr($value, 0, 100, 'utf-8');  
        // 如果截取的内容中包含HTML标签，你可能还需要去除这些标签  
        $title = strip_tags($title);  
        // 可以添加...来表示内容被截断  
        if (mb_strlen($value, 'utf-8') > 100) {  
            $title .= '...';  
        }  
        $this->set('title',$title);
        return $content;
    }

    // 自动将图片数组转成字符串
    public function setImagesAttr($value)
    {
        if(is_array($value)){
            return implode(',', $value);
        }
        return $value;
    }

    // 自动将图片字符串转成数组
    public function getImagesAttr($value)
    {
        if($value){
            return explode(',', $value);
        }
        return [];
    }

    // 自动获取封面
    public function getCoverAttr($value,$data)
    {
        if($data["images"] != ""){
            $arr = explode(',', $data["images"]);
            return $arr[0];
        }
        return "";
    }

    // 根据id获取帖子
    public static function getArticleById($id)
    {
        $query = self::where("id",$id);
        $query = self::withArticleDetail($query);
        $data = $query->find();
        if(!$data) {
            ApiException("帖子不存在");
        }
        return self::formatArticleItem($data);
    }

    // 获取帖子详情数据
    public static function withArticleDetail($query)
    {
        // 获取登录用户ID
        $currentUser_id = getCurrentUserIdByToken();

        $query = $query->with([
            // 作者昵称头像
            "user",
            // 话题名称和ID
            "topic",
            // 判断是否已点赞该文章
            "mySupport" => function(\think\Db\Query $query) use($currentUser_id){
                $query->where('support.user_id', $currentUser_id);
            },
            // 判断是否已关注该作者
            "isFollowCurrentUser" => function(\think\Db\Query $query) use($currentUser_id){
                $query->where('follow.user_id', $currentUser_id);
            }
        ]);

        return $query;
    }

    // 获取我关注的作者帖子分页列表
    public static function getMyFollowArticleList($page = 1,$order = "id desc")
    {
        // 获取当前登录用户ID
        $currentUser_id = getCurrentUserIdByToken();

        // 当前用户没登陆，返回空
        if($currentUser_id == 0){
            return [
                "total" => 0,
                "per_page" => 10,
                "current_page" => 1,
                "last_page" => 0,
                "data" => []
            ];
        }

        // 获取关注用户ID列表
        $uids = Follow::getFollowIdListByUserId($currentUser_id);

        $query = self::page($page,10)->where("user_id","in", $uids)->order($order);

        $query = self::withArticleDetail($query);

        return $query->hidden([ "content"])->paginate(10)->filter(function($item){
            return self::formatArticleItem($item);
        });
    }

    // 获取帖子分页列表
    public static function getArticleList($page = 1,$where = [],$order = "id desc")
    {
        // $query = self::page($page,10)->field("id,user_id,title,images,url,topic_id,category_id,share_count,ding_count,cai_count,comment_count,read_count,collect_count,create_time,update_time");

        // 用户封禁的帖子不显示
        $query = self::page($page,10);
        if($order){
            $query = $query->order($order);
        }
        
        if(count($where)){
            $query = $query->where($where);
        }

        // 获取所有我拉黑/被我拉黑的用户ID
        $BlackUserIds = getBlackUsers();
        $query = $query->where("user_id","not in", $BlackUserIds);

        $query = self::withArticleDetail($query);

        return $query->hidden([ "content"])->paginate(10)->filter(function($item){
            return self::formatArticleItem($item);
        });
    }

    // 格式化结果
    public static function formatArticleItem($item)
    {
        $item->isfollow = $item->isfollow == null ? false : true;
        if($item->user_support_action == null){
            $item->user_support_action = "";
        }
        return $item;
    }

    // 更新收藏数
    public static function updateCollectCount($article,$action = "+")
    {
        // if(is_numeric($article)){
        //     $article = self::find($article);
        // }
        // $article->collect_count = $action == "+" ? $article->collect_count + 1 : $article->collect_count - 1;

        // if($article->collect_count < 0){
        //     $article->collect_count = 0;
        // }
        // $article->save();
        // return $article->collect_count;
        if(is_numeric($article)){
            $article = self::find($article);
        }
        if(!$article) ApiException("帖子不存在");
        $user_id = request()->currentUser->id;
        $count = Collection::where([
            "article_id"=>$article->id
        ])->count();

        $article->collect_count = $count;
        $article->save();
        return $count;
    }

    // 更新评论数（不包括回复）
    public static function updateCommentCount($article)
    {
        if($article){
            $count = Comment::where([
                "article_id"=>$article->id,
                "comment_id"=> null
            ])->count();
            $article->comment_count = $count;
            $article->save();
        }
    }

    // 更新顶/踩数
    public static function updateSupportCount($article_id)
    {
        $data = [];
        $data["ding_count"] = Support::where([
            "article_id"=>$article_id,
            "type"=>1
        ])->count();

        $data["cai_count"] = Support::where([
            "article_id"=>$article_id,
            "type"=>0
        ])->count();
        
        // 更新帖子顶踩数
        self::update($data, ['id' => $article_id]);
    }
}
