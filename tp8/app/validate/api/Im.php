<?php
declare (strict_types = 1);

namespace app\validate\api;

use think\Validate;
use app\model\ImConversation;
class Im extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        "page|页码" => "require|integer",
        "target_id|接收者id" => "require|integer",
        "conversation_id|会话id" => "require|integer|checkConversationId",
        "type|消息类型" => "require|in:text,image,voice,video,file,location,custom",
        "body|消息内容" => "require|max:2000",
        "client_create_time|客户端创建消息的时间戳" => "require|integer",
        "client_id|客户端id" => "require|integer"
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];

    protected $scene = [
        "send" => ["target_id", "type", "body", "client_create_time"],
        "getConversationList" => ["page"],
        "getMessage" => ["conversation_id","page"],
        "createConversation" => ["target_id"],
        "bindOnline" => ["client_id"],
        "readConversation"=>["conversation_id"]
    ];

    // 验证会话id是否存在
    protected function checkConversationId($value, $rule, $data)
    {
        $user_id = request()->currentUser->id;
        $conversation = ImConversation::where("user_id",$user_id)->where("id",$value)->find();
        if(!$conversation){
            return "会话ID不存在";
        }
        return true;
    }
}
