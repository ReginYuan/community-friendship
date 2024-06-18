<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class UserActionLog extends Model
{
    // 关联用户
    public function user()
    {
        return $this->belongsTo("User","user_id")->bind([
            "name",
            "avatar",
            "user_status"=>"status"
        ]);
    }

    public function setParamAttr($value)
    {
        return json_encode($value);
    }

    public function getParamAttr($value)
    {
        return json_decode($value,true);
    }

    // 添加日志
    public static function addLog($notes = "",$type = "info")
    {
        $request = request();
        self::create([
            "ip"=>$request->ip(),
            "user_id" => getCurrentUserIdByToken(),
            "url"=>$request->url(),
            "method"=>$request->method(),
            "user_agent"=>$request->header("user-agent"),
            "param"=>$request->param(),
            "type"=>$type,
            "notes"=>$notes
        ]);
    }
}
