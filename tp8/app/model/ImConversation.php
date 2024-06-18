<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ImConversation extends Model
{
    public function user2()
    {
        return $this->belongsTo("User","user_id")->bind([
            "user_name"=>"name",
            "user_avatar"=>"avatar",
            "user_status"=>"status"
        ]);
    }
    
    // 关联对方
    public function target2()
    {
        return $this->belongsTo("User","target_id")->bind([
            "target_name"=>"name",
            "target_avatar"=>"avatar",
            "target_status"=>"status"
        ]);
    }
    
    /**
     * 获取会话，没有则直接创建
     * @param int $user_id  发送者id
     * @param int $target_id 接收者id
     * @return ImConversation 
     * @throws ApiException
     */
    public static function getConversation($user_id,$target_id)
    {
        $data = [
            "target_id" => (int)$target_id,
            "user_id" => (int)$user_id,
        ];
        $conversation = self::where($data)->find();
        // 会话不存在 则创建会话
        if(!$conversation){
            $conversation = self::createConversation($user_id,$target_id);
        }
        return $conversation;
    }

    /**
     * 创建会话
     * @param int $user_id  发送者id
     * @param int $target_id 接收者id
     * @return ImConversation 
     * @throws ApiException
     */
    public static function createConversation($user_id,$target_id)
    {
        $data = [
            "target_id" => (int)$target_id,
            "user_id" => (int)$user_id,
        ];
        $conversation = self::where($data)->with(["target"])->hidden(["user_id","create_time","target"])->find();
        if ($conversation) {
            // 聊天会话置顶
            $conversation->update_time = time();
            $conversation->save();
            return $conversation;
        }

        // 对方不存在
        $target = User::find($target_id);
        if (!$target) {
            ApiException("对方不存在");
        }

        $data["unread_count"] = 0;
        $data["last_msg_note"] = "打个招呼吧~";
        $data["create_time"] = time();
        $data["update_time"] = time();

        $conversation = self::create($data);
        return $conversation->append(["target"])->hidden(["user_id","create_time","target"]);
    }

    // 获取当前用户聊天会话分页列表，根据update_time排序，并且关联获取用户信息
    public static function getConversationList($page = 1,$user_id = 0)
    {
        if(!$user_id){
            $user_id = getCurrentUserIdByToken();
        }
        $data = self::page($page,10)
        ->order("update_time","desc")
        ->where("user_id",$user_id)
        ->with("target")
        ->hidden(["user_id","create_time"])
        ->paginate(10);
        return $data;
    }

    // 关联获取用户信息
    public function target()
    {
        return $this->belongsTo("User","target_id")->bind([
            "name",
            "avatar",
        ]);
    }

    // 更新我与对方的会话最后一条消息last_msg_note
    public static function updateMyConversationLastMsgNote($conversation,$message)
    {
        $stateToText = [
            100=>"发送成功",
            101=>"对方已把你拉黑",
            102=>"你把对方拉黑了",
            103=>"对方已被系统封禁",
            104=>"禁止发送",
        ];
        $conversation->last_msg_note = $message->state == 100 ? $message->body : $stateToText[$message->state];
        $result = $conversation->save();
        if(!$result){
            ApiException("更新会话最后一条消息失败");
        }
        // 创建关联
        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'message_id'=> $message->id,
            'user_id'=>$conversation->user_id,
        ]);

        // 推送消息
        pushMessageToUid($conversation->user_id,[
            "type" => "conversation",
            "data"=> $conversation->append(["target"])->hidden(["user_id","create_time","target"])->toArray()
        ]);

        return $result;
    }
    
    // 更新对方与我的会话未读消息数和最后一条消息last_msg_note
    public static function updateTargetConversationLastMsgNote($target_id,$message)
    {
        // 获取当前用户ID
        $user_id = request()->currentUser->id;
        // 获取对方与我的会话
        $conversation = self::getConversation($target_id,$user_id);
        // 未读消息数+1
        $conversation->unread_count += 1;
        // 最后一条消息
        $conversation->last_msg_note = $message->body;
        $result = $conversation->save();
        if(!$result){
            ApiException("更新对方的最后一条消息失败");
        }
        // 创建关联
        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'message_id'=> $message->id,
            'user_id'=>$conversation->user_id
        ]);

        // 推送消息
        pushMessageToUid($conversation->user_id,[
            "type" => "conversation",
            "data"=> $conversation->append(["target"])->hidden(["user_id","create_time","target"])->toArray()
        ]);

        return $result;
    }

    // 阅读会话消息
    public static function readConversation($conversation_id)
    {
        // 获取当前用户ID
        $user_id = request()->currentUser->id;
        $conversation = self::where('user_id',$user_id)->find($conversation_id);
        if(!$conversation){
            ApiException("会话不存在");
        }
        // 阅读数为0
        if($conversation->unread_count > 0){
            $conversation->unread_count = 0;
            $conversation->save();
        }
        return $conversation->append(["target"])->hidden(["user_id","create_time","target"]);
    }
    
    // [socket]统计总未读数，并推送
    public static function pushTotalUnreadCount($user_id){
        $TotalUnreadCount = self::where('user_id',$user_id)->sum("unread_count");

        // 推送消息
        pushMessageToUid($user_id,[
            "type" => "total_unread_count",
            "data"=> $TotalUnreadCount
        ]);
    }
}
