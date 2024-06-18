<?php

return [
    // token过期时间
    'token_expire'=>0,
    // ip黑名单限制
    'ip_limit'=>[
        // 是否开启 true 开启 false 关闭
        'enable'=>true,
    ],
    // 接口请求次数限制
    'request_limit'=>[
        // 是否开启 true 开启 false 关闭
        'enable'=>true,
        // 60秒内
        'time'=>60,
        // 限定时间内最大请求数
        'count'=>1000
    ],
    // 聊天发送次数限制
    'chat_limit'=>[
        // 是否开启 true 开启 false 关闭
        'enable'=>true,
        // 60秒内
        'time'=>60,
        // 限定时间内最大发送消息数
        'count'=>300
    ],
    // 是否开启内容限制
    'content_limit'=>[
        // 是否开启 true 开启 false 关闭
        'enable'=>false
    ],
];