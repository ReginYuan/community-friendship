<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\db\Query;
/**
 * @mixin \think\Model
 */
class ImMessage extends Model
{
    public function getStateTextAttr($value,$data)
    {
        $stateToText = [
            100=>"发送成功",
            101=>"对方已把你拉黑",
            102=>"你把对方拉黑了",
            103=>"对方已被系统封禁",
            104=>"禁止发送",
        ];
        return $stateToText[$data["state"]];
    }
    
    // 关联会话ID
    public function conversationID()
    {
        return $this->hasOne("ConversationMessage", 'message_id','id')
        ->field("conversation_id,message_id,user_id")
        ->bind([
            "conversation_id"
        ]);
    }

    // client_create_time时间戳转时间
    public function getClientCreateTimeAttr($value)
    {
        return date("Y-m-d H:i:s",$value);
    }

    // 关联用户
    public function user()
    {
        return $this->belongsTo("User","user_id")->bind([
            "name",
            "avatar",
        ]);
    }
    
    public function user2()
    {
        return $this->belongsTo("User","user_id")->bind([
            "user_name"=>"name",
            "user_avatar"=>"avatar",
            "user_status"=>"status"
        ]);
    }

    // 关联对方
    public function target()
    {
        return $this->belongsTo("User","target_id")->bind([
            "name",
            "avatar",
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

    // 获取某个会话的聊天记录分页列表
    public static function getMessage($conversation_id,$page){
        $userId = request()->userId;
        $w1 = [
            ['state', '=', 100],
        ];
        $where = self::where($w1);
        $data = ConversationMessage::hasWhere("message",$where)
        ->where("conversation_id",$conversation_id)
        ->page((int)$page,10)
        ->with([
            "message"=>function($query){
                $query->with("user");
            }
        ])
        ->order("id","desc")
        ->paginate(10)
        ->map(function($item) use($conversation_id){
            $item->message->conversation_id = (int)$conversation_id;
            return $item->message;
        });
        return $data;
    }

    // 保存消息
    public static function saveMessage($conversation,$type,$body,$client_create_time){
        $state = 100;
        $conversation_id = $conversation->id;
        $target_id = $conversation->target_id;
        $user_id = $conversation->user_id;

        // 对方被你拉黑了
        if(Blacklist::isBlackedByTarget($target_id)){
            $state = 101;
        }

        // 你把对方拉黑了
        if(Blacklist::isBlackedByMe($target_id)){
            $state = 102;
        }

        // 对方已被系统封禁
        $t = User::field("status")->find($target_id);
        if($t->status == 0) $state = 103;

        $data = [
            // 发布人
            "user_id" => $user_id,
            // 接收人
            "target_id" => $target_id,
            // 消息类型 text文本
            "type"=> $type,
            // 消息内容
            "body"=> $body,
            // 客户端发送时间
            "client_create_time"=> (int)($client_create_time/1000),
            // 是否撤回 0未撤回 1已撤回
            "is_revoke"=> 0,
            // 消息状态 100发送成功，101 对方已把你拉黑，102 你把对方拉黑了， 103对方已被系统封禁，104 禁止发送（内容不合法）
            "state"=> $state,
            // 是否推送消息 0否 1是
            "is_push"=> 0
        ];

        // 创建消息
        $result = self::create($data);

        // 创建消息失败
        if(!$result){
            ApiException("发送消息失败");
        }

        return $result->append(["user","state_text"])->hidden(["user"]);
    }

    // 根据ID获取没有推送过，没有撤回，发送成功的消息
    public static function getUnPushMessageById($id,$target_id = 0){
        // 没有推送过，没有撤回，发送成功的消息
        $data = self::where("is_revoke",0)
        ->where("is_push",0)
        ->where("state",100)
        ->with([
            "conversationID"=>function($query) use($target_id){
                $query->where("user_id",$target_id);
            },
            "user"
        ])
        ->find($id);

        return $data;
    }

    // 根据target_id获取is_push为0的消息
    public static function getUnPushMessages($target_id){
        $data = self::where('target_id',$target_id)
        ->where("is_revoke",0)
        ->where('is_push',0)
        ->where("state",100)
        ->with([
            "conversationID"=>function($query) use($target_id){
                $query->where("user_id",$target_id);
            },
            "user"
        ])
        ->select();
        return $data;
    }

    // [socket]推送未推送的消息
    public static function pushUnPushMessage($target_id){
        // 推送未推送记录
        $msg_list = self::getUnPushMessages($target_id);
        if(count($msg_list) > 0 && isUidOnline($target_id)) {
            // 记录推送成功的消息ID
            $update_list = [];
            foreach ($msg_list as $msg) {
                // 直接发送
                pushMessageToUid($target_id,[
                    'type'=>'message',
                    'data'=>$msg
                ]);
                $update_list[] = [
                    "id" => $msg->id,
                    "is_push" => 1,
                ];
            }
            // 更新推送状态is_push为1
            if(count($update_list) > 0) {
                (new self())->saveAll($update_list);
            }
        }
    }

    // [socket]推送消息
    public static function pushMessage($target_id,$message)
    {
        // 获取未推送消息数据
        $msg = self::getUnPushMessageById($message->id,$target_id);
        if(!$msg) return;
        
        // 推送消息
        $r = pushMessageToUid($target_id,[
            'type'=>'message',
            'data'=>$msg
        ]);
        // 对方不在线
        if(!$r) return;
        // 更新消息推送状态is_push
        $msg->is_push = 1;
        $msg->save();
    }

}
