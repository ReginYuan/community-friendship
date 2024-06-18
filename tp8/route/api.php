<?php
use think\facade\Route;

// 隐私政策
Route::get('privacy', 'api.Common/privacy');
// 用户协议
Route::get('agreement', 'api.Common/agreement');

// 无需登录
Route::group('api/v1/',function(){
    // 广告位列表
    Route::get('adsense/:type', 'api.v1.Adsense/index');
    // 指定分类下的话题列表
    Route::get('category/:category_id/topic/:page', 'api.v1.Topic/index');
    // 指定分类下的帖子列表
    Route::get('category/:category_id/article/:page', 'api.v1.Article/index');
    // 分类列表
    Route::get('category/:type', 'api.v1.Category/index');

    // 指定话题下的帖子列表
    Route::get('topic/:topic_id/article/:page', 'api.v1.Article/index');
    // 话题详情
    Route::get('topic/:id', 'api.v1.Topic/read');

    // 指定帖子的评论列表
    Route::get('article/:article_id/comment/:page', 'api.v1.Comment/index');

    // 指定评论的回复列表
    Route::get('comment/:comment_id/replies/:page', 'api.v1.Comment/index');

    // 评论详情
    Route::get('comment/:id', 'api.v1.Comment/read');

    // 指定用户的评论列表
    Route::get('user/:user_id/comment/:page', 'api.v1.User/comments');

    // 帖子详情
    Route::get('article/:id', 'api.v1.Article/read');

    // 获取帖子观看记录列表
    Route::get('article_read_log/:page', 'api.v1.ArticleReadLog/index');


    // 搜索帖子
    Route::get('search/article/:page', 'api.v1.Article/search');
    // 搜索话题
    Route::get('search/topic/:page', 'api.v1.Topic/search');
    // 搜索用户
    Route::get('search/user/:page', 'api.v1.User/search'); 

    // 指定用户下的帖子列表
    Route::get('user/:user_id/article/:page', 'api.v1.Article/index');

    // 获取指定用户的关注列表
    Route::get('user/:user_id/follows/:page', 'api.v1.Follow/index');
    // 获取指定用户的粉丝列表
    Route::get('user/:user_id/fans/:page', 'api.v1.Follow/index');

    // 获取用户详情
    Route::get('user_info/:id', 'api.v1.User/read');

    // 发送验证码
    Route::post('user/sendcode','api.v1.User/sendCode');
    // 手机验证码登录
    Route::post('user/phonelogin','api.v1.User/phoneLogin');
    // 用户密码登录
    Route::post('user/login','api.v1.User/login');
    // 退出登录
    Route::post('user/logout','api.v1.User/logout');
    // 获取我关注的人的帖子列表
    Route::get('get_follow_articles/:page', 'api.v1.Article/getFollowArticles');
    // 获取聊天会话列表
    Route::get('im/conversation/:page','api.v1.Im/getConversationList');
    // 忘记密码
    Route::post('user/forget','api.v1.User/forget');
    // 获取app升级版本
    Route::post('upgradation','api.v1.Upgradation/index');
})->allowCrossDomain([
    "Access-Control-Allow-Origin"=> "*",
    "Access-Control-Allow-Headers"=>"token, Authorization, Content-Type,Content-Length, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With,Origin,accept-language,accept-encoding,referer,content-type,user-agent,accept,content-length,connection,host,Content-Disposition",
    'Access-Control-Max-Age' => 3600,
]);

// 需要登录
Route::group('api/v1/',function(){
    // 发送验证码（用于改绑手机号，修改密码）
    Route::post('user/sendcode2','api.v1.User/sendCode2');
    // 修改密码
    Route::post('user/changepwd','api.v1.User/changepwd');
    // 获取当前用户信息
    Route::get('user/info','api.v1.User/info');
    // 绑定手机
    Route::post('user/bindphone','api.v1.User/bindPhone');
    // 修改资料
    Route::post('user/changeinfo','api.v1.User/changeInfo');
    // 修改头像
    Route::post('user/changeavatar','api.v1.User/changeAvatar');

    // 删除帖子
    Route::post('article/delete/:id','api.v1.Article/delete');

    // 删除评论
    Route::post('comment/delete/:id','api.v1.Comment/delete');

    // 加入黑名单
    Route::post('add_blacklist/:id','api.v1.Blacklist/save');
    // 移除黑名单
    Route::post('remove_blacklist/:id','api.v1.Blacklist/delete');
    // 我的黑名单列表
    Route::get('myblacklist/:page','api.v1.Blacklist/index');

    // 关注用户
    Route::post('add_follow/:id','api.v1.Follow/save');
    // 取消关注用户
    Route::post('remove_follow/:id','api.v1.Follow/delete');

    // 上传图片
    Route::post('upload','api.v1.Image/upload');
    // 发布帖子
    Route::post('article/save','api.v1.Article/save');
    // 顶/踩帖子
    Route::post('support/:type/:article_id','api.v1.Support/action');

    // 发布评论
    Route::post('comment/save','api.v1.Comment/save');
    // 回复评论
    Route::post('comment/reply','api.v1.Comment/save');

    // 用户反馈
    Route::post('feedback/save','api.v1.Feedback/save');
    // 获取用户反馈列表
    Route::get('feedback/:page','api.v1.Feedback/index');
    
    // 发送消息
    Route::post('im/send','api.v1.Im/send');
    // 查看会话消息记录
    Route::post('im/read_conversation/:conversation_id','api.v1.Im/readConversation');
    // 获取某个会话的聊天记录
    Route::get('im/:conversation_id/message/:page','api.v1.Im/getMessage');
    // 创建聊天会话
    Route::post('im/create_conversation','api.v1.Im/createConversation');
    // 绑定上线
    Route::post('im/bind_online','api.v1.Im/bindOnline');

    // 收藏帖子
    Route::post('add_collection/:article_id','api.v1.Collection/save');
    // 取消收藏帖子
    Route::post('remove_collection/:article_id','api.v1.Collection/delete');
    // 获取我收藏的帖子列表
    Route::get('mycollections/:page','api.v1.Collection/index');

    // 举报帖子
    Route::post('report/save','api.v1.Report/save');

})->allowCrossDomain([
    "Access-Control-Allow-Origin"=> "*",
    "Access-Control-Allow-Headers"=>"token, Authorization, Content-Type,Content-Length, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With,Origin,accept-language,accept-encoding,referer,content-type,user-agent,accept,content-length,connection,host,Content-Disposition",
    'Access-Control-Max-Age' => 3600,
])->middleware([
    \app\middleware\ApiUserAuth::class
]);
