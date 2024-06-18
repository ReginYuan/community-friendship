<?php
declare (strict_types = 1);

namespace app\controller\web;

use think\Request;
use app\model\ImMessage as ImMessageModel;
use app\model\ImConversation as ImConversationModel;
use app\model\User as UserModel;
use GatewayWorker\Lib\Gateway;
class Devtool extends Base
{
    // 初始化registerAddress
    public function __construct(){
        $action = request()->action();
        if($action == "bindOnline"){
            $registerAddress = config('gateway_worker.registerAddress');
            Gateway::$registerAddress = $registerAddress;
        }
    }

    // 用户列表
    public function userList(Request $request)
    {
        $keyword = $request->param('keyword',"");
        $page = (int)($request->param('page',1));
        $query = UserModel::field("id,username,avatar,phone,email")
        ->where("status",1);
        if($keyword){
            $query->where('username|phone|email', 'like', '%' . $keyword . '%');
        }
        $data = $query->page($page,20)->paginate(20)->append(["name"]);
        
        return apiSuccess("ok",$data);
    }

    // 切换用户
    public function switchUser(Request $request)
    {
        $user_id = $request->param('user_id');
        $user = UserModel::where("status",1)->find($user_id)->append(["name"]);
        if(!$user) ApiException("用户不存在");
        // 生成token
        $user = $user->toArray();
        $token = createToken($user,"devtool_");
        $user["token"] = $token;
        return apiSuccess("ok",$user);
    }

    // 绑定上线
    public function bindOnline(Request $request)
    {
        $user_id = $request->currentUser->id;
        $client_id = $request->param("client_id");
        // 验证client_id合法性
        if (!$client_id || !Gateway::isOnline($client_id)) {
            ApiException('clientId不合法');
        }
        // 验证当前客户端是否已经绑定
        $binduid = Gateway::getUidByClientId($client_id);
        if ($binduid && $binduid!= $user_id) 
        {
            ApiException('已被绑定');
        }
        // 直接绑定
        Gateway::bindUid($client_id,$user_id);

        // 推送未推送记录
        ImMessageModel::pushUnPushMessage($user_id);

        // 推送总未读数
        ImConversationModel::pushTotalUnreadCount($user_id);

        return apiSuccess('上线成功');
    }

    // 发送消息
    public function send(Request $request)
    {
        $target_id = $request->param("target_id");
        $type = $request->param("type");
        $body = $request->param("body");
        $client_create_time = $request->param("client_create_time");
        $user_id = $request->currentUser->id;
        // 当前我与对方的会话
        $conversation = ImConversationModel::getConversation($user_id,$target_id);

        // 保存消息
        $result = ImMessageModel::saveMessage($conversation,$type,$body,$client_create_time);

        // 更新我与对方的会话最后一条消息last_msg_note
        ImConversationModel::updateMyConversationLastMsgNote($conversation,$result);

        // 只有发送成功时才推送消息
        if($result->state == 100){
            // 更新对方与我的会话未读消息数和最后一条消息last_msg_note
            ImConversationModel::updateTargetConversationLastMsgNote($target_id,$result);

            // 触发推送消息事件
            ImMessageModel::pushMessage($target_id,$result);

            // 推送总未读数
            ImConversationModel::pushTotalUnreadCount($target_id);
        }
        
        // 返回数据
        return apiSuccess("ok",$result);
    }

    // 获取聊天会话列表
    public function getConversationList(Request $request)
    {
        $page = $request->param('page',1);
        $user_id = getCurrentUserIdByToken("devtool_");
        return apiSuccess("ok",ImConversationModel::getConversationList($page,$user_id));
    }

    // 获取某个会话的聊天记录分页列表
    public function getMessage(Request $request)
    {
        $conversation_id = $request->param('conversation_id');
        $page = $request->param('page',1);
        // 验证当前会话是否存在
        $user_id = $request->userId;
        $conversation = ImConversationModel::where('user_id',$user_id)->find($conversation_id);
        if(!$conversation){
            ApiException("会话不存在");
        }
        return apiSuccess("ok",ImMessageModel::getMessage($conversation_id,$page));
    }

    // 阅读会话消息
    public function readConversation(Request $request)
    {
        $conversation_id = $request->param('conversation_id');
        // 更新未读数
        $data = ImConversationModel::readConversation($conversation_id);
        // 推送总未读数
        ImConversationModel::pushTotalUnreadCount($data->user_id);
        return apiSuccess("ok",$data);
    }

    // 发起聊天会话
    public function createConversation(Request $request)
    {
        $target_id = $request->param("target_id");
        $user_id = $request->userId;
        // 不能和自己聊天
        if ($user_id == $target_id) {
            ApiException("不能和自己聊天");
        }
        // 创建会话
        $result = ImConversationModel::createConversation($user_id,$target_id);
        return apiSuccess("ok",$result);
    }
}
