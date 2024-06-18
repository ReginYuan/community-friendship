<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use app\model\User as UserModel;
/**
 * @mixin \think\Model
 */
class Blacklist extends Model
{
    // 关联拉黑人信息
    public function blackuser()
    {
        return $this->belongsTo('User','black_id')->bind([
            'avatar',
            'name',
            'desc',
            'fans_count'
        ]);
    }
    // 获取我是否已拉黑
    public function getCurrentBlackedAttr($value,$data)
    {
        return true;
    }
    // 我是否拉黑了对方
    public static function isBlackedByMe($target_id){
        $user = request()->currentUser;
        $data = self::where('user_id',$user->id)->where('black_id',$target_id)->find();
        if($data){
            return true;
        }
        return false;
    }

    // 对方是否拉黑了我
    public static function isBlackedByTarget($target_id,$my_id = 0){
        if($my_id == 0){
            $my_id = request()->currentUser->id;
        }
        
        $data = self::where('user_id',$target_id)->where('black_id',$my_id)->find();
        if($data){
            return true;
        }
        return false;
    }

    // 是否在黑名单中
    public static function isBlacklist($id){
        $user_id = getCurrentUserIdByToken();
        if(!$user_id){
            return false;
        }
        $data = self::where('user_id',$user_id)->where('black_id',$id)->find();
        if($data){
            return true;
        }else{
            return false;
        }
    }

    // 加入黑名单
    public static function addBlacklist($id){
        $user = request()->currentUser;
        if($user->id == $id){
            ApiException("不能操作自己");
        }
        // 用户是否存在
        if(!UserModel::isUserExist("id",$id)){
            ApiException("用户不存在");
        }
        return self::create(['user_id' => $user->id, 'black_id' => $id]);
    }

    // 移除黑名单
    public static function removeBlacklist($id){
        $user = request()->currentUser;
        if($user->id == $id){
            ApiException("不能操作自己");
        }
        return self::where('user_id',$user->id)->where('black_id',$id)->delete();
    }

    // 获取所有我拉黑/被我拉黑的用户
    public static function getBlackUsers(){
        $user_id = getCurrentUserIdByToken();
        if(!$user_id){
            return [];
        }
        $v1 = self::where('user_id',$user_id)->column("black_id");
        $v2 = self::where('black_id',$user_id)->column("user_id");
        // 合并两个数组
        return array_merge($v1,$v2);
    }
}
