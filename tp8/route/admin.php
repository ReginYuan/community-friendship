<?php
use think\facade\Route;

$adminkey = "admin";

Route::group($adminkey.'/',function(){
    // 登录
    Route::post('login','admin.User/login');
    // 注销登录
    Route::post('logout','admin.User/logout');
})->allowCrossDomain();

Route::group($adminkey.'/',function(){
    // 当前登录用户信息
    Route::get('user/info','admin.User/info');
    // 用户列表
    Route::get('user/:page','admin.User/index');
    // 删除用户
    Route::post('user/delete','admin.User/delete');
    // 新建用户
    Route::post('user/save','admin.User/save');
    // 更新用户
    Route::post('user/update','admin.User/update');
    // 给用户配置角色
    Route::post('user/setrole','admin.User/setRole');

    // 广告列表
    Route::get('adsense/:page','admin.Adsense/index');
    // 删除广告
    Route::post('adsense/delete','admin.Adsense/delete');
    // 新建广告
    Route::post('adsense/save','admin.Adsense/save');
    // 更新广告
    Route::post('adsense/update','admin.Adsense/update');

    // 帖子列表
    Route::get('article/:page','admin.Article/index');
    // 删除帖子
    Route::post('article/delete','admin.Article/delete');
    // 新建帖子
    Route::post('article/save','admin.Article/save');
    // 更新帖子
    Route::post('article/update','admin.Article/update');

    // 话题列表
    Route::get('topic/:page','admin.Topic/index');
    // 删除话题
    Route::post('topic/delete','admin.Topic/delete');
    // 新建话题
    Route::post('topic/save','admin.Topic/save');
    // 更新话题
    Route::post('topic/update','admin.Topic/update');

    // 分类列表
    Route::get('category/:page','admin.Category/index');
    // 删除分类
    Route::post('category/delete','admin.Category/delete');
    // 新建分类
    Route::post('category/save','admin.Category/save');
    // 更新分类
    Route::post('category/update','admin.Category/update');

    // 反馈列表
    Route::get('feedback/:page','admin.Feedback/index');
    // 删除反馈
    Route::post('feedback/delete','admin.Feedback/delete');

    // 举报列表
    Route::get('report/:page','admin.Report/index');
    // 删除举报
    Route::post('report/delete','admin.Report/delete');
    // 更新举报
    Route::post('report/update','admin.Report/update');

    // 消息列表
    Route::get('immessage/:page','admin.ImMessage/index');
    // 删除消息
    Route::post('immessage/delete','admin.ImMessage/delete');

    // 会话列表
    Route::get('imconversation/:page','admin.ImConversation/index');
    // 删除会话
    Route::post('imconversation/delete','admin.ImConversation/delete');

    // 升级列表
    Route::get('upgradation/:page','admin.Upgradation/index');
    // 删除升级
    Route::post('upgradation/delete','admin.Upgradation/delete');
    // 新建升级
    Route::post('upgradation/save','admin.Upgradation/save');
    // 更新升级
    Route::post('upgradation/update','admin.Upgradation/update');
    // 上传apk安装包
    Route::post('upgradation/upload','admin.Upgradation/upload');

    // 上传图片
    Route::post('upload','admin.Image/upload');

    // 角色列表
    Route::get('role/:page','admin.Role/index');
    // 删除角色
    Route::post('role/delete','admin.Role/delete');
    // 新建角色
    Route::post('role/save','admin.Role/save');
    // 更新角色
    Route::post('role/update','admin.Role/update');
    // 给角色设置权限
    Route::post('role/setrule','admin.Role/setRule');

    // 权限列表
    Route::get('rule/:page','admin.Rule/index');
    // 删除权限
    Route::post('rule/delete','admin.Rule/delete');
    // 新建权限
    Route::post('rule/save','admin.Rule/save');
    // 更新权限
    Route::post('rule/update','admin.Rule/update');

    // 用户日志列表
    Route::get('user_action_log/:page','admin.UserActionLog/index');
    // 删除用户日志
    Route::post('user_action_log/delete','admin.UserActionLog/delete');
    // 清空用户日志
    Route::post('user_action_log/clear','admin.UserActionLog/clear');

    // 拉黑ip
    Route::post('ip_blacklist/save','admin.IpBlacklist/save');
    // ip黑名单列表
    Route::get('ip_blacklist/:page','admin.IpBlacklist/index');
    // 删除ip黑名单
    Route::post('ip_blacklist/delete','admin.IpBlacklist/delete');

    // 图片审核列表
    Route::get('ip_image/:page','admin.IpImage/index');
    // 图片审核
    Route::post('ip_image/update','admin.IpImage/update');
    // 删除图片审核记录
    Route::post('ip_image/delete','admin.IpImage/delete');

})->allowCrossDomain()->middleware([
    // \app\middleware\Cross::class, 
    \app\middleware\AdminUserAuth::class
]);