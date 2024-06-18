<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Follow extends Model
{
    // 关注之前
    public static function onBeforeInsert($follow)
    {
        if(Blacklist::isBlackedByTarget($follow->follow_id,$follow->user_id)){
            ApiException("我已经被对方拉黑");
            return false;
        }
    }

    // 关注之后
    public static function onAfterInsert($follow)
    {
        // 更新up主的粉丝数
        User::updateFansCount($follow->_follow);
        // 更新粉丝的关注数
        User::updateFollowsCount($follow->_fan);
    }

    // 取消关注之后
    public static function onAfterDelete($follow)
    {
        // 更新up主的粉丝数
        User::updateFansCount($follow->_follow);
        // 更新粉丝的关注数
        User::updateFollowsCount($follow->_fan);
    }

    // 获取我是否已关注
    public function getCurrentFollowAttr($value,$data)
    {
        return true;
    }

    // 关联关注人信息
    public function follow()
    {
        return $this->belongsTo('User','follow_id')->bind([
            'avatar',
            'name',
            'desc',
            'fans_count'
        ]);
    }

    public function _follow()
    {
        return $this->belongsTo('User','follow_id');
    }

    // 关联粉丝信息
    public function fan()
    {
        return $this->belongsTo('User','user_id')->bind([
            'avatar',
            'name',
            'desc',
            'fans_count'
        ]);
    }

    public function _fan()
    {
        return $this->belongsTo('User','user_id');
    }

    // // 我是否也关注了粉丝
    // public function isFollowByUserId()
    // {
    //     return $this->hasOne('Follow','follow_id','user_id')
    //     ->field("user_id,follow_id")
    //     ->bind([
    //         "isfollow" => "current_follow"
    //     ]);
    // }

    // 是否在关注列表里
    public static function isFollow($user_id,$follow_id){
        $follow = self::where(['user_id' => $user_id, 'follow_id' => $follow_id])->find();
        if($follow){
            return $follow;
        }
        return false;
    }

    // 关注用户
    public static function addFollow($follow_id){
        $user_id = request()->currentUser->id;
        if(!self::isFollow($user_id,$follow_id)){
            // 被关注用户ID是否存在
            if(!User::isUserExist('id',$follow_id)){
                ApiException('被关注用户不存在');
            }

            $follow = new self();
            $follow->user_id = $user_id;
            $follow->follow_id = $follow_id;
            if(!($follow->save())){
                ApiException('关注失败');
            }

            return true;
        }
        ApiException('已经关注过了');
    }

    // 取消关注用户
    public static function removeFollow($follow_id){
        $user_id = request()->currentUser->id;
        $follow = self::isFollow($user_id,$follow_id);
        if($follow){
            if(!($follow->delete())){
                ApiException('取消关注失败');
            }
            return true;
        }
        ApiException('你还没有关注过');
    }

    // 根据用户ID获取关注用户ID列表
    public static function getFollowIdListByUserId($user_id){
        $follow = self::where(['user_id' => $user_id])
        // 排除被封禁的用户
        ->hasWhere('_follow',['status'=>1])
        ->column('follow_id');
        return $follow;
    }
}
