<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\controller\api\Base;
use app\model\ImMessage as ImMessageModel;
use app\model\ImConversation as ImConversationModel;
use app\model\User as UserModel;
use GatewayWorker\Lib\Gateway;

class Im extends Base
{
    // 初始化registerAddress
    public function __construct(){
        $action = request()->action();
        if($action == "bindOnline"){
            $registerAddress = config('gateway_worker.registerAddress');
            Gateway::$registerAddress = $registerAddress;
        }
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

        return apiSuccess('绑定成功');
    }

    // 发送消息
    public function send(Request $request)
    {
        // ApiException('聊天功能目前仅限购买过课程的学员使用。详情可观看课程视频演示了解。');
        
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
        return apiSuccess("ok",ImConversationModel::getConversationList($page));
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

    // 创建聊天会话
    public function createConversation(Request $request)
    {
        // ApiException('聊天功能目前仅限购买过课程的学员使用。详情可观看课程视频演示了解。');
        
        $target_id = $request->param("target_id");
        $user_id = $request->currentUser->id;
        // 不能和自己聊天
        if ($request->currentUser->id == $target_id) {
            ApiException("不能和自己聊天");
        }
        // 创建会话
        $result = ImConversationModel::createConversation($user_id,$target_id);
        return apiSuccess("ok",$result);
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
}
