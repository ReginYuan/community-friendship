<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Report extends Model
{
    // 更新之后
    public static function onAfterUpdate($report)
    {
        // 封禁用户
        if($report->state == "success" && $report->reportUser){
            $report->reportUser->status = 0;
            $report->reportUser->save();
        }
    }

    // 关联举报人
    public function user()
    {
        return $this->belongsTo("User","user_id")->bind([
            "name",
            "avatar",
            "user_status"
        ]);
    }

    // 关联被举报人
    public function reportUser()
    {
        return $this->belongsTo("User","report_uid")->bind([
            "report_name"=>"name",
            "report_avatar"=>"avatar",
            "report_user_status"=>"status"
        ]);
    }
}
