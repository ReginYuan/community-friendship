<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ConversationMessage extends Model
{
    // 关联消息
    public function message()
    {
        return $this->belongsTo('ImMessage','message_id','id');
    }
}
