<?php
use think\facade\Route;

// 用户列表
Route::get('devtool/user_list/:page', 'web.Devtool/userList')->allowCrossDomain();

// 切换用户
Route::post('devtool/switchuser/:user_id', 'web.Devtool/switchUser')->allowCrossDomain();

// 获取聊天会话列表
Route::get('devtool/conversation/:page','web.Devtool/getConversationList');

// Route::get('test','web.Devtool/test');

// 需要登录
Route::group(function(){
    // 发送消息
    Route::post('devtool/send','web.Devtool/send');
    // 查看会话消息记录
    Route::post('devtool/read_conversation/:conversation_id','web.Devtool/readConversation');
    // 发起聊天会话
    Route::post('devtool/create_conversation','web.Devtool/createConversation');
    // 获取某个会话的聊天记录
    Route::get('devtool/:conversation_id/message/:page','web.Devtool/getMessage');
    // 绑定上线
    Route::post('devtool/bind_online','web.Devtool/bindOnline');

})->middleware([
    \app\middleware\DevtoolUserAuth::class
]);
