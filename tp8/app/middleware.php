<?php
// 全局中间件定义文件
return [
    // 请求次数/聊天消息次数限制
    \app\middleware\RequestLimit::class,
    // ip限制
    \app\middleware\IpLimit::class,
    // 内容限制
    \app\middleware\ContentLimit::class,
    // 跨域
     \think\middleware\AllowCrossDomain::class
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    // \think\middleware\SessionInit::class,
];
