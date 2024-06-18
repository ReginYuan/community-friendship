<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Workerman设置 仅对 php think worker:gateway 指令有效
// +----------------------------------------------------------------------
return [
    // 完整监听地址
    'socket'                => 'websocket://0.0.0.0:23489', 
    // Register配置
    'registerAddress'       => '127.0.0.1:1240',
    // Gateway配置
    'name'                  => 'Im',
    'count'                 => 2,
    'lanIp'                 => '127.0.0.1',
    'startPort'             => 3900,
    'pingInterval'          => 30,
    'pingData'              => '{"type":"ping"}',

    // BusinsessWorker配置
    'businessWorker'        => [
        'name'         => 'BusinessWorker',
        'count'        => 4
    ],
];
