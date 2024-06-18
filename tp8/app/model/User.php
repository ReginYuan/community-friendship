<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class User extends Model
{
    // 删用户成功之后
    public static function onAfterDelete($user)
    {
        $uid = $user->id;
        // 删除帖子
        Article::destroy(function($query) use($uid){
            $query->where('user_id',$uid);
        });
        // 删除评论
        Comment::destroy(function($query) use($uid){
            $query->where('user_id',$uid);
        });
        // 删除阅读记录
        ArticleReadLog::destroy(function($query) use($uid){
            $query->where('user_id',$uid);
        });
        // 删除收藏
        Collection::destroy(function($query) use($uid){
            $query->where('user_id',$uid);
        });
        // 删除聊天会话
        ImConversation::destroy(function($query) use($uid){
            $query->where('user_id',$uid);
        });
        ImConversation::destroy(function($query) use($uid){
            $query->where('target_id',$uid);
        });
        // 删除聊天信息
        ImMessage::destroy(function($query) use($uid){
            $query->where('user_id',$uid);
        });
        ImMessage::destroy(function($query) use($uid){
            $query->where('target_id',$uid);
        });
        // 删除聊天会话关联信息
        ConversationMessage::destroy(function($query) use($uid){
            $query->where('user_id',$uid);
        });
        // 删除顶踩数据
        Support::destroy(function($query) use($uid){
            $query->where('user_id',$uid);
        });
        // 删除角色用户关联
        UserRole::destroy(function($query) use($uid){
            $query->where('user_id',$uid);
        });
    }

    // 多对多关联角色
    public function roles()
    {
        return $this->belongsToMany('Role','user_role','role_id','user_id');
    }


    // 默认名字
    public function getNameAttr($value,$data)
    {
        $name = "";
        if($data["username"]){
            $name = $data["username"];
        } elseif($data["phone"]){
            $name = maskPhoneNumber($data["phone"]);
        } elseif($data["email"]){
            $name = $data["email"];
        } else {
            $name = "未知";
        }
        return $name;
    }

    // 获取完整头像路径
    public function getAvatarAttr($value)
    {
        return getUploadPath($value);
    }

    // 默认隐藏手机号
    public function getPhoneAttr($value)
    {
        return maskPhoneNumber($value);
    }

    // 默认隐藏密码
    public function getPasswordAttr($value)
    {
        return $value ? true : false;
    }

    // 用户是否存在
    public static function isUserExist(...$args){
        $key = "";
        $value = "";
        if(count($args) == 2){
            $key = $args[0];
            $value = $args[1];
        } else {
            $value = $args[0];
            $key = "";
            if(isPhoneNumber($value)){
                $key = "phone";
            } elseif (isEmail($value)) {
                $key = "email";
            } else {
                return false;
            }
        }
        
        $user = self::getUserInfo($key,$value);
        if($user){
            // 用户已被禁用
            if($user->status == 0) ApiException('该用户已被禁用');
            return $user;
        }
        return false;
    }

    // 获取用户信息
    public static function getUserInfo(...$args){
        if(is_array($args[0])){
            $params = $args[0];
        } else {
            $params = [
                $args[0] => $args[1]
            ];
        }

        $user = false;

        if(array_key_exists("token",$params)){
            return cache($token);
        }

        if(array_key_exists("id",$params)){
            $user = self::find($params["id"]);
        }
        
        if(array_key_exists("username",$params)){
            $user = self::where('username',$params["username"])->find();
        }
        if(array_key_exists("phone",$params)){
            $user = self::where('phone',$params["phone"])->find();
        }
        if(array_key_exists("email",$params)){
            $user = self::where('email',$params["email"])->find();
        }

        if($user){
            $user->append(['name']);
        }

        return $user;
    }

    // 登录处理
    public static function loginHandle($user){
        if(!is_array($user)){
            $user = $user->toArray();
        }
        $user['token'] = createToken($user);
        // 将用户ID和token进行绑定
        cache("login_".$user["id"],$user["token"],config('api.token_expire'));
        return $user;
    }

    // 关联是否拉黑该作者
    public function isBlackedCurrentUser()
    {
        return $this->hasOne('Blacklist','black_id','id')
        ->field("user_id,black_id")
        ->bind([
            "isblacked" => "current_blacked"
        ]);
    }

    // 关联是否关注该作者
    public function isFollowCurrentUser()
    {
        return $this->hasOne('Follow','follow_id','id')
        ->field("user_id,follow_id")
        ->bind([
            "isfollow" => "current_follow"
        ]);
    }

    // 列表/详情数据，关联当前用户是否关注该作者
    public static function withIsFollow($query,$user_id = 0)
    {
        // 获取登录用户ID
        if($user_id == 0){
            $user_id = getCurrentUserIdByToken();
        }
        return $query->with([
            // 判断是否已关注该作者
            "isFollowCurrentUser" => function(\think\Db\Query $query) use($user_id){
                $query->where('follow.user_id', $user_id);
            },
        ]);
    }

    // 统计用户帖子数
    public static function updateArticlesCount($user)
    {
        $count = Article::where("user_id",$user->id)->count();
        $user->articles_count = $count;
        $user->save();
    }

    // 统计用户关注数
    public static function updateFollowsCount($user)
    {
        $count = Follow::where("user_id",$user->id)->count();
        $user->follows_count = $count;
        $user->save();
    }

    // 统计用户粉丝数
    public static function updateFansCount($user)
    {
        $count = Follow::where("follow_id",$user->id)->count();
        $user->fans_count = $count;
        $user->save();
    }

    // 统计用户评论数
    public static function updateCommentsCount($user)
    {
        $count = Comment::where("user_id",$user->id)->count();
        $user->comments_count = $count;
        $user->save();
    }
}
